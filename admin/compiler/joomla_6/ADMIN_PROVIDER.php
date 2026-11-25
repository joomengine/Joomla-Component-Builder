<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###

###POWER_AUTOLOADER###

// (soon) use Joomla\CMS\Association\AssociationExtensionInterface;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Extension\###Component###Component;
// (soon) use ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Helper\AssociationsHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * The ###NAMESPACEPREFIX### ###Component### service provider.
 *
 * @since  4.0.0
 */
return new class () implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		// (soon) $container->set(AssociationExtensionInterface::class, new AssociationsHelper());

		$container->registerServiceProvider(new CategoryFactory('\\###NAMESPACEPREFIX###\\Component\\###ComponentNamespace###'));
		$container->registerServiceProvider(new MVCFactory('\\###NAMESPACEPREFIX###\\Component\\###ComponentNamespace###'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\###NAMESPACEPREFIX###\\Component\\###ComponentNamespace###'));
		$container->registerServiceProvider(new RouterFactory('\\###NAMESPACEPREFIX###\\Component\\###ComponentNamespace###'));

		$container->set(
			ComponentInterface::class,
			function (Container $container) {
				$component = new ###Component###Component($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setRegistry($container->get(Registry::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
				// (soon) $component->setAssociationExtension($container->get(AssociationExtensionInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));

				return $component;
			}
		);
	}
};
