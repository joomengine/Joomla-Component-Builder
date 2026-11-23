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
		return new IncludeHelper();
	}
}

