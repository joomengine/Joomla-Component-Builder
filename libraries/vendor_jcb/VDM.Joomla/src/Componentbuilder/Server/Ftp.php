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


use Joomla\CMS\Client\FtpClient;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Componentbuilder\Interfaces\Serverinterface;


/**
 * Ftp Class
 * 
 * @since 3.2.0
 */
class Ftp implements Serverinterface
{
	/**
	 * Creates isolated protocol clients.
	 *
	 * @var    ClientFactory
	 * @since  6.2.1
	 */
	protected ClientFactory $clients;

	/**
	* The client object
	 *
	 * @var     FtpClient|null
	 * @since 3.2.0
	 **/
	protected ?FtpClient $client = null;

	/**
	* The server details
	 *
	 * @var     object
	 * @since 3.2.0
	 **/
	protected ?object $details = null;

	/**
	 * Construct the FTP transport.
	 *
	 * @param   ClientFactory|null  $clients  Protocol client factory.
	 *
	 * @since   6.2.1
	 */
	public function __construct(?ClientFactory $clients = null)
	{
		$this->clients = $clients ?? new ClientFactory();
	}

	/**
	 * set the server details
	 *
	 * @param   object     $details   The server details
	 *
	 * @return  Ftp
	 * @since 3.2.0
	 **/
	public function set(object $details): Ftp
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
		try
		{
			if ($this->connected())
			{
				return $this->client->store($localPath, $fileName);
			}
		}
		catch (\Exception $error)
		{
			// The caller reports a failed transfer without exposing credentials.
			$this->client = null;
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
		if ($this->client instanceof FtpClient && $this->client->isConnected())
		{
			return true;
		}

		$this->client = $this->getClient();

		return $this->client instanceof FtpClient;
	}

	/**
	 * get the FtpClient object
	 *
	 * @return  FtpClient|null
	 * @since 3.2.0
	 **/
	private function getClient(): ?FtpClient
	{
		// make sure we have a string and it is not default or empty
		if (!StringHelper::check($this->details->signature ?? null))
		{
			return null;
		}

		$signature = [];
		parse_str($this->details->signature, $signature);

		foreach (['host' => 'HOSTNAME', 'port' => 'PORT_INT', 'username' => 'user@name.com', 'password' => 'password'] as $field => $example)
		{
			if (!isset($signature[$field]) || !is_string($signature[$field])
				|| $signature[$field] === '' || $signature[$field] === $example)
			{
				return null;
			}
		}

		$port = filter_var($signature['port'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);

		if ($port === false || str_contains($signature['host'], "\0") || str_contains($signature['username'], "\0"))
		{
			return null;
		}

		$options = [];

		if (isset($signature['options']))
		{
			if (!is_array($signature['options']))
			{
				return null;
			}

			if (isset($signature['options']['timeout']))
			{
				$timeout = filter_var($signature['options']['timeout'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

				if ($timeout === false)
				{
					return null;
				}

				$options['timeout'] = $timeout;
			}

			if (isset($signature['options']['type']))
			{
				$type = $signature['options']['type'];
				// Autoload Joomla's transfer constants, also available without ext-ftp.
				class_exists(FtpClient::class);
				$modes = ['FTP_ASCII' => FTP_ASCII, 'FTP_BINARY' => FTP_BINARY, 'FTP_AUTOASCII' => FTP_AUTOASCII];

				if (!is_string($type))
				{
					return null;
				}

				if (isset($modes[$type]))
				{
					$type = $modes[$type];
				}
				elseif (filter_var($type, FILTER_VALIDATE_INT) !== false
					&& in_array((int) $type, array_values($modes), true))
				{
					$type = (int) $type;
				}
				else
				{
					return null;
				}

				$options['type'] = $type;
			}
		}

		// Explicit login checks are required: getInstance() discards login's result.
		$client = $this->clients->ftp($options);

		if (!$client->connect($signature['host'], $port))
		{
			return null;
		}

		if (!$client->login($signature['username'], $signature['password']))
		{
			$client->quit();

			return null;
		}

		return $client;
	}
}

