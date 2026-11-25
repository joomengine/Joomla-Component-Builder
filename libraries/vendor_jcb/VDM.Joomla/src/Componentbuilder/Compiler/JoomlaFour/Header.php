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

namespace VDM\Joomla\Componentbuilder\Compiler\JoomlaFour;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface as Event;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Builder\UikitComp;
use VDM\Joomla\Componentbuilder\Compiler\Builder\AdminFilterType;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Category;
use VDM\Joomla\Componentbuilder\Compiler\Builder\AccessSwitchList;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Filter;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Tags;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\HeaderInterface;


/**
 * Build headers for all Joomla 4 files
 * 
 * @since 3.2.0
 */
final class Header implements HeaderInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * The EventInterface Class.
	 *
	 * @var   Event
	 * @since 3.2.0
	 */
	protected Event $event;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 3.2.0
	 */
	protected Placeholder $placeholder;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 3.2.0
	 */
	protected Language $language;

	/**
	 * The UikitComp Class.
	 *
	 * @var   UikitComp
	 * @since 3.2.0
	 */
	protected UikitComp $uikitcomp;

	/**
	 * The AdminFilterType Class.
	 *
	 * @var   AdminFilterType
	 * @since 3.2.0
	 */
	protected AdminFilterType $adminfiltertype;

	/**
	 * The Category Class.
	 *
	 * @var   Category
	 * @since 3.2.0
	 */
	protected Category $category;

	/**
	 * The AccessSwitchList Class.
	 *
	 * @var   AccessSwitchList
	 * @since 3.2.0
	 */
	protected AccessSwitchList $accessswitchlist;

	/**
	 * The Filter Class.
	 *
	 * @var   Filter
	 * @since 3.2.0
	 */
	protected Filter $filter;

	/**
	 * The Tags Class.
	 *
	 * @var   Tags
	 * @since 3.2.0
	 */
	protected Tags $tags;

	/**
	 * The Header Context array
	 *
	 * @var   array
	 * @since 3.2.0
	 */
	protected array $headers = [];

	/**
	 * List of disallowed context strings.
	 *
	 * @var   array<string>
	 * @since 5.1.2
	 */
	protected array $disallowedContexts = [
		'admin.helper',
		'site.helper',
		'plugin.extension.header',
		'plugin.provider.header',
		'module.provider.header',
		'module.dispatcher.header',
		'module.dynamicgets.header',
		'module.helper.header',
		'module.default.template.header',
		'module.extra.template.header',
	];

	/**
	 * The Namespace Prefix
	 *
	 * @var   string
	 * @since 3.2.0
	 */
	protected string $NamespacePrefix;

	/**
	 * The Component Name (in code)
	 *
	 * @var   string
	 * @since 3.2.0
	 */
	protected string $ComponentName;

	/**
	 * The Component Namespace (in code)
	 *
	 * @var   string
	 * @since 3.2.0
	 */
	protected string $ComponentNamespace;

	/**
	 * Constructor.
	 *
	 * @param Config             $config             The Config Class.
	 * @param Event              $event              The EventInterface Class.
	 * @param Placeholder        $placeholder        The Placeholder Class.
	 * @param Language           $language           The Language Class.
	 * @param UikitComp          $uikitcomp          The UikitComp Class.
	 * @param AdminFilterType    $adminfiltertype    The AdminFilterType Class.
	 * @param Category           $category           The Category Class.
	 * @param AccessSwitchList   $accessswitchlist   The AccessSwitchList Class.
	 * @param Filter             $filter             The Filter Class.
	 * @param Tags               $tags               The Tags Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Config $config, Event $event, Placeholder $placeholder,
		Language $language, UikitComp $uikitcomp,
		AdminFilterType $adminfiltertype, Category $category,
		AccessSwitchList $accessswitchlist, Filter $filter,
		Tags $tags)
	{
		$this->config = $config;
		$this->event = $event;
		$this->placeholder = $placeholder;
		$this->language = $language;
		$this->uikitcomp = $uikitcomp;
		$this->adminfiltertype = $adminfiltertype;
		$this->category = $category;
		$this->accessswitchlist = $accessswitchlist;
		$this->filter = $filter;
		$this->tags = $tags;

		// set some global values
		$this->NamespacePrefix = $this->placeholder->get('NamespacePrefix');
		$this->ComponentName = $this->placeholder->get('Component');
		$this->ComponentNamespace = $this->placeholder->get('ComponentNamespace');
	}

	/**
	 * Get the headers for a file
	 *
	 * @param   string  $context    The name of the context
	 * @param   string  $codeName   The view, views, or layout code name
	 *
	 * @return  string  The header string to place in the header of the file
	 * @since 3.2.0
	 */
	public function get(string $context, string $codeName): string
	{
		// get static headers
		$headers = $this->getHeaders($context);

		// add to all except the helper classes
		if ($this->isAllowedContext($context))
		{
			$target = 'Administrator';
			if ($this->config->get('build_target', 'admin') === 'site')
			{
				$target = 'Site';
			}

			$headers[] = "use {$this->NamespacePrefix}\\Component\\{$this->ComponentNamespace}\\{$target}\\Helper\\{$this->ComponentName}Helper;";

			// we will add more as needed
			switch ($context)
			{
				case 'site.view.model':
				case 'site.views.model':
				case 'site.view.html':
				case 'site.views.html':
					$headers[] = "use {$this->NamespacePrefix}\\Component\\{$this->ComponentNamespace}\\Site\\Helper\\RouteHelper;";
				break;

				default:
					break;
			}
		}

		// get dynamic headers
		switch ($context)
		{
			case 'admin.view.html':
			case 'admin.views.html':
			case 'custom.admin.view.html':
			case 'custom.admin.views.html':
			case 'site.admin.view.html':
			case 'site.view.html':
			case 'site.views.html':
				if ((2 == $this->config->uikit || 1 == $this->config->uikit)
					&& $this->uikitcomp->exists($codeName))
				{
					$headers[] = 'use Joomla\CMS\Filesystem\File;';
				}
				break;

			case 'admin.view':
			case 'custom.admin.view':
			case 'custom.admin.views':
			case 'site.admin.view':
				$headers[] = '';
				$headers[] = '/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */';
				$headers[] = '$wa = $this->getDocument()->getWebAssetManager();';
				$headers[] = '$wa->useScript(\'keepalive\')->useScript(\'form.validate\');';
				$headers[] = 'Html::_(\'bootstrap.tooltip\');';
				break;

			case 'admin.view.model':
			case 'site.admin.view.model':
			case 'custom.admin.view.model':
			case 'site.view.model':
			case 'admin.views.model':
			case 'site.views.model':
				$headers[] = 'use Joomla\CMS\Helper\TagsHelper;';
				break;

			case 'plugin.provider.header':
				// Defer plugin placeholders until rendering, including after self-compilation.
				$headers[] = "use {$this->NamespacePrefix}\\Plugin\\"
					. Placefix::_('PluginGroupNamespace') . '\\'
					. Placefix::_('PluginNamespace') . "\\Extension\\{$codeName};";
				break;

			default:
				break;
		}

		// Trigger Event: jcb_ce_setClassHeader
		$this->event->trigger(
			'jcb_ce_setClassHeader', [&$context, &$codeName, &$headers]
		);

		// return the headers
		return $this->placeholder->update_(implode(PHP_EOL, $headers));
	}

	/**
	 * Get the headers for a file
	 *
	 * @param   string  $context    The name of the context
	 *
	 * @return  array  The header string to place in the header of the file
	 * @since 3.2.0
	 */
	protected function getHeaders(string $context): array
	{
		if (isset($this->headers[$context]))
		{
			return $this->headers[$context];
		}

		// set the defaults
		$headers = [];
		$headers[] = 'use Joomla\CMS\Factory;';
		$headers[] = 'use Joomla\CMS\Language\Text;';

		switch ($context)
		{
			case 'admin.component':
				$headers[] = 'use Joomla\CMS\Access\Exception\NotAllowed;';
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\MVC\Controller\BaseController;';
				break;

			case 'admin.helper':
			case 'site.helper':
				$headers[] = 'use Joomla\CMS\Access\Access;';
				$headers[] = 'use Joomla\CMS\Access\Rules as AccessRules;';
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\Filesystem\File;';
				$headers[] = 'use Joomla\CMS\Language\Language;';
				$headers[] = 'use Joomla\CMS\MVC\Model\BaseDatabaseModel;';
				$headers[] = 'use Joomla\CMS\Object\CMSObject;';
				$headers[] = 'use Joomla\CMS\Session\Session;';
				$headers[] = 'use Joomla\CMS\Table\Table;';
				$headers[] = 'use Joomla\CMS\Uri\Uri;';
				$headers[] = 'use Joomla\CMS\Version;';
				$headers[] = 'use Joomla\Database\DatabaseInterface;';
				$headers[] = 'use Joomla\Registry\Registry;';
				$headers[] = 'use Joomla\String\StringHelper;';
				$headers[] = 'use Joomla\Utilities\ArrayHelper;';
				break;

			case 'admin.layout':
			case 'site.layout':
			case 'custom.admin.layout':
			case 'override.layout':
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\Layout\LayoutHelper;';
				break;

			case 'admin.view':
			case 'custom.admin.view':
			case 'custom.admin.views':
			case 'site.admin.view':
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\Layout\LayoutHelper;';
				$headers[] = 'use Joomla\CMS\Router\Route;';
				break;

			case 'admin.view.controller':
				$headers[] = 'use Joomla\CMS\Form\FormFactoryInterface;';
				$headers[] = 'use Joomla\CMS\Application\CMSApplication;';
				$headers[] = 'use Joomla\CMS\MVC\Factory\MVCFactoryInterface;';
				$headers[] = 'use Joomla\Input\Input;';
			case 'site.admin.view.controller':
				$headers[] = 'use Joomla\CMS\Versioning\VersionableControllerTrait;';
			case 'site.view.controller':
				$headers[] = 'use Joomla\CMS\MVC\Controller\FormController;';
				$headers[] = 'use Joomla\CMS\MVC\Model\BaseDatabaseModel;';
				$headers[] = 'use Joomla\Utilities\ArrayHelper;';
				$headers[] = 'use Joomla\CMS\Router\Route;';
				$headers[] = 'use Joomla\CMS\Session\Session;';
				$headers[] = 'use Joomla\CMS\Uri\Uri;';
				break;

			case 'admin.view.html':
			case 'admin.views.html':
			case 'site.admin.view.html':
				$headers[] = 'use Joomla\CMS\Toolbar\Toolbar;';
				$headers[] = 'use Joomla\CMS\Form\FormHelper;';
				$headers[] = 'use Joomla\CMS\Session\Session;';
				$headers[] = 'use Joomla\CMS\Uri\Uri;';
				$headers[] = 'use Joomla\CMS\User\User;';
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\Layout\FileLayout;';
				$headers[] = 'use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;';
				$headers[] = 'use Joomla\CMS\Plugin\PluginHelper;';
				$headers[] = 'use Joomla\CMS\Toolbar\ToolbarHelper;';
				$headers[] = 'use Joomla\CMS\Document\Document;';
				break;

			case 'site.view.html':
			case 'site.views.html':
				$headers[] = 'use Joomla\CMS\Toolbar\Toolbar;';
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\Layout\FileLayout;';
				$headers[] = 'use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;';
				$headers[] = 'use Joomla\CMS\Plugin\PluginHelper;';
				$headers[] = 'use Joomla\CMS\Toolbar\ToolbarHelper;';
				$headers[] = 'use Joomla\CMS\Document\Document;';
				$headers[] = "use {$this->NamespacePrefix}\\Component\\{$this->ComponentNamespace}\\Site\\Helper\\HeaderCheck;";
				break;

			case 'custom.admin.view.html':
			case 'custom.admin.views.html':
				$target = 'Administrator';
				if ($this->config->get('build_target', 'admin') === 'site')
				{
					$target = 'Site';
				}
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\Layout\FileLayout;';
				$headers[] = 'use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;';
				$headers[] = 'use Joomla\CMS\Plugin\PluginHelper;';
				$headers[] = 'use Joomla\CMS\Toolbar\ToolbarHelper;';
				$headers[] = 'use Joomla\CMS\User\User;';
				$headers[] = 'use Joomla\CMS\Document\Document;';
				$headers[] = "use {$this->NamespacePrefix}\\Component\\{$this->ComponentNamespace}\\{$target}\\Helper\\HeaderCheck;";
				break;

			case 'admin.view.model':
			case 'site.admin.view.model':
				$headers[] = 'use Joomla\CMS\Application\CMSApplicationInterface;';
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\Form\Form;';
				$headers[] = 'use Joomla\CMS\Filter\InputFilter;';
				$headers[] = 'use Joomla\CMS\Filter\OutputFilter;';
				$headers[] = 'use Joomla\CMS\MVC\Model\AdminModel;';
				$headers[] = 'use Joomla\CMS\MVC\Factory\MVCFactoryInterface;';
				$headers[] = 'use Joomla\CMS\Table\Table;';
				$headers[] = 'use Joomla\CMS\UCM\UCMType;';
				$headers[] = 'use Joomla\CMS\Versioning\VersionableModelTrait;';
				$headers[] = 'use Joomla\CMS\User\User;';
				$headers[] = 'use Joomla\Registry\Registry;';
				$headers[] = 'use Joomla\String\StringHelper;';
				$headers[] = 'use Joomla\Utilities\ArrayHelper;';
				$headers[] = 'use Joomla\Input\Input;';
				break;

			case 'admin.views.modal':
				$headers[] = 'use Joomla\CMS\Session\Session;';
			case 'admin.views':
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\Layout\LayoutHelper;';
				$headers[] = 'use Joomla\CMS\Router\Route;';
				break;

			case 'admin.views.controller':
			case 'custom.admin.views.controller':
			case 'dashboard.controller':
				$headers[] = 'use Joomla\CMS\MVC\Controller\AdminController;';
				$headers[] = 'use Joomla\Utilities\ArrayHelper;';
				$headers[] = 'use Joomla\CMS\Router\Route;';
				$headers[] = 'use Joomla\CMS\Session\Session;';
				break;

			case 'ajax.admin.model':
			case 'ajax.site.model':
				$headers[] = 'use Joomla\CMS\Application\CMSApplicationInterface;';
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\Layout\LayoutHelper;';
				$headers[] = 'use Joomla\CMS\MVC\Model\ListModel;';
				$headers[] = 'use Joomla\CMS\MVC\Factory\MVCFactoryInterface;';
				$headers[] = 'use Joomla\CMS\Plugin\PluginHelper;';
				$headers[] = 'use Joomla\CMS\User\User;';
				$headers[] = 'use Joomla\Utilities\ArrayHelper;';
				$headers[] = 'use Joomla\Input\Input;';
				$headers[] = 'use Joomla\CMS\Router\Route;';
				$headers[] = 'use Joomla\CMS\Session\Session;';
				$headers[] = 'use Joomla\CMS\Uri\Uri;';
				$headers[] = 'use Joomla\Registry\Registry;';
				break;

			case 'dashboard.model':
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\Session\Session;';
				$headers[] = 'use Joomla\CMS\Uri\Uri;';
			case 'admin.views.model':
			case 'custom.admin.views.model':
			case 'site.views.model':
				$headers[] = 'use Joomla\CMS\Application\CMSApplicationInterface;';
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\MVC\Model\ListModel;';
				$headers[] = 'use Joomla\CMS\MVC\Factory\MVCFactoryInterface;';
				$headers[] = 'use Joomla\CMS\Plugin\PluginHelper;';
				$headers[] = 'use Joomla\CMS\Router\Route;';
				$headers[] = 'use Joomla\CMS\User\User;';
				$headers[] = 'use Joomla\Utilities\ArrayHelper;';
				$headers[] = 'use Joomla\Input\Input;';
				break;

			case 'custom.admin.view.controller':
			case 'import.controller':
			case 'import.custom.controller':
				$headers[] = 'use Joomla\CMS\MVC\Controller\BaseController;';
				$headers[] = 'use Joomla\CMS\Router\Route;';
				$headers[] = 'use Joomla\CMS\Session\Session;';
				$headers[] = 'use Joomla\Utilities\ArrayHelper;';
				break;

			case 'custom.admin.view.model':
			case 'site.view.model':
				$headers[] = 'use Joomla\CMS\Application\CMSApplicationInterface;';
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\MVC\Model\ItemModel;';
				$headers[] = 'use Joomla\CMS\MVC\Factory\MVCFactoryInterface;';
				$headers[] = 'use Joomla\CMS\Plugin\PluginHelper;';
				$headers[] = 'use Joomla\CMS\Router\Route;';
				$headers[] = 'use Joomla\CMS\Uri\Uri;';
				$headers[] = 'use Joomla\CMS\User\User;';
				$headers[] = 'use Joomla\Input\Input;';
				$headers[] = 'use Joomla\Utilities\ArrayHelper;';

				break;
			case 'import.custom.model':
			case 'import.model':
				$headers[] = 'use Joomla\CMS\Filesystem\File;';
				$headers[] = 'use Joomla\Filesystem\Folder;';
				$headers[] = 'use Joomla\CMS\Filesystem\Path;';
				$headers[] = 'use Joomla\CMS\Filter\OutputFilter;';
				$headers[] = 'use Joomla\CMS\Installer\InstallerHelper;';
				$headers[] = 'use Joomla\CMS\MVC\Model\BaseDatabaseModel;';
				$headers[] = 'use Joomla\String\StringHelper;';
				$headers[] = 'use Joomla\Utilities\ArrayHelper;';
				$headers[] = 'use PhpOffice\PhpSpreadsheet\IOFactory;';
				break;

			case 'dashboard.view':
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				break;

			case 'dashboard.view.html':
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;';
				$headers[] = 'use Joomla\CMS\Toolbar\ToolbarHelper;';
				$headers[] = 'use Joomla\CMS\Document\Document;';
				break;

			case 'site.router':
				$headers[] = 'use Joomla\CMS\Application\SiteApplication;';
				$headers[] = 'use Joomla\CMS\Categories\CategoryFactoryInterface;';
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\Component\Router\RouterView;';
				$headers[] = 'use Joomla\CMS\Component\Router\RouterViewConfiguration;';
				$headers[] = 'use Joomla\CMS\Component\Router\Rules\MenuRules;';
				$headers[] = 'use Joomla\CMS\Component\Router\Rules\NomenuRules;';
				$headers[] = 'use Joomla\CMS\Component\Router\Rules\StandardRules;';
				$headers[] = 'use Joomla\CMS\Menu\AbstractMenu;';
				$headers[] = 'use Joomla\Database\DatabaseInterface;';
				$headers[] = 'use Joomla\Database\ParameterType;';
				$headers[] = 'use Joomla\Registry\Registry;';
				break;

			case 'site.view':
			case 'site.views':
				$headers[] = 'use Joomla\CMS\Router\Route;';
				$headers[] = 'use Joomla\CMS\Layout\LayoutHelper;';
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				break;

			case 'form.custom.field':
				$headers[] = 'use Joomla\CMS\HTML\HTMLHelper as Html;';
				$headers[] = 'use Joomla\CMS\Component\ComponentHelper;';
				$headers[] = 'use Joomla\CMS\Form\Field\###FORM_EXTENDS###;';
				break;

			case 'module.provider.header':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Extension\Service\Provider\HelperFactory;';
				$headers[] = 'use Joomla\CMS\Extension\Service\Provider\Module;';
				$headers[] = 'use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;';
				$headers[] = 'use Joomla\DI\ServiceProviderInterface;';
				$headers[] = 'use Joomla\DI\Container;';
				break;
			case 'module.dispatcher.header':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;';
				$headers[] = 'use Joomla\CMS\Helper\HelperFactoryAwareInterface;';
				$headers[] = 'use Joomla\CMS\Helper\HelperFactoryAwareTrait;';
				break;
			case 'module.dynamicgets.header':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Application\CMSApplicationInterface;';
				$headers[] = 'use Joomla\Database\DatabaseAwareInterface;';
				$headers[] = 'use Joomla\Database\DatabaseAwareTrait;';
				$headers[] = 'use Joomla\Registry\Registry;';
				$headers[] = 'use Joomla\Input\Input;';
				break;

			case 'plugin.extension.header':
				$headers = [];
				break;
			case 'plugin.provider.header':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Factory;';
				$headers[] = 'use Joomla\CMS\Plugin\PluginHelper;';
				$headers[] = 'use Joomla\CMS\Extension\PluginInterface;';
				$headers[] = 'use Joomla\Event\DispatcherInterface;';
				$headers[] = 'use Joomla\DI\ServiceProviderInterface;';
				$headers[] = 'use Joomla\DI\Container;';
				break;

			case 'api.view.controller':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Factory;';
				$headers[] = 'use Joomla\CMS\Language\Text;';
				$headers[] = 'use Joomla\CMS\Access\Exception\NotAllowed;';
				$headers[] = 'use Joomla\CMS\MVC\Controller\ApiController;';
				$headers[] = 'use Joomla\CMS\MVC\Controller\Exception\ResourceNotFound;';
				break;

			case 'api.views.controller':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Factory;';
				$headers[] = 'use Joomla\CMS\Language\Text;';
				$headers[] = 'use Joomla\CMS\Filter\InputFilter;';
				$headers[] = 'use Joomla\CMS\MVC\Controller\ApiController;';
				break;

			case 'api.view.json':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Factory;';
				$headers[] = 'use Joomla\CMS\Helper\TagsHelper;';
				$headers[] = 'use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;';
				break;

			case 'api.views.json':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Factory;';
				$headers[] = 'use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;';
				$headers[] = 'use Joomla\Registry\Registry;';
				break;

			case 'api.dynamic.view.controller':
			case 'api.dynamic.views.controller':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Factory;';
				$headers[] = 'use Joomla\CMS\Language\Text;';
				$headers[] = 'use Joomla\CMS\MVC\Controller\ApiController;';
				$headers[] = 'use Joomla\CMS\Access\Exception\NotAllowed;';
				break;

			case 'api.dynamic.view.json':
			case 'api.dynamic.views.json':
				$headers = [];
				$headers[] = 'use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;';
				break;

			case 'api.view.serializer':
				$headers = [];
				$headers[] = 'use Joomla\CMS\Serializer\JoomlaSerializer;';
				$headers[] = 'use Joomla\CMS\Tag\TagApiSerializerTrait;';
				$headers[] = 'use Tobscure\JsonApi\Collection;';
				$headers[] = 'use Tobscure\JsonApi\Relationship;';
				$headers[] = 'use Tobscure\JsonApi\Resource;';
				break;

			default:
				break;
		}

		$this->headers[$context] = $headers;

		return $headers;
	}

	/**
	 * Determine if the given context is allowed.
	 *
	 * Returns true if the context is not in the disallowed list,
	 * false if the context is disallowed.
	 *
	 * @param  string  $context  The context string to evaluate.
	 *
	 * @return bool  True if allowed, false if disallowed.
	 * @since  5.1.2
	 */
	protected function isAllowedContext(string $context): bool
	{
		return !in_array($context, $this->disallowedContexts, true);
	}
}

