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
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\ComHelperClass\CreateUserInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\ComHelperClass\CreateUser as J6CreateUser;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\ComHelperClass\CreateUser as J5CreateUser;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\ComHelperClass\CreateUser as J4CreateUser;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\ComHelperClass\CreateUser as J3CreateUser;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\ComHelperClass\ExcelMethodsInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\ComHelperClass\ExcelMethods as SharedExcelMethods;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\ComHelperClass\ExcelMethods as J3ExcelMethods;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Component\AssetsTableInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\AssetsTable as SharedAssetsTable;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Component\AssetsTable as J3AssetsTable;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Component\UninstallScriptInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\UninstallScript as SharedUninstallScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Component\UninstallScript as J3UninstallScript;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Component\ContentTypesInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\ContentTypes as SharedContentTypes;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Component\ContentTypes as J3ContentTypes;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\UninstallSql;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Component\InstallSqlInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\InstallSql as SharedInstallSql;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Component\InstallSql as J3InstallSql;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\ImageType;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\LicenseLock;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\Whmcs;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\ComHelperClass\CryptKey;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\ComHelperClass\UikitMethods;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Component\MoveFolderMethodInterface as ComponentMoveFolderMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\MoveFolderMethod as SharedComponentMoveFolderMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Component\MoveFolderMethod as J3ComponentMoveFolderMethod;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Component\PostInstallScriptInterface as ComponentPostInstallScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\PostInstallScript as SharedComponentPostInstallScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Component\PostInstallScript as J3ComponentPostInstallScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\PostUpdateScript as ComponentPostUpdateScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\ImportCustomScripts as ComponentImportCustomScripts;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\ComHelperClass\UserPermissionCheckAccess as ComHelperUserPermissionCheckAccess;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Component\MoveFolderScriptInterface as ComponentMoveFolderScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\MoveFolderScript as SharedComponentMoveFolderScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Component\MoveFolderScript as J3ComponentMoveFolderScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\Details as ComponentDetails;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\Assembly as ComponentAssembly;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\SiteStatics as ComponentSiteStatics;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\InstallScripts as ComponentInstallScripts;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\Finalise as ComponentFinalise;


/**
 * Architecture Component Helper Class Service Provider
 * 
 * @since 5.0.2
 */
class ArchitectureComponent implements ServiceProviderInterface
{
	/**
	 * Current Joomla Version Being Build
	 *
	 * @var     int
	 * @since 5.0.2
	 **/
	protected $targetVersion;

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 * @since 5.0.2
	 */
	public function register(Container $container)
	{
		$container->alias(CreateUserInterface::class, 'Architecture.ComHelperClass.CreateUser')
			->share('Architecture.ComHelperClass.CreateUser', [$this, 'getCreateUser'], true);

		$container->alias(J6CreateUser::class, 'Architecture.ComHelperClass.J6.CreateUser')
			->share('Architecture.ComHelperClass.J6.CreateUser', [$this, 'getJ6CreateUser'], true);

		$container->alias(J5CreateUser::class, 'Architecture.ComHelperClass.J5.CreateUser')
			->share('Architecture.ComHelperClass.J5.CreateUser', [$this, 'getJ5CreateUser'], true);

		$container->alias(J4CreateUser::class, 'Architecture.ComHelperClass.J4.CreateUser')
			->share('Architecture.ComHelperClass.J4.CreateUser', [$this, 'getJ4CreateUser'], true);

		$container->alias(J3CreateUser::class, 'Architecture.ComHelperClass.J3.CreateUser')
			->share('Architecture.ComHelperClass.J3.CreateUser', [$this, 'getJ3CreateUser'], true);

		$container->alias(ImageType::class, 'Architecture.Component.ImageType')
			->share('Architecture.Component.ImageType', [$this, 'getImageType'], true);

		$container->alias(LicenseLock::class, 'Architecture.Component.LicenseLock')
			->share('Architecture.Component.LicenseLock', [$this, 'getLicenseLock'], true);

		$container->alias(Whmcs::class, 'Architecture.Component.Whmcs')
			->share('Architecture.Component.Whmcs', [$this, 'getWhmcs'], true);

		$container->alias(CryptKey::class, 'Architecture.ComHelperClass.CryptKey')
			->share('Architecture.ComHelperClass.CryptKey', [$this, 'getCryptKey'], true);

		$container->alias(UikitMethods::class, 'Architecture.ComHelperClass.UikitMethods')
			->share('Architecture.ComHelperClass.UikitMethods', [$this, 'getUikitMethods'], true);

		$container->alias(ExcelMethodsInterface::class, 'Architecture.ComHelperClass.ExcelMethods')
			->share('Architecture.ComHelperClass.ExcelMethods', [$this, 'getExcelMethods'], true);

		$container->alias(SharedExcelMethods::class, 'Architecture.ComHelperClass.Shared.ExcelMethods')
			->share('Architecture.ComHelperClass.Shared.ExcelMethods', [$this, 'getSharedExcelMethods'], true);

		$container->alias(J3ExcelMethods::class, 'Architecture.ComHelperClass.J3.ExcelMethods')
			->share('Architecture.ComHelperClass.J3.ExcelMethods', [$this, 'getJ3ExcelMethods'], true);

		$container->alias(AssetsTableInterface::class, 'Architecture.Component.AssetsTable')
			->share('Architecture.Component.AssetsTable', [$this, 'getAssetsTable'], true);

		$container->alias(SharedAssetsTable::class, 'Architecture.Component.Shared.AssetsTable')
			->share('Architecture.Component.Shared.AssetsTable', [$this, 'getSharedAssetsTable'], true);

		$container->alias(J3AssetsTable::class, 'Architecture.Component.J3.AssetsTable')
			->share('Architecture.Component.J3.AssetsTable', [$this, 'getJ3AssetsTable'], true);

		$container->alias(UninstallScriptInterface::class, 'Architecture.Component.UninstallScript')
			->share('Architecture.Component.UninstallScript', [$this, 'getUninstallScript'], true);

		$container->alias(SharedUninstallScript::class, 'Architecture.Component.Shared.UninstallScript')
			->share('Architecture.Component.Shared.UninstallScript', [$this, 'getSharedUninstallScript'], true);

		$container->alias(J3UninstallScript::class, 'Architecture.Component.J3.UninstallScript')
			->share('Architecture.Component.J3.UninstallScript', [$this, 'getJ3UninstallScript'], true);

		$container->alias(ContentTypesInterface::class, 'Architecture.Component.ContentTypes')
			->share('Architecture.Component.ContentTypes', [$this, 'getContentTypes'], true);

		$container->alias(SharedContentTypes::class, 'Architecture.Component.Shared.ContentTypes')
			->share('Architecture.Component.Shared.ContentTypes', [$this, 'getSharedContentTypes'], true);

		$container->alias(J3ContentTypes::class, 'Architecture.Component.J3.ContentTypes')
			->share('Architecture.Component.J3.ContentTypes', [$this, 'getJ3ContentTypes'], true);

		$container->alias(UninstallSql::class, 'Architecture.Component.UninstallSql')
			->share('Architecture.Component.UninstallSql', [$this, 'getUninstallSql'], true);

		$container->alias(InstallSqlInterface::class, 'Architecture.Component.InstallSql')
			->share('Architecture.Component.InstallSql', [$this, 'getInstallSql'], true);

		$container->alias(SharedInstallSql::class, 'Architecture.Component.Shared.InstallSql')
			->share('Architecture.Component.Shared.InstallSql', [$this, 'getSharedInstallSql'], true);

		$container->alias(J3InstallSql::class, 'Architecture.Component.J3.InstallSql')
			->share('Architecture.Component.J3.InstallSql', [$this, 'getJ3InstallSql'], true);

		$container->alias(ComponentMoveFolderScript::class, 'Architecture.Component.MoveFolderScript')
			->share('Architecture.Component.MoveFolderScript', [$this, 'getComponentMoveFolderScript'], true);

		$container->alias(SharedComponentMoveFolderScript::class, 'Architecture.Component.Shared.MoveFolderScript')
			->share('Architecture.Component.Shared.MoveFolderScript', [$this, 'getSharedComponentMoveFolderScript'], true);

		$container->alias(J3ComponentMoveFolderScript::class, 'Architecture.Component.J3.MoveFolderScript')
			->share('Architecture.Component.J3.MoveFolderScript', [$this, 'getJ3ComponentMoveFolderScript'], true);

		$container->alias(ComponentDetails::class, 'Architecture.Component.Details')
			->share('Architecture.Component.Details', [$this, 'getComponentDetails'], true);

		$container->alias(ComponentAssembly::class, 'Architecture.Component.Assembly')
			->share('Architecture.Component.Assembly', [$this, 'getComponentAssembly'], true);

		$container->alias(ComponentSiteStatics::class, 'Architecture.Component.SiteStatics')
			->share('Architecture.Component.SiteStatics', [$this, 'getComponentSiteStatics'], true);

		$container->alias(ComponentInstallScripts::class, 'Architecture.Component.InstallScripts')
			->share('Architecture.Component.InstallScripts', [$this, 'getComponentInstallScripts'], true);

		$container->alias(ComponentFinalise::class, 'Architecture.Component.Finalise')
			->share('Architecture.Component.Finalise', [$this, 'getComponentFinalise'], true);

		$container->alias(ComponentMoveFolderMethod::class, 'Architecture.Component.MoveFolderMethod')
			->share('Architecture.Component.MoveFolderMethod', [$this, 'getComponentMoveFolderMethod'], true);

		$container->alias(SharedComponentMoveFolderMethod::class, 'Architecture.Component.Shared.MoveFolderMethod')
			->share('Architecture.Component.Shared.MoveFolderMethod', [$this, 'getSharedComponentMoveFolderMethod'], true);

		$container->alias(J3ComponentMoveFolderMethod::class, 'Architecture.Component.J3.MoveFolderMethod')
			->share('Architecture.Component.J3.MoveFolderMethod', [$this, 'getJ3ComponentMoveFolderMethod'], true);

		$container->alias(ComponentPostInstallScript::class, 'Architecture.Component.PostInstallScript')
			->share('Architecture.Component.PostInstallScript', [$this, 'getComponentPostInstallScript'], true);

		$container->alias(SharedComponentPostInstallScript::class, 'Architecture.Component.Shared.PostInstallScript')
			->share('Architecture.Component.Shared.PostInstallScript', [$this, 'getSharedComponentPostInstallScript'], true);

		$container->alias(J3ComponentPostInstallScript::class, 'Architecture.Component.J3.PostInstallScript')
			->share('Architecture.Component.J3.PostInstallScript', [$this, 'getJ3ComponentPostInstallScript'], true);

		$container->alias(ComponentPostUpdateScript::class, 'Architecture.Component.PostUpdateScript')
			->share('Architecture.Component.PostUpdateScript', [$this, 'getComponentPostUpdateScript'], true);

		$container->alias(ComponentImportCustomScripts::class, 'Architecture.Component.ImportCustomScripts')
			->share('Architecture.Component.ImportCustomScripts', [$this, 'getComponentImportCustomScripts'], true);

		$container->alias(ComHelperUserPermissionCheckAccess::class, 'Architecture.ComHelperClass.UserPermissionCheckAccess')
			->share('Architecture.ComHelperClass.UserPermissionCheckAccess', [$this, 'getComHelperUserPermissionCheckAccess'], true);
	}

	/**
	 * Get The CreateUserInterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CreateUserInterface
	 * @since   5.0.2
	 */
	public function getCreateUser(Container $container): CreateUserInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.ComHelperClass.J' . $this->targetVersion . '.CreateUser');
	}

	/**
	 * Get The CreateUser Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6CreateUser
	 * @since   5.1.2
	 */
	public function getJ6CreateUser(Container $container): J6CreateUser
	{
		return new J6CreateUser();
	}

	/**
	 * Get The CreateUser Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5CreateUser
	 * @since   5.0.2
	 */
	public function getJ5CreateUser(Container $container): J5CreateUser
	{
		return new J5CreateUser();
	}

	/**
	 * Get The CreateUser Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4CreateUser
	 * @since   5.0.2
	 */
	public function getJ4CreateUser(Container $container): J4CreateUser
	{
		return new J4CreateUser();
	}

	/**
	 * Get The CreateUser Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3CreateUser
	 * @since   5.0.2
	 */
	public function getJ3CreateUser(Container $container): J3CreateUser
	{
		return new J3CreateUser();
	}

	/**
	 * Get The ImageType Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ImageType
	 * @since   5.1.4
	 */
	public function getImageType(Container $container): ImageType
	{
		return new ImageType(
			$container->get('Utilities.Paths'),
			$container->get('Utilities.Image')
		);
	}

	/**
	 * Get The LicenseLock Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LicenseLock
	 * @since   6.1.7
	 */
	public function getLicenseLock(Container $container): LicenseLock
	{
		return new LicenseLock(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi')
		);
	}

	/**
	 * Get The Whmcs Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Whmcs
	 * @since   6.1.7
	 */
	public function getWhmcs(Container $container): Whmcs
	{
		return new Whmcs(
			$container->get('Component')
		);
	}

	/**
	 * Get The CryptKey Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CryptKey
	 * @since   6.1.7
	 */
	public function getCryptKey(Container $container): CryptKey
	{
		return new CryptKey(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Compiler.Builder.Model.Basic.Field'),
			$container->get('Compiler.Builder.Model.Medium.Field'),
			$container->get('Compiler.Builder.Model.Whmcs.Field'),
			$container->get('Utilities.Structure'),
			$container->get('Architecture.Component.Whmcs')
		);
	}

	/**
	 * Get The UikitMethods Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  UikitMethods
	 * @since   6.1.7
	 */
	public function getUikitMethods(Container $container): UikitMethods
	{
		return new UikitMethods(
			$container->get('Config')
		);
	}

	/**
	 * Get The ExcelMethods Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ExcelMethodsInterface
	 * @since   6.1.7
	 */
	public function getExcelMethods(Container $container): ExcelMethodsInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 resolves the active user the legacy way
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.ComHelperClass.J3.ExcelMethods');
		}

		return $container->get('Architecture.ComHelperClass.Shared.ExcelMethods');
	}

	/**
	 * Get The ComHelperClass ExcelMethods Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedExcelMethods
	 * @since   6.1.7
	 */
	public function getSharedExcelMethods(Container $container): SharedExcelMethods
	{
		return new SharedExcelMethods(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The ExcelMethods Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ExcelMethods
	 * @since   6.1.7
	 */
	public function getJ3ExcelMethods(Container $container): J3ExcelMethods
	{
		return new J3ExcelMethods(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The AssetsTable Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AssetsTableInterface
	 * @since   6.1.7
	 */
	public function getAssetsTable(Container $container): AssetsTableInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 carries the whole treatment in the generated script.php
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Component.J3.AssetsTable');
		}

		return $container->get('Architecture.Component.Shared.AssetsTable');
	}

	/**
	 * Get The Component AssetsTable Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAssetsTable
	 * @since   6.1.7
	 */
	public function getSharedAssetsTable(Container $container): SharedAssetsTable
	{
		return new SharedAssetsTable(
			$container->get('Config')
		);
	}

	/**
	 * Get The AssetsTable Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AssetsTable
	 * @since   6.1.7
	 */
	public function getJ3AssetsTable(Container $container): J3AssetsTable
	{
		return new J3AssetsTable(
			$container->get('Config')
		);
	}

	/**
	 * Get The UninstallScript Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  UninstallScriptInterface
	 * @since   6.1.7
	 */
	public function getUninstallScript(Container $container): UninstallScriptInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 removes its registered content types, fields and history
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Component.J3.UninstallScript');
		}

		return $container->get('Architecture.Component.Shared.UninstallScript');
	}

	/**
	 * Get The Component UninstallScript Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedUninstallScript
	 * @since   6.1.7
	 */
	public function getSharedUninstallScript(Container $container): SharedUninstallScript
	{
		return new SharedUninstallScript(
			$container->get('Customcode.Dispenser'),
			$container->get('Architecture.Component.AssetsTable')
		);
	}

	/**
	 * Get The UninstallScript Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3UninstallScript
	 * @since   6.1.7
	 */
	public function getJ3UninstallScript(Container $container): J3UninstallScript
	{
		return new J3UninstallScript(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Architecture.Component.AssetsTable')
		);
	}

	/**
	 * Get The ContentTypes Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ContentTypesInterface
	 * @since   6.1.7
	 */
	public function getContentTypes(Container $container): ContentTypesInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 inserts its own content type rows
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Component.J3.ContentTypes');
		}

		return $container->get('Architecture.Component.Shared.ContentTypes');
	}

	/**
	 * Get The Component ContentTypes Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedContentTypes
	 * @since   6.1.7
	 */
	public function getSharedContentTypes(Container $container): SharedContentTypes
	{
		return new SharedContentTypes(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Alias'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Custom.Field.Links'),
			$container->get('Compiler.Builder.Dynamic.Fields'),
			$container->get('Compiler.Builder.Hidden.Fields'),
			$container->get('Compiler.Builder.History'),
			$container->get('Compiler.Builder.Integer.Fields'),
			$container->get('Compiler.Builder.Main.Text.Field'),
			$container->get('Compiler.Builder.Meta.Data'),
			$container->get('Compiler.Builder.Tags'),
			$container->get('Compiler.Builder.Title'),
			$container->get('Compiler.Builder.Uninstall.Script.Context'),
			$container->get('Compiler.Builder.Uninstall.Script.Content')
		);
	}

	/**
	 * Get The ContentTypes Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ContentTypes
	 * @since   6.1.7
	 */
	public function getJ3ContentTypes(Container $container): J3ContentTypes
	{
		return new J3ContentTypes(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Alias'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Custom.Field.Links'),
			$container->get('Compiler.Builder.Dynamic.Fields'),
			$container->get('Compiler.Builder.Hidden.Fields'),
			$container->get('Compiler.Builder.History'),
			$container->get('Compiler.Builder.Integer.Fields'),
			$container->get('Compiler.Builder.Main.Text.Field'),
			$container->get('Compiler.Builder.Meta.Data'),
			$container->get('Compiler.Builder.Tags'),
			$container->get('Compiler.Builder.Title'),
			$container->get('Compiler.Builder.Uninstall.Script.Context'),
			$container->get('Compiler.Builder.Uninstall.Script.Content')
		);
	}

	/**
	 * Get The UninstallSql Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  UninstallSql
	 * @since   6.1.7
	 */
	public function getUninstallSql(Container $container): UninstallSql
	{
		return new UninstallSql(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Database.Uninstall')
		);
	}

	/**
	 * Get The InstallSql Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  InstallSqlInterface
	 * @since   6.1.7
	 */
	public function getInstallSql(Container $container): InstallSqlInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 keeps the zero-date defaults and carries no sql header
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Component.J3.InstallSql');
		}

		return $container->get('Architecture.Component.Shared.InstallSql');
	}

	/**
	 * Get The Component InstallSql Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedInstallSql
	 * @since   6.1.7
	 */
	public function getSharedInstallSql(Container $container): SharedInstallSql
	{
		return new SharedInstallSql(
			$container->get('Config'),
			$container->get('Registry'),
			$container->get('Placeholder'),
			$container->get('Customcode.Dispenser'),
			$container->get('Utilities.Counter'),
			$container->get('Compiler.Builder.Database.Tables'),
			$container->get('Compiler.Builder.Database.Uninstall'),
			$container->get('Compiler.Builder.Update.Mysql'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Component.Fields'),
			$container->get('Compiler.Builder.Meta.Data'),
			$container->get('Compiler.Builder.Database.Unique.Keys'),
			$container->get('Compiler.Builder.Database.Keys'),
			$container->get('Compiler.Builder.Mysql.Table.Setting')
		);
	}

	/**
	 * Get The InstallSql Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3InstallSql
	 * @since   6.1.7
	 */
	public function getJ3InstallSql(Container $container): J3InstallSql
	{
		return new J3InstallSql(
			$container->get('Config'),
			$container->get('Registry'),
			$container->get('Placeholder'),
			$container->get('Customcode.Dispenser'),
			$container->get('Utilities.Counter'),
			$container->get('Compiler.Builder.Database.Tables'),
			$container->get('Compiler.Builder.Database.Uninstall'),
			$container->get('Compiler.Builder.Update.Mysql'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Component.Fields'),
			$container->get('Compiler.Builder.Meta.Data'),
			$container->get('Compiler.Builder.Database.Unique.Keys'),
			$container->get('Compiler.Builder.Database.Keys'),
			$container->get('Compiler.Builder.Mysql.Table.Setting')
		);
	}
	/**
	 * Get The Component MoveFolderScript Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentMoveFolderScript
	 * @since   6.1.7
	 */
	public function getComponentMoveFolderScript(Container $container): ComponentMoveFolderScript
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only a Joomla 3 install script is handed the application and the parent
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Component.J3.MoveFolderScript');
		}

		return $container->get('Architecture.Component.Shared.MoveFolderScript');
	}

	/**
	 * Get The Component MoveFolderScript Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedComponentMoveFolderScript
	 * @since   6.1.7
	 */
	public function getSharedComponentMoveFolderScript(Container $container): SharedComponentMoveFolderScript
	{
		return new SharedComponentMoveFolderScript(
			$container->get('Registry')
		);
	}

	/**
	 * Get The Joomla 3 Component MoveFolderScript Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ComponentMoveFolderScript
	 * @since   6.1.7
	 */
	public function getJ3ComponentMoveFolderScript(Container $container): J3ComponentMoveFolderScript
	{
		return new J3ComponentMoveFolderScript(
			$container->get('Registry')
		);
	}

	/**
	 * Get The Component Details Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentDetails
	 * @since   6.1.7
	 */
	public function getComponentDetails(Container $container): ComponentDetails
	{
		return new ComponentDetails(
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Placeholder'),
			$container->get('Component.Placeholder'),
			$container->get('Component'),
			$container->get('Config'),
			$container->get('Utilities.Counter'),
			$container->get('Customcode.Dispenser'),
			$container->get('Architecture.Component.ImageType'),
			$container->get('Compiler.Creator.Access.Sections'),
			$container->get('Compiler.Creator.Config.Fieldsets'),
			$container->get('Architecture.ComHelperClass.CreateUser'),
			$container->get('Compiler.Creator.Helper'),
			$container->get('Compiler.Creator.Email.Helper')
		);
	}

	/**
	 * Get The Component Assembly Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentAssembly
	 * @since   6.1.7
	 */
	public function getComponentAssembly(Container $container): ComponentAssembly
	{
		return new ComponentAssembly(
			$container->get('Config'),
			$container->get('Header'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Architecture.Component.LicenseLock'),
			$container->get('Registry'),
			$container->get('Placeholder'),
			$container->get('Architecture.Controller.AjaxCases'),
			$container->get('Architecture.Dashboard.Icons'),
			$container->get('Architecture.ComHelperClass.CryptKey'),
			$container->get('Architecture.ComHelperClass.ExcelMethods'),
			$container->get('Architecture.Component.InstallSql'),
			$container->get('Architecture.Component.UninstallSql'),
			$container->get('Architecture.Menu.MainMenus'),
			$container->get('Architecture.Menu.SubMenus'),
			$container->get('Extension.VersionUpdate'),
			$container->get('Architecture.Dashboard.ModelMethods'),
			$container->get('Architecture.Model.AjaxMethods'),
			$container->get('Architecture.Controller.AjaxTasks'),
			$container->get('Compiler.Builder.Custom.Admin.Added'),
			$container->get('Compiler.Builder.Contributors'),
			$container->get('Compiler.Builder.Permission.Dashboard'),
			$container->get('Architecture.Controller.AllowEditViews'),
			$container->get('Architecture.Dashboard.View'),
			$container->get('Utilities.Structure')
		);
	}

	/**
	 * Get The Component SiteStatics Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentSiteStatics
	 * @since   6.1.7
	 */
	public function getComponentSiteStatics(Container $container): ComponentSiteStatics
	{
		return new ComponentSiteStatics(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Component'),
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The Component InstallScripts Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentInstallScripts
	 * @since   6.1.7
	 */
	public function getComponentInstallScripts(Container $container): ComponentInstallScripts
	{
		return new ComponentInstallScripts(
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Uninstall.Script.Context'),
			$container->get('Compiler.Builder.Uninstall.Script.Fields'),
			$container->get('Architecture.Component.PostInstallScript'),
			$container->get('Architecture.Component.PostUpdateScript'),
			$container->get('Architecture.Component.UninstallScript'),
			$container->get('Architecture.Component.MoveFolderScript'),
			$container->get('Architecture.Component.MoveFolderMethod'),
			$container->get('Architecture.ComHelperClass.UikitMethods')
		);
	}

	/**
	 * Get The Component Finalise Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentFinalise
	 * @since   6.1.7
	 */
	public function getComponentFinalise(Container $container): ComponentFinalise
	{
		return new ComponentFinalise(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Component.Fields'),
			$container->get('Compiler.Creator.Router'),
			$container->get('Power.Autoloader'),
			$container->get('Joomlamodule.Infusion'),
			$container->get('Joomlaplugin.Infusion')
		);
	}

	/**
	 * Get The Component MoveFolderMethod Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentMoveFolderMethod
	 * @since   6.1.7
	 */
	public function getComponentMoveFolderMethod(Container $container): ComponentMoveFolderMethod
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only a Joomla 3 install script is handed the application and the parent
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Component.J3.MoveFolderMethod');
		}

		return $container->get('Architecture.Component.Shared.MoveFolderMethod');
	}

	/**
	 * Get The Component MoveFolderMethod Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedComponentMoveFolderMethod
	 * @since   6.1.7
	 */
	public function getSharedComponentMoveFolderMethod(Container $container): SharedComponentMoveFolderMethod
	{
		return new SharedComponentMoveFolderMethod(
			$container->get('Config'),
			$container->get('Registry')
		);
	}

	/**
	 * Get The Joomla 3 Component MoveFolderMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ComponentMoveFolderMethod
	 * @since   6.1.7
	 */
	public function getJ3ComponentMoveFolderMethod(Container $container): J3ComponentMoveFolderMethod
	{
		return new J3ComponentMoveFolderMethod(
			$container->get('Config'),
			$container->get('Registry')
		);
	}

	/**
	 * Get The Component PostInstallScript Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentPostInstallScript
	 * @since   6.1.7
	 */
	public function getComponentPostInstallScript(Container $container): ComponentPostInstallScript
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only a Joomla 3 install script writes the extension permissions itself
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Component.J3.PostInstallScript');
		}

		return $container->get('Architecture.Component.Shared.PostInstallScript');
	}

	/**
	 * Get The Component PostInstallScript Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedComponentPostInstallScript
	 * @since   6.1.7
	 */
	public function getSharedComponentPostInstallScript(Container $container): SharedComponentPostInstallScript
	{
		return new SharedComponentPostInstallScript(
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Assets.Rules'),
			$container->get('Compiler.Builder.Extensions.Params'),
			$container->get('Architecture.Component.ImageType'),
			$container->get('Architecture.Component.ContentTypes'),
			$container->get('Architecture.Component.AssetsTable')
		);
	}

	/**
	 * Get The Joomla 3 Component PostInstallScript Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ComponentPostInstallScript
	 * @since   6.1.7
	 */
	public function getJ3ComponentPostInstallScript(Container $container): J3ComponentPostInstallScript
	{
		return new J3ComponentPostInstallScript(
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Assets.Rules'),
			$container->get('Compiler.Builder.Extensions.Params'),
			$container->get('Architecture.Component.ImageType'),
			$container->get('Architecture.Component.ContentTypes'),
			$container->get('Architecture.Component.AssetsTable')
		);
	}

	/**
	 * Get The Component PostUpdateScript Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentPostUpdateScript
	 * @since   6.1.7
	 */
	public function getComponentPostUpdateScript(Container $container): ComponentPostUpdateScript
	{
		return new ComponentPostUpdateScript(
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Component'),
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Architecture.Component.ImageType'),
			$container->get('Architecture.Component.ContentTypes')
		);
	}

	/**
	 * Get The Component ImportCustomScripts Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentImportCustomScripts
	 * @since   6.1.7
	 */
	public function getComponentImportCustomScripts(Container $container): ComponentImportCustomScripts
	{
		return new ComponentImportCustomScripts(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Utilities.Structure'),
			$container->get('Header'),
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The ComHelperClass UserPermissionCheckAccess Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComHelperUserPermissionCheckAccess
	 * @since   6.1.7
	 */
	public function getComHelperUserPermissionCheckAccess(Container $container): ComHelperUserPermissionCheckAccess
	{
		return new ComHelperUserPermissionCheckAccess(
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Config'),
			$container->get('Language')
		);
	}
}

