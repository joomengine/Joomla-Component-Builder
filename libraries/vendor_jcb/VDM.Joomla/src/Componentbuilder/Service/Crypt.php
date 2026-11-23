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
use phpseclib3\Crypt\AES as BASEAES;
use VDM\Joomla\Componentbuilder\Crypt as Crypto;
use VDM\Joomla\Componentbuilder\Crypt\KeyLoader;
use VDM\Joomla\Componentbuilder\Crypt\Random;
use VDM\Joomla\Componentbuilder\Crypt\Password;
use VDM\Joomla\Componentbuilder\Crypt\FOF;
use VDM\Joomla\Componentbuilder\Crypt\Aes;
use VDM\Joomla\Componentbuilder\Crypt\Aes\Legacy;


/**
 * Phpseclib Crypt Service Provider
 * 
 * @since 3.2.0
 */
class Crypt implements ServiceProviderInterface
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
		$container->alias(Crypto::class, 'Crypt')
			->share('Crypt', [$this, 'getCrypt'], true);

		$container->alias(Random::class, 'Crypt.Random')
			->share('Crypt.Random', [$this, 'getRandom'], true);

		$container->alias(Password::class, 'Crypt.Password')
			->share('Crypt.Password', [$this, 'getPassword'], true);

		$container->alias(KeyLoader::class, 'Crypt.Key')
			->share('Crypt.Key', [$this, 'getKeyLoader'], true);

		$container->alias(BASEAES::class, 'Crypt.AESCBC')
			->share('Crypt.AESCBC', [$this, 'getBASEAESCBC'], false);

		$container->alias(FOF::class, 'Crypt.FOF')
			->share('Crypt.FOF', [$this, 'getFOF'], true);

		$container->alias(Aes::class, 'Crypt.AES.CBC')
			->share('Crypt.AES.CBC', [$this, 'getAesCBC'], true);

		$container->alias(Legacy::class, 'Crypt.AES.LEGACY')
			->share('Crypt.AES.LEGACY', [$this, 'getAesLEGACY'], true);
	}

	/**
	 * Get the Crypto class
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Crypto
	 * @since 3.2.0
	 */
	public function getCrypt(Container $container): Crypto
	{
		return new Crypto(
			$container->get('Crypt.FOF'),
			$container->get('Crypt.AES.CBC'),
			$container->get('Crypt.AES.LEGACY'),
			$container->get('Crypt.Password')
		);
	}

	/**
	 * Get the Password class
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Password
	 * @since 3.2.0
	 */
	public function getPassword(Container $container): Password
	{
		return new Password();
	}

	/**
	 * Get the Random class
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Random
	 * @since 3.2.0
	 */
	public function getRandom(Container $container): Random
	{
		return new Random();
	}

	/**
	 * Get the KeyLoader class
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  KeyLoader
	 * @since 3.2.0
	 */
	public function getKeyLoader(Container $container): KeyLoader
	{
		return new KeyLoader();
	}

	/**
	 * Get the AES Cyper with CBC mode
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  BASEAES
	 * @since 3.2.0
	 */
	public function getBASEAESCBC(Container $container): BASEAES
	{
		return new BASEAES('cbc');
	}

	/**
	 * Get the Wrapper AES Cyper with CBC mode
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Aes
	 * @since 3.2.0
	 */
	public function getAesCBC(Container $container): Aes
	{
		return new Aes(
			$container->get('Crypt.AESCBC'),
			$container->get('Crypt.Random')
		);
	}

	/**
	 * Get the Wrapper AES Legacy Cyper with CBC mode
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Legacy
	 * @since 3.2.0
	 */
	public function getAesLEGACY(Container $container): Legacy
	{
		return new Legacy(
			$container->get('Crypt.AESCBC')
		);
	}

	/**
	 * Get the FOF AES Cyper with CBC mode
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FOF
	 * @since 3.2.0
	 */
	public function getFOF(Container $container): FOF
	{
		return new FOF();
	}
}

