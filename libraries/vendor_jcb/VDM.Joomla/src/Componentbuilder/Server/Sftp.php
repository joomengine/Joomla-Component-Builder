<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Server;


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplicationInterface as CMSApplication;
use phpseclib3\Net\SFTP as SftpClient;
use VDM\Joomla\Componentbuilder\Crypt\KeyLoader;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\FileHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Componentbuilder\Interfaces\Serverinterface;


/**
 * Sftp Class
 * 
 * @since 3.2.0
 */
class Sftp implements Serverinterface
{
	/**
	 * Creates isolated protocol clients.
	 *
	 * @var    ClientFactory
	 * @since  6.2.1
	 */
	protected ClientFactory $clients;

	/**
	 * The KeyLoader
	 *
	 * @var    KeyLoader
	 * @since 3.2.0
	 */
	protected KeyLoader $key;

	/**
	* The client object
	 *
	 * @var     SftpClient|null
	 * @since 3.2.0
	 **/
	protected ?SftpClient $client = null;

	/**
	* The server details
	 *
	 * @var     object
	 * @since 3.2.0
	 **/
	protected ?object $details = null;

	/**
	 * Application object.
	 *
	 * @var    CMSApplication
	 * @since 3.2.0
	 **/
	protected CMSApplication $app;

	/**
	 * Constructor
	 *
	 * @param KeyLoader    $key   The key loader object.
	 * @param CMSApplication|null     $app          The app object.
	 * @param ClientFactory|null      $clients      Protocol client factory.
	 *
	 * @since 3.2.0
	 */
	public function __construct(KeyLoader $key, ?CMSApplication $app = null, ?ClientFactory $clients = null)
	{
		$this->key = $key;
		$this->clients = $clients ?? new ClientFactory();
		$this->app = $app ?: Factory::getApplication();
	}

	/**
	 * set the server details
	 *
	 * @param   object    $details    The server details
	 *
	 * @return  Sftp
	 * @since 3.2.0
	 **/
	public function set(object $details): Sftp
	{
		// we need to make sure the if the details changed to get a new server client
		if (!ObjectHelper::equal($details, $this->details))
		{
			// set the details
			$this->details = $details;

			// reset the client if it was set before
			$this->client = null;
		}

		return $this;
	}

	/**
	 * move a file to server with the FTP client
	 *
	 * @param   string      $localPath      The full local path to the file
	 * @param   string      $fileName      The file name
	 *
	 * @return  bool
	 * @since 3.2.0
	 **/
	public function move(string $localPath, string $fileName): bool
	{
		if (!is_file($localPath) || !is_readable($localPath))
		{
			return false;
		}

		try
		{
			if ($this->connected() && ($data = FileHelper::getContent($localPath, null)) !== null)
			{
				$path = (string) ($this->details->path ?? '');
				$path = $path === '' ? '' : rtrim($path, '/') . '/';

				return $this->client->put($path . trim($fileName), $data);
			}
		}
		catch (\Exception $error)
		{
			$this->client = null;
			$this->app->enqueueMessage(
				Text::sprintf('COM_COMPONENTBUILDER_MOVING_OF_THE_S_FAILED', $fileName),
				'Error'
			);
		}

		return false;
	}

	/**
	 * Make sure we are connected
	 *
	 * @return  bool
	 * @since 3.2.0
	 **/
	private function connected(): bool
	{
		// check if we have a connection
		if ($this->client instanceof SftpClient && $this->client->isAuthenticated()
			&& ($this->client->isConnected() || $this->client->ping()))
		{
			return true;
		}

		$this->client = $this->getClient();

		return $this->client instanceof SftpClient;
	}

	/**
	 * get the SftpClient object
	 *
	 * @return  SftpClient|null
	 * @since 3.2.0
	 **/
	private function getClient(): ?SftpClient
	{
		if (!StringHelper::check($this->details->host ?? null)
			|| !StringHelper::check($this->details->username ?? null))
		{
			return null;
		}

		$host = trim($this->details->host);
		$username = trim($this->details->username);
		$port = filter_var($this->details->port ?? 22, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);

		if ($host === '' || $username === '' || $port === false
			|| str_contains($host, "\0") || str_contains($username, "\0"))
		{
			return null;
		}

		// Passwords and passphrases are exact values, including whitespace and "0".
		$password = $this->details->password ?? null;
		$passphrase = $this->details->secret ?? false;
		$authentication = (int) ($this->details->authentication ?? 0);
		$credentials = [];

		if (in_array($authentication, [2, 3, 4, 5], true))
		{
			$privateKey = $this->details->private_key ?? null;
			$message = Text::_('COM_COMPONENTBUILDER_LOADING_THE_PRIVATE_KEY_TEXT_FAILED');

			if ($authentication === 2 || $authentication === 3)
			{
				$path = $this->details->private ?? '';
				$privateKey = is_string($path) && is_file($path) && is_readable($path)
					? FileHelper::getContent($path, null) : null;
				$message = Text::_('COM_COMPONENTBUILDER_LOADING_THE_PRIVATE_KEY_FILE_FAILED');
			}

			try
			{
				if (!StringHelper::check($privateKey))
				{
					$this->app->enqueueMessage($message, 'Error');

					return null;
				}

				$credentials[] = $this->key::load(trim($privateKey), $passphrase);
			}
			catch (\Exception $error)
			{
				$this->app->enqueueMessage($message, 'Error');

				return null;
			}
		}
		elseif ($authentication !== 1)
		{
			return null;
		}

		if (in_array($authentication, [1, 3, 5], true))
		{
			if (!is_string($password) || $password === '')
			{
				$this->app->enqueueMessage(Text::_('COM_COMPONENTBUILDER_LOGIN_FAILED'), 'Error');

				return null;
			}

			$credentials[] = $password;
		}

		try
		{
			$sftp = $this->clients->sftp($host, $port);

			if ($sftp->login($username, ...$credentials))
			{
				return $sftp;
			}
		}
		catch (\Exception $error)
		{
			// Authentication failures share one diagnostic and never expose secrets.
		}

		$this->app->enqueueMessage(Text::_('COM_COMPONENTBUILDER_LOGIN_FAILED'), 'Error');

		return null;
	}
}

