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
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Plugin\ExtensionInterface as Extension;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Plugin\Extension as J6Extension;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Plugin\Privacy as J6Privacy;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\Plugin\Extension as J5Extension;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\Plugin\Extension as J4Extension;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Plugin\Extension as J3Extension;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Plugin\ProviderInterface as Provider;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Plugin\Provider as J6Provider;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\Plugin\Provider as J5Provider;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\Plugin\Provider as J4Provider;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Plugin\Provider as J3Provider;
use VDM\Joomla\Componentbuilder\Interfaces\Architecture\Plugin\MainXMLInterface as MainXML;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Plugin\MainXML as J6MainXML;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\Plugin\MainXML as J5MainXML;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\Plugin\MainXML as J4MainXML;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Plugin\MainXML as J3MainXML;


/**
 * Architecture Plugin Service Provider
 * 
 * @since 5.0.2
 */
class ArchitecturePlugin implements ServiceProviderInterface
{
	/**
	 * Current Joomla Version Being Build
	 *
	 * @var      int
	 * @since  5.0.2
	 **/
	protected int $targetVersion;

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 * @since   5.0.2
	 */
	public function register(Container $container)
	{
		$container->alias(Extension::class, 'Architecture.Plugin.Extension')
			->share('Architecture.Plugin.Extension', [$this, 'getExtension'], true);

		$container->alias(J6Privacy::class, 'Architecture.Plugin.J6.Privacy')
			->share('Architecture.Plugin.J6.Privacy', [$this, 'getJ6Privacy'], true);

		$container->alias(J6Extension::class, 'Architecture.Plugin.J6.Extension')
			->share('Architecture.Plugin.J6.Extension', [$this, 'getJ6Extension'], true);

		$container->alias(J5Extension::class, 'Architecture.Plugin.J5.Extension')
			->share('Architecture.Plugin.J5.Extension', [$this, 'getJ5Extension'], true);

		$container->alias(J4Extension::class, 'Architecture.Plugin.J4.Extension')
			->share('Architecture.Plugin.J4.Extension', [$this, 'getJ4Extension'], true);

		$container->alias(J3Extension::class, 'Architecture.Plugin.J3.Extension')
			->share('Architecture.Plugin.J3.Extension', [$this, 'getJ3Extension'], true);

		$container->alias(Provider::class, 'Architecture.Plugin.Provider')
			->share('Architecture.Plugin.Provider', [$this, 'getProvider'], true);

		$container->alias(J6Provider::class, 'Architecture.Plugin.J6.Provider')
			->share('Architecture.Plugin.J6.Provider', [$this, 'getJ6Provider'], true);

		$container->alias(J5Provider::class, 'Architecture.Plugin.J5.Provider')
			->share('Architecture.Plugin.J5.Provider', [$this, 'getJ5Provider'], true);

		$container->alias(J4Provider::class, 'Architecture.Plugin.J4.Provider')
			->share('Architecture.Plugin.J4.Provider', [$this, 'getJ4Provider'], true);

		$container->alias(J3Provider::class, 'Architecture.Plugin.J3.Provider')
			->share('Architecture.Plugin.J3.Provider', [$this, 'getJ3Provider'], true);

		$container->alias(MainXML::class, 'Architecture.Plugin.MainXML')
			->share('Architecture.Plugin.MainXML', [$this, 'getMainXML'], true);

		$container->alias(J6MainXML::class, 'Architecture.Plugin.J6.MainXML')
			->share('Architecture.Plugin.J6.MainXML', [$this, 'getJ6MainXML'], true);

		$container->alias(J5MainXML::class, 'Architecture.Plugin.J5.MainXML')
			->share('Architecture.Plugin.J5.MainXML', [$this, 'getJ5MainXML'], true);

		$container->alias(J4MainXML::class, 'Architecture.Plugin.J4.MainXML')
			->share('Architecture.Plugin.J4.MainXML', [$this, 'getJ4MainXML'], true);

		$container->alias(J3MainXML::class, 'Architecture.Plugin.J3.MainXML')
			->share('Architecture.Plugin.J3.MainXML', [$this, 'getJ3MainXML'], true);
	}

	/**
	 * Get The Extension Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Extension
	 * @since   5.0.2
	 */
	public function getExtension(Container $container): Extension
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.Plugin.J' . $this->targetVersion . '.Extension');
	}

	/**
	 * Get The Extension Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6Extension
	 * @since   5.1.2
	 */
	public function getJ6Extension(Container $container): J6Extension
	{
		return new J6Extension(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Power.Parser'),
			$container->get('Joomla.Power'),
			$container->get('Architecture.Plugin.J6.Privacy')
		);
	}

	/**
	 * Get the bounded native privacy definition renderer.
	 *
	 * @param Container $container The DI container.
	 *
	 * @return J6Privacy
	 * @since 6.2.0
	 */
	public function getJ6Privacy(Container $container): J6Privacy
	{
		return new J6Privacy();
	}

	/**
	 * Get The Extension Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5Extension
	 * @since   5.0.2
	 */
	public function getJ5Extension(Container $container): J5Extension
	{
		return new J5Extension(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Power.Parser')
		);
	}

	/**
	 * Get The Extension Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4Extension
	 * @since  5.0.2
	 */
	public function getJ4Extension(Container $container): J4Extension
	{
		return new J4Extension(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Power.Parser')
		);
	}

	/**
	 * Get The Extension Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3Extension
	 * @since   5.0.2
	 */
	public function getJ3Extension(Container $container): J3Extension
	{
		return new J3Extension(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The ProviderInterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Provider
	 * @since 5.0.2
	 */
	public function getProvider(Container $container): Provider
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.Plugin.J' . $this->targetVersion . '.Provider');
	}

	/**
	 * Get The Provider Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6Provider
	 * @since   5.1.2
	 */
	public function getJ6Provider(Container $container): J6Provider
	{
		return new J6Provider(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The Provider Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5Provider
	 * @since 5.0.2
	 */
	public function getJ5Provider(Container $container): J5Provider
	{
		return new J5Provider(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The Provider Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4Provider
	 * @since 5.0.2
	 */
	public function getJ4Provider(Container $container): J4Provider
	{
		return new J4Provider(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The Provider Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3Provider
	 * @since 5.0.2
	 */
	public function getJ3Provider(Container $container): J3Provider
	{
		return new J3Provider(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The MainXML Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MainXML
	 * @since   5.0.2
	 */
	public function getMainXML(Container $container): MainXML
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.Plugin.J' . $this->targetVersion . '.MainXML');
	}

	/**
	 * Get The MainXML Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6MainXML
	 * @since   5.1.2
	 */
	public function getJ6MainXML(Container $container): J6MainXML
	{
		return new J6MainXML(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Language.Set'),
			$container->get('Language.Purge'),
			$container->get('Language.Translation'),
			$container->get('Language.Multilingual'),
			$container->get('Event'),
			$container->get('Compiler.Creator.Fieldset.Extension'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Languages'),
			$container->get('Compiler.Builder.Multilingual'),
			$container->get('Utilities.Counter'),
			$container->get('Utilities.File')
		);
	}

	/**
	 * Get The MainXML Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5MainXML
	 * @since   5.0.2
	 */
	public function getJ5MainXML(Container $container): J5MainXML
	{
		return new J5MainXML(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Language.Set'),
			$container->get('Language.Purge'),
			$container->get('Language.Translation'),
			$container->get('Language.Multilingual'),
			$container->get('Event'),
			$container->get('Compiler.Creator.Fieldset.Extension'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Languages'),
			$container->get('Compiler.Builder.Multilingual'),
			$container->get('Utilities.Counter'),
			$container->get('Utilities.File')
		);
	}

	/**
	 * Get The MainXML Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4MainXML
	 * @since   5.0.2
	 */
	public function getJ4MainXML(Container $container): J4MainXML
	{
		return new J4MainXML(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Language.Set'),
			$container->get('Language.Purge'),
			$container->get('Language.Translation'),
			$container->get('Language.Multilingual'),
			$container->get('Event'),
			$container->get('Compiler.Creator.Fieldset.Extension'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Languages'),
			$container->get('Compiler.Builder.Multilingual'),
			$container->get('Utilities.Counter'),
			$container->get('Utilities.File')
		);
	}

	/**
	 * Get The MainXML Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3MainXML
	 * @since   5.0.2
	 */
	public function getJ3MainXML(Container $container): J3MainXML
	{
		return new J3MainXML(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Language.Set'),
			$container->get('Language.Purge'),
			$container->get('Language.Translation'),
			$container->get('Language.Multilingual'),
			$container->get('Event'),
			$container->get('Compiler.Creator.Fieldset.Extension'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Languages'),
			$container->get('Compiler.Builder.Multilingual'),
			$container->get('Utilities.Counter'),
			$container->get('Utilities.File')
		);
	}
}

