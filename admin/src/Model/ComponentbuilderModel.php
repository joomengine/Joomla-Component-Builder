<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace VDM\Component\Componentbuilder\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder List Model
 *
 * @since  1.6
 */
class ComponentbuilderModel extends ListModel
{
	/**
	 * Represents the current user object.
	 *
	 * @var   User  The user object representing the current user.
	 * @since 3.2.0
	 */
	protected User $user;

	/**
	 * View groups of this component
	 *
	 * @var   array<string, string>
	 * @since 5.1.1
	 */
	protected array $viewGroups = [
		'main' => ['png.compiler', 'png.extrusion', 'png.joomla_components', 'png.joomla_modules', 'png.joomla_plugins', 'png.powers', 'png.search', 'png.admin_views', 'png.custom_admin_views', 'png.site_views', 'png.template.add', 'png.templates', 'png.layouts', 'png.dynamic_get.add', 'png.dynamic_gets', 'png.custom_codes', 'png.placeholders', 'png.libraries', 'png.snippets', 'png.validation_rules', 'png.field.add', 'png.fields', 'png.fields.catid_qpo0O0oqp_com_componentbuilder_po0O0oq_field', 'png.fieldtypes', 'png.fieldtypes.catid_qpo0O0oqp_com_componentbuilder_po0O0oq_fieldtype', 'png.language_translations', 'png.languages', 'png.servers', 'png.repositories', 'png.help_documents'],
	];

	/**
	 * View access array.
	 *
	 * @var   array<string, string>
	 * @since 5.1.1
	 */
	protected array $viewAccess = [
		'compiler.access' => 'compiler.access',
		'compiler.submenu' => 'compiler.submenu',
		'compiler.dashboard_list' => 'compiler.dashboard_list',
		'search.access' => 'search.access',
		'search.submenu' => 'search.submenu',
		'search.dashboard_list' => 'search.dashboard_list',
		'initialization_selection.access' => 'initialization_selection.access',
		'pull_selection.access' => 'pull_selection.access',
		'import_translations.access' => 'import_translations.access',
		'extrusion.access' => 'extrusion.access',
		'extrusion.submenu' => 'extrusion.submenu',
		'extrusion.dashboard_list' => 'extrusion.dashboard_list',
		'joomla_component.create' => 'joomla_component.create',
		'joomla_components.access' => 'joomla_component.access',
		'joomla_component.access' => 'joomla_component.access',
		'joomla_components.submenu' => 'joomla_component.submenu',
		'joomla_components.dashboard_list' => 'joomla_component.dashboard_list',
		'joomla_module.create' => 'joomla_module.create',
		'joomla_modules.access' => 'joomla_module.access',
		'joomla_module.access' => 'joomla_module.access',
		'joomla_modules.submenu' => 'joomla_module.submenu',
		'joomla_modules.dashboard_list' => 'joomla_module.dashboard_list',
		'joomla_plugin.create' => 'joomla_plugin.create',
		'joomla_plugins.access' => 'joomla_plugin.access',
		'joomla_plugin.access' => 'joomla_plugin.access',
		'joomla_plugins.submenu' => 'joomla_plugin.submenu',
		'joomla_plugins.dashboard_list' => 'joomla_plugin.dashboard_list',
		'joomla_power.create' => 'joomla_power.create',
		'joomla_powers.access' => 'joomla_power.access',
		'joomla_power.access' => 'joomla_power.access',
		'joomla_powers.submenu' => 'joomla_power.submenu',
		'power.create' => 'power.create',
		'powers.access' => 'power.access',
		'power.access' => 'power.access',
		'powers.submenu' => 'power.submenu',
		'powers.dashboard_list' => 'power.dashboard_list',
		'admin_view.create' => 'admin_view.create',
		'admin_views.access' => 'admin_view.access',
		'admin_view.access' => 'admin_view.access',
		'admin_views.submenu' => 'admin_view.submenu',
		'admin_views.dashboard_list' => 'admin_view.dashboard_list',
		'custom_admin_views.access' => 'custom_admin_view.access',
		'custom_admin_view.access' => 'custom_admin_view.access',
		'custom_admin_views.submenu' => 'custom_admin_view.submenu',
		'custom_admin_views.dashboard_list' => 'custom_admin_view.dashboard_list',
		'site_views.access' => 'site_view.access',
		'site_view.access' => 'site_view.access',
		'site_views.submenu' => 'site_view.submenu',
		'site_views.dashboard_list' => 'site_view.dashboard_list',
		'templates.access' => 'template.access',
		'template.access' => 'template.access',
		'templates.submenu' => 'template.submenu',
		'templates.dashboard_list' => 'template.dashboard_list',
		'template.dashboard_add' => 'template.dashboard_add',
		'layouts.access' => 'layout.access',
		'layout.access' => 'layout.access',
		'layouts.submenu' => 'layout.submenu',
		'layouts.dashboard_list' => 'layout.dashboard_list',
		'dynamic_get.create' => 'dynamic_get.create',
		'dynamic_gets.access' => 'dynamic_get.access',
		'dynamic_get.access' => 'dynamic_get.access',
		'dynamic_gets.submenu' => 'dynamic_get.submenu',
		'dynamic_gets.dashboard_list' => 'dynamic_get.dashboard_list',
		'dynamic_get.dashboard_add' => 'dynamic_get.dashboard_add',
		'custom_code.create' => 'custom_code.create',
		'custom_codes.access' => 'custom_code.access',
		'custom_code.access' => 'custom_code.access',
		'custom_codes.submenu' => 'custom_code.submenu',
		'custom_codes.dashboard_list' => 'custom_code.dashboard_list',
		'class_extends.create' => 'class_extends.create',
		'class_extendings.access' => 'class_extends.access',
		'class_extends.access' => 'class_extends.access',
		'class_property.create' => 'class_property.create',
		'class_properties.access' => 'class_property.access',
		'class_property.access' => 'class_property.access',
		'class_method.create' => 'class_method.create',
		'class_methods.access' => 'class_method.access',
		'class_method.access' => 'class_method.access',
		'placeholder.create' => 'placeholder.create',
		'placeholders.access' => 'placeholder.access',
		'placeholder.access' => 'placeholder.access',
		'placeholders.submenu' => 'placeholder.submenu',
		'placeholders.dashboard_list' => 'placeholder.dashboard_list',
		'library.create' => 'library.create',
		'libraries.access' => 'library.access',
		'library.access' => 'library.access',
		'libraries.submenu' => 'library.submenu',
		'libraries.dashboard_list' => 'library.dashboard_list',
		'snippets.access' => 'snippet.access',
		'snippet.access' => 'snippet.access',
		'snippets.submenu' => 'snippet.submenu',
		'snippets.dashboard_list' => 'snippet.dashboard_list',
		'validation_rule.create' => 'validation_rule.create',
		'validation_rules.access' => 'validation_rule.access',
		'validation_rule.access' => 'validation_rule.access',
		'validation_rules.submenu' => 'validation_rule.submenu',
		'validation_rules.dashboard_list' => 'validation_rule.dashboard_list',
		'field.create' => 'field.create',
		'fields.access' => 'field.access',
		'field.access' => 'field.access',
		'fields.submenu' => 'field.submenu',
		'fields.dashboard_list' => 'field.dashboard_list',
		'field.dashboard_add' => 'field.dashboard_add',
		'fieldtype.create' => 'fieldtype.create',
		'fieldtypes.access' => 'fieldtype.access',
		'fieldtype.access' => 'fieldtype.access',
		'fieldtypes.submenu' => 'fieldtype.submenu',
		'fieldtypes.dashboard_list' => 'fieldtype.dashboard_list',
		'language_translation.create' => 'language_translation.create',
		'language_translations.access' => 'language_translation.access',
		'language_translation.access' => 'language_translation.access',
		'language_translations.submenu' => 'language_translation.submenu',
		'language_translations.dashboard_list' => 'language_translation.dashboard_list',
		'language.create' => 'language.create',
		'languages.access' => 'language.access',
		'language.access' => 'language.access',
		'languages.submenu' => 'language.submenu',
		'languages.dashboard_list' => 'language.dashboard_list',
		'server.create' => 'server.create',
		'servers.access' => 'server.access',
		'server.access' => 'server.access',
		'servers.submenu' => 'server.submenu',
		'servers.dashboard_list' => 'server.dashboard_list',
		'repository.create' => 'repository.create',
		'repositories.access' => 'repository.access',
		'repository.access' => 'repository.access',
		'repositories.submenu' => 'repository.submenu',
		'repositories.dashboard_list' => 'repository.dashboard_list',
		'help_document.create' => 'help_document.create',
		'help_documents.access' => 'help_document.access',
		'help_document.access' => 'help_document.access',
		'help_documents.submenu' => 'help_document.submenu',
		'help_documents.dashboard_list' => 'help_document.dashboard_list',
		'admin_fields.create' => 'admin_fields.create',
		'admins_fields.access' => 'admin_fields.access',
		'admin_fields.access' => 'admin_fields.access',
		'admin_fields_conditions.create' => 'admin_fields_conditions.create',
		'admins_fields_conditions.access' => 'admin_fields_conditions.access',
		'admin_fields_conditions.access' => 'admin_fields_conditions.access',
		'admin_fields_relations.create' => 'admin_fields_relations.create',
		'admins_fields_relations.access' => 'admin_fields_relations.access',
		'admin_fields_relations.access' => 'admin_fields_relations.access',
		'admin_custom_tabs.create' => 'admin_custom_tabs.create',
		'admins_custom_tabs.access' => 'admin_custom_tabs.access',
		'admin_custom_tabs.access' => 'admin_custom_tabs.access',
		'component_admin_views.create' => 'component_admin_views.create',
		'components_admin_views.access' => 'component_admin_views.access',
		'component_admin_views.access' => 'component_admin_views.access',
		'component_site_views.create' => 'component_site_views.create',
		'components_site_views.access' => 'component_site_views.access',
		'component_site_views.access' => 'component_site_views.access',
		'component_custom_admin_views.create' => 'component_custom_admin_views.create',
		'components_custom_admin_views.access' => 'component_custom_admin_views.access',
		'component_custom_admin_views.access' => 'component_custom_admin_views.access',
		'component_updates.create' => 'component_updates.create',
		'components_updates.access' => 'component_updates.access',
		'component_updates.access' => 'component_updates.access',
		'component_mysql_tweaks.create' => 'component_mysql_tweaks.create',
		'components_mysql_tweaks.access' => 'component_mysql_tweaks.access',
		'component_mysql_tweaks.access' => 'component_mysql_tweaks.access',
		'component_custom_admin_menus.create' => 'component_custom_admin_menus.create',
		'components_custom_admin_menus.access' => 'component_custom_admin_menus.access',
		'component_custom_admin_menus.access' => 'component_custom_admin_menus.access',
		'component_router.create' => 'component_router.create',
		'components_routers.access' => 'component_router.access',
		'component_router.access' => 'component_router.access',
		'component_config.create' => 'component_config.create',
		'components_config.access' => 'component_config.access',
		'component_config.access' => 'component_config.access',
		'component_dashboard.create' => 'component_dashboard.create',
		'components_dashboard.access' => 'component_dashboard.access',
		'component_dashboard.access' => 'component_dashboard.access',
		'component_files_folders.create' => 'component_files_folders.create',
		'components_files_folders.access' => 'component_files_folders.access',
		'component_files_folders.access' => 'component_files_folders.access',
		'component_placeholders.create' => 'component_placeholders.create',
		'components_placeholders.access' => 'component_placeholders.access',
		'component_placeholders.access' => 'component_placeholders.access',
		'component_plugins.create' => 'component_plugins.create',
		'components_plugins.access' => 'component_plugins.access',
		'component_plugins.access' => 'component_plugins.access',
		'component_modules.create' => 'component_modules.create',
		'components_modules.access' => 'component_modules.access',
		'component_modules.access' => 'component_modules.access',
		'snippet_type.create' => 'snippet_type.create',
		'snippet_types.access' => 'snippet_type.access',
		'snippet_type.access' => 'snippet_type.access',
		'library_config.create' => 'library_config.create',
		'libraries_config.access' => 'library_config.access',
		'library_config.access' => 'library_config.access',
		'library_files_folders_urls.create' => 'library_files_folders_urls.create',
		'libraries_files_folders_urls.access' => 'library_files_folders_urls.access',
		'library_files_folders_urls.access' => 'library_files_folders_urls.access',
		'joomla_module_updates.create' => 'joomla_module_updates.create',
		'joomla_modules_updates.access' => 'joomla_module_updates.access',
		'joomla_module_updates.access' => 'joomla_module_updates.access',
		'joomla_module_files_folders_urls.create' => 'joomla_module_files_folders_urls.create',
		'joomla_modules_files_folders_urls.access' => 'joomla_module_files_folders_urls.access',
		'joomla_module_files_folders_urls.access' => 'joomla_module_files_folders_urls.access',
		'joomla_plugin_groups.access' => 'joomla_plugin_group.access',
		'joomla_plugin_group.access' => 'joomla_plugin_group.access',
		'joomla_plugin_updates.create' => 'joomla_plugin_updates.create',
		'joomla_plugins_updates.access' => 'joomla_plugin_updates.access',
		'joomla_plugin_updates.access' => 'joomla_plugin_updates.access',
		'joomla_plugin_files_folders_urls.create' => 'joomla_plugin_files_folders_urls.create',
		'joomla_plugins_files_folders_urls.access' => 'joomla_plugin_files_folders_urls.access',
		'joomla_plugin_files_folders_urls.access' => 'joomla_plugin_files_folders_urls.access',
	];

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'administrator/components/com_componentbuilder/assets/css/admin.css',
		'administrator/components/com_componentbuilder/assets/css/dashboard.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'administrator/components/com_componentbuilder/assets/js/admin.js'
 	];

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$this->user ??= $this->getCurrentUser();
	}

	/**
	 * Get dashboard icons, grouped by view sections.
	 *
	 * @return array<string, array<int, \stdClass|false>>
	 * @since  5.1.1
	 */
	public function getIcons(): array
	{
		$icons = [];

		foreach ($this->viewGroups as $group => $views)
		{
			if (!UtilitiesArrayHelper::check($views))
			{
				$icons[$group][] = false;
				continue;
			}

			foreach ($views as $view)
			{
				$icon = $this->buildIconObject($view);
				if ($icon !== null)
				{
					$icons[$group][] = $icon;
				}
			}
		}

		return $icons;
	}

	/**
	 * Method to get the styles that have to be included on the view
	 *
	 * @return  array    styles files
	 * @since   4.3
	 */
	public function getStyles(): array
	{
		return $this->styles;
	}

	/**
	 * Method to set the styles that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setStyles(string $path): void
	{
		$this->styles[] = $path;
	}

	/**
	 * Method to get the script that have to be included on the view
	 *
	 * @return  array    script files
	 * @since   4.3
	 */
	public function getScripts(): array
	{
		return $this->scripts;
	}

	/**
	 * Method to set the script that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setScript(string $path): void
	{
		$this->scripts[] = $path;
	}

	/**
	 * Build a single dashboard icon if access is granted.
	 *
	 * @param string $view The view string to parse.
	 *
	 * @return \stdClass|null  The icon object or null if access denied.
	 * @since  5.1.1
	 */
	protected function buildIconObject(string $view): ?\stdClass
	{
		$parsed = $this->parseViewDefinition($view);
		if (!$parsed)
		{
			return null;
		}

		[
			'type' => $type,
			'name' => $name,
			'url' => $url,
			'image' => $image,
			'alt' => $alt,
			'viewName' => $viewName,
			'add' => $add,
		] = $parsed;

		if (!$this->hasAccessToView($viewName, $add))
		{
			return null;
		}

		return $this->createIconObject($url, $name, $image, $alt);
	}

	/**
	 * Parse a view string into structured components.
	 *
	 * @param string $view  The view definition string.
	 *
	 * @return array<string, mixed>|null  Parsed values or null on failure.
	 * @since  5.1.1
	 */
	protected function parseViewDefinition(string $view): ?array
	{
		$add = false;

		if (strpos($view, '||') !== false)
		{
			$parts = explode('||', $view);
			if (count($parts) === 3)
			{
				[$type, $name, $url] = $parts;
				return [
					'type' => $type,
					'name' => 'COM_COMPONENTBUILDER_DASHBOARD_' . StringHelper::safe($name, 'U'),
					'url' => $url,
					'image' => "{$name}.{$type}",
					'alt' => $name,
					'viewName' => $name,
					'add' => false,
				];
			}
		}

		if (strpos($view, '.') !== false)
		{
			$parts = explode('.', $view);
			$type = $parts[0] ?? '';
			$name = $parts[1] ?? '';
			$action = $parts[2] ?? null;
			$viewName = $name;

			if ($action)
			{
				if ($action === 'add')
				{
					$url = "index.php?option=com_componentbuilder&view={$name}&layout=edit";
					$image = "{$name}_{$action}.{$type}";
					$alt = "{$name}&nbsp;{$action}";
					$name = 'COM_COMPONENTBUILDER_DASHBOARD_' .
							StringHelper::safe($name, 'U') . '_ADD';
					$add = true;
				}
				else
				{
					if (strpos($action, '_qpo0O0oqp_') !== false)
					{
						[$action, $ext] = explode('_qpo0O0oqp_', $action);
						$extension = str_replace('_po0O0oq_', '.', $ext);
					}
					else
					{
						$extension = "com_componentbuilder.{$name}";
					}
					$url = "index.php?option=com_categories&view=categories&extension={$extension}";
					$image = "{$name}_{$action}.{$type}";
					$alt = "{$name}&nbsp;{$action}";
					$name = 'COM_COMPONENTBUILDER_DASHBOARD_' .
							StringHelper::safe($name, 'U') . '_' .
							StringHelper::safe($action, 'U');
				}
			}
			else
			{
				$url = "index.php?option=com_componentbuilder&view={$name}";
				$image = "{$name}.{$type}";
				$alt = $name;
				$name = 'COM_COMPONENTBUILDER_DASHBOARD_' .
						StringHelper::safe($name, 'U');
			}

			return compact('type', 'name', 'url', 'image', 'alt', 'viewName', 'add');
		}

		return [
			'type' => 'png',
			'name' => ucwords($view) . '<br /><br />',
			'url' => "index.php?option=com_componentbuilder&view={$view}",
			'image' => "{$view}.png",
			'alt' => $view,
			'viewName' => $view,
			'add' => false,
		];
	}

	/**
	 * Determine if the user has access to view or create the item.
	 *
	 * @param string $viewName The base name of the view.
	 * @param bool $add If this is an add-action.
	 *
	 * @return bool
	 * @since  5.1.1
	 */
	protected function hasAccessToView(string $viewName, bool $add): bool
	{
		$viewAccess = $this->viewAccess;
		$accessAdd = $add && isset($viewAccess["{$viewName}.create"])
			? $viewAccess["{$viewName}.create"]
			: ($add ? 'core.create' : '');

		$accessTo = $viewAccess["{$viewName}.access"] ?? '';

		$dashboardAdd = isset($viewAccess["{$viewName}.dashboard_add"]) &&
					$this->user->authorise($viewAccess["{$viewName}.dashboard_add"], 'com_componentbuilder');

		$dashboardList = isset($viewAccess["{$viewName}.dashboard_list"]) &&
					$this->user->authorise($viewAccess["{$viewName}.dashboard_list"], 'com_componentbuilder');

		if ($add && StringHelper::check($accessAdd))
		{
			return $this->user->authorise($accessAdd, 'com_componentbuilder') && $dashboardAdd;
		}

		if (StringHelper::check($accessTo))
		{
			return $this->user->authorise($accessTo, 'com_componentbuilder') && $dashboardList;
		}

		return !$accessTo && !$accessAdd;
	}

	/**
	 * Create a \stdClass icon object.
	 *
	 * @param string $url Icon URL.
	 * @param string $name Language string or label.
	 * @param string $image Image filename.
	 * @param string $alt Alt text.
	 *
	 * @return \stdClass
	 * @since  5.1.1
	 */
	protected function createIconObject(string $url, string $name, string $image, string $alt): \stdClass
	{
		$icon = new \stdClass;
		$icon->url = $url;
		$icon->name = $name;
		$icon->image = $image;
		$icon->alt = $alt;
		return $icon;
	}


	/**
	 * Load and display the page content using an AJAX call to the component endpoint.
	 *
	 * This method injects an inline JavaScript script that asynchronously fetches the page MD content
	 * via a JSON API endpoint in the component. It uses the `marked` library to render markdown content
	 * and inserts the result into the `JcbDoc-page-md` container. Errors are displayed in a separate element.
	 *
	 * @return string  HTML markup including a container for the page content and an error message area.
	 * @since  5.1.4
	 */
	public function getJcbDocGitHubMd()
	{
		// call the ajax get wiki endpoint
		$call_url = Uri::base() . 'index.php?option=com_componentbuilder&task=ajax.getJcbDocGitHubMd&format=json&raw=true&' . Session::getFormToken() . '=1&path=joomengine.jcb-documentation.refs.heads.master.english.README';

		/** \Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->addInlineScript('
		function getJcbDocGitHubMd(){
			fetch("' . $call_url . '").then((response) => {
				if (response.ok) {
					return response.json();
				}
			}).then((result) => {
				if (typeof result.page !== "undefined") {
					document.getElementById("JcbDoc-page-md").innerHTML = marked.parse(result.page);
				} else if (typeof result.error !== "undefined") {
					document.getElementById("JcbDoc-page-md-error").innerHTML = result.error
				}
			});
		}
		setTimeout(getJcbDocGitHubMd, 1000);');

		return '<div id="JcbDoc-page-md"><small>'.Text::_('COM_COMPONENTBUILDER_THE_PAGE_IS_LOADING').'.<span class="loading-dots">.</span></small></div><div id="JcbDoc-page-md-error" style="color: red"></div>';
	}

	/**
	 * Load and display the component's README file using JavaScript fetch and markdown rendering.
	 *
	 * This method injects an inline script into the document that, once the DOM is fully loaded,
	 * fetches the README.txt file located in the administrator component directory, parses it using
	 * the `marked` JavaScript library, and inserts the HTML into the `readme-md` div.
	 *
	 * Automatically uses WebAssetManager if available (Joomla 4+), and falls back to legacy
	 * `$doc->addScriptDeclaration()` for Joomla 3.
	 *
	 * @return string  HTML markup including a container for the README content and a loading message.
	 * @since 3.9.0
	 */
	public function getReadme(): string
	{
		$app = $this->app ?? Factory::getApplication();

		$callUrl = Uri::root()
			. 'administrator/components/com_componentbuilder/README.txt';

		$errorMessage = Text::_('COM_COMPONENTBUILDER_PLEASE_CHECK_AGAIN_LATER');

		/** @var \Joomla\CMS\Document\Document $document */
		$document = $app->getDocument();

		// JavaScript to fetch and render README using `marked`
		$script = <<<JS
document.addEventListener("DOMContentLoaded", function () {
	fetch("{$callUrl}")
		.then(response => {
			if (!response.ok) {
				throw new Error("Network response was not ok");
			}
			return response.text();
		})
		.then(readme => {
			document.getElementById("readme-md").innerHTML = marked.parse(readme);
		})
		.catch(error => {
			console.error("There has been a problem with your fetch operation:", error);
			document.getElementById("readme-md").innerHTML = "{$errorMessage}";
		});
});
JS;

		// Use WebAssetManager if available (Joomla 4+), otherwise fallback
		if (method_exists($document, 'getWebAssetManager'))
		{
			/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
			$wa = $document->getWebAssetManager();
			$wa->addInlineScript($script);
		}
		else
		{
			$document->addScriptDeclaration($script);
		}

		// Return the README container markup
		return '<div id="readme-md"><small>'
			. Text::_('COM_COMPONENTBUILDER_THE_README_IS_LOADING')
			. '.<span class="loading-dots">.</span></small></div>';
	}

	/**
	 * Inject JavaScript that fetches and displays the current component version status.
	 *
	 * This method adds an inline script to the page which asynchronously calls the component's
	 * AJAX endpoint to check the latest version. It updates the `#component-update-notice` element
	 * with the fetched version notice or error message.
	 *
	 * @return void
	 * @since  2.3.0
	 */
	public function getVersion()
	{
		// call the ajax get version endpoint
		$call_url = Uri::base()
			. 'index.php?option=com_componentbuilder&task=ajax.getVersion&format=json&raw=true&'
			. Session::getFormToken() . '=1&version=1.0.0';

		try {
			/** \Joomla\CMS\WebAsset\WebAssetManager $wa */
			$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

			$wa->addInlineScript('
			function getComponentVersionStatus() {
				fetch("' . $call_url . '").then((response) => {
					if (response.ok) {
						return response.json();
					}
				}).then((result) => {
					const target = document.getElementById("component-update-notice");
					if (!target) return;
					if (typeof result.notice !== "undefined") {
						target.innerHTML = result.notice;
					} else if (typeof result.error !== "undefined") {
						target.innerHTML = result.error;
					}
				});
			}
			setTimeout(getComponentVersionStatus, 800);');
		} catch (\Throwable $e) {
			// we do nothing....
		}
	}
}
