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
use VDM\Joomla\Componentbuilder\Compiler\Creator\AccessSections;
use VDM\Joomla\Componentbuilder\Compiler\Creator\AccessSectionsCategory;
use VDM\Joomla\Componentbuilder\Compiler\Creator\AccessSectionsJoomlaFields;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Builders;
use VDM\Joomla\Componentbuilder\Compiler\Creator\CustomFieldTypeFile;
use VDM\Joomla\Componentbuilder\Compiler\Creator\CustomButtonPermissions;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsets;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsetsCustomfield;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsetsEmailHelper;
use VDM\Joomla\Componentbuilder\Compiler\Creator\JoomlaThree\ConfigFieldsetsEmailHelper as J3ConfigFieldsetsEmailHelper;
use VDM\Joomla\Componentbuilder\Compiler\Creator\JoomlaFour\ConfigFieldsetsEmailHelper as J4ConfigFieldsetsEmailHelper;
use VDM\Joomla\Componentbuilder\Compiler\Creator\JoomlaFive\ConfigFieldsetsEmailHelper as J5ConfigFieldsetsEmailHelper;
use VDM\Joomla\Componentbuilder\Compiler\Creator\JoomlaSix\ConfigFieldsetsEmailHelper as J6ConfigFieldsetsEmailHelper;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsetsEncryption;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsetsGlobal;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsetsGooglechart;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsetsGroupControl;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsetsSiteControl;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsetsUikit;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Layout;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Permission;
use VDM\Joomla\Componentbuilder\Compiler\Creator\SiteFieldData;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Request;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Router;
use VDM\Joomla\Componentbuilder\Compiler\Creator\RouterConstructorDefault;
use VDM\Joomla\Componentbuilder\Compiler\Creator\RouterConstructorManual;
use VDM\Joomla\Componentbuilder\Compiler\Creator\RouterMethodsDefault;
use VDM\Joomla\Componentbuilder\Compiler\Creator\JoomlaSix\RouterMethodsDefault as J6RouterMethodsDefault;
use VDM\Joomla\Componentbuilder\Compiler\Creator\RouterMethodsManual;
use VDM\Joomla\Componentbuilder\Compiler\Creator\JoomlaSix\RouterMethodsManual as J6RouterMethodsManual;
use VDM\Joomla\Componentbuilder\Compiler\Creator\FieldsetExtension;
use VDM\Joomla\Componentbuilder\Compiler\Creator\FieldsetString;
use VDM\Joomla\Componentbuilder\Compiler\Creator\FieldsetXML;
use VDM\Joomla\Componentbuilder\Compiler\Creator\FieldsetDynamic;
use VDM\Joomla\Componentbuilder\Compiler\Creator\FieldXML;
use VDM\Joomla\Componentbuilder\Compiler\Creator\FieldString;
use VDM\Joomla\Componentbuilder\Compiler\Creator\FieldDynamic;
use VDM\Joomla\Componentbuilder\Compiler\Creator\FieldAsString;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Creator\Fieldtypeinterface as FieldType;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Creator\Fieldsetinterface as Fieldset;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Helper;
use VDM\Joomla\Componentbuilder\Compiler\Creator\JoomlaSix\Helper as J6Helper;
use VDM\Joomla\Componentbuilder\Compiler\Creator\EmailHelper;


/**
 * Creator Service Provider
 * 
 * @since 3.2.0
 */
class Creator implements ServiceProviderInterface
{
	/**
	 * Compile target selected for this build scope.
	 *
	 * @var   int
	 * @since 6.2.0
	 */
	protected int $targetVersion;

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
		$container->alias(AccessSections::class, 'Compiler.Creator.Access.Sections')
			->share('Compiler.Creator.Access.Sections', [$this, 'getAccessSections'], true);

		$container->alias(AccessSectionsCategory::class, 'Compiler.Creator.Access.Sections.Category')
			->share('Compiler.Creator.Access.Sections.Category', [$this, 'getAccessSectionsCategory'], true);

		$container->alias(AccessSectionsJoomlaFields::class, 'Compiler.Creator.Access.Sections.Joomla.Fields')
			->share('Compiler.Creator.Access.Sections.Joomla.Fields', [$this, 'getAccessSectionsJoomlaFields'], true);

		$container->alias(Builders::class, 'Compiler.Creator.Builders')
			->share('Compiler.Creator.Builders', [$this, 'getBuilders'], true);

		$container->alias(CustomFieldTypeFile::class, 'Compiler.Creator.Custom.Field.Type.File')
			->share('Compiler.Creator.Custom.Field.Type.File', [$this, 'getCustomFieldTypeFile'], true);

		$container->alias(CustomButtonPermissions::class, 'Compiler.Creator.Custom.Button.Permissions')
			->share('Compiler.Creator.Custom.Button.Permissions', [$this, 'getCustomButtonPermissions'], true);

		$container->alias(FieldsetExtension::class, 'Compiler.Creator.Fieldset.Extension')
			->share('Compiler.Creator.Fieldset.Extension', [$this, 'getFieldsetExtension'], true);

		$container->alias(ConfigFieldsets::class, 'Compiler.Creator.Config.Fieldsets')
			->share('Compiler.Creator.Config.Fieldsets', [$this, 'getConfigFieldsets'], true);

		$container->alias(ConfigFieldsetsCustomfield::class, 'Compiler.Creator.Config.Fieldsets.Customfield')
			->share('Compiler.Creator.Config.Fieldsets.Customfield', [$this, 'getConfigFieldsetsCustomfield'], true);

		$container->alias(ConfigFieldsetsEmailHelper::class, 'Compiler.Creator.Config.Fieldsets.Email.Helper')
			->share('Compiler.Creator.Config.Fieldsets.Email.Helper', [$this, 'getConfigFieldsetsEmailHelper'], true);

		$container->alias(J3ConfigFieldsetsEmailHelper::class, 'Compiler.Creator.Config.Fieldsets.J3.Email.Helper')
			->share('Compiler.Creator.Config.Fieldsets.J3.Email.Helper', [$this, 'getJ3ConfigFieldsetsEmailHelper'], true);

		$container->alias(J4ConfigFieldsetsEmailHelper::class, 'Compiler.Creator.Config.Fieldsets.J4.Email.Helper')
			->share('Compiler.Creator.Config.Fieldsets.J4.Email.Helper', [$this, 'getJ4ConfigFieldsetsEmailHelper'], true);

		$container->alias(J5ConfigFieldsetsEmailHelper::class, 'Compiler.Creator.Config.Fieldsets.J5.Email.Helper')
			->share('Compiler.Creator.Config.Fieldsets.J5.Email.Helper', [$this, 'getJ5ConfigFieldsetsEmailHelper'], true);

		$container->alias(J6ConfigFieldsetsEmailHelper::class, 'Compiler.Creator.Config.Fieldsets.J6.Email.Helper')
			->share('Compiler.Creator.Config.Fieldsets.J6.Email.Helper', [$this, 'getJ6ConfigFieldsetsEmailHelper'], true);

		$container->alias(ConfigFieldsetsEncryption::class, 'Compiler.Creator.Config.Fieldsets.Encryption')
			->share('Compiler.Creator.Config.Fieldsets.Encryption', [$this, 'getConfigFieldsetsEncryption'], true);

		$container->alias(ConfigFieldsetsGlobal::class, 'Compiler.Creator.Config.Fieldsets.Global')
			->share('Compiler.Creator.Config.Fieldsets.Global', [$this, 'getConfigFieldsetsGlobal'], true);

		$container->alias(ConfigFieldsetsGooglechart::class, 'Compiler.Creator.Config.Fieldsets.Googlechart')
			->share('Compiler.Creator.Config.Fieldsets.Googlechart', [$this, 'getConfigFieldsetsGooglechart'], true);

		$container->alias(ConfigFieldsetsGroupControl::class, 'Compiler.Creator.Config.Fieldsets.Group.Control')
			->share('Compiler.Creator.Config.Fieldsets.Group.Control', [$this, 'getConfigFieldsetsGroupControl'], true);

		$container->alias(ConfigFieldsetsSiteControl::class, 'Compiler.Creator.Config.Fieldsets.Site.Control')
			->share('Compiler.Creator.Config.Fieldsets.Site.Control', [$this, 'getConfigFieldsetsSiteControl'], true);

		$container->alias(ConfigFieldsetsUikit::class, 'Compiler.Creator.Config.Fieldsets.Uikit')
			->share('Compiler.Creator.Config.Fieldsets.Uikit', [$this, 'getConfigFieldsetsUikit'], true);

		$container->alias(Layout::class, 'Compiler.Creator.Layout')
			->share('Compiler.Creator.Layout', [$this, 'getLayout'], true);

		$container->alias(Permission::class, 'Compiler.Creator.Permission')
			->share('Compiler.Creator.Permission', [$this, 'getPermission'], true);

		$container->alias(SiteFieldData::class, 'Compiler.Creator.Site.Field.Data')
			->share('Compiler.Creator.Site.Field.Data', [$this, 'getSiteFieldData'], true);

		$container->alias(Request::class, 'Compiler.Creator.Request')
			->share('Compiler.Creator.Request', [$this, 'getRequest'], true);

		$container->alias(Router::class, 'Compiler.Creator.Router')
			->share('Compiler.Creator.Router', [$this, 'getRouter'], true);

		$container->alias(RouterConstructorDefault::class, 'Compiler.Creator.Router.Constructor.Default')
			->share('Compiler.Creator.Router.Constructor.Default', [$this, 'getRouterConstructorDefault'], true);

		$container->alias(RouterConstructorManual::class, 'Compiler.Creator.Router.Constructor.Manual')
			->share('Compiler.Creator.Router.Constructor.Manual', [$this, 'getRouterConstructorManual'], true);

		$container->alias(RouterMethodsDefault::class, 'Compiler.Creator.Router.Methods.Default')
			->share('Compiler.Creator.Router.Methods.Default', [$this, 'getRouterMethodsDefault'], true);

		$container->share('Compiler.Creator.Router.Methods.Shared.Default', [$this, 'getSharedRouterMethodsDefault'], true);

		$container->alias(J6RouterMethodsDefault::class, 'Compiler.Creator.Router.Methods.J6.Default')
			->share('Compiler.Creator.Router.Methods.J6.Default', [$this, 'getJ6RouterMethodsDefault'], true);

		$container->alias(RouterMethodsManual::class, 'Compiler.Creator.Router.Methods.Manual')
			->share('Compiler.Creator.Router.Methods.Manual', [$this, 'getRouterMethodsManual'], true);

		$container->share('Compiler.Creator.Router.Methods.Shared.Manual', [$this, 'getSharedRouterMethodsManual'], true);

		$container->alias(J6RouterMethodsManual::class, 'Compiler.Creator.Router.Methods.J6.Manual')
			->share('Compiler.Creator.Router.Methods.J6.Manual', [$this, 'getJ6RouterMethodsManual'], true);

		$container->alias(FieldsetString::class, 'Compiler.Creator.Fieldset.String')
			->share('Compiler.Creator.Fieldset.String', [$this, 'getFieldsetString'], true);

		$container->alias(FieldsetXML::class, 'Compiler.Creator.Fieldset.XML')
			->share('Compiler.Creator.Fieldset.XML', [$this, 'getFieldsetXML'], true);

		$container->alias(FieldsetDynamic::class, 'Compiler.Creator.Fieldset.Dynamic')
			->share('Compiler.Creator.Fieldset.Dynamic', [$this, 'getFieldsetDynamic'], true);

		$container->alias(FieldXML::class, 'Compiler.Creator.Field.XML')
			->share('Compiler.Creator.Field.XML', [$this, 'getFieldXML'], true);

		$container->alias(FieldString::class, 'Compiler.Creator.Field.String')
			->share('Compiler.Creator.Field.String', [$this, 'getFieldString'], true);

		$container->alias(FieldDynamic::class, 'Compiler.Creator.Field.Dynamic')
			->share('Compiler.Creator.Field.Dynamic', [$this, 'getFieldDynamic'], true);

		$container->alias(FieldAsString::class, 'Compiler.Creator.Field.As.String')
			->share('Compiler.Creator.Field.As.String', [$this, 'getFieldAsString'], true);

		$container->alias(FieldType::class, 'Compiler.Creator.Field.Type')
			->share('Compiler.Creator.Field.Type', [$this, 'getFieldType'], true);

		$container->alias(Fieldset::class, 'Compiler.Creator.Fieldset')
			->share('Compiler.Creator.Fieldset', [$this, 'getFieldset'], true);

		$container->alias(Helper::class, 'Compiler.Creator.Helper')
			->share('Compiler.Creator.Helper', [$this, 'getHelper'], true);

		$container->share('Compiler.Creator.Shared.Helper', [$this, 'getSharedHelper'], true);

		$container->alias(J6Helper::class, 'Compiler.Creator.J6.Helper')
			->share('Compiler.Creator.J6.Helper', [$this, 'getJ6Helper'], true);

		$container->alias(EmailHelper::class, 'Compiler.Creator.Email.Helper')
			->share('Compiler.Creator.Email.Helper', [$this, 'getEmailHelper'], true);
	}

	/**
	 * Get The AccessSections Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AccessSections
	 * @since 3.2.0
	 */
	public function getAccessSections(Container $container): AccessSections
	{
		return new AccessSections(
			$container->get('Config'),
			$container->get('Event'),
			$container->get('Language'),
			$container->get('Component'),
			$container->get('Field.Name'),
			$container->get('Field.Type.Name'),
			$container->get('Utilities.Counter'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Compiler.Builder.Assets.Rules'),
			$container->get('Compiler.Builder.Custom.Tabs'),
			$container->get('Compiler.Builder.Permission.Views'),
			$container->get('Compiler.Builder.Permission.Fields'),
			$container->get('Compiler.Builder.Permission.Component'),
			$container->get('Compiler.Creator.Custom.Button.Permissions')
		);
	}

	/**
	 * Get The AccessSectionsCategory Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AccessSectionsCategory
	 * @since 3.2.0
	 */
	public function getAccessSectionsCategory(Container $container): AccessSectionsCategory
	{
		return new AccessSectionsCategory(
			$container->get('Compiler.Builder.Category.Code')
		);
	}

	/**
	 * Get The AccessSectionsJoomlaFields Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AccessSectionsJoomlaFields
	 * @since 3.2.0
	 */
	public function getAccessSectionsJoomlaFields(Container $container): AccessSectionsJoomlaFields
	{
		return new AccessSectionsJoomlaFields();
	}

	/**
	 * Get The Builders Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Builders
	 * @since 3.2.0
	 */
	public function getBuilders(Container $container): Builders
	{
		return new Builders(
			$container->get('Config'),
			$container->get('Power'),
			$container->get('Language'),
			$container->get('Placeholder'),
			$container->get('Compiler.Creator.Layout'),
			$container->get('Compiler.Creator.Site.Field.Data'),
			$container->get('Compiler.Builder.Tags'),
			$container->get('Compiler.Builder.Database.Tables'),
			$container->get('Compiler.Builder.Database.Unique.Keys'),
			$container->get('Compiler.Builder.Database.Keys'),
			$container->get('Compiler.Builder.Database.Unique.Guid'),
			$container->get('Compiler.Builder.List.Join'),
			$container->get('Compiler.Builder.History'),
			$container->get('Compiler.Builder.Alias'),
			$container->get('Compiler.Builder.Title'),
			$container->get('Compiler.Builder.Category.Other.Name'),
			$container->get('Compiler.Builder.Lists'),
			$container->get('Compiler.Builder.Custom.List'),
			$container->get('Compiler.Builder.Field.Relations'),
			$container->get('Compiler.Builder.Hidden.Fields'),
			$container->get('Compiler.Builder.Integer.Fields'),
			$container->get('Compiler.Builder.Dynamic.Fields'),
			$container->get('Compiler.Builder.Main.Text.Field'),
			$container->get('Compiler.Builder.Custom.Field'),
			$container->get('Compiler.Builder.Custom.Field.Links'),
			$container->get('Compiler.Builder.Script.User.Switch'),
			$container->get('Compiler.Builder.Script.Media.Switch'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Check.Box'),
			$container->get('Compiler.Builder.Json.String'),
			$container->get('Compiler.Builder.Base.Six.Four'),
			$container->get('Compiler.Builder.Model.Basic.Field'),
			$container->get('Compiler.Builder.Model.Whmcs.Field'),
			$container->get('Compiler.Builder.Model.Medium.Field'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator'),
			$container->get('Compiler.Builder.Model.Expert.Field'),
			$container->get('Compiler.Builder.Json.Item'),
			$container->get('Compiler.Builder.Items.Method.List.String'),
			$container->get('Compiler.Builder.Json.Item.Array'),
			$container->get('Compiler.Builder.Items.Method.Eximport.String'),
			$container->get('Compiler.Builder.Selection.Translation'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Sort'),
			$container->get('Compiler.Builder.Search'),
			$container->get('Compiler.Builder.Filter'),
			$container->get('Compiler.Builder.Component.Fields')
		);
	}

	/**
	 * Get The CustomFieldTypeFile Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomFieldTypeFile
	 * @since 3.2.0
	 */
	public function getCustomFieldTypeFile(Container $container): CustomFieldTypeFile
	{
		return new CustomFieldTypeFile(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Compiler.Builder.Site.Field.Data'),
			$container->get('Placeholder'),
			$container->get('Language'),
			$container->get('Component.Placeholder'),
			$container->get('Utilities.Structure'),
			$container->get('Field.Input.Button'),
			$container->get('Compiler.Builder.Field.Group.Control'),
			$container->get('Compiler.Builder.Extension.Custom.Fields'),
			$container->get('Header'),
			$container->get('Field.Core.Field')
		);
	}

	/**
	 * Get The CustomButtonPermissions Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomButtonPermissions
	 * @since 3.2.0
	 */
	public function getCustomButtonPermissions(Container $container): CustomButtonPermissions
	{
		return new CustomButtonPermissions(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Permission.Component'),
			$container->get('Utilities.Counter')
		);
	}

	/**
	 * Get The ConfigFieldsets Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConfigFieldsets
	 * @since 3.2.0
	 */
	public function getConfigFieldsets(Container $container): ConfigFieldsets
	{
		return new ConfigFieldsets(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Event'),
			$container->get('Placeholder'),
			$container->get('Component.Placeholder'),
			$container->get('Compiler.Builder.Extensions.Params'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield'),
			$container->get('Compiler.Creator.Field.As.String'),
			$container->get('Compiler.Creator.Config.Fieldsets.Global'),
			$container->get('Compiler.Creator.Config.Fieldsets.Site.Control'),
			$container->get('Compiler.Creator.Config.Fieldsets.Group.Control'),
			$container->get('Compiler.Creator.Config.Fieldsets.Uikit'),
			$container->get('Compiler.Creator.Config.Fieldsets.Googlechart'),
			$container->get('Compiler.Creator.Config.Fieldsets.Email.Helper'),
			$container->get('Compiler.Creator.Config.Fieldsets.Encryption'),
			$container->get('Compiler.Creator.Config.Fieldsets.Customfield')
		);
	}

	/**
	 * Get The ConfigFieldsetsCustomfield Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConfigFieldsetsCustomfield
	 * @since 3.2.0
	 */
	public function getConfigFieldsetsCustomfield(Container $container): ConfigFieldsetsCustomfield
	{
		return new ConfigFieldsetsCustomfield(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield'),
			$container->get('Compiler.Builder.Config.Fieldsets')
		);
	}

	/**
	 * Get The ConfigFieldsetsEmailHelper Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConfigFieldsetsEmailHelper
	 * @since 3.2.0
	 */
	public function getConfigFieldsetsEmailHelper(Container $container): ConfigFieldsetsEmailHelper
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Compiler.Creator.Config.Fieldsets.J' . $this->targetVersion . '.Email.Helper');
	}

	/**
	 * Build the email configuration renderer for Joomla 3 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ConfigFieldsetsEmailHelper
	 * @since   6.2.0
	 */
	public function getJ3ConfigFieldsetsEmailHelper(Container $container): J3ConfigFieldsetsEmailHelper
	{
		return new J3ConfigFieldsetsEmailHelper(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield')
		);
	}

	/**
	 * Build the email configuration renderer for Joomla 4 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4ConfigFieldsetsEmailHelper
	 * @since   6.2.0
	 */
	public function getJ4ConfigFieldsetsEmailHelper(Container $container): J4ConfigFieldsetsEmailHelper
	{
		return new J4ConfigFieldsetsEmailHelper(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield')
		);
	}

	/**
	 * Build the email configuration renderer for Joomla 5 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5ConfigFieldsetsEmailHelper
	 * @since   6.2.0
	 */
	public function getJ5ConfigFieldsetsEmailHelper(Container $container): J5ConfigFieldsetsEmailHelper
	{
		return new J5ConfigFieldsetsEmailHelper(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield')
		);
	}

	/**
	 * Build the email configuration renderer for Joomla 6 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ConfigFieldsetsEmailHelper
	 * @since   6.2.0
	 */
	public function getJ6ConfigFieldsetsEmailHelper(Container $container): J6ConfigFieldsetsEmailHelper
	{
		return new J6ConfigFieldsetsEmailHelper(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield')
		);
	}

	/**
	 * Get The ConfigFieldsetsEncryption Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConfigFieldsetsEncryption
	 * @since 3.2.0
	 */
	public function getConfigFieldsetsEncryption(Container $container): ConfigFieldsetsEncryption
	{
		return new ConfigFieldsetsEncryption(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield')
		);
	}

	/**
	 * Get The ConfigFieldsetsGlobal Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConfigFieldsetsGlobal
	 * @since 3.2.0
	 */
	public function getConfigFieldsetsGlobal(Container $container): ConfigFieldsetsGlobal
	{
		return new ConfigFieldsetsGlobal(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Contributors'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Extensions.Params'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield')
		);
	}

	/**
	 * Get The ConfigFieldsetsGooglechart Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConfigFieldsetsGooglechart
	 * @since 3.2.0
	 */
	public function getConfigFieldsetsGooglechart(Container $container): ConfigFieldsetsGooglechart
	{
		return new ConfigFieldsetsGooglechart(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield'),
			$container->get('Compiler.Builder.Extensions.Params')
		);
	}

	/**
	 * Get The ConfigFieldsetsGroupControl Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConfigFieldsetsGroupControl
	 * @since 3.2.0
	 */
	public function getConfigFieldsetsGroupControl(Container $container): ConfigFieldsetsGroupControl
	{
		return new ConfigFieldsetsGroupControl(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Field.Group.Control'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Extensions.Params'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield')
		);
	}

	/**
	 * Get The ConfigFieldsetsSiteControl Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConfigFieldsetsSiteControl
	 * @since 3.2.0
	 */
	public function getConfigFieldsetsSiteControl(Container $container): ConfigFieldsetsSiteControl
	{
		return new ConfigFieldsetsSiteControl(
			$container->get('Component'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield'),
			$container->get('Compiler.Builder.Has.Menu.Global'),
			$container->get('Compiler.Builder.Frontend.Params'),
			$container->get('Compiler.Creator.Request')
		);
	}

	/**
	 * Get The ConfigFieldsetsUikit Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ConfigFieldsetsUikit
	 * @since 3.2.0
	 */
	public function getConfigFieldsetsUikit(Container $container): ConfigFieldsetsUikit
	{
		return new ConfigFieldsetsUikit(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Config.Fieldsets'),
			$container->get('Compiler.Builder.Extensions.Params'),
			$container->get('Compiler.Builder.Config.Fieldsets.Customfield')
		);
	}

	/**
	 * Get The Layout Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Layout
	 * @since 3.2.0
	 */
	public function getLayout(Container $container): Layout
	{
		return new Layout(
			$container->get('Config'),
			$container->get('Compiler.Builder.Order.Zero'),
			$container->get('Compiler.Builder.Tab.Counter'),
			$container->get('Compiler.Builder.Layout'),
			$container->get('Compiler.Builder.Moved.Publishing.Fields'),
			$container->get('Compiler.Builder.New.Publishing.Fields')
		);
	}

	/**
	 * Get The Permission Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Permission
	 * @since 3.2.0
	 */
	public function getPermission(Container $container): Permission
	{
		return new Permission(
			$container->get('Config'),
			$container->get('Compiler.Builder.Permission.Core'),
			$container->get('Compiler.Builder.Permission.Views'),
			$container->get('Compiler.Builder.Permission.Action'),
			$container->get('Compiler.Builder.Permission.Component'),
			$container->get('Compiler.Builder.Permission.Global.Action'),
			$container->get('Compiler.Builder.Permission.Dashboard'),
			$container->get('Utilities.Counter'),
			$container->get('Language')
		);
	}

	/**
	 * Get The SiteFieldData Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SiteFieldData
	 * @since 3.2.0
	 */
	public function getSiteFieldData(Container $container): SiteFieldData
	{
		return new SiteFieldData(
			$container->get('Config'),
			$container->get('Compiler.Builder.Site.Fields'),
			$container->get('Compiler.Builder.Site.Field.Data')
		);
	}

	/**
	 * Get The Request Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Request
	 * @since 3.2.0
	 */
	public function getRequest(Container $container): Request
	{
		return new Request(
			$container->get('Compiler.Builder.Request')
		);
	}

	/**
	 * Get The Router Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Router
	 * @since 3.2.0
	 */
	public function getRouter(Container $container): Router
	{
		return new Router(
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Request'),
			$container->get('Compiler.Builder.Router'),
			$container->get('Compiler.Creator.Router.Constructor.Default'),
			$container->get('Compiler.Creator.Router.Constructor.Manual'),
			$container->get('Compiler.Creator.Router.Methods.Default'),
			$container->get('Compiler.Creator.Router.Methods.Manual')
		);
	}

	/**
	 * Get The RouterConstructorDefault Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RouterConstructorDefault
	 * @since 3.2.0
	 */
	public function getRouterConstructorDefault(Container $container): RouterConstructorDefault
	{
		return new RouterConstructorDefault(
			$container->get('Compiler.Builder.Router')
		);
	}

	/**
	 * Get The RouterConstructorManual Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RouterConstructorManual
	 * @since 3.2.0
	 */
	public function getRouterConstructorManual(Container $container): RouterConstructorManual
	{
		return new RouterConstructorManual(
			$container->get('Compiler.Builder.Router')
		);
	}

	/**
	 * Get The RouterMethodsDefault Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RouterMethodsDefault
	 * @since 3.2.0
	 */
	public function getRouterMethodsDefault(Container $container): RouterMethodsDefault
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Compiler.Creator.Router.Methods.J6.Default');
		}

		return $container->get('Compiler.Creator.Router.Methods.Shared.Default');
	}

	/**
	 * Build the RouterMethodsDefault renderer for shared targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RouterMethodsDefault
	 * @since   6.2.0
	 */
	public function getSharedRouterMethodsDefault(Container $container): RouterMethodsDefault
	{
		return new RouterMethodsDefault(
			$container->get('Compiler.Builder.Router')
		);
	}

	/**
	 * Build the RouterMethodsDefault renderer for Joomla 6 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6RouterMethodsDefault
	 * @since   6.2.0
	 */
	public function getJ6RouterMethodsDefault(Container $container): J6RouterMethodsDefault
	{
		return new J6RouterMethodsDefault(
			$container->get('Compiler.Builder.Router')
		);
	}

	/**
	 * Get The RouterMethodsManual Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RouterMethodsManual
	 * @since 3.2.0
	 */
	public function getRouterMethodsManual(Container $container): RouterMethodsManual
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Compiler.Creator.Router.Methods.J6.Manual');
		}

		return $container->get('Compiler.Creator.Router.Methods.Shared.Manual');
	}

	/**
	 * Build the RouterMethodsManual renderer for shared targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RouterMethodsManual
	 * @since   6.2.0
	 */
	public function getSharedRouterMethodsManual(Container $container): RouterMethodsManual
	{
		return new RouterMethodsManual(
			$container->get('Compiler.Builder.Router')
		);
	}

	/**
	 * Build the RouterMethodsManual renderer for Joomla 6 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6RouterMethodsManual
	 * @since   6.2.0
	 */
	public function getJ6RouterMethodsManual(Container $container): J6RouterMethodsManual
	{
		return new J6RouterMethodsManual(
			$container->get('Compiler.Builder.Router')
		);
	}

	/**
	 * Get The FieldsetExtension Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldsetExtension
	 * @since   5.0.2
	 */
	public function getFieldsetExtension(Container $container): FieldsetExtension
	{
		return new FieldsetExtension(
			$container->get('Component.Placeholder'),
			$container->get('Compiler.Creator.Fieldset.Dynamic')
		);
	}

	/**
	 * Get The FieldsetString Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldsetString
	 * @since 3.2.0
	 */
	public function getFieldsetString(Container $container): FieldsetString
	{
		return new FieldsetString(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Language.Fieldset'),
			$container->get('Event'),
			$container->get('Adminview.Permission'),
			$container->get('Compiler.Creator.Field.Dynamic'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Meta.Data'),
			$container->get('Compiler.Creator.Layout'),
			$container->get('Utilities.Counter')
		);
	}

	/**
	 * Get The FieldsetXML Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldsetXML
	 * @since 3.2.0
	 */
	public function getFieldsetXML(Container $container): FieldsetXML
	{
		return new FieldsetXML(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Language.Fieldset'),
			$container->get('Event'),
			$container->get('Adminview.Permission'),
			$container->get('Compiler.Creator.Field.Dynamic'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Meta.Data'),
			$container->get('Compiler.Creator.Layout'),
			$container->get('Utilities.Counter'),
			$container->get('Utilities.Xml')
		);
	}

	/**
	 * Get The FieldsetDynamic Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldsetDynamic
	 * @since 3.2.0
	 */
	public function getFieldsetDynamic(Container $container): FieldsetDynamic
	{
		return new FieldsetDynamic(
			$container->get('Compiler.Creator.Field.As.String')
		);
	}

	/**
	 * Get The FieldXML Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldXML
	 * @since 3.2.0
	 */
	public function getFieldXML(Container $container): FieldXML
	{
		return new FieldXML(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Field'),
			$container->get('Field.Groups'),
			$container->get('Field.Name'),
			$container->get('Field.Type.Name'),
			$container->get('Field.Attributes'),
			$container->get('Field.ModalSelect'),
			$container->get('Utilities.Xml'),
			$container->get('Compiler.Creator.Custom.Field.Type.File'),
			$container->get('Utilities.Counter'),
			$container->get('Compiler.Builder.Component.Fields')
		);
	}

	/**
	 * Get The FieldString Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldString
	 * @since 3.2.0
	 */
	public function getFieldString(Container $container): FieldString
	{
		return new FieldString(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Field'),
			$container->get('Field.Groups'),
			$container->get('Field.Name'),
			$container->get('Field.Type.Name'),
			$container->get('Field.Attributes'),
			$container->get('Field.ModalSelect'),
			$container->get('Compiler.Creator.Custom.Field.Type.File'),
			$container->get('Utilities.Counter'),
			$container->get('Compiler.Builder.Component.Fields')
		);
	}

	/**
	 * Get The FieldDynamic Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldDynamic
	 * @since 3.2.0
	 */
	public function getFieldDynamic(Container $container): FieldDynamic
	{
		return new FieldDynamic(
			$container->get('Field.Name'),
			$container->get('Field.Type.Name'),
			$container->get('Field.Attributes'),
			$container->get('Field.ModalSelect'),
			$container->get('Field.Groups'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Creator.Field.Type'),
			$container->get('Compiler.Creator.Builders'),
			$container->get('Compiler.Creator.Layout')
		);
	}

	/**
	 * Get The FieldAsString Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldAsString
	 * @since 3.2.0
	 */
	public function getFieldAsString(Container $container): FieldAsString
	{
		return new FieldAsString(
			$container->get('Compiler.Creator.Field.Dynamic'),
			$container->get('Utilities.Xml')
		);
	}

	/**
	 * Get The Fieldtypeinterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldType
	 * @since 3.2.0
	 */
	public function getFieldType(Container $container): FieldType
	{
		// check what type of field builder to use
		if ($container->get('Config')->get('field_builder_type', 2) == 1)
		{
			// build field set using string manipulation
			return $container->get('Compiler.Creator.Field.String');
		}
		else
		{
			// build field set with simpleXMLElement class
			return $container->get('Compiler.Creator.Field.XML');
		}
	}

	/**
	 * Get The Fieldsetinterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Fieldset
	 * @since 3.2.0
	 */
	public function getFieldset(Container $container): Fieldset
	{
		// check what type of field builder to use
		if ($container->get('Config')->get('field_builder_type', 2) == 1)
		{
			// build field set using string manipulation
			return $container->get('Compiler.Creator.Fieldset.String');
		}
		else
		{
			// build field set with simpleXMLElement class
			return $container->get('Compiler.Creator.Fieldset.XML');
		}
	}

	/**
	 * Get The Helper Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Helper
	 * @since   5.1.4
	 */
	public function getHelper(Container $container): Helper
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Compiler.Creator.J6.Helper');
		}

		return $container->get('Compiler.Creator.Shared.Helper');
	}

	/**
	 * Build the Helper renderer for shared targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Helper
	 * @since   6.2.0
	 */
	public function getSharedHelper(Container $container): Helper
	{
		return new Helper(
			$container->get('Config'),
			$container->get('Utilities.Structure'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi')
		);
	}

	/**
	 * Build the Helper renderer for Joomla 6 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6Helper
	 * @since   6.2.0
	 */
	public function getJ6Helper(Container $container): J6Helper
	{
		return new J6Helper(
			$container->get('Config'),
			$container->get('Utilities.Structure'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi')
		);
	}

	/**
	 * Get The EmailHelper Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  EmailHelper
	 * @since   5.1.4
	 */
	public function getEmailHelper(Container $container): EmailHelper
	{
		return new EmailHelper(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Utilities.Structure'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi')
		);
	}
}

