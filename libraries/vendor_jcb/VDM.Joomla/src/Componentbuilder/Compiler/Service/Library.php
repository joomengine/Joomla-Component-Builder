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

namespace VDM\Joomla\Componentbuilder\Compiler\Service;


use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use VDM\Joomla\Componentbuilder\Compiler\Library\Data;
use VDM\Joomla\Componentbuilder\Compiler\Library\Structure;
use VDM\Joomla\Componentbuilder\Compiler\Library\Document;
use VDM\Joomla\Componentbuilder\Compiler\Library\IncludeHelper;
use VDM\Joomla\Componentbuilder\Compiler\Library\JoomlaThree\IncludeHelper as J3IncludeHelper;
use VDM\Joomla\Componentbuilder\Compiler\Library\JoomlaFour\IncludeHelper as J4IncludeHelper;
use VDM\Joomla\Componentbuilder\Compiler\Library\JoomlaFive\IncludeHelper as J5IncludeHelper;
use VDM\Joomla\Componentbuilder\Compiler\Library\JoomlaSix\IncludeHelper as J6IncludeHelper;


/**
 * Compiler Library
 * 
 * @since 3.2.0
 */
class Library implements ServiceProviderInterface
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
		$container->alias(J6IncludeHelper::class, 'Library.J6.IncludeHelper')
			->share('Library.J6.IncludeHelper', [$this, 'getJ6IncludeHelper'], true);

		$container->alias(J5IncludeHelper::class, 'Library.J5.IncludeHelper')
			->share('Library.J5.IncludeHelper', [$this, 'getJ5IncludeHelper'], true);

		$container->alias(J4IncludeHelper::class, 'Library.J4.IncludeHelper')
			->share('Library.J4.IncludeHelper', [$this, 'getJ4IncludeHelper'], true);

		$container->alias(J3IncludeHelper::class, 'Library.J3.IncludeHelper')
			->share('Library.J3.IncludeHelper', [$this, 'getJ3IncludeHelper'], true);

		$container->alias(Data::class, 'Library.Data')
			->share('Library.Data', [$this, 'getData'], true);

		$container->alias(Structure::class, 'Library.Structure')
			->share('Library.Structure', [$this, 'getStructure'], true);

		$container->alias(Document::class, 'Library.Document')
			->share('Library.Document', [$this, 'getDocument'], true);

		$container->alias(IncludeHelper::class, 'Library.IncludeHelper')
			->share('Library.IncludeHelper', [$this, 'getIncludeHelper'], true);
	}

	/**
	 * Get the Compiler Library Data
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Data
	 * @since 3.2.0
	 */
	public function getData(Container $container): Data
	{
		return new Data(
			$container->get('Config'),
			$container->get('Registry'),
			$container->get('Customcode'),
			$container->get('Customcode.Gui'),
			$container->get('Field.Data'),
			$container->get('Model.Filesfolders'),
			$container->get('Joomla.Database'),
			$container->get('Package.Get')
		);
	}

	/**
	 * Get the Compiler Library Structure Builder
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Structure
	 * @since 3.2.0
	 */
	public function getStructure(Container $container): Structure
	{
		return new Structure(
			$container->get('Config'),
			$container->get('Registry'),
			$container->get('Event'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Utilities.Counter'),
			$container->get('Utilities.Paths'),
			$container->get('Utilities.Folder'),
			$container->get('Utilities.File')
		);
	}

	/**
	 * Get The Document Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Document
	 * @since 5.1.2
	 */
	public function getDocument(Container $container): Document
	{
		return new Document(
			$container->get('Config'),
			$container->get('Registry'),
			$container->get('Library.IncludeHelper'),
			$container->get('Utilities.Paths')
		);
	}

	/**
	 * Get The IncludeHelper Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  IncludeHelper
	 * @since 5.1.2
	 */
	public function getIncludeHelper(Container $container): IncludeHelper
	{
		return $container->get('Library.J' . $container->get('Config')->joomla_version . '.IncludeHelper');
	}

	/**
	 * Build the Joomla 3 library asset renderer.
	 *
	 * @param   Container  $container  The compiler services.
	 *
	 * @return  J3IncludeHelper
	 * @since   6.2.0
	 */
	public function getJ3IncludeHelper(Container $container): J3IncludeHelper
	{
		return new J3IncludeHelper();
	}

	/**
	 * Build the Joomla 4 library asset renderer.
	 *
	 * @param   Container  $container  The compiler services.
	 *
	 * @return  J4IncludeHelper
	 * @since   6.2.0
	 */
	public function getJ4IncludeHelper(Container $container): J4IncludeHelper
	{
		return new J4IncludeHelper();
	}

	/**
	 * Build the Joomla 5 library asset renderer.
	 *
	 * @param   Container  $container  The compiler services.
	 *
	 * @return  J5IncludeHelper
	 * @since   6.2.0
	 */
	public function getJ5IncludeHelper(Container $container): J5IncludeHelper
	{
		return new J5IncludeHelper();
	}

	/**
	 * Build the Joomla 6 library asset renderer.
	 *
	 * @param   Container  $container  The compiler services.
	 *
	 * @return  J6IncludeHelper
	 * @since   6.2.0
	 */
	public function getJ6IncludeHelper(Container $container): J6IncludeHelper
	{
		return new J6IncludeHelper();
	}
}

