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
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Controller\AllowAddInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Controller\AllowAdd as J6ControllerAllowAdd;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\Controller\AllowAdd as J5ControllerAllowAdd;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\Controller\AllowAdd as J4ControllerAllowAdd;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Controller\AllowAdd as J3ControllerAllowAdd;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Controller\AllowEditInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Controller\AllowEdit as J6ControllerAllowEdit;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\Controller\AllowEdit as J5ControllerAllowEdit;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\Controller\AllowEdit as J4ControllerAllowEdit;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Controller\AllowEdit as J3ControllerAllowEdit;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Controller\AllowEditViewsInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Controller\AllowEditViews as J6ControllerAllowEditViews;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\Controller\AllowEditViews as J5ControllerAllowEditViews;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\Controller\AllowEditViews as J4ControllerAllowEditViews;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Controller\AllowEditViews as J3ControllerAllowEditViews;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Controller\EximportMethodInterface as ControllerEximportMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Controller\EximportMethod as SharedControllerEximportMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Controller\EximportMethod as J3ControllerEximportMethod;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Controller\AjaxCasesInterface as ControllerAjaxCases;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Controller\AjaxCases as SharedControllerAjaxCases;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Controller\AjaxCases as J3ControllerAjaxCases;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Controller\CustomAdminDynamicButtonInterface as ControllerCustomAdminDynamicButton;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Controller\CustomAdminDynamicButton as SharedControllerCustomAdminDynamicButton;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Controller\CustomAdminDynamicButton as J3ControllerCustomAdminDynamicButton;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Controller\AjaxTasks as ControllerAjaxTasks;


/**
 * Architecture Controller Service Provider
 * 
 * @since 3.2.0
 */
class ArchitectureController implements ServiceProviderInterface
{
	/**
	 * Current Joomla Version Being Build
	 *
	 * @var     int
	 * @since 3.2.0
	 **/
	protected $targetVersion;

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
		$container->alias(AllowAddInterface::class, 'Architecture.Controller.AllowAdd')
			->share('Architecture.Controller.AllowAdd', [$this, 'getAllowAdd'], true);

		$container->alias(J6ControllerAllowAdd::class, 'Architecture.Controller.J6.AllowAdd')
			->share('Architecture.Controller.J6.AllowAdd', [$this, 'getJ6ControllerAllowAdd'], true);

		$container->alias(J5ControllerAllowAdd::class, 'Architecture.Controller.J5.AllowAdd')
			->share('Architecture.Controller.J5.AllowAdd', [$this, 'getJ5ControllerAllowAdd'], true);

		$container->alias(J4ControllerAllowAdd::class, 'Architecture.Controller.J4.AllowAdd')
			->share('Architecture.Controller.J4.AllowAdd', [$this, 'getJ4ControllerAllowAdd'], true);

		$container->alias(J3ControllerAllowAdd::class, 'Architecture.Controller.J3.AllowAdd')
			->share('Architecture.Controller.J3.AllowAdd', [$this, 'getJ3ControllerAllowAdd'], true);

		$container->alias(AllowEditInterface::class, 'Architecture.Controller.AllowEdit')
			->share('Architecture.Controller.AllowEdit', [$this, 'getAllowEdit'], true);

		$container->alias(J6ControllerAllowEdit::class, 'Architecture.Controller.J6.AllowEdit')
			->share('Architecture.Controller.J6.AllowEdit', [$this, 'getJ6ControllerAllowEdit'], true);

		$container->alias(J5ControllerAllowEdit::class, 'Architecture.Controller.J5.AllowEdit')
			->share('Architecture.Controller.J5.AllowEdit', [$this, 'getJ5ControllerAllowEdit'], true);

		$container->alias(J4ControllerAllowEdit::class, 'Architecture.Controller.J4.AllowEdit')
			->share('Architecture.Controller.J4.AllowEdit', [$this, 'getJ4ControllerAllowEdit'], true);

		$container->alias(J3ControllerAllowEdit::class, 'Architecture.Controller.J3.AllowEdit')
			->share('Architecture.Controller.J3.AllowEdit', [$this, 'getJ3ControllerAllowEdit'], true);

		$container->alias(AllowEditViewsInterface::class, 'Architecture.Controller.AllowEditViews')
			->share('Architecture.Controller.AllowEditViews', [$this, 'getAllowEditViews'], true);

		$container->alias(J6ControllerAllowEditViews::class, 'Architecture.Controller.J6.AllowEditViews')
			->share('Architecture.Controller.J6.AllowEditViews', [$this, 'getJ6ControllerAllowEditViews'], true);

		$container->alias(J5ControllerAllowEditViews::class, 'Architecture.Controller.J5.AllowEditViews')
			->share('Architecture.Controller.J5.AllowEditViews', [$this, 'getJ5ControllerAllowEditViews'], true);

		$container->alias(J4ControllerAllowEditViews::class, 'Architecture.Controller.J4.AllowEditViews')
			->share('Architecture.Controller.J4.AllowEditViews', [$this, 'getJ4ControllerAllowEditViews'], true);

		$container->alias(J3ControllerAllowEditViews::class, 'Architecture.Controller.J3.AllowEditViews')
			->share('Architecture.Controller.J3.AllowEditViews', [$this, 'getJ3ControllerAllowEditViews'], true);

		$container->alias(ControllerEximportMethod::class, 'Architecture.Controller.EximportMethod')
			->share('Architecture.Controller.EximportMethod', [$this, 'getControllerEximportMethod'], true);

		$container->alias(SharedControllerEximportMethod::class, 'Architecture.Controller.Shared.EximportMethod')
			->share('Architecture.Controller.Shared.EximportMethod', [$this, 'getSharedControllerEximportMethod'], true);

		$container->alias(J3ControllerEximportMethod::class, 'Architecture.Controller.J3.EximportMethod')
			->share('Architecture.Controller.J3.EximportMethod', [$this, 'getJ3ControllerEximportMethod'], true);

		$container->alias(ControllerAjaxCases::class, 'Architecture.Controller.AjaxCases')
			->share('Architecture.Controller.AjaxCases', [$this, 'getControllerAjaxCases'], true);

		$container->alias(SharedControllerAjaxCases::class, 'Architecture.Controller.Shared.AjaxCases')
			->share('Architecture.Controller.Shared.AjaxCases', [$this, 'getSharedControllerAjaxCases'], true);

		$container->alias(J3ControllerAjaxCases::class, 'Architecture.Controller.J3.AjaxCases')
			->share('Architecture.Controller.J3.AjaxCases', [$this, 'getJ3ControllerAjaxCases'], true);

		$container->alias(ControllerAjaxTasks::class, 'Architecture.Controller.AjaxTasks')
			->share('Architecture.Controller.AjaxTasks', [$this, 'getControllerAjaxTasks'], true);

		$container->alias(ControllerCustomAdminDynamicButton::class, 'Architecture.Controller.CustomAdminDynamicButton')
			->share('Architecture.Controller.CustomAdminDynamicButton', [$this, 'getControllerCustomAdminDynamicButton'], true);

		$container->alias(SharedControllerCustomAdminDynamicButton::class, 'Architecture.Controller.Shared.CustomAdminDynamicButton')
			->share('Architecture.Controller.Shared.CustomAdminDynamicButton', [$this, 'getSharedControllerCustomAdminDynamicButton'], true);

		$container->alias(J3ControllerCustomAdminDynamicButton::class, 'Architecture.Controller.J3.CustomAdminDynamicButton')
			->share('Architecture.Controller.J3.CustomAdminDynamicButton', [$this, 'getJ3ControllerCustomAdminDynamicButton'], true);
	}

	/**
	 * Get The AllowAddInterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AllowAddInterface
	 * @since 3.2.0
	 */
	public function getAllowAdd(Container $container): AllowAddInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.Controller.J' . $this->targetVersion . '.AllowAdd');
	}

	/**
	 * Get The AllowAdd Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ControllerAllowAdd
	 * @since   5.1.2
	 */
	public function getJ6ControllerAllowAdd(Container $container): J6ControllerAllowAdd
	{
		return new J6ControllerAllowAdd(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The AllowAdd Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5ControllerAllowAdd
	 * @since 3.2.0
	 */
	public function getJ5ControllerAllowAdd(Container $container): J5ControllerAllowAdd
	{
		return new J5ControllerAllowAdd(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The AllowAdd Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4ControllerAllowAdd
	 * @since 3.2.0
	 */
	public function getJ4ControllerAllowAdd(Container $container): J4ControllerAllowAdd
	{
		return new J4ControllerAllowAdd(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The AllowAdd Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ControllerAllowAdd
	 * @since 3.2.0
	 */
	public function getJ3ControllerAllowAdd(Container $container): J3ControllerAllowAdd
	{
		return new J3ControllerAllowAdd(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser')
		);
	}

		/**
	 * Get The AllowEditInterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AllowEditInterface
	 * @since 3.2.0
	 */
	public function getAllowEdit(Container $container): AllowEditInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.Controller.J' . $this->targetVersion . '.AllowEdit');
	}

	/**
	 * Get The AllowEdit Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ControllerAllowEdit
	 * @since   5.1.2
	 */
	public function getJ6ControllerAllowEdit(Container $container): J6ControllerAllowEdit
	{
		return new J6ControllerAllowEdit(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name')
		);
	}

	/**
	 * Get The AllowEdit Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5ControllerAllowEdit
	 * @since  3.2.0
	 */
	public function getJ5ControllerAllowEdit(Container $container): J5ControllerAllowEdit
	{
		return new J5ControllerAllowEdit(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name')
		);
	}

	/**
	 * Get The AllowEdit Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4ControllerAllowEdit
	 * @since  3.2.0
	 */
	public function getJ4ControllerAllowEdit(Container $container): J4ControllerAllowEdit
	{
		return new J4ControllerAllowEdit(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name')
		);
	}

	/**
	 * Get The AllowEdit Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ControllerAllowEdit
	 * @since 3.2.0
	 */
	public function getJ3ControllerAllowEdit(Container $container): J3ControllerAllowEdit
	{
		return new J3ControllerAllowEdit(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name')
		);
	}

	/**
	 * Get The AllowEditViewsInterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AllowEditViewsInterface
	 * @since 5.0.2
	 */
	public function getAllowEditViews(Container $container): AllowEditViewsInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.Controller.J' . $this->targetVersion . '.AllowEditViews');
	}

	/**
	 * Get The AllowEditViews Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ControllerAllowEdit
	 * @since  5.1.2
	 */
	public function getJ6ControllerAllowEditViews(Container $container): J6ControllerAllowEditViews
	{
		return new J6ControllerAllowEditViews(
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name')
		);
	}

	/**
	 * Get The AllowEditViews Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5ControllerAllowEdit
	 * @since 5.0.2
	 */
	public function getJ5ControllerAllowEditViews(Container $container): J5ControllerAllowEditViews
	{
		return new J5ControllerAllowEditViews(
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name')
		);
	}

	/**
	 * Get The AllowEditViews Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4ControllerAllowEditViews
	 * @since 5.0.2
	 */
	public function getJ4ControllerAllowEditViews(Container $container): J4ControllerAllowEditViews
	{
		return new J4ControllerAllowEditViews(
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name')
		);
	}

	/**
	 * Get The AllowEditViews Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ControllerAllowEditViews
	 * @since 5.0.2
	 */
	public function getJ3ControllerAllowEditViews(Container $container): J3ControllerAllowEditViews
	{
		return new J3ControllerAllowEditViews(
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name')
		);
	}

	/**
	 * Get The EximportMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ControllerEximportMethod
	 * @since   6.1.7
	 */
	public function getControllerEximportMethod(Container $container): ControllerEximportMethod
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 takes the current user from the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Controller.J3.EximportMethod');
		}

		return $container->get('Architecture.Controller.Shared.EximportMethod');
	}

	/**
	 * Get The EximportMethod Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedControllerEximportMethod
	 * @since   6.1.7
	 */
	public function getSharedControllerEximportMethod(Container $container): SharedControllerEximportMethod
	{
		return new SharedControllerEximportMethod(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Eximport.View'),
			$container->get('Compiler.Builder.Import.Custom.Scripts')
		);
	}

	/**
	 * Get The EximportMethod Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ControllerEximportMethod
	 * @since   6.1.7
	 */
	public function getJ3ControllerEximportMethod(Container $container): J3ControllerEximportMethod
	{
		return new J3ControllerEximportMethod(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Eximport.View'),
			$container->get('Compiler.Builder.Import.Custom.Scripts')
		);
	}
	/**
	 * Get The Controller AjaxCases Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ControllerAjaxCases
	 * @since   6.1.7
	 */
	public function getControllerAjaxCases(Container $container): ControllerAjaxCases
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 reaches its models without naming the side they belong to
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Controller.J3.AjaxCases');
		}

		return $container->get('Architecture.Controller.Shared.AjaxCases');
	}

	/**
	 * Get The Controller AjaxCases Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedControllerAjaxCases
	 * @since   6.1.7
	 */
	public function getSharedControllerAjaxCases(Container $container): SharedControllerAjaxCases
	{
		return new SharedControllerAjaxCases(
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The Joomla 3 Controller AjaxCases Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ControllerAjaxCases
	 * @since   6.1.7
	 */
	public function getJ3ControllerAjaxCases(Container $container): J3ControllerAjaxCases
	{
		return new J3ControllerAjaxCases(
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The Controller AjaxTasks Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ControllerAjaxTasks
	 * @since   6.1.7
	 */
	public function getControllerAjaxTasks(Container $container): ControllerAjaxTasks
	{
		return new ControllerAjaxTasks(
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The Controller CustomAdminDynamicButton Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ControllerCustomAdminDynamicButton
	 * @since   6.1.7
	 */
	public function getControllerCustomAdminDynamicButton(Container $container): ControllerCustomAdminDynamicButton
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 asks the factory for the user rather than the application
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Controller.J3.CustomAdminDynamicButton');
		}

		return $container->get('Architecture.Controller.Shared.CustomAdminDynamicButton');
	}

	/**
	 * Get The Controller CustomAdminDynamicButton Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedControllerCustomAdminDynamicButton
	 * @since   6.1.7
	 */
	public function getSharedControllerCustomAdminDynamicButton(Container $container): SharedControllerCustomAdminDynamicButton
	{
		return new SharedControllerCustomAdminDynamicButton(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Dynamic.Buttons')
		);
	}

	/**
	 * Get The Joomla 3 Controller CustomAdminDynamicButton Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ControllerCustomAdminDynamicButton
	 * @since   6.1.7
	 */
	public function getJ3ControllerCustomAdminDynamicButton(Container $container): J3ControllerCustomAdminDynamicButton
	{
		return new J3ControllerCustomAdminDynamicButton(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Dynamic.Buttons')
		);
	}
}

