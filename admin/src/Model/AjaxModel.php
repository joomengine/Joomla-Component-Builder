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
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Joomla\Gitea\Factory as GiteaFactory;
use VDM\Joomla\Utilities\FileHelper;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Search\Factory as SearchFactory;
use VDM\Joomla\Componentbuilder\Import\Factory as ImportFactory;
use VDM\Joomla\Utilities\GuidHelper;
use VDM\Joomla\Componentbuilder\Remote\Version;
use VDM\Joomla\Utilities\SessionHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\GetHelper;
use VDM\Joomla\Utilities\Base64Helper;
use VDM\Joomla\Componentbuilder\Table\Search;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\FieldHelper;
use VDM\Joomla\Utilities\FormHelper;
use VDM\Joomla\Componentbuilder\Utilities\FilterHelper;
use VDM\Joomla\Data\Factory as DataFactory;
use VDM\Joomla\Componentbuilder\Factory as ComponentbuilderFactory;
use VDM\Joomla\Componentbuilder\File\Factory as FileFactory;
use VDM\Joomla\File\TypeDefinition;
use VDM\Joomla\Componentbuilder\Extrusion\Factory as ExtrusionFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Form\FormHelper as FormFormHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Ajax List Model
 *
 * @since  1.6
 */
class AjaxModel extends ListModel
{
	/**
	 * The component params.
	 *
	 * @var   Registry
	 * @since 3.2.0
	 */
	protected Registry $app_params;

	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

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

		$this->app_params = ComponentHelper::getParams('com_componentbuilder');
		$this->app ??= Factory::getApplication();
	}

	// Used in joomla_component

	/**
	 * Retrieves the component details as an HTML display and metadata.
	 *
	 * This function fetches the details of a Joomla component from the database based on the provided target identifier.
	 * The target can be a GUID or an ID. If the target is valid, the function constructs a query to retrieve component
	 * information from the `#__componentbuilder_joomla_component` table, formats the result into an HTML representation, 
	 * and includes the preferred Joomla version metadata.
	 *
	 * @param string|int $target The identifier of the component. This can either be:
	 *                           - A GUID (globally unique identifier) as a string, or
	 *                           - A numeric ID (integer).
	 *
	 * @return array|null Returns an associative array with the following keys on success:
	 *                    - `html` (string): An HTML representation of the component details.
	 *                    - `preferred_joomla_version` (mixed): The preferred Joomla version, or 0 if not available.
	 *                    Returns `null` if the target is invalid or no matching component is found in the database.
	 * @since  3.0.0
	 */
	public function getComponentDetails($target): ?array
	{
		if (GuidHelper::valid($target))
		{
			$key = 'guid';
		}
		elseif (is_numeric($target))
		{
			$key = 'id';
		}
		else
		{
			return null;
		}

		try {
			// Need to find the asset id by the name of the component.
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
				->select($db->quoteName([
					'id','companyname','component_version','copyright','debug_linenr',
					'description','email','image','license','name','preferred_joomla_version',
					'short_description','website','author','add_placeholders',
					'system_name','mvc_versiondate']))
				->from($db->quoteName('#__componentbuilder_joomla_component'))
				->where($db->quoteName($key) . ' = ' . $db->quote($target));
			$db->setQuery($query);
			$db->execute();
			if ($db->loadRowList())
			{
				$object = $db->loadObject();
				return [
					'html' => $this->componentDetailsDisplay($object),
					'preferred_joomla_version' => $object->preferred_joomla_version ?? 0
				];
			}
		} catch (\Exception $e) {
			return [
				'error' => $e->getMessage()
			];
		}

		return null;
	}

	/**
	 * Generates a detailed HTML representation of the component information.
	 *
	 * This method constructs a visually appealing HTML display for a Joomla component using the provided object
	 * containing component details. It includes information such as the component name, version, description, image,
	 * author details, license, copyright, and other settings.
	 *
	 * @param object $object An object containing the component details. Expected properties include:
	 *                       - `id` (int): The component ID.
	 *                       - `name` (string): The component name.
	 *                       - `component_version` (string): The version of the component.
	 *                       - `image` (string|null): The relative path to the component's image.
	 *                       - `description` (string|null): The detailed description of the component.
	 *                       - `short_description` (string|null): A short description (used if `description` is missing).
	 *                       - `add_placeholders` (bool): Whether to add custom code placeholders.
	 *                       - `debug_linenr` (bool): Whether debug line numbers are enabled.
	 *                       - `companyname` (string|null): The company name.
	 *                       - `author` (string|null): The author's name.
	 *                       - `email` (string|null): The author's email address.
	 *                       - `website` (string|null): The company's or author's website URL.
	 *                       - `license` (string|null): The component's license information.
	 *                       - `copyright` (string|null): The copyright details.
	 *                       - `system_name` (string): The system name of the component.
	 *
	 * @return string The HTML string representing the component details.
	 *
	 * @throws \InvalidArgumentException If a required property in the `$object` is missing or invalid.
	 * @since  3.0.0
	 */
	protected function componentDetailsDisplay($object): string
	{
		// Validate required properties
		if (empty($object->name) || empty($object->component_version) || empty($object->id))
		{
			throw new \InvalidArgumentException(Text::_('COM_COMPONENTBUILDER_INVALID_COMPONENT_OBJECT_MISSING_REQUIRED_PROPERTIES'));
		}

		return LayoutHelper::render('jcbcompilercomponentdetails', $object);
	}

	/**
	 * Get the current version notice.
	 *
	 * Compares the installed version of the component with the latest available
	 * version from the repository tags and returns an appropriate message.
	 *
	 * @param   string|null  $version  Optional version to compare if manifest version not found.
	 *
	 * @return  array  The array with 'notice' or 'error' and optional 'github-error' / 'gitea-error'.
	 * @since   2.3.0
	 * @since   5.1.1 Improved with support for pre-releases and intelligent tag grouping.
	 */
	public function getVersion(?string $version = null): array
	{
		return (new Version(
			'joomengine', 'pkg-component-builder',
			'joomla', 'pkg-component-builder'
		))->get($version);
	}

	/**
	 * Get the content of a GitHub markdown page.
	 *
	 * @param   string  $path  The path to the markdown page
	 *
	 * @return  array  Associative array with 'page' or 'error' key.
	 * @since   2.3.0
	 */
	public function getJcbDocGitHubMd(string $path): array
	{
		try {
			$githubUrl = $this->toRawJcbDocGithubMarkdownUrl($path);
			$cacheKey = $githubUrl;
			if (($page = SessionHelper::get($cacheKey, 'not_found')) === 'not_found')
			{
				// Restrict fallback to supported languages in the official documentation.
				$englishUrl = preg_replace(
					'~^(https://raw\\.githubusercontent\\.com/joomengine/jcb-documentation/refs/heads/master/)(?:french|persian)/~',
					'${1}english/',
					$githubUrl
				);
				try {
					$page = FileHelper::getContent($githubUrl);
				} catch (\Throwable $e) {
					if ($englishUrl === $githubUrl) {
						throw $e;
					}
					$page = false;
				}
				if (empty($page) && $englishUrl !== $githubUrl) {
					$page = FileHelper::getContent($englishUrl);
					$githubUrl = $englishUrl;
				}
				if (!empty($page))
				{
					$page = $this->rewriteJcbDocRelativeMarkdownLinks($page, $githubUrl);
				}

				SessionHelper::set($cacheKey, $page);
			}

			if (!empty($page))
			{
				return ['page' => $page];
			}
		} catch (\Throwable $e) {
			return ['error' => $e->getMessage()];
		}

		return ['error' => Text::_('COM_COMPONENTBUILDER_THE_PAGE_CAN_ONLY_BE_LOADED_WHEN_YOUR_SYSTEM_HAS_INTERNET_CONNECTION')];
	}

	/**
	 * Convert a dot-notated GitHub repository path into a raw.githubusercontent.com URL
	 * with strict structural and safety validation.
	 *
	 * Validation rules:
	 * - The path must contain at least two dots (minimum three segments)
	 * - No empty segments are allowed
	 * - Each segment must be URL-safe (RFC 3986 unreserved characters only)
	 *
	 * @param  string  $path  Dot-notated repository path
	 *
	 * @return string  Fully-qualified raw GitHub Markdown URL
	 *
	 * @throws \InvalidArgumentException If the path is invalid or unsafe
	 * @since  5.1.4
	 */
	protected function toRawJcbDocGithubMarkdownUrl(string $path): string
	{
		$path = trim($path);

		// Must contain at least two dots (minimum three segments)
		if (substr_count($path, '.') < 2)
		{
			throw new \InvalidArgumentException(
				'Invalid path: must contain at least three dot-separated segments.'
			);
		}

		$segments = explode('.', $path);

		foreach ($segments as $segment)
		{
			// Reject empty segments (e.g. "..", ".foo", "foo.")
			if ($segment === '')
			{
				throw new \InvalidArgumentException(
					'Invalid path: empty segment detected.'
				);
			}

			// RFC 3986 unreserved characters only: ALPHA / DIGIT / "-" / "." / "_" / "~"
			// Fast ASCII-safe validation
			if (!preg_match('/^[A-Za-z0-9._~-]+$/', $segment))
			{
				throw new \InvalidArgumentException(
					'Invalid path segment detected: ' . $segment
				);
			}
		}

		return 'https://raw.githubusercontent.com/'
			. str_replace('.', '/', $path)
			. '.md';
	}

	/**
	 * Rewrite relative Markdown links (./file.md) to absolute GitHub blob URLs.
	 *
	 * This method scans a Markdown document and replaces only links that:
	 * - Use Markdown link syntax: [text](./path)
	 * - Start with "./"
	 *
	 * It leaves untouched:
	 * - Absolute URLs (http/https)
	 * - Anchors (#section)
	 * - Non-Markdown content
	 *
	 * The GitHub base URL is derived from a raw.githubusercontent.com URL.
	 *
	 * @param  string  $page       The full Markdown page content
	 * @param  string  $rawPathUrl A raw.githubusercontent.com URL pointing to the same repo/path
	 *
	 * @return string  The Markdown page with corrected GitHub links
	 *
	 * @throws \InvalidArgumentException If the raw path URL is invalid
	 * @since  5.1.4
	 */
	protected function rewriteJcbDocRelativeMarkdownLinks(string $page, string $rawPathUrl): string
	{
		// Validate raw GitHub URL structure
		if (
			!str_starts_with($rawPathUrl, 'https://raw.githubusercontent.com/')
			|| !str_contains($rawPathUrl, '/refs/heads/')
		)
		{
			throw new \InvalidArgumentException(
				'Invalid raw GitHub URL provided.'
			);
		}

		/*
		 * Convert:
		 * https://raw.githubusercontent.com/org/repo/refs/heads/branch/path/file.md
		 * -->
		 * https://github.com/org/repo/blob/branch/path/
		 */
		$githubBase = str_replace(
			[
				'https://raw.githubusercontent.com/',
				'/refs/heads/'
			],
			[
				'https://github.com/',
				'/blob/'
			],
			$rawPathUrl
		);

		// Strip the filename (keep trailing slash)
		$githubBase = substr($githubBase, 0, strrpos($githubBase, '/') + 1);

		/*
		 * Replace only Markdown links that start with "./"
		 * Pattern matches: [label](./path)
		 */
		return preg_replace_callback(
			'/\[(.*?)\]\(\.\/([^)]+)\)/',
			static function (array $matches) use ($githubBase): string
			{
				return '[' . $matches[1] . '](' . $githubBase . $matches[2] . ')';
			},
			$page
		);
	}

	// Used in joomla_module
	/**
	 * Generates and returns the module code based on the provided data.
	 *
	 * This method processes the input data to generate module-specific code snippets 
	 * for class inclusion, data handling, libraries, CSS, and template loading. 
	 * It merges these code blocks into an array and specifies their placement within the final module output.
	 *
	 * @param string $data JSON-encoded string containing the module's class, get, lib, and other properties.
	 *
	 * @return array An associative array containing the generated code snippets for the module, 
	 *               including class, get, libraries, CSS, and template code, each with merge settings.
	 * @since  3.0.9
	 */
	public function getModuleCode($data)
	{
		// reset the return array
		$code = [];
		if (JsonHelper::check($data))
		{
			// convert the data to object
			$data = json_decode($data);
			// set class
			if (isset($data->class) && is_numeric($data->class) && ((int) $data->class == 2 || (int) $data->class == 1))
			{
				$code['class'] = [];
				// add the code
				$code['class']['code'] = '// Include the helper functions only once';
				$code['class']['code'] .= PHP_EOL . "JLoader::register('Mod[[[Module]]]Helper', __DIR__ . '/helper.php');";
				// set placement
				$code['class']['merge'] = 1;
				$code['class']['merge_target'] = 'prepend';
			}
			// get data
			if (isset($data->get) && UtilitiesArrayHelper::check($data->get))
			{
				$code['get'] = [];
				// add the code
				$code['get']['code'] = '// Include the data functions only once';
				$code['get']['code'] .= PHP_EOL . "JLoader::register('Mod[[[Module]]]Data', __DIR__ . '/data.php');";
				// set placement
				$code['get']['merge'] = 1;
				$code['get']['merge_target'] = 'prepend';
			}
			// get libraries
			if (isset($data->lib) && UtilitiesArrayHelper::check($data->lib))
			{
				$code['lib'] = [];
				// add the code
				$code['lib']['code'] = '[[[MOD_LIBRARIES]]]';
				// set placement
				$code['lib']['merge'] = 1;
				$code['lib']['merge_target'] = '// get the module class sfx (local)';
			}
		}
		// set the defaults
		$code['css'] = [];
		$code['tmpl'] = [];
		// add the code
		$code['css']['code'] = '// get the module class sfx (local)';
		$code['css']['code'] .= PHP_EOL . "\$moduleclass_sfx = htmlspecialchars(\$params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');";
		$code['tmpl']['code'] = '// load the default Tmpl';
		$code['tmpl']['code'] .= PHP_EOL . "require Joomla__"."_f15d556d_33dd_4ee3_a0f7_0653e4a7a1e4___Power::getLayoutPath('mod_[[[module]]]', \$params->get('layout', 'default'));";
		// set placement
		$code['css']['merge'] = 1;
		$code['css']['merge_target'] = '// load the default Tmpl';
		$code['tmpl']['merge'] = 1;
		$code['tmpl']['merge_target'] = 'append';

		return $code;
	}

	// Used in joomla_plugin
	/**
	 * Retrieves the class code based on the provided ID and type.
	 *
	 * @param int|string $id   The ID|GUID of the class.
	 * @param string     $type The type of the class (e.g., 'property', 'method').
	 *
	 * @return mixed The class code, or false on failure.
	 * @since  3.0.9
	 */
	public function getClassCode($id, $type)
	{
		return ComponentbuilderHelper::getClassCode($id, $type);
	}

	/**
	 * Retrieves class code IDs based on the provided ID, type, and key.
	 *
	 * @param int|string $target   The ID|GUID of the class.
	 * @param string     $type The type of the class (e.g., 'property', 'method', 'joomla_plugin_group').
	 * @param int        $key  The key that determines which data to return (e.g., 1 for 'joomla_plugin_group', 2 for 'extension_type').
	 *
	 * @return mixed The corresponding class code IDs or false if no valid data is found.
	 * @since  3.0.9
	 */
	public function getClassCodeIds($target, $type, $key)
	{
		if ('property' === $type || 'method' === $type)
		{
			// we get the plugin group, or the powers
			if ($key == 1)
			{
				return GetHelper::vars('class_' . $type, $target, 'joomla_plugin_group', 'id');
			}
			elseif ($key == 2)
			{
				return GetHelper::vars('class_' . $type, 'powers', 'extension_type', 'id');
			}
		}
		elseif ('joomla_plugin_group' === $type)
		{
			return GetHelper::vars($type, $target, 'class_extends', 'id');
		}
		return false;
	}

	/**
	 * Retrieves the header code of the class based on the provided ID and type.
	 *
	 * @param int|string $target   The ID/GUID of the class.
	 * @param string     $type The type of the header (e.g., 'extends').
	 *
	 * @return string|false The decoded header code, or false if no valid data is found.
	 * @since  3.0.9
	 */
	public function getClassHeaderCode($target, $type)
	{
		if (GuidHelper::valid($target))
		{
			$key = 'guid';
		}
		elseif (is_numeric($target))
		{
			$key = 'id';
		}
		else
		{
			return null;
		}

		if ('extends' === $type &&
			($head = GetHelper::var('class_' . $type, $target, $key, 'head')) !== false &&
				StringHelper::check($head))
		{
			return base64_decode($head);
		}

		return null;
	}

	// Used in admin_view

	/**
	 * Defines the maximum number of rows allowed for specific item types.
	 *
	 * This array maps item types to their respective maximum row limits:
	 * - 'admin_fields_conditions': Maximum 80 rows.
	 * - 'admin_fields': Maximum 50 rows.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected array $rowNumbers = [
		'admin_fields_conditions' => 80,
		'admin_fields' => 50,
	];

	/**
	 * Stores tab names for dynamic configuration.
	 *
	 * This array is reserved for storing dynamically assigned tab names.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected array $tabNames = [];

	/**
	 * Maps button configurations to their corresponding backend keys.
	 *
	 * This array defines associations between button keys and their backend representations.
	 * Some keys are boolean values (e.g., 'language' => true) for conditional handling.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected array $buttonArray = [
		'library_config' => 'libraries_config',
		'library_files_folders_urls' => 'libraries_files_folders_urls',
		'admin_fields' => 'admins_fields',
		'admin_fields_conditions' => 'admins_fields_conditions',
		'admin_fields_relations' => 'admins_fields_relations',
		'admin_custom_tabs' => 'admins_custom_tabs',
		'validation_rule' => 'validation_rules',
		'field' => 'fields',
		'component_admin_views' => 'components_admin_views',
		'component_site_views' => 'components_site_views',
		'component_custom_admin_views' => 'components_custom_views',
		'component_updates' => 'components_updates',
		'component_mysql_tweaks' => 'components_mysql_tweaks',
		'component_custom_admin_menus' => 'components_custom_admin_menus',
		'component_config' => 'components_config',
		'component_dashboard' => 'components_dashboard',
		'component_files_folders' => 'components_files_folders',
		'custom_code' => 'custom_codes',
		'language' => true,
	];

	/**
	 * Maps function names to their respective handlers.
	 *
	 * This array defines the function-to-handler mappings for different operations
	 * in libraries, admin views, and Joomla components.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected array $functionArray = [
		// Library
		'rename' => 'setYesNo',
		'update' => 'setYesNo',
		'type' => 'setURLType',
		// Admin View
		'field' => 'setItemNames',
		'listfield' => 'setItemNames',
		'joinfields' => 'setItemNames',
		'area' => 'setAreaName',
		'set' => 'setCode',
		'join_type' => 'setJoinType',
		'list' => 'setAdminBehaviour',
		'title' => 'setYesNo',
		'alias' => 'setYesNo',
		'sort' => 'setYesNo',
		'search' => 'setYesNo',
		'filter' => 'setYesNo',
		'link' => 'setYesNo',
		'permission' => 'setPermissions',
		'tab' => 'setTabName',
		'alignment' => 'setAlignmentName',
		'target_field' => 'setItemNames',
		'target_behavior' => 'setTargetBehavior',
		'target_relation' => 'setTargetRelation',
		'match_field' => 'setItemNames',
		'match_behavior' => 'setMatchBehavior',
		'match_options' => 'setMatchOptions',
		// Joomla Component
		'menu' => 'setYesNo',
		'metadata' => 'setYesNo',
		'default_view' => 'setYesNo',
		'access' => 'setYesNo',
		'public_access' => 'setYesNo',
		'mainmenu' => 'setYesNo',
		'dashboard_list' => 'setYesNo',
		'submenu' => 'setYesNo',
		'dashboard_add' => 'setYesNo',
		'checkin' => 'setYesNo',
		'history' => 'setYesNo',
		'joomla_fields' => 'setYesNo',
		'port' => 'setYesNo',
		'add_api' => 'setAddApiType',
		'edit_create_site_view' => 'setYesNo',
		'icomoon' => 'setIcoMoon',
		'customadminview' => 'setItemNames',
		'adminviews' => 'setItemNames',
		'adminview' => 'setItemNames',
		'siteview' => 'setItemNames',
		'before' => 'setItemNames',
	];

	/**
	 * Maps specific field types to their respective field handlers.
	 *
	 * This array defines the mapping between field types and their corresponding
	 * handler methods or sub-arrays for processing.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected array $fieldsArray = [
		'library_config' => 'addconfig',
		'library_files_folders_urls' => [
			'addurls',
			'addfiles',
			'addfolders',
			'addfoldersfullpath',
			'addfilesfullpath',
		],
		'admin_fields' => 'addfields',
		'admin_fields_conditions' => 'addconditions',
		'admin_fields_relations' => 'addrelations',
		'component_admin_views' => 'addadmin_views',
		'component_site_views' => 'addsite_views',
		'component_custom_admin_views' => 'addcustom_admin_views',
	];

	/**
	 * Defines the allowed views for operations.
	 *
	 * This array lists the allowed view names for specific actions in the application.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected array $allowedViewsArray = [
		'admin_view',
		'joomla_component',
		'library',
	];

	/**
	 * Maps conversion checks to their associated types.
	 *
	 * This array defines the mapping between conversion check keys and their associated field types.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected array $conversionCheck = [
		'addfields' => 'field',
		'addconditions' => 'target_field',
		'addadmin_views' => 'adminview',
		'addconfig' => 'field',
		'addcustom_admin_views' => 'customadminview',
		'addcustommenus' => 'name',
		'addsite_views' => 'siteview',
		'sql_tweak' => 'adminview',
		'version_update' => 'version',
	];

	/**
	 * Stores item names grouped by their type.
	 *
	 * This array is used to cache item names for various field types. The structure includes:
	 * - 'field': For fields.
	 * - 'admin_view': For admin views.
	 * - 'site_view': For site views.
	 * - 'custom_admin_view': For custom admin views.
	 *
	 * @var array
	 * @since  3.0.9
	 */
	protected array $itemNames = [
		'field' => [],
		'admin_view' => [],
		'site_view' => [],
		'custom_admin_view' => []
	];

	/**
	 * Defines the configuration for item keys.
	 *
	 * This array maps various keys (e.g., 'field', 'adminview') to their corresponding database table,
	 * ID column, name column, and additional metadata. The structure includes:
	 * - 'table': The table name in the database.
	 * - 'tables': The plural form of the table name.
	 * - 'id': The column name for the unique identifier.
	 * - 'name': The column name for the item's name.
	 * - 'text': The human-readable label for the item type.
	 * - 'get': The name of a helper method to retrieve additional information.
	 *
	 * @var array
	 * @since  3.0.9
	 */
	protected array $itemKeys = [
		// Admin view keys
		'field' => [
			'table' => 'field',
			'tables' => 'fields',
			'key' => 'guid',
			'name' => 'name',
			'text' => 'Field',
			'get' => 'getFieldNameAndType'
		],
		'target_field' => [
			'table' => 'field',
			'tables' => 'fields',
			'key' => 'guid',
			'name' => 'name',
			'text' => 'Field',
			'get' => 'getFieldNameAndType'
		],
		'match_field' => [
			'table' => 'field',
			'tables' => 'fields',
			'key' => 'guid',
			'name' => 'name',
			'text' => 'Field',
			'get' => 'getFieldNameAndType'
		],
		'listfield' => [
			'table' => 'field',
			'tables' => 'fields',
			'key' => 'guid',
			'name' => 'name',
			'text' => 'Field',
			'get' => 'getFieldNameAndType'
		],
		'joinfields' => [
			'table' => 'field',
			'tables' => 'fields',
			'key' => 'guid',
			'name' => 'name',
			'text' => 'Field',
			'get' => 'getFieldNameAndType'
		],

		// Joomla component view keys
		'siteview' => [
			'table' => 'site_view',
			'tables' => 'site_views',
			'key' => 'guid',
			'name' => 'name',
			'text' => 'Site View'
		],
		'customadminview' => [
			'table' => 'custom_admin_view',
			'tables' => 'custom_admin_views',
			'key' => 'guid',
			'name' => 'system_name',
			'text' => 'Custom Admin View'
		],
		'adminviews' => [
			'table' => 'admin_view',
			'tables' => 'admin_views',
			'key' => 'guid',
			'name' => 'system_name',
			'text' => 'Admin View'
		],
		'adminview' => [
			'table' => 'admin_view',
			'tables' => 'admin_views',
			'key' => 'guid',
			'name' => 'system_name',
			'text' => 'Admin View'
		],
		'before' => [
			'table' => 'admin_view',
			'tables' => 'admin_views',
			'key' => 'guid',
			'name' => 'system_name',
			'text' => 'Admin View'
		]
	];

	/**
	 * An associative array mapping field types to configurations
	 * used for linking different database tables and their relationships
	 * within the component. This property defines the structure
	 * and metadata for various linked tables, including their fields,
	 * relationships, and additional metadata required for component functionality.
	 *
	 * Structure:
	 * - Key: Represents the context or type (e.g., 'field', 'admin_view', 'library', etc.).
	 * - Value: An array of configurations where each configuration contains:
	 *   - 'table': The database table name (singular form).
	 *   - 'tables': The database table name (plural form).
	 *   - 'fields': An associative array of field mappings (e.g., component-specific fields to database columns).
	 *   - 'linked': A string identifying the linked component or relationship.
	 *   - Optional:
	 *     - 'linked_name': A specific key to identify the linked name.
	 *     - 'type_name': An optional identifier for specific field types.
	 *
	 * Example Usage:
	 * - 'field': Links fields to specific configurations for components, libraries, etc.
	 * - 'library': Links libraries to templates, layouts, or other component parts.
	 * - 'power': Links metadata about system power usage or relationships for fields.
	 *
	 * @var array<string, array<array{
	 *     table: string,
	 *     tables: string,
	 *     fields: array<string, string>,
	 *     linked: string,
	 *     linked_name?: string,
	 *     type_name?: string
	 * }>> A multi-dimensional associative array defining table relationships.
	 * @since  3.0.0
	 */
	protected array $linkedKeys = [
		'field' => [
			[
				'table' => 'component_config',
				'tables' => 'components_config',
				'fields' => [
					'addconfig' => 'field',
					'joomla_component' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_JOOMLA_COMPONENT',
				'linked_name' => 'system_name'
			],
			[
				'table' => 'library_config',
				'tables' => 'libraries_config',
				'fields' => [
					'addconfig' => 'field',
					'library' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_LIBRARY',
				'linked_name' => 'name'
			],
			[
				'table' => 'admin_fields',
				'tables' => 'admins_fields',
				'fields' => [
					'addfields' => 'field',
					'admin_view' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_ADMIN_VIEW',
				'linked_name' => 'system_name'
			],
			[
				'table' => 'field',
				'tables' => 'fields',
				'fields' => [
					'xml' => 'fields',
					'name' => 'NAME',
					'fieldtype' => 'TYPE'
				],
				'linked' => 'COM_COMPONENTBUILDER_FIELD',
				'type_name' => 'name'
			],
			[
				'table' => 'joomla_module',
				'tables' => 'joomla_modules',
				'fields' => [
					'fields' => 'fields.fields.field',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_JOOMLA_MODULE'
			],
			[
				'table' => 'joomla_plugin',
				'tables' => 'joomla_plugins',
				'fields' => [
					'fields' => 'fields.fields.field',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_JOOMLA_PLUGIN'
			]
		],
		'admin_view' => [
			[
				'table' => 'component_admin_views',
				'tables' => 'components_admin_views',
				'fields' => [
					'addadmin_views' => 'adminview',
					'joomla_component' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_JOOMLA_COMPONENT',
				'linked_name' => 'system_name'
			]
		],
		'custom_admin_view' => [
			[
				'table' => 'component_custom_admin_views',
				'tables' => 'components_custom_admin_views',
				'fields' => [
					'addcustom_admin_views' => 'customadminview',
					'joomla_component' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_JOOMLA_COMPONENT',
				'linked_name' => 'system_name'
			]
		],
		'site_view' => [
			[
				'table' => 'component_site_views',
				'tables' => 'components_site_views',
				'fields' => [
					'addsite_views' => 'siteview',
					'joomla_component' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_JOOMLA_COMPONENT',
				'linked_name' => 'system_name'
			]
		],
		'library' => [
			[
				'table' => 'template',
				'tables' => 'templates',
				'fields' => [
					'libraries' => 'ARRAY',
					'name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_TEMPLATE'
			],
			[
				'table' => 'layout',
				'tables' => 'layouts',
				'fields' => [
					'libraries' => 'ARRAY',
					'name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_LAYOUT'
			],
			[
				'table' => 'site_view',
				'tables' => 'site_views',
				'fields' => [
					'libraries' => 'ARRAY',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_SITE_VIEW'
			],
			[
				'table' => 'custom_admin_view',
				'tables' => 'custom_admin_views',
				'fields' => [
					'libraries' => 'ARRAY',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW'
			]
		],
		'dynamic_get' => [
			[
				'table' => 'site_view',
				'tables' => 'site_views',
				'fields' => [
					'custom_get' => 'ARRAY',
					'main_get' => 'GUID',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_SITE_VIEW'
			],
			[
				'table' => 'custom_admin_view',
				'tables' => 'custom_admin_views',
				'fields' => [
					'custom_get' => 'ARRAY',
					'main_get' => 'GUID',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW'
			]
		],
		'joomla_module' => [
			[
				'table' => 'component_modules',
				'tables' => 'components_modules',
				'fields' => [
					'addjoomla_modules' => 'module',
					'joomla_component' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_JOOMLA_COMPONENT',
				'linked_name' => 'system_name'
			]
		],
		'joomla_plugin' => [
			[
				'table' => 'component_plugins',
				'tables' => 'components_plugins',
				'fields' => [
					'addjoomla_plugins' => 'plugin',
					'joomla_component' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_JOOMLA_COMPONENT',
				'linked_name' => 'system_name'
			]
		],
		'power' => [
			[
				'table' => 'admin_view',
				'tables' => 'admin_views',
				'fields' => [
					'params' => 'admin_view_headers:power_:power',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_ADMIN_VIEW'
			],
			[
				'table' => 'site_view',
				'tables' => 'site_views',
				'fields' => [
					'params' => 'site_view_headers:power_:power',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_SITE_VIEW'
			],
			[
				'table' => 'custom_admin_view',
				'tables' => 'custom_admin_views',
				'fields' => [
					'params' => 'custom_admin_view_headers:power_:power',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW'
			],
			[
				'table' => 'joomla_component',
				'tables' => 'joomla_components',
				'fields' => [
					'params' => 'joomla_component_headers:power_:power',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_JOOMLA_COMPONENT'
			],
			[
				'table' => 'component_dashboard',
				'tables' => 'components_dashboard',
				'fields' => [
					'params' => 'component_dashboard_headers:power_:power',
					'joomla_component' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_COMPONENT_DASHBOARD',
				'linked_name' => 'system_name'
			],
			[
				'table' => 'power',
				'tables' => 'powers',
				'fields' => [
					'extends' => 'GUID',
					'extendsinterfaces' => 'ARRAY',
					'implements' => 'ARRAY',
					'use_selection' => 'use',
					'load_selection' => 'load',
					'system_name' => 'NAME'
				],
				'linked' => 'COM_COMPONENTBUILDER_POWER'
			]
		]
	];

	/**
	 * Retrieves the translated language string for a given key.
	 *
	 * This function fetches the appropriate language string from a predefined array based on the provided key.
	 * It supports both unique keys and shared keys using the `|=VDM=|` delimiter for shared language values.
	 * If the key is not found, it falls back to a safe default value.
	 *
	 * @param string $key The key to look up in the language array. It can be:
	 *                    - A unique key (e.g., 'rename', 'field').
	 *                    - A shared key in the format 'prefix|=VDM=|key' (e.g., 'custom|=VDM=|field').
	 *
	 * @return string The translated language string associated with the key. If the key is not found,
	 *                a safe fallback value is returned.
	 * @since  3.0.0
	 *
	 * Example usage:
	 * ```php
	 * $languageString = $this->getLanguage('rename');
	 * echo $languageString; // Outputs: 'Rename'
	 *
	 * $sharedString = $this->getLanguage('custom|=VDM=|field');
	 * echo $sharedString; // Outputs: 'Field'
	 * ```
	 *
	 * Behavior:
	 * - If the `$key` exists in the `$language` array, the corresponding value is returned.
	 * - If the `$key` is in shared format (`|=VDM=|`), it attempts to resolve the shared key.
	 * - If the key is not found, it falls back to `StringHelper::safe`
	 */
	protected function getLanguage($key)
	{
		$language = [
			// Library (folder file url)
			'rename' => Text::_('COM_COMPONENTBUILDER_RENAME'),
			'path' => Text::_('COM_COMPONENTBUILDER_TARGET_PATH'),
			'update' => Text::_('COM_COMPONENTBUILDER_UPDATE'),
			// Admin View (fields)
			'field' => Text::_('COM_COMPONENTBUILDER_FIELD'),
			'listfield' =>  Text::_('COM_COMPONENTBUILDER_LIST_FIELD'),
			'joinfields' =>  Text::_('COM_COMPONENTBUILDER_JOIN_FIELDS'),
			'set' =>  Text::_('COM_COMPONENTBUILDER_GLUECODE'),
			'join_type' =>  Text::_('COM_COMPONENTBUILDER_JOIN_TYPE'),
			'list' => Text::_('COM_COMPONENTBUILDER_ADMIN_BEHAVIOUR'),
			'order_list' => Text::_('COM_COMPONENTBUILDER_ORDER_IN_LIST_VIEWS'),
			'title' => Text::_('COM_COMPONENTBUILDER_TITLE'),
			'alias' => Text::_('COM_COMPONENTBUILDER_ALIAS'),
			'sort' => Text::_('COM_COMPONENTBUILDER_SORTABLE'),
			'search' => Text::_('COM_COMPONENTBUILDER_SEARCHABLE'),
			'filter' => Text::_('COM_COMPONENTBUILDER_FILTER'),
			'link' => Text::_('COM_COMPONENTBUILDER_LINK'),
			'permission' => Text::_('COM_COMPONENTBUILDER_PERMISSIONS'),
			'tab' => Text::_('COM_COMPONENTBUILDER_TAB'),
			'alignment' => Text::_('COM_COMPONENTBUILDER_ALIGNMENT'),
			'order_edit' => Text::_('COM_COMPONENTBUILDER_ORDER_IN_EDIT'),
			// Admin View (conditions)
			'target_field' => Text::_('COM_COMPONENTBUILDER_TARGET_FIELDS'),
			'target_behavior' => Text::_('COM_COMPONENTBUILDER_TARGET_BEHAVIOUR'),
			'target_relation' => Text::_('COM_COMPONENTBUILDER_TARGET_RELATION'),
			'match_field' => Text::_('COM_COMPONENTBUILDER_MATCH_FIELD'),
			'match_behavior' => Text::_('COM_COMPONENTBUILDER_MATCH_BEHAVIOUR'),
			'match_options' => Text::_('COM_COMPONENTBUILDER_MATCH_OPTIONS'),
			// Joomla Component
			'menu' => Text::_('COM_COMPONENTBUILDER_ADD_MENU'),
			'metadata' => Text::_('COM_COMPONENTBUILDER_HAS_METADATA'),
			'default_view' => Text::_('COM_COMPONENTBUILDER_DEFAULT_VIEW'),
			'access' => Text::_('COM_COMPONENTBUILDER_ADD_ACCESS'),
			'public_access' => Text::_('COM_COMPONENTBUILDER_PUBLIC_ACCESS'),
			'mainmenu' => Text::_('COM_COMPONENTBUILDER_MAIN_MENU'),
			'dashboard_list' => Text::_('COM_COMPONENTBUILDER_DASHBOARD_LIST_OF_RECORDS'),
			'dashboard_add' => Text::_('COM_COMPONENTBUILDER_DASHBOARD_ADD_RECORD'),
			'submenu' => Text::_('COM_COMPONENTBUILDER_SUBMENU'),
			'checkin' => Text::_('COM_COMPONENTBUILDER_AUTO_CHECKIN'),
			'history' => Text::_('COM_COMPONENTBUILDER_KEEP_HISTORY'),
			'joomla_fields' => Text::_('COM_COMPONENTBUILDER_JOOMLA_FIELDS'),
			'port' => Text::_('COM_COMPONENTBUILDER_EXPORTIMPORT_DATA'),
			'edit_create_site_view' => Text::_('COM_COMPONENTBUILDER_EDITCREATE_SITE_VIEW'),
			'icomoon' => Text::_('COM_COMPONENTBUILDER_ICON'),
			'customadminview' => Text::_('COM_COMPONENTBUILDER_VIEW'),
			'adminviews' => Text::_('COM_COMPONENTBUILDER_VIEWS'),
			'adminview' => Text::_('COM_COMPONENTBUILDER_VIEW'),
			'siteview' => Text::_('COM_COMPONENTBUILDER_VIEW'),
			'before' => Text::_('COM_COMPONENTBUILDER_ORDER_BEFORE')
		];

		// check if a unique value is available
		if (isset($language[$key]))
		{
			return $language[$key];
		}

		// check a shared value is available
		if (strpos($key, '|=VDM=|') !== false)
		{
			$keys = explode('|=VDM=|', $key);
			if (isset($language[$keys[1]]))
			{
				return $language[$keys[1]];
			}
		}

		return StringHelper::safe($keys[1], 'Ww');
	}

	/**
	 * Checks if the current view has an alias field.
	 *
	 * This method retrieves the view ID and checks if the associated fields include
	 * an alias field (a field marked with an `alias` value of 1). It ensures the view
	 * is valid and part of the allowed views before performing the check.
	 *
	 * @param mixed $type  The type of the view to check (not directly used in this function but passed for consistency).
	 *
	 * @return bool Returns `true` if an alias field exists in the view, `false` otherwise.
	 * @since  3.0.0
	 */
	public function checkAliasField($type): bool
	{
		// get the view name & id
		$values = $this->getViewID();
		if (GuidHelper::valid($values['a_guid'] ?? '') && strlen($values['a_view']) && in_array($values['a_view'], $this->allowedViewsArray))
		{
			// get the fields
			if ($fields = GetHelper::var('admin_fields', $values['a_guid'], 'admin_view', 'addfields'))
			{
				// open the fields
				if (JsonHelper::check($fields))
				{
					$fields = json_decode($fields, true);
					if (UtilitiesArrayHelper::check($fields))
					{
						foreach($fields as $field)
						{
							if (isset($field['alias']) && $field['alias'] == 1)
							{
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 * Checks if the current view has a category field.
	 *
	 * This method retrieves the view ID and checks if the associated fields include
	 * a category field (a field with a type of `category`). It ensures the view is
	 * valid and part of the allowed views before performing the check.
	 *
	 * @param mixed $type   The type of the view to check (not directly used in this function but passed for consistency).
	 *
	 * @return bool Returns `true` if a category field exists in the view, `false` otherwise.
	 * @since  3.0.0
	 */
	public function checkCategoryField($type): bool
	{
		// get the view name & id
		$values = $this->getViewID();
		if (GuidHelper::valid($values['a_guid'] ?? '') && strlen($values['a_view']) && in_array($values['a_view'], $this->allowedViewsArray))
		{
			// get the fields
			if ($fields = GetHelper::var('admin_fields', $values['a_guid'], 'admin_view', 'addfields'))
			{
				// open the fields
				if (JsonHelper::check($fields))
				{
					$fields = json_decode($fields, true);
					if (UtilitiesArrayHelper::check($fields))
					{
						foreach($fields as $field)
						{
							if (isset($field['field']) &&
								($field_values = ComponentbuilderHelper::getFieldNameAndType($field['field'])) !== null && 
								$field_values['type'] === 'category' )
							{
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 * Retrieves dynamic scripts for the given type.
	 *
	 * This method acts as a wrapper to retrieve dynamic scripts for a specific type
	 * by delegating the call to a global helper method.
	 *
	 * @param string $type   The target type of string
	 *
	 * @return string|array The dynamic scripts associated with the specified type. The return
	 *                        value depends on the implementation of the helper method.
	 * @since  3.0.0
	 */
	public function getDynamicScripts($type)
	{
		// get from global helper
		return ComponentbuilderHelper::getDynamicScripts($type);
	}

	/**
	 * Retrieves the name and type of a field.
	 *
	 * This function fetches the name and type of a field using the component helper and formats them into a string.
	 *
	 * @param mixed $value The value used to identify the field (e.g., field ID or GUID).
	 *
	 * @return string A formatted string containing the field name and type in the format `[name - type]`.
	 *                Returns an empty string if the field cannot be resolved.
	 * @since  3.0.9
	 */
	protected function getFieldNameAndType($value): string
	{
		// check if we can get the field name and type
		if (($array = ComponentbuilderHelper::getFieldNameAndType($value, true)) !== false)
		{
			return ' [' . $array['name'] . ' - ' . $array['type'] . ']';
		}
		return '';
	}

	/**
	 * Converts permission values into human-readable strings.
	 *
	 * This function translates permission values into their respective labels (e.g., "Editing", "Access").
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $values A single value or an array of values representing permissions.
	 *
	 * @return string A comma-separated string of permission labels. Returns "None" if no valid permissions are found.
	 * @since  3.0.9
	 */
	protected function setPermissions(string $header, $values): string
	{
		// check if value is array
		if (!UtilitiesArrayHelper::check($values))
		{
			$values = [$values];
		}

		// check if value is array
		if (UtilitiesArrayHelper::check($values))
		{
			// Editing, Access, View
			$bucket = [];
			foreach ($values as $value)
			{
				switch ($value)
				{
					case 1:
						$bucket[] = Text::_('COM_COMPONENTBUILDER_EDITING');
					break;
					case 2:
						$bucket[] = Text::_('COM_COMPONENTBUILDER_ACCESS');
					break;
					case 3:
						$bucket[] = Text::_('COM_COMPONENTBUILDER_VIEW');
					break;
				}
			}

			// check if value is array
			if (UtilitiesArrayHelper::check($bucket))
			{
				return implode(', ', $bucket);
			}
		}

		return Text::_('COM_COMPONENTBUILDER_NONE');
	}

	/**
	 * Converts join type values into human-readable strings.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The value representing the join type.
	 *
	 * @return string The human-readable label for the join type. Returns "not set" if the value is invalid.
	 * @since  3.0.9
	 */
	protected function setJoinType(string $header, $value): string
	{
		switch ($value)
		{
			case 1:
				return Text::_('COM_COMPONENTBUILDER_CONCATENATE');
			break;
			case 2:
				return Text::_('COM_COMPONENTBUILDER_CUSTOM_CODE');
			break;
		}
		return Text::_('COM_COMPONENTBUILDER_NOT_SET');
	}

	/**
	 * Converts URL type values into human-readable strings.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The value representing the URL type.
	 *
	 * @return string The human-readable label for the URL type. Returns "not set" if the value is invalid.
	 * @since  3.0.9
	 */
	protected function setURLType(string $header, $value): string
	{
		switch ($value)
		{
			case 1:
				return Text::_('COM_COMPONENTBUILDER_DEFAULT_LINK');
			break;
			case 2:
				return Text::_('COM_COMPONENTBUILDER_LOCAL_GET');
			break;
			case 3:
				return Text::_('COM_COMPONENTBUILDER_LINK_LOCAL_DYNAMIC');
			break;
		}
		return Text::_('COM_COMPONENTBUILDER_NOT_SET');
	}

	/**
	 * Converts IcoMoon values into an HTML span element.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The IcoMoon icon name.
	 *
	 * @return string An HTML span element with the IcoMoon icon class. Returns "-" if the value is invalid.
	 * @since  3.0.9
	 */
	protected function setIcoMoon(string $header, $value): string
	{
		if (StringHelper::check($value))
		{
			return '<span class="icon-' . $value . '"></span>';
		}
		return '-';
	}

	/**
	 * Converts alignment values into human-readable strings.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The value representing the alignment.
	 *
	 * @return string The human-readable label for the alignment. Returns "not set" if the value is invalid.
	 * @since  3.0.9
	 */
	protected function setAlignmentName(string $header, $value): string
	{
		switch ($value)
		{
			case 1:
				return Text::_('COM_COMPONENTBUILDER_LEFT_IN_TAB');
			break;
			case 2:
				return Text::_('COM_COMPONENTBUILDER_RIGHT_IN_TAB');
			break;
			case 3:
				return Text::_('COM_COMPONENTBUILDER_FULL_WIDTH_IN_TAB');
			break;
			case 4:
				return Text::_('COM_COMPONENTBUILDER_ABOVE_TABS');
			break;
			case 5:
				return Text::_('COM_COMPONENTBUILDER_UNDERNEATH_TABS');
			break;
			case 6:
				return Text::_('COM_COMPONENTBUILDER_LEFT_OF_TABS');
			break;
			case 7:
				return Text::_('COM_COMPONENTBUILDER_RIGHT_OF_TABS');
			break;
		}
		return Text::_('COM_COMPONENTBUILDER_NOT_SET');
	}

	/**
	 * Converts admin behavior values into human-readable strings.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The value representing the admin behavior.
	 *
	 * @return string The human-readable label for the admin behavior. Returns "Default" if the value is invalid.
	 * @since  3.0.9
	 */
	protected function setAdminBehaviour(string $header, $value): string
	{
		switch ($value)
		{
			case 1:
				return Text::_('COM_COMPONENTBUILDER_SHOW_IN_ALL_LIST_VIEWS');
			break;
			case 2:
				return Text::_('COM_COMPONENTBUILDER_NONE_DB');
			break;
			case 3:
				return Text::_('COM_COMPONENTBUILDER_ONLY_IN_ADMIN_LIST_VIEW');
			break;
			case 4:
				return Text::_('COM_COMPONENTBUILDER_ONLY_IN_LINKED_LIST_VIEWS');
			break;
			default:
				return Text::_('COM_COMPONENTBUILDER_DEFAULT');
			break;
		}
	}

	/**
	 * Resolves a tab name based on the provided value.
	 *
	 * This function retrieves a tab name from a predefined list or dynamically fetches it if not already set.
	 * If the value is 15, it defaults to "Publishing".
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The value representing the tab.
	 *
	 * @return string The resolved tab name. Returns "Details" if no matching tab is found.
	 * @since  3.0.9
	 */
	protected function setTabName(string $header, $value): string
	{
		// return published if set to 15 (since this is the default number for it)
		if (15 == $value)
		{
			return Text::_('COM_COMPONENTBUILDER_PUBLISHING');
		}

		if (!UtilitiesArrayHelper::check($this->tabNames))
		{
			// get the view name & id
			$values = $this->getViewID();
			if (!is_null($values['a_id']) && $values['a_id'] > 0 && strlen($values['a_view']) && $values['a_view'] === 'admin_view')
			{
				if ($tabs = GetHelper::var('admin_view', $values['a_id'], 'id', 'addtabs'))
				{
					$tabs = json_decode($tabs, true);
					if (UtilitiesArrayHelper::check($tabs))
					{
						$nr = 1;
						foreach ($tabs as $tab)
						{
							if (UtilitiesArrayHelper::check($tab) && isset($tab['name']))
							{
								$this->tabNames[$nr] = $tab['name'];
								$nr++;
							}
						}
					}
				}
			}
		}

		// has it been set
		if (UtilitiesArrayHelper::check($this->tabNames) && isset($this->tabNames[$value]))
		{
			return $this->tabNames[$value];
		}

		return Text::_('COM_COMPONENTBUILDER_DETAILS');
	}

	/**
	 * Converts area values into human-readable strings.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The value representing the area.
	 *
	 * @return string The human-readable label for the area. Returns "not set" if the value is invalid.
	 * @since  3.0.9
	 */
	protected function setAreaName(string $header, $value): string
	{
		switch ($value)
		{
			case 1:
				return Text::_('COM_COMPONENTBUILDER_MODEL_BEFORE_MODELLING');
			break;
			case 2:
				return Text::_('COM_COMPONENTBUILDER_VIEW');
			break;
			case 3:
				return Text::_('COM_COMPONENTBUILDER_MODEL_AFTER_MODELLING');
			break;
		}
		return  Text::_('COM_COMPONENTBUILDER_NOT_SET');
	}

	/**
	 * Formats code values for display.
	 *
	 * This function converts the given value into a safe, HTML-escaped, and line-breaked format.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The code value to format.
	 *
	 * @return string The formatted code string.
	 * @since  3.0.9
	 */
	protected function setCode(string $header, $value): string
	{
		return nl2br(htmlspecialchars($value));
	}

	/**
	 * Converts binary values (1/0) into Yes/No HTML icons.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The binary value to convert.
	 *
	 * @return string An HTML string with a green checkmark for "Yes" or a gray delete icon for "No".
	 * @since  3.0.9
	 */
	protected function setYesNo(string $header, $value): string
	{
		if (1 === (int) $value)
		{
			return '<span style="color: #46A546;" class="icon-ok"></span>';
		}

		return '<span style="color: #e6e6e6;" class="icon-delete"></span>';
	}

	/**
	 * Converts target behavior values into human-readable strings.
	 *
	 * This function translates a numeric value into its corresponding behavior for
	 * displaying, hiding, or toggling a field's visibility.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The numeric value representing the target behavior.
	 *
	 * @return string The human-readable label for the target behavior.
	 * @since  3.0.9
	 */
	protected function setTargetBehavior(string $header, $value): string
	{
		if (1 == $value)
		{
			return Text::_('COM_COMPONENTBUILDER_SHOW_TOGGLE');
		}
		elseif (3 == $value)
		{
			return Text::_('COM_COMPONENTBUILDER_SHOW_ONLY');
		}
		elseif (4 == $value)
		{
			return Text::_('COM_COMPONENTBUILDER_HIDE_ONLY');
		}
		return Text::_('COM_COMPONENTBUILDER_HIDE_TOGGLE');
	}

	/**
	 * Converts target relation values into human-readable strings.
	 *
	 * This function translates a numeric value into its corresponding relation type
	 * for handling field dependencies.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The numeric value representing the target relation.
	 *
	 * @return string The human-readable label for the target relation. Returns "not set" if the value is invalid.
	 * @since  3.0.9
	 */
	protected function setTargetRelation(string $header, $value): string
	{
		switch ($value)
		{
			case 0:
				return Text::_('COM_COMPONENTBUILDER_ISOLATE');
			break;
			case 1:
				return Text::_('COM_COMPONENTBUILDER_CHAIN');
			break;
		}
		return  Text::_('COM_COMPONENTBUILDER_NOT_SET');
	}

	/**
	 * Converts match behavior values into human-readable strings.
	 *
	 * This function translates numeric values into behavior types for matching fields, such as
	 * keywords, length constraints, or selection criteria.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The numeric value representing the match behavior.
	 *
	 * @return string The human-readable label for the match behavior. Returns "not set" if the value is invalid.
	 * @since  3.0.9
	 */
	protected function setMatchBehavior($header, $value)
	{
		switch ($value)
		{
			case 1:
				return Text::_('COM_COMPONENTBUILDER_IS_ONLY_FOUR_LISTRADIOCHECKBOXES');
			break;
			case 2:
				return Text::_('COM_COMPONENTBUILDER_IS_NOT_ONLY_FOUR_LISTRADIOCHECKBOXES');
			break;
			case 3:
				return Text::_('COM_COMPONENTBUILDER_ANY_SELECTION_ONLY_FOUR_LISTRADIOCHECKBOXESDYNAMIC_LIST');
			break;
			case 4:
				return Text::_('COM_COMPONENTBUILDER_ACTIVE_ONLY_FOUR_TEXT_FIELD');
			break;
			case 5:
				return Text::_('COM_COMPONENTBUILDER_UNACTIVE_ONLY_FOUR_TEXT_FIELD');
			break;
			case 6:
				return Text::_('COM_COMPONENTBUILDER_KEY_WORD_ALL_CASESENSITIVE_ONLY_FOUR_TEXT_FIELD');
			break;
			case 7:
				return Text::_('COM_COMPONENTBUILDER_KEY_WORD_ANY_CASESENSITIVE_ONLY_FOUR_TEXT_FIELD');
			break;
			case 8:
				return Text::_('COM_COMPONENTBUILDER_KEY_WORD_ALL_CASEINSENSITIVE_ONLY_FOUR_TEXT_FIELD');
			break;
			case 9:
				return Text::_('COM_COMPONENTBUILDER_KEY_WORD_ANY_CASEINSENSITIVE_ONLY_FOUR_TEXT_FIELD');
			break;
			case 10:
				return Text::_('COM_COMPONENTBUILDER_MIN_LENGTH_ONLY_FOUR_TEXT_FIELD');
			break;
			case 11:
				return Text::_('COM_COMPONENTBUILDER_MAX_LENGTH_ONLY_FOUR_TEXT_FIELD');
			break;
			case 12:
				return Text::_('COM_COMPONENTBUILDER_EXACT_LENGTH_ONLY_FOUR_TEXT_FIELD');
			break;
		}
		return  Text::_('COM_COMPONENTBUILDER_NOT_SET');
	}

	/**
	 * Converts match options into a formatted string.
	 *
	 * This function replaces newlines in the input value with `<br />` tags for proper HTML formatting.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The value containing match options (e.g., a string with newline-separated options).
	 *
	 * @return string The formatted string with `<br />` tags for each option.
	 * @since  3.0.9
	 */
	protected function setMatchOptions(string $header, $value): string
	{
		return str_replace("\n", "<br />", $value);
	}

	/**
	 * Retrieves the select options for a given field.
	 *
	 * This function fetches field options from the database based on the field's ID and type.
	 * Depending on the field type, it processes the options differently (e.g., list, text, dynamic).
	 *
	 * @param mixed $target The ID|GUID of the field.
	 *
	 * @return string|null Returns a string of options if successful, or `null` if no options are found.
	 * @since  3.0.9
	 */
	public function getFieldSelectOptions($target): ?string
	{
		if (GuidHelper::valid($target))
		{
			$key = 'guid';
		}
		elseif (is_numeric($target))
		{
			$key = 'id';
		}
		else
		{
			return null;
		}

		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(['a.xml', 'b.name']));
		$query->from($db->quoteName('#__componentbuilder_field', 'a'));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_fieldtype', 'b') . ' ON (' . $db->quoteName('a.fieldtype') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.' . $key) . ' = ' . $db->quote($target));

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			$result = $db->loadObject();
			$result->name = strtolower($result->name);
			if (ComponentbuilderHelper::fieldCheck($result->name,'list'))
			{
				// load the values form params
				$xml = json_decode($result->xml);

				$xmlOptions = GetHelper::between($xml, 'option="', '"');

				$optionSet = '';
				if (strpos($xmlOptions,',') !== false)
				{
					// mulitpal options
					$options = explode(',',$xmlOptions);
					foreach ($options as $option)
					{
						// return both value and text
						if (StringHelper::check($optionSet))
						{
							// no add to option set
							$optionSet .= "\n".$option;
						}
						else 
						{
							// no add to option set
							$optionSet .= $option;
						}
					}
				}
				else
				{
					// return both value and text
					if (StringHelper::check($optionSet))
					{
						// no add to option set
						$optionSet .= "\n".$xmlOptions;
					}
					else 
					{
						// no add to option set
						$optionSet .= $xmlOptions;
					}
				}				
				// return found field options
				return $optionSet;
			}
			elseif (ComponentbuilderHelper::fieldCheck($result->name,'text'))
			{
				return "keywords=\"\"\nlength=\"\"";
			}
			elseif (ComponentbuilderHelper::fieldCheck($result->name, 'dynamic'))
			{
				return 'dynamic_list';
			}
			elseif (ComponentbuilderHelper::fieldCheck($result->name))
			{
				return 'match field type not supported. Select another!';
			}
			else
			{
				return 'dynamic_list';
			}
		}
		return null;
	}

	/**
	 * Retrieves the columns of a database table.
	 *
	 * This function fetches the column names and formats them into a string for easy display.
	 *
	 * @param string $tableName The name of the database table (without the prefix).
	 *
	 * @return string|null Returns a newline-separated string of column names if successful,
	 *                        or `null` if the table has no columns or an error occurs.
	 * @since  3.0.9
	 */
	public function getTableColumns(string $tableName): ?string
	{
		// Get a db connection.
		$db = $this->getDatabase();

        	// get the columns
		$columns = $db->getTableColumns("#__" . $tableName);
		if (UtilitiesArrayHelper::check($columns))
		{
        	   	// build the return string
			$tableColumns = [];

			foreach ($columns as $column => $type)
			{
				$tableColumns[] = $column . ' => ' . $column;
			}

			return implode("\n",$tableColumns);
		}

		return null;
	}

	/**
	 * Get linked item references.
	 *
	 * @param   int  $type  The display return type.
	 *
	 * @return  string  The linked item display HTML.
	 * @since   6.1.6
	 */
	public function getLinked($type): string
	{
		// Kept for backward compatibility with the existing method signature.
		unset($type);

		// Get the current view context and item ID.
		$values = $this->getViewID();

		$view   = isset($values['a_view']) ? (string) $values['a_view'] : '';
		$id     = isset($values['a_id']) ? (int) $values['a_id'] : 0;
		$guid   = $values['a_guid'] ?? null;
		$return = $values['a_return'] ?? null;

		// Validate the current context before attempting to load links.
		if ($view !== '' && $id > 0 && isset($this->linkedKeys[$view]))
		{
			// Build the return URL.
			$returnUrl = 'index.php?option=com_componentbuilder&view=' . $view . '&layout=edit&id=' . $id;

			if ($return !== null && $return !== '')
			{
				$returnUrl .= '&return=' . (string) $return;
			}

			// Build the reference string used elsewhere in the class.
			$this->ref = '&ref=' . $view . '&refid=' . $id . '&return=' . urlencode(base64_encode($returnUrl));

			// Load linked references.
			$linked = $this->getLinkedTo($view, $id, $guid);

			if (!empty($linked) && is_array($linked))
			{
				$viewSafeCaption = StringHelper::safe($view, 'w');
				$rows = [];

				foreach ($linked as $item)
				{
					$rows[] = '<tr><td>' . (string) $item . '</td></tr>';
				}

				return
					'<div class="card mb-3">' .
						'<div class="card-body">' .
							'<div class="table-responsive">' .
								'<table class="table table-striped table-hover align-middle mb-0">' .
									'<caption class="caption-top">' .
										Text::sprintf('COM_COMPONENTBUILDER_PLACES_ACROSS_JCB_WHERE_THIS_S_IS_LINKED', $viewSafeCaption) .
									'</caption>' .
									'<thead>' .
										'<tr>' .
											'<th scope="col">' .
												Text::_('COM_COMPONENTBUILDER_TYPE_NAME') .
											'</th>' .
										'</tr>' .
									'</thead>' .
									'<tbody>' . implode('', $rows) . '</tbody>' .
								'</table>' .
							'</div>' .
						'</div>' .
					'</div>';
			}

			// Valid context, but no links found.
			$viewSafeTitle = StringHelper::safe($view, 'Ww');

			return
				'<div class="alert alert-info">' .
					'<h4 class="alert-heading">' .
						Text::sprintf('COM_COMPONENTBUILDER_S_NOT_LINKED', $viewSafeTitle) .
					'</h4>' .
					'<p class="mb-0">' .
						Text::sprintf('COM_COMPONENTBUILDER_THIS_STRONGSSTRONG_IS_NOT_LINKED_TO_ANY_OTHER_AREAS_OF_JCB_AT_THIS_TIME', $view) .
					'</p>' .
				'</div>';
		}

		// Invalid context or access not allowed.
		return
			'<div class="alert alert-danger">' .
				'<h4 class="alert-heading">' .
					Text::_('COM_COMPONENTBUILDER_ERROR') .
				'</h4>' .
				'<p class="mb-0">' .
					Text::_('COM_COMPONENTBUILDER_THERE_WAS_A_PROBLEM_STRONGNO_VIEW_OR_ID_FOUND_IN_SESSION_OR_VIEW_NOT_ALLOWED_TO_ACCESS_AREASTRONG_WE_COULD_NOT_LOAD_ANY_LINKED_TO_VALUES_PLEASE_INFORM_YOUR_SYSTEM_ADMINISTRATOR') .
				'</p>' .
			'</div>';
	}

	/**
	 * Get linked to items.
	 *
	 * @param   string       $view  View that is being searched for.
	 * @param   int          $id    ID.
	 * @param   string|null  $guid  GUID.
	 *
	 * @return  array|null  Found items.
	 * @since   3.0.0
	 */
	protected function getLinkedTo(string $view, int $id, ?string $guid): ?array
	{
		if (
			!isset($this->linkedKeys[$view])
			|| !UtilitiesArrayHelper::check($this->linkedKeys[$view])
		)
		{
			return null;
		}

		$linked = [];
		$db = $this->getDatabase();

		foreach ($this->linkedKeys[$view] as $search)
		{
			$items = $this->getLinkedSearchItems($db, $search);

			if ($items === [])
			{
				continue;
			}

			foreach ($items as $item)
			{
				$match = $this->findLinkedItemMatch($item, $search, $id, $guid);

				if ($match['found'])
				{
					$linked[] = $this->buildLinkedItemDisplay($item, $search, $match);
				}
			}
		}

		if (UtilitiesArrayHelper::check($linked))
		{
			return $linked;
		}

		return null;
	}

	/**
	 * Load candidate items for a linked search definition.
	 *
	 * @param   object  $db      The database object.
	 * @param   array   $search  The linked search configuration.
	 *
	 * @return  array
	 * @since   6.1.6
	 */
	protected function getLinkedSearchItems($db, array $search): array
	{
		$query = $db->getQuery(true);

		$selection = array_keys($search['fields']);
		$selection[] = 'id';

		$query->select($selection);
		$query->from('#__componentbuilder_' . $search['table']);

		$db->setQuery($query);

		$items = $db->loadObjectList();

		if (!UtilitiesArrayHelper::check($items))
		{
			return [];
		}

		return $items;
	}

	/**
	 * Find a match for the current linked item.
	 *
	 * @param   object       $item    The database row item.
	 * @param   array        $search  The linked search configuration.
	 * @param   int          $id      The target ID.
	 * @param   string|null  $guid    The target GUID.
	 *
	 * @return  array  Match result with keys: found, linked_name, linked_name_table, type_name, type_name_table.
	 * @since   6.1.6
	 */
	protected function findLinkedItemMatch(object $item, array $search, int $id, ?string $guid): array
	{
		$match = [
			'found' => false,
			'linked_name' => null,
			'linked_name_table' => null,
			'type_name' => null,
			'type_name_table' => null,
		];

		foreach ($search['fields'] as $key => $target)
		{
			if ('NAME' === $target)
			{
				$match['linked_name'] = property_exists($item, $key) ? $item->{$key} : null;
				$match['linked_name_table'] = $key;
				continue;
			}

			if ('TYPE' === $target)
			{
				$match['type_name'] = property_exists($item, $key) ? $item->{$key} : null;
				$match['type_name_table'] = $key;
				continue;
			}

			if ($match['found'])
			{
				continue;
			}

			$value = property_exists($item, $key) ? $item->{$key} : null;

			if ($value !== null && $this->fieldContainsLinkedReference($key, $target, $value, $id, $guid))
			{
				$match['found'] = true;
			}
		}

		return $match;
	}

	/**
	 * Determine whether a field contains the linked reference.
	 *
	 * @param   string       $key     The field name.
	 * @param   string       $target  The field target definition.
	 * @param   mixed        $value   The field value.
	 * @param   int          $id      The target ID.
	 * @param   string|null  $guid    The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function fieldContainsLinkedReference(
		string $key,
		string $target,
		$value,
		int $id,
		?string $guid
	): bool
	{
		if ('INT' === $target)
		{
			return ($value == $id);
		}

		if ('GUID' === $target)
		{
			return $this->linkedGuid($guid, $value);
		}

		// Attempt JSON decode for complex field types.
		$decodedValue = $this->decodeLinkedFieldValue($value);

		// Check array-based structures first.
		if (UtilitiesArrayHelper::check($decodedValue))
		{
			return $this->arrayFieldContainsLinkedReference($decodedValue, $target, $id, $guid);
		}

		// Fall through to XML check using the raw value (not decoded),
		// since only non-array string values can be valid XML fields.
		if ('xml' === $key)
		{
			return $this->xmlFieldContainsLinkedReference($value, $target, $id, $guid);
		}

		return false;
	}

	/**
	 * Decode a linked field value from JSON when possible.
	 *
	 * @param   mixed  $value  The raw field value.
	 *
	 * @return  mixed  The decoded value, or the original value if decoding fails or is not applicable.
	 * @since   6.1.6
	 */
	protected function decodeLinkedFieldValue($value)
	{
		if (JsonHelper::check($value))
		{
			$decoded = json_decode($value, true);

			if (json_last_error() === JSON_ERROR_NONE)
			{
				return $decoded;
			}
		}

		return $value;
	}

	/**
	 * Determine whether an array-based field contains the linked reference.
	 *
	 * @param   array        $value   The decoded array value.
	 * @param   string       $target  The field target definition.
	 * @param   int          $id      The target ID.
	 * @param   string|null  $guid    The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function arrayFieldContainsLinkedReference(array $value, string $target, int $id, ?string $guid): bool
	{
		if ('ARRAY' === $target)
		{
			return $this->simpleArrayContainsLinkedReference($value, $id, $guid);
		}

		if (strpos($target, '.') !== false)
		{
			return $this->dotTargetContainsLinkedReference($value, $target, $id, $guid);
		}

		if (strpos($target, ':') !== false)
		{
			return $this->colonTargetContainsLinkedReference($value, $target, $id, $guid);
		}

		return $this->rowFieldContainsLinkedReference($value, $target, $id, $guid);
	}

	/**
	 * Check a simple array for a linked reference.
	 *
	 * @param   array        $values  The array values.
	 * @param   int          $id      The target ID.
	 * @param   string|null  $guid    The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function simpleArrayContainsLinkedReference(array $values, int $id, ?string $guid): bool
	{
		foreach ($values as $_id)
		{
			if ($_id == $id || $this->linkedGuid($guid, $_id))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Check a dot-notation target for a linked reference.
	 *
	 * Supports two-level (e.g. "subfield.key") and three-level (e.g. "subfield.ignored.key")
	 * dot-separated targets within subform row structures.
	 *
	 * @param   array        $value   The array value.
	 * @param   string       $target  The dot notation target.
	 * @param   int          $id      The target ID.
	 * @param   string|null  $guid    The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function dotTargetContainsLinkedReference(array $value, string $target, int $id, ?string $guid): bool
	{
		$_target = explode('.', $target);
		$_size = UtilitiesArrayHelper::check($_target);

		if ($_size === false)
		{
			return false;
		}

		foreach ($value as $row)
		{
			if (!is_array($row))
			{
				continue;
			}

			if ($_size == 2 && $this->dotTwoLevelMatch($row, $_target, $id, $guid))
			{
				return true;
			}

			if ($_size == 3 && $this->dotThreeLevelMatch($row, $_target, $id, $guid))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Check a two-level dot target match within a single row.
	 *
	 * @param   array        $row      The row data.
	 * @param   array        $_target  The exploded target parts.
	 * @param   int          $id       The target ID.
	 * @param   string|null  $guid     The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function dotTwoLevelMatch(array $row, array $_target, int $id, ?string $guid): bool
	{
		return (
			isset($row[$_target[0]][$_target[1]])
			&& (
				$row[$_target[0]][$_target[1]] == $id
				|| $this->linkedGuid($guid, $row[$_target[0]][$_target[1]])
			)
		);
	}

	/**
	 * Check a three-level dot target match within a single row.
	 *
	 * @param   array        $row      The row data.
	 * @param   array        $_target  The exploded target parts.
	 * @param   int          $id       The target ID.
	 * @param   string|null  $guid     The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function dotThreeLevelMatch(array $row, array $_target, int $id, ?string $guid): bool
	{
		if (
			!isset($row[$_target[0]])
			|| !UtilitiesArrayHelper::check($row[$_target[0]])
		)
		{
			return false;
		}

		foreach ($row[$_target[0]] as $_row)
		{
			if (
				is_array($_row)
				&& isset($_row[$_target[2]])
				&& (
					$_row[$_target[2]] == $id
					|| $this->linkedGuid($guid, $_row[$_target[2]])
				)
			)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Check a colon-notation target for a linked reference.
	 *
	 * @param   array        $value   The array value.
	 * @param   string       $target  The colon notation target.
	 * @param   int          $id      The target ID.
	 * @param   string|null  $guid    The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function colonTargetContainsLinkedReference(array $value, string $target, int $id, ?string $guid): bool
	{
		$_target = explode(':', $target);
		$_size = UtilitiesArrayHelper::check($_target);

		if ($_size == 2)
		{
			return $this->colonTwoPartMatch($value, $_target, $id, $guid);
		}

		if ($_size == 3)
		{
			return $this->colonThreePartMatch($value, $_target, $id, $guid);
		}

		return false;
	}

	/**
	 * Check a two-part colon target for a linked reference.
	 *
	 * @param   array        $value    The array value.
	 * @param   array        $_target  The exploded target parts.
	 * @param   int          $id       The target ID.
	 * @param   string|null  $guid     The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function colonTwoPartMatch(array $value, array $_target, int $id, ?string $guid): bool
	{
		foreach ($value as $field_name => $row)
		{
			if ($field_name !== $_target[0] || !is_array($row))
			{
				continue;
			}

			foreach ($row as $_key => $_ids)
			{
				if (
					strpos($_key, $_target[1]) !== false
					&& is_array($_ids)
					&& (
						in_array($id, $_ids)
						|| $this->linkedGuid($guid, $_ids)
					)
				)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check a three-part colon target for a linked reference.
	 *
	 * @param   array        $value    The array value.
	 * @param   array        $_target  The exploded target parts.
	 * @param   int          $id       The target ID.
	 * @param   string|null  $guid     The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function colonThreePartMatch(array $value, array $_target, int $id, ?string $guid): bool
	{
		foreach ($value as $field_name => $row)
		{
			if ($field_name !== $_target[0] || !is_array($row))
			{
				continue;
			}

			foreach ($row as $_key => $_items)
			{
				if (
					strpos($_key, $_target[1]) === false
					|| !is_array($_items)
					|| count($_items) === 0
				)
				{
					continue;
				}

				foreach ($_items as $_item)
				{
					if (
						is_array($_item)
						&& isset($_item[$_target[2]])
						&& (
							$id == $_item[$_target[2]]
							|| $this->linkedGuid($guid, $_item[$_target[2]])
						)
					)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check row arrays for a linked reference using a direct field name.
	 *
	 * @param   array        $value   The array value.
	 * @param   string       $target  The target field name.
	 * @param   int          $id      The target ID.
	 * @param   string|null  $guid    The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function rowFieldContainsLinkedReference(array $value, string $target, int $id, ?string $guid): bool
	{
		foreach ($value as $row)
		{
			if (
				is_array($row)
				&& isset($row[$target])
				&& (
					$row[$target] == $id
					|| $this->linkedGuid($guid, $row[$target])
				)
			)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether an XML field contains the linked reference.
	 *
	 * @param   mixed        $value   The raw field value (must be a string for XML parsing).
	 * @param   string       $target  The target XML attribute.
	 * @param   int          $id      The target ID.
	 * @param   string|null  $guid    The target GUID.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected function xmlFieldContainsLinkedReference($value, string $target, int $id, ?string $guid): bool
	{
		if (
			!StringHelper::check($value)
			|| strpos($value, $target . '="') === false
		)
		{
			return false;
		}

		$_fields = GetHelper::between($value, $target . '="', '"');

		if (!StringHelper::check($_fields))
		{
			return false;
		}

		$_fields = array_map('trim', (array) explode(',', $_fields));

		if (!UtilitiesArrayHelper::check($_fields))
		{
			return false;
		}

		foreach ($_fields as $_field)
		{
			if ($_field == $id || $this->linkedGuid($guid, $_field))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Build the display value for a found linked item.
	 *
	 * @param   object  $item    The database row item.
	 * @param   array   $search  The linked search configuration.
	 * @param   array   $match   The match details.
	 *
	 * @return  string
	 * @since   6.1.6
	 */
	protected function buildLinkedItemDisplay(object $item, array $search, array $match): string
	{
		$nameResult = $this->resolveLinkedName($match, $search);
		$typeName = $this->resolveTypeName($match, $search);
		$link = $this->buildLinkedEditButton($item, $search, $nameResult['edit']);

		return Text::_($search['linked']) . $typeName . ' - ' . $nameResult['name'] . ' ' . $link;
	}

	/**
	 * Resolve the linked item name.
	 *
	 * @param   array  $match   The match details.
	 * @param   array  $search  The linked search configuration.
	 *
	 * @return  array  With keys 'name' (string) and 'edit' (bool).
	 * @since   6.1.6
	 */
	protected function resolveLinkedName(array $match, array $search): array
	{
		$linkedName = $match['linked_name'];
		$linkedNameTable = $match['linked_name_table'];
		$edit = true;

		if (
			(is_numeric($linkedName) || GuidHelper::valid($linkedName))
			&& isset($search['linked_name'])
		)
		{
			$key_field = GuidHelper::valid($linkedName) ? 'guid' : 'id';

			$resolved = GetHelper::var(
				$linkedNameTable,
				$linkedName,
				$key_field,
				$search['linked_name']
			);

			if (!$resolved)
			{
				return ['name' => Text::_('COM_COMPONENTBUILDER_NO_FOUND'), 'edit' => false];
			}

			$linkedName = $resolved;
		}

		return ['name' => (string) $linkedName, 'edit' => $edit];
	}

	/**
	 * Resolve the linked item type display.
	 *
	 * @param   array  $match   The match details.
	 * @param   array  $search  The linked search configuration.
	 *
	 * @return  string  The formatted type string (empty if none).
	 * @since   6.1.6
	 */
	protected function resolveTypeName(array $match, array $search): string
	{
		$typeName = $match['type_name'];
		$typeNameTable = $match['type_name_table'];

		if (
			(is_numeric($typeName) || GuidHelper::valid($typeName))
			&& isset($search['type_name'])
		)
		{
			$key_field = GuidHelper::valid($typeName) ? 'guid' : 'id';

			$resolved = GetHelper::var(
				$typeNameTable,
				$typeName,
				$key_field,
				$search['type_name']
			);

			if (!$resolved)
			{
				return '';
			}

			return ' (' . $resolved . ') ';
		}

		if (StringHelper::check($typeName) || is_numeric($typeName))
		{
			return ' (' . $typeName . ') ';
		}

		return '';
	}

	/**
	 * Build the edit button for a linked item.
	 *
	 * @param   object  $item    The database row item.
	 * @param   array   $search  The linked search configuration.
	 * @param   bool    $edit    Whether edit access is enabled.
	 *
	 * @return  string
	 * @since   6.1.6
	 */
	protected function buildLinkedEditButton(object $item, array $search, bool $edit): string
	{
		if (!$edit)
		{
			return '';
		}

		return ComponentbuilderHelper::getEditButton(
			$item->id,
			$search['table'],
			$search['tables'],
			$this->ref
		);
	}

	/**
	 * Check if we have a GUID match.
	 *
	 * @param   string|null   $guid     The active power GUID.
	 * @param   string|array  $setGuid  The linked power GUID.
	 *
	 * @return  bool  True if match is found.
	 * @since   3.0.0
	 */
	protected function linkedGuid(?string $guid, $setGuid): bool
	{
		if ($guid === null || !GuidHelper::valid($guid))
		{
			return false;
		}

		if (
			is_string($setGuid)
			&& GuidHelper::valid($setGuid)
			&& $guid === $setGuid
		)
		{
			return true;
		}

		if (is_array($setGuid) && in_array($guid, $setGuid))
		{
			return true;
		}

		return false;
	}

	/**
	 * Converts Add Api Type values into human-readable strings.
	 *
	 * This function translates numeric values into add API types.
	 *
	 * @param string $header The header name (not used in this function but kept for consistency).
	 * @param mixed  $value  The numeric value representing the Api Type.
	 *
	 * @return string The human-readable label for the add API Type. Returns "not set" if the value is invalid.
	 * @since  6.1.6
	 */
	protected function setAddApiType($header, $value)
	{
		switch ($value)
		{
			case 0:
				return Text::_('COM_COMPONENTBUILDER_NONE');
			break;
			case 1:
				return Text::_('COM_COMPONENTBUILDER_LIST');
			break;
			case 2:
				return Text::_('COM_COMPONENTBUILDER_BOTH');
			break;
			case 3:
				return Text::_('COM_COMPONENTBUILDER_ITEM');
			break;
		}
		return  Text::_('COM_COMPONENTBUILDER_NOT_SET');
	}

	/**
	 * The view persistence details
	 *
	 * @var	  array
	 * @since 3.0.13
	 */
	protected array $viewid = [];

	/**
	 * Get the view details via the session
	 *
	 * @input   string   $call   The persistence key
	 *
	 * @return array
	 * @since  3.0.13
	 */
	protected function getViewID(string $call = 'table'): array
	{
		if (!isset($this->viewid[$call]))
		{
			// get the vdm key
			$app = $this->app ?? Factory::getApplication();
			$input = $this->input ?? (method_exists($app, 'getInput') ? $app->getInput() : $app->input);
			$vdm = $input->get('vdm', null, 'WORD');

			if ($vdm)
			{
				// set view and id
				if (($view = SessionHelper::get($vdm)) !== null)
				{
					$current = (array) explode('__', $view);
					if (StringHelper::check($current[0]) && isset($current[1]) && is_numeric($current[1]))
					{
						// get the view name & id
						$this->viewid[$call] = array(
							'a_id' => (int) $current[1],
							'a_view' => $current[0]
						);
					}
				}

				// set GUID if found
				if (($guid = SessionHelper::get($vdm . '__guid')) !== null)
				{
					if (GuidHelper::valid($guid))
					{
						$this->viewid[$call]['a_guid'] = $guid;
					}
				}

				// set return if found
				if (($return = SessionHelper::get($vdm . '__return')) !== null)
				{
					if (StringHelper::check($return))
					{
						$this->viewid[$call]['a_return'] = $return;
					}
				}
			}
		}

		if (isset($this->viewid[$call]))
		{
			return $this->viewid[$call];
		}

		return [];
	}

	/**
	 * Generate a new-item creation button for a specific type and size.
	 *
	 * @param  string  $type  The item type (view name) for which to create the button.
	 * @param  int     $size  The button size: 1 = large, 2 = medium, 3 = small.
	 *
	 * @return string  The generated HTML button markup, an empty string if unauthorized, or false if type not found.
	 * @since  3.0.0
	 */
	public function getButton(string $type, int $size): string
	{
		// Ensure the type exists in the button definition array.
		if (!isset($this->buttonArray[$type]))
		{
			return '';
		}

		$app = $this->app ?? Factory::getApplication();
		$user = method_exists($this, 'getCurrentUser')
			? $this->getCurrentUser()
			: $app->getIdentity();

		// Only add if user is authorized to create this type.
		if (!$user->authorise($type . '.create', 'com_componentbuilder'))
		{
			return '';
		}

		// Build the destination URL.
		$url = 'index.php?option=com_componentbuilder&view=' . rawurlencode($type) . '&layout=edit';

		// Initialize reference string based on current view context.
		$values = $this->getViewID();

		if (!empty($values['a_id']) && !empty($values['a_view']))
		{
			$returnUrl = 'index.php?option=com_componentbuilder&view=' . (string) $values['a_view']
				. '&layout=edit&id=' . (int) $values['a_id'];

			// Add return if available.
			if (!empty($values['a_return']))
			{
				$returnUrl .= '&return=' . (string) $values['a_return'];
			}

			$url .= '&amp;return=' . urlencode(base64_encode($returnUrl));

			// Add GUID initialization if available.
			if (!empty($values['a_guid']))
			{
				$url .= '&amp;init_defaults=' . urlencode((string) json_encode([
					$values['a_view'] => $values['a_guid']
				]));
			}
		}

		// Confirmation text.
		$confirmText = Text::_('COM_COMPONENTBUILDER_ALL_UNSAVED_WORK_ON_THIS_PAGE_WILL_BE_LOST_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE');

		// Safe label for title.
		$safeType = StringHelper::safe($type, 'W');
		$title = Text::sprintf('COM_COMPONENTBUILDER_CREATE_NEW_S', $safeType);

		// Build Joomla dialog confirmation flow.
		$href = ComponentbuilderHelper::buildEditHref($url, $confirmText);

		$attributes = $href . ' title="' . $title . '"';

		// Build button markup based on size.
		return match ($size) {
			3 => sprintf(
				'<a class="btn btn-sm btn-success" style="margin:0 0 5px 0;" %s><span class="icon-plus" aria-hidden="true"></span></a>',
				$attributes
			),
			2 => sprintf(
				'<a class="btn btn-success vdm-button-new" %s><span class="icon-plus" aria-hidden="true"></span> %s</a>',
				$attributes,
				Text::_('COM_COMPONENTBUILDER_CREATE')
			),
			default => sprintf(
				'<div class="control-group">
					<div class="control-label">
						<label>%s</label>
					</div>
					<div class="controls">
						<a class="btn btn-success vdm-button-new" %s>
							<span class="icon-plus" aria-hidden="true"></span> %s
						</a>
					</div>
				</div>',
				$type,
				$attributes,
				Text::_('COM_COMPONENTBUILDER_NEW')
			),
		};
	}

	/**
	 * Generate a context-aware create/edit button tied to an item ID.
	 *
	 * @param  string  $type  The item type (view name) for which to create or edit the button.
	 * @param  int     $size  The button size: 1 = large, 2 = medium, 3 = small.
	 *
	 * @return string  The generated HTML button markup, or an empty string if unauthorized or unavailable.
	 * @since  3.0.0
	 */
	public function getButtonID(string $type, int $size): string
	{
		// Ensure the type exists in the button definition array.
		if (!isset($this->buttonArray[$type]))
		{
			return '';
		}

		$app = $this->app ?? Factory::getApplication();
		$user = method_exists($this, 'getCurrentUser')
			? $this->getCurrentUser()
			: $app->getIdentity();

		// Only add if user is authorized to create this type.
		if (!$user->authorise($type . '.create', 'com_componentbuilder'))
		{
			return '';
		}

		// Initialize.
		$values = $this->getViewID();
		$cssClass = 'control-group-' . StringHelper::safe($type . '-' . $size, 'L', '-');

		// Require both ID and view to continue.
		if (empty($values['a_id']) || empty($values['a_view']))
		{
			// Show an info message only for large buttons.
			if ($size === 1)
			{
				return sprintf(
					'<div class="control-group %s"><div class="alert alert-info">%s</div></div>',
					htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8'),
					htmlspecialchars(
						Text::sprintf(
							'Button to create %s will show once %s is saved for the first time.',
							StringHelper::safe($type, 'w'),
							StringHelper::safe((string) ($values['a_view'] ?? ''), 'w')
						),
						ENT_QUOTES,
						'UTF-8'
					)
				);
			}

			return '';
		}

		// Build return URL.
		$returnUrl = 'index.php?option=com_componentbuilder&view=' . (string) $values['a_view']
			. '&layout=edit&id=' . (int) $values['a_id'];

		if (!empty($values['a_return']))
		{
			$returnUrl .= '&return=' . (string) $values['a_return'];
		}

		$ref = '&amp;return=' . urlencode(base64_encode($returnUrl));

		// Key value defaults to the item ID.
		$keyValue = $values['a_id'];

		// Add GUID defaults if available.
		if (!empty($values['a_guid']))
		{
			$ref .= '&amp;init_defaults=' . urlencode((string) json_encode(
				[
					$values['a_view'] => $values['a_guid'],
				],
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
			));
			$keyValue = $values['a_guid'];
		}

		// Determine if the related record already exists.
		$id = GetHelper::var(
			$type,
			$keyValue,
			$values['a_view'],
			'id'
		);

		$isExisting = (!empty($id) && (int) $id > 0);

		// Determine button details.
		if ($isExisting)
		{
			$buttonText = Text::sprintf(
				'Edit %s for this %s',
				StringHelper::safe($type, 'w'),
				StringHelper::safe($values['a_view'], 'w')
			);
			$buttonTextSmall = Text::_('COM_COMPONENTBUILDER_EDIT');
			$targetUrl = 'index.php?option=com_componentbuilder&view=' . $this->buttonArray[$type]
				. '&task=' . $type . '.edit&id=' . (int) $id . $ref;
			$icon = 'icon-edit';
		}
		else
		{
			$buttonText = Text::sprintf(
				'Create %s for this %s',
				StringHelper::safe($type, 'w'),
				StringHelper::safe($values['a_view'], 'w')
			);
			$buttonTextSmall = Text::_('COM_COMPONENTBUILDER_CREATE');
			$targetUrl = 'index.php?option=com_componentbuilder&view=' . $type
				. '&layout=edit' . $ref;
			$icon = 'icon-plus';
		}

		// Confirmation message aligned with the Joomla dialog flow used in your example.
		$confirmText = Text::_('COM_COMPONENTBUILDER_ALL_UNSAVED_WORK_ON_THIS_PAGE_WILL_BE_LOST_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE');

		$href = ComponentbuilderHelper::buildEditHref($targetUrl, $confirmText);

		$buttonAttributes = $href . ' title="' . $buttonText . '"';

		// Build button markup.
		$output = [];

		if ($size === 1)
		{
			$output[] = '<div class="control-group ' . $cssClass . '">';
			$output[] = '	<div class="control-label">';
			$output[] = '		<label>' . StringHelper::safe($type, 'Ww') . '</label>';
			$output[] = '	</div>';
			$output[] = '	<div class="controls">';
			$output[] = '		<a class="btn btn-success vdm-button-new" ' . $buttonAttributes . '>';
			$output[] = '			<span class="' . $icon . '" aria-hidden="true"></span> ' . $buttonText;
			$output[] = '		</a>';
			$output[] = '	</div>';
			$output[] = '</div>';
		}
		elseif ($size === 2)
		{
			$output[] = '<a class="btn btn-success vdm-button-new" ' . $buttonAttributes . '>';
			$output[] = '	<span class="' . $icon . '" aria-hidden="true"></span> ' . $buttonTextSmall;
			$output[] = '</a>';
		}
		elseif ($size === 3)
		{
			$output[] = '<a class="btn btn-sm btn-success" ' . $buttonAttributes . '>';
			$output[] = '	<span class="' . $icon . '" aria-hidden="true"></span>';
			$output[] = '</a>';
		}

		return implode("\n", $output);
	}

	/**
	 * Build a subform HTML table from JSON or array data.
	 *
	 * @param  string       $idName  The subform identifier.
	 * @param  string|array $data    The JSON string or array containing subform data.
	 *
	 * @return string|false  The rendered HTML table, or false if no valid data.
	 * @since  3.0.0
	 */
	protected function getSubformTable(string $idName, string|array $data): string|false
	{
		// Decode JSON if necessary
		if (JsonHelper::check($data))
		{
			$data = json_decode($data, true);
		}

		// Ensure we have a valid array and identifier
		if (
			!UtilitiesArrayHelper::check($data)
			|| !StringHelper::check($idName)
		)
		{
			return false;
		}

		// --- Build table headers --------------------------------------------------
		$head = [];

		foreach ($data as $headers)
		{
			foreach ($headers as $header => $value)
			{
				if (!isset($head[$header]))
				{
					$head[$header] = $this->getLanguage($idName . '|=VDM=|' . $header);
				}
			}
		}

		// --- Build table rows -----------------------------------------------------
		$rows = [];

		if (
			UtilitiesArrayHelper::check($data)
			&& UtilitiesArrayHelper::check($head)
		)
		{
			foreach ($data as $nr => $values)
			{
				foreach ($head as $key => $_header)
				{
					$value = $values[$key] ?? '';
					$this->setSubformRows(
						$nr,
						$this->setSubformValue($key, $value),
						$rows,
						$_header
					);
				}
			}
		}

		// --- Return final table ---------------------------------------------------
		if (
			UtilitiesArrayHelper::check($rows)
			&& UtilitiesArrayHelper::check($head)
		)
		{
			$this->rowNumber = count($rows);

			return ComponentbuilderHelper::setSubformTable($head, $rows, $idName);
		}

		return false;
	}

	/**
	 * Process and sanitize a single subform value.
	 *
	 * @param  string  $header  The header key for the value.
	 * @param  mixed   $value   The raw value to process.
	 *
	 * @return string|mixed  The processed or original value, with fallback.
	 * @since  3.0.0
	 */
	protected function setSubformValue(string $header, mixed $value): mixed
	{
		// Run mapped function if available
		if (
			array_key_exists($header, $this->functionArray)
			&& method_exists($this, $this->functionArray[$header])
		)
		{
			$value = $this->{$this->functionArray[$header]}($header, $value);
		}

		// Fallback if value is empty or invalid
		if (!StringHelper::check($value))
		{
			$value = '-';
		}

		return $value;
	}

	/**
	 * Build subform row cells for the HTML table.
	 *
	 * @param  int|string $nr      The row number/index.
	 * @param  mixed      $value   The processed cell value.
	 * @param  array      &$rows   Reference to the rows array being built.
	 * @param  string     $header  The column header (used for data attributes).
	 *
	 * @return void
	 * @since  3.0.0
	 */
	protected function setSubformRows(int|string $nr, mixed $value, array &$rows, string $header): void
	{
		$cell = sprintf(
			'<td data-column="%s">%s</td>',
			htmlspecialchars($header, ENT_QUOTES, 'UTF-8'),
			(string) $value
		);

		$rows[$nr] = ($rows[$nr] ?? '') . $cell;
	}

	/**
	 * Generate the HTML display for fields linked to a specific view via an AJAX request.
	 *
	 * This method retrieves and builds the HTML for fields linked to a given view type.
	 * It validates the view and type, constructs proper return URLs, fetches related field tables,
	 * and wraps them in structured HTML markup. Informational or error alerts are returned if
	 * the requested type or view is invalid.
	 *
	 * @param  string  $type  The type of fields to retrieve and display.
	 *
	 * @return string  The generated HTML output:
	 *                 - HTML list of fields when linked.
	 *                 - Informational alert if no fields are linked.
	 *                 - Error alert for invalid or missing type.
	 * @since  3.0.0
	 */
	public function getAjaxDisplay(string $type): string
	{
		// Ensure the field type exists
		if (!isset($this->fieldsArray[$type]))
		{
			return '<div class="control-group"><div class="alert alert-error">'
				. '<h4>' . Text::_('COM_COMPONENTBUILDER_TYPE_ERROR') . '</h4>'
				. '<p>' . Text::_('COM_COMPONENTBUILDER_THERE_HAS_BEEN_AN_ERROR_IF_THIS_CONTINUES_PLEASE_INFORM_YOUR_SYSTEM_ADMINISTRATOR_OF_A_TYPE_ERROR_IN_THE_FIELDS_DISPLAY_REQUEST') . '</p>'
				. '</div></div>';
		}

		$typeName = StringHelper::safe($type, 'w');
		$values   = $this->getViewID();

		// Validate view context
		$isValidView =
			GuidHelper::valid($values['a_guid'] ?? '') &&
			!empty($values['a_id']) &&
			!empty($values['a_view']) &&
			in_array($values['a_view'], $this->allowedViewsArray, true);

		if (!$isValidView)
		{
			return sprintf(
				'<div class="control-group"><div class="alert alert-info">%s</div></div>',
				Text::sprintf('COM_COMPONENTBUILDER_NO_S_HAVE_BEEN_LINKED_TO_THIS_VIEW_AS_SOON_AS_THEY_ARE_THEY_WILL_BE_DISPLAYED_HERE',
					$typeName
				)
			);
		}

		// --- Build return reference -------------------------------------------------
		$returnUrl = sprintf(
			'index.php?option=com_componentbuilder&view=%s&layout=edit&id=%d',
			$values['a_view'],
			(int) $values['a_id']
		);

		if (!empty($values['a_return']))
		{
			$returnUrl .= '&return=' . (string) $values['a_return'];
		}

		$this->ref = sprintf(
			'&ref=%s&refid=%d&return=%s',
			$values['a_view'],
			(int) $values['a_id'],
			urlencode(base64_encode($returnUrl))
		);

		$target = $values['a_guid'] ?? null;
		$result = [];

		// --- Retrieve field tables --------------------------------------------------
		$fieldList = $this->fieldsArray[$type];
		if (UtilitiesArrayHelper::check($fieldList))
		{
			// If it's an array of fields
			foreach ((array) $fieldList as $fieldName)
			{
				if ($table = $this->getFieldTable($type, $target, $values['a_view'], $fieldName, $typeName))
				{
					$result[] = $table;
				}
			}
		}
		elseif (StringHelper::check($fieldList))
		{
			// Single field
			if ($table = $this->getFieldTable($type, $target, $values['a_view'], $fieldList, $typeName))
			{
				$result[] = $table;
			}
		}

		// --- Generate HTML output ---------------------------------------------------
		if (!UtilitiesArrayHelper::check($result))
		{
			return sprintf(
				'<div class="control-group"><div class="alert alert-info">%s</div></div>',
				Text::sprintf('COM_COMPONENTBUILDER_NO_S_HAVE_BEEN_LINKED_TO_THIS_VIEW_AS_SOON_AS_THEY_ARE_THEY_WILL_BE_DISPLAYED_HERE',
					$typeName
				)
			);
		}

		// Combine results
		$html = count($result) === 1
			? $result[0]
			: '<div>' . implode('</div><div>', $result) . '</div>';

		return $html;
	}

	/**
	 * Sets and retrieves item names with optional edit links.
	 *
	 * This method processes a list of items or a single item based on the provided header and value.
	 * It fetches the item names from a predefined table, appends additional information if required, 
	 * and generates edit links if supported. If no items are found, an appropriate message is returned.
	 *
	 * @param string $header The key used to access the item configuration (`itemKeys` and `itemNames`).
	 * @param mixed  $value  The value(s) to process. Can be a single numeric or GUID value, or an array of such values.
	 *
	 * @return string A string containing the processed item names and optional edit links, separated by `<br />`.
	 *
	 * @throws \RuntimeException If a required dependency or method is missing.
	 * @since  3.0.9
	 */
	protected function setItemNames(string $header, $value)
	{
		// Check if the header exists in itemKeys and if the corresponding table is defined
		if (!isset($this->itemKeys[$header]) || !isset($this->itemKeys[$header]['table']) || !isset($this->itemNames[$this->itemKeys[$header]['table']]))
		{
			return Text::_('COM_COMPONENTBUILDER_NO_ITEM_FOUND');
		}

		// Check for helper methods
		$guidEdit = method_exists(ComponentbuilderHelper::class, 'getEditButtonGUID');
		$getEdit = method_exists(ComponentbuilderHelper::class, 'getEditButton');

		// Initialize bucket to hold processed items
		$bucket = [];

		// Process an array of values
		if (is_array($value) && UtilitiesArrayHelper::check($value))
		{
			foreach ($value as $item)
			{
				$bucket[] = $this->processItemName($header, $item, $guidEdit, $getEdit);
			}
		} 
		// Process a single value
		elseif (is_numeric($value) || GuidHelper::valid($value))
		{
			$bucket[] = $this->processItemName($header, $value, $guidEdit, $getEdit);
		}

		// Return processed items or a "No items found" message
		if (!empty($bucket))
		{
			return implode('<br />', $bucket);
		}

		return Text::sprintf('COM_COMPONENTBUILDER_NO_S_FOUND', $this->itemKeys[$header]['text'] ?? 'Item');
	}

	/**
	 * Processes a single item and retrieves its name with an optional edit link.
	 *
	 * @param string  $header    The key used to access the item configuration.
	 * @param mixed   $item      The item value (ID or GUID) to process.
	 * @param bool    $guidEdit  Whether GUID edit links are supported.
	 * @param bool    $getEdit   Whether standard edit links are supported.
	 *
	 * @return string The processed item name with an optional edit link.
	 * @since  3.0.9
	 */
	private function processItemName(string $header, $item, bool $guidEdit, bool $getEdit): string
	{
		$edit = true;

		// Fetch the item name if not already cached
		if (!isset($this->itemNames[$this->itemKeys[$header]['table']][$item]))
		{
			$this->itemNames[$this->itemKeys[$header]['table']][$item] = GetHelper::var(
				$this->itemKeys[$header]['table'],
				$item,
				$this->itemKeys[$header]['key'],
				$this->itemKeys[$header]['name']
			);

			// Handle the case where the item was not found
			if (empty($this->itemNames[$this->itemKeys[$header]['table']][$item]))
			{
				$this->itemNames[$this->itemKeys[$header]['table']][$item] = Text::sprintf('COM_COMPONENTBUILDER_NO_S_FOUND', $this->itemKeys[$header]['text']);
				$edit = false;
			}

			// Append additional information if configured
			if (
				$edit &&
				isset($this->itemKeys[$header]['get']) &&
				StringHelper::check($this->itemKeys[$header]['get']) &&
				method_exists(__CLASS__, $this->itemKeys[$header]['get'])
			) {
				$this->itemNames[$this->itemKeys[$header]['table']][$item] .= $this->{$this->itemKeys[$header]['get']}($item);
			}
		}

		// Generate the edit link based on the type of item (GUID or ID)
		if (GuidHelper::valid($item))
		{
			$link = ($edit && $guidEdit)
				? ComponentbuilderHelper::getEditButtonGUID($item, $this->itemKeys[$header]['key'], $this->itemKeys[$header]['table'], $this->itemKeys[$header]['tables'], $this->ref)
				: '';
		}
		else
		{
			$link = ($edit && $getEdit)
				? ComponentbuilderHelper::getEditButton($item, $this->itemKeys[$header]['table'], $this->itemKeys[$header]['tables'], $this->ref)
				: '';
		}

		// Return the item name with the edit link
		return $this->itemNames[$this->itemKeys[$header]['table']][$item] . $link;
	}

	/**
	 * Retrieves the HTML table for a specific field type.
	 *
	 * This method fetches field data and generates an HTML table for the given field type. It also checks for
	 * repeatable conversion if applicable, validates the number of rows, and returns appropriate notices 
	 * about best practices if limits are exceeded.
	 *
	 * @param string $type        The type of the field (e.g., 'subform', 'repeatable').
	 * @param mixed  $target      The ID|GUID of the parent entity.
	 * @param string $targetKey   The name of the ID column in the database.
	 * @param string $fieldName   The name of the field being processed.
	 * @param string $typeName    The display name of the field type (used in notices).
	 *
	 * @return string|null  Returns the generated HTML table with optional notices on success, or `false` if no data is found.
	 *
	 * @throws \RuntimeException If a required dependency or method is missing.
	 * @since  3.0.0
	 */
	protected function getFieldTable($type, $target, $targetKey, $fieldName, $typeName): ?string
	{
		// Fetch field data
		$fieldsData = GetHelper::var($type, $target, $targetKey, $fieldName);

		if ($fieldsData === false)
		{
			// Return false if no field data is found
			return null;
		}

		// Generate the subform table
		$table = $this->getSubformTable($type, $fieldsData);

		// Determine the maximum allowed number of rows
		$maxRows = $this->rowNumbers[$type] ?? false;

		// Generate a notice about best practices if the number of rows exceeds the limit
		$notice = '';
		if (isset($this->rowNumber) && $maxRows)
		{
			if ($this->rowNumber > $maxRows)
			{
				$notice = '<div class="alert alert-warning">' . Text::sprintf('COM_COMPONENTBUILDER_YOU_HAVE_S_S_ADDING_MORE_THAN_S_S_IS_CONSIDERED_BAD_PRACTICE_YOUR_S_PAGE_LOAD_IN_JCB_WILL_SLOW_DOWN_YOU_SHOULD_CONSIDER_DECOUPLING_SOME_OF_THESE_S',
					$this->rowNumber,
					$typeName,
					$maxRows,
					$typeName,
					$typeName,
					$typeName
				) . '</div>';
			}
			else
			{
				$notice = '<div class="alert alert-info">' . Text::sprintf('COM_COMPONENTBUILDER_YOU_HAVE_S_S_ADDING_MORE_THAN_S_S_IS_CONSIDERED_BAD_PRACTICE',
					$this->rowNumber,
					$typeName,
					$maxRows,
					$typeName
				) . '</div>';
			}
		}

		// Return the notice (if any) concatenated with the table HTML
		return $notice . $table;
	}

	// Used in template
	/**
	 * Get template code snippet details for other published templates.
	 *
	 * @param   int  $id  The current template ID to exclude.
	 *
	 * @return  string|false  The generated Bootstrap table HTML, or false when no templates are found.
	 * @since   3.0.0
	 */
	public function getTemplateDetails($id)
	{
		// Get the matching template records.
		$results = $this->getPublishedTemplateDetails((int) $id);

		if (empty($results))
		{
			return false;
		}

		// Get the current view context used for edit references.
		$values = $this->getViewID();
		$ref    = $this->buildTemplateReference($values);

		// Build all table rows.
		$rows = [];

		foreach ($results as $result)
		{
			$rows[] = $this->buildTemplateDetailsRow($result, $ref);
		}

		// Build and return the Bootstrap table.
		return $this->buildTemplateDetailsTable($rows);
	}

	/**
	 * Get all published template details except the current one.
	 *
	 * @param   int  $id  The current template ID to exclude.
	 *
	 * @return  array  The matching template records.
	 * @since   6.1.6
	 */
	protected function getPublishedTemplateDetails(int $id): array
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				[
					'a.id',
					'a.alias',
					'a.template',
					'b.name',
					'a.dynamic_get',
				]
			)
		)
			->from($db->quoteName('#__componentbuilder_template', 'a'))
			->join(
				'LEFT',
				$db->quoteName('#__componentbuilder_dynamic_get', 'b')
				. ' ON (' . $db->quoteName('b.guid') . ' = ' . $db->quoteName('a.dynamic_get') . ')'
			)
			->where($db->quoteName('a.id') . ' != ' . $id)
			->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);

		return (array) $db->loadObjectList();
	}

	/**
	 * Build the template edit reference string.
	 *
	 * @param   array  $values  The current view values.
	 *
	 * @return  string  The reference query string.
	 * @since   6.1.6
	 */
	protected function buildTemplateReference(array $values): string
	{
		$view = isset($values['a_view']) ? (string) $values['a_view'] : '';
		$id   = isset($values['a_id']) ? (int) $values['a_id'] : 0;

		if ($id <= 0 || $view === '')
		{
			return '';
		}

		$returnUrl = 'index.php?option=com_componentbuilder&view=' . $view . '&layout=edit&id=' . $id;

		if (isset($values['a_return']))
		{
			$returnUrl .= '&return=' . (string) $values['a_return'];
		}

		return '&ref=' . $view . '&refid=' . $id . '&return=' . urlencode(base64_encode($returnUrl));
	}

	/**
	 * Build a single template details table row.
	 *
	 * @param   object  $result  The template result object.
	 * @param   string  $ref     The reference query string.
	 *
	 * @return  string  The HTML table row.
	 * @since   6.1.6
	 */
	protected function buildTemplateDetailsRow(object $result, string $ref): string
	{
		$edit    = $this->getTemplateEditButton((int) $result->id, $ref);
		$editGet = $this->getTemplateDynamicGetEditButton($result, $ref);
		$name    = $this->getTemplateDynamicGetName($result);
		$snippet = $this->buildTemplateSnippet($result);

		return '<tr>'
			. '<td><strong>' . $name . '</strong> ' . $editGet . '</td>'
			. '<td><code>' . $snippet . '</code> ' . $edit . '</td>'
			. '</tr>';
	}

	/**
	 * Get the template edit button HTML.
	 *
	 * @param   int     $id   The template ID.
	 * @param   string  $ref  The reference query string.
	 *
	 * @return  string  The button HTML or empty string.
	 * @since   6.1.6
	 */
	protected function getTemplateEditButton(int $id, string $ref): string
	{
		$button = ComponentbuilderHelper::getEditButton($id, 'template', 'templates', $ref);

		return ($button !== false) ? $button : '';
	}

	/**
	 * Get the dynamic get edit button HTML for the template.
	 *
	 * @param   object  $result  The template result object.
	 * @param   string  $ref     The reference query string.
	 *
	 * @return  string  The button HTML or empty string.
	 * @since   6.1.6
	 */
	protected function getTemplateDynamicGetEditButton(object $result, string $ref): string
	{
		if (!isset($result->dynamic_get) || (int) $result->dynamic_get <= 0)
		{
			return '';
		}

		$button = ComponentbuilderHelper::getEditButton((int) $result->dynamic_get, 'dynamic_get', 'dynamic_gets', $ref);

		return ($button !== false) ? $button : '';
	}

	/**
	 * Get the display name of the dynamic get for the template.
	 *
	 * @param   object  $result  The template result object.
	 *
	 * @return  string  The display name.
	 * @since   6.1.6
	 */
	protected function getTemplateDynamicGetName(object $result): string
	{
		if (isset($result->name) && StringHelper::check($result->name))
		{
			return (string) $result->name;
		}

		return Text::_('COM_COMPONENTBUILDER_NONE_SELECTED');
	}

	/**
	 * Build the template snippet string.
	 *
	 * @param   object  $result  The template result object.
	 *
	 * @return  string  The template snippet.
	 * @since   6.1.6
	 */
	protected function buildTemplateSnippet(object $result): string
	{
		$alias = StringHelper::safe((string) $result->alias);

		return "&lt;?php echo \$this->loadTemplate('" . $alias . "'); ?&gt;";
	}

	/**
	 * Build the Bootstrap-based template details table.
	 *
	 * @param   array  $rows  The table rows.
	 *
	 * @return  string  The full HTML table output.
	 * @since   6.1.6
	 */
	protected function buildTemplateDetailsTable(array $rows): string
	{
		$html = [];
		$html[] = '<div class="mt-4">';
		$html[] = '<h2>' . Text::_('COM_COMPONENTBUILDER_TEMPLATE_CODE_SNIPPETS') . '</h2>';
		$html[] = '<p class="text-muted">' . Text::_('COM_COMPONENTBUILDER_TO_ADD_SIMPLY_COPY_AND_PAST_THE_SNIPPET_INTO_YOUR_CODE') . '</p>';
		$html[] = '<div class="border rounded p-2 bg-body-tertiary" style="max-height: 380px; overflow: auto;">';
		$html[] = '<div class="table-responsive">';
		$html[] = '<table class="table table-striped table-hover table-sm align-middle mb-0">';
		$html[] = '<caption class="caption-top">' . Text::_('COM_COMPONENTBUILDER_AVAILABLE_TEMPLATE_RENDERING_SNIPPETS') . '</caption>';
		$html[] = '<thead>';
		$html[] = '<tr>';
		$html[] = '<th scope="col">' . Text::_('COM_COMPONENTBUILDER_NAME_OF_DYNAMICGET') . '</th>';
		$html[] = '<th scope="col">' . Text::_('COM_COMPONENTBUILDER_SNIPPET') . '</th>';
		$html[] = '</tr>';
		$html[] = '</thead>';
		$html[] = '<tbody>';
		$html[] = implode('', $rows);
		$html[] = '</tbody>';
		$html[] = '</table>';
		$html[] = '</div>';
		$html[] = '</div>';
		$html[] = '</div>';

		return implode('', $html);
	}

	// Used in layout
	/**
	 * Get layout code snippet details for other published layouts.
	 *
	 * @param   int  $id  The current layout ID to exclude.
	 *
	 * @return  string|false  The generated Bootstrap table HTML, or false when no layouts are found.
	 * @since   3.0.0
	 */
	public function getLayoutDetails($id)
	{
		// Get the matching layout records.
		$results = $this->getPublishedLayoutDetails((int) $id);

		if (empty($results))
		{
			return false;
		}

		// Get the current view context used for edit references.
		$values = $this->getViewID();
		$ref    = $this->buildLayoutReference($values);

		// Build all table rows.
		$rows = [];

		foreach ($results as $result)
		{
			$rows[] = $this->buildLayoutDetailsRow($result, $ref);
		}

		// Build and return the Bootstrap table.
		return $this->buildLayoutDetailsTable($rows);
	}

	/**
	 * Get all published layout details except the current one.
	 *
	 * @param   int  $id  The current layout ID to exclude.
	 *
	 * @return  array  The matching layout records.
	 * @since   6.1.6
	 */
	protected function getPublishedLayoutDetails(int $id): array
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				[
					'a.id',
					'a.alias',
					'a.layout',
					'b.getcustom',
					'b.gettype',
					'b.name',
					'a.dynamic_get',
				]
			)
		)
			->from($db->quoteName('#__componentbuilder_layout', 'a'))
			->join(
				'LEFT',
				$db->quoteName('#__componentbuilder_dynamic_get', 'b')
				. ' ON (' . $db->quoteName('b.guid') . ' = ' . $db->quoteName('a.dynamic_get') . ')'
			)
			->where($db->quoteName('a.id') . ' != ' . $id)
			->where($db->quoteName('a.published') . ' = 1');

		$db->setQuery($query);

		return (array) $db->loadObjectList();
	}

	/**
	 * Build the layout edit reference string.
	 *
	 * @param   array  $values  The current view values.
	 *
	 * @return  string  The reference query string.
	 * @since   6.1.6
	 */
	protected function buildLayoutReference(array $values): string
	{
		$view = isset($values['a_view']) ? (string) $values['a_view'] : '';
		$id   = isset($values['a_id']) ? (int) $values['a_id'] : 0;

		if ($id <= 0 || $view === '')
		{
			return '';
		}

		$returnUrl = 'index.php?option=com_componentbuilder&view=' . $view . '&layout=edit&id=' . $id;

		if (isset($values['a_return']))
		{
			$returnUrl .= '&return=' . (string) $values['a_return'];
		}

		return '&ref=' . $view . '&refid=' . $id . '&return=' . urlencode(base64_encode($returnUrl));
	}

	/**
	 * Build a single layout details table row.
	 *
	 * @param   object  $result  The layout result object.
	 * @param   string  $ref     The reference query string.
	 *
	 * @return  string  The HTML table row.
	 * @since   6.1.6
	 */
	protected function buildLayoutDetailsRow(object $result, string $ref): string
	{
		$edit    = $this->getLayoutEditButton((int) $result->id, $ref);
		$editGet = $this->getDynamicGetEditButton($result, $ref);
		$name    = $this->getDynamicGetName($result);
		$snippet = $this->buildLayoutSnippet($result);

		return '<tr>'
			. '<td><strong>' . $name . '</strong> ' . $editGet . '</td>'
			. '<td><code>' . $snippet . '</code> ' . $edit . '</td>'
			. '</tr>';
	}

	/**
	 * Get the layout edit button HTML.
	 *
	 * @param   int     $id   The layout ID.
	 * @param   string  $ref  The reference query string.
	 *
	 * @return  string  The button HTML or empty string.
	 * @since   6.1.6
	 */
	protected function getLayoutEditButton(int $id, string $ref): string
	{
		$button = ComponentbuilderHelper::getEditButton($id, 'layout', 'layouts', $ref);

		return ($button !== false) ? $button : '';
	}

	/**
	 * Get the dynamic get edit button HTML.
	 *
	 * @param   object  $result  The layout result object.
	 * @param   string  $ref     The reference query string.
	 *
	 * @return  string  The button HTML or empty string.
	 * @since   6.1.6
	 */
	protected function getDynamicGetEditButton(object $result, string $ref): string
	{
		if (!isset($result->dynamic_get) || (int) $result->dynamic_get <= 0)
		{
			return '';
		}

		$button = ComponentbuilderHelper::getEditButton((int) $result->dynamic_get, 'dynamic_get', 'dynamic_gets', $ref);

		return ($button !== false) ? $button : '';
	}

	/**
	 * Get the display name of the dynamic get.
	 *
	 * @param   object  $result  The layout result object.
	 *
	 * @return  string  The display name.
	 * @since   6.1.6
	 */
	protected function getDynamicGetName(object $result): string
	{
		if (isset($result->name) && StringHelper::check($result->name))
		{
			return (string) $result->name;
		}

		return Text::_('COM_COMPONENTBUILDER_NONE_SELECTED');
	}

	/**
	 * Build the layout snippet string.
	 *
	 * @param   object  $result  The layout result object.
	 *
	 * @return  string  The layout snippet.
	 * @since   6.1.6
	 */
	protected function buildLayoutSnippet(object $result): string
	{
		$alias = StringHelper::safe((string) $result->alias);

		switch ((int) $result->gettype)
		{
			case 1:
				return "&lt;?php echo Joomla__" . "_7ab82272_0b3d_4bb1_af35_e63a096cfe0b___Power::render('" . $alias . "', \$this->item); ?&gt;";

			case 2:
				return "&lt;?php echo Joomla__" . "_7ab82272_0b3d_4bb1_af35_e63a096cfe0b___Power::render('" . $alias . "', \$this->items); ?&gt;";

			case 3:
			case 4:
				$varName = $this->getCustomVariableName((string) $result->getcustom);

				return "&lt;?php echo Joomla__" . "_7ab82272_0b3d_4bb1_af35_e63a096cfe0b___Power::render('" . $alias . "', \$this->" . $varName . "); ?&gt;";

			default:
				return "&lt;?php echo Joomla__" . "_7ab82272_0b3d_4bb1_af35_e63a096cfe0b___Power::render('" . $alias . "', [?]); ?&gt;";
		}
	}

	/**
	 * Get the custom variable name from the dynamic get method name.
	 *
	 * @param   string  $getcustom  The custom get name.
	 *
	 * @return  string  The variable name.
	 * @since   6.1.6
	 */
	protected function getCustomVariableName(string $getcustom): string
	{
		$getcustom = StringHelper::safe($getcustom);

		if (substr($getcustom, 0, 3) === 'get')
		{
			return (string) substr($getcustom, 3);
		}

		return $getcustom;
	}

	/**
	 * Build the Bootstrap-based layout details table.
	 *
	 * @param   array  $rows  The table rows.
	 *
	 * @return  string  The full HTML table output.
	 * @since   6.1.6
	 */
	protected function buildLayoutDetailsTable(array $rows): string
	{
		$html = [];
		$html[] = '<div class="mt-4">';
		$html[] = '<h2>' . Text::_('COM_COMPONENTBUILDER_LAYOUT_CODE_SNIPPETS') . '</h2>';
		$html[] = '<p class="text-muted">' . Text::_('COM_COMPONENTBUILDER_TO_ADD_SIMPLY_COPY_AND_PAST_THE_SNIPPET_INTO_YOUR_CODE') . '</p>';
		$html[] = '<div class="border rounded p-2 bg-body-tertiary" style="max-height: 380px; overflow: auto;">';
		$html[] = '<div class="table-responsive">';
		$html[] = '<table class="table table-striped table-hover table-sm align-middle mb-0">';
		$html[] = '<caption class="caption-top">' . Text::_('COM_COMPONENTBUILDER_AVAILABLE_LAYOUT_RENDERING_SNIPPETS') . '</caption>';
		$html[] = '<thead>';
		$html[] = '<tr>';
		$html[] = '<th scope="col">' . Text::_('COM_COMPONENTBUILDER_NAME_OF_DYNAMICGET') . '</th>';
		$html[] = '<th scope="col">' . Text::_('COM_COMPONENTBUILDER_SNIPPET') . '</th>';
		$html[] = '</tr>';
		$html[] = '</thead>';
		$html[] = '<tbody>';
		$html[] = implode('', $rows);
		$html[] = '</tbody>';
		$html[] = '</table>';
		$html[] = '</div>';
		$html[] = '</div>';
		$html[] = '</div>';

		return implode('', $html);
	}

	// Used in dynamic_get
	/**
	 * Retrieve view table columns.
	 *
	 * @param string   $adminView  The admin view identifier.
	 * @param string   $as         The alias to use.
	 * @param int      $type       The type indicator.
	 *
	 * @return mixed The view table columns.
	 * @since  3.0.0
	 */
	public function getViewTableColumns($adminView, $as, $type)
	{
		return ComponentbuilderHelper::getViewTableColumns($adminView, $as, $type);
	}

	/**
	 * Retrieve database table columns.
	 *
	 * @param string  $tableName The name of the database table.
	 * @param string  $as        The alias to use.
	 * @param int     $type      The type indicator.
	 *
	 * @return mixed The database table columns.
	 * @since  3.0.0
	 */
	public function getDbTableColumns($tableName, $as, $type)
	{
		return ComponentbuilderHelper::getDbTableColumns($tableName, $as, $type);
	}

	/**
	 * Retrieve dynamic values based on a key and view.
	 *
	 * The method builds a database query to retrieve dynamic settings, processes main and joined
	 * selections, and returns an HTML code snippet that shows PHP code for outputting these fields.
	 *
	 * @param mixed  $key  The key used to fetch dynamic values (can be a guid or numeric id).
	 * @param string $view The view context (e.g., 'template', 'site_view', 'custom_admin_view', 'layout').
	 *
	 * @return string|null HTML code snippet with dynamic value output or false if key is invalid or no rows found.
	 * @since  3.0.0
	 */
	public function getDynamicValues($key, $view)
	{
		// Determine the target field based on the key.
		if (GuidHelper::valid($key))
		{
			$target = 'guid';
		}
		elseif (is_numeric($key))
		{
			$target = 'id';
		}
		else
		{
			return null;
		}

		// Get a database connection.
		$db = Factory::getDbo();

		// Build the query.
		$query = $db->getQuery(true);
		$fields = [
			'getcustom', 'gettype', 'select_all', 'db_table_main',
			'view_table_main', 'main_source', 'view_selection',
			'db_selection', 'join_view_table', 'join_db_table',
			'addcalculation', 'php_calculation'
		];

		$query->select($db->quoteName($fields))
			  ->from($db->quoteName('#__componentbuilder_dynamic_get'))
			  ->where($db->quoteName('published') . ' = 1')
			  ->where($db->quoteName($target) . ' = ' . $db->quote($key));
		$db->setQuery($query);
		$db->execute();

		if (!$db->getNumRows())
		{
			return null;
		}

		$result = $db->loadObject();

		// Initialize selection arrays.
		$selections = [];
		$selectionsList = [];

		// Process main source selections.
		if ($result->main_source == 1)
		{
			if ($result->select_all == 1)
			{
				$result->view_selection = ComponentbuilderHelper::getViewTableColumns($result->view_table_main, 'a', $result->gettype);
			}
			$selections[] = explode("\n", $result->view_selection);
		}
		elseif ($result->main_source == 2)
		{
			if ($result->select_all == 1)
			{
				$result->db_selection = ComponentbuilderHelper::getDbTableColumns($result->db_table_main, 'a', $result->gettype);
			}
			$selections[] = explode("\n", $result->db_selection);
		}
		elseif ($result->main_source == 3)
		{
			return '<br /><br /><h2>Custom get source! You will need to transpose the variables manually.</h2>';
		}

		// Process joined view table selections.
		$joinViewTable = json_decode($result->join_view_table, true);
		if (UtilitiesArrayHelper::check($joinViewTable))
		{
			list($joinSelections, $joinSelectionsList) = $this->processJoinTables($joinViewTable, true);
			$selections = array_merge($selections, $joinSelections);
			$selectionsList = array_merge($selectionsList, $joinSelectionsList);
		}

		// Process joined database table selections.
		$joinDbTable = json_decode($result->join_db_table, true);
		if (UtilitiesArrayHelper::check($joinDbTable))
		{
			list($joinSelections, $joinSelectionsList) = $this->processJoinTables($joinDbTable, false);
			$selections = array_merge($selections, $joinSelections);
			$selectionsList = array_merge($selectionsList, $joinSelectionsList);
		}

		// Process calculation selections.
		if ($result->addcalculation == 1)
		{
			$phpCalculation = base64_decode($result->php_calculation);
			$phpSelections  = GetHelper::allBetween($phpCalculation, 'cal__', ' ');
			$selections[]   = array_unique($phpSelections);
			unset($phpCalculation, $phpSelections, $result->php_calculation);
		}

		// Determine the bucket variable name based on view and get type.
		$buketName = $this->determineBucketName($result, $view);
		if (empty($buketName))
		{
			return null;
		}

		// Build and return the final HTML code snippet.
		return $this->buildCodeOutput($result->gettype, $buketName, $selections, $selectionsList);
	}

	/**
	 * Process join tables for dynamic selections.
	 *
	 * This method iterates over an array of join table configurations, replacing '*' selections
	 * with full column lists and sorting the results into main selections and selection lists.
	 *
	 * @param array $joinTables An array of join table configurations.
	 * @param bool  $isView True if processing view tables; false if processing DB tables.
	 *
	 * @return array Returns an array with two elements: [selections, selectionsList].
	 * @since  5.0.4
	 */
	protected function processJoinTables(array $joinTables, bool $isView): array
	{
		$selections = [];
		$selectionsList = [];

		foreach ($joinTables as $join)
		{
			// Replace '*' with full selection if applicable.
			if (strpos($join['selection'], '*') !== false)
			{
				$join['selection'] = ComponentbuilderHelper::getViewTableColumns($join['view_table'], $join['as'], $join['row_type']);
			}

			// Process based on the row type.
			if ($join['row_type'] == '1')
			{
				$selections[] = explode("\n", $join['selection']);
			}
			elseif ($join['row_type'] == '2')
			{
				$names = $this->setListMethodName(
					[$join['on_field'], $join['join_field']],
					$isView ? $join['view_table'] : $join['db_table'],
					$join['as'],
					$isView ? 1 : 2
				);
				$selectionsList[implode('', $names)] = explode("\n", $join['selection']);
			}
		}

		return [$selections, $selectionsList];
	}

	/**
	 * Determine the bucket variable name based on the result type and view.
	 *
	 * This method selects the proper variable (e.g. "$this->item" or "$this->items")
	 * or a custom bucket name based on the provided result and view context.
	 *
	 * @param object $result The result object from the database query.
	 * @param string $view   The view context.
	 *
	 * @return string|null The bucket variable name (or an empty string if none can be determined).
	 * @since  5.0.4
	 */
	protected function determineBucketName($result, string $view): ?string
	{
		if (in_array($view, ['template', 'site_view', 'custom_admin_view'], true))
		{
			switch ($result->gettype) {
				case 1:
					return 'this->item';
				case 2:
					return 'this->items';
				case 3:
				case 4:
					$custom  = StringHelper::safe($result->getcustom);
					$varName = (strpos($custom, 'get') === 0) ? substr($custom, 3) : $custom;
					return 'this->' . $varName;
				default:
					return null;
			}
		}
		elseif ($view === 'layout')
		{
			return 'displayData';
		}

		return null;
	}

	/**
	 * Build the HTML code output for dynamic selections.
	 *
	 * This method generates HTML code snippets (using <code> blocks) that output the
	 * dynamic selection values based on the get type. For list types, it wraps the output
	 * in a foreach loop.
	 *
	 * @param int $getType The type of get operation.
	 * @param string $buketName The bucket variable name.
	 * @param array  $selections An array of selections.
	 * @param array  $selectionsList An array of selection lists.
	 *
	 * @return string The HTML code snippet.
	 * @since  5.0.4
	 */
	protected function buildCodeOutput(int $getType, string $buketName, array $selections, array $selectionsList): string
	{
		$outputLines = [];

		// Set the starting code based on the get type.
		switch ($getType)
		{
			case 1:
			case 3:
				$prefix = '&lt;?php echo $' . $buketName;
				$suffix = '; ?&gt;';
				break;
			case 2:
			case 4:
				$prefix = '&lt;?php echo $item';
				$suffix = '; ?&gt;';
				$outputLines[] = '<code>&lt;?php foreach ($' . $buketName . ' as $item): ?&gt;</code><br />';
				break;
			default:
				$prefix = '';
				$suffix = '';
		}

		// Build code lines for the main selections.
		foreach ($selections as $selection)
		{
			if (UtilitiesArrayHelper::check($selection))
			{
				foreach ($selection as $value)
				{
					$value = trim($value);
					if (strpos($value, 'AS') !== false)
					{
						list(, $key) = explode('AS', $value);
						$outputLines[] = '<code>' . $prefix . '->' . trim($key) . $suffix . '</code>';
					}
					else
					{
						$outputLines[] = '<code>' . $prefix . '->' . $value . $suffix . '</code>';
					}
				}
			}
		}

		// Build code lines for selection lists.
		if (UtilitiesArrayHelper::check($selectionsList))
		{
			$outputLines[] = '<hr />';
			foreach ($selectionsList as $name => $selectionList)
			{
				if (UtilitiesArrayHelper::check($selectionList))
				{
					$listPrefix = '&lt;?php echo $' . $name;
					$listSuffix = '; ?&gt;';
					$outputLines[] = '<code>&lt;?php foreach ($item->' . $name . ' as $' . $name . '): ?&gt;</code><br />';
					foreach ($selectionList as $value)
					{
						$value = trim($value);
						if (strpos($value, 'AS') !== false)
						{
							list(, $key) = explode('AS', $value);
							$outputLines[] = '<code>' . $listPrefix . '->' . trim($key) . $listSuffix . '</code>';
						}
						else
						{
							$outputLines[] = '<code>' . $listPrefix . '->' . $value . $listSuffix . '</code>';
						}
					}
					$outputLines[] = '<br /><code>&lt;?php endforeach; ?&gt;</code><hr />';
				}
			}
		}

		// For list types, add the closing foreach.
		if (in_array($getType, [2, 4], true))
		{
			$outputLines[] = '<br /><code>&lt;?php endforeach; ?&gt;</code>';
		}

		return implode('&nbsp;', $outputLines);
	}

	/**
	 * Generate a list method name based on provided names and table.
	 *
	 * This method creates an array of sanitized method/variable names based on input field names,
	 * the table name, and an alias.
	 *
	 * @param array  $names An array of field names.
	 * @param string $table The table name.
	 * @param string $as The alias.
	 * @param int $type  The type flag (1 for view, 2 for database).
	 *
	 * @return array An array of method names.
	 * @since  3.0.0
	 */
	protected function setListMethodName($names, $table, $as, $type)
	{
		$methodNames = [];
		if (UtilitiesArrayHelper::check($names))
		{
			foreach ($names as $nr => $name)
			{
				if (StringHelper::check($name))
				{
					if (strpos($name, '.') !== false)
					{
						list(, $var) = explode('.', $name);
					}
					else
					{
						$var = $name;
					}

					if ($nr > 0)
					{
						$methodNames[] = StringHelper::safe($var, 'F');
					}
					else
					{
						$methodNames[] = StringHelper::safe($var);
					}
				}
			}
		}

		switch ($type)
		{
			// For view tables.
			case 1:
				$methodNames[] = StringHelper::safe($this->getViewName($table), 'F');
				break;
			// For database tables.
			case 2:
				$methodNames[] = StringHelper::safe($table, 'F');
				break;
		}

		$methodNames[] = StringHelper::safe($as, 'U');
		return $methodNames;
	}

	/**
	 * Retrieve the view name based on a key.
	 *
	 * @param mixed $key The key used to determine the view name.
	 *
	 * @return string The view name or an empty string if not found.
	 * @since  3.0.0
	 */
	protected function getViewName($key): string
	{
		if (GuidHelper::valid($key))
		{
			$target = 'guid';
		}
		elseif (is_numeric($key))
		{
			$target = 'id';
		}
		else
		{
			return '';
		}

		// Retrieve the view name.
		if ($name = GetHelper::var('admin_view', $key, $target, 'name_single'))
		{
			return $name;
		}

		return '';
	}

	// Used in custom_code
	/**
	 * Retrieves edit custom code buttons.
	 *
	 * This method orchestrates the process of validating the view,
	 * building a return URL, fetching custom code data from the database,
	 * and processing that data to generate a set of edit buttons.
	 *
	 * @param int $id The identifier for the custom code.
	 *
	 * @return  array|false Returns an array of buttons if successful, or false otherwise.
	 * @since   5.1.0
	 */
	public function getEditCustomCodeButtons(int $id)
	{
		// Validate that the current view request is legitimate.
		$validationResult = $this->validateViewRequest($id);
		if ($validationResult === null)
		{
			return false;
		}
		list($view, $target) = $validationResult;

		// Build the return URL parameter.
		$returnUrl = $this->buildReturnUrl($view, $id);

		// Fetch custom code data from the database.
		$data = $this->fetchCustomCodeData($id, $target);
		if (empty($data))
		{
			return false;
		}

		// Process the data to generate buttons.
		$buttons = $this->processCustomCodeData($data, $target, $returnUrl);

		// Return the buttons if any valid ones are found.
		if (UtilitiesArrayHelper::check($buttons))
		{
			return $buttons;
		}

		return false;
	}

	/**
	 * Checks the validity of a function name.
	 *
	 * This function splits the provided function name at uppercase letters,
	 * sanitizes it, and then verifies if the name is already in use. If the name
	 * exists and belongs to a different record (ID), it returns an error message.
	 * Otherwise, it returns a success message along with the sanitized name.
	 *
	 * @param string $name The function name to validate.
	 * @param int $id   The identifier associated with the function.
	 *
	 * @return array An associative array containing:
	 *               - 'name': The sanitized function name (only on success).
	 *               - 'message': A message indicating success or error.
	 *               - 'status': A status indicator ('success' or 'danger').
	 * @since   5.1.0
	 */
	public function checkFunctionName(string $name, int $id): array
	{
		// Split the name into parts at each uppercase letter.
		$nameArray = $this->splitAtUpperCase($name);
		
		// Reassemble the name with spaces and sanitize it.
		$sanitizedName = StringHelper::safe(implode(' ', $nameArray), 'cA');

		// Check if the function name is already used in the custom code context.
		$found = GetHelper::var('custom_code', $sanitizedName, 'function_name', 'id');
		if ($found && ((int)$id !== (int)$found))
		{
			return [
				'message' => Text::_('COM_COMPONENTBUILDER_SORRY_THIS_FUNCTION_NAME_IS_ALREADY_IN_USE'),
				'status'  => 'danger'
			];
		}

		return [
			'name' => $sanitizedName,
			'message' => Text::_('COM_COMPONENTBUILDER_GREAT_THIS_FUNCTION_NAME_WILL_WORK'),
			'status' => 'success'
		];
	}

	/**
	 * Validates the view request.
	 *
	 * This method checks that the current view has a valid ID and that the code
	 * search keys are available.
	 *
	 * @param int $id The identifier to validate.
	 *
	 * @return  array|null Returns an array with view and target data if valid, or null otherwise.
	 * @since   5.1.0
	 */
	protected function validateViewRequest(int $id): ?array
	{
		$view = $this->getViewID();
		if (isset($view['a_id'], $view['a_view']) && (int) $view['a_id'] === $id)
		{
			$target = $this->getCodeSearchKeys($view['a_view'], 'customcode', 'query_');
			if ($target !== null)
			{
				return [$view, $target];
			}
		}
		return null;
	}

	/**
	 * Builds the return URL parameter.
	 *
	 * This method reads the input for a "return_here" parameter and constructs a URL
	 * fragment accordingly. If no valid return value is found, it builds a reference URL.
	 *
	 * @param array $view The view array.
	 * @param int   $id   The identifier.
	 *
	 * @return string The constructed return URL parameter.
	 * @since  5.1.0
	 */
	protected function buildReturnUrl(array $view, int $id): string
	{
		if (!empty($view['a_return']))
		{
			return '&return=' . (string) $view['a_return'];
		}
		return '&ref=' . $view['a_view'] . '&refid=' . $id;
	}

	/**
	 * Fetches custom code data from the database.
	 *
	 * This method constructs and executes a database query based on the target
	 * configuration and retrieves the associated data.
	 *
	 * @param int   $id The identifier for the data.
	 * @param array $target The target configuration array.
	 *
	 * @return array|null Returns the fetched data as an associative array, or null if not found.
	 * @since  5.1.0
	 */
	protected function fetchCustomCodeData(int $id, array $target): ?array
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName($target['select']))
			->from($db->quoteName('#__componentbuilder_' . $target['table'], 'a'))
			->where('a.id = ' . $id);
		$db->setQuery($query);
		$db->execute();

		if ($db->loadRowList())
		{
			return $db->loadAssoc();
		}
		return null;
	}

	/**
	 * Processes the custom code data to generate buttons.
	 *
	 * This method decodes values if needed, searches for custom code placeholders,
	 * and builds both creation and edit buttons based on the data.
	 *
	 * @param array  $data The associative array of custom code data.
	 * @param array  $target The target configuration array.
	 * @param string $returnUrl The return URL parameter.
	 *
	 * @return array Returns an array of buttons.
	 * @since  5.1.0
	 */
	protected function processCustomCodeData(array $data, array $target, string $returnUrl): array
	{
		$buttons = [];
		$bucket  = [];
		$helper  = ['xml' => 'note_filter_information'];

		foreach ($data as $key => $value)
		{
			// Skip the 'id' and primary target name fields and empty field values.
			if ($key === 'id' || $key === $target['name'] || empty($value))
			{
				continue;
			}

			// Decode the value if a decoder is specified.
			$decoder = $target['decode'][$key] ?? null;
			if (!empty($decoder))
			{
				if ($decoder === 'base64')
				{
					$value = Base64Helper::open($value, null);
				}
				elseif ($decoder === 'json')
				{
					$value = json_decode($value);
				}
			}

			// Special processing for XML fields.
			if ($target['table'] === 'field' && $key === 'xml')
			{
				$this->searchFieldBase64($value);
			}

			// skip empty fields values
			if (empty($value))
			{
				continue;
			}

			// Check for custom code placeholders.
			if (strpos($value, '[CUSTOMC' . 'ODE=') !== false)
			{
				$bucket[$key] = GetHelper::allBetween($value, '[CUSTOMC' . 'ODE=', ']');
			}

			// Build a "create" button if the field contains valid content.
			if (StringHelper::check($value))
			{
				$fieldKey = $helper[$key] ?? $key;
				$buttons[$fieldKey] = [];
				$button = $this->getButton('custom_code', 3);
				if ($button && StringHelper::check($button))
				{
					$buttons[$fieldKey]['_create'] = $button;
				}
			}
		}

		// Process the bucket to add "edit" buttons.
		if (UtilitiesArrayHelper::check($bucket))
		{
			foreach ($bucket as $field => $customCodes)
			{
				$fieldKey = $helper[$field] ?? $field;
				$editIcon = '<span class="icon-edit" aria-hidden="true"></span> ';
				foreach ($customCodes as $customCode)
				{
					$parts = explode('+', $customCode);
					$functionName = $parts[0];
					if (!isset($buttons[$fieldKey][$functionName]))
					{
						$_id = GetHelper::var('custom_code', $functionName, 'function_name');
						if ($_id !== false)
						{
							$button = ComponentbuilderHelper::getEditTextButton(
								$editIcon . $functionName,
								$_id,
								'custom_code',
								'custom_codes',
								$returnUrl,
								'com_componentbuilder',
								false,
								'btn btn-small button-edit" style="margin: 0 0 5px 0;'
							);

							if ($button && StringHelper::check($button))
							{
								$buttons[$fieldKey][$functionName] = $button;
							}
						}
					}
				}
			}
		}

		return $buttons;
	}

	/**
	 * Splits a string at each uppercase letter.
	 *
	 * This function uses a regular expression to split the input string at every
	 * occurrence of an uppercase letter. The resulting array contains the parts
	 * of the original string.
	 *
	 * @param string $string The string to be split.
	 *
	 * @return array An array of substrings resulting from the split.
	 * @since  5.1.0
	 */
	protected function splitAtUpperCase(string $string): array
	{
		return preg_split('/(?=[A-Z])/', $string, -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Processes custom code placeholders and returns corresponding edit buttons.
	 *
	 * This method builds a database query based on the target configuration,
	 * retrieves the dataset, and then examines each field for custom code
	 * placeholders. Depending on the search mode, it builds a bucket of matches
	 * and finally returns an HTML list of edit buttons.
	 *
	 * @param string  $customcode The search value.
	 * @param int     $id The record ID.
	 * @param string|int  $targeting The targeting parameter for code search keys.
	 *
	 * @return array|null Returns an array with keys 'in' (HTML) and 'id' if buttons are found; false otherwise.
	 * @since  5.1.0
	 */
	public function usedin(string $customcode, int $id, string|int $targeting): ?array
	{
		// Get the target configuration using the provided targeting value.
		$target = $this->getCodeSearchKeys($targeting, 'customcode', 'query');
		if (!$target)
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_INVALID_TABLE_TARGET')];
		}

		// Build the database query based on the target configuration.
		$query = $this->buildQueryForTarget($target);
		if ($query === null)
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_NAME_FIELD_MISSING_LINK_VALUES')];
		}

		$db = Factory::getDbo();
		$db->setQuery($query);
		$db->execute();

		// If no records were found, return null.
		if (!$db->loadRowList())
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_NO_ROWS_FOUND_FOR_THIS_TARGET_AREA')];
		}

		$dataSet = $db->loadAssocList();

		// Process the dataset to extract a bucket of records with placeholders.
		$bucket = $this->extractPlaceholders($dataSet, $target, $customcode, $id, 'customcode');
		if (!UtilitiesArrayHelper::check($bucket))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_NO_SEARCH_VALUES_FOUND_IN_ROWS_FOR_THIS_TARGET_AREA')];
		}

		// Build the return URL for later use.
		$returnUrl = $this->buildReturnUrlForcustom_code($id);

		// Create edit buttons from the bucket.
		$buttons = $this->buildButtonsFromBucket($bucket, $target, $returnUrl);
		if (empty($buttons))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_WE_FAILED_TO_BUILD_THE_DETAILS_OF_THE_SEARCH_RESULTS')];
		}

		// Wrap the buttons in an HTML unordered list.
		$html = '<ul><li>' . implode('</li><li>', $buttons) . '</li></ul>';
		$view_name = $target['area_name'] ?? 'Empty';

		return ['in' => $html, 'id' => $targeting, 'area_name' => Text::_($view_name)];
	}

	/**
	 * Builds the return URL parameter for custom_code.
	 *
	 * Retrieves a base64-encoded return parameter from the input; if not found,
	 * uses a default reference URL.
	 *
	 * @param int $id The record ID.
	 *
	 * @return string Returns the constructed return URL.
	 * @since  5.1.0
	 */
	protected function buildReturnUrlForcustom_code(int $id): string
	{
		$input = Factory::getApplication()->getInput();
		$returnHere = $input->get('return_here', null, 'base64');
		if (StringHelper::check($returnHere))
		{
			return '&return=' . $returnHere;
		}
		return '&ref=custom_code&refid=' . $id;
	}

	/**
	 * Search for base64 strings and decode them
	 *
	 * @param   string  $value The string to search
	 *
	 * @return  void
	 * @since   5.1.0
	 */
	protected function searchFieldBase64(string &$value): void
	{
		// first get the start property (if dynamic)
		$starts =  [];
		// get all values
		$allBetween = GetHelper::allBetween($value, 'type_php', '="');
		// just again make sure we found some
		if (UtilitiesArrayHelper::check($allBetween))
		{
			if (count((array) $allBetween) > 1)
			{
				// search for many
				foreach ($allBetween as $between)
				{
					// load the starting property
					$start = 'type_php';
					$start .= $between;
					$start .= '="';

					$starts[] = $start;
				}
			}
			else
			{
				// load the starting property
				$start = 'type_php';
				$start .= array_values($allBetween)[0];
				$start .= '="';

				$starts[] = $start;
			}
		}
		// has any been found
		if (UtilitiesArrayHelper::check($starts))
		{
			foreach ($starts as $_start)
			{
				// get the base64 string
				$base64 = GetHelper::between($value, $_start, '"');
				// now open the base64 text
				$tmp = Base64Helper::open($base64);
				// insert it back into the value (so we still search the whole string)
				$value = str_replace($base64, $tmp, $value);
			}
		}
	}

	/**
	 * The code search keys/targets
	 *
	 * @var      array
	 * @since  3.0.9
	 */
	protected array $codeSearchKeys = ['customcode' => [], 'placeholders' => [], 'powers' => []];

	/**
	 * Get the keys of the values to search custom code in.
	 *
	 * This method supports an optional type parameter. When the type is 'query',
	 * the method assumes that the $target parameter is a single letter corresponding
	 * to the position of the table in the internal list, and it maps that letter to
	 * the actual table name. For types 'query' or 'query_', the configuration is
	 * adjusted for query usage by prefixing the search keys with "a.", adding a 'table'
	 * entry, and removing the original 'search' key.
	 *
	 * @param string|int     $target  The table targeted. When type is 'query', this can be an int.
	 * @param string          $area    The area we are searching
	 * @param string|null  $type    The type of get ('query', 'query_' or null).
	 * 
	 * @return array|null The configuration for the targeted table or null if not found.
	 * @since  3.0.9
	 */
	protected function getCodeSearchKeys(string|int $target, string $area = 'customcode', ?string $type = null): ?array
	{
		// Lazy-load the code search keys if they haven't been loaded yet.
		if (empty($this->codeSearchKeys[$area]))
		{
			$this->codeSearchKeys[$area] = (new Search())->getTextSearchSet($area);
		}

		// Return null if the configuration is still empty.
		if (empty($this->codeSearchKeys[$area]))
		{
			return null;
		}

		// If type is 'query', assume $target is a letter that maps to a table name.
		if (is_numeric($target))
		{
			$tableKeys = array_keys($this->codeSearchKeys[$area]);
			if (isset($tableKeys[$target]))
			{
				$target = $tableKeys[$target];
			}
		}

		// If the target does not exist in the configuration, return null immediately.
		if (!isset($this->codeSearchKeys[$area][$target]))
		{
			return null;
		}

		// Retrieve the configuration for the target.
		$config = $this->codeSearchKeys[$area][$target];

		// If type is 'query' or 'query_', adjust the configuration for query usage.
		if (in_array($type, ['query', 'query_'], true))
		{
			// Prefix each search key with 'a.' for the select clause.
			$config['select'] = array_map(fn(string $select): string => 'a.' . $select, $config['search']);
			$config['table'] = $target;
			unset($config['search']);
		}

		return $config;
	}

	/**
	 * Builds the database query for the given target configuration.
	 *
	 * If the target configuration defines a join (via "name_link"), it adds the
	 * necessary SELECT and JOIN clauses.
	 *
	 * @param array $target The target configuration array.
	 *
	 * @return object|null Returns the constructed database query object or false on error.
	 * @since  5.1.0
	 */
	protected function buildQueryForTarget(array &$target): ?object
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName($target['select']))
			->from($db->quoteName('#__componentbuilder_' . $target['table'], 'a'));

		if (!empty($target['name_link']))
		{
			$targetJoin  = $target['name_link']['table'] ?? null;
			$targetValue = $target['name_link']['value'] ?? null;
			$targetKey   = $target['name_link']['key'] ?? null;
			$parentKey   = $target['name'] ?? null;

			// Validate required join parameters.
			if (empty($targetJoin) || empty($targetValue) || empty($targetKey) || empty($parentKey))
			{
				return null;
			}

			// Select the proper name from the joined table.
			$query->select($db->quoteName(['c.' . $targetValue], [$targetValue]));
			// Add the LEFT JOIN clause.
			$query->join('LEFT', $db->quoteName('#__componentbuilder_' . $targetJoin, 'c') 
				. ' ON (' . $db->quoteName('a.' . $parentKey) . ' = ' . $db->quoteName('c.' . $targetKey) . ')');

			// Update the target "name" field.
			$target['name'] = $targetValue;
		}

		return $query;
	}

	/**
	 * Decodes a field value based on the target configuration.
	 *
	 * Supports 'base64' and 'json' decoders. Also processes XML fields if the table is 'field'.
	 *
	 * @param mixed  $value  The original field value.
	 * @param string $field  The field key.
	 * @param array  $target The target configuration array.
	 *
	 * @return mixed Returns the decoded field value.
	 * @since  5.1.0
	 */
	protected function decodeFieldValue($value, string $field, array $target)
	{
		$decoder = $target['decode'][$field] ?? null;
		if (!empty($decoder))
		{
			if ($decoder === 'base64')
			{
				$value = Base64Helper::open($value, null);
			}
			elseif ($decoder === 'json')
			{
				$value = json_decode($value);
			}
		}

		// Special handling for XML fields in the 'field' table.
		if ($target['table'] === 'field' && $field === 'xml')
		{
			$this->searchFieldBase64($value);
		}

		return $value;
	}

	/**
	 * Extracts a bucket of records that contain the desired custom code placeholders.
	 *
	 * For each record in the dataset, each field (except 'id' and the main name)
	 * is examined after decoding. Depending on the search mode, the method checks
	 * for the presence of a placeholder. When found, the field is added to a bucket.
	 *
	 * @param array  $dataSet       The dataset retrieved from the database.
	 * @param array  $target        The target configuration array.
	 * @param mixed  $searchValue   The search value to use when looking for placeholders.
	 * @param int    $id            The record ID.
	 * @param string $area          The search area ('customcode' or 'placeholders' or 'powers').
	 *
	 * @return array Returns a bucket array keyed by record ID with 'name' and 'fields' keys.
	 * @since  5.1.0
	 */
	protected function extractPlaceholders(array $dataSet, array $target, $searchValue, int $id, string $area): array
	{
		$bucket = [];

		foreach ($dataSet as $data)
		{
			foreach ($data as $field => $fieldValue)
			{
				// Skip the 'id' and primary name field and empty field values
				if ($field === 'id' || $field === $target['name'] || empty($fieldValue))
				{
					continue;
				}

				// Decode the field value if needed.
				$decodedValue = $this->decodeFieldValue($fieldValue, $field, $target);
				// Skip the empty decoded field values
				if (empty($decodedValue))
				{
					continue;
				}

				// Determine if the placeholder is present.
				if ($area === 'customcode')
				{
					if (
						strpos($decodedValue, '[CUSTOMCODE=' . (string) $searchValue . ']') !== false ||
						strpos($decodedValue, '[CUSTOMCODE=' . (int) $id . ']') !== false ||
						strpos($decodedValue, '[CUSTOMCODE=' . (string) $searchValue . '+') !== false ||
						strpos($decodedValue, '[CUSTOMCODE=' . (int) $id . '+') !== false
					) {
						$bucket = $this->addFieldToBucket($bucket, $data, $target, $field);
					}
				}
				elseif ($area === 'placeholders')
				{
					// Clean and wrap the search value.
					$cleanValue   = preg_replace("/[^A-Za-z0-9_]/", '', $searchValue);
					$wrappedValue = '[[[' . trim($cleanValue) . ']]]';
					if (strpos($decodedValue, (string)$wrappedValue) !== false)
					{
						$bucket = $this->addFieldToBucket($bucket, $data, $target, $field);
					}
				}
				elseif ($area === 'powers')
				{
					// soon we will add these :)
				}
			}
		}

		return $bucket;
	}

	/**
	 * Adds a field to the bucket for a given record.
	 *
	 * Initializes the bucket entry for the record if not already set, then appends the field.
	 *
	 * @param array  $bucket The current bucket array.
	 * @param array  $data   The record data.
	 * @param array  $target The target configuration array.
	 * @param string $field  The field key that contains the placeholder.
	 *
	 * @return array Returns the updated bucket array.
	 * @since  5.1.0
	 */
	protected function addFieldToBucket(array $bucket, array $data, array $target, string $field): array
	{
		$recordId = $data['id'];
		if (!isset($bucket[$recordId]))
		{
			$bucket[$recordId] = [
				'name'   => $data[$target['name']],
				'fields' => []
			];
		}
		$bucket[$recordId]['fields'][] = $field;
		return $bucket;
	}

	/**
	 * Builds edit buttons from the bucket of matched records.
	 *
	 * For each record in the bucket, an edit button is generated using an external helper.
	 * The button is appended with a list of field names where the placeholder was found.
	 *
	 * @param array  $bucket     The bucket array containing matched records.
	 * @param array  $target     The target configuration array.
	 * @param string $returnUrl  The return URL parameter.
	 *
	 * @return array Returns an array of HTML strings representing the edit buttons.
	 * @since  5.1.0
	 */
	protected function buildButtonsFromBucket(array $bucket, array $target, string $returnUrl): array
	{
		$buttons = [];
		foreach ($bucket as $editId => $values)
		{
			$button = ComponentbuilderHelper::getEditTextButton(
				$values['name'],
				$editId,
				$target['table'],
				$target['views'],
				$returnUrl,
				'com_componentbuilder',
				false,
				''
			);

			if ($button && StringHelper::check($button))
			{
				$buttons[] = $button . ' (' . implode(', ', $values['fields']) . ')';
			}
		}
		return $buttons;
	}

	// Used in placeholder
	public function checkPlaceholderName($id, $name)
	{
		return ComponentbuilderHelper::validateUniquePlaceholder($id, $name);
	}

	/**
	 * Processes custom code placeholders and returns corresponding edit buttons.
	 *
	 * This method builds a database query based on the target configuration,
	 * retrieves the dataset, and then examines each field for custom code
	 * placeholders. Depending on the search mode, it builds a bucket of matches
	 * and finally returns an HTML list of edit buttons.
	 *
	 * @param string  $placeholders The search value.
	 * @param int     $id The record ID.
	 * @param string|int  $targeting The targeting parameter for code search keys.
	 *
	 * @return array|null Returns an array with keys 'in' (HTML) and 'id' if buttons are found; false otherwise.
	 * @since  5.1.0
	 */
	public function placedin(string $placeholders, int $id, string|int $targeting): ?array
	{
		// Get the target configuration using the provided targeting value.
		$target = $this->getCodeSearchKeys($targeting, 'placeholders', 'query');
		if (!$target)
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_INVALID_TABLE_TARGET')];
		}

		// Build the database query based on the target configuration.
		$query = $this->buildQueryForTarget($target);
		if ($query === null)
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_NAME_FIELD_MISSING_LINK_VALUES')];
		}

		$db = Factory::getDbo();
		$db->setQuery($query);
		$db->execute();

		// If no records were found, return null.
		if (!$db->loadRowList())
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_NO_ROWS_FOUND_FOR_THIS_TARGET_AREA')];
		}

		$dataSet = $db->loadAssocList();

		// Process the dataset to extract a bucket of records with placeholders.
		$bucket = $this->extractPlaceholders($dataSet, $target, $placeholders, $id, 'placeholders');
		if (!UtilitiesArrayHelper::check($bucket))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_NO_SEARCH_VALUES_FOUND_IN_ROWS_FOR_THIS_TARGET_AREA')];
		}

		// Build the return URL for later use.
		$returnUrl = $this->buildReturnUrlForplaceholder($id);

		// Create edit buttons from the bucket.
		$buttons = $this->buildButtonsFromBucket($bucket, $target, $returnUrl);
		if (empty($buttons))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_WE_FAILED_TO_BUILD_THE_DETAILS_OF_THE_SEARCH_RESULTS')];
		}

		// Wrap the buttons in an HTML unordered list.
		$html = '<ul><li>' . implode('</li><li>', $buttons) . '</li></ul>';
		$view_name = $target['area_name'] ?? 'Empty';

		return ['in' => $html, 'id' => $targeting, 'area_name' => Text::_($view_name)];
	}

	/**
	 * Builds the return URL parameter for placeholder.
	 *
	 * Retrieves a base64-encoded return parameter from the input; if not found,
	 * uses a default reference URL.
	 *
	 * @param int $id The record ID.
	 *
	 * @return string Returns the constructed return URL.
	 * @since  5.1.0
	 */
	protected function buildReturnUrlForplaceholder(int $id): string
	{
		$input = Factory::getApplication()->getInput();
		$returnHere = $input->get('return_here', null, 'base64');
		if (StringHelper::check($returnHere))
		{
			return '&return=' . $returnHere;
		}
		return '&ref=placeholder&refid=' . $id;
	}

	// Used in snippet

	/**
	 * Retrieves published snippet GUIDs for valid libraries.
	 *
	 * @param   mixed  $libraries  JSON string or array of library GUIDs.
	 *
	 * @return  array|false  List of snippet IDs or false on failure.
	 * @since   5.1.1
	 */
	public function getSnippets($libraries)
	{
		// Decode JSON if required
		if (JsonHelper::check($libraries))
		{
			$libraries = json_decode($libraries, true);
		}

		// Ensure we have a valid array of libraries
		if (!UtilitiesArrayHelper::check($libraries))
		{
			return false;
		}

		// Validate and expand libraries
		$validatedLibraries = $this->expandAndValidateLibraries($libraries);

		if (!$validatedLibraries)
		{
			return false;
		}

		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName('a.guid'))
			->from($db->quoteName('#__componentbuilder_snippet', 'a'))
			->where($db->quoteName('a.published') . ' = 1')
			->where($db->quoteName('a.library') . ' IN ("' . implode('","', $validatedLibraries) . '")');

		$db->setQuery($query);
		$db->execute();

		return $db->getNumRows() ? $db->loadColumn() : false;
	}

	/**
	 * Validates and expands library GUIDs to ensure only integers and valid references remain.
	 *
	 * @param   array  $libraries  The original list of library GUIDs.
	 *
	 * @return  array|false  Sanitized and validated list of libraries, or false.
	 * @since   5.1.1
	 */
	protected function expandAndValidateLibraries(array $libraries)
	{
		$expanded = [];

		foreach ($libraries as $guid)
		{
			$guid = (string) $guid;
			$type = GetHelper::var('library', $guid, 'guid', 'type');

			if ((int) $type === 2)
			{
				$bundled = GetHelper::var('library', $guid, 'guid', 'libraries');

				if (JsonHelper::check($bundled))
				{
					$bundled = json_decode($bundled, true);
				}

				if (UtilitiesArrayHelper::check($bundled))
				{
					foreach ($bundled as $lib)
					{
						$expanded[$lib] = $lib;
					}
				}
				elseif (is_numeric($bundled))
				{
					$expanded[$bundled] = $bundled;
				}
			}
			else
			{
				$expanded[$guid] = $guid;
			}
		}

		// Remove invalid entries and duplicates
		$valid = array_filter(array_unique($expanded), function ($guid) {
			return GuidHelper::valid($guid);
		});

		return UtilitiesArrayHelper::check($valid) ? array_values($valid) : false;
	}

	/**
	 * Retrieves snippet details by GUID or ID.
	 *
	 * @param   string|int  $key  The snippet GUID (string) or ID (int).
	 *
	 * @return  object|false  The snippet data object or false on failure.
	 * @since   5.1.1
	 */
	public function getSnippetDetails($key)
	{
		$target = $this->resolveSnippetKeyField($key);

		if ($target === false)
		{
			return false;
		}

		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName(
				[
					'a.name',
					'a.heading',
					'a.usage',
					'a.description',
					'b.name',
					'a.snippet',
					'a.url',
					'c.name'
				],
				[
					'name',
					'heading',
					'usage',
					'description',
					'type',
					'snippet',
					'url',
					'library'
				]
			))
			->from($db->quoteName('#__componentbuilder_snippet', 'a'))
			->join('LEFT', $db->quoteName('#__componentbuilder_snippet_type', 'b') . ' ON ' . $db->quoteName('a.type') . ' = ' . $db->quoteName('b.guid'))
			->join('LEFT', $db->quoteName('#__componentbuilder_library', 'c') . ' ON ' . $db->quoteName('a.library') . ' = ' . $db->quoteName('c.guid'))
			->where($db->quoteName('a.published') . ' >= 1')
			->where($db->quoteName("a.$target") . ' = ' . $db->quote($key));

		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows() > 0)
		{
			$snippet = $db->loadObject();

			if (isset($snippet->snippet))
			{
				$snippet->snippet = base64_decode($snippet->snippet);
			}

			return $snippet;
		}

		return false;
	}

	/**
	 * Resolves whether the given key is a GUID or numeric ID and returns the appropriate field.
	 *
	 * @param   mixed  $key  The value used to identify the snippet.
	 *
	 * @return  string|false  'guid', 'id', or false if invalid.
	 * @since   5.1.1
	 */
	protected function resolveSnippetKeyField($key)
	{
		if (GuidHelper::valid($key))
		{
			return 'guid';
		}

		if (is_numeric($key))
		{
			return 'id';
		}

		return false;
	}

	// Used in validation_rule
	/**
	 * Get the source code body of an existing Joomla validation rule.
	 *
	 * This method loads the PHP source file of a known Joomla validation rule
	 * and extracts the code inside the rule class body.
	 *
	 * The original production logic is preserved by first attempting the known
	 * and working Joomla class split pattern:
	 * FormRule followed by a new line and opening brace.
	 *
	 * A few lightweight fallback patterns are then checked to improve tolerance
	 * for formatting differences without adding unnecessary complexity.
	 *
	 * The return structure is preserved for backward compatibility:
	 *
	 * [
	 *     'values' => '...class body code...'
	 * ]
	 *
	 * @param   string  $name  The validation rule name.
	 *
	 * @return  array|bool  The extracted class body on success, or false on failure.
	 * @since   6.1.6
	 */
	public function getExistingValidationRuleCode($name)
	{
		// Make sure we have all the existing rule names.
		if (!($names = ComponentbuilderHelper::getExistingValidationRuleNames()))
		{
			return false;
		}

		// Check that this is a valid rule file.
		if (
			!UtilitiesArrayHelper::check($names)
			|| !in_array($name, $names, true)
		)
		{
			return false;
		}

		// Get the full path to the rule file.
		$path = JPATH_LIBRARIES . '/src/Form/Rule/' . $name . 'Rule.php';

		if (!is_file($path) || !is_readable($path))
		{
			return false;
		}

		// Get all the code.
		if (!($code = FileHelper::getContent($path)))
		{
			return false;
		}

		// Preserve the original working production pattern first.
		$codeArray = explode("FormRule\n{\n", $code, 2);

		// Add a few lightweight fallbacks for formatting differences.
		if (!isset($codeArray[1]))
		{
			$codeArray = explode("FormRule\r\n{\r\n", $code, 2);
		}
		if (!isset($codeArray[1]))
		{
			$codeArray = explode("FormRule\r\n{\n", $code, 2);
		}
		if (!isset($codeArray[1]))
		{
			$codeArray = explode("FormRule\n{\r\n", $code, 2);
		}
		if (!isset($codeArray[1]))
		{
			$codeArray = explode("FormRule\n{", $code, 2);
		}
		if (!isset($codeArray[1]))
		{
			$codeArray = explode("FormRule\r\n{", $code, 2);
		}
		if (!isset($codeArray[1]))
		{
			$codeArray = explode("FormRule {", $code, 2);
		}
		if (!isset($codeArray[1]))
		{
			$codeArray = explode("FormRule\t{", $code, 2);
		}

		if (!isset($codeArray[1]))
		{
			return false;
		}

		return ['values' => rtrim(rtrim(rtrim($codeArray[1]), '}'))];
	}

	/**
	 * Check whether a validation rule name is available.
	 *
	 * This method sanitizes the given rule name, checks whether it already
	 * exists in the local custom validation rules table, and then checks
	 * whether it already exists as part of the Joomla core validation rules.
	 *
	 * If the name is already used by another local rule or by Joomla core,
	 * a danger response is returned. Otherwise, a success response is returned
	 * together with the sanitized name.
	 *
	 * @param   string     $name  The proposed validation rule name.
	 * @param   int|string $id    The current validation rule ID.
	 *
	 * @return  array  The validation response array.
	 * @since   6.1.6
	 */
	public function checkRuleName($name, $id)
	{
		$name = StringHelper::safe($name);
		$id = (int) $id;

		// Check whether this rule name already exists in the custom rules table.
		$found = GetHelper::var(
			'validation_rule',
			$name,
			'name',
			'id'
		);

		if ($found && $id !== (int) $found)
		{
			return [
				'message' => Text::sprintf('COM_COMPONENTBUILDER_SORRY_THIS_VALIDATION_RULE_NAME_S_ALREADY_EXISTS_IN_YOUR_SYSTEM',
					$name
				),
				'status' => 'danger',
				'timeout' => 6000,
			];
		}

		// Check whether this rule name already exists in Joomla core.
		$names = ComponentbuilderHelper::getExistingValidationRuleNames(true);

		if ($names && in_array($name, $names, true))
		{
			return [
				'message' => Text::sprintf('COM_COMPONENTBUILDER_SORRY_THIS_VALIDATION_RULE_NAME_S_ALREADY_EXISTS_AS_PART_OF_THE_JOOMLA_CORE_THERE_IS_NO_NEED_TO_CREATE_IT_IF_YOU_ARE_ADAPTING_IT_GIVE_IT_YOUR_OWN_UNIQUE_NAME',
					$name
				),
				'status' => 'danger',
				'timeout' => 10000,
			];
		}

		return [
			'name' => $name,
			'message' => Text::sprintf('COM_COMPONENTBUILDER_GREAT_THIS_VALIDATION_RULE_NAME_S_WILL_WORK',
				$name
			),
			'status' => 'success',
			'timeout' => 5000,
		];
	}

	/**
	 * Get the validation rules table markup.
	 *
	 * This method builds an HTML table showing all available validation rules
	 * for the form field validate attribute.
	 *
	 * The table is rendered using Joomla 6 compatible Bootstrap 5 classes and
	 * no longer depends on UIkit.
	 *
	 * @param   string|int  $id  The field or context identifier.
	 *
	 * @return  string|bool  The HTML table markup on success, or false if no
	 *                       validation rules could be loaded.
	 * @since   6.1.6
	 */
	public function getValidationRulesTable($id)
	{
		// Keep the parameter for backward compatibility.
		unset($id);

		// Get all validation rules.
		$rules = $this->getValidationRules();

		if (!$rules || !is_array($rules))
		{
			return false;
		}

		$table = [];
		$table[] = '<div class="table-responsive">';
		$table[] = '<table class="table table-striped table-hover table-sm caption-top">';
		$table[] = '<caption>' . Text::_('COM_COMPONENTBUILDER_THE_AVAILABLE_VALIDATION_RULES_FOR_THE_VALIDATE_ATTRIBUTE_ARE') . '</caption>';
		$table[] = '<thead>';
		$table[] = '<tr>';
		$table[] = '<th scope="col" class="text-end">' . Text::_('COM_COMPONENTBUILDER_VALIDATE') . '</th>';
		$table[] = '<th scope="col">' . Text::_('COM_COMPONENTBUILDER_DESCRIPTION') . '</th>';
		$table[] = '</tr>';
		$table[] = '</thead>';
		$table[] = '<tbody>';

		foreach ($rules as $name => $description)
		{
			$description = (StringHelper::check($description)
				&& !is_numeric($description))
				? $description
				: '';

			$table[] = '<tr>';
			$table[] = '<td class="text-end"><code>' . $name . '</code></td>';
			$table[] = '<td>' . $description . '</td>';
			$table[] = '</tr>';
		}

		$table[] = '</tbody>';
		$table[] = '</table>';
		$table[] = '</div>';

		return implode('', $table);
	}

	/**
	 * Get all available validation rules.
	 *
	 * Ensures all rules have readable descriptions, including dynamically
	 * discovered Joomla and custom validation rules.
	 *
	 * @return array|bool
	 * @since  6.1.6
	 */
	public function getValidationRules()
	{
		$existingNames = ComponentbuilderHelper::getExistingValidationRuleNames(true);

		if (!$existingNames)
		{
			return false;
		}

		// Initialize all rules with a safe default description
		$existingRules = [];

		foreach ($existingNames as $name)
		{
			$existingRules[$name] = Text::sprintf('COM_COMPONENTBUILDER_VALIDATION_RULE_S',
				$name
			);
		}

		// Override known Joomla rule descriptions
		$existingRules['boolean'] = Text::_("COM_COMPONENTBUILDER_ACCEPTS_ONLY_THE_VALUES_ZERO_ONE_TRUE_OR_FALSE_CASEINSENSITIVE");
		$existingRules['color'] = Text::_("COM_COMPONENTBUILDER_ACCEPTS_RGB_OR_RRGGBB_EMPTY_BECOMES_ZERO");
		$existingRules['email'] = Text::_("COM_COMPONENTBUILDER_VALID_EMAIL_ADDRESS_IN_THE_FORMAT_XYZZ");
		$existingRules['equals'] = Text::sprintf("COM_COMPONENTBUILDER_VALUE_MUST_MATCH_ANOTHER_FIELD_SEE_FIELD_ATTRIBUTE_EGS",
			'<br /><code>&lt;input name="email_check" validate="equals" field="email" /&gt;</code>'
		);
		$existingRules['options'] = Text::_("COM_COMPONENTBUILDER_VALUE_MUST_EXIST_IN_A_LIST_FIELD");
		$existingRules['tel'] = Text::_("COM_COMPONENTBUILDER_VALID_TELEPHONE_NUMBER_EONE_HUNDRED_AND_SIXTY_FOUR_NANPA_RFCTHREE_THOUSAND_NINE_HUNDRED_AND_SIXTY_SIX");
		$existingRules['url'] = Text::sprintf("COM_COMPONENTBUILDER_VALID_URL_WITH_OPTIONAL_SCHEME_RESTRICTION_EGS",
			'<br /><code>&lt;input name="link" validate="url" scheme="http,https" /&gt;</code>'
		);
		$existingRules['username'] = Text::_("COM_COMPONENTBUILDER_MUST_NOT_ALREADY_EXIST_AS_A_USERNAME");

		// Load custom rules
		$customRules = [];

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(['a.name', 'a.short_description']))
			->from($db->quoteName('#__componentbuilder_validation_rule', 'a'))
			->where($db->quoteName('a.published') . ' >= 1');

		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			$customRules = $db->loadAssocList('name', 'short_description');
		}

		$rules = UtilitiesArrayHelper::merge(
			[$existingRules, $customRules]
		);

		ksort($rules);

		return $rules;
	}

	// Used in field

	/**
	 * The current extras available
	 *
	 * @var   array
	 * @since 3.0.0
	 */
	protected array $extraFieldProperties = [
			'listclass' => 'COM_COMPONENTBUILDER_SET_A_CLASS_VALUE_FOR_THE_LIST_VIEW_OF_THIS_FIELD',
			'escape' => 'COM_COMPONENTBUILDER_SHOULD_THIS_FIELD_BE_ESCAPED_IN_THE_LIST_VIEW',
			'display' => 'COM_COMPONENTBUILDER_DISPLAY_SWITCH_FOR_DYNAMIC_PLACEMENT_IN_RELATION_TO_THE_USE_OF_THE_FIELD_IN_MENU_AND_GLOBAL_CONFIGURATION_OPTIONS_SO_THE_CONFIG_OPTION_WILL_ONLY_ADD_THE_FIELD_TO_THE_GLOBAL_CONFIGURATION_AREA_MENU_WILL_ADD_THE_FIELD_ONLY_TO_THE_MENU_AREA',
			'validate' => 'COM_COMPONENTBUILDER_TO_ADD_VALIDATION_TO_A_FIELD_IF_VALIDATION_IS_NOT_PART_OF_FIELD_TYPE_PROPERTIES_LOADED_ABOVE_SO_IF_YOU_HAVE_VALIDATION_SET_AS_A_FIELD_PROPERTY_THIS_EXTRA_PROPERTY_WILL_NOT_BE_NEEDED'
	];

	/**
	 * Retrieves and constructs the properties for a given field type.
	 *
	 * This method generates a set of field properties, including subforms and extra properties,
	 * based on the specified field type. It also handles additional PHP-based properties by
	 * constructing textarea fields for multi-line input. The resulting field configuration is 
	 * returned as an associative array, which includes HTML-rendered fields for integration into 
	 * the Joomla form interface.
	 *
	 * @param mixed $fieldtype The type of the field to retrieve properties for. This can be an 
	 *                         identifier such as a GUID, numeric ID, or a string.
	 *
	 * @return array|null An associative array containing the following keys if successful:
	 *                     - `subform`: HTML-rendered subform for field properties.
	 *                     - `extra`: HTML-rendered subform for extra field properties.
	 *                     - `textarea`: An array of HTML-rendered textarea fields for PHP-based properties (if any).
	 *                     Returns `false` if the field type is invalid or no properties are found.
	 * @since 3.0.0
	 */
	public function getFieldTypeProperties($fieldtype): ?array
	{
		// get the fieldtype key
		if (GuidHelper::valid($fieldtype))
		{
			$key = 'guid';
		}
		elseif (is_numeric($fieldtype))
		{
			$key = 'id';
		}
		else
		{
			return null;
		}

		// get the xml
		$xml = $this->getFieldXML($fieldtype);

		// now get the field options
		if ($field = ComponentbuilderHelper::getFieldTypeProperties($fieldtype, $key, null, $xml, true))
		{
			// get subform field properties object
			$properties = $this->buildFieldOptionsSubform($field['subform'], $field['nameListOptions']);

			// load the extra options
			$extraValues = $this->getFieldExtraValues($xml, $field['nameListOptions']);

			// set the nameListOption
			$extraNameListOption = $this->extraFieldProperties;
			array_walk($extraNameListOption, function (&$value, $key) {
				$value = $key;
			});

			// get subform field object
			$extras = $this->buildFieldOptionsSubform(
				$extraValues,
				$extraNameListOption,
				'extraproperties',
				'COM_COMPONENTBUILDER_EXTRA_PROPERTIES_LIKE_LISTCLASS_ESCAPE_DISPLAY_VALIDATEBR_SMALLHERE_YOU_CAN_SET_THE_EXTRA_PROPERTIES_FOR_THIS_FIELDSMALL'
			);

			// load the html 
			$field['subform'] = '<div class="control-label prop_removal">'. $properties->label . '</div><div class="controls prop_removal">' . $properties->input . '</div>';
			$field['extra'] = '<div class="control-label prop_removal">'. $extras->label . '</div><div class="controls prop_removal">' . $extras->input . '</div>';

			// check if we have PHP values
			if (UtilitiesArrayHelper::check($field['php']))
			{
				$field['textarea'] = [];
				foreach($field['php'] as $name => $values)
				{
					$value = implode(PHP_EOL, $values['value']);
					$textarea = $this->buildFieldTextarea($name, $values['desc'], $value, substr_count( $value, PHP_EOL ));
					// load the html 
					$field['textarea'][] = '<div class="control-label prop_removal">'. $textarea->label . '</div><div class="controls prop_removal">' . $textarea->input . '</div><br />';
				}
			}

			// remove some unneeded values
			unset($field['values']);

			// return found field options
			return $field;
		}

		return null;
	}

	/**
	 * Retrieves extra field properties and their values.
	 *
	 * This method identifies and extracts additional properties from a field's XML definition
	 * that are not included in the provided options array. It verifies the validity of each
	 * property's value and returns an associative array of extra properties with their names, 
	 * values, and descriptions.
	 *
	 * @param \SimpleXMLElement $xml The XML definition of the field, used to extract property values.
	 * @param array $options An array of property names to exclude from the extraction process.
	 *
	 * @return array|null An associative array of extra properties, each containing:
	 *                    - `name`: The name of the property.
	 *                    - `value`: The value of the property if it is valid.
	 *                    - `desc`: The translated description of the property.
	 *                    Returns `null` if no extra properties are found.
	 * @since 3.0.0
	 */
	protected function getFieldExtraValues($xml, $options): ?array
	{
		if (empty($this->extraFieldProperties))
		{
			return null;
		}

		// get the value
		$values = [];

		// value to check since there are false and null values even 0 in the values returned
		$confirmation = '8qvZHoyuFYQqpj0YQbc6F3o5DhBlmS-_-a8pmCZfOVSfANjkmV5LG8pCdAY2JNYu6cB';
		$nr = 0;
		foreach ($this->extraFieldProperties as $extra => $desc)
		{
			if (!in_array($extra, $options))
			{
				$value =  FieldHelper::getValue($xml, $extra, $confirmation);
				if ($confirmation !== $value)
				{
					$values['extraproperties' . $nr] = ['name' => $extra, 'value' => $value, 'desc' => Text::_($desc)];
					$nr++;
				}
			}
		}

		// return only if extras found
		if (UtilitiesArrayHelper::check($values))
		{
			return $values;
		}

		return null;
	}

	/**
	 * Builds a textarea field for a Joomla form.
	 *
	 * This method dynamically generates a textarea field with configurable attributes 
	 * such as name, description, default value, and number of rows. The textarea is 
	 * used to capture multi-line input, such as scripts or extended text values.
	 *
	 * @param string $name The name identifier for the textarea field, appended to the 
	 *                     'property_' prefix to create a unique field name.
	 * @param string $desc The label or description for the textarea field, displayed 
	 *                     in the form UI.
	 * @param mixed $default The default value to populate the textarea field.
	 * @param int $rows The number of rows for the textarea. If the provided value is 
	 *                  less than 3, 2 additional rows are added to ensure readability.
	 *
	 * @return object A Joomla textarea field object configured with the specified attributes and default value.
	 *
	 * @throws \Exception If any errors occur while generating the field XML or setting up the field.
	 * @since 3.0.0
	 */
	protected function buildFieldTextarea($name, $desc, $default, $rows): object
	{
		// get the textarea
		$textarea = FormFormHelper::loadFieldType('textarea', true);
		// start building the name field XML
		$textareaXML = new \SimpleXMLElement('<field/>');
		// textarea attributes
		$textareaAttribute = [
			'type' => 'textarea',
			'name' => 'property_' . $name,
			'label' => $desc,
			'rows' => (int) ($rows >= 3) ? $rows : $rows + 2,
			'cols' => '15',
			'class' => 'text_area  span12',
			'filter' => 'RAW',
			'hint' => 'COM_COMPONENTBUILDER__ADD_YOUR_PHP_SCRIPT_HERE'
		];
		// load the textarea attributes
		FormHelper::attributes($textareaXML, $textareaAttribute);

		// setup subform with values
		$textarea->setup($textareaXML, $default);

		// return textarea object
		return $textarea;
	}

	/**
	 * Builds a subform field for configuring field options.
	 *
	 * This method dynamically generates a Joomla subform field with multiple attributes,
	 * child fields, and configurations. The generated subform field includes properties for 
	 * name, value, and description, and supports dynamic field attributes and options based 
	 * on the provided input values and configurations.
	 *
	 * @param array $values An array of initial values to populate the subform fields.
	 *                      Each value corresponds to a row in the subform.
	 * @param array|null $nameListOptions An optional associative array of options for the 
	 *                                    "name" field. If provided, the field will render 
	 *                                    as a dropdown list with these options. If `null`, 
	 *                                    the field will render as a text input.
	 * @param string $name The name attribute for the subform field. Default is 'properties'.
	 * @param string $label The label for the subform field. By default, it includes a 
	 *                      description of the subform purpose.
	 *
	 * @return object A Joomla subform field object configured with the specified 
	 *                attributes, child fields, and values.
	 * @since 3.0.0
	 */
	protected function buildFieldOptionsSubform($values, $nameListOptions = null, $name = 'properties',
		$label = 'COM_COMPONENTBUILDER_PROPERTIESBR_SMALLHERE_YOU_CAN_SET_THE_PROPERTIES_FOR_THIS_FIELDSMALL'): object
	{
		// get the subform
		$subform = FormFormHelper::loadFieldType('subform', true);
		// start building the subform field XML
		$subformXML = new \SimpleXMLElement('<field/>');
		// subform attributes
		$subformAttribute = [
			'type' => 'subform',
			'name' => $name,
			'label' => $label,
			'layout' => 'joomla.form.field.subform.repeatable-table',
			'multiple' => 'true',
			'icon' => 'list',
			'max' =>  (UtilitiesArrayHelper::check($nameListOptions)) ? (int) count($nameListOptions) : 4
		];
		// load the subform attributes
		FormHelper::attributes($subformXML, $subformAttribute);
		// now add the subform child form
		$childForm = $subformXML->addChild('form');
		// child form attributes
		$childFormAttribute = [
			'hidden' => 'true',
			'name' => 'list_properties',
			'repeat' => 'true'
		];
		// load the child form attributes
		FormHelper::attributes($childForm, $childFormAttribute);

		// start building the name field XML
		$nameXML = new \SimpleXMLElement('<field/>');
		$isList = UtilitiesArrayHelper::check($nameListOptions);
		// subform attributes
		$nameAttribute = [
			'type' => $isList ? 'list' : 'text',
			'name' => 'name',
			'label' => 'COM_COMPONENTBUILDER_PROPERTY',
			'class' => $isList ? 'list_class' : 'text_area',
			'filter' => 'STRING'
		];
		// add the hint only if not name list and description if name list is an array
		if ($isList)
		{
			$nameAttribute['description'] = 'COM_COMPONENTBUILDER_SELECTION';
			$nameAttribute['multiple'] = 'false';
			$nameAttribute['onchange'] = "getFieldPropertyDesc(this, '".$name."')";
		}
		else
		{
			$nameAttribute['hint'] = 'COM_COMPONENTBUILDER_PROPERTY_NAME';
			$nameAttribute['size'] = '40';
			$nameAttribute['maxlength'] = '150';
		}
		// load the subform attributes
		FormHelper::attributes($nameXML, $nameAttribute);
		// add name list if found
		if ($isList)
		{
			$_nameListoptions = array_merge($nameListOptions, ['' => Text::_('COM_COMPONENTBUILDER_SELECT_A_PROPERTY')]);
			FormHelper::options($nameXML, $_nameListoptions);
		}
		// now add the fields to the child form
		FormHelper::append($childForm, $nameXML);

		// start building the name field XML
		$valueXML = new \SimpleXMLElement('<field/>');
		// subform attributes
		$valueAttribute = [
			'type' => 'textarea',
			'name' => 'value',
			'label' => 'COM_COMPONENTBUILDER_VALUE',
			'rows' => '1',
			'cols' => '15',
			'class' => 'text_area full-column-in-subform',
			'filter' => 'STRING',
			'hint' => 'COM_COMPONENTBUILDER_PROPERTY_VALUE'
		];
		// load the subform attributes
		FormHelper::attributes($valueXML, $valueAttribute);
		// now add the fields to the child form
		FormHelper::append($childForm, $valueXML);

		// start building the desc field XML
		$descXML = new \SimpleXMLElement('<field/>');
		// subform attributes
		$descAttribute = [
			'type' => 'textarea',
			'name' => 'desc',
			'label' => 'COM_COMPONENTBUILDER_DESCRIPTION',
			'rows' => '3',
			'cols' => '25',
			'readonly' => 'true',
			'class' => 'text_area full-column-in-subform',
			'filter' => 'WORD',
			'hint' => 'COM_COMPONENTBUILDER_SELECT_A_PROPERTY'
		];
		// load the desc attributes
		FormHelper::attributes($descXML, $descAttribute);
		// now add the fields to the child form
		FormHelper::append($childForm, $descXML);

		// setup subform with values
		$subform->setup($subformXML, $values);

		// return subfrom object
		return $subform;
	}

	/**
	 * Retrieves the field property description based on the given property name and field type.
	 *
	 * This function performs a series of validations and database lookups to fetch
	 * the description and associated value of a specified field property. If the
	 * property is not found or valid, it returns `false`.
	 *
	 * @param string $_property The name of the field property to retrieve.
	 * @param mixed $fieldtype The type of the field, which can be a GUID, numeric ID, or other identifier.
	 *
	 * @return array|false Returns an associative array with keys `value` and `desc` if the property is found, 
	 *                     or `false` if no matching property is found.
	 * @since 3.0.0
	 */
	public function getFieldPropertyDesc(string $_property, $fieldtype)
	{
		if (GuidHelper::valid($fieldtype))
		{
			$key = 'guid';
		}
		elseif (is_numeric($fieldtype))
		{
			$key = 'id';
		}
		elseif (isset($this->extraFieldProperties[$_property]))
		{
			return ['value' => '', 'desc' => Text::_($this->extraFieldProperties[$_property])];
		}
		else
		{
			return false;
		}

		// Get a db connection.  
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(['properties', 'short_description', 'description']));
		$query->from($db->quoteName('#__componentbuilder_fieldtype'));
		$query->where($db->quoteName($key) . ' = '. $db->quote($fieldtype));

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			// get the result
			$result = $db->loadObject();
			// get the xml
			$xml = $this->getFieldXML($fieldtype);
			// open the properties
			$properties = json_decode($result->properties,true);
			// value to check since there are false and null values even 0 in the values returned
			$confirmation = '8qvZHoyuFYQqpj0YQbc6F3o5DhBlmS-_-a8pmCZfOVSfANjkmV5LG8pCdAY2JNYu6cB';

			// make sure we have an array
			if (!UtilitiesArrayHelper::check($properties))
			{
				return false;
			}

			foreach ($properties as $property)
			{
				if(isset($property['name']) && $_property === $property['name'])
				{
					// check if we should load the value
					$value = FieldHelper::getValue($xml, $property['name'], $confirmation);
					if ($confirmation === $value)
					{
						$value = (isset($property['example']) && StringHelper::check($property['example'])) ? $property['example'] : '';
					}
					// return the found values
					return ['value' => $value, 'desc' => $property['description']];
				}
			}
		}
		return false;
	}

	/**
	 * Retrieves the XML configuration for a given field type.
	 *
	 * This method fetches the XML configuration associated with a specific field type.
	 * If the current view is a "field" view and the view ID is valid, it attempts to 
	 * retrieve the XML configuration from the database. It also ensures that the XML 
	 * configuration matches the specified field type, applying transformations if 
	 * necessary to adapt the XML to the new field type.
	 *
	 * @param mixed $fieldtype The type of the field for which XML configuration is requested.
	 *                         This can be a numeric ID, GUID, or other identifier.
	 *
	 * @return string|null The XML configuration as a string if found and processed, 
	 *                     or `null` if no valid XML configuration is available.
	 * @since 3.0.0
	 */
	protected function getFieldXML($fieldtype): ?string
	{
		// reset xml to null
		$xml = null;

		// get the view name & id
		$global = $this->getViewID();

		// get the xml if this view already has it set
		if (!is_null($global['a_id']) && $global['a_id'] > 0 && isset($global['a_view']) && 'field' === $global['a_view'])
		{
			// first check field type
			$_fieldType = GetHelper::var('field', $global['a_id'], 'id', 'fieldtype');
			$xmlDB = GetHelper::var('field', $global['a_id'], 'id', 'xml');

			// check if it is a string
			if (StringHelper::check($xmlDB))
			{
				$xml = json_decode($xmlDB);
			}

			// remove the field type if not the same
			if ($xml && $fieldtype != $_fieldType)
			{
				// unset some stuff
				$pattern = [];
				$pattern[] = '/type=".+?"/i'; // to force the new type to be set
				$pattern[] = '/class=".+?"/i'; // to remove all classes
				$pattern[] = '/type_php.+?".+?"/i'; // to remove any PHP code stuff

				// also add a special switch to force adding all properties of the new type
				$xml = preg_replace($pattern, '..__FORCE_LOAD_ALL_PROPERTIES__..', $xml);
			}
		}
		return $xml;
	}

	// Used in language_translation

	/**
	 * Export language translation data by filtering records based on extension, translated, and untranslated tags.
	 *
	 * This method loads translation records from the database and structures them into an array
	 * with language-tagged headers (e.g., `en-GB`, `de-DE`). It supports filtering for a specific
	 * extension, already translated languages, and missing translations. All matching records are
	 * padded with empty values for missing languages, and returned with a size count or errors.
	 *
	 * @param   string  $extension      The extension identifier in format "type__name" (e.g., "com_example__field").
	 * @param   string  $translated     Comma-separated list of language tags that must have translations.
	 * @param   string  $notTranslated  Comma-separated list of language tags that must not yet have translations.
	 *
	 * @return  array<string, mixed>  Returns an array with:
	 *                                - 'data' (array<int, array<string, string>>): The exportable translation rows.
	 *                                - 'size' (int): Number of rows (if successful).
	 *                                - 'errors' (string): Error message (if an error occurred).
	 *
	 * @throws  \Throwable  If any unexpected exception occurs during data fetching or parsing.
	 * @since   5.1.1
	 */
	public function exportLanguageTranslations(string $extension, string $translated, string $notTranslated): array
	{
		try {
			$ids = $this->resolveLanguageTranslationFilterIds($extension, $translated, $notTranslated);
			$where = $this->buildLanguageTranslationWhereClause($ids);
			$records = $this->loadLanguageTranslationRows($where);
			$headers = $this->getLanguageTranslationHeaders();

			if (empty($records))
			{
				return $this->errorLanguageTranslationResponse(Text::_('COM_COMPONENTBUILDER_NO_LANGUAGE_STRINGS_FOUND'));
			}

			$data = $this->normalizeLanguageTranslationData($records, $headers);

			return [
				'data' => $data,
				'size' => count($data),
			];
		} catch (\Throwable $e) {
			return $this->errorLanguageTranslationResponse($e->getMessage());
		}
	}

	/**
	 * Resolve all relevant record IDs from the given filters.
	 *
	 * @param   string  $extension      Extension string in format "type__name".
	 * @param   string  $translated     Comma-separated list of translated language tags.
	 * @param   string  $notTranslated  Comma-separated list of untranslated language tags.
	 *
	 * @return  array<int>|null  Array of record IDs, or empty array to load all or null to force a skip all.
	 * @since   5.1.1
	 */
	protected function resolveLanguageTranslationFilterIds(string $extension, string $translated, string $notTranslated): ?array
	{
		$ids = [];
		$forceEmpty = false;

		// Extension IDs
		if (!empty($extension) && strpos($extension, '__') !== false)
		{
			[$type, $name] = explode('__', $extension, 2);
			$extIds = FilterHelper::translation($name, $type);
			if (!empty($extIds))
			{
				$ids = array_merge($ids, $extIds);
			}
			else
			{
				$forceEmpty = true;
			}
		}

		// Translated IDs
		if (!empty($translated))
		{
			$trIds = FilterHelper::translations($translated);
			if (!empty($trIds))
			{
				$ids = array_merge($ids, $trIds);
			}
			else
			{
				$forceEmpty = true;
			}
		}

		// Not translated IDs
		if (!empty($notTranslated))
		{
			$untrIds = FilterHelper::translations($notTranslated, false);
			if (!empty($untrIds))
			{
				$ids = array_merge($ids, $untrIds);
			}
			else
			{
				$forceEmpty = true;
			}
		}

		if ($ids === [] && !$forceEmpty)
		{
			return [];
		}

		return $forceEmpty ? null : array_unique($ids);
	}

	/**
	 * Build a SQL WHERE clause using resolved IDs.
	 *
	 * @param   array<int>|null  $ids  The record IDs to include in the query.
	 *
	 * @return  array<string, array<string, mixed>>  A structured WHERE clause.
	 * @since   5.1.1
	 */
	protected function buildLanguageTranslationWhereClause(?array $ids): array
	{
		if ($ids === [])
		{
			// return all published
			return ['published' => ['value' => 1, 'operator' => '=', 'quote' => false]];
		}
		elseif ($ids === null)
		{
			// return none
			return ['id' => ['value' => 0, 'operator' => '=', 'quote' => false]];
		}

		// return selected and published
		return [
			'id' => ['value' => $ids, 'operator' => 'IN', 'quote' => false],
			'published' => ['value' => 1, 'operator' => '=', 'quote' => false]
		];
	}

	/**
	 * Load translation rows from the database based on the given WHERE clause.
	 *
	 * @param   array<string, array<string, mixed>>|null  $where  Optional WHERE clause.
	 *
	 * @return  array<int, array<string, mixed>>  Loaded records with 'source' and 'translation' keys.
	 * @since   5.1.1
	 */
	protected function loadLanguageTranslationRows(?array $where): array
	{
		return DataFactory::_('Load')->rows(
			['source', 'translation'],
			['language_translation'],
			$where,
			['source' => 'ASC']
		);
	}

	/**
	 * Get the list of available language headers (e.g., ['en-GB' => 'en-GB']).
	 *
	 * This includes a default 'source' => 'source' entry.
	 *
	 * @return  array<string, string>  Associative list of language tags.
	 * @since   5.1.1
	 */
	protected function getLanguageTranslationHeaders(): array
	{
		return ComponentbuilderHelper::getLanguageTranslationsHeaders() ?? ['source' => 'source'];
	}

	/**
	 * Normalize translation records by mapping language keys and padding missing headers.
	 *
	 * @param   array<int, array<string, mixed>>  $rows     Raw translation rows from the database.
	 * @param   array<string, string>             $headers  Valid language header list.
	 *
	 * @return  array<int, array<string, string>>  Structured translation data ready for export.
	 * @since   5.1.1
	 */
	protected function normalizeLanguageTranslationData(array $rows, array $headers): array
	{
		$normalized = [];

		foreach ($rows as $row)
		{
			$translations = json_decode($row['translation'] ?? '[]', true) ?: [];
			unset($row['translation']);

			// Pad all expected language headers
			foreach ($headers as $lang => $_)
			{
				if ($lang === 'source')
				{
					continue;
				}
				$row[$lang] = '';
			}

			foreach ($translations as $entry)
			{
				$lang = $entry['language'] ?? '';
				$text = trim(($entry['translation'] ?? ''));

				if (isset($headers[$lang]) && trim($text) !== '')
				{
					$row[$lang] = $text;
				}
			}

			$normalized[] = $row;
		}

		return $normalized;
	}

	/**
	 * Build a standardized error response with message.
	 *
	 * @param   string  $message  Error message to return.
	 *
	 * @return  array<string, mixed>  Error response with 'data' as empty array and 'errors' as message.
	 * @since   5.1.1
	 */
	protected function errorLanguageTranslationResponse(string $message): array
	{
		return [
			'data' => [],
			'errors' => $message,
		];
	}

	// Used in admin_fields_relations
	/**
	 * Get the code glue options string.
	 *
	 * @param   string  $listfield   The main list field GUID.
	 * @param   string  $joinfields  A comma-separated list of join field GUIDs or 'none'.
	 * @param   int     $type        The glue type.
	 * @param   int     $area        The target area.
	 *
	 * @return  string|false
	 * @since   5.3.10
	 */
	public function getCodeGlueOptions(string $listfield, string $joinfields, int $type, int $area)
	{
		// CONCATENATE GLUE
		if ($type === 1)
		{
			// MODEL
			if ($area === 1 || $area === 3)
			{
				return ', ';
			}

			// VIEW
			if ($area === 2)
			{
				return '<br />';
			}
		}
		// CUSTOM CODE
		elseif ($type === 2)
		{
			if ($joinfields !== 'none')
			{
				$fields = array_filter(
					array_map(
						static function ($guid) {
							return trim((string) $guid);
						},
						explode(',', $joinfields)
					),
					static function ($guid) {
						return $guid !== '';
					}
				);

				array_unshift($fields, $listfield);
			}
			else
			{
				$fields = [$listfield];
			}

			$names = array_map(
				static function ($guid) {
					return '[' . $guid . ']=> ' . GetHelper::var('field', $guid, 'guid', 'name');
				},
				$fields
			);

			// MODEL
			if ($area === 1 || $area === 3)
			{
				$note = '// ' . implode('; ', $names);

				return $note . PHP_EOL
					. '$item->{' . $listfield . '} = $item->{'
					. implode("} . ', ' . \$item->{", $fields)
					. '};';
			}

			// VIEW
			if ($area === 2)
			{
				$note = '<!--  ' . implode('; ', $names) . ' -->';

				return '[field=' . implode(']<br />[field=', $fields) . ']'
					. PHP_EOL . PHP_EOL . $note;
			}
		}

		return false;
	}

	// Used in search
	/**
	 * Search for value in a table
	 *
	 * @param   string           $tableName    The main table to search
	 * @param   int              $typeSearch  The type of search being done
	 * @param   string           $searchValue  The value to search for
	 * @param   int               $matchCase  The switch to control match case
	 * @param   int               $wholeWord  The switch to control whole word
	 * @param   int               $regexSearch  The switch to control regex search
	 * @param   int               $componentId  The option to filter by component
	 *
	 * @return  array|null
	 * @since   3.2.0
	 **/
	public function doSearch(string $tableName, int $typeSearch, string $searchValue,
		int $matchCase, int $wholeWord, int $regexSearch, int $componentId): ?array
	{
		// check if this is a valid table
		if (SearchFactory::_('Table')->exist($tableName))
		{
			try
			{
				// load the configurations
				SearchFactory::_('Config')->table_name = $tableName;
				SearchFactory::_('Config')->type_search = $typeSearch;
				SearchFactory::_('Config')->search_value = $searchValue;
				SearchFactory::_('Config')->match_case = $matchCase;
				SearchFactory::_('Config')->whole_word = $wholeWord;
				SearchFactory::_('Config')->regex_search = $regexSearch;
				SearchFactory::_('Config')->component_id = $componentId;

				if (($items = SearchFactory::_('Agent')->table($tableName)) !== null)
				{
					return [
						'success' => Text::sprintf('COM_COMPONENTBUILDER_WE_FOUND_SOME_INSTANCES_IN_S', $tableName),
						'items' => $items,
						'fields_count' => SearchFactory::_('Config')->field_counter,
						'line_count' => SearchFactory::_('Config')->line_counter
					];
				}

				return [
					'not_found' => Text::sprintf('COM_COMPONENTBUILDER_NO_INSTANCES_WHERE_FOUND_IN_S', $tableName),
					'fields_count' => SearchFactory::_('Config')->field_counter,
					'line_count' => SearchFactory::_('Config')->line_counter
				];
			}
			catch(Exception $error)
			{
				return ['error' => $error->getMessage()];
			}
		}

		return ['error' => Text::_('COM_COMPONENTBUILDER_THERE_HAS_BEEN_AN_ERROR_PLEASE_TRY_AGAIN')];
	}

	/**
	 * Search and replace value in a table
	 *
	 * @param   string           $tableName    The main table to search
	 * @param   string           $searchValue  The value to search for
	 * @param   string|null      $replaceValue  The value to replace search value
	 * @param   int              $matchCase  The switch to control match case
	 * @param   int              $wholeWord  The switch to control whole word
	 * @param   int              $regexSearch  The switch to control regex search
	 * @param   int              $componentId  The option to filter by component
	 *
	 * @return  array|null
	 * @since   3.2.0
	 **/
	public function replaceAll(string $tableName, string $searchValue, ?string $replaceValue,
		int $matchCase, int $wholeWord, int $regexSearch, int $componentId): ?array
	{
		// check if this is a valid table
		if (SearchFactory::_('Table')->exist($tableName))
		{
			try
			{
				// load the configurations
				SearchFactory::_('Config')->table_name = $tableName;
				SearchFactory::_('Config')->search_value = $searchValue;
				SearchFactory::_('Config')->replace_value = $replaceValue;
				SearchFactory::_('Config')->match_case = $matchCase;
				SearchFactory::_('Config')->whole_word = $wholeWord;
				SearchFactory::_('Config')->regex_search = $regexSearch;
				SearchFactory::_('Config')->component_id = $componentId;

				if (($number = SearchFactory::_('Agent')->replace()) !== 0)
				{
					return ['success' => Text::sprintf('COM_COMPONENTBUILDER_ALL_FOUND_INSTANCES_IN_S_WHERE_REPLACED', $tableName)];
				}
				return ['not_found' => Text::sprintf('COM_COMPONENTBUILDER_NO_INSTANCES_WHERE_FOUND_IN_S', $tableName)];
			}
			catch(Exception $error)
			{
				return ['error' => $error->getMessage()];
			}
		}
		return ['error' => Text::_('COM_COMPONENTBUILDER_THERE_HAS_BEEN_AN_ERROR_PLEASE_TRY_AGAIN')];
	}

	/**
	 * Get a selected search value from a given table and row
	 *
	 * @param   string         $fieldName    The field key
	 * @param   int            $rowId        The item ID
	 * @param   string         $tableName    The table
	 * @param   string         $searchValue  The value to search for
	 * @param   string|null    $replaceValue The value to replace search value
	 * @param   int            $matchCase    The switch to control match case
	 * @param   int            $wholeWord    The switch to control whole word
	 * @param   int            $regexSearch  The switch to control regex search
	 *
	 * @return  array
	 * @since   3.2.0
	 **/
	public function getSearchValue(string $fieldName, int $rowId, string $tableName,
		string $searchValue, ?string $replaceValue, int $matchCase, int $wholeWord, int $regexSearch): array
	{
		// check if this is a valid table and field
		if ($rowId > 0 && SearchFactory::_('Table')->exist($tableName, $fieldName))
		{
			try
			{
				// load the configurations
				SearchFactory::_('Config')->table_name = $tableName;
				SearchFactory::_('Config')->type_search = 1;
				SearchFactory::_('Config')->search_value = $searchValue;
				SearchFactory::_('Config')->replace_value = $replaceValue;
				SearchFactory::_('Config')->match_case = $matchCase;
				SearchFactory::_('Config')->whole_word = $wholeWord;
				SearchFactory::_('Config')->regex_search = $regexSearch;

				if (($value = SearchFactory::_('Agent')->getValue($rowId, $fieldName, 0, $tableName)) !== null)
				{
					// load the value
					return ['value' => $value];
				}
			}
			catch(Exception $error)
			{
				return ['error' => $error->getMessage()];
			}
		}
		return ['error' => Text::_('COM_COMPONENTBUILDER_THERE_HAS_BEEN_AN_ERROR_PLEASE_TRY_AGAIN')];
	}

	/**
	 * Get a replaced search value from a given table and row
	 *
	 * @param   string         $fieldName    The field key
	 * @param   int            $rowId        The item ID
	 * @param   mixed          $line         The line line
	 * @param   string         $tableName    The table
	 * @param   string         $searchValue  The value to search for
	 * @param   string|null    $replaceValue The value to replace search value
	 * @param   int            $matchCase    The switch to control match case
	 * @param   int            $wholeWord    The switch to control whole word
	 * @param   int            $regexSearch  The switch to control regex search
	 *
	 * @return  array
	 * @since   3.2.0
	 **/
	public function getReplaceValue(string $fieldName, int $rowId, $line, string $tableName,
		string $searchValue, ?string $replaceValue, int $matchCase, int $wholeWord, int $regexSearch): array
	{
		// check if this is a valid table and field
		if ($rowId > 0 && SearchFactory::_('Table')->exist($tableName, $fieldName))
		{
			try
			{
				// load the configurations
				SearchFactory::_('Config')->table_name = $tableName;
				SearchFactory::_('Config')->type_search = 2;
				SearchFactory::_('Config')->search_value = $searchValue;
				SearchFactory::_('Config')->replace_value = $replaceValue;
				SearchFactory::_('Config')->match_case = $matchCase;
				SearchFactory::_('Config')->whole_word = $wholeWord;
				SearchFactory::_('Config')->regex_search = $regexSearch;

				// load the value
				if (($value = SearchFactory::_('Agent')->getValue($rowId, $fieldName, $line, $tableName, true)) !== null)
				{
					return ['value' => $value];
				}
			}
			catch(Exception $error)
			{
				return ['error' => $error->getMessage()];
			}
		}
		return ['error' => Text::_('COM_COMPONENTBUILDER_THERE_HAS_BEEN_AN_ERROR_PLEASE_TRY_AGAIN')];
	}

	/**
	 * Set selected search value in a given table and row
	 *
	 * @param   mixed        $value        The field value
	 * @param   int          $rowId        The item ID
	 * @param   string       $fieldName    The field key
	 * @param   string       $tableName    The table
	 *
	 * @return  array
	 * @since   3.2.0
	 **/
	public function setValue($value, int $rowId, string $fieldName, string $tableName): array
	{
		// check if this is a valid table and field
		if ($rowId > 0 && SearchFactory::_('Table')->exist($tableName, $fieldName) &&
			SearchFactory::_('Agent')->setValue($value, $rowId, $fieldName, $tableName))
		{
			return ['success' => Text::sprintf(
					'<b>%s</b> (%s:%s) was successfully updated!',
					$tableName, $rowId, $fieldName)];
		}
		return ['error' => Text::_('COM_COMPONENTBUILDER_THERE_HAS_BEEN_AN_ERROR_PLEASE_TRY_AGAIN')];
	}


	// Used in initialization_selection
	/**
	 * Method to get the power get class
	 *
	 * @param   string  $repo  The repo to list index
	 * @param   string  $area  The target area
	 *
	 * @return  array
	 * @since   5.1.1
	 */
	public function getRepoIndex(string $repo, string $area): array
	{
		if (!GuidHelper::valid($repo))
		{
			return ['success' => false, 'message' => Text::_('COM_COMPONENTBUILDER_INVALID_REPO_SELECTED')];
		}

		if (($entity = ComponentbuilderFactory::getEntity($area)) === null)
		{
			return ['success' => false, 'message' => Text::_('COM_COMPONENTBUILDER_INVALID_AREA_SELECTED')];
		}

		try
		{
			$class = ComponentbuilderFactory::_($entity, "{$area}.Remote.Get");
			if ($class !== null)
			{
				$result = $class->list($repo);
			}
		}
		catch (\Exception $e)
		{
			return ['success' => false, 'message' => $e->getMessage()];
		}

		if (!empty($result))
		{
			foreach($result as &$values)
			{
				// ensure we don't leak the repo token
				if (isset($values->token))
				{
					$values->token = '***redacted***';
				}
			}

			return ['success' => true, 'index' => $result];
		}

		return ['success' => false, 'message' => Text::_('COM_COMPONENTBUILDER_THE_REPO_INDEX_FAILED_TO_LOAD_PLEASE_TRY_AGAIN')];
	}

	/**
	 * Method to initialize the selected packages
	 *
	 * @param   string  $repo      The repo to list index
	 * @param   string  $area      The target area
	 * @param   array   $selected  The selected powers
	 *
	 * @return  array
	 * @since   5.1.1
	 */
	public function initSelectedPackages(string $repo, string $area, array $selected): array
	{
		if (!GuidHelper::valid($repo))
		{
			return ['success' => false, 'message' => Text::_('COM_COMPONENTBUILDER_INVALID_REPO_SELECTED')];
		}

		if (($entity = ComponentbuilderFactory::getEntity($area)) === null)
		{
			return ['success' => false, 'message' => Text::_('COM_COMPONENTBUILDER_INVALID_AREA_SELECTED')];
		}

		$result = [];
		try
		{
			$class = ComponentbuilderFactory::_($entity, "Package.Builder.Get");
			$prep = ComponentbuilderFactory::_($entity, "{$area}.Remote.Get");
			if (!empty($selected) && $class !== null && $prep !== null)
			{
				$repo_path = $prep->path($repo);
				$result = $class->init($entity, $selected, $repo_path);
			}
		}
		catch (\Exception $e)
		{
			return ['success' => false, 'message' => $e->getMessage()];
		}

		if ($this->hasIntResults($result))
		{
			return ['success' => true, 'result_log' => $result];
		}

		return ['success' => false, 'message' => Text::_('COM_COMPONENTBUILDER_THE_INITIALIZATION_FAILED_PLEASE_TRY_AGAIN')];
	}

	/**
	 * Check if at least one key in the array has a non-empty value.
	 *
	 * @param array $data The result array (with 'local', 'not_found', 'added' keys)
	 *
	 * @return bool True if some values are non-empty; false if all are empty.
	 * @since  5.1.1
	 */
	protected static function hasIntResults(array $data): bool
	{
		return (bool) array_filter($data);
	}

	// Used in pull_selection
	/**
	 * Method to pull the selected packages
	 *
	 * @param   string  $repo      The repo to list index
	 * @param   string  $area      The target area
	 * @param   array   $selected  The selected powers
	 *
	 * @return  array
	 * @since   5.1.4
	 */
	public function pullSelectedPackages(string $repo, string $area, array $selected): array
	{
		if (!GuidHelper::valid($repo))
		{
			return ['success' => false, 'message' => Text::_('COM_COMPONENTBUILDER_INVALID_REPO_SELECTED')];
		}

		if (($entity = ComponentbuilderFactory::getEntity($area)) === null)
		{
			return ['success' => false, 'message' => Text::_('COM_COMPONENTBUILDER_INVALID_AREA_SELECTED')];
		}

		$result = [];
		try
		{
			$class = ComponentbuilderFactory::_($entity, "Package.Builder.Get");
			$prep = ComponentbuilderFactory::_($entity, "{$area}.Remote.Get");
			if (!empty($selected) && $class !== null && $prep !== null)
			{
				$repo_path = $prep->path($repo);
				$result = $class->init($entity, $selected, $repo_path, true);
			}
		}
		catch (\Exception $e)
		{
			return ['success' => false, 'message' => $e->getMessage()];
		}

		if ($this->hasIntResults($result))
		{
			return ['success' => true, 'result_log' => $result];
		}

		return ['success' => false, 'message' => Text::_('COM_COMPONENTBUILDER_THE_PULL_FAILED_PLEASE_TRY_AGAIN')];
	}

	// Used in import_translations
	/**
	 * Get the translation details, if it exists.
	 *
	 * @param string $guid    The translation (file_type) guid
	 * @param string $target  The target entity name
	 *
	 * @return array
	 * @since  5.1.4
	 */
	public function getTranslationDetails(string $guid, string $target): array
	{
		if (GuidHelper::valid($guid))
		{
			try
			{
				$target = base64_decode($target);
				if ('import_translations' === $target)
				{
					$importer_type = SessionHelper::get("componentbuilder_{$target}_{$guid}", null);
					if (!empty($importer_type))
					{
						$type = (object) $importer_type;
					}
				}
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			if (!empty($type))
			{
				return ['data' => $type];
			}
		}

		return ['error' => Text::_('COM_COMPONENTBUILDER_TRANSLATION_DETAILS_COULD_NOT_BE_FOUND')];
	}

	/**
	 * Upload a file, of a translated.
	 *
	 * @param string $guid    The translation session guid
	 * @param string $entity  The entity guid
	 * @param string $target  The target entity name
	 *
	 * @return array
	 * @since  5.1.4
	 */
	public function uploadTranslation(string $guid, string $entity, string $target): array
	{
		if (GuidHelper::valid($guid)
			&& GuidHelper::valid($entity))
		{
			try
			{
				$target = base64_decode($target);
				if ('import_translations' === $target)
				{
					$type = SessionHelper::get("componentbuilder_{$target}_{$guid}", null);
				}
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			try
			{
				if (!empty($type))
				{
					$fileDefinition = FileFactory::_('File.Agent')->type(
						(new TypeDefinition($type))
					)->get();
				}
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			if (!empty($fileDefinition) && 'import_translations' === $target)
			{
				SessionHelper::set("componentbuilder_{$entity}", $fileDefinition->toArray());

				return ['success' => Text::_('COM_COMPONENTBUILDER_THE_FILE_WAS_SUCCESSFULLY_UPLOADED')];
			}
		}

		return ['error' => Text::_('COM_COMPONENTBUILDER_THE_FILE_FAILED_TO_UPLOAD')];
	}

	/**
	 * Load the display of the import linked this entity.
	 *
	 * @param string $entity  The entity guid
	 * @param string $target  The target entity name
	 *
	 * @return array
	 * @since 5.0.2
	 */
	public function displayTranslationColumns(string $entity, string $target): array
	{
		if (GuidHelper::valid($entity))
		{
			$display = null;

			try
			{
				$target = base64_decode($target);
				$fileDefinition = null;
				if ('import_translations' === $target)
				{
					$fileDefinition = SessionHelper::get("componentbuilder_{$entity}");
				}

				if ($fileDefinition !== null)
				{
					$displayData =  ['data' => [(object) $fileDefinition], 'entity' => $entity, 'target' => $target];
					// change this to the layout of your custom importer columns display
					$display = LayoutHelper::render('translationimportercolumnsdisplayjsix', $displayData);
				}
				else
				{
					// change this to the layout of your custom importer easy mapping
					return ['data' => LayoutHelper::render('translationimportereasymappingjsix', []), 'state' => 0];
				}
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			if (!empty($display))
			{
				return ['data' => $display, 'state' => 1];
			}
		}

		return ['error' => Text::_('COM_COMPONENTBUILDER_THE_FILE_DISPLAY_COULD_NOT_BE_LOADED')];
	}

	/**
	 * Delete a file of a given entity.
	 *
	 * @param string $guid    The file guid
	 *
	 * @return array
	 * @since  5.1.4
	 */
	public function deleteTranslation(string $guid): array
	{
		if (GuidHelper::valid($guid))
		{
			$success = false;

			try
			{
				$fileDefinition = SessionHelper::get("componentbuilder_{$guid}", null);
				if ($fileDefinition !== null)
				{
					SessionHelper::set("componentbuilder_{$guid}", null);
					$success = FileFactory::_('File.Agent')->delete($fileDefinition->file_path);
				}
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			if ($success)
			{
				return ['success' => Text::_('COM_COMPONENTBUILDER_THE_TRANSLATIONS_FILE_WAS_SUCCESSFULLY_DELETED')];
			}
		}

		return ['error' => Text::_('COM_COMPONENTBUILDER_THE_TRANSLATIONS_FILE_COULD_NOT_BE_DELETED')];
	}

	// Used in extrusion
	/**
	 * Safe diagnostics already generated for this request's backend reports.
	 *
	 * @var   array<string, array>
	 * @since 6.2.1
	 */
	protected array $extrusionFailureReferences = [];

	/**
	 * Harvest an extrusion source and return the approval payload.
	 *
	 * Nothing is written here. The source is read, resolved and lined up
	 * against the target component, and the whole tree goes back to the
	 * page for the pairing step.
	 *
	 * Language note: user-facing strings here are natural strings inside
	 * Text::_() by design, never language constants -- JCB manages these
	 * strings itself when this code is imported.
	 *
	 * @param string $config  The run configuration as a JSON object.
	 *
	 * @return array
	 * @since  6.1.7
	 */
	public function extrusionHarvest(string $config): array
	{
		$user = method_exists($this, 'getCurrentUser')
			? $this->getCurrentUser()
			: Factory::getApplication()->getIdentity();

		if (!$user->authorise('extrusion.access', 'com_componentbuilder'))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_YOU_DO_NOT_HAVE_PERMISSION_TO_USE_THE_EXTRUSION_TOOL')];
		}

		$options = json_decode($config, true);

		if (!is_array($options))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_THE_EXTRUSION_CONFIGURATION_COULD_NOT_BE_READ')];
		}

		$phase = 'harvest';
		$completed = 'validated';

		try
		{
			$phase = 'configure';
			[$extruder, $powers] = $this->extrusionEngines($options);
			$completed = $phase;
			$phase = 'harvest';

			if ($extruder === null && $powers === null)
			{
				return ['error' => Text::_('COM_COMPONENTBUILDER_GIVE_THE_TOOL_AT_LEAST_A_COMPONENT_SOURCE_FOLDER_AN_SQL_DUMP_OR_A_LIBRARY_FOLDER_TO_HARVEST')];
			}

			$extruder?->harvest();
			$completed = $phase;
			$phase = 'candidates';

			$candidates = ExtrusionFactory::_('Extrusion.Resolver.Candidates');
			$component = (int) ($options['component'] ?? 0);
			$detected = $extruder !== null ? $candidates->detect() : null;

			if (!empty($options['detect']) && $detected !== null)
			{
				$component = (int) $detected->id;
			}

			$sourceComponent = max(0, (int) ($options['source_component'] ?? $component));

			$harvested = [
				'success' => Text::_('COM_COMPONENTBUILDER_THE_SOURCE_WAS_HARVESTED_REVIEW_THE_PAIRINGS_BELOW_THEN_IMPORT'),
				'component' => $component,
				'source_component' => $sourceComponent,
				'detected' => $detected,
				'components' => $candidates->components(),
				'candidates' => $extruder !== null ? $candidates->candidates($component) : null,
				'powers' => null,
				'messages' => ExtrusionFactory::_('Extruder')->messages(),
				'report' => $this->extrusionPublicReport()
			];

			// what every row of the board would change. The account of the
			// harvest is taken first, above, because weighing starts the run
			// over: it has to be the very run an import makes, or the board
			// would answer for a run nobody is going to make -- so it is aimed
			// at the component the board pairs against, detected or chosen
			$completed = $phase;
			$phase = 'preview';

			try
			{
				$harvested['changes'] = $this->extrusionProposals(
					['component' => $component, 'source_component' => $sourceComponent, 'detect' => false] + $options
				) ?? [];
				$harvested = array_replace($harvested, $this->extrusionReview());
				$harvested['messages'] = ExtrusionFactory::_('Extruder')->messages();
				$harvested['report'] = $this->extrusionPublicReport();
			}
			catch (\Throwable $error)
			{
				// the harvest stood; only the weighing fell, and the board says so
				$harvested['changes'] = [];
				$failure = $this->extrusionFailure($error, $phase, $completed);
				$harvested['weighing'] = $failure['error'] . ' '
					. Text::_('COM_COMPONENTBUILDER_FAILURE_REFERENCE') . ' ' . $failure['failure']['reference'];
				$harvested['failure'] = $failure['failure'];
			}

			return $harvested;
		}
		catch (\Throwable $error)
		{
			return $this->extrusionFailure($error, $phase, $completed);
		}
	}

	/**
	 * Run an extrusion import under the pairing verdicts of the page.
	 *
	 * The source is harvested again server-side -- the page's tree is a
	 * preview, never the payload -- and every identity the writers settle
	 * is governed by the loaded verdicts.
	 *
	 * @param string $config     The run configuration as a JSON object.
	 * @param string $decisions  The pairing verdicts as a JSON object.
	 *
	 * @return array
	 * @since  6.1.7
	 */
	public function extrusionImport(string $config, string $decisions): array
	{
		$user = method_exists($this, 'getCurrentUser')
			? $this->getCurrentUser()
			: Factory::getApplication()->getIdentity();

		if (!$user->authorise('extrusion.access', 'com_componentbuilder'))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_YOU_DO_NOT_HAVE_PERMISSION_TO_IMPORT_WITH_THE_EXTRUSION_TOOL')];
		}

		$options = json_decode($config, true);
		$verdicts = json_decode($decisions, true);

		if (!is_array($options) || !is_array($verdicts))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_THE_EXTRUSION_CONFIGURATION_COULD_NOT_BE_READ')];
		}

		if (empty($options['dry_run']) && !preg_match('/^[a-f0-9]{64}$/D', (string) ($options['approved_plan'] ?? '')))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_REVIEW_THE_CURRENT_WRITE_PLAN_BEFORE_IMPORTING')];
		}

		$phase = 'import';
		$completed = 'validated';

		try
		{
			$phase = 'configure';
			[$extruder, $powers] = $this->extrusionEngines($options);
			$completed = $phase;
			$phase = 'import';

			if ($extruder === null && $powers === null)
			{
				return ['error' => Text::_('COM_COMPONENTBUILDER_GIVE_THE_TOOL_AT_LEAST_A_COMPONENT_SOURCE_FOLDER_AN_SQL_DUMP_OR_A_LIBRARY_FOLDER_TO_HARVEST')];
			}

			// the verdicts load after the engines reset, because reset is the run boundary
			if (is_array($verdicts) && $verdicts !== [])
			{
				ExtrusionFactory::_('Extrusion.Resolver.Pairing')->load($verdicts);
			}

			// The component engine owns the shared plan, including its libraries.
			($extruder ?? $powers)->extrude();
			$completed = $phase;
			$phase = 'review';
			$review = $this->extrusionReview();
			$status = $review['plan']['status'] ?? 'blocked';

			return $review + [
				(in_array($status, ['committed', 'unchanged', 'preview'], true) ? 'success' : 'error')
					=> in_array($status, ['committed', 'unchanged', 'preview'], true)
						? Text::_('COM_COMPONENTBUILDER_THE_IMPORT_HAS_RUN_THE_FULL_REPORT_FOLLOWS')
						: Text::_('COM_COMPONENTBUILDER_THE_IMPORT_WAS_BLOCKED_OR_ROLLED_BACK_REVIEW_THE_REPORTED_PLAN_BEFORE_TRYING_AGAIN'),
				'messages' => ExtrusionFactory::_('Extruder')->messages(),
				'report' => $this->extrusionPublicReport()
			];
		}
		catch (\Throwable $error)
		{
			return $this->extrusionFailure($error, $phase, $completed);
		}
	}

	/**
	 * The catalogue of one component's linked definitions.
	 *
	 * The pairing board calls this when the person points the harvest at
	 * another component, so every proposed pairing can be re-drawn from
	 * what that component actually links.
	 *
	 * @param int $componentId  The component id, zero for none.
	 *
	 * @return array
	 * @since  6.1.7
	 */
	public function extrusionCatalogue(int $componentId): array
	{
		$user = method_exists($this, 'getCurrentUser')
			? $this->getCurrentUser()
			: Factory::getApplication()->getIdentity();

		if (!$user->authorise('extrusion.access', 'com_componentbuilder'))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_YOU_DO_NOT_HAVE_PERMISSION_TO_USE_THE_EXTRUSION_TOOL')];
		}

		$phase = 'catalogue';
		$completed = 'validated';

		try
		{
			return ExtrusionFactory::_('Extrusion.Resolver.Candidates')->catalogue(
				$componentId,
				trim($this->app->input->getString('power_search', ''))
			);
		}
		catch (\Throwable $error)
		{
			return $this->extrusionFailure($error, $phase, $completed);
		}
	}

	/**
	 * List the folders below one folder of this site.
	 *
	 * The extrusion setup selects its folders by walking the site from its
	 * root, never by typing paths. Only folders below the site root answer;
	 * anything else -- traversal, symlinks out, files -- is refused.
	 *
	 * @param string $path  The folder relative to the site root, '' for the root.
	 *
	 * @return array
	 * @since  6.1.7
	 */
	public function extrusionFolders(string $path): array
	{
		$user = method_exists($this, 'getCurrentUser')
			? $this->getCurrentUser()
			: Factory::getApplication()->getIdentity();

		if (!$user->authorise('extrusion.access', 'com_componentbuilder'))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_YOU_DO_NOT_HAVE_PERMISSION_TO_USE_THE_EXTRUSION_TOOL')];
		}

		$base = realpath(JPATH_ROOT);

		if ($base === false)
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_THE_SITE_ROOT_COULD_NOT_BE_RESOLVED')];
		}

		$relative = trim(str_replace('\\', '/', $path), '/');
		$target = $relative === '' ? $base : realpath($base . '/' . $relative);

		if ($target === false || !is_dir($target)
			|| ($target !== $base && !str_starts_with($target, $base . DIRECTORY_SEPARATOR)))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_THAT_FOLDER_DOES_NOT_EXIST_BELOW_THE_SITE_ROOT')];
		}

		$relative = trim(str_replace('\\', '/', substr($target, strlen($base))), '/');
		$folders = [];

		foreach (scandir($target) ?: [] as $entry)
		{
			if ($entry !== '.' && $entry !== '..' && is_dir($target . '/' . $entry))
			{
				$folders[] = $entry;
			}
		}

		sort($folders, SORT_NATURAL | SORT_FLAG_CASE);

		return [
			'base' => $base,
			'path' => $relative,
			'parent' => $relative === ''
				? null
				: (str_contains($relative, '/')
					? substr($relative, 0, strrpos($relative, '/')) : ''),
			'folders' => $folders
		];
	}

	/**
	 * Aim and configure the extrusion engines for one run.
	 *
	 * The reset comes first and clears all run state, then every option the
	 * page sent is applied. An engine that was given nothing to read comes
	 * back null, so the caller knows which pipelines are in play.
	 *
	 * @param array $options  The run configuration.
	 *
	 * @return array  The component extruder (or null) and the powers extruder (or null).
	 * @since  6.1.7
	 */
	protected function extrusionEngines(array $options): array
	{
		$extruder = ExtrusionFactory::_('Extruder');
		$powers = ExtrusionFactory::_('Extrusion.Powers.Extruder');

		// one reset clears the shared run state of both engines
		$extruder->reset();

		$path = trim((string) ($options['path'] ?? ''));
		$admin = trim((string) ($options['admin_path'] ?? ''));
		$site = trim((string) ($options['site_path'] ?? ''));
		$dump = trim((string) ($options['dump'] ?? ''));
		$libraries = array_values(array_filter(array_map('trim',
			(array) ($options['libraries'] ?? []))));

		$component = max(0, (int) ($options['component'] ?? 0));
		$componentCode = trim((string) ($options['component_code'] ?? ''));
		$repairNamespaces = in_array($options['repair_namespaces'] ?? false, [true, 1, '1'], true);

		if ($repairNamespaces && ($component === 0 || ($options['mode'] ?? 'create') !== 'update'))
		{
			throw new \InvalidArgumentException(Text::_('COM_COMPONENTBUILDER_NAMESPACE_REPAIR_REQUIRES_UPDATE_MODE_AND_AN_EXPLICITLY_SELECTED_EXISTING_TARGET_COMPONENT'));
		}

		if ($repairNamespaces && $libraries === [])
		{
			throw new \InvalidArgumentException(Text::_('COM_COMPONENTBUILDER_SELECT_AT_LEAST_ONE_LIBRARY_SOURCE_FOLDER_CONTAINING_THE_CLASSES_WHOSE_POWER_NAMESPACES_SHOULD_BE_REPAIRED'));
		}

		if ($repairNamespaces && isset($options['source_component']) && (int) $options['source_component'] <= 0)
		{
			throw new \InvalidArgumentException(Text::_('COM_COMPONENTBUILDER_NAMESPACE_REPAIR_REQUIRES_AN_EXISTING_SOURCE_COMPONENT_CONTEXT'));
		}

		$onExisting = (string) ($options['on_existing'] ?? 'update');

		if ($repairNamespaces)
		{
			$onExisting = 'update';
		}

		$dryRun = !empty($options['dry_run']);
		$depth = max(1, (int) ($options['depth'] ?? 12));
		$maxFiles = max(1, (int) ($options['max_files'] ?? 20000));

		$aimed = ($path !== '' || $admin !== '' || $site !== '' || $dump !== '');

		if ($aimed)
		{
			if ($path !== '')
			{
				$extruder->path($path);
			}

			if ($admin !== '')
			{
				$extruder->adminPath($admin);
			}

			if ($site !== '')
			{
				$extruder->sitePath($site);
			}

			if ($dump !== '')
			{
				$extruder->dump($dump);
			}

			if ($componentCode !== '')
			{
				// the person named the component the run creates for, so the
				// component namespace placeholder has a value to stand on
				$extruder->codeName($componentCode);
			}

			$extruder
				->component($component)
				->mode((string) ($options['mode'] ?? 'create'))
				->onExisting($onExisting)
				->layout((string) ($options['layout'] ?? 'auto'))
				->languageTag((string) ($options['language_tag'] ?? 'en-GB'))
				->tableClass((string) ($options['table_class'] ?? 'auto'))
				->dryRun($dryRun)
				->strict(!empty($options['strict']))
				->limits($depth, $maxFiles);

			foreach (['admin', 'site', 'tabs', 'conditions', 'language',
				'translations', 'relations', 'component_details'] as $scope)
			{
				if (isset($options['scope_' . $scope]))
				{
					$extruder->scope($scope, !empty($options['scope_' . $scope]));
				}
			}

			if (isset($options['scope_site_views']))
			{
				$extruder->scope('siteViews', !empty($options['scope_site_views']));
			}
		}

		if ($libraries !== [])
		{
			if ($componentCode !== '')
			{
				$powers->componentCode($componentCode);
			}

			$powers
				->libraries($libraries)
				->component($component)
				->onExisting($onExisting)
				->dryRun($dryRun)
				->limits($depth, $maxFiles);
		}

		$settings = ExtrusionFactory::_('Extrusion.Config');
		$settings->set('repairNamespaces', $repairNamespaces);
		$settings->set('sourceComponent', max(0, (int) ($options['source_component'] ?? $component)));
		$settings->set('approvedPlan', (string) ($options['approved_plan'] ?? ''));

		foreach (['shared', 'foreign', 'unknown', 'remapping'] as $scope)
		{
			$settings->set('acknowledge' . ucfirst($scope),
				($options['acknowledged_scopes'][$scope] ?? false) === true);
		}

		if (isset($options['source_bindings']))
		{
			if (!is_array($options['source_bindings']))
			{
				throw new \InvalidArgumentException(Text::_('COM_COMPONENTBUILDER_SOURCEROOT_BINDINGS_MUST_BE_A_MAPPING'));
			}

			$settings->set('sourceBindings', $options['source_bindings']);
		}

		return [$aimed ? $extruder : null, $libraries !== [] ? $powers : null];
	}

	/**
	 * What every row of the pairing board would change, under its decisions.
	 *
	 * The board asks this after every decision, because a decision on one row
	 * moves what other rows would write: pairing a view elsewhere re-pairs
	 * the fields under it, and ignoring a field changes the links its view
	 * carries. Only the weight of each row travels, so the answer stays the
	 * size of the board rather than the size of the change.
	 *
	 * Language note: user-facing strings here are natural strings inside
	 * Text::_() by design, never language constants -- JCB manages these
	 * strings itself when this code is imported.
	 *
	 * @param string $config     The run configuration as a JSON object.
	 * @param string $decisions  The pairing verdicts as a JSON object.
	 *
	 * @return array
	 * @since  6.2.0
	 */
	public function extrusionWeigh(string $config, string $decisions): array
	{
		$user = method_exists($this, 'getCurrentUser')
			? $this->getCurrentUser()
			: Factory::getApplication()->getIdentity();

		if (!$user->authorise('extrusion.access', 'com_componentbuilder'))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_YOU_DO_NOT_HAVE_PERMISSION_TO_USE_THE_EXTRUSION_TOOL')];
		}

		$options = json_decode($config, true);
		$verdicts = json_decode($decisions, true);

		if (!is_array($options))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_THE_EXTRUSION_CONFIGURATION_COULD_NOT_BE_READ')];
		}

		$phase = 'weigh';
		$completed = 'validated';

		try
		{
			if (!is_array($verdicts))
			{
				return ['error' => Text::_('COM_COMPONENTBUILDER_THE_PAIRING_DECISIONS_COULD_NOT_BE_READ')];
			}

			$changes = $this->extrusionProposals($options, $verdicts);

			if ($changes === null)
			{
				return ['error' => Text::_('COM_COMPONENTBUILDER_GIVE_THE_TOOL_AT_LEAST_A_COMPONENT_SOURCE_FOLDER_AN_SQL_DUMP_OR_A_LIBRARY_FOLDER_TO_HARVEST')];
			}

			$completed = $phase;
			$phase = 'review';

			return ['changes' => $changes] + $this->extrusionReview();
		}
		catch (\Throwable $error)
		{
			return $this->extrusionFailure($error, $phase, $completed);
		}
	}

	/**
	 * The whole change one row of the pairing board would make.
	 *
	 * Nothing is stored between the harvest and this call: the source is read
	 * and composed again, under the same verdicts, and only the row asked for
	 * is answered. A whole run costs a fraction of a second, so a person opens
	 * a diff and reads what stands at that very moment, never a cached picture
	 * of what stood earlier.
	 *
	 * Only the changed lines and the few around them travel, so the answer is
	 * the size of the change rather than the size of the record.
	 *
	 * Language note: user-facing strings here are natural strings inside
	 * Text::_() by design, never language constants -- JCB manages these
	 * strings itself when this code is imported.
	 *
	 * @param string $config     The run configuration as a JSON object.
	 * @param string $decisions  The pairing verdicts as a JSON object.
	 * @param string $row        The board row, as kind and key.
	 *
	 * @return array
	 * @since  6.2.0
	 */
	public function extrusionDiff(string $config, string $decisions, string $row): array
	{
		$user = method_exists($this, 'getCurrentUser')
			? $this->getCurrentUser()
			: Factory::getApplication()->getIdentity();

		if (!$user->authorise('extrusion.access', 'com_componentbuilder'))
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_YOU_DO_NOT_HAVE_PERMISSION_TO_USE_THE_EXTRUSION_TOOL')];
		}

		$options = json_decode($config, true);
		$verdicts = json_decode($decisions, true);
		$row = trim($row);

		if (!is_array($options) || !is_array($verdicts) || $row === '')
		{
			return ['error' => Text::_('COM_COMPONENTBUILDER_THE_EXTRUSION_CONFIGURATION_COULD_NOT_BE_READ')];
		}

		$phase = 'diff';
		$completed = 'validated';

		try
		{
			if ($this->extrusionProposals($options, is_array($verdicts) ? $verdicts : []) === null)
			{
				return ['error' => Text::_('COM_COMPONENTBUILDER_GIVE_THE_TOOL_AT_LEAST_A_COMPONENT_SOURCE_FOLDER_AN_SQL_DUMP_OR_A_LIBRARY_FOLDER_TO_HARVEST')];
			}

			$completed = $phase;
			$phase = 'review';

			return [
				'row' => $row,
				'records' => $this->extrusionRecords($row)
			] + $this->extrusionReview();
		}
		catch (\Throwable $error)
		{
			return $this->extrusionFailure($error, $phase, $completed);
		}
	}

	/**
	 * Report a bounded operation failure without exposing exception content.
	 *
	 * @param   \Throwable|null  $error      The failure, or null when already captured by an engine.
	 * @param   string           $phase      The phase which did not finish.
	 * @param   string           $completed  The last completed phase.
	 *
	 * @return  array  Safe error text and a correlation reference.
	 * @since   6.2.1
	 */
	protected function extrusionFailure(?\Throwable $error, string $phase, string $completed): array
	{
		$reference = substr(hash('sha256', uniqid('', true)), 0, 16);
		$failure = [
			'kind' => 'operation',
			'reference' => $reference,
			'phase' => $phase,
			'last_completed_phase' => $completed
		];
		$counters = [];

		try
		{
			$report = ExtrusionFactory::_('Extrusion.Registry.Report');

			foreach (['new', 'existing', 'parsed', 'parse_reused', 'dependency_lookups',
				'dependency_reused', 'binding_checks', 'binding_applications',
				'blocked_dependency_edges'] as $key)
			{
				$counters['powers.' . $key] = (int) $report->get('counts.powers.' . $key, 0);
			}
		}
		catch (\Throwable $reportError)
		{
			// The operation may have failed while constructing the container.
		}

		try
		{
			\Joomla\CMS\Log\Log::addLogger(
				['text_file' => 'jcb-extrusion.php'],
				\Joomla\CMS\Log\Log::ALL,
				['com_componentbuilder.extrusion']
			);
			\Joomla\CMS\Log\Log::add(
				json_encode($failure + [
					'exception' => $error === null ? 'reported-by-engine' : get_class($error),
					'counters' => $counters,
					'peak_memory_bytes' => memory_get_peak_usage(true)
				], JSON_UNESCAPED_SLASHES),
				\Joomla\CMS\Log\Log::ERROR,
				'com_componentbuilder.extrusion'
			);
		}
		catch (\Throwable $loggingError)
		{
			// A broken logger or container must not replace the original failure.
		}

		return [
			'error' => Text::_('COM_COMPONENTBUILDER_THE_SERVER_COULD_NOT_COMPLETE_THIS_OPERATION_REVIEW_THE_CURRENT_STATE_AND_USE_THE_FAILURE_REFERENCE_WHEN_REPORTING_THE_PROBLEM'),
			'failure' => $failure
		];
	}

	/**
	 * Remove unexpected exception content from the browser's report copy.
	 *
	 * Engines preserve private failure details for their callers. The browser
	 * receives a correlation reference instead, while deliberate validation
	 * blockers retain their useful explanations. The private registry is unchanged.
	 *
	 * @return  array  The safe public report.
	 * @since   6.2.1
	 */
	protected function extrusionPublicReport(): array
	{
		$report = ExtrusionFactory::_('Extrusion.Registry.Report')->toArray();
		$plan = (array) ($report['plan'] ?? []);
		$preparation = [];

		foreach ((array) ($plan['blockers'] ?? []) as $index => $blocker)
		{
			if (is_array($blocker) && in_array($blocker['key'] ?? '', ['component.prepare', 'powers.prepare'], true))
			{
				$preparation[] = $index;
			}
		}

		if ($preparation === [] && !isset($plan['error']) && !isset($plan['rollback_error']))
		{
			return $report;
		}

		$key = hash('sha256', serialize($plan));

		if (!isset($this->extrusionFailureReferences[$key]))
		{
			$phase = $preparation === [] ? 'commit' : 'prepare';
			$this->extrusionFailureReferences[$key] = $this->extrusionFailure(
				null, $phase, $phase === 'commit' ? 'prepared' : 'configured'
			);
		}

		$failure = $this->extrusionFailureReferences[$key];
		$message = $failure['error'] . ' ' . Text::_('COM_COMPONENTBUILDER_FAILURE_REFERENCE') . ' ' . $failure['failure']['reference'];

		foreach ($preparation as $index)
		{
			$plan['blockers'][$index]['reason'] = $message;
		}

		foreach (['error', 'rollback_error'] as $field)
		{
			if (isset($plan[$field]))
			{
				$plan[$field] = $message;
			}
		}

		$report['plan'] = $plan;
		$report['failure'] = $failure['failure'];

		return $report;
	}

	/**
	 * Weigh what a run would write, without writing any of it.
	 *
	 * The weighing is a run of its own, from the source up, because that is
	 * the only run whose answer is worth anything: resolving a second time
	 * over a harvest that has already resolved settles the shared fields
	 * differently, and the board would then show weights for a run the import
	 * is never going to make. Writing is suppressed whatever the run itself
	 * is set to.
	 *
	 * @param array $options   The run configuration.
	 * @param array $verdicts  The pairing verdicts, when the caller has any.
	 *
	 * @return array|null  The weight of every board row, or null when nothing was aimed at.
	 * @since  6.2.0
	 */
	protected function extrusionProposals(array $options, array $verdicts = []): ?array
	{
		[$extruder, $powers] = $this->extrusionEngines(['dry_run' => true] + $options);

		if ($extruder === null && $powers === null)
		{
			return null;
		}

		// the verdicts load after the engines reset, because reset is the run boundary
		if ($verdicts !== [])
		{
			ExtrusionFactory::_('Extrusion.Resolver.Pairing')->load($verdicts);
		}

		($extruder ?? $powers)->extrude();

		return ExtrusionFactory::_('Extrusion.Registry.Proposal')->summary();
	}

	/**
	 * Every changed record of one board row, line by line.
	 *
	 * @param string $row  The board row, as kind and key.
	 *
	 * @return array
	 * @since  6.2.0
	 */
	protected function extrusionRecords(string $row): array
	{
		$diff = ExtrusionFactory::_('Extrusion.Resolver.Diff');
		$records = [];

		foreach (ExtrusionFactory::_('Extrusion.Registry.Proposal')->records() as $record)
		{
			if ((string) ($record['origin'] ?? '') !== $row || empty($record['changed']))
			{
				continue;
			}

			$columns = [];

			foreach ((array) ($record['columns'] ?? []) as $column => $change)
			{
				$columns[] = [
					'name' => $column,
					'shape' => (string) ($change['shape'] ?? 'value'),
					'additions' => (int) ($change['additions'] ?? 0),
					'deletions' => (int) ($change['deletions'] ?? 0),
					'hunks' => $diff->compare(
						(string) ($change['before'] ?? ''),
						(string) ($change['after'] ?? '')
					)['hunks']
				];
			}

			$records[] = [
				'table' => (string) ($record['table'] ?? ''),
				'identity' => (string) ($record['identity'] ?? ''),
				'action' => (string) ($record['action'] ?? 'update'),
				'additions' => (int) ($record['additions'] ?? 0),
				'deletions' => (int) ($record['deletions'] ?? 0),
				'columns' => $columns
			];
		}

		return $records;
	}

	/**
	 * The authoritative public review contract, without raw source or placeholder maps.
	 *
	 * @return array  The plan and freshly resolved Power rows for this context.
	 * @since  6.2.0
	 */
	protected function extrusionReview(): array
	{
		$plan = (array) ($this->extrusionPublicReport()['plan'] ?? []);

		return [
			'plan' => array_intersect_key($plan, array_flip([
				'fingerprint', 'status', 'required_approvals', 'changes', 'blockers', 'writes'
			])),
			'powers' => $this->extrusionPowersTree()
		];
	}

	/**
	 * The powers harvest tree, trimmed for the pairing board.
	 *
	 * A harvest can carry hundreds of classes, each with its full body --
	 * the page needs the identities and the grouping, never the code.
	 *
	 * @return array  The libraries and the trimmed class candidates.
	 * @since  6.1.7
	 */
	protected function extrusionPowersTree(): array
	{
		$tree = ExtrusionFactory::_('Extrusion.Powers.Extruder')->harvested();
		$classes = [];

		foreach ((array) ($tree['classes'] ?? []) as $candidate)
		{
			$candidate = (array) $candidate;
			$result = (array) ($candidate['resolution'] ?? []);
			$classes[] = [
				'source_key' => $candidate['source_key'] ?? '',
				'source_unit' => $candidate['source_unit'] ?? '',
				'guid' => $candidate['guid'] ?? '',
				'library' => $candidate['library'] ?? '',
				'bundle' => $candidate['bundle'] ?? '',
				'relative' => $candidate['relative'] ?? '',
				'class' => $candidate['class'] ?? '',
				'type' => $candidate['type'] ?? '',
				'fqn' => $candidate['fqn'] ?? '',
				'stored' => $candidate['stored'] ?? '',
				'exists' => !empty($candidate['exists']),
				'id' => (int) ($candidate['id'] ?? 0),
				'action' => $candidate['action'] ?? 'unresolved',
				'status' => $result['status'] ?? 'unresolved',
				'matched_guid' => $result['matched_guid'] ?? null,
				'write_guid' => $result['write_guid'] ?? null,
				'target' => $result['target'] ?? null,
				'consumers' => array_values((array) ($result['consumers'] ?? [])),
				'source_component' => $result['source_component'] ?? null,
				'target_component' => $result['target_component'] ?? null,
				'write_eligibility' => $result['write_eligibility'] ?? 'blocked',
				'write_scope' => $result['write_scope'] ?? 'unestablished',
				'reason' => $result['reason'] ?? '',
				'remapping' => !empty($result['remapping']),
				'blockers' => array_values((array) ($result['blockers'] ?? [])),
				'alternatives' => array_values((array) ($result['candidates'] ?? [])),
				'dependencies' => array_values((array) ($result['dependencies'] ?? [])),
				'namespace_proposal' => array_intersect_key((array) ($result['namespace'] ?? []), array_flip([
					'value', 'preserved', 'relocation', 'round_trip', 'provenance', 'target_fqn'
				]))
			];
		}

		return [
			'libraries' => (array) ($tree['libraries'] ?? []),
			'classes' => $classes
		];
	}
}
