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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Table\Table;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use Joomla\CMS\Helper\TagsHelper;
use VDM\Joomla\Utilities\SessionHelper;
use VDM\Joomla\Utilities\StringHelper as UtilitiesStringHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\GuidHelper;
use VDM\Joomla\FOF\Encrypt\AES;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\Component\Helper;
use VDM\Joomla\Data\Factory as DataFactory;
use VDM\Joomla\Utilities\GetHelper;
use VDM\Joomla\Utilities\String\ComponentCodeNameHelper;
use VDM\Joomla\Componentbuilder\Extrusion\Helper\Extrusion;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Form\FormHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Joomla_component Admin Model
 *
 * @since  1.6
 */
class Joomla_componentModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The tab layout fields array.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected $tabLayoutFields = array(
		'details' => array(
			'left' => array(
				'name',
				'name_code',
				'component_version',
				'debug_linenr',
				'add_placeholders',
				'remove_line_breaks',
				'mvc_versiondate',
				'note_version_options_1',
				'note_version_options_2',
				'note_version_options_3',
				'short_description',
				'description'
			),
			'right' => array(
				'companyname',
				'author',
				'email',
				'website',
				'license',
				'bom',
				'image',
				'copyright'
			),
			'above' => array(
				'system_name',
				'preferred_joomla_version',
				'add_powers'
			),
			'under' => array(
				'not_required'
			)
		),
		'dynamic_build' => array(
			'fullwidth' => array(
				'note_buildcomp_dynamic_mysql',
				'buildcomp',
				'buildcompsql'
			)
		),
		'dynamic_integration' => array(
			'left' => array(
				'add_update_server',
				'update_server_url',
				'update_server_target',
				'note_update_server_note_ftp',
				'note_update_server_note_zip',
				'note_update_server_note_other',
				'update_server',
				'add_sales_server',
				'sales_server',
				'add_backup_folder_path',
				'note_backup_folder_path',
				'backup_folder_path',
				'add_git_folder_path',
				'note_git_folder_path',
				'git_folder_path',
				'add_jcb_powers_path',
				'jcb_powers_path'
			),
			'right' => array(
				'add_changelog_server',
				'changelog_server_url',
				'changelog_server_target',
				'note_changelog_server_note_ftp',
				'note_changelog_server_note_zip',
				'note_changelog_server_note_other',
				'changelog_server',
				'translation_tool',
				'note_crowdin',
				'crowdin_project_identifier',
				'crowdin_project_api_key',
				'crowdin_username',
				'crowdin_account_api_key'
			)
		),
		'mysql' => array(
			'fullwidth' => array(
				'add_sql',
				'sql',
				'add_sql_uninstall',
				'sql_uninstall',
				'assets_table_fix'
			)
		),
		'dash_install' => array(
			'left' => array(
				'dashboard_type'
			),
			'right' => array(
				'note_dynamic_dashboard',
				'dashboard',
				'note_botton_component_dashboard'
			),
			'fullwidth' => array(
				'add_php_preflight_install',
				'php_preflight_install',
				'add_php_preflight_update',
				'php_preflight_update',
				'add_php_postflight_install',
				'php_postflight_install',
				'add_php_postflight_update',
				'php_postflight_update',
				'add_php_method_uninstall',
				'php_method_uninstall',
				'add_php_method_install',
				'php_method_install'
			)
		),
		'libs_helpers' => array(
			'fullwidth' => array(
				'creatuserhelper',
				'adduikit',
				'addfootable',
				'add_email_helper',
				'add_php_helper_both',
				'php_helper_both',
				'add_php_helper_admin',
				'php_helper_admin',
				'add_admin_event',
				'php_admin_event',
				'add_php_helper_site',
				'php_helper_site',
				'add_site_event',
				'php_site_event',
				'add_javascript',
				'javascript',
				'add_css_admin',
				'css_admin',
				'add_css_site',
				'css_site'
			)
		),
		'settings' => array(
			'left' => array(
				'note_moved_views',
				'spacer_hr_1',
				'note_mysql_tweak_options',
				'spacer_hr_2',
				'note_add_custom_menus',
				'spacer_hr_3',
				'note_add_config'
			),
			'right' => array(
				'note_component_files_folders',
				'spacer_hr_4',
				'add_namespace_prefix',
				'namespace_prefix',
				'spacer_hr_5',
				'add_menu_prefix',
				'menu_prefix',
				'spacer_hr_6',
				'to_ignore_note',
				'toignore',
				'spacer_hr_7'
			),
			'fullwidth' => array(
				'note_on_contributors',
				'addcontributors',
				'emptycontributors',
				'number'
			)
		),
		'readme' => array(
			'left' => array(
				'addreadme',
				'readme'
			),
			'right' => array(
				'note_readme'
			)
		),
		'admin_views' => array(
			'fullwidth' => array(
				'note_on_admin_views',
				'note_display_component_admin_views'
			)
		),
		'site_views' => array(
			'fullwidth' => array(
				'note_on_site_views',
				'note_display_component_site_views'
			)
		),
		'custom_admin_views' => array(
			'fullwidth' => array(
				'note_on_custom_admin_views',
				'note_display_component_custom_admin_views'
			)
		)
	);

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'media/com_componentbuilder/css/admin/admin.css',
		'media/com_componentbuilder/css/admin/joomla_component.css'
	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'media/com_componentbuilder/js/admin/admin.js',
		'media/com_componentbuilder/js/admin/joomla_component.js'
	];

	/**
	 * @var     string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_COMPONENTBUILDER';

	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_componentbuilder.joomla_component';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A database object
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getTable($type = 'joomla_component', $prefix = 'Administrator', $config = [])
	{
		// get instance of the table
		return parent::getTable($type, $prefix, $config);
	}


	/**
	 * The VDM view key
	 *
	 * @var    string
	 * @since   3.0.13
	 */
	protected string $vastDevMod;

	/**
	 * Retrieves or generates a Vast Development Method (VDM) key for the current item.
	 *
	 * This function performs the following operations:
	 * 1. Checks if the VDM key is already set. If not, it proceeds to generate or retrieve one.
	 * 2. Determines the item ID based on the presence of a specific argument.
	 * 3. Attempts to retrieve an existing VDM key from a helper method using the item ID.
	 * 4. If a VDM key is not found, it generates a new random VDM key.
	 * 5. Stores the VDM key and associates it with the item ID in a helper method.
	 * 6. Optionally, stores return and GUID values if available.
	 * 7. Returns the VDM key.
	 *
	 * @return string The VDM key for the current item.
	 * @since   3.0.13
	 */
	public function getVDM(): string
	{
		if (!isset($this->vastDevMod))
		{
			$_id = 0; // new item probably (since it was not set in the getItem method)

			if (empty($_id))
			{
				$id = 0;
			}
			else
			{
				$id = $_id;
			}
			// set the id and view name to session
			if (($vdm = SessionHelper::get('joomla_component__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'joomla_component__' . $id);
				SessionHelper::set('joomla_component__' . $id, $this->vastDevMod);
				// set a return value if found
				$app = $this->app ?? Factory::getApplication();
				$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
				$return = $input->get('return', null, 'base64');
				SessionHelper::set($this->vastDevMod . '__return', $return);
				// set a GUID value if found
				if (isset($item) && ObjectHelper::check($item) && isset($item->guid)
					&& GuidHelper::valid($item->guid))
				{
					SessionHelper::set($this->vastDevMod . '__guid', $item->guid);
				}
			}
		}

		return $this->vastDevMod;
	}


	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (property_exists($item, 'metadata') && !is_array($item->metadata))
			{
				// Convert the metadata field to an array.
				$metadata       = new Registry($item->metadata);
				$item->metadata = $metadata->toArray();
			}

			// API reads use read permissions; administrator editing keeps its edit guard.
			if (!empty($item->id))
			{
				$app = Factory::getApplication();

				if ($app->isClient('api'))
				{
					$user = $app->getIdentity();
					if (!($user->authorise('joomla_component.access', 'com_componentbuilder.joomla_component.' . $item->id) && $user->authorise('joomla_component.access', 'com_componentbuilder')) || (!$user->authorise('core.options', 'com_componentbuilder') && !in_array((int) $item->access, $user->getAuthorisedViewLevels())))
					{
						throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
					}
				}
				elseif (!$this->allowEdit((array) $item))
				{
					$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
					$app->redirect('index.php?option=com_componentbuilder');
					return false;
				}
			}

			if (!empty($item->buildcompsql))
			{
				// base64 Decode buildcompsql.
				$item->buildcompsql = base64_decode($item->buildcompsql);
			}

			if (!empty($item->readme))
			{
				// base64 Decode readme.
				$item->readme = base64_decode($item->readme);
			}

			if (!empty($item->javascript))
			{
				// base64 Decode javascript.
				$item->javascript = base64_decode($item->javascript);
			}

			if (!empty($item->css_admin))
			{
				// base64 Decode css_admin.
				$item->css_admin = base64_decode($item->css_admin);
			}

			if (!empty($item->css_site))
			{
				// base64 Decode css_site.
				$item->css_site = base64_decode($item->css_site);
			}

			if (!empty($item->php_preflight_install))
			{
				// base64 Decode php_preflight_install.
				$item->php_preflight_install = base64_decode($item->php_preflight_install);
			}

			if (!empty($item->php_preflight_update))
			{
				// base64 Decode php_preflight_update.
				$item->php_preflight_update = base64_decode($item->php_preflight_update);
			}

			if (!empty($item->php_postflight_install))
			{
				// base64 Decode php_postflight_install.
				$item->php_postflight_install = base64_decode($item->php_postflight_install);
			}

			if (!empty($item->php_postflight_update))
			{
				// base64 Decode php_postflight_update.
				$item->php_postflight_update = base64_decode($item->php_postflight_update);
			}

			if (!empty($item->php_method_uninstall))
			{
				// base64 Decode php_method_uninstall.
				$item->php_method_uninstall = base64_decode($item->php_method_uninstall);
			}

			if (!empty($item->php_method_install))
			{
				// base64 Decode php_method_install.
				$item->php_method_install = base64_decode($item->php_method_install);
			}

			if (!empty($item->sql))
			{
				// base64 Decode sql.
				$item->sql = base64_decode($item->sql);
			}

			if (!empty($item->sql_uninstall))
			{
				// base64 Decode sql_uninstall.
				$item->sql_uninstall = base64_decode($item->sql_uninstall);
			}

			if (!empty($item->php_helper_both))
			{
				// base64 Decode php_helper_both.
				$item->php_helper_both = base64_decode($item->php_helper_both);
			}

			if (!empty($item->php_helper_admin))
			{
				// base64 Decode php_helper_admin.
				$item->php_helper_admin = base64_decode($item->php_helper_admin);
			}

			if (!empty($item->php_admin_event))
			{
				// base64 Decode php_admin_event.
				$item->php_admin_event = base64_decode($item->php_admin_event);
			}

			if (!empty($item->php_helper_site))
			{
				// base64 Decode php_helper_site.
				$item->php_helper_site = base64_decode($item->php_helper_site);
			}

			if (!empty($item->php_site_event))
			{
				// base64 Decode php_site_event.
				$item->php_site_event = base64_decode($item->php_site_event);
			}

			// Get the basic encryption.
			$basickey = ComponentbuilderHelper::getCryptKey('basic');
			// Get the encryption object.
			$basic = new AES($basickey);

			if (!empty($item->crowdin_username) && $basickey && !is_numeric($item->crowdin_username) && $item->crowdin_username === base64_encode(base64_decode($item->crowdin_username, true)))
			{
				// basic decrypt data crowdin_username.
				$item->crowdin_username = rtrim($basic->decryptString($item->crowdin_username), "\0");
			}

			if (!empty($item->crowdin_project_api_key) && $basickey && !is_numeric($item->crowdin_project_api_key) && $item->crowdin_project_api_key === base64_encode(base64_decode($item->crowdin_project_api_key, true)))
			{
				// basic decrypt data crowdin_project_api_key.
				$item->crowdin_project_api_key = rtrim($basic->decryptString($item->crowdin_project_api_key), "\0");
			}

			if (!empty($item->crowdin_account_api_key) && $basickey && !is_numeric($item->crowdin_account_api_key) && $item->crowdin_account_api_key === base64_encode(base64_decode($item->crowdin_account_api_key, true)))
			{
				// basic decrypt data crowdin_account_api_key.
				$item->crowdin_account_api_key = rtrim($basic->decryptString($item->crowdin_account_api_key), "\0");
			}

			if (!empty($item->addcontributors))
			{
				// Convert the addcontributors field to an array.
				$addcontributors = new Registry;
				$addcontributors->loadString($item->addcontributors);
				$item->addcontributors = $addcontributors->toArray();
			}


			if (empty($item->id))
			{
				$id = 0;
			}
			else
			{
				$id = $item->id;
			}
			// set the id and view name to session
			if (($vdm = SessionHelper::get('joomla_component__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'joomla_component__' . $id);
				SessionHelper::set('joomla_component__' . $id, $this->vastDevMod);
				// set a return value if found
				$app = $this->app ?? Factory::getApplication();
				$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
				$return = $input->get('return', null, 'base64');
				SessionHelper::set($this->vastDevMod . '__return', $return);
				// set a GUID value if found
				if (isset($item) && ObjectHelper::check($item) && isset($item->guid)
					&& GuidHelper::valid($item->guid))
				{
					SessionHelper::set($this->vastDevMod . '__guid', $item->guid);
				}
			}
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @param   array    $options   Optional array of options for the form creation.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = [], $loadData = true, $options = ['control' => 'jform'])
	{
		// set load data option
		$options['load_data'] = $loadData;
		// check if xpath was set in options
		$xpath = false;
		if (isset($options['xpath']))
		{
			$xpath = $options['xpath'];
			unset($options['xpath']);
		}
		// check if clear form was set in options
		$clear = false;
		if (isset($options['clear']))
		{
			$clear = $options['clear'];
			unset($options['clear']);
		}

		// Get the form.
		$form = $this->loadForm('com_componentbuilder.joomla_component', 'joomla_component', $options, $clear, $xpath);

		if (empty($form))
		{
			return false;
		}

		$app = Factory::getApplication();

		$jinput = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		// The record being saved decides the permissions, so its id wins over the request.
		if (is_array($data) && isset($data['id']) && (int) $data['id'] > 0)
		{
			$id = (int) $data['id'];
		}
		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		elseif ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0, 'INT');
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0, 'INT');
		}

		$user = Factory::getApplication()->getIdentity();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('joomla_component.edit.state', 'com_componentbuilder.joomla_component.' . (int) $id))
			|| ($id == 0 && !$user->authorise('joomla_component.edit.state', 'com_componentbuilder')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
		// If this is a new item insure the greated by is set.
		if (0 == $id)
		{
			// Set the created_by to this user
			$form->setValue('created_by', null, $user->id);
		}
		// Modify the form based on Edit Creaded By access controls.
		if ($id != 0 && (!$user->authorise('joomla_component.edit.created_by', 'com_componentbuilder.joomla_component.' . (int) $id))
			|| ($id == 0 && !$user->authorise('joomla_component.edit.created_by', 'com_componentbuilder')))
		{
			if ($app->isClient('api'))
			{
				// Exclude protected metadata from API validation and binding.
				$form->removeField('created_by');
			}
			else
			{
				// Retain disabled metadata controls in administrator forms.
				$form->setFieldAttribute('created_by', 'disabled', 'true');
				$form->setFieldAttribute('created_by', 'readonly', 'true');
				$form->setFieldAttribute('created_by', 'filter', 'unset');
			}
		}
		// Modify the form based on Edit Creaded Date access controls.
		if ($id != 0 && (!$user->authorise('joomla_component.edit.created', 'com_componentbuilder.joomla_component.' . (int) $id))
			|| ($id == 0 && !$user->authorise('joomla_component.edit.created', 'com_componentbuilder')))
		{
			if ($app->isClient('api'))
			{
				// Exclude protected metadata from API validation and binding.
				$form->removeField('created');
			}
			else
			{
				// Retain disabled metadata controls in administrator forms.
				$form->setFieldAttribute('created', 'disabled', 'true');
				$form->setFieldAttribute('created', 'filter', 'unset');
			}
		}

		// Omitted PATCH metadata must not be filtered or rebound from storage.
		if ($app->isClient('api') && $jinput->getMethod() === 'PATCH')
		{
			$submittedApiData = $jinput->get('data', json_decode($jinput->json->getRaw(), true), 'array');
			foreach (['created', 'created_by'] as $metadataField)
			{
				if (!is_array($submittedApiData) || !array_key_exists($metadataField, $submittedApiData))
				{
					$form->removeField($metadataField);
				}
			}
		}
		// Only load these values if no id is found
		if (0 == $id)
		{
			// Set redirected view name
			$redirectedView = $jinput->get('ref', null, 'STRING');
			// Set field name (or fall back to view name)
			$redirectedField = $jinput->get('field', $redirectedView, 'STRING');
			// Set redirected view id
			$redirectedId = $jinput->get('refid', 0, 'INT');
			// Set field id (or fall back to redirected view id)
			$redirectedValue = $jinput->get('field_id', $redirectedId, 'INT');
			if (0 != $redirectedValue && $redirectedField)
			{
				// Now set the local-redirected field default value
				$form->setValue($redirectedField, null, $redirectedValue);
			}
			$initDefaults = $jinput->get('init_defaults', null, 'STRING');
			if (!empty($initDefaults))
			{
				// Now check if this json values are valid
				$initDefaults = json_decode(urldecode($initDefaults), true);
				if (is_array($initDefaults))
				{
					foreach ($initDefaults as $field => $value)
					{
						$form->setValue($field, null, $value);
					}
				}
			}
		}
		// Only load these values if no id is found
		if (0 == $id)
		{
			// set company defaults
			$form->setValue('companyname', null, ComponentHelper::getParams('com_componentbuilder')->get('export_company', ''));
			$form->setValue('author', null, ComponentHelper::getParams('com_componentbuilder')->get('export_owner', ''));
			$form->setValue('email', null, ComponentHelper::getParams('com_componentbuilder')->get('export_email', ''));
			$form->setValue('website', null, ComponentHelper::getParams('com_componentbuilder')->get('export_website', ''));
			$form->setValue('copyright', null, ComponentHelper::getParams('com_componentbuilder')->get('export_copyright', 'Copyright (C) 2015. All Rights Reserved'));
			$form->setValue('license', null, ComponentHelper::getParams('com_componentbuilder')->get('export_license', 'GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html'));
		}

		// update all editors to use this components global editor
		$global_editor = ComponentHelper::getParams('com_componentbuilder')->get('editor', 'none');
		// now get all the editor fields
		$editors = $form->getXml()->xpath("//field[@type='editor']");
		// check if we found any
		if (UtilitiesArrayHelper::check($editors))
		{
			foreach ($editors as $editor)
			{
				// get the field names
				$name = (string) $editor['name'];
				// set the field editor value (with none as fallback)
				$form->setFieldAttribute($name, 'editor', $global_editor . '|none');
			}
		}


		// Only load the GUID if new item (or empty)
		if (0 == $id || !($val = $form->getValue('guid')))
		{
			$form->setValue('guid', null, GuidHelper::get());
		}

		if ($app->isClient('api') && $jinput->getMethod() === 'PATCH')
		{
			$this->setState('jcb.api.patch.form', $form);
		}
		return $form;
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
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (empty($record->id) || ($record->published != -2))
		{
			return false;
		}

		// The record has been set. Check the record permissions.
		return $this->getCurrentUser()->authorise('joomla_component.delete', 'com_componentbuilder.joomla_component.' . (int) $record->id);
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = $this->getCurrentUser();
		$recordId = $record->id ?? 0;

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('joomla_component.edit.state', 'com_componentbuilder.joomla_component.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('joomla_component.edit.state', 'com_componentbuilder');
	}

	/**
	 * Method to check if you can edit an existing record.
	 *   We know this is a double access check (Controller already does an allowEdit check)
	 *   But when the item is directly accessed the controller is skipped (2025_).
	 *
	 * @param    array    $data   An array of input data.
	 * @param    string   $key    The name of the key for the primary key.
	 *
	 * @return   boolean  True if allowed to edit the record. Defaults to the permission set in the component.
	 * @since    2.5
	 */
	protected function allowEdit(array $data = [], string $key = 'id'): bool
	{
		// get user object.
		$user = $this->getCurrentUser();
		// get record id.
		$recordId = isset($data[$key]) ? (int) $data[$key] : 0;


		// Access check.
		$access = ($user->authorise('joomla_component.access', 'com_componentbuilder.joomla_component.' . (int) $recordId) && $user->authorise('joomla_component.access', 'com_componentbuilder'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('joomla_component.edit', 'com_componentbuilder.joomla_component.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('joomla_component.edit.own', 'com_componentbuilder.joomla_component.' . (int) $recordId))
				{
					// Now test the owner is the user.
					$ownerId = isset($data['created_by']) ? (int) $data['created_by'] : 0;
					if (empty($ownerId))
					{
						return false;
					}

					// If the owner matches 'me' then allow.
					if ($ownerId == $user->id)
					{
						if ($user->authorise('joomla_component.edit.own', 'com_componentbuilder'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return $user->authorise('joomla_component.edit', $this->option);
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = Factory::getDate();
		$user = $this->getCurrentUser();

		if (isset($table->name))
		{
			$table->name = \htmlspecialchars_decode($table->name, ENT_QUOTES);
		}

		if (isset($table->alias) && empty($table->alias))
		{
			$table->generateAlias();
		}

		if (empty($table->id))
		{
			$table->created = $date->toSql(false, $this->getDatabase());
			// set the user
			if ($table->created_by == 0 || empty($table->created_by))
			{
				$table->created_by = $user->id;
			}
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = $this->getDatabase();
				$query = $db->createQuery()
					->select('MAX(ordering)')
					->from($db->quoteName('#__componentbuilder_joomla_component'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			$table->modified = $date->toSql(false, $this->getDatabase());
			$table->modified_by = $user->id;
		}

		if (!empty($table->id))
		{
			// Increment the items version number.
			$table->version++;
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_componentbuilder.edit.joomla_component.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the per process of the data
		$this->preprocessData('com_componentbuilder.joomla_component', $data);

		return $data;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		$conditionRule = FormHelper::loadRuleType('jcbconditionalrequired');
		$conditionField = '__jcb_conditional_required';
		if (!$conditionRule || $form->getFieldXml($conditionField, $group) !== false)
		{
			return false;
		}
		$conditionRan = false;
		$conditionAttributes = [];
		$conditionCallback = function (array $data) use ($form, $group, &$conditionRan, &$conditionAttributes): bool
		{
			if ($conditionRan)
			{
				return false;
			}
			$conditionRan = true;
			$conditionGroups = [0 => ['matches' => [0 => ['name' => 'add_update_server', 'behavior' => 1, 'options' => [0 => '1'], 'user' => false, 'checkbox' => false, 'array' => false, 'supported' => true]], 'targets' => [0 => 'update_server_target'], 'show' => true, 'toggle' => true], 1 => ['matches' => [0 => ['name' => 'add_changelog_server', 'behavior' => 1, 'options' => [0 => '1'], 'user' => false, 'checkbox' => false, 'array' => false, 'supported' => true]], 'targets' => [0 => 'changelog_server_target'], 'show' => true, 'toggle' => true], 2 => ['matches' => [0 => ['name' => 'buildcomp', 'behavior' => 1, 'options' => [0 => '1'], 'user' => false, 'checkbox' => false, 'array' => false, 'supported' => true]], 'targets' => [0 => 'buildcompsql'], 'show' => true, 'toggle' => true]];
			// The browser's not_required list is informational, never an authority.
			$conditionInput = new Registry($data);
			$conditionData = $group ? (array) $conditionInput->get($group, []) : $data;
			$conditionStored = [];
			$conditionApp = Factory::getApplication();
			$conditionPatch = $conditionApp->isClient('api') && $conditionApp->getInput()->getMethod() === 'PATCH';
			$recordId = (int) ($data['id'] ?? $this->getState($this->getName() . '.id', 0));
			if ($recordId > 0)
			{
				$stored = $this->getItem($recordId);
				if ($stored === false || $stored === null)
				{
					return false;
				}
				$conditionStored = new Registry($stored);
				$conditionStored = $group ? (array) $conditionStored->get($group, []) : $conditionStored->toArray();
			}
			$conditionPresent = static function ($value): bool
			{
				return $value !== null && $value !== '' && $value !== [];
			};
			$conditionEquals = static function ($value, $option): bool
			{
				// Selection values arrive as DOM strings; numeric/boolean options use JS equality.
				if (is_numeric($option) || $option === 'true' || $option === 'false')
				{
					if ($value === null)
					{
						return false;
					}
					$number = $option === 'true' ? 1 : ($option === 'false' ? 0 : (float) $option);
					if (is_bool($value) || (is_string($value) && trim($value) === ''))
					{
						return (float) $value === (float) $number;
					}
					return is_numeric($value) && (float) $value === (float) $number;
				}
				return is_scalar($value) && (string) $value === (string) $option;
			};
			$conditionMatch = static function ($value, array $rule) use ($conditionPresent, $conditionEquals): bool
			{
				$behavior = $rule['behavior'];
				$options = $rule['options'];
				if ($behavior >= 1 && $behavior <= 3)
				{
					if ($options !== [])
					{
						foreach ($options as $option)
						{
							$equal = $conditionEquals($value, $option);
							// Preserve the browser's OR across options, including Is Not.
							if ($behavior === 2 ? !$equal : $equal)
							{
								return true;
							}
						}
						return false;
					}
					$present = $conditionPresent($value);
					if ($behavior === 2)
					{
						return !$present;
					}
					return $present && !($behavior === 3 && $rule['user'] && $conditionEquals($value, '0'));
				}
				if ($behavior === 4 || $behavior === 5)
				{
					return $behavior === 4 ? $conditionPresent($value) : !$conditionPresent($value);
				}
				if (!is_scalar($value) && $value !== null)
				{
					return false;
				}
				$value = (string) $value;
				if ($behavior >= 6 && $behavior <= 9)
				{
					$keywords = $options['keywords'] ?? [];
					if ($keywords === [])
					{
						return $value === 'error';
					}
					$all = $behavior === 6 || $behavior === 8;
					if ($behavior === 8 || $behavior === 9)
					{
						$value = StringHelper::strtolower($value);
					}
					foreach ($keywords as $keyword)
					{
						$found = strpos($value, $keyword) !== false;
						if ($all ? !$found : $found)
						{
							return !$all;
						}
					}
					return $all;
				}
				// JavaScript length counts UTF-16 code units, including surrogate pairs.
				$length = StringHelper::strlen($value) + preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $value);
				$expected = (int) (($options['length'] ?? 0) ?: 5);
				switch ($behavior)
				{
					case 10:
						return $length >= $expected;
					case 11:
						return $length <= $expected;
					case 12:
						return $length == $expected;
				}
				return false;
			};
			$conditionalRequired = [];
			foreach ($conditionGroups as $conditionGroup)
			{
				foreach ($conditionGroup['targets'] as $target)
				{
					$conditionalRequired[$target] = true;
				}
			}
			foreach ($conditionGroups as $conditionGroup)
			{
				$matched = true;
				foreach ($conditionGroup['matches'] as $rule)
				{
					// Unsupported definitions cannot relax a required field.
					if (!$rule['supported'])
					{
						continue 2;
					}
					// ACL-denied selectors cannot change applicability through discarded input.
					$disabled = strtolower((string) $form->getFieldAttribute($rule['name'], 'disabled', '', $group));
					$filter = strtolower((string) $form->getFieldAttribute($rule['name'], 'filter', '', $group));
					$available = $form->getFieldAttribute($rule['name'], 'name', null, $group) !== null;
					$protected = !$available || in_array($disabled, ['true', '1', 'disabled'], true) || $filter === 'unset';
					$values = $protected ? $conditionStored : $conditionData;
					if (array_key_exists($rule['name'], $values))
					{
						$value = $values[$rule['name']];
					}
					elseif (!$protected && $conditionPatch && array_key_exists($rule['name'], $conditionStored))
					{
						$value = $conditionStored[$rule['name']];
					}
					elseif (!$protected && $rule['checkbox'])
					{
						// Native unchecked checkboxes omit their key on ordinary form submissions.
						$value = false;
					}
					else
					{
						$value = $form->getFieldAttribute($rule['name'], 'default', null, $group);
					}
					if ($rule['checkbox'])
					{
						$value = (bool) $value;
					}
					if ($rule['array'])
					{
						$values = $conditionPresent($value) ? (array) $value : [];
						$oneMatches = false;
						foreach ($values as $entry)
						{
							if ($conditionMatch($entry, $rule))
							{
								$oneMatches = true;
								break;
							}
						}
					}
					else
					{
						$oneMatches = $conditionMatch($value, $rule);
					}
					$matched = $matched && $oneMatches;
				}
				if ($matched || $conditionGroup['toggle'])
				{
					$required = $matched ? $conditionGroup['show'] : !$conditionGroup['show'];
					foreach ($conditionGroup['targets'] as $target)
					{
						$conditionalRequired[$target] = $required;
					}
				}
			}
			foreach ($conditionalRequired as $field => $required)
			{
				$conditionElement = $form->getFieldXml($field, $group);
				if ($conditionElement !== false)
				{
					// Snapshot after native validation plugins have finished changing the form.
					$conditionAttributes[] = [$conditionElement, isset($conditionElement['required']) ? (string) $conditionElement['required'] : null];
					$form->setFieldAttribute($field, 'required', $required ? 'true' : 'false', $group);
				}
			}
			// Inactive fields keep their values; ordinary filtering and validation still apply.
			return true;
		};
		if (!$conditionRule::attach($form, $conditionCallback, $group, $conditionField))
		{
			return false;
		}
		$conditionNode = null;
		try
		{
			$element = new \SimpleXMLElement('<field name="__jcb_conditional_required" type="hidden" filter="unset" validate="jcbconditionalrequired" />');
			if (!$form->setField($element, $group))
			{
				return false;
			}
			$element = $form->getFieldXml($conditionField, $group);
			if ($element === false)
			{
				return false;
			}
			$conditionNode = dom_import_simplexml($element);
			$container = $conditionNode->parentNode;
			while ($container->nodeName !== 'form' && $container->nodeName !== 'fields')
			{
				$container = $container->parentNode;
			}
			$container->insertBefore($conditionNode, $container->firstChild);
			// The internal rule needs no submitted value and must never reach persistence.
			$conditionPath = $group ? $group . '.' . $conditionField : $conditionField;
			$conditionInput = new Registry($data);
			$conditionInput->remove($conditionPath);
			$result = parent::validate($form, $conditionInput->toArray(), $group);
			if (!$conditionRan || $result === false)
			{
				return false;
			}
			$conditionOutput = new Registry($result);
			$conditionOutput->remove($conditionPath);
			return $conditionOutput->toArray();
		}
		finally
		{
			if ($conditionNode !== null && $conditionNode->parentNode !== null)
			{
				$conditionNode->parentNode->removeChild($conditionNode);
			}
			foreach ($conditionAttributes as [$conditionElement, $conditionRequired])
			{
				if ($conditionRequired === null)
				{
					unset($conditionElement['required']);
				}
				else
				{
					$conditionElement['required'] = $conditionRequired;
				}
			}
			$form->removeField($conditionField, $group);
			$conditionRule::detach($form);
		}
	}

	/**
	 * Method to get the unique fields of this table.
	 *
	 * @return  mixed  An array of field names, boolean false if none is set.
	 *
	 * @since   3.0
	 */
	protected function getUniqueFields()
	{
		return array('guid');
	}

	/**
	 * Database column settings used when generating unique values.
	 *
	 * @return  array  Column types, character limits and nullability.
	 * @since   6.2.1
	 */
	protected function getUniqueFieldSettings()
	{
		return [
			'system_name' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'name_code' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'short_description' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'companyname' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
			'buildcompsql' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'translation_tool' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'jcb_powers_path' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'sales_server' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'add_update_server' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_sql' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_postflight_install' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'mvc_versiondate' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'remove_line_breaks' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => true],
			'add_placeholders' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'debug_linenr' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_preflight_install' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'description' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_php_method_uninstall' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'author' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'assets_table_fix' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'email' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'website' => ['type' => 'CHAR', 'length' => 255, 'nullable' => false],
			'add_git_folder_path' => ['type' => 'CHAR', 'length' => 1, 'nullable' => true],
			'license' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'add_css_admin' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'crowdin_username' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'dashboard_type' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'bom' => ['type' => 'CHAR', 'length' => 64, 'nullable' => false],
			'component_version' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
			'add_css_site' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'image' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'dashboard' => ['type' => 'VARCHAR', 'length' => 64, 'nullable' => true],
			'copyright' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'add_php_preflight_update' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'preferred_joomla_version' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'add_php_postflight_update' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_powers' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_method_install' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_sql_uninstall' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'readme' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'update_server_target' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'update_server' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'git_folder_path' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'changelog_server_url' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'crowdin_project_identifier' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'add_namespace_prefix' => ['type' => 'CHAR', 'length' => 1, 'nullable' => false],
			'created' => ['type' => 'DATETIME', 'length' => null, 'nullable' => true],
			'namespace_prefix' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'javascript' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'css_admin' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_menu_prefix' => ['type' => 'CHAR', 'length' => 1, 'nullable' => false],
			'css_site' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'menu_prefix' => ['type' => 'VARCHAR', 'length' => 100, 'nullable' => true],
			'php_preflight_install' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'toignore' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_preflight_update' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_postflight_install' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_postflight_update' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'addcontributors' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_method_uninstall' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'emptycontributors' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_method_install' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'number' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'sql' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'sql_uninstall' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'addreadme' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'update_server_url' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'creatuserhelper' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_sales_server' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'adduikit' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_backup_folder_path' => ['type' => 'CHAR', 'length' => 1, 'nullable' => true],
			'addfootable' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'backup_folder_path' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'add_email_helper' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_helper_both' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_jcb_powers_path' => ['type' => 'CHAR', 'length' => 1, 'nullable' => false],
			'php_helper_both' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_changelog_server' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_helper_admin' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'changelog_server_target' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_helper_admin' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_admin_event' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'changelog_server' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'php_admin_event' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_php_helper_site' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'crowdin_project_api_key' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_helper_site' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'crowdin_account_api_key' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_site_event' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'buildcomp' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_site_event' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'guid' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'add_javascript' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'modified' => ['type' => 'DATETIME', 'length' => null, 'nullable' => true],
			'name' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
		];
	}

	/**
	 * Generate a valid GUID candidate for a copied record.
	 *
	 * @return  string  A new GUID candidate.
	 * @since   6.2.1
	 */
	protected function generateUniqueGuid()
	{
		return (string) GuidHelper::get();
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (!parent::delete($pks))
		{
			return false;
		}

		// linked tables to update
		$_tables_array = [
			'component_admin_views' => 'joomla_component',
			'component_site_views' => 'joomla_component',
			'component_custom_admin_views' => 'joomla_component',
			'component_updates' => 'joomla_component',
			'component_mysql_tweaks' => 'joomla_component',
			'component_custom_admin_menus' => 'joomla_component',
			'component_config' => 'joomla_component',
			'component_dashboard' => 'joomla_component',
			'component_files_folders' => 'joomla_component',
			'component_placeholders' => 'joomla_component',
			'custom_code' => 'component',
			'component_router' => 'joomla_component'
		];

		// Update all linked tables
		if (!empty($_tables_array) && UtilitiesArrayHelper::check($pks))
		{
			// Ensure field key
			$_field_key ??= 'guid';

			// Set active component context
			Helper::setOption('com_componentbuilder');

			// Load GUIDs once
			$_guids = DataFactory::_('Load')->values(
				['a.' . $_field_key], // selection
				['a' => 'joomla_component'], // source table
				['a.id' => ['value' => (array) $pks, 'operator' => 'IN']] // where
			);

			// Abort early if nothing returned
			if (empty($_guids))
			{
				return true;
			}

			// Normalize & deduplicate GUIDs
			$_guids = array_values(array_unique((array) $_guids));

			foreach ($_tables_array as $_delete_table => $_field_name)
			{
				// Skip invalid configuration
				if (empty($_delete_table) || empty($_field_name))
				{
					continue;
				}

				// Load linked item IDs
				$_pks = DataFactory::_('Load')->values(
					['a.id' => 'id'], // selection
					['a' => $_delete_table], // table
					['a.' . $_field_name => ['value' => $_guids, 'operator' => 'IN']] // where
				);

				// Skip empty or broken relations
				if (empty($_pks))
				{
					continue;
				}

				// Normalize keys
				$_pks = array_values(array_unique((array) $_pks));

				// Load model safely (it throws; it never returns null)
				try
				{
					$_Model = Helper::getModel($_delete_table);
				}
				catch (\Throwable $e)
				{
					// Intentionally ignored (safe fail)
					continue;
				}

				// Move to trash first
				$_Model->publish($_pks, -2);

				// Delete records
				$_Model->delete($_pks);
			}
		}

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		if (!parent::publish($pks, $value))
		{
			return false;
		}

		// linked tables to update
		$_tables_array = [
			'component_admin_views' => 'joomla_component',
			'component_site_views' => 'joomla_component',
			'component_custom_admin_views' => 'joomla_component',
			'component_updates' => 'joomla_component',
			'component_mysql_tweaks' => 'joomla_component',
			'component_custom_admin_menus' => 'joomla_component',
			'component_config' => 'joomla_component',
			'component_dashboard' => 'joomla_component',
			'component_files_folders' => 'joomla_component',
			'component_placeholders' => 'joomla_component',
			'custom_code' => 'component',
			'component_router' => 'joomla_component'
		];

		// Update all linked tables
		if (!empty($_tables_array) && UtilitiesArrayHelper::check($pks))
		{
			// Ensure field key
			$_field_key ??= 'guid';

			// Set active component context
			Helper::setOption('com_componentbuilder');

			// Load GUIDs once
			$_guids = DataFactory::_('Load')->values(
				['a.' . $_field_key], // selection
				['a' => 'joomla_component'], // source table
				['a.id' => ['value' => (array) $pks, 'operator' => 'IN']] // where
			);

			// Abort early if nothing returned
			if (empty($_guids))
			{
				return true;
			}

			// Normalize & deduplicate GUIDs
			$_guids = array_values(array_unique((array) $_guids));

			foreach ($_tables_array as $_update_table => $_field_name)
			{
				// Skip invalid config
				if (empty($_update_table) || empty($_field_name))
				{
					continue;
				}

				// Load linked IDs
				$_pks = DataFactory::_('Load')->values(
					['a.id' => 'id'], // selection
					['a' => $_update_table], // source table
					['a.' . $_field_name => ['value' => $_guids, 'operator' => 'IN']] // where
				);

				// Skip empty or broken relations
				if (empty($_pks))
				{
					continue;
				}

				// Normalize keys
				$_pks = array_values(array_unique((array) $_pks));

				// Load model safely
				try {
					$_Model = Helper::getModel($_update_table);
				} catch (\Throwable $e) {
					// Intentionally ignored
					continue;
				}

				// Apply publish state
				$_Model->publish($_pks, $value);
			}
		}

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 * @since   1.6
	 */
	public function save($data)
	{
		$input    = Factory::getApplication()->getInput();
		$filter   = InputFilter::getInstance();

		// set the metadata to the Item Data
		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');

			$metadata = new Registry;
			$metadata->loadArray($data['metadata']);
			$data['metadata'] = (string) $metadata;
		}

		// The record keys, as every line below expects them: the primary key as an
		// integer that is never taken from the request (null from the API on create).
		$data['id'] = (int) ($data['id'] ?? 0);

		// The guid is the server's: an existing record keeps the guid it was stored
		// with, and the API never takes one from the request.
		if ($data['id'] > 0)
		{
			$data['guid'] = (string) GetHelper::var('joomla_component', $data['id'], 'id', 'guid', '=', 'componentbuilder');
		}
		elseif (Factory::getApplication()->isClient('api'))
		{
			$data['guid'] = '';
		}
		else
		{
			$data['guid'] = (string) ($data['guid'] ?? '');
		}

		// Set the guid while it is empty, not valid, or not unique in this table.
		while (!GuidHelper::valid($data['guid'], 'joomla_component', $data['id'], 'componentbuilder'))
		{
			$data['guid'] = (string) GuidHelper::get();
		}

		// Preserve omitted API PATCH values in their original storage representation.
		$jcbPatchStored = [];
		$jcbPatchInput = [];
		$jcbPatchId = $data['id'];

		if ($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api') && $jcbPatchId > 0)
		{
			$jcbPatchSubmitted = $input->get('data', json_decode($input->json->getRaw(), true), 'array');
			$jcbPatchSubmitted = is_array($jcbPatchSubmitted) ? $jcbPatchSubmitted : [];
			// Joomla's API inserts an empty tags array after validation. It must not
			// clear omitted relationships or bypass the form's field permissions.
			$jcbPatchForm = $this->getState('jcb.api.patch.form');
			if (!array_key_exists('tags', $jcbPatchSubmitted)
				|| ($jcbPatchForm !== null && (!$jcbPatchForm->getField('tags')
					|| strtolower((string) $jcbPatchForm->getFieldAttribute('tags', 'filter', '')) === 'unset'
					|| in_array(strtolower((string) $jcbPatchForm->getFieldAttribute('tags', 'disabled', '')), ['true', '1'], true))))
			{
				unset($data['tags']);
			}

			$jcbPatchTable = $this->getTable();

			if (!$jcbPatchTable->load($jcbPatchId))
			{
				throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_RECORD_LOAD'), 404);
			}

			foreach ($jcbPatchTable->getFields() as $jcbPatchField)
			{
				$jcbPatchName = $jcbPatchField->Field;

				if ($jcbPatchName !== $jcbPatchTable->getKeyName() && $jcbPatchName !== 'guid'
					&& !array_key_exists($jcbPatchName, $jcbPatchSubmitted)
					&& array_key_exists($jcbPatchName, $data))
				{
					$jcbPatchStored[$jcbPatchName] = $jcbPatchTable->{$jcbPatchName};
					$jcbPatchInput[$jcbPatchName] = serialize($data[$jcbPatchName]);
				}
			}
		}

		// if system name is empty create from name
		if (empty($data['system_name']) || !UtilitiesStringHelper::check($data['system_name']))
		{
			$data['system_name'] = $data['name'];
		}

		// make sure that the component code name is safe.
		if (!empty($data['system_name']) && UtilitiesStringHelper::check($data['system_name']))
		{
			$data['name_code'] = ComponentCodeNameHelper::safe($data['name_code']);
		}

		// Set the GUID if empty or not valid
		if (empty($data['guid']) && $data['id'] > 0)
		{
			// get the existing one
			$data['guid'] = (string) GetHelper::var('joomla_component', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "joomla_component", $data['id']))
		{
			// must always be set
			$data['guid'] = (string) GuidHelper::get();
		}

		// A copy or a custom derivation must follow the normal storage transforms.
		if ((int) $data['id'] !== $jcbPatchId)
		{
			$jcbPatchStored = [];
		}
		else
		{
			foreach ($jcbPatchStored as $jcbPatchName => $jcbPatchValue)
			{
				if (!array_key_exists($jcbPatchName, $data)
					|| serialize($data[$jcbPatchName]) !== $jcbPatchInput[$jcbPatchName])
				{
					unset($jcbPatchStored[$jcbPatchName]);
				}
			}
		}

		if (array_key_exists('addcontributors', $jcbPatchStored)
			&& (!array_key_exists('addcontributors', $data) || serialize($data['addcontributors']) !== $jcbPatchInput['addcontributors']))
		{
			unset($jcbPatchStored['addcontributors']);
		}

		// Set the addcontributors items to data.
		if (isset($data['addcontributors']) && is_array($data['addcontributors']) && !array_key_exists('addcontributors', $jcbPatchStored))
		{
			$addcontributors = new Registry;
			$addcontributors->loadArray($data['addcontributors']);
			$data['addcontributors'] = (string) $addcontributors;
		}
		elseif (!isset($data['addcontributors']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty addcontributors to data
			$data['addcontributors'] = '';
		}

		if (array_key_exists('buildcompsql', $jcbPatchStored)
			&& (!array_key_exists('buildcompsql', $data) || serialize($data['buildcompsql']) !== $jcbPatchInput['buildcompsql']))
		{
			unset($jcbPatchStored['buildcompsql']);
		}

		// Set the buildcompsql string to base64 string.
		if (isset($data['buildcompsql']) && !array_key_exists('buildcompsql', $jcbPatchStored))
		{
			$data['buildcompsql'] = base64_encode($data['buildcompsql']);
		}

		if (array_key_exists('readme', $jcbPatchStored)
			&& (!array_key_exists('readme', $data) || serialize($data['readme']) !== $jcbPatchInput['readme']))
		{
			unset($jcbPatchStored['readme']);
		}

		// Set the readme string to base64 string.
		if (isset($data['readme']) && !array_key_exists('readme', $jcbPatchStored))
		{
			$data['readme'] = base64_encode($data['readme']);
		}

		if (array_key_exists('javascript', $jcbPatchStored)
			&& (!array_key_exists('javascript', $data) || serialize($data['javascript']) !== $jcbPatchInput['javascript']))
		{
			unset($jcbPatchStored['javascript']);
		}

		// Set the javascript string to base64 string.
		if (isset($data['javascript']) && !array_key_exists('javascript', $jcbPatchStored))
		{
			$data['javascript'] = base64_encode($data['javascript']);
		}

		if (array_key_exists('css_admin', $jcbPatchStored)
			&& (!array_key_exists('css_admin', $data) || serialize($data['css_admin']) !== $jcbPatchInput['css_admin']))
		{
			unset($jcbPatchStored['css_admin']);
		}

		// Set the css_admin string to base64 string.
		if (isset($data['css_admin']) && !array_key_exists('css_admin', $jcbPatchStored))
		{
			$data['css_admin'] = base64_encode($data['css_admin']);
		}

		if (array_key_exists('css_site', $jcbPatchStored)
			&& (!array_key_exists('css_site', $data) || serialize($data['css_site']) !== $jcbPatchInput['css_site']))
		{
			unset($jcbPatchStored['css_site']);
		}

		// Set the css_site string to base64 string.
		if (isset($data['css_site']) && !array_key_exists('css_site', $jcbPatchStored))
		{
			$data['css_site'] = base64_encode($data['css_site']);
		}

		if (array_key_exists('php_preflight_install', $jcbPatchStored)
			&& (!array_key_exists('php_preflight_install', $data) || serialize($data['php_preflight_install']) !== $jcbPatchInput['php_preflight_install']))
		{
			unset($jcbPatchStored['php_preflight_install']);
		}

		// Set the php_preflight_install string to base64 string.
		if (isset($data['php_preflight_install']) && !array_key_exists('php_preflight_install', $jcbPatchStored))
		{
			$data['php_preflight_install'] = base64_encode($data['php_preflight_install']);
		}

		if (array_key_exists('php_preflight_update', $jcbPatchStored)
			&& (!array_key_exists('php_preflight_update', $data) || serialize($data['php_preflight_update']) !== $jcbPatchInput['php_preflight_update']))
		{
			unset($jcbPatchStored['php_preflight_update']);
		}

		// Set the php_preflight_update string to base64 string.
		if (isset($data['php_preflight_update']) && !array_key_exists('php_preflight_update', $jcbPatchStored))
		{
			$data['php_preflight_update'] = base64_encode($data['php_preflight_update']);
		}

		if (array_key_exists('php_postflight_install', $jcbPatchStored)
			&& (!array_key_exists('php_postflight_install', $data) || serialize($data['php_postflight_install']) !== $jcbPatchInput['php_postflight_install']))
		{
			unset($jcbPatchStored['php_postflight_install']);
		}

		// Set the php_postflight_install string to base64 string.
		if (isset($data['php_postflight_install']) && !array_key_exists('php_postflight_install', $jcbPatchStored))
		{
			$data['php_postflight_install'] = base64_encode($data['php_postflight_install']);
		}

		if (array_key_exists('php_postflight_update', $jcbPatchStored)
			&& (!array_key_exists('php_postflight_update', $data) || serialize($data['php_postflight_update']) !== $jcbPatchInput['php_postflight_update']))
		{
			unset($jcbPatchStored['php_postflight_update']);
		}

		// Set the php_postflight_update string to base64 string.
		if (isset($data['php_postflight_update']) && !array_key_exists('php_postflight_update', $jcbPatchStored))
		{
			$data['php_postflight_update'] = base64_encode($data['php_postflight_update']);
		}

		if (array_key_exists('php_method_uninstall', $jcbPatchStored)
			&& (!array_key_exists('php_method_uninstall', $data) || serialize($data['php_method_uninstall']) !== $jcbPatchInput['php_method_uninstall']))
		{
			unset($jcbPatchStored['php_method_uninstall']);
		}

		// Set the php_method_uninstall string to base64 string.
		if (isset($data['php_method_uninstall']) && !array_key_exists('php_method_uninstall', $jcbPatchStored))
		{
			$data['php_method_uninstall'] = base64_encode($data['php_method_uninstall']);
		}

		if (array_key_exists('php_method_install', $jcbPatchStored)
			&& (!array_key_exists('php_method_install', $data) || serialize($data['php_method_install']) !== $jcbPatchInput['php_method_install']))
		{
			unset($jcbPatchStored['php_method_install']);
		}

		// Set the php_method_install string to base64 string.
		if (isset($data['php_method_install']) && !array_key_exists('php_method_install', $jcbPatchStored))
		{
			$data['php_method_install'] = base64_encode($data['php_method_install']);
		}

		if (array_key_exists('sql', $jcbPatchStored)
			&& (!array_key_exists('sql', $data) || serialize($data['sql']) !== $jcbPatchInput['sql']))
		{
			unset($jcbPatchStored['sql']);
		}

		// Set the sql string to base64 string.
		if (isset($data['sql']) && !array_key_exists('sql', $jcbPatchStored))
		{
			$data['sql'] = base64_encode($data['sql']);
		}

		if (array_key_exists('sql_uninstall', $jcbPatchStored)
			&& (!array_key_exists('sql_uninstall', $data) || serialize($data['sql_uninstall']) !== $jcbPatchInput['sql_uninstall']))
		{
			unset($jcbPatchStored['sql_uninstall']);
		}

		// Set the sql_uninstall string to base64 string.
		if (isset($data['sql_uninstall']) && !array_key_exists('sql_uninstall', $jcbPatchStored))
		{
			$data['sql_uninstall'] = base64_encode($data['sql_uninstall']);
		}

		if (array_key_exists('php_helper_both', $jcbPatchStored)
			&& (!array_key_exists('php_helper_both', $data) || serialize($data['php_helper_both']) !== $jcbPatchInput['php_helper_both']))
		{
			unset($jcbPatchStored['php_helper_both']);
		}

		// Set the php_helper_both string to base64 string.
		if (isset($data['php_helper_both']) && !array_key_exists('php_helper_both', $jcbPatchStored))
		{
			$data['php_helper_both'] = base64_encode($data['php_helper_both']);
		}

		if (array_key_exists('php_helper_admin', $jcbPatchStored)
			&& (!array_key_exists('php_helper_admin', $data) || serialize($data['php_helper_admin']) !== $jcbPatchInput['php_helper_admin']))
		{
			unset($jcbPatchStored['php_helper_admin']);
		}

		// Set the php_helper_admin string to base64 string.
		if (isset($data['php_helper_admin']) && !array_key_exists('php_helper_admin', $jcbPatchStored))
		{
			$data['php_helper_admin'] = base64_encode($data['php_helper_admin']);
		}

		if (array_key_exists('php_admin_event', $jcbPatchStored)
			&& (!array_key_exists('php_admin_event', $data) || serialize($data['php_admin_event']) !== $jcbPatchInput['php_admin_event']))
		{
			unset($jcbPatchStored['php_admin_event']);
		}

		// Set the php_admin_event string to base64 string.
		if (isset($data['php_admin_event']) && !array_key_exists('php_admin_event', $jcbPatchStored))
		{
			$data['php_admin_event'] = base64_encode($data['php_admin_event']);
		}

		if (array_key_exists('php_helper_site', $jcbPatchStored)
			&& (!array_key_exists('php_helper_site', $data) || serialize($data['php_helper_site']) !== $jcbPatchInput['php_helper_site']))
		{
			unset($jcbPatchStored['php_helper_site']);
		}

		// Set the php_helper_site string to base64 string.
		if (isset($data['php_helper_site']) && !array_key_exists('php_helper_site', $jcbPatchStored))
		{
			$data['php_helper_site'] = base64_encode($data['php_helper_site']);
		}

		if (array_key_exists('php_site_event', $jcbPatchStored)
			&& (!array_key_exists('php_site_event', $data) || serialize($data['php_site_event']) !== $jcbPatchInput['php_site_event']))
		{
			unset($jcbPatchStored['php_site_event']);
		}

		// Set the php_site_event string to base64 string.
		if (isset($data['php_site_event']) && !array_key_exists('php_site_event', $jcbPatchStored))
		{
			$data['php_site_event'] = base64_encode($data['php_site_event']);
		}

		// Get the basic encryption key.
		$basickey = ComponentbuilderHelper::getCryptKey('basic');
		// Get the encryption object
		$basic = new AES($basickey);

		if (array_key_exists('crowdin_username', $jcbPatchStored)
			&& (!array_key_exists('crowdin_username', $data) || serialize($data['crowdin_username']) !== $jcbPatchInput['crowdin_username']))
		{
			unset($jcbPatchStored['crowdin_username']);
		}

		// Encrypt data crowdin_username.
		if (isset($data['crowdin_username']) && $basickey && !array_key_exists('crowdin_username', $jcbPatchStored))
		{
			$data['crowdin_username'] = $basic->encryptString($data['crowdin_username']);
		}

		if (array_key_exists('crowdin_project_api_key', $jcbPatchStored)
			&& (!array_key_exists('crowdin_project_api_key', $data) || serialize($data['crowdin_project_api_key']) !== $jcbPatchInput['crowdin_project_api_key']))
		{
			unset($jcbPatchStored['crowdin_project_api_key']);
		}

		// Encrypt data crowdin_project_api_key.
		if (isset($data['crowdin_project_api_key']) && $basickey && !array_key_exists('crowdin_project_api_key', $jcbPatchStored))
		{
			$data['crowdin_project_api_key'] = $basic->encryptString($data['crowdin_project_api_key']);
		}

		if (array_key_exists('crowdin_account_api_key', $jcbPatchStored)
			&& (!array_key_exists('crowdin_account_api_key', $data) || serialize($data['crowdin_account_api_key']) !== $jcbPatchInput['crowdin_account_api_key']))
		{
			unset($jcbPatchStored['crowdin_account_api_key']);
		}

		// Encrypt data crowdin_account_api_key.
		if (isset($data['crowdin_account_api_key']) && $basickey && !array_key_exists('crowdin_account_api_key', $jcbPatchStored))
		{
			$data['crowdin_account_api_key'] = $basic->encryptString($data['crowdin_account_api_key']);
		}

		// Restore unchanged omitted columns without a lossy decode/encode round trip.
		foreach ($jcbPatchStored as $jcbPatchName => $jcbPatchValue)
		{
			if (array_key_exists($jcbPatchName, $data)
				&& serialize($data[$jcbPatchName]) === $jcbPatchInput[$jcbPatchName])
			{
				$data[$jcbPatchName] = $jcbPatchValue;
			}
		}

		// we check if component should be build from sql file
		if (isset($data['buildcomp']) && 1 == $data['buildcomp'])
		{
			$extruder__ = new Extrusion($data);
		}

		// Set the Params Items to data
		if (isset($data['params']) && is_array($data['params']))
		{
			$params = new Registry;
			$params->loadArray($data['params']);
			$data['params'] = (string) $params;
		}

		// Alter the unique field for save as copy
		if ($input->get('task') === 'save2copy')
		{
			// Automatic handling of other unique fields
			$uniqueFields = $this->getUniqueFields();
			if (UtilitiesArrayHelper::check($uniqueFields))
			{
				foreach ($uniqueFields as $uniqueField)
				{
					if (!array_key_exists($uniqueField, $data))
					{
						continue;
					}
					$uniqueValue = $this->generateUnique($uniqueField, $data[$uniqueField]);
					if ($uniqueValue === false)
					{
						return false;
					}
					$data[$uniqueField] = $uniqueValue;
				}
			}
		}

		if (parent::save($data))
		{
			return true;
		}
		return false;
	}

	/**
	 * Generate a storable unique copy value without changing numeric identifiers.
	 *
	 * @param   string  $field  The database column name.
	 * @param   mixed   $value  The requested copy value.
	 *
	 * @return  mixed  An available value, or false after setting a model error.
	 * @since   3.0
	 */
	protected function generateUnique($field, $value)
	{
		$settings = $this->getUniqueFieldSettings();
		$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_ERROR';

		if (!isset($settings[$field]) || (!is_scalar($value) && $value !== null) || $value === false)
		{
			$this->setError(Text::sprintf($error, $field));
			return false;
		}

		$column = $settings[$field];

		// Table::load compares NULL as a quoted value, not with SQL IS NULL.
		if ($value === null)
		{
			if ($column['nullable'])
			{
				return null;
			}

			$this->setError(Text::sprintf($error, $field));
			return false;
		}

		$isText = in_array($column['type'], ['CHAR', 'VARCHAR'], true);
		$matched = [];

		try
		{
			$table = $this->getTable();

			// At most 100 collision queries, including the original requested value.
			for ($attempt = 0; $attempt < 100; $attempt++)
			{
				if ($isText && $column['length'] !== null
					&& StringHelper::strlen((string) $value) > $column['length'])
				{
					$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_LENGTH';
					break;
				}

				$loaded = $table->load([$field => $value]);

				// A failed query/bind is never evidence that a value is available.
				if ($table->getError())
				{
					$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_ERROR';
					break;
				}

				if (!$loaded)
				{
					return $value;
				}

				$key = $table->getKeyName();
				$primaryKey = (string) $table->$key;

				// Detect coercion/collation matches that suffixing cannot escape.
				if (isset($matched[$primaryKey]))
				{
					$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_CONFLICT';
					break;
				}

				$matched[$primaryKey] = true;

				if ($field === 'guid')
				{
					$value = $this->generateUniqueGuid();
				}
				elseif ($isText && $column['length'] !== null)
				{
					$value = StringHelper::increment((string) $value);
				}
				else
				{
					$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_CONFLICT';
					break;
				}

				$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_LIMIT';
			}
		}
		catch (\Throwable $exception)
		{
			$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_ERROR';
		}

		$this->setError(Text::sprintf($error, $field));
		return false;
	}

	/**
	 * Method to change the title
	 *
	 * @param   string   $title   The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 */
	protected function _generateNewTitle($title)
	{

		// Alter the title
		$table = $this->getTable();

		while ($table->load(['title' => $title]))
		{
			$title = StringHelper::increment($title);
		}

		return $title;
	}
}
