<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    15th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Server;


use Joomla\CMS\Client\FtpClient;
use phpseclib3\Net\SFTP as SftpClient;


/**
 * Creates independent FTP and SFTP clients without opening a connection.
 * 
 * @since  6.2.1
 */
class ClientFactory
{
	/**
	 * Create an FTP client with the validated transfer options.
	 *
	 * @param   array<string, int>  $options  Transfer mode and timeout options.
	 *
	 * @return  FtpClient
	 * @since   6.2.1
	 */
	public function ftp(array $options): FtpClient
	{
		return new FtpClient($options);
	}

	/**
	 * Create an SFTP client for the requested endpoint.
	 *
	 * @param   string  $host  Remote host.
	 * @param   int     $port  Remote SSH port.
	 *
	 * @return  SftpClient
	 * @since   6.2.1
	 */
	public function sftp(string $host, int $port): SftpClient
	{
		return new SftpClient($host, $port);
	}
}

