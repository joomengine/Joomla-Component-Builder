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
use VDM\Joomla\Componentbuilder\Server\Model\Load as ServerLoad;
use VDM\Joomla\Componentbuilder\Compiler\Model\Joomlaplugins;
use VDM\Joomla\Componentbuilder\Compiler\Model\Joomlamodules;
use VDM\Joomla\Componentbuilder\Compiler\Model\Historycomponent;
use VDM\Joomla\Componentbuilder\Compiler\Model\Customadminviews;
use VDM\Joomla\Componentbuilder\Compiler\Model\Ajaxcustomview;
use VDM\Joomla\Componentbuilder\Compiler\Model\Javascriptcustomview;
use VDM\Joomla\Componentbuilder\Compiler\Model\Csscustomview;
use VDM\Joomla\Componentbuilder\Compiler\Model\Phpcustomview;
use VDM\Joomla\Componentbuilder\Compiler\Model\Dynamicget;
use VDM\Joomla\Componentbuilder\Compiler\Model\Libraries;
use VDM\Joomla\Componentbuilder\Compiler\Model\Siteviews;
use VDM\Joomla\Componentbuilder\Compiler\Model\Permissions;
use VDM\Joomla\Componentbuilder\Compiler\Model\Historyadminview;
use VDM\Joomla\Componentbuilder\Compiler\Model\Mysqlsettings;
use VDM\Joomla\Componentbuilder\Compiler\Model\Sql;
use VDM\Joomla\Componentbuilder\Compiler\Model\Customalias;
use VDM\Joomla\Componentbuilder\Compiler\Model\Ajaxadmin;
use VDM\Joomla\Componentbuilder\Compiler\Model\Customimportscripts;
use VDM\Joomla\Componentbuilder\Compiler\Model\Custombuttons;
use VDM\Joomla\Componentbuilder\Compiler\Model\Loader;
use VDM\Joomla\Componentbuilder\Compiler\Model\Phpadminview;
use VDM\Joomla\Componentbuilder\Compiler\Model\Cssadminview;
use VDM\Joomla\Componentbuilder\Compiler\Model\Javascriptadminview;
use VDM\Joomla\Componentbuilder\Compiler\Model\Linkedviews;
use VDM\Joomla\Componentbuilder\Compiler\Model\Relations;
use VDM\Joomla\Componentbuilder\Compiler\Model\Conditions;
use VDM\Joomla\Componentbuilder\Compiler\Model\Fields;
use VDM\Joomla\Componentbuilder\Compiler\Model\Updatesql;
use VDM\Joomla\Componentbuilder\Compiler\Model\Tabs;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Model\CustomtabsInterface as Customtabs;
use VDM\Joomla\Componentbuilder\Compiler\Model\JoomlaThree\Customtabs as CustomtabsJ3;
use VDM\Joomla\Componentbuilder\Compiler\Model\JoomlaFour\Customtabs as CustomtabsJ4;
use VDM\Joomla\Componentbuilder\Compiler\Model\JoomlaFive\Customtabs as CustomtabsJ5;
use VDM\Joomla\Componentbuilder\Compiler\Model\JoomlaSix\Customtabs as CustomtabsJ6;
use VDM\Joomla\Componentbuilder\Compiler\Model\Adminviews;
use VDM\Joomla\Componentbuilder\Compiler\Model\Sqltweaking;
use VDM\Joomla\Componentbuilder\Compiler\Model\Sqldump;
use VDM\Joomla\Componentbuilder\Compiler\Model\Whmcs;
use VDM\Joomla\Componentbuilder\Compiler\Model\Filesfolders;
use VDM\Joomla\Componentbuilder\Compiler\Model\Modifieddate;
use VDM\Joomla\Componentbuilder\Compiler\Model\Createdate;
use VDM\Joomla\Componentbuilder\Compiler\Model\Router;
use VDM\Joomla\Componentbuilder\Compiler\Model\Updateserver;


/**
 * Model Service Provider
 * 
 * @since 3.2.0
 */
class Model implements ServiceProviderInterface
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
		$container->alias(ServerLoad::class, 'Model.Server.Load')
			->share('Model.Server.Load', [$this, 'getServerLoad'], true);

		$container->alias(Joomlaplugins::class, 'Model.Joomlaplugins')
			->share('Model.Joomlaplugins', [$this, 'getJoomlaplugins'], true);

		$container->alias(Joomlamodules::class, 'Model.Joomlamodules')
			->share('Model.Joomlamodules', [$this, 'getJoomlamodules'], true);

		$container->alias(Historycomponent::class, 'Model.Historycomponent')
			->share('Model.Historycomponent', [$this, 'getHistorycomponent'], true);

		$container->alias(Customadminviews::class, 'Model.Customadminviews')
			->share('Model.Customadminviews', [$this, 'getCustomadminviews'], true);

		$container->alias(Ajaxcustomview::class, 'Model.Ajaxcustomview')
			->share('Model.Ajaxcustomview', [$this, 'getAjaxcustomview'], true);

		$container->alias(Javascriptcustomview::class, 'Model.Javascriptcustomview')
			->share('Model.Javascriptcustomview', [$this, 'getJavascriptcustomview'], true);

		$container->alias(Csscustomview::class, 'Model.Csscustomview')
			->share('Model.Csscustomview', [$this, 'getCsscustomview'], true);

		$container->alias(Phpcustomview::class, 'Model.Phpcustomview')
			->share('Model.Phpcustomview', [$this, 'getPhpcustomview'], true);

		$container->alias(Dynamicget::class, 'Model.Dynamicget')
			->share('Model.Dynamicget', [$this, 'getDynamicget'], true);

		$container->alias(Libraries::class, 'Model.Libraries')
			->share('Model.Libraries', [$this, 'getLibraries'], true);

		$container->alias(Siteviews::class, 'Model.Siteviews')
			->share('Model.Siteviews', [$this, 'getSiteviews'], true);

		$container->alias(Permissions::class, 'Model.Permissions')
			->share('Model.Permissions', [$this, 'getPermissions'], true);

		$container->alias(Historyadminview::class, 'Model.Historyadminview')
			->share('Model.Historyadminview', [$this, 'getHistoryadminview'], true);

		$container->alias(Mysqlsettings::class, 'Model.Mysqlsettings')
			->share('Model.Mysqlsettings', [$this, 'getMysqlsettings'], true);

		$container->alias(Sql::class, 'Model.Sql')
			->share('Model.Sql', [$this, 'getSql'], true);

		$container->alias(Customalias::class, 'Model.Customalias')
			->share('Model.Customalias', [$this, 'getCustomalias'], true);

		$container->alias(Ajaxadmin::class, 'Model.Ajaxadmin')
			->share('Model.Ajaxadmin', [$this, 'getAjaxadmin'], true);

		$container->alias(Customimportscripts::class, 'Model.Customimportscripts')
			->share('Model.Customimportscripts', [$this, 'getCustomimportscripts'], true);

		$container->alias(Custombuttons::class, 'Model.Custombuttons')
			->share('Model.Custombuttons', [$this, 'getCustombuttons'], true);

		$container->alias(Loader::class, 'Model.Loader')
			->share('Model.Loader', [$this, 'getLoader'], true);

		$container->alias(Phpadminview::class, 'Model.Phpadminview')
			->share('Model.Phpadminview', [$this, 'getPhpadminview'], true);

		$container->alias(Cssadminview::class, 'Model.Cssadminview')
			->share('Model.Cssadminview', [$this, 'getCssadminview'], true);

		$container->alias(Javascriptadminview::class, 'Model.Javascriptadminview')
			->share('Model.Javascriptadminview', [$this, 'getJavascriptadminview'], true);

		$container->alias(Linkedviews::class, 'Model.Linkedviews')
			->share('Model.Linkedviews', [$this, 'getLinkedviews'], true);

		$container->alias(Relations::class, 'Model.Relations')
			->share('Model.Relations', [$this, 'getRelations'], true);

		$container->alias(Conditions::class, 'Model.Conditions')
			->share('Model.Conditions', [$this, 'getConditions'], true);

		$container->alias(Fields::class, 'Model.Fields')
			->share('Model.Fields', [$this, 'getFields'], true);

		$container->alias(Updatesql::class, 'Model.Updatesql')
			->share('Model.Updatesql', [$this, 'getUpdatesql'], true);

		$container->alias(Tabs::class, 'Model.Tabs')
			->share('Model.Tabs', [$this, 'getTabs'], true);

		$container->alias(Customtabs::class, 'Model.Customtabs')
			->share('Model.Customtabs', [$this, 'getCustomtabs'], true);

		$container->alias(CustomtabsJ3::class, 'Model.J3.Customtabs')
			->share('Model.J3.Customtabs', [$this, 'getCustomtabsJ3'], true);

		$container->alias(CustomtabsJ4::class, 'Model.J4.Customtabs')
			->share('Model.J4.Customtabs', [$this, 'getCustomtabsJ4'], true);

		$container->alias(CustomtabsJ5::class, 'Model.J5.Customtabs')
			->share('Model.J5.Customtabs', [$this, 'getCustomtabsJ5'], true);

		$container->alias(CustomtabsJ6::class, 'Model.J6.Customtabs')
			->share('Model.J6.Customtabs', [$this, 'getCustomtabsJ6'], true);

		$container->alias(Adminviews::class, 'Model.Adminviews')
			->share('Model.Adminviews', [$this, 'getAdminviews'], true);

		$container->alias(Sqltweaking::class, 'Model.Sqltweaking')
			->share('Model.Sqltweaking', [$this, 'getSqltweaking'], true);

		$container->alias(Sqldump::class, 'Model.Sqldump')
			->share('Model.Sqldump', [$this, 'getSqldump'], true);

		$container->alias(Whmcs::class, 'Model.Whmcs')
			->share('Model.Whmcs', [$this, 'getWhmcs'], true);

		$container->alias(Filesfolders::class, 'Model.Filesfolders')
			->share('Model.Filesfolders', [$this, 'getFilesfolders'], true);

		$container->alias(Modifieddate::class, 'Model.Modifieddate')
			->share('Model.Modifieddate', [$this, 'getModifieddate'], true);

		$container->alias(Createdate::class, 'Model.Createdate')
			->share('Model.Createdate', [$this, 'getCreatedate'], true);

		$container->alias(Router::class, 'Model.Router')
			->share('Model.Router', [$this, 'getRouter'], true);

		$container->alias(Updateserver::class, 'Model.Updateserver')
			->share('Model.Updateserver', [$this, 'getUpdateserver'], true);
	}

	/**
	 * Get The Load Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ServerLoad
	 * @since 3.2.0
	 */
	public function getServerLoad(Container $container): ServerLoad
	{
		return new ServerLoad(
			$container->get('Crypt'),
			$container->get('Table')
		);
	}

	/**
	 * Get The Joomlaplugins Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Joomlaplugins
	 * @since 3.2.0
	 */
	public function getJoomlaplugins(Container $container): Joomlaplugins
	{
		return new Joomlaplugins(
			$container->get('Joomlaplugin.Data'),
			$container->get('Architecture.Api.Plugin.Routes')
		);
	}

	/**
	 * Get The Joomlamodules Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Joomlamodules
	 * @since 3.2.0
	 */
	public function getJoomlamodules(Container $container): Joomlamodules
	{
		return new Joomlamodules(
			$container->get('Joomlamodule.Data')
		);
	}

	/**
	 * Get The Historycomponent Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Historycomponent
	 * @since 3.2.0
	 */
	public function getHistorycomponent(Container $container): Historycomponent
	{
		return new Historycomponent(
			$container->get('Config'),
			$container->get('History'),
			$container->get('Model.Updatesql')
		);
	}

	/**
	 * Get The Customadminviews Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Customadminviews
	 * @since 3.2.0
	 */
	public function getCustomadminviews(Container $container): Customadminviews
	{
		return new Customadminviews(
			$container->get('Customview.Data'),
			$container->get('Config')
		);
	}

	/**
	 * Get The Ajaxcustomview Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Ajaxcustomview
	 * @since 3.2.0
	 */
	public function getAjaxcustomview(Container $container): Ajaxcustomview
	{
		return new Ajaxcustomview(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Templatelayout.Data')
		);
	}

	/**
	 * Get The Javascriptcustomview Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Javascriptcustomview
	 * @since 3.2.0
	 */
	public function getJavascriptcustomview(Container $container): Javascriptcustomview
	{
		return new Javascriptcustomview(
			$container->get('Customcode'),
			$container->get('Customcode.Gui')
		);
	}

	/**
	 * Get The Csscustomview Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Csscustomview
	 * @since 3.2.0
	 */
	public function getCsscustomview(Container $container): Csscustomview
	{
		return new Csscustomview(
			$container->get('Customcode')
		);
	}

	/**
	 * Get The Phpcustomview Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Phpcustomview
	 * @since 3.2.0
	 */
	public function getPhpcustomview(Container $container): Phpcustomview
	{
		return new Phpcustomview(
			$container->get('Customcode'),
			$container->get('Customcode.Gui'),
			$container->get('Model.Loader'),
			$container->get('Templatelayout.Data')
		);
	}

	/**
	 * Get The Dynamicget Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Dynamicget
	 * @since 3.2.0
	 */
	public function getDynamicget(Container $container): Dynamicget
	{
		return new Dynamicget(
			$container->get('Config'),
			$container->get('Compiler.Builder.Site.Dynamic.Get'),
			$container->get('Compiler.Builder.Site.Main.Get'),
			$container->get('Customcode'),
			$container->get('Customcode.Gui'),
			$container->get('Placeholder'),
			$container->get('Dynamicget.Selection')
		);
	}

	/**
	 * Get The Libraries Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Libraries
	 * @since 3.2.0
	 */
	public function getLibraries(Container $container): Libraries
	{
		return new Libraries(
			$container->get('Config'),
			$container->get('Compiler.Builder.Library.Manager'),
			$container->get('Library.Data')
		);
	}

	/**
	 * Get The Siteviews Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Siteviews
	 * @since 3.2.0
	 */
	public function getSiteviews(Container $container): Siteviews
	{
		return new Siteviews(
			$container->get('Customview.Data'),
			$container->get('Config')
		);
	}

	/**
	 * Get The Permissions Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Permissions
	 * @since 3.2.0
	 */
	public function getPermissions(Container $container): Permissions
	{
		return new Permissions();
	}

	/**
	 * Get The Historyadminview Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Historyadminview
	 * @since 3.2.0
	 */
	public function getHistoryadminview(Container $container): Historyadminview
	{
		return new Historyadminview(
			$container->get('Config'),
			$container->get('History'),
			$container->get('Model.Updatesql')
		);
	}

	/**
	 * Get The Mysqlsettings Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Mysqlsettings
	 * @since 3.2.0
	 */
	public function getMysqlsettings(Container $container): Mysqlsettings
	{
		return new Mysqlsettings(
			$container->get('Config'),
			$container->get('Compiler.Builder.Mysql.Table.Setting')
		);
	}

	/**
	 * Get The Sql Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Sql
	 * @since 3.2.0
	 */
	public function getSql(Container $container): Sql
	{
		return new Sql(
			$container->get('Customcode.Dispenser'),
			$container->get('Model.Sqldump')
		);
	}

	/**
	 * Get The Customalias Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Customalias
	 * @since 3.2.0
	 */
	public function getCustomalias(Container $container): Customalias
	{
		return new Customalias(
			$container->get('Compiler.Builder.Custom.Alias'),
			$container->get('Field.Name')
		);
	}

	/**
	 * Get The Ajaxadmin Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Ajaxadmin
	 * @since 3.2.0
	 */
	public function getAjaxadmin(Container $container): Ajaxadmin
	{
		return new Ajaxadmin(
			$container->get('Config'),
			$container->get('Compiler.Builder.Site.Edit.View'),
			$container->get('Customcode.Dispenser'),
			$container->get('Templatelayout.Data')
		);
	}

	/**
	 * Get The Customimportscripts Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Customimportscripts
	 * @since 3.2.0
	 */
	public function getCustomimportscripts(Container $container): Customimportscripts
	{
		return new Customimportscripts(
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The Custombuttons Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Custombuttons
	 * @since 3.2.0
	 */
	public function getCustombuttons(Container $container): Custombuttons
	{
		return new Custombuttons(
			$container->get('Customcode'),
			$container->get('Customcode.Gui'),
			$container->get('Templatelayout.Data')
		);
	}

	/**
	 * Get The Loader Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Loader
	 * @since 3.2.0
	 */
	public function getLoader(Container $container): Loader
	{
		return new Loader(
			$container->get('Config'),
			$container->get('Compiler.Builder.Footable.Scripts'),
			$container->get('Compiler.Builder.Google.Chart'),
			$container->get('Compiler.Builder.Get.Module'),
			$container->get('Compiler.Builder.Uikit.Comp')
		);
	}

	/**
	 * Get The Phpadminview Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Phpadminview
	 * @since 3.2.0
	 */
	public function getPhpadminview(Container $container): Phpadminview
	{
		return new Phpadminview(
			$container->get('Customcode.Dispenser'),
			$container->get('Templatelayout.Data')
		);
	}

	/**
	 * Get The Cssadminview Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Cssadminview
	 * @since 3.2.0
	 */
	public function getCssadminview(Container $container): Cssadminview
	{
		return new Cssadminview(
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The Javascriptadminview Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Javascriptadminview
	 * @since 3.2.0
	 */
	public function getJavascriptadminview(Container $container): Javascriptadminview
	{
		return new Javascriptadminview(
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The Linkedviews Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Linkedviews
	 * @since 3.2.0
	 */
	public function getLinkedviews(Container $container): Linkedviews
	{
		return new Linkedviews(
			$container->get('Registry')
		);
	}

	/**
	 * Get The Relations Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Relations
	 * @since 3.2.0
	 */
	public function getRelations(Container $container): Relations
	{
		return new Relations(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Customcode'),
			$container->get('Compiler.Builder.List.Join'),
			$container->get('Compiler.Builder.List.Head.Override'),
			$container->get('Compiler.Builder.Field.Relations')
		);
	}

	/**
	 * Get The Conditions Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Conditions
	 * @since 3.2.0
	 */
	public function getConditions(Container $container): Conditions
	{
		return new Conditions(
			$container->get('Field.Type.Name'),
			$container->get('Field.Name'),
			$container->get('Field.Groups')
		);
	}

	/**
	 * Get The Fields Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Fields
	 * @since 3.2.0
	 */
	public function getFields(Container $container): Fields
	{
		return new Fields(
			$container->get('Config'),
			$container->get('Registry'),
			$container->get('History'),
			$container->get('Customcode'),
			$container->get('Field'),
			$container->get('Field.Name'),
			$container->get('Field.Groups'),
			$container->get('Model.Updatesql')
		);
	}

	/**
	 * Get The Updatesql Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Updatesql
	 * @since 3.2.0
	 */
	public function getUpdatesql(Container $container): Updatesql
	{
		return new Updatesql(
			$container->get('Registry')
		);
	}

	/**
	 * Get The Tabs Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Tabs
	 * @since 3.2.0
	 */
	public function getTabs(Container $container): Tabs
	{
		return new Tabs();
	}

	/**
	 * Get The Customtabs Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Customtabs
	 * @since 3.2.0
	 */
	public function getCustomtabs(Container $container): Customtabs
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Model.J' . $this->targetVersion . '.Customtabs');
	}

	/**
	 * Get The CustomtabsJ3 Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomtabsJ3
	 * @since 3.2.0
	 */
	public function getCustomtabsJ3(Container $container): CustomtabsJ3
	{
		return new CustomtabsJ3(
			$container->get('Config'),
			$container->get('Compiler.Builder.Custom.Tabs'),
			$container->get('Language'),
			$container->get('Placeholder'),
			$container->get('Customcode')
		);
	}

	/**
	 * Get The CustomtabsJ4 Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomtabsJ4
	 * @since 3.2.0
	 */
	public function getCustomtabsJ4(Container $container): CustomtabsJ4
	{
		return new CustomtabsJ4(
			$container->get('Config'),
			$container->get('Compiler.Builder.Custom.Tabs'),
			$container->get('Language'),
			$container->get('Placeholder'),
			$container->get('Customcode')
		);
	}

	/**
	 * Get The CustomtabsJ5 Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomtabsJ5
	 * @since 3.2.0
	 */
	public function getCustomtabsJ5(Container $container): CustomtabsJ5
	{
		return new CustomtabsJ5(
			$container->get('Config'),
			$container->get('Compiler.Builder.Custom.Tabs'),
			$container->get('Language'),
			$container->get('Placeholder'),
			$container->get('Customcode')
		);
	}

	/**
	 * Get The CustomtabsJ6 Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomtabsJ6
	 * @since   5.1.2
	 */
	public function getCustomtabsJ6(Container $container): CustomtabsJ6
	{
		return new CustomtabsJ6(
			$container->get('Config'),
			$container->get('Compiler.Builder.Custom.Tabs'),
			$container->get('Language'),
			$container->get('Placeholder'),
			$container->get('Customcode')
		);
	}

	/**
	 * Get The Adminviews Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Adminviews
	 * @since 3.2.0
	 */
	public function getAdminviews(Container $container): Adminviews
	{
		return new Adminviews(
			$container->get('Config'),
			$container->get('Adminview.Data'),
			$container->get('Compiler.Builder.Site.Edit.View'),
			$container->get('Compiler.Builder.Admin.Filter.Type')
		);
	}

	/**
	 * Get The Sqltweaking Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Sqltweaking
	 * @since 3.2.0
	 */
	public function getSqltweaking(Container $container): Sqltweaking
	{
		return new Sqltweaking(
			$container->get('Registry')
		);
	}

	/**
	 * Get The Sqldump Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Sqldump
	 * @since 3.2.0
	 */
	public function getSqldump(Container $container): Sqldump
	{
		return new Sqldump(
			$container->get('Registry')
		);
	}

	/**
	 * Get The Whmcs Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Whmcs
	 * @since 3.2.0
	 */
	public function getWhmcs(Container $container): Whmcs
	{
		return new Whmcs();
	}

	/**
	 * Get The Filesfolders Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Filesfolders
	 * @since 3.2.0
	 */
	public function getFilesfolders(Container $container): Filesfolders
	{
		return new Filesfolders();
	}

	/**
	 * Get The Modifieddate Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Modifieddate
	 * @since 3.2.0
	 */
	public function getModifieddate(Container $container): Modifieddate
	{
		return new Modifieddate();
	}

	/**
	 * Get The Createdate Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Createdate
	 * @since 3.2.0
	 */
	public function getCreatedate(Container $container): Createdate
	{
		return new Createdate();
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
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Router')
		);
	}

	/**
	 * Get The Updateserver Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Updateserver
	 * @since 3.2.0
	 */
	public function getUpdateserver(Container $container): Updateserver
	{
		return new Updateserver();
	}
}

