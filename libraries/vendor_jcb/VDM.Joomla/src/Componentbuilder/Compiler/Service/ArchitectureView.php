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
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomButtons;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\DynamicButtons;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ToolbarComposer as AdminViewsToolbarComposer;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminView\AddToolBarInterface as AdminViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\AdminView\AddToolBar as J6AdminViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\AdminView\AddToolBar as J5AdminViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\AdminView\AddToolBar as J4AdminViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminView\AddToolBar as J3AdminViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminView\AddModalToolBarInterface as AdminViewAddModalToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\AdminView\AddModalToolBar as J6AdminViewAddModalToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\AdminView\AddModalToolBar as J5AdminViewAddModalToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\AdminView\AddModalToolBar as J4AdminViewAddModalToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminView\AddModalToolBar as J3AdminViewAddModalToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\AddToolBarInterface as AdminViewsAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\AdminViews\AddToolBar as J6AdminViewsAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\AdminViews\AddToolBar as J5AdminViewsAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\AdminViews\AddToolBar as J4AdminViewsAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\AddToolBar as J3AdminViewsAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\SiteView\AddToolBarInterface as SiteViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\SiteViews\ModelData as SiteViewsModelData;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\SiteViews\Headers as SiteViewsHeaders;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\SiteViews\Builder as SiteViewsBuilder;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\SiteView\AddToolBar as J6SiteViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\SiteView\AddToolBar as J5SiteViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\SiteView\AddToolBar as J4SiteViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\SiteView\AddToolBar as J3SiteViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\CustomAdmin\AddToolBarInterface as CustomAdminAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\CustomAdminView\AddToolBar as J6CustomAdminViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\CustomAdminView\AddToolBar as J5CustomAdminViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\CustomAdminView\AddToolBar as J4CustomAdminViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\CustomAdminView\AddToolBar as J3CustomAdminViewAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\CustomAdminViews\AddToolBar as J6CustomAdminViewsAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\CustomAdminViews\AddToolBar as J5CustomAdminViewsAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\CustomAdminViews\AddToolBar as J4CustomAdminViewsAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\CustomAdminViews\AddToolBar as J3CustomAdminViewsAddToolBar;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListItemBuilder;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListItem;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListItem\ItemCode;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListItem\Link;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListItem\LinkAuthority;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListItem\LinkLogic;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\DisplayMethodInterface as AdminViewsDisplayMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListLink as AdminViewsListLink;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\FadeInEffect as AdminViewFadeInEffect;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\CustomTabs as AdminViewCustomTabs;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\LinkedView\ListHead as LinkedViewListHead;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\LinkedView\ListQueryInterface as LinkedViewListQuery;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\LinkedView\ListQuery as SharedLinkedViewListQuery;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\LinkedView\ListQuery as J3LinkedViewListQuery;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\LinkedView\ListBodyInterface as LinkedViewListBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\LinkedView\ListBody as SharedLinkedViewListBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\LinkedView\ListBody as J3LinkedViewListBody;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\LinkedView\BuilderInterface as LinkedViewBuilder;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\LinkedView\Builder as SharedLinkedViewBuilder;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\LinkedView\Builder as J3LinkedViewBuilder;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\LinkedView\Builder as J4LinkedViewBuilder;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminView\EditBodyInterface as AdminViewEditBody;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminView\FootableScriptsInterface as AdminViewFootableScripts;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\FootableScripts as SharedAdminViewFootableScripts;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminView\FootableScripts as J3AdminViewFootableScripts;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\EditBody as SharedAdminViewEditBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminView\EditBody as J3AdminViewEditBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\TabLayoutFields as AdminViewTabLayoutFields;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\ViewScript as AdminViewScript;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Router\RouteHelperInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Router\RouteHelper as SharedRouteHelper;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Router\RouteHelper as J3RouteHelper;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Router\SiteRouter;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Layout\View as LayoutView;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\ViewBodyInterface as AdminViewsViewBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ViewBody as SharedAdminViewsViewBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\ViewBody as J3AdminViewsViewBody;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\ListHeadInterface as AdminViewsListHead;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListHead as SharedAdminViewsListHead;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\ListHead as J3AdminViewsListHead;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\FilterFieldFile as AdminViewsFilterFieldFile;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\FilterFieldHelperInterface as AdminViewsFilterFieldHelper;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\FilterFieldHelper as SharedAdminViewsFilterFieldHelper;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\FilterFieldHelper as J3AdminViewsFilterFieldHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\ListBodyInterface as AdminViewsListBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListBody as SharedAdminViewsListBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\ListBody as J3AdminViewsListBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\DisplayMethod as SharedAdminViewsDisplayMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\DisplayMethod as J3AdminViewsDisplayMethod;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\CustomView\DisplayMethodInterface as CustomViewDisplayMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\DisplayMethod as SharedCustomViewDisplayMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\CustomView\DisplayMethod as J4CustomViewDisplayMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\CustomView\DisplayMethod as J3CustomViewDisplayMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Menu\AdminView as MenuAdminView;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Menu\CustomMainMenu as MenuCustomMainMenu;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Menu\CustomSubMenu as MenuCustomSubMenu;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Menu\SubMenus as MenuSubMenus;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Menu\MainMenusInterface as MenuMainMenusInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Menu\MainMenus as SharedMenuMainMenus;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Menu\MainMenus as J3MenuMainMenus;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\UikitLoaderInterface as ViewUikitLoader;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\UikitLoader as SharedViewUikitLoader;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\View\UikitLoader as J6ViewUikitLoader;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\DocumentMetadataInterface as ViewDocumentMetadataInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\DocumentInlineAssetsInterface as ViewDocumentInlineAssetsInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\DocumentInlineAssets as SharedViewDocumentInlineAssets;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\View\DocumentInlineAssets as J3ViewDocumentInlineAssets;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\DocumentCustomPHP as ViewDocumentCustomPHP;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\CustomCSS as ViewCustomCSS;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\GoogleChartLoader as ViewGoogleChartLoader;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\FootableScriptsLoader as ViewFootableScriptsLoader;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\LibrariesLoaderInterface as ViewLibrariesLoaderInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\LibrariesLoader as SharedViewLibrariesLoader;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\View\LibrariesLoader as J3ViewLibrariesLoader;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\JavaScriptFile as ViewJavaScriptFile;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\GetModules as ViewGetModules;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\PrepareDocument as ViewPrepareDocument;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\AjaxTokenInterface as ViewAjaxTokenInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\AjaxToken as SharedViewAjaxToken;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\View\AjaxToken as J3ViewAjaxToken;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\SidebarFiltersInterface as AdminViewsSidebarFiltersInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\SidebarFilters as SharedAdminViewsSidebarFilters;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\SidebarFilters as J3AdminViewsSidebarFilters;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\BatchOptionsInterface as AdminViewsBatchOptionsInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\BatchOptions as SharedAdminViewsBatchOptions;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\BatchOptions as J3AdminViewsBatchOptions;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\Body as CustomViewBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\TemplateBody as CustomViewTemplateBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\Layouts as CustomViewLayouts;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\CustomView\FormInterface as CustomViewFormInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\Form as SharedCustomViewForm;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\CustomView\Form as J6CustomViewForm;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\DocumentMetadata as SharedViewDocumentMetadata;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\View\DocumentMetadata as J3ViewDocumentMetadata;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Menu\CustomViewInterface as MenuCustomViewInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Menu\CustomView as SharedMenuCustomView;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Menu\CustomView as J3MenuCustomView;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\CanDo as AdminViewsCanDo;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Field\SetAccessControl as FieldSetAccessControl;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\SubmitButtonScript as CustomViewSubmitButtonScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Field\ClearValueScript as FieldClearValueScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\Jquery as ViewJquery;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\EximportButtonsInterface as AdminViewsEximportButtons;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\EximportButtons as SharedAdminViewsEximportButtons;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\EximportButtons as J3AdminViewsEximportButtons;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\FilterSetInterface as AdminViewsFilterSet;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\FilterSet as SharedAdminViewsFilterSet;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\FilterSet as J3AdminViewsFilterSet;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\FilterListSetInterface as AdminViewsFilterListSet;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\FilterListSet as SharedAdminViewsFilterListSet;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\AdminViews\FilterListSet as J3AdminViewsFilterListSet;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\Placeholders as ViewPlaceholders;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\EditView as AdminViewsEditView;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\Loop as AdminViewsLoop;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListView as AdminViewsListView;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\Shared as AdminViewsShared;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomAdminViews\Builder as CustomAdminViewsBuilder;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\CodeBody as CustomViewCodeBody;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\ExtraDisplayMethods as CustomViewExtraDisplayMethods;


/**
 * Architecture View Service Provider
 * 
 * @since 5.1.4
 */
class ArchitectureView implements ServiceProviderInterface
{
	/**
	 * Current Joomla Version Being Build
	 *
	 * @var    int
	 * @since  5.1.4
	 **/
	protected $targetVersion;

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 * @since   5.1.4
	 */
	public function register(Container $container)
	{
		$container->alias(CustomButtons::class, 'Architecture.CustomButtons')
			->share('Architecture.CustomButtons', [$this, 'getCustomButtons'], true);

		$container->alias(DynamicButtons::class, 'Architecture.DynamicButtons')
			->share('Architecture.DynamicButtons', [$this, 'getDynamicButtons'], true);


		$container->alias(AdminViewAddToolBar::class, 'Architecture.AdminView.AddToolBar')
			->share('Architecture.AdminView.AddToolBar', [$this, 'getAdminViewAddToolBar'], true);

		$container->alias(J6AdminViewAddToolBar::class, 'Architecture.AdminView.J6.AddToolBar')
			->share('Architecture.AdminView.J6.AddToolBar', [$this, 'getJ6AdminViewAddToolBar'], true);

		$container->alias(J5AdminViewAddToolBar::class, 'Architecture.AdminView.J5.AddToolBar')
			->share('Architecture.AdminView.J5.AddToolBar', [$this, 'getJ5AdminViewAddToolBar'], true);

		$container->alias(J4AdminViewAddToolBar::class, 'Architecture.AdminView.J4.AddToolBar')
			->share('Architecture.AdminView.J4.AddToolBar', [$this, 'getJ4AdminViewAddToolBar'], true);

		$container->alias(J3AdminViewAddToolBar::class, 'Architecture.AdminView.J3.AddToolBar')
			->share('Architecture.AdminView.J3.AddToolBar', [$this, 'getJ3AdminViewAddToolBar'], true);


		$container->alias(AdminViewAddModalToolBar::class, 'Architecture.AdminView.AddModalToolBar')
			->share('Architecture.AdminView.AddModalToolBar', [$this, 'getAdminViewAddModalToolBar'], true);

		$container->alias(J6AdminViewAddModalToolBar::class, 'Architecture.AdminView.J6.AddModalToolBar')
			->share('Architecture.AdminView.J6.AddModalToolBar', [$this, 'getJ6AdminViewAddModalToolBar'], true);

		$container->alias(J5AdminViewAddModalToolBar::class, 'Architecture.AdminView.J5.AddModalToolBar')
			->share('Architecture.AdminView.J5.AddModalToolBar', [$this, 'getJ5AdminViewAddModalToolBar'], true);

		$container->alias(J4AdminViewAddModalToolBar::class, 'Architecture.AdminView.J4.AddModalToolBar')
			->share('Architecture.AdminView.J4.AddModalToolBar', [$this, 'getJ4AdminViewAddModalToolBar'], true);

		$container->alias(J3AdminViewAddModalToolBar::class, 'Architecture.AdminView.J3.AddModalToolBar')
			->share('Architecture.AdminView.J3.AddModalToolBar', [$this, 'getJ3AdminViewAddModalToolBar'], true);


		$container->alias(AdminViewsToolbarComposer::class, 'Architecture.AdminViews.ToolbarComposer')
			->share('Architecture.AdminViews.ToolbarComposer', [$this, 'getAdminViewsToolbarComposer'], true);

		$container->alias(AdminViewsAddToolBar::class, 'Architecture.AdminViews.AddToolBar')
			->share('Architecture.AdminViews.AddToolBar', [$this, 'getAdminViewsAddToolBar'], true);

		$container->alias(J6AdminViewsAddToolBar::class, 'Architecture.AdminViews.J6.AddToolBar')
			->share('Architecture.AdminViews.J6.AddToolBar', [$this, 'getJ6AdminViewsAddToolBar'], true);

		$container->alias(J5AdminViewsAddToolBar::class, 'Architecture.AdminViews.J5.AddToolBar')
			->share('Architecture.AdminViews.J5.AddToolBar', [$this, 'getJ5AdminViewsAddToolBar'], true);

		$container->alias(J4AdminViewsAddToolBar::class, 'Architecture.AdminViews.J4.AddToolBar')
			->share('Architecture.AdminViews.J4.AddToolBar', [$this, 'getJ4AdminViewsAddToolBar'], true);

		$container->alias(J3AdminViewsAddToolBar::class, 'Architecture.AdminViews.J3.AddToolBar')
			->share('Architecture.AdminViews.J3.AddToolBar', [$this, 'getJ3AdminViewsAddToolBar'], true);

		$container->alias(SiteViewAddToolBar::class, 'Architecture.SiteView.AddToolBar')
			->share('Architecture.SiteView.AddToolBar', [$this, 'getSiteViewAddToolBar'], true);

		$container->alias(J6SiteViewAddToolBar::class, 'Architecture.SiteView.J6.AddToolBar')
			->share('Architecture.SiteView.J6.AddToolBar', [$this, 'getJ6SiteViewAddToolBar'], true);

		$container->alias(J5SiteViewAddToolBar::class, 'Architecture.SiteView.J5.AddToolBar')
			->share('Architecture.SiteView.J5.AddToolBar', [$this, 'getJ5SiteViewAddToolBar'], true);

		$container->alias(J4SiteViewAddToolBar::class, 'Architecture.SiteView.J4.AddToolBar')
			->share('Architecture.SiteView.J4.AddToolBar', [$this, 'getJ4SiteViewAddToolBar'], true);

		$container->alias(J3SiteViewAddToolBar::class, 'Architecture.SiteView.J3.AddToolBar')
			->share('Architecture.SiteView.J3.AddToolBar', [$this, 'getJ3SiteViewAddToolBar'], true);


		$container->alias(CustomAdminAddToolBar::class, 'Architecture.CustomAdminView.AddToolBar')
			->share('Architecture.CustomAdminView.AddToolBar', [$this, 'getCustomAdminViewAddToolBar'], true);

		$container->alias(J6CustomAdminViewAddToolBar::class, 'Architecture.CustomAdminView.J6.AddToolBar')
			->share('Architecture.CustomAdminView.J6.AddToolBar', [$this, 'getJ6CustomAdminViewAddToolBar'], true);

		$container->alias(J5CustomAdminViewAddToolBar::class, 'Architecture.CustomAdminView.J5.AddToolBar')
			->share('Architecture.CustomAdminView.J5.AddToolBar', [$this, 'getJ5CustomAdminViewAddToolBar'], true);

		$container->alias(J4CustomAdminViewAddToolBar::class, 'Architecture.CustomAdminView.J4.AddToolBar')
			->share('Architecture.CustomAdminView.J4.AddToolBar', [$this, 'getJ4CustomAdminViewAddToolBar'], true);

		$container->alias(J3CustomAdminViewAddToolBar::class, 'Architecture.CustomAdminView.J3.AddToolBar')
			->share('Architecture.CustomAdminView.J3.AddToolBar', [$this, 'getJ3CustomAdminViewAddToolBar'], true);


		$container->alias(CustomAdminAddToolBar::class, 'Architecture.CustomAdminViews.AddToolBar')
			->share('Architecture.CustomAdminViews.AddToolBar', [$this, 'getCustomAdminViewsAddToolBar'], true);

		$container->alias(J6CustomAdminViewsAddToolBar::class, 'Architecture.CustomAdminViews.J6.AddToolBar')
			->share('Architecture.CustomAdminViews.J6.AddToolBar', [$this, 'getJ6CustomAdminViewsAddToolBar'], true);

		$container->alias(J5CustomAdminViewsAddToolBar::class, 'Architecture.CustomAdminViews.J5.AddToolBar')
			->share('Architecture.CustomAdminViews.J5.AddToolBar', [$this, 'getJ5CustomAdminViewsAddToolBar'], true);

		$container->alias(J4CustomAdminViewsAddToolBar::class, 'Architecture.CustomAdminViews.J4.AddToolBar')
			->share('Architecture.CustomAdminViews.J4.AddToolBar', [$this, 'getJ4CustomAdminViewsAddToolBar'], true);

		$container->alias(J3CustomAdminViewsAddToolBar::class, 'Architecture.CustomAdminViews.J3.AddToolBar')
			->share('Architecture.CustomAdminViews.J3.AddToolBar', [$this, 'getJ3CustomAdminViewsAddToolBar'], true);


		$container->alias(ListItemBuilder::class, 'Architecture.AdminViews.ListItemBuilder')
			->share('Architecture.AdminViews.ListItemBuilder', [$this, 'getAdminViewsListItemBuilder'], true);

		$container->alias(ListItem::class, 'Architecture.AdminViews.ListItem')
			->share('Architecture.AdminViews.ListItem', [$this, 'getAdminViewsListItem'], true);

		$container->alias(ItemCode::class, 'Architecture.AdminViews.ListItem.ItemCode')
			->share('Architecture.AdminViews.ListItem.ItemCode', [$this, 'getAdminViewsListItemItemCode'], true);

		$container->alias(Link::class, 'Architecture.AdminViews.ListItem.Link')
			->share('Architecture.AdminViews.ListItem.Link', [$this, 'getAdminViewsListItemLink'], true);

		$container->alias(LinkAuthority::class, 'Architecture.AdminViews.ListItem.LinkAuthority')
			->share('Architecture.AdminViews.ListItem.LinkAuthority', [$this, 'getAdminViewsListItemLinkAuthority'], true);

		$container->alias(LinkLogic::class, 'Architecture.AdminViews.ListItem.LinkLogic')
			->share('Architecture.AdminViews.ListItem.LinkLogic', [$this, 'getAdminViewsListItemLinkLogic'], true);

		$container->alias(AdminViewFadeInEffect::class, 'Architecture.AdminView.FadeInEffect')
			->share('Architecture.AdminView.FadeInEffect', [$this, 'getAdminViewFadeInEffect'], true);

		$container->alias(AdminViewCustomTabs::class, 'Architecture.AdminView.CustomTabs')
			->share('Architecture.AdminView.CustomTabs', [$this, 'getAdminViewCustomTabs'], true);

		$container->alias(LinkedViewListHead::class, 'Architecture.LinkedView.ListHead')
			->share('Architecture.LinkedView.ListHead', [$this, 'getLinkedViewListHead'], true);

		$container->alias(LinkedViewListQuery::class, 'Architecture.LinkedView.ListQuery')
			->share('Architecture.LinkedView.ListQuery', [$this, 'getLinkedViewListQuery'], true);

		$container->alias(SharedLinkedViewListQuery::class, 'Architecture.LinkedView.Shared.ListQuery')
			->share('Architecture.LinkedView.Shared.ListQuery', [$this, 'getSharedLinkedViewListQuery'], true);

		$container->alias(J3LinkedViewListQuery::class, 'Architecture.LinkedView.J3.ListQuery')
			->share('Architecture.LinkedView.J3.ListQuery', [$this, 'getJ3LinkedViewListQuery'], true);

		$container->alias(LinkedViewListBody::class, 'Architecture.LinkedView.ListBody')
			->share('Architecture.LinkedView.ListBody', [$this, 'getLinkedViewListBody'], true);

		$container->alias(SharedLinkedViewListBody::class, 'Architecture.LinkedView.Shared.ListBody')
			->share('Architecture.LinkedView.Shared.ListBody', [$this, 'getSharedLinkedViewListBody'], true);

		$container->alias(J3LinkedViewListBody::class, 'Architecture.LinkedView.J3.ListBody')
			->share('Architecture.LinkedView.J3.ListBody', [$this, 'getJ3LinkedViewListBody'], true);

		$container->alias(LinkedViewBuilder::class, 'Architecture.LinkedView.Builder')
			->share('Architecture.LinkedView.Builder', [$this, 'getLinkedViewBuilder'], true);

		$container->alias(SharedLinkedViewBuilder::class, 'Architecture.LinkedView.Shared.Builder')
			->share('Architecture.LinkedView.Shared.Builder', [$this, 'getSharedLinkedViewBuilder'], true);

		$container->alias(J3LinkedViewBuilder::class, 'Architecture.LinkedView.J3.Builder')
			->share('Architecture.LinkedView.J3.Builder', [$this, 'getJ3LinkedViewBuilder'], true);

		$container->alias(J4LinkedViewBuilder::class, 'Architecture.LinkedView.J4.Builder')
			->share('Architecture.LinkedView.J4.Builder', [$this, 'getJ4LinkedViewBuilder'], true);

		$container->alias(AdminViewEditBody::class, 'Architecture.AdminView.EditBody')
			->share('Architecture.AdminView.EditBody', [$this, 'getAdminViewEditBody'], true);

		$container->alias(AdminViewFootableScripts::class, 'Architecture.AdminView.FootableScripts')
			->share('Architecture.AdminView.FootableScripts', [$this, 'getAdminViewFootableScripts'], true);

		$container->alias(SharedAdminViewFootableScripts::class, 'Architecture.AdminView.Shared.FootableScripts')
			->share('Architecture.AdminView.Shared.FootableScripts', [$this, 'getSharedAdminViewFootableScripts'], true);

		$container->alias(J3AdminViewFootableScripts::class, 'Architecture.AdminView.J3.FootableScripts')
			->share('Architecture.AdminView.J3.FootableScripts', [$this, 'getJ3AdminViewFootableScripts'], true);

		$container->alias(SharedAdminViewEditBody::class, 'Architecture.AdminView.Shared.EditBody')
			->share('Architecture.AdminView.Shared.EditBody', [$this, 'getSharedAdminViewEditBody'], true);

		$container->alias(J3AdminViewEditBody::class, 'Architecture.AdminView.J3.EditBody')
			->share('Architecture.AdminView.J3.EditBody', [$this, 'getJ3AdminViewEditBody'], true);

		$container->alias(AdminViewTabLayoutFields::class, 'Architecture.AdminView.TabLayoutFields')
			->share('Architecture.AdminView.TabLayoutFields', [$this, 'getAdminViewTabLayoutFields'], true);

		$container->alias(AdminViewScript::class, 'Architecture.AdminView.ViewScript')
			->share('Architecture.AdminView.ViewScript', [$this, 'getAdminViewScript'], true);

		$container->alias(RouteHelperInterface::class, 'Architecture.Router.RouteHelper')
			->share('Architecture.Router.RouteHelper', [$this, 'getRouteHelper'], true);

		$container->alias(SharedRouteHelper::class, 'Architecture.Router.Shared.RouteHelper')
			->share('Architecture.Router.Shared.RouteHelper', [$this, 'getSharedRouteHelper'], true);

		$container->alias(J3RouteHelper::class, 'Architecture.Router.J3.RouteHelper')
			->share('Architecture.Router.J3.RouteHelper', [$this, 'getJ3RouteHelper'], true);

		$container->alias(SiteRouter::class, 'Architecture.Router.SiteRouter')
			->share('Architecture.Router.SiteRouter', [$this, 'getSiteRouter'], true);

		$container->alias(LayoutView::class, 'Architecture.Layout.View')
			->share('Architecture.Layout.View', [$this, 'getLayoutView'], true);

		$container->alias(AdminViewsListLink::class, 'Architecture.AdminViews.ListLink')
			->share('Architecture.AdminViews.ListLink', [$this, 'getAdminViewsListLink'], true);

		$container->alias(AdminViewsViewBody::class, 'Architecture.AdminViews.ViewBody')
			->share('Architecture.AdminViews.ViewBody', [$this, 'getAdminViewsViewBody'], true);

		$container->alias(SharedAdminViewsViewBody::class, 'Architecture.AdminViews.Shared.ViewBody')
			->share('Architecture.AdminViews.Shared.ViewBody', [$this, 'getSharedAdminViewsViewBody'], true);

		$container->alias(J3AdminViewsViewBody::class, 'Architecture.AdminViews.J3.ViewBody')
			->share('Architecture.AdminViews.J3.ViewBody', [$this, 'getJ3AdminViewsViewBody'], true);

		$container->alias(AdminViewsListHead::class, 'Architecture.AdminViews.ListHead')
			->share('Architecture.AdminViews.ListHead', [$this, 'getAdminViewsListHead'], true);

		$container->alias(SharedAdminViewsListHead::class, 'Architecture.AdminViews.Shared.ListHead')
			->share('Architecture.AdminViews.Shared.ListHead', [$this, 'getSharedAdminViewsListHead'], true);

		$container->alias(J3AdminViewsListHead::class, 'Architecture.AdminViews.J3.ListHead')
			->share('Architecture.AdminViews.J3.ListHead', [$this, 'getJ3AdminViewsListHead'], true);

		$container->alias(AdminViewsFilterSet::class, 'Architecture.AdminViews.FilterSet')
			->share('Architecture.AdminViews.FilterSet', [$this, 'getAdminViewsFilterSet'], true);

		$container->alias(SharedAdminViewsFilterSet::class, 'Architecture.AdminViews.Shared.FilterSet')
			->share('Architecture.AdminViews.Shared.FilterSet', [$this, 'getSharedAdminViewsFilterSet'], true);

		$container->alias(J3AdminViewsFilterSet::class, 'Architecture.AdminViews.J3.FilterSet')
			->share('Architecture.AdminViews.J3.FilterSet', [$this, 'getJ3AdminViewsFilterSet'], true);

		$container->alias(AdminViewsFilterListSet::class, 'Architecture.AdminViews.FilterListSet')
			->share('Architecture.AdminViews.FilterListSet', [$this, 'getAdminViewsFilterListSet'], true);

		$container->alias(SharedAdminViewsFilterListSet::class, 'Architecture.AdminViews.Shared.FilterListSet')
			->share('Architecture.AdminViews.Shared.FilterListSet', [$this, 'getSharedAdminViewsFilterListSet'], true);

		$container->alias(J3AdminViewsFilterListSet::class, 'Architecture.AdminViews.J3.FilterListSet')
			->share('Architecture.AdminViews.J3.FilterListSet', [$this, 'getJ3AdminViewsFilterListSet'], true);

		$container->alias(AdminViewsEditView::class, 'Architecture.AdminViews.EditView')
			->share('Architecture.AdminViews.EditView', [$this, 'getAdminViewsEditView'], true);

		$container->alias(AdminViewsListView::class, 'Architecture.AdminViews.ListView')
			->share('Architecture.AdminViews.ListView', [$this, 'getAdminViewsListView'], true);

		$container->alias(AdminViewsShared::class, 'Architecture.AdminViews.Shared')
			->share('Architecture.AdminViews.Shared', [$this, 'getAdminViewsShared'], true);

		$container->alias(AdminViewsLoop::class, 'Architecture.AdminViews.Loop')
			->share('Architecture.AdminViews.Loop', [$this, 'getAdminViewsLoop'], true);

		$container->alias(CustomAdminViewsBuilder::class, 'Architecture.CustomAdminViews.Builder')
			->share('Architecture.CustomAdminViews.Builder', [$this, 'getCustomAdminViewsBuilder'], true);

		$container->alias(SiteViewsModelData::class, 'Architecture.SiteViews.ModelData')
			->share('Architecture.SiteViews.ModelData', [$this, 'getSiteViewsModelData'], true);

		$container->alias(SiteViewsHeaders::class, 'Architecture.SiteViews.Headers')
			->share('Architecture.SiteViews.Headers', [$this, 'getSiteViewsHeaders'], true);

		$container->alias(SiteViewsBuilder::class, 'Architecture.SiteViews.Builder')
			->share('Architecture.SiteViews.Builder', [$this, 'getSiteViewsBuilder'], true);

		$container->alias(CustomViewCodeBody::class, 'Architecture.CustomView.CodeBody')
			->share('Architecture.CustomView.CodeBody', [$this, 'getCustomViewCodeBody'], true);

		$container->alias(CustomViewExtraDisplayMethods::class, 'Architecture.CustomView.ExtraDisplayMethods')
			->share('Architecture.CustomView.ExtraDisplayMethods', [$this, 'getCustomViewExtraDisplayMethods'], true);

		$container->alias(AdminViewsFilterFieldFile::class, 'Architecture.AdminViews.FilterFieldFile')
			->share('Architecture.AdminViews.FilterFieldFile', [$this, 'getAdminViewsFilterFieldFile'], true);

		$container->alias(AdminViewsFilterFieldHelper::class, 'Architecture.AdminViews.FilterFieldHelper')
			->share('Architecture.AdminViews.FilterFieldHelper', [$this, 'getAdminViewsFilterFieldHelper'], true);

		$container->alias(SharedAdminViewsFilterFieldHelper::class, 'Architecture.AdminViews.Shared.FilterFieldHelper')
			->share('Architecture.AdminViews.Shared.FilterFieldHelper', [$this, 'getSharedAdminViewsFilterFieldHelper'], true);

		$container->alias(J3AdminViewsFilterFieldHelper::class, 'Architecture.AdminViews.J3.FilterFieldHelper')
			->share('Architecture.AdminViews.J3.FilterFieldHelper', [$this, 'getJ3AdminViewsFilterFieldHelper'], true);

		$container->alias(AdminViewsListBody::class, 'Architecture.AdminViews.ListBody')
			->share('Architecture.AdminViews.ListBody', [$this, 'getAdminViewsListBody'], true);

		$container->alias(SharedAdminViewsListBody::class, 'Architecture.AdminViews.Shared.ListBody')
			->share('Architecture.AdminViews.Shared.ListBody', [$this, 'getSharedAdminViewsListBody'], true);

		$container->alias(J3AdminViewsListBody::class, 'Architecture.AdminViews.J3.ListBody')
			->share('Architecture.AdminViews.J3.ListBody', [$this, 'getJ3AdminViewsListBody'], true);

		$container->alias(AdminViewsDisplayMethod::class, 'Architecture.AdminViews.DisplayMethod')
			->share('Architecture.AdminViews.DisplayMethod', [$this, 'getAdminViewsDisplayMethod'], true);

		$container->alias(SharedAdminViewsDisplayMethod::class, 'Architecture.AdminViews.Shared.DisplayMethod')
			->share('Architecture.AdminViews.Shared.DisplayMethod', [$this, 'getSharedAdminViewsDisplayMethod'], true);

		$container->alias(J3AdminViewsDisplayMethod::class, 'Architecture.AdminViews.J3.DisplayMethod')
			->share('Architecture.AdminViews.J3.DisplayMethod', [$this, 'getJ3AdminViewsDisplayMethod'], true);

		$container->alias(CustomViewDisplayMethod::class, 'Architecture.CustomView.DisplayMethod')
			->share('Architecture.CustomView.DisplayMethod', [$this, 'getCustomViewDisplayMethod'], true);

		$container->alias(SharedCustomViewDisplayMethod::class, 'Architecture.CustomView.Shared.DisplayMethod')
			->share('Architecture.CustomView.Shared.DisplayMethod', [$this, 'getSharedCustomViewDisplayMethod'], true);

		$container->alias(J4CustomViewDisplayMethod::class, 'Architecture.CustomView.J4.DisplayMethod')
			->share('Architecture.CustomView.J4.DisplayMethod', [$this, 'getJ4CustomViewDisplayMethod'], true);

		$container->alias(J3CustomViewDisplayMethod::class, 'Architecture.CustomView.J3.DisplayMethod')
			->share('Architecture.CustomView.J3.DisplayMethod', [$this, 'getJ3CustomViewDisplayMethod'], true);

		$container->alias(MenuAdminView::class, 'Architecture.Menu.AdminView')
			->share('Architecture.Menu.AdminView', [$this, 'getMenuAdminView'], true);


		$container->alias(MenuCustomMainMenu::class, 'Architecture.Menu.CustomMainMenu')
			->share('Architecture.Menu.CustomMainMenu', [$this, 'getMenuCustomMainMenu'], true);

		$container->alias(MenuCustomSubMenu::class, 'Architecture.Menu.CustomSubMenu')
			->share('Architecture.Menu.CustomSubMenu', [$this, 'getMenuCustomSubMenu'], true);

		$container->alias(MenuSubMenus::class, 'Architecture.Menu.SubMenus')
			->share('Architecture.Menu.SubMenus', [$this, 'getMenuSubMenus'], true);

		$container->alias(MenuMainMenusInterface::class, 'Architecture.Menu.MainMenus')
			->share('Architecture.Menu.MainMenus', [$this, 'getMenuMainMenus'], true);

		$container->alias(SharedMenuMainMenus::class, 'Architecture.Menu.Shared.MainMenus')
			->share('Architecture.Menu.Shared.MainMenus', [$this, 'getSharedMenuMainMenus'], true);

		$container->alias(J3MenuMainMenus::class, 'Architecture.Menu.J3.MainMenus')
			->share('Architecture.Menu.J3.MainMenus', [$this, 'getJ3MenuMainMenus'], true);

		$container->alias(ViewUikitLoader::class, 'Architecture.View.UikitLoader')
			->share('Architecture.View.UikitLoader', [$this, 'getViewUikitLoader'], true);

		$container->alias(SharedViewUikitLoader::class, 'Architecture.View.Shared.UikitLoader')
			->share('Architecture.View.Shared.UikitLoader', [$this, 'getSharedViewUikitLoader'], true);

		$container->alias(J6ViewUikitLoader::class, 'Architecture.View.J6.UikitLoader')
			->share('Architecture.View.J6.UikitLoader', [$this, 'getJ6ViewUikitLoader'], true);
		$container->alias(MenuCustomViewInterface::class, 'Architecture.Menu.CustomView')
			->share('Architecture.Menu.CustomView', [$this, 'getMenuCustomView'], true);

		$container->alias(SharedMenuCustomView::class, 'Architecture.Menu.Shared.CustomView')
			->share('Architecture.Menu.Shared.CustomView', [$this, 'getSharedMenuCustomView'], true);

		$container->alias(J3MenuCustomView::class, 'Architecture.Menu.J3.CustomView')
			->share('Architecture.Menu.J3.CustomView', [$this, 'getJ3MenuCustomView'], true);

		$container->alias(ViewDocumentMetadataInterface::class, 'Architecture.View.DocumentMetadata')
			->share('Architecture.View.DocumentMetadata', [$this, 'getViewDocumentMetadata'], true);

		$container->alias(SharedViewDocumentMetadata::class, 'Architecture.View.Shared.DocumentMetadata')
			->share('Architecture.View.Shared.DocumentMetadata', [$this, 'getSharedViewDocumentMetadata'], true);

		$container->alias(J3ViewDocumentMetadata::class, 'Architecture.View.J3.DocumentMetadata')
			->share('Architecture.View.J3.DocumentMetadata', [$this, 'getJ3ViewDocumentMetadata'], true);

		$container->alias(ViewDocumentInlineAssetsInterface::class, 'Architecture.View.DocumentInlineAssets')
			->share('Architecture.View.DocumentInlineAssets', [$this, 'getViewDocumentInlineAssets'], true);

		$container->alias(SharedViewDocumentInlineAssets::class, 'Architecture.View.Shared.DocumentInlineAssets')
			->share('Architecture.View.Shared.DocumentInlineAssets', [$this, 'getSharedViewDocumentInlineAssets'], true);

		$container->alias(J3ViewDocumentInlineAssets::class, 'Architecture.View.J3.DocumentInlineAssets')
			->share('Architecture.View.J3.DocumentInlineAssets', [$this, 'getJ3ViewDocumentInlineAssets'], true);

		$container->alias(ViewDocumentCustomPHP::class, 'Architecture.View.DocumentCustomPHP')
			->share('Architecture.View.DocumentCustomPHP', [$this, 'getViewDocumentCustomPHP'], true);

		$container->alias(ViewCustomCSS::class, 'Architecture.View.CustomCSS')
			->share('Architecture.View.CustomCSS', [$this, 'getViewCustomCSS'], true);

		$container->alias(ViewGoogleChartLoader::class, 'Architecture.View.GoogleChartLoader')
			->share('Architecture.View.GoogleChartLoader', [$this, 'getViewGoogleChartLoader'], true);

		$container->alias(ViewFootableScriptsLoader::class, 'Architecture.View.FootableScriptsLoader')
			->share('Architecture.View.FootableScriptsLoader', [$this, 'getViewFootableScriptsLoader'], true);

		$container->alias(ViewLibrariesLoaderInterface::class, 'Architecture.View.LibrariesLoader')
			->share('Architecture.View.LibrariesLoader', [$this, 'getViewLibrariesLoader'], true);

		$container->alias(SharedViewLibrariesLoader::class, 'Architecture.View.Shared.LibrariesLoader')
			->share('Architecture.View.Shared.LibrariesLoader', [$this, 'getSharedViewLibrariesLoader'], true);

		$container->alias(J3ViewLibrariesLoader::class, 'Architecture.View.J3.LibrariesLoader')
			->share('Architecture.View.J3.LibrariesLoader', [$this, 'getJ3ViewLibrariesLoader'], true);

		$container->alias(ViewJavaScriptFile::class, 'Architecture.View.JavaScriptFile')
			->share('Architecture.View.JavaScriptFile', [$this, 'getViewJavaScriptFile'], true);

		$container->alias(ViewGetModules::class, 'Architecture.View.GetModules')
			->share('Architecture.View.GetModules', [$this, 'getViewGetModules'], true);

		$container->alias(ViewPlaceholders::class, 'Architecture.View.Placeholders')
			->share('Architecture.View.Placeholders', [$this, 'getViewPlaceholders'], true);

		$container->alias(ViewPrepareDocument::class, 'Architecture.View.PrepareDocument')
			->share('Architecture.View.PrepareDocument', [$this, 'getViewPrepareDocument'], true);

		$container->alias(ViewAjaxTokenInterface::class, 'Architecture.View.AjaxToken')
			->share('Architecture.View.AjaxToken', [$this, 'getViewAjaxToken'], true);

		$container->alias(SharedViewAjaxToken::class, 'Architecture.View.Shared.AjaxToken')
			->share('Architecture.View.Shared.AjaxToken', [$this, 'getSharedViewAjaxToken'], true);

		$container->alias(J3ViewAjaxToken::class, 'Architecture.View.J3.AjaxToken')
			->share('Architecture.View.J3.AjaxToken', [$this, 'getJ3ViewAjaxToken'], true);

		$container->alias(AdminViewsSidebarFiltersInterface::class, 'Architecture.AdminViews.SidebarFilters')
			->share('Architecture.AdminViews.SidebarFilters', [$this, 'getAdminViewsSidebarFilters'], true);

		$container->alias(SharedAdminViewsSidebarFilters::class, 'Architecture.AdminViews.Shared.SidebarFilters')
			->share('Architecture.AdminViews.Shared.SidebarFilters', [$this, 'getSharedAdminViewsSidebarFilters'], true);

		$container->alias(J3AdminViewsSidebarFilters::class, 'Architecture.AdminViews.J3.SidebarFilters')
			->share('Architecture.AdminViews.J3.SidebarFilters', [$this, 'getJ3AdminViewsSidebarFilters'], true);

		$container->alias(AdminViewsBatchOptionsInterface::class, 'Architecture.AdminViews.BatchOptions')
			->share('Architecture.AdminViews.BatchOptions', [$this, 'getAdminViewsBatchOptions'], true);

		$container->alias(SharedAdminViewsBatchOptions::class, 'Architecture.AdminViews.Shared.BatchOptions')
			->share('Architecture.AdminViews.Shared.BatchOptions', [$this, 'getSharedAdminViewsBatchOptions'], true);

		$container->alias(J3AdminViewsBatchOptions::class, 'Architecture.AdminViews.J3.BatchOptions')
			->share('Architecture.AdminViews.J3.BatchOptions', [$this, 'getJ3AdminViewsBatchOptions'], true);

		$container->alias(CustomViewBody::class, 'Architecture.CustomView.Body')
			->share('Architecture.CustomView.Body', [$this, 'getCustomViewBody'], true);

		$container->alias(CustomViewTemplateBody::class, 'Architecture.CustomView.TemplateBody')
			->share('Architecture.CustomView.TemplateBody', [$this, 'getCustomViewTemplateBody'], true);

		$container->alias(CustomViewLayouts::class, 'Architecture.CustomView.Layouts')
			->share('Architecture.CustomView.Layouts', [$this, 'getCustomViewLayouts'], true);

		$container->alias(CustomViewSubmitButtonScript::class, 'Architecture.CustomView.SubmitButtonScript')
			->share('Architecture.CustomView.SubmitButtonScript', [$this, 'getCustomViewSubmitButtonScript'], true);

		$container->alias(FieldClearValueScript::class, 'Architecture.Field.ClearValueScript')
			->share('Architecture.Field.ClearValueScript', [$this, 'getFieldClearValueScript'], true);

		$container->alias(ViewJquery::class, 'Architecture.View.Jquery')
			->share('Architecture.View.Jquery', [$this, 'getViewJquery'], true);

		$container->alias(AdminViewsEximportButtons::class, 'Architecture.AdminViews.EximportButtons')
			->share('Architecture.AdminViews.EximportButtons', [$this, 'getAdminViewsEximportButtons'], true);

		$container->alias(SharedAdminViewsEximportButtons::class, 'Architecture.AdminViews.Shared.EximportButtons')
			->share('Architecture.AdminViews.Shared.EximportButtons', [$this, 'getSharedAdminViewsEximportButtons'], true);

		$container->alias(J3AdminViewsEximportButtons::class, 'Architecture.AdminViews.J3.EximportButtons')
			->share('Architecture.AdminViews.J3.EximportButtons', [$this, 'getJ3AdminViewsEximportButtons'], true);

		$container->alias(CustomViewFormInterface::class, 'Architecture.CustomView.Form')
			->share('Architecture.CustomView.Form', [$this, 'getCustomViewForm'], true);

		$container->alias(SharedCustomViewForm::class, 'Architecture.CustomView.Shared.Form')
			->share('Architecture.CustomView.Shared.Form', [$this, 'getSharedCustomViewForm'], true);

		$container->alias(J6CustomViewForm::class, 'Architecture.CustomView.J6.Form')
			->share('Architecture.CustomView.J6.Form', [$this, 'getJ6CustomViewForm'], true);

		$container->alias(AdminViewsCanDo::class, 'Architecture.AdminViews.CanDo')
			->share('Architecture.AdminViews.CanDo', [$this, 'getAdminViewsCanDo'], true);

		$container->alias(FieldSetAccessControl::class, 'Architecture.Field.SetAccessControl')
			->share('Architecture.Field.SetAccessControl', [$this, 'getFieldSetAccessControl'], true);
	}

	/**
	 * Get The CustomButtons Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomButtons
	 * @since   5.1.4
	 */
	public function getCustomButtons(Container $container): CustomButtons
	{
		return new CustomButtons(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Compiler.Builder.Custom.Form'),
			$container->get('Compiler.Builder.Only.Function.Buttons'),
			$container->get('Utilities.Structure'),
			$container->get('Language'),
			$container->get('Placeholder'),
			$container->get('Registry')
		);
	}

	/**
	 * Get The DynamicButtons Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DynamicButtons
	 * @since   5.1.4
	 */
	public function getDynamicButtons(Container $container): DynamicButtons
	{
		return new DynamicButtons(
			$container->get('Config'),
			$container->get('Compiler.Builder.Dynamic.Buttons'),
			$container->get('Language')
		);
	}

	/**
	 * Get The AddToolBarInterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewAddToolBar
	 * @since   5.1.4
	 */
	public function getAdminViewAddToolBar(Container $container): AdminViewAddToolBar
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.AdminView.J' . $this->targetVersion . '.AddToolBar');
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6AdminViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ6AdminViewAddToolBar(Container $container): J6AdminViewAddToolBar
	{
		return new J6AdminViewAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Compiler.Builder.History'),
			$container->get('Language'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5AdminViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ5AdminViewAddToolBar(Container $container): J5AdminViewAddToolBar
	{
		return new J5AdminViewAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Compiler.Builder.History'),
			$container->get('Language'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4AdminViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ4AdminViewAddToolBar(Container $container): J4AdminViewAddToolBar
	{
		return new J4AdminViewAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Compiler.Builder.History'),
			$container->get('Language'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ3AdminViewAddToolBar(Container $container): J3AdminViewAddToolBar
	{
		return new J3AdminViewAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Compiler.Builder.History'),
			$container->get('Language'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AdminView AddModalToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewAddModalToolBar
	 * @since   5.1.4
	 */
	public function getAdminViewAddModalToolBar(Container $container): AdminViewAddModalToolBar
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.AdminView.J' . $this->targetVersion . '.AddModalToolBar');
	}

	/**
	 * Get The AddModalToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6AdminViewAddModalToolBar
	 * @since   5.1.4
	 */
	public function getJ6AdminViewAddModalToolBar(Container $container): J6AdminViewAddModalToolBar
	{
		return new J6AdminViewAddModalToolBar(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Language'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddModalToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5AdminViewAddModalToolBar
	 * @since   5.1.4
	 */
	public function getJ5AdminViewAddModalToolBar(Container $container): J5AdminViewAddModalToolBar
	{
		return new J5AdminViewAddModalToolBar(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Language'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddModalToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4AdminViewAddModalToolBar
	 * @since   5.1.4
	 */
	public function getJ4AdminViewAddModalToolBar(Container $container): J4AdminViewAddModalToolBar
	{
		return new J4AdminViewAddModalToolBar(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Language'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddModalToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewAddModalToolBar
	 * @since   5.1.4
	 */
	public function getJ3AdminViewAddModalToolBar(Container $container): J3AdminViewAddModalToolBar
	{
		return new J3AdminViewAddModalToolBar(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Language'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The Admin Views Toolbar Composer Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsToolbarComposer
	 * @since 5.1.4
	 */
	public function getAdminViewsToolbarComposer(Container $container): AdminViewsToolbarComposer
	{
		return new AdminViewsToolbarComposer();
	}

	/**
	 * Get The AdminViews AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsAddToolBar
	 * @since   5.1.4
	 */
	public function getAdminViewsAddToolBar(Container $container): AdminViewsAddToolBar
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.AdminViews.J' . $this->targetVersion . '.AddToolBar');
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6AdminViewsAddToolBar
	 * @since   5.1.4
	 */
	public function getJ6AdminViewsAddToolBar(Container $container): J6AdminViewsAddToolBar
	{
		return new J6AdminViewsAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.AdminViews.ToolbarComposer'),
			$container->get('Architecture.DynamicButtons'),
			$container->get('Architecture.CustomButtons'),
			$container->get('Compiler.Builder.Only.Function.Buttons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5AdminViewsAddToolBar
	 * @since   5.1.4
	 */
	public function getJ5AdminViewsAddToolBar(Container $container): J5AdminViewsAddToolBar
	{
		return new J5AdminViewsAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.AdminViews.ToolbarComposer'),
			$container->get('Architecture.DynamicButtons'),
			$container->get('Architecture.CustomButtons'),
			$container->get('Compiler.Builder.Only.Function.Buttons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4AdminViewsAddToolBar
	 * @since   5.1.4
	 */
	public function getJ4AdminViewsAddToolBar(Container $container): J4AdminViewsAddToolBar
	{
		return new J4AdminViewsAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.DynamicButtons'),
			$container->get('Architecture.CustomButtons'),
			$container->get('Compiler.Builder.Only.Function.Buttons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsAddToolBar
	 * @since   5.1.4
	 */
	public function getJ3AdminViewsAddToolBar(Container $container): J3AdminViewsAddToolBar
	{
		return new J3AdminViewsAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.DynamicButtons'),
			$container->get('Architecture.CustomButtons'),
			$container->get('Compiler.Builder.Only.Function.Buttons')
		);
	}

	/**
	 * Get The AddToolBarInterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SiteViewAddToolBar
	 * @since   5.1.4
	 */
	public function getSiteViewAddToolBar(Container $container): SiteViewAddToolBar
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.SiteView.J' . $this->targetVersion . '.AddToolBar');
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6SiteViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ6SiteViewAddToolBar(Container $container): J6SiteViewAddToolBar
	{
		return new J6SiteViewAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5SiteViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ5SiteViewAddToolBar(Container $container): J5SiteViewAddToolBar
	{
		return new J5SiteViewAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4SiteViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ4SiteViewAddToolBar(Container $container): J4SiteViewAddToolBar
	{
		return new J4SiteViewAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3SiteViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ3SiteViewAddToolBar(Container $container): J3SiteViewAddToolBar
	{
		return new J3SiteViewAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBarInterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomAdminAddToolBar
	 * @since   5.1.4
	 */
	public function getCustomAdminViewAddToolBar(Container $container): CustomAdminAddToolBar
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.CustomAdminView.J' . $this->targetVersion . '.AddToolBar');
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6CustomAdminViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ6CustomAdminViewAddToolBar(Container $container): J6CustomAdminViewAddToolBar
	{
		return new J6CustomAdminViewAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5CustomAdminViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ5CustomAdminViewAddToolBar(Container $container): J5CustomAdminViewAddToolBar
	{
		return new J5CustomAdminViewAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4CustomAdminViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ4CustomAdminViewAddToolBar(Container $container): J4CustomAdminViewAddToolBar
	{
		return new J4CustomAdminViewAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3CustomAdminViewAddToolBar
	 * @since   5.1.4
	 */
	public function getJ3CustomAdminViewAddToolBar(Container $container): J3CustomAdminViewAddToolBar
	{
		return new J3CustomAdminViewAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBarInterface Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomAdminAddToolBar
	 * @since   5.1.4
	 */
	public function getCustomAdminViewsAddToolBar(Container $container): CustomAdminAddToolBar
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.CustomAdminViews.J' . $this->targetVersion . '.AddToolBar');
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6CustomAdminViewsAddToolBar
	 * @since   5.1.4
	 */
	public function getJ6CustomAdminViewsAddToolBar(Container $container): J6CustomAdminViewsAddToolBar
	{
		return new J6CustomAdminViewsAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5CustomAdminViewsAddToolBar
	 * @since   5.1.4
	 */
	public function getJ5CustomAdminViewsAddToolBar(Container $container): J5CustomAdminViewsAddToolBar
	{
		return new J5CustomAdminViewsAddToolBar(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4CustomAdminViewsAddToolBar
	 * @since   5.1.4
	 */
	public function getJ4CustomAdminViewsAddToolBar(Container $container): J4CustomAdminViewsAddToolBar
	{
		return new J4CustomAdminViewsAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The AddToolBar Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3CustomAdminViewsAddToolBar
	 * @since   5.1.4
	 */
	public function getJ3CustomAdminViewsAddToolBar(Container $container): J3CustomAdminViewsAddToolBar
	{
		return new J3CustomAdminViewsAddToolBar(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Architecture.CustomButtons')
		);
	}

	/**
	 * Get The ListItemBuilder Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ListItemBuilder
	 * @since   5.1.5
	 */
	public function getAdminViewsListItemBuilder(Container $container): ListItemBuilder
	{
		return new ListItemBuilder(
			$container->get('Placeholder'),
			$container->get('Architecture.AdminViews.ListItem'),
			$container->get('Compiler.Builder.Field.Relations'),
			$container->get('Compiler.Builder.List.Join')
		);
	}

	/**
	 * Get The ListItem Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ListItem
	 * @since   5.1.5
	 */
	public function getAdminViewsListItem(Container $container): ListItem
	{
		return new ListItem(
			$container->get('Architecture.AdminViews.ListItem.ItemCode'),
			$container->get('Architecture.AdminViews.ListItem.Link'),
			$container->get('Architecture.AdminViews.ListItem.LinkAuthority'),
			$container->get('Architecture.AdminViews.ListItem.LinkLogic')
		);
	}

	/**
	 * Get The ItemCode Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ItemCode
	 * @since   5.1.5
	 */
	public function getAdminViewsListItemItemCode(Container $container): ItemCode
	{
		return new ItemCode(
			$container->get('Config'),
			$container->get('Compiler.Builder.Selection.Translation'),
			$container->get('Compiler.Builder.Do.Not.Escape')
		);
	}

	/**
	 * Get The Link Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Link
	 * @since   5.1.5
	 */
	public function getAdminViewsListItemLink(Container $container): Link
	{
		return new Link(
			$container->get('Compiler.Builder.Category')
		);
	}

	/**
	 * Get The LinkAuthority Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LinkAuthority
	 * @since   5.1.5
	 */
	public function getAdminViewsListItemLinkAuthority(Container $container): LinkAuthority
	{
		return new LinkAuthority(
			$container->get('Config'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The LinkLogic Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LinkLogic
	 * @since   5.1.5
	 */
	public function getAdminViewsListItemLinkLogic(Container $container): LinkLogic
	{
		return new LinkLogic(
			$container->get('Config')
		);
	}

	/**
	 * Get The AdminView FadeInEffect Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewFadeInEffect
	 * @since   6.1.7
	 */
	public function getAdminViewFadeInEffect(Container $container): AdminViewFadeInEffect
	{
		return new AdminViewFadeInEffect(
			$container->get('Config')
		);
	}

	/**
	 * Get The AdminView CustomTabs Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewCustomTabs
	 * @since   6.1.7
	 */
	public function getAdminViewCustomTabs(Container $container): AdminViewCustomTabs
	{
		return new AdminViewCustomTabs(
			$container->get('Compiler.Builder.Custom.Tabs')
		);
	}

	/**
	 * Get The LinkedView ListHead Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LinkedViewListHead
	 * @since   6.1.7
	 */
	public function getLinkedViewListHead(Container $container): LinkedViewListHead
	{
		return new LinkedViewListHead(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Lists'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Compiler.Builder.List.Head.Override'),
			$container->get('Compiler.Builder.Field.Names')
		);
	}

	/**
	 * Get The LinkedView ListQuery Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LinkedViewListQuery
	 * @since   6.1.7
	 */
	public function getLinkedViewListQuery(Container $container): LinkedViewListQuery
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 takes its user and database from the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.LinkedView.J3.ListQuery');
		}

		return $container->get('Architecture.LinkedView.Shared.ListQuery');
	}

	/**
	 * Get The LinkedView ListQuery Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedLinkedViewListQuery
	 * @since   6.1.7
	 */
	public function getSharedLinkedViewListQuery(Container $container): SharedLinkedViewListQuery
	{
		return new SharedLinkedViewListQuery(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Field.Database.Name'),
			$container->get('Architecture.Model.CustomQuery'),
			$container->get('Architecture.Model.ItemsStringFix'),
			$container->get('Architecture.Model.SelectionTranslation'),
			$container->get('Architecture.Model.SelectionTranslationMethod'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Views.Default.Ordering')
		);
	}

	/**
	 * Get The LinkedView ListQuery Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3LinkedViewListQuery
	 * @since   6.1.7
	 */
	public function getJ3LinkedViewListQuery(Container $container): J3LinkedViewListQuery
	{
		return new J3LinkedViewListQuery(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Field.Database.Name'),
			$container->get('Architecture.Model.CustomQuery'),
			$container->get('Architecture.Model.ItemsStringFix'),
			$container->get('Architecture.Model.SelectionTranslation'),
			$container->get('Architecture.Model.SelectionTranslationMethod'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Views.Default.Ordering')
		);
	}

	/**
	 * Get The LinkedView ListBody Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LinkedViewListBody
	 * @since   6.1.7
	 */
	public function getLinkedViewListBody(Container $container): LinkedViewListBody
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 loads the checked out user from the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.LinkedView.J3.ListBody');
		}

		return $container->get('Architecture.LinkedView.Shared.ListBody');
	}

	/**
	 * Get The LinkedView ListBody Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedLinkedViewListBody
	 * @since   6.1.7
	 */
	public function getSharedLinkedViewListBody(Container $container): SharedLinkedViewListBody
	{
		return new SharedLinkedViewListBody(
			$container->get('Config'),
			$container->get('Compiler.Builder.Lists'),
			$container->get('Architecture.AdminViews.ListItemBuilder'),
			$container->get('Architecture.AdminViews.ListLink'),
			$container->get('Compiler.Builder.Do.Not.Escape'),
			$container->get('Compiler.Builder.Field.Names')
		);
	}

	/**
	 * Get The LinkedView ListBody Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3LinkedViewListBody
	 * @since   6.1.7
	 */
	public function getJ3LinkedViewListBody(Container $container): J3LinkedViewListBody
	{
		return new J3LinkedViewListBody(
			$container->get('Config'),
			$container->get('Compiler.Builder.Lists'),
			$container->get('Architecture.AdminViews.ListItemBuilder'),
			$container->get('Architecture.AdminViews.ListLink'),
			$container->get('Compiler.Builder.Do.Not.Escape'),
			$container->get('Compiler.Builder.Field.Names')
		);
	}

	/**
	 * Get The LinkedView Builder Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LinkedViewBuilder
	 * @since   6.1.7
	 */
	public function getLinkedViewBuilder(Container $container): LinkedViewBuilder
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// Joomla 3 takes its input from the global application and reaches a
		// new record through the edit task
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.LinkedView.J3.Builder');
		}

		// seeding a new record from the parent guid arrived in Joomla 5
		if ((int) $this->targetVersion === 4)
		{
			return $container->get('Architecture.LinkedView.J4.Builder');
		}

		return $container->get('Architecture.LinkedView.Shared.Builder');
	}

	/**
	 * Get The LinkedView Builder Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedLinkedViewBuilder
	 * @since   6.1.7
	 */
	public function getSharedLinkedViewBuilder(Container $container): SharedLinkedViewBuilder
	{
		return new SharedLinkedViewBuilder(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Architecture.AdminView.FootableScripts'),
			$container->get('Architecture.LinkedView.ListBody'),
			$container->get('Architecture.LinkedView.ListHead'),
			$container->get('Architecture.LinkedView.ListQuery')
		);
	}

	/**
	 * Get The LinkedView Builder Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3LinkedViewBuilder
	 * @since   6.1.7
	 */
	public function getJ3LinkedViewBuilder(Container $container): J3LinkedViewBuilder
	{
		return new J3LinkedViewBuilder(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Architecture.AdminView.FootableScripts'),
			$container->get('Architecture.LinkedView.ListBody'),
			$container->get('Architecture.LinkedView.ListHead'),
			$container->get('Architecture.LinkedView.ListQuery')
		);
	}

	/**
	 * Get The LinkedView Builder Class for Joomla 4.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4LinkedViewBuilder
	 * @since   6.1.7
	 */
	public function getJ4LinkedViewBuilder(Container $container): J4LinkedViewBuilder
	{
		return new J4LinkedViewBuilder(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Architecture.AdminView.FootableScripts'),
			$container->get('Architecture.LinkedView.ListBody'),
			$container->get('Architecture.LinkedView.ListHead'),
			$container->get('Architecture.LinkedView.ListQuery')
		);
	}

	/**
	 * Get The AdminView FootableScripts Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewFootableScripts
	 * @since   6.1.7
	 */
	public function getAdminViewFootableScripts(Container $container): AdminViewFootableScripts
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 declares an inline script without the asset manager
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminView.J3.FootableScripts');
		}

		return $container->get('Architecture.AdminView.Shared.FootableScripts');
	}

	/**
	 * Get The AdminView FootableScripts Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewFootableScripts
	 * @since   6.1.7
	 */
	public function getSharedAdminViewFootableScripts(Container $container): SharedAdminViewFootableScripts
	{
		return new SharedAdminViewFootableScripts(
			$container->get('Config')
		);
	}

	/**
	 * Get The AdminView FootableScripts Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewFootableScripts
	 * @since   6.1.7
	 */
	public function getJ3AdminViewFootableScripts(Container $container): J3AdminViewFootableScripts
	{
		return new J3AdminViewFootableScripts(
			$container->get('Config')
		);
	}

	/**
	 * Get The AdminView EditBody Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewEditBody
	 * @since   6.1.7
	 */
	public function getAdminViewEditBody(Container $container): AdminViewEditBody
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 lays the edit view out with the Bootstrap 2 grid
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminView.J3.EditBody');
		}

		return $container->get('Architecture.AdminView.Shared.EditBody');
	}

	/**
	 * Get The AdminView EditBody Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewEditBody
	 * @since   6.1.7
	 */
	public function getSharedAdminViewEditBody(Container $container): SharedAdminViewEditBody
	{
		return new SharedAdminViewEditBody(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Registry'),
			$container->get('Adminview.Data'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Architecture.Layout.View'),
			$container->get('Architecture.AdminView.CustomTabs'),
			$container->get('Compiler.Builder.Layout'),
			$container->get('Compiler.Builder.Tab.Counter'),
			$container->get('Compiler.Builder.Second.Run.Admin'),
			$container->get('Compiler.Builder.New.Publishing.Fields'),
			$container->get('Compiler.Builder.Moved.Publishing.Fields'),
			$container->get('Compiler.Builder.Meta.Data'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Has.Permissions')
		);
	}

	/**
	 * Get The AdminView EditBody Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewEditBody
	 * @since   6.1.7
	 */
	public function getJ3AdminViewEditBody(Container $container): J3AdminViewEditBody
	{
		return new J3AdminViewEditBody(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Registry'),
			$container->get('Adminview.Data'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Architecture.Layout.View'),
			$container->get('Architecture.AdminView.CustomTabs'),
			$container->get('Compiler.Builder.Layout'),
			$container->get('Compiler.Builder.Tab.Counter'),
			$container->get('Compiler.Builder.Second.Run.Admin'),
			$container->get('Compiler.Builder.New.Publishing.Fields'),
			$container->get('Compiler.Builder.Moved.Publishing.Fields'),
			$container->get('Compiler.Builder.Meta.Data'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Has.Permissions')
		);
	}

	/**
	 * Get The AdminView TabLayoutFields Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewTabLayoutFields
	 * @since   6.1.7
	 */
	public function getAdminViewTabLayoutFields(Container $container): AdminViewTabLayoutFields
	{
		return new AdminViewTabLayoutFields(
			$container->get('Compiler.Builder.Layout')
		);
	}

	/**
	 * Get The AdminViewScript Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewScript
	 * @since   6.1.7
	 */
	public function getAdminViewScript(Container $container): AdminViewScript
	{
		return new AdminViewScript(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Customcode.Dispenser'),
			$container->get('Utilities.Structure'),
			$container->get('Library.IncludeHelper'),
			$container->get('Model.Createdate'),
			$container->get('Model.Modifieddate'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Compiler.Builder.Script.Media.Switch'),
			$container->get('Compiler.Builder.Script.User.Switch'),
			$container->get('Compiler.Builder.Validation.Fix'),
			$container->get('Compiler.Builder.View.Script'),
			$container->get('Architecture.Field.ValueScript'),
			$container->get('Architecture.Field.OptionsScript'),
			$container->get('Architecture.Field.IfValueScript'),
			$container->get('Architecture.Field.TargetControlsScript'),
			$container->get('Architecture.Field.TargetRelationScript')
		);
	}

	/**
	 * Get The RouteHelper Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  RouteHelperInterface
	 * @since   6.1.7
	 */
	public function getRouteHelper(Container $container): RouteHelperInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 finds a link's menu item through the core needle lookup
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Router.J3.RouteHelper');
		}

		return $container->get('Architecture.Router.Shared.RouteHelper');
	}

	/**
	 * Get The Router RouteHelper Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedRouteHelper
	 * @since   6.1.7
	 */
	public function getSharedRouteHelper(Container $container): SharedRouteHelper
	{
		return new SharedRouteHelper(
			$container->get('Config'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Has.Menu.Global'),
			$container->get('Compiler.Builder.Tags')
		);
	}

	/**
	 * Get The RouteHelper Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3RouteHelper
	 * @since   6.1.7
	 */
	public function getJ3RouteHelper(Container $container): J3RouteHelper
	{
		return new J3RouteHelper(
			$container->get('Config'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Has.Menu.Global'),
			$container->get('Compiler.Builder.Tags')
		);
	}

	/**
	 * Get The SiteRouter Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SiteRouter
	 * @since   6.1.7
	 */
	public function getSiteRouter(Container $container): SiteRouter
	{
		return new SiteRouter(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Utilities.Structure'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi')
		);
	}

	/**
	 * Get The Layout View Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LayoutView
	 * @since   6.1.7
	 */
	public function getLayoutView(Container $container): LayoutView
	{
		return new LayoutView(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Compiler.Builder.Layout.Data'),
			$container->get('Templatelayout.Data'),
			$container->get('Header'),
			$container->get('Utilities.Structure')
		);
	}

	/**
	 * Get The AdminViews ListLink Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsListLink
	 * @since   6.1.7
	 */
	public function getAdminViewsListLink(Container $container): AdminViewsListLink
	{
		return new AdminViewsListLink(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Custom.Admin.View.List.Link'),
			$container->get('Compiler.Builder.Custom.Admin.View.List.Id'),
			$container->get('Compiler.Builder.Custom.Admin.Added'),
			$container->get('Compiler.Builder.Dynamic.Buttons')
		);
	}

	/**
	 * Get The AdminViews ViewBody Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsViewBody
	 * @since   6.1.7
	 */
	public function getAdminViewsViewBody(Container $container): AdminViewsViewBody
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 opens a different container and batch modal
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.ViewBody');
		}

		return $container->get('Architecture.AdminViews.Shared.ViewBody');
	}

	/**
	 * Get The AdminViews ViewBody Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsViewBody
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsViewBody(Container $container): SharedAdminViewsViewBody
	{
		return new SharedAdminViewsViewBody(
			$container->get('Config'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Templatelayout.Data'),
			$container->get('Event')
		);
	}

	/**
	 * Get The AdminViews ViewBody Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsViewBody
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsViewBody(Container $container): J3AdminViewsViewBody
	{
		return new J3AdminViewsViewBody(
			$container->get('Config'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Templatelayout.Data'),
			$container->get('Event')
		);
	}

	/**
	 * Get The AdminViews ListHead Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsListHead
	 * @since   6.1.7
	 */
	public function getAdminViewsListHead(Container $container): AdminViewsListHead
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 guards its sorting differently
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.ListHead');
		}

		return $container->get('Architecture.AdminViews.Shared.ListHead');
	}

	/**
	 * Get The AdminViews ListHead Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsListHead
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsListHead(Container $container): SharedAdminViewsListHead
	{
		return new SharedAdminViewsListHead(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Lists'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.List.Head.Override'),
			$container->get('Compiler.Builder.List.Column.Number')
		);
	}

	/**
	 * Get The AdminViews ListHead Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsListHead
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsListHead(Container $container): J3AdminViewsListHead
	{
		return new J3AdminViewsListHead(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Lists'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.List.Head.Override'),
			$container->get('Compiler.Builder.List.Column.Number')
		);
	}

	/**
	 * Get The AdminViews ListBody Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsListBody
	 * @since   6.1.7
	 */
	public function getAdminViewsListBody(Container $container): AdminViewsListBody
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 loads the checked out user from the global factory
		// and carries no modal guard on its permission tests
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.ListBody');
		}

		return $container->get('Architecture.AdminViews.Shared.ListBody');
	}

	/**
	 * Get The AdminViews FilterSet Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsFilterSet
	 * @since   6.1.7
	 */
	public function getAdminViewsFilterSet(Container $container): AdminViewsFilterSet
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only a Joomla 3 filter is styled and submitted the way it was
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.FilterSet');
		}

		return $container->get('Architecture.AdminViews.Shared.FilterSet');
	}

	/**
	 * Get The AdminViews FilterSet Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsFilterSet
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsFilterSet(Container $container): SharedAdminViewsFilterSet
	{
		return new SharedAdminViewsFilterSet(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Utilities.Structure'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Filter')
		);
	}

	/**
	 * Get The Joomla 3 AdminViews FilterSet Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsFilterSet
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsFilterSet(Container $container): J3AdminViewsFilterSet
	{
		return new J3AdminViewsFilterSet(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Utilities.Structure'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Filter')
		);
	}

	/**
	 * Get The AdminViews FilterListSet Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsFilterListSet
	 * @since   6.1.7
	 */
	public function getAdminViewsFilterListSet(Container $container): AdminViewsFilterListSet
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only a Joomla 3 filter is styled and submitted the way it was
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.FilterListSet');
		}

		return $container->get('Architecture.AdminViews.Shared.FilterListSet');
	}

	/**
	 * Get The AdminViews FilterListSet Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsFilterListSet
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsFilterListSet(Container $container): SharedAdminViewsFilterListSet
	{
		return new SharedAdminViewsFilterListSet(
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Sort'),
			$container->get('Adminview.DefaultOrdering')
		);
	}

	/**
	 * Get The Joomla 3 AdminViews FilterListSet Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsFilterListSet
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsFilterListSet(Container $container): J3AdminViewsFilterListSet
	{
		return new J3AdminViewsFilterListSet(
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Sort'),
			$container->get('Adminview.DefaultOrdering')
		);
	}

	/**
	 * Get The AdminViews EditView Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsEditView
	 * @since   6.1.7
	 */
	public function getAdminViewsEditView(Container $container): AdminViewsEditView
	{
		return new AdminViewsEditView(
			$container->get('Config'),
			$container->get('Event'),
			$container->get('Header'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Compiler.Creator.Fieldset'),
			$container->get('Architecture.AdminView.AddToolBar'),
			$container->get('Architecture.AdminView.AddModalToolBar'),
			$container->get('Architecture.AdminView.ViewScript'),
			$container->get('Architecture.AdminView.TabLayoutFields'),
			$container->get('Architecture.AdminView.EditBody'),
			$container->get('Architecture.AdminView.FadeInEffect'),
			$container->get('Architecture.Menu.AdminView'),
			$container->get('Architecture.View.AjaxToken'),
			$container->get('Architecture.View.Jquery'),
			$container->get('Architecture.Model.CheckboxSave'),
			$container->get('Architecture.Model.GetItemMethod'),
			$container->get('Architecture.Model.ItemSave'),
			$container->get('Architecture.Model.ValidationFix'),
			$container->get('Architecture.Field.SetAccessControl'),
			$container->get('Architecture.Table.Constructor'),
			$container->get('Architecture.Component.LicenseLock'),
			$container->get('Architecture.Api.Controller.GetModel'),
			$container->get('Architecture.Api.Controller.RecordId'),
			$container->get('Architecture.Api.Controller.AllowView'),
			$container->get('Architecture.Api.Controller.AllowDelete'),
			$container->get('Architecture.Api.View.Fields'),
			$container->get('Architecture.Api.View.FieldPermissions'),
			$container->get('Architecture.Api.View.PrepareItem'),
			$container->get('Architecture.Api.View.Relationships'),
			$container->get('Architecture.Api.Serializer.Relations')
		);
	}

	/**
	 * Get The AdminViews ListView Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsListView
	 * @since   6.1.7
	 */
	public function getAdminViewsListView(Container $container): AdminViewsListView
	{
		return new AdminViewsListView(
			$container->get('Config'),
			$container->get('Event'),
			$container->get('Header'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Architecture.AdminView.ViewScript'),
			$container->get('Architecture.View.Jquery'),
			$container->get('Architecture.CustomButtons'),
			$container->get('Architecture.Model.CheckInNow'),
			$container->get('Compiler.Builder.Only.Function.Buttons'),
			$container->get('Compiler.Builder.Eximport.View'),
			$container->get('Compiler.Builder.Import.Custom.Scripts'),
			$container->get('Compiler.Builder.List.Column.Number'),
			$container->get('Architecture.AdminViews.DisplayMethod'),
			$container->get('Architecture.AdminViews.BatchOptions'),
			$container->get('Architecture.Controller.EximportMethod'),
			$container->get('Architecture.AdminViews.ViewBody'),
			$container->get('Architecture.AdminViews.EximportButtons'),
			$container->get('Architecture.AdminViews.FilterListSet'),
			$container->get('Architecture.AdminViews.FilterSet'),
			$container->get('Architecture.AdminViews.FilterFieldHelper'),
			$container->get('Architecture.AdminViews.SidebarFilters'),
			$container->get('Architecture.Model.FilterFields'),
			$container->get('Architecture.Model.ItemsStringFix'),
			$container->get('Architecture.Model.ItemsMethod'),
			$container->get('Architecture.Component.ImportCustomScripts'),
			$container->get('Architecture.AdminViews.CanDo'),
			$container->get('Architecture.AdminViews.ListBody'),
			$container->get('Architecture.AdminViews.ListHead'),
			$container->get('Architecture.Model.ListQuery'),
			$container->get('Architecture.Model.PopulateState'),
			$container->get('Architecture.Router.SiteRouter'),
			$container->get('Architecture.Model.SelectionTranslation'),
			$container->get('Architecture.Model.SelectionTranslationMethod'),
			$container->get('Architecture.Model.SortFields'),
			$container->get('Architecture.Model.StoredId'),
			$container->get('Architecture.Api.Controller.GetModel'),
			$container->get('Architecture.Api.Controller.DisplayList'),
			$container->get('Architecture.Api.View.Fields'),
			$container->get('Architecture.Api.View.FieldPermissions'),
			$container->get('Architecture.Api.View.PrepareItem'),
			$container->get('Architecture.Api.View.Relationships')
		);
	}

	/**
	 * Get The AdminViews Shared Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsShared
	 * @since   6.1.7
	 */
	public function getAdminViewsShared(Container $container): AdminViewsShared
	{
		return new AdminViewsShared(
			$container->get('Event'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Architecture.Router.SiteRouter'),
			$container->get('Architecture.AdminViews.AddToolBar'),
			$container->get('Architecture.Controller.AllowAdd'),
			$container->get('Architecture.Controller.AllowEdit'),
			$container->get('Architecture.DynamicButtons'),
			$container->get('Architecture.Model.AllowEdit'),
			$container->get('Architecture.Model.CanDelete'),
			$container->get('Architecture.Model.CanEditState'),
			$container->get('Compiler.Creator.Access.Sections.Category'),
			$container->get('Compiler.Creator.Access.Sections.Joomla.Fields'),
			$container->get('Architecture.Model.AliasTitleFix'),
			$container->get('Architecture.Model.BatchCopy'),
			$container->get('Architecture.Model.BatchMove'),
			$container->get('Architecture.Model.GenerateNewAlias'),
			$container->get('Architecture.Model.GenerateNewTitle'),
			$container->get('Architecture.Model.GetForm'),
			$container->get('Architecture.Router.RouteHelper'),
			$container->get('Architecture.Model.UniqueFields'),
			$container->get('Architecture.Controller.CustomAdminDynamicButton')
		);
	}

	/**
	 * Get The AdminViews Loop Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsLoop
	 * @since   6.1.7
	 */
	public function getAdminViewsLoop(Container $container): AdminViewsLoop
	{
		return new AdminViewsLoop(
			$container->get('Config'),
			$container->get('Component'),
			$container->get('Compiler.Creator.Helper'),
			$container->get('Architecture.AdminViews.EditView'),
			$container->get('Architecture.AdminViews.ListView'),
			$container->get('Architecture.AdminViews.Shared'),
			$container->get('Architecture.AdminViews.ListLink'),
			$container->get('Architecture.View.Placeholders')
		);
	}

	/**
	 * Get The CustomAdminViews Builder Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomAdminViewsBuilder
	 * @since   6.1.7
	 */
	public function getCustomAdminViewsBuilder(Container $container): CustomAdminViewsBuilder
	{
		return new CustomAdminViewsBuilder(
			$container->get('Config'),
			$container->get('Event'),
			$container->get('Header'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Architecture.Component.LicenseLock'),
			$container->get('Component'),
			$container->get('Language'),
			$container->get('Registry'),
			$container->get('Placeholder'),
			$container->get('Architecture.CustomAdminView.AddToolBar'),
			$container->get('Architecture.CustomAdminViews.AddToolBar'),
			$container->get('Dynamicget.CustomGetMethods'),
			$container->get('Dynamicget.GetItem'),
			$container->get('Dynamicget.GetItems'),
			$container->get('Dynamicget.ListQuery'),
			$container->get('Dynamicget.Methods'),
			$container->get('Architecture.CustomView.Body'),
			$container->get('Architecture.CustomView.DisplayMethod'),
			$container->get('Architecture.CustomView.Form'),
			$container->get('Architecture.CustomView.Layouts'),
			$container->get('Architecture.CustomView.TemplateBody'),
			$container->get('Architecture.CustomView.SubmitButtonScript'),
			$container->get('Architecture.CustomView.CodeBody'),
			$container->get('Architecture.CustomView.ExtraDisplayMethods'),
			$container->get('Architecture.View.PrepareDocument'),
			$container->get('Architecture.Api.Dynamic.Resource')
		);
	}

	/**
	 * Get The SiteViews ModelData Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SiteViewsModelData
	 * @since   6.1.7
	 */
	public function getSiteViewsModelData(Container $container): SiteViewsModelData
	{
		return new SiteViewsModelData(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Dynamicget.GetItem'),
			$container->get('Dynamicget.GetItems'),
			$container->get('Dynamicget.ListQuery'),
			$container->get('Architecture.ComHelperClass.UserPermissionCheckAccess')
		);
	}

	/**
	 * Get The SiteViews Headers Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SiteViewsHeaders
	 * @since   6.1.7
	 */
	public function getSiteViewsHeaders(Container $container): SiteViewsHeaders
	{
		return new SiteViewsHeaders(
			$container->get('Header'),
			$container->get('Compiler.Builder.Content.Multi')
		);
	}

	/**
	 * Get The SiteViews Builder Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SiteViewsBuilder
	 * @since   6.1.7
	 */
	public function getSiteViewsBuilder(Container $container): SiteViewsBuilder
	{
		return new SiteViewsBuilder(
			$container->get('Config'),
			$container->get('Event'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Architecture.Component.LicenseLock'),
			$container->get('Architecture.Router.SiteRouter'),
			$container->get('Architecture.Router.RouteHelper'),
			$container->get('Language'),
			$container->get('Placeholder'),
			$container->get('Dynamicget.CustomGetMethods'),
			$container->get('Dynamicget.Methods'),
			$container->get('Architecture.CustomView.Body'),
			$container->get('Architecture.CustomView.DisplayMethod'),
			$container->get('Architecture.CustomView.Form'),
			$container->get('Architecture.CustomView.TemplateBody'),
			$container->get('Architecture.CustomView.CodeBody'),
			$container->get('Architecture.CustomView.ExtraDisplayMethods'),
			$container->get('Architecture.View.PrepareDocument'),
			$container->get('Architecture.Menu.CustomView'),
			$container->get('Architecture.SiteView.AddToolBar'),
			$container->get('Architecture.SiteViews.ModelData'),
			$container->get('Architecture.SiteViews.Headers'),
			$container->get('Architecture.Api.Dynamic.Resource')
		);
	}

	/**
	 * Get The CustomView CodeBody Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomViewCodeBody
	 * @since   6.1.7
	 */
	public function getCustomViewCodeBody(Container $container): CustomViewCodeBody
	{
		return new CustomViewCodeBody(
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The CustomView ExtraDisplayMethods Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomViewExtraDisplayMethods
	 * @since   6.1.7
	 */
	public function getCustomViewExtraDisplayMethods(Container $container): CustomViewExtraDisplayMethods
	{
		return new CustomViewExtraDisplayMethods(
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The AdminViews FilterFieldFile Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsFilterFieldFile
	 * @since   6.1.7
	 */
	public function getAdminViewsFilterFieldFile(Container $container): AdminViewsFilterFieldFile
	{
		return new AdminViewsFilterFieldFile(
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Utilities.Structure')
		);
	}

	/**
	 * Get The AdminViews FilterFieldHelper Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsFilterFieldHelper
	 * @since   6.1.7
	 */
	public function getAdminViewsFilterFieldHelper(Container $container): AdminViewsFilterFieldHelper
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 takes its database and user from the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.FilterFieldHelper');
		}

		return $container->get('Architecture.AdminViews.Shared.FilterFieldHelper');
	}

	/**
	 * Get The AdminViews FilterFieldHelper Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsFilterFieldHelper
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsFilterFieldHelper(Container $container): SharedAdminViewsFilterFieldHelper
	{
		return new SharedAdminViewsFilterFieldHelper(
			$container->get('Config'),
			$container->get('Compiler.Builder.Filter'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Selection.Translation'),
			$container->get('Architecture.Field.CustomFieldCode'),
			$container->get('Architecture.AdminViews.FilterFieldFile')
		);
	}

	/**
	 * Get The AdminViews FilterFieldHelper Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsFilterFieldHelper
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsFilterFieldHelper(Container $container): J3AdminViewsFilterFieldHelper
	{
		return new J3AdminViewsFilterFieldHelper(
			$container->get('Config'),
			$container->get('Compiler.Builder.Filter'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Selection.Translation'),
			$container->get('Architecture.Field.CustomFieldCode'),
			$container->get('Architecture.AdminViews.FilterFieldFile')
		);
	}

	/**
	 * Get The AdminViews ListBody Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsListBody
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsListBody(Container $container): SharedAdminViewsListBody
	{
		return new SharedAdminViewsListBody(
			$container->get('Compiler.Creator.Permission'),
			$container->get('Compiler.Builder.Lists'),
			$container->get('Architecture.AdminViews.ListItemBuilder'),
			$container->get('Architecture.AdminViews.ListLink'),
			$container->get('Compiler.Builder.List.Field.Class'),
			$container->get('Compiler.Builder.Do.Not.Escape'),
			$container->get('Compiler.Builder.Field.Names')
		);
	}

	/**
	 * Get The AdminViews ListBody Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsListBody
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsListBody(Container $container): J3AdminViewsListBody
	{
		return new J3AdminViewsListBody(
			$container->get('Compiler.Creator.Permission'),
			$container->get('Compiler.Builder.Lists'),
			$container->get('Architecture.AdminViews.ListItemBuilder'),
			$container->get('Architecture.AdminViews.ListLink'),
			$container->get('Compiler.Builder.List.Field.Class'),
			$container->get('Compiler.Builder.Do.Not.Escape'),
			$container->get('Compiler.Builder.Field.Names')
		);
	}

	/**
	 * Get The AdminViews DisplayMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsDisplayMethod
	 * @since   6.1.7
	 */
	public function getAdminViewsDisplayMethod(Container $container): AdminViewsDisplayMethod
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 renders a different filter form
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.DisplayMethod');
		}

		return $container->get('Architecture.AdminViews.Shared.DisplayMethod');
	}

	/**
	 * Get The AdminViews DisplayMethod Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsDisplayMethod
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsDisplayMethod(Container $container): SharedAdminViewsDisplayMethod
	{
		return new SharedAdminViewsDisplayMethod(
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Adminview.DefaultOrdering')
		);
	}

	/**
	 * Get The AdminViews DisplayMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsDisplayMethod
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsDisplayMethod(Container $container): J3AdminViewsDisplayMethod
	{
		return new J3AdminViewsDisplayMethod(
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Adminview.DefaultOrdering')
		);
	}

	/**
	 * Get The CustomView DisplayMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomViewDisplayMethod
	 * @since   6.1.7
	 */
	public function getCustomViewDisplayMethod(Container $container): CustomViewDisplayMethod
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// Joomla 3 and 4 still dispatch plugin events the legacy way
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.CustomView.J3.DisplayMethod');
		}

		if ((int) $this->targetVersion === 4)
		{
			return $container->get('Architecture.CustomView.J4.DisplayMethod');
		}

		return $container->get('Architecture.CustomView.Shared.DisplayMethod');
	}

	/**
	 * Get The CustomView DisplayMethod Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedCustomViewDisplayMethod
	 * @since   6.1.7
	 */
	public function getSharedCustomViewDisplayMethod(Container $container): SharedCustomViewDisplayMethod
	{
		return new SharedCustomViewDisplayMethod(
			$container->get('Config'),
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The CustomView DisplayMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4CustomViewDisplayMethod
	 * @since   6.1.7
	 */
	public function getJ4CustomViewDisplayMethod(Container $container): J4CustomViewDisplayMethod
	{
		return new J4CustomViewDisplayMethod(
			$container->get('Config'),
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The CustomView DisplayMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3CustomViewDisplayMethod
	 * @since   6.1.7
	 */
	public function getJ3CustomViewDisplayMethod(Container $container): J3CustomViewDisplayMethod
	{
		return new J3CustomViewDisplayMethod(
			$container->get('Config'),
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The Menu AdminView Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MenuAdminView
	 * @since   6.1.7
	 */
	public function getMenuAdminView(Container $container): MenuAdminView
	{
		return new MenuAdminView(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Utilities.Structure')
		);
	}

	/**
	 * Get The CustomMainMenu Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MenuCustomMainMenu
	 * @since   6.1.7
	 */
	public function getMenuCustomMainMenu(Container $container): MenuCustomMainMenu
	{
		return new MenuCustomMainMenu(
			$container->get('Component'),
			$container->get('Language')
		);
	}

	/**
	 * Get The MenuCustomSubMenu Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MenuCustomSubMenu
	 * @since   6.1.7
	 */
	public function getMenuCustomSubMenu(Container $container): MenuCustomSubMenu
	{
		return new MenuCustomSubMenu(
			$container->get('Component'),
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The MenuSubMenus Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MenuSubMenus
	 * @since   6.1.7
	 */
	public function getMenuSubMenus(Container $container): MenuSubMenus
	{
		return new MenuSubMenus(
			$container->get('Component'),
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Registry'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name'),
			$container->get('Compiler.Builder.Uninstall.Script.Context'),
			$container->get('Compiler.Builder.Uninstall.Script.Fields'),
			$container->get('Architecture.Menu.CustomSubMenu')
		);
	}

	/**
	 * Get The MenuMainMenus Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MenuMainMenusInterface
	 * @since   6.1.7
	 */
	public function getMenuMainMenus(Container $container): MenuMainMenusInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 has no default dashboard for a component to reach
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Menu.J3.MainMenus');
		}

		return $container->get('Architecture.Menu.Shared.MainMenus');
	}

	/**
	 * Get The Menu MainMenus Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedMenuMainMenus
	 * @since   6.1.7
	 */
	public function getSharedMenuMainMenus(Container $container): SharedMenuMainMenus
	{
		return new SharedMenuMainMenus(
			$container->get('Component'),
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Registry'),
			$container->get('Architecture.Menu.CustomMainMenu')
		);
	}

	/**
	 * Get The MenuMainMenus Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3MenuMainMenus
	 * @since   6.1.7
	 */
	public function getJ3MenuMainMenus(Container $container): J3MenuMainMenus
	{
		return new J3MenuMainMenus(
			$container->get('Component'),
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Registry'),
			$container->get('Architecture.Menu.CustomMainMenu')
		);
	}

	/**
	 * Get The UikitLoader Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewUikitLoader
	 * @since   6.1.7
	 */
	public function getViewUikitLoader(Container $container): ViewUikitLoader
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// Joomla 6 does not carry uikit at all
		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Architecture.View.J6.UikitLoader');
		}

		return $container->get('Architecture.View.Shared.UikitLoader');
	}

	/**
	 * Get The UikitLoader Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedViewUikitLoader
	 * @since   6.1.7
	 */
	public function getSharedViewUikitLoader(Container $container): SharedViewUikitLoader
	{
		return new SharedViewUikitLoader(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Site.Field.Data'),
			$container->get('Compiler.Builder.Uikit.Comp')
		);
	}

	/**
	 * Get The UikitLoader Class for Joomla 6.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ViewUikitLoader
	 * @since   6.1.7
	 */
	public function getJ6ViewUikitLoader(Container $container): J6ViewUikitLoader
	{
		return new J6ViewUikitLoader(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Site.Field.Data'),
			$container->get('Compiler.Builder.Uikit.Comp')
		);
	}

	/**
	 * Get The Menu CustomView Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MenuCustomViewInterface
	 * @since   6.1.7
	 */
	public function getMenuCustomView(Container $container): MenuCustomViewInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 uses unprefixed path attributes
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Menu.J3.CustomView');
		}

		return $container->get('Architecture.Menu.Shared.CustomView');
	}

	/**
	 * Get The Menu CustomView Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedMenuCustomView
	 * @since   6.1.7
	 */
	public function getSharedMenuCustomView(Container $container): SharedMenuCustomView
	{
		return new SharedMenuCustomView(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Frontend.Params'),
			$container->get('Compiler.Builder.Request'),
			$container->get('Utilities.Structure')
		);
	}

	/**
	 * Get The Menu CustomView Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3MenuCustomView
	 * @since   6.1.7
	 */
	public function getJ3MenuCustomView(Container $container): J3MenuCustomView
	{
		return new J3MenuCustomView(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Frontend.Params'),
			$container->get('Compiler.Builder.Request'),
			$container->get('Utilities.Structure')
		);
	}

	/**
	 * Get The View DocumentMetadata Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewDocumentMetadataInterface
	 * @since   6.1.7
	 */
	public function getViewDocumentMetadata(Container $container): ViewDocumentMetadataInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 reaches its document through a property of the view
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.View.J3.DocumentMetadata');
		}

		return $container->get('Architecture.View.Shared.DocumentMetadata');
	}

	/**
	 * Get The View DocumentMetadata Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedViewDocumentMetadata
	 * @since   6.1.7
	 */
	public function getSharedViewDocumentMetadata(Container $container): SharedViewDocumentMetadata
	{
		return new SharedViewDocumentMetadata();
	}

	/**
	 * Get The Joomla 3 View DocumentMetadata Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ViewDocumentMetadata
	 * @since   6.1.7
	 */
	public function getJ3ViewDocumentMetadata(Container $container): J3ViewDocumentMetadata
	{
		return new J3ViewDocumentMetadata();
	}

	/**
	 * Get The View DocumentInlineAssets Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewDocumentInlineAssetsInterface
	 * @since   6.1.7
	 */
	public function getViewDocumentInlineAssets(Container $container): ViewDocumentInlineAssetsInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 declares its inline assets on the document itself
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.View.J3.DocumentInlineAssets');
		}

		return $container->get('Architecture.View.Shared.DocumentInlineAssets');
	}

	/**
	 * Get The View DocumentInlineAssets Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedViewDocumentInlineAssets
	 * @since   6.1.7
	 */
	public function getSharedViewDocumentInlineAssets(Container $container): SharedViewDocumentInlineAssets
	{
		return new SharedViewDocumentInlineAssets(
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The Joomla 3 View DocumentInlineAssets Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ViewDocumentInlineAssets
	 * @since   6.1.7
	 */
	public function getJ3ViewDocumentInlineAssets(Container $container): J3ViewDocumentInlineAssets
	{
		return new J3ViewDocumentInlineAssets(
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The View DocumentCustomPHP Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewDocumentCustomPHP
	 * @since   6.1.7
	 */
	public function getViewDocumentCustomPHP(Container $container): ViewDocumentCustomPHP
	{
		return new ViewDocumentCustomPHP(
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The View CustomCSS Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewCustomCSS
	 * @since   6.1.7
	 */
	public function getViewCustomCSS(Container $container): ViewCustomCSS
	{
		return new ViewCustomCSS(
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The View GoogleChartLoader Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewGoogleChartLoader
	 * @since   6.1.7
	 */
	public function getViewGoogleChartLoader(Container $container): ViewGoogleChartLoader
	{
		return new ViewGoogleChartLoader(
			$container->get('Config'),
			$container->get('Compiler.Builder.Google.Chart')
		);
	}

	/**
	 * Get The View FootableScriptsLoader Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewFootableScriptsLoader
	 * @since   6.1.7
	 */
	public function getViewFootableScriptsLoader(Container $container): ViewFootableScriptsLoader
	{
		return new ViewFootableScriptsLoader(
			$container->get('Config'),
			$container->get('Compiler.Builder.Footable.Scripts'),
			$container->get('Architecture.AdminView.FootableScripts')
		);
	}

	/**
	 * Get The View LibrariesLoader Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewLibrariesLoaderInterface
	 * @since   6.1.7
	 */
	public function getViewLibrariesLoader(Container $container): ViewLibrariesLoaderInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 has to require the header checker before it can be used
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.View.J3.LibrariesLoader');
		}

		return $container->get('Architecture.View.Shared.LibrariesLoader');
	}

	/**
	 * Get The View LibrariesLoader Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedViewLibrariesLoader
	 * @since   6.1.7
	 */
	public function getSharedViewLibrariesLoader(Container $container): SharedViewLibrariesLoader
	{
		return new SharedViewLibrariesLoader(
			$container->get('Config'),
			$container->get('Registry'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Library.Manager'),
			$container->get('Library.Document')
		);
	}

	/**
	 * Get The Joomla 3 View LibrariesLoader Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ViewLibrariesLoader
	 * @since   6.1.7
	 */
	public function getJ3ViewLibrariesLoader(Container $container): J3ViewLibrariesLoader
	{
		return new J3ViewLibrariesLoader(
			$container->get('Config'),
			$container->get('Registry'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Library.Manager'),
			$container->get('Library.Document')
		);
	}

	/**
	 * Get The View JavaScriptFile Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewJavaScriptFile
	 * @since   6.1.7
	 */
	public function getViewJavaScriptFile(Container $container): ViewJavaScriptFile
	{
		return new ViewJavaScriptFile(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Utilities.Structure'),
			$container->get('Model.Createdate'),
			$container->get('Model.Modifieddate'),
			$container->get('Library.IncludeHelper')
		);
	}

	/**
	 * Get The View GetModules Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewGetModules
	 * @since   6.1.7
	 */
	public function getViewGetModules(Container $container): ViewGetModules
	{
		return new ViewGetModules(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Compiler.Builder.Get.Module')
		);
	}

	/**
	 * Get The View Placeholders Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewPlaceholders
	 * @since   6.1.7
	 */
	public function getViewPlaceholders(Container $container): ViewPlaceholders
	{
		return new ViewPlaceholders(
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.Multi')
		);
	}

	/**
	 * Get The View PrepareDocument Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewPrepareDocument
	 * @since   6.1.7
	 */
	public function getViewPrepareDocument(Container $container): ViewPrepareDocument
	{
		return new ViewPrepareDocument(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Architecture.View.LibrariesLoader'),
			$container->get('Architecture.View.UikitLoader'),
			$container->get('Architecture.View.GoogleChartLoader'),
			$container->get('Architecture.View.FootableScriptsLoader'),
			$container->get('Architecture.View.DocumentMetadata'),
			$container->get('Architecture.View.DocumentCustomPHP'),
			$container->get('Architecture.View.DocumentInlineAssets'),
			$container->get('Architecture.View.CustomCSS'),
			$container->get('Architecture.CustomButtons'),
			$container->get('Architecture.View.GetModules'),
			$container->get('Architecture.View.JavaScriptFile')
		);
	}

	/**
	 * Get The View AjaxToken Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewAjaxTokenInterface
	 * @since   6.1.7
	 */
	public function getViewAjaxToken(Container $container): ViewAjaxTokenInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 declares the token straight on the document
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.View.J3.AjaxToken');
		}

		return $container->get('Architecture.View.Shared.AjaxToken');
	}

	/**
	 * Get The View AjaxToken Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedViewAjaxToken
	 * @since   6.1.7
	 */
	public function getSharedViewAjaxToken(Container $container): SharedViewAjaxToken
	{
		return new SharedViewAjaxToken(
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The Joomla 3 View AjaxToken Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ViewAjaxToken
	 * @since   6.1.7
	 */
	public function getJ3ViewAjaxToken(Container $container): J3ViewAjaxToken
	{
		return new J3ViewAjaxToken(
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The AdminViews SidebarFilters Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsSidebarFiltersInterface
	 * @since   6.1.7
	 */
	public function getAdminViewsSidebarFilters(Container $container): AdminViewsSidebarFiltersInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 puts the filters of a list view in a sidebar
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.SidebarFilters');
		}

		return $container->get('Architecture.AdminViews.Shared.SidebarFilters');
	}

	/**
	 * Get The AdminViews SidebarFilters Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsSidebarFilters
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsSidebarFilters(Container $container): SharedAdminViewsSidebarFilters
	{
		return new SharedAdminViewsSidebarFilters();
	}

	/**
	 * Get The Joomla 3 AdminViews SidebarFilters Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsSidebarFilters
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsSidebarFilters(Container $container): J3AdminViewsSidebarFilters
	{
		return new J3AdminViewsSidebarFilters(
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Filter'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Category')
		);
	}

	/**
	 * Get The AdminViews BatchOptions Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsBatchOptionsInterface
	 * @since   6.1.7
	 */
	public function getAdminViewsBatchOptions(Container $container): AdminViewsBatchOptionsInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 builds the batch options of a list view from a helper
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.BatchOptions');
		}

		return $container->get('Architecture.AdminViews.Shared.BatchOptions');
	}

	/**
	 * Get The AdminViews BatchOptions Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsBatchOptions
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsBatchOptions(Container $container): SharedAdminViewsBatchOptions
	{
		return new SharedAdminViewsBatchOptions();
	}

	/**
	 * Get The Joomla 3 AdminViews BatchOptions Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsBatchOptions
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsBatchOptions(Container $container): J3AdminViewsBatchOptions
	{
		return new J3AdminViewsBatchOptions(
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Filter'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Component')
		);
	}

	/**
	 * Get The CustomView Body Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomViewBody
	 * @since   6.1.7
	 */
	public function getCustomViewBody(Container $container): CustomViewBody
	{
		return new CustomViewBody(
			$container->get('Compiler.Builder.Custom.Form'),
			$container->get('Config'),
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The CustomView TemplateBody Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomViewTemplateBody
	 * @since   6.1.7
	 */
	public function getCustomViewTemplateBody(Container $container): CustomViewTemplateBody
	{
		return new CustomViewTemplateBody(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Compiler.Builder.Template.Data'),
			$container->get('Model.Createdate'),
			$container->get('Model.Modifieddate'),
			$container->get('Utilities.Structure')
		);
	}

	/**
	 * Get The CustomView Layouts Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomViewLayouts
	 * @since   6.1.7
	 */
	public function getCustomViewLayouts(Container $container): CustomViewLayouts
	{
		return new CustomViewLayouts(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.Multi'),
			$container->get('Compiler.Builder.Layout.Data'),
			$container->get('Utilities.Structure'),
			$container->get('Header')
		);
	}

	/**
	 * Get The CustomView SubmitButtonScript Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomViewSubmitButtonScript
	 * @since   6.1.7
	 */
	public function getCustomViewSubmitButtonScript(Container $container): CustomViewSubmitButtonScript
	{
		return new CustomViewSubmitButtonScript();
	}

	/**
	 * Get The Field ClearValueScript Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldClearValueScript
	 * @since   6.1.7
	 */
	public function getFieldClearValueScript(Container $container): FieldClearValueScript
	{
		return new FieldClearValueScript();
	}

	/**
	 * Get The View Jquery Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ViewJquery
	 * @since   6.1.7
	 */
	public function getViewJquery(Container $container): ViewJquery
	{
		return new ViewJquery();
	}

	/**
	 * Get The AdminViews EximportButtons Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsEximportButtons
	 * @since   6.1.7
	 */
	public function getAdminViewsEximportButtons(Container $container): AdminViewsEximportButtons
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only a Joomla 3 list view was ever given these buttons
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.AdminViews.J3.EximportButtons');
		}

		return $container->get('Architecture.AdminViews.Shared.EximportButtons');
	}

	/**
	 * Get The AdminViews EximportButtons Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedAdminViewsEximportButtons
	 * @since   6.1.7
	 */
	public function getSharedAdminViewsEximportButtons(Container $container): SharedAdminViewsEximportButtons
	{
		return new SharedAdminViewsEximportButtons();
	}

	/**
	 * Get The Joomla 3 AdminViews EximportButtons Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3AdminViewsEximportButtons
	 * @since   6.1.7
	 */
	public function getJ3AdminViewsEximportButtons(Container $container): J3AdminViewsEximportButtons
	{
		return new J3AdminViewsEximportButtons(
			$container->get('Config'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Eximport.View')
		);
	}

	/**
	 * Get The CustomView Form Class of the target being built.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomViewFormInterface
	 * @since   6.1.7
	 */
	public function getCustomViewForm(Container $container): CustomViewFormInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 6 carries the component in the route rather than the query
		if ((int) $this->targetVersion >= 6)
		{
			return $container->get('Architecture.CustomView.J6.Form');
		}

		return $container->get('Architecture.CustomView.Shared.Form');
	}

	/**
	 * Get The CustomView Form Class shared by every earlier target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedCustomViewForm
	 * @since   6.1.7
	 */
	public function getSharedCustomViewForm(Container $container): SharedCustomViewForm
	{
		return new SharedCustomViewForm(
			$container->get('Config'),
			$container->get('Compiler.Builder.Custom.Form'),
			$container->get('Compiler.Builder.Custom.Admin.View.List.Id')
		);
	}

	/**
	 * Get The Joomla 6 CustomView Form Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6CustomViewForm
	 * @since   6.1.7
	 */
	public function getJ6CustomViewForm(Container $container): J6CustomViewForm
	{
		return new J6CustomViewForm(
			$container->get('Config'),
			$container->get('Compiler.Builder.Custom.Form'),
			$container->get('Compiler.Builder.Custom.Admin.View.List.Id')
		);
	}
	/**
	 * Get The AdminViews CanDo Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AdminViewsCanDo
	 * @since   6.1.7
	 */
	public function getAdminViewsCanDo(Container $container): AdminViewsCanDo
	{
		return new AdminViewsCanDo(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The Field SetAccessControl Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldSetAccessControl
	 * @since   6.1.7
	 */
	public function getFieldSetAccessControl(Container $container): FieldSetAccessControl
	{
		return new FieldSetAccessControl(
			$container->get('Config')
		);
	}
}

