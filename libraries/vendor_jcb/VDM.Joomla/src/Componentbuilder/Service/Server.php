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

namespace VDM\Joomla\Componentbuilder\Service;


use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use VDM\Joomla\Componentbuilder\Server as Client;
use VDM\Joomla\Componentbuilder\Server\Load;
use VDM\Joomla\Componentbuilder\Server\Ftp;
use VDM\Joomla\Componentbuilder\Server\Sftp;
use VDM\Joomla\Componentbuilder\Server\ClientFactory;


/**
 * Server Service Provider
 * 
 * @since 3.2.0
 */
class Server implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	public function register(Container $container)
	{
		$container->alias(Client::class, 'Server')
			->share('Server', [$this, 'getServer'], true);

		$container->alias(Load::class, 'Server.Load')
			->share('Server.Load', [$this, 'getServerLoad'], true);

		$container->alias(ClientFactory::class, 'Server.ClientFactory')
			->share('Server.ClientFactory', [$this, 'getClientFactory'], true);

		$container->alias(Ftp::class, 'Server.FTP')
			->share('Server.FTP', [$this, 'getServerFtp'], true);
		$container->alias(Sftp::class, 'Server.SFTP')
			->share('Server.SFTP', [$this, 'getServerSftp'], true);
	}

	/**
	 * Get the Server Client class
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Client
	 * @since 3.2.0
	 */
	public function getServer(Container $container): Client
	{
		return new Client(
			$container->get('Server.Load'),
			$container->get('Server.FTP'),
			$container->get('Server.SFTP')
		);
	}

	/**
	 * Get the Server Load class
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Load
	 * @since 3.2.0
	 */
	public function getServerLoad(Container $container): Load
	{
		return new Load(
			$container->get('Load'),
			$container->get('Model.Server.Load')
		);
	}

	/**
	 * Get the Server Ftp class
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Ftp
	 * @since 3.2.0
	 */
	public function getServerFtp(Container $container): Ftp
	{
		return new Ftp($container->get('Server.ClientFactory'));
	}

	/**
	 * Get the Server Sftp class
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Sftp
	 * @since 3.2.0
	 */
	public function getServerSftp(Container $container): Sftp
	{
		return new Sftp(
			$container->get('Crypt.Key'),
			null,
			$container->get('Server.ClientFactory')
		);
	}

	/**
	 * Get the protocol client construction boundary.
	 *
	 * @param   Container  $container  The dependency container.
	 *
	 * @return  ClientFactory
	 * @since   6.2.1
	 */
	public function getClientFactory(Container $container): ClientFactory
	{
		return new ClientFactory();
	}
}

