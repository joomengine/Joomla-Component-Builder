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
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\Component\Helper;
use VDM\Joomla\Data\Factory as DataFactory;
use VDM\Joomla\Utilities\GetHelper;
use VDM\Joomla\Utilities\String\ClassfunctionHelper;
use Joomla\CMS\Access\Exception\NotAllowed;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Joomla_plugin Admin Model
 *
 * @since  1.6
 */
class Joomla_pluginModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The tab layout fields array.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected $tabLayoutFields = array(
		'code' => array(
			'left' => array(
				'name',
				'description',
				'class_extends',
				'joomla_plugin_group',
				'add_head',
				'note_plugin'
			),
			'right' => array(
				'property_selection',
				'method_selection'
			),
			'fullwidth' => array(
				'head',
				'main_class_code',
				'note_linked_to_notice',
				'not_required'
			),
			'above' => array(
				'system_name',
				'plugin_version'
			)
		),
		'mysql' => array(
			'fullwidth' => array(
				'add_sql',
				'sql',
				'add_sql_uninstall',
				'sql_uninstall'
			)
		),
		'script_file' => array(
			'fullwidth' => array(
				'add_php_script_construct',
				'php_script_construct',
				'add_php_preflight_install',
				'php_preflight_install',
				'add_php_preflight_update',
				'php_preflight_update',
				'add_php_preflight_uninstall',
				'php_preflight_uninstall',
				'add_php_postflight_install',
				'php_postflight_install',
				'add_php_postflight_update',
				'php_postflight_update',
				'add_php_method_uninstall',
				'php_method_uninstall'
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
				'sales_server'
			)
		),
		'readme' => array(
			'left' => array(
				'addreadme',
				'readme'
			)
		),
		'forms_fields' => array(
			'fullwidth' => array(
				'fields'
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
		'administrator/components/com_componentbuilder/assets/css/admin.css',
		'administrator/components/com_componentbuilder/assets/css/joomla_plugin.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'administrator/components/com_componentbuilder/assets/js/admin.js',
		'media/com_componentbuilder/js/joomla_plugin.js'
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
	public $typeAlias = 'com_componentbuilder.joomla_plugin';

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
	public function getTable($type = 'joomla_plugin', $prefix = 'Administrator', $config = [])
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
			if (($vdm = SessionHelper::get('joomla_plugin__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'joomla_plugin__' . $id);
				SessionHelper::set('joomla_plugin__' . $id, $this->vastDevMod);
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
					if (!($user->authorise('joomla_plugin.access', 'com_componentbuilder.joomla_plugin.' . $item->id) && $user->authorise('joomla_plugin.access', 'com_componentbuilder')) || (!$user->authorise('core.options', 'com_componentbuilder') && !in_array((int) $item->access, $user->getAuthorisedViewLevels())))
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

			if (!empty($item->head))
			{
				// base64 Decode head.
				$item->head = base64_decode($item->head);
			}

			if (!empty($item->main_class_code))
			{
				// base64 Decode main_class_code.
				$item->main_class_code = base64_decode($item->main_class_code);
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

			if (!empty($item->sql))
			{
				// base64 Decode sql.
				$item->sql = base64_decode($item->sql);
			}

			if (!empty($item->php_script_construct))
			{
				// base64 Decode php_script_construct.
				$item->php_script_construct = base64_decode($item->php_script_construct);
			}

			if (!empty($item->sql_uninstall))
			{
				// base64 Decode sql_uninstall.
				$item->sql_uninstall = base64_decode($item->sql_uninstall);
			}

			if (!empty($item->readme))
			{
				// base64 Decode readme.
				$item->readme = base64_decode($item->readme);
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

			if (!empty($item->php_preflight_uninstall))
			{
				// base64 Decode php_preflight_uninstall.
				$item->php_preflight_uninstall = base64_decode($item->php_preflight_uninstall);
			}

			if (!empty($item->method_selection))
			{
				// Convert the method_selection field to an array.
				$method_selection = new Registry;
				$method_selection->loadString($item->method_selection);
				$item->method_selection = $method_selection->toArray();
			}

			if (!empty($item->property_selection))
			{
				// Convert the property_selection field to an array.
				$property_selection = new Registry;
				$property_selection->loadString($item->property_selection);
				$item->property_selection = $property_selection->toArray();
			}

			if (!empty($item->fields))
			{
				// Convert the fields field to an array.
				$fields = new Registry;
				$fields->loadString($item->fields);
				$item->fields = $fields->toArray();
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
			if (($vdm = SessionHelper::get('joomla_plugin__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'joomla_plugin__' . $id);
				SessionHelper::set('joomla_plugin__' . $id, $this->vastDevMod);
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
		$form = $this->loadForm('com_componentbuilder.joomla_plugin', 'joomla_plugin', $options, $clear, $xpath);

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
		if ($id != 0 && (!$user->authorise('joomla_plugin.edit.state', 'com_componentbuilder.joomla_plugin.' . (int) $id))
			|| ($id == 0 && !$user->authorise('joomla_plugin.edit.state', 'com_componentbuilder')))
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
		if ($id != 0 && (!$user->authorise('joomla_plugin.edit.created_by', 'com_componentbuilder.joomla_plugin.' . (int) $id))
			|| ($id == 0 && !$user->authorise('joomla_plugin.edit.created_by', 'com_componentbuilder')))
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
		if ($id != 0 && (!$user->authorise('joomla_plugin.edit.created', 'com_componentbuilder.joomla_plugin.' . (int) $id))
			|| ($id == 0 && !$user->authorise('joomla_plugin.edit.created', 'com_componentbuilder')))
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
		return $this->getCurrentUser()->authorise('joomla_plugin.delete', 'com_componentbuilder.joomla_plugin.' . (int) $record->id);
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
			$permission = $user->authorise('joomla_plugin.edit.state', 'com_componentbuilder.joomla_plugin.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('joomla_plugin.edit.state', 'com_componentbuilder');
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
		$access = ($user->authorise('joomla_plugin.access', 'com_componentbuilder.joomla_plugin.' . (int) $recordId) && $user->authorise('joomla_plugin.access', 'com_componentbuilder'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('joomla_plugin.edit', 'com_componentbuilder.joomla_plugin.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('joomla_plugin.edit.own', 'com_componentbuilder.joomla_plugin.' . (int) $recordId))
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
						if ($user->authorise('joomla_plugin.edit.own', 'com_componentbuilder'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return $user->authorise('joomla_plugin.edit', $this->option);
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
			$table->created = $date->toSql();
			// set the user
			if ($table->created_by == 0 || empty($table->created_by))
			{
				$table->created_by = $user->id;
			}
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = $this->getDatabase();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__componentbuilder_joomla_plugin'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			$table->modified = $date->toSql();
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
		$data = Factory::getApplication()->getUserState('com_componentbuilder.edit.joomla_plugin.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the per process of the data
		$this->preprocessData('com_componentbuilder.joomla_plugin', $data);

		return $data;
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
			'class_extends' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'joomla_plugin_group' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'add_sql' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_method_uninstall' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_postflight_update' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_postflight_install' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'sales_server' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'add_update_server' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'method_selection' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'property_selection' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_head' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_sql_uninstall' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'addreadme' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'head' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'update_server_target' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'main_class_code' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'update_server' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'description' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_postflight_install' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'plugin_version' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
			'php_postflight_update' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'fields' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_method_uninstall' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_php_script_construct' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'sql' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_script_construct' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'sql_uninstall' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_php_preflight_install' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'readme' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_preflight_install' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'update_server_url' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'add_php_preflight_update' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_preflight_update' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_php_preflight_uninstall' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_sales_server' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_preflight_uninstall' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'guid' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'name' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
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
			'joomla_plugin_updates' => 'joomla_plugin',
			'joomla_plugin_files_folders_urls' => 'joomla_plugin'
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
				['a' => 'joomla_plugin'], // source table
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
			'joomla_plugin_updates' => 'joomla_plugin',
			'joomla_plugin_files_folders_urls' => 'joomla_plugin'
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
				['a' => 'joomla_plugin'], // source table
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
			$data['guid'] = (string) GetHelper::var('joomla_plugin', $data['id'], 'id', 'guid', '=', 'componentbuilder');
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
		while (!GuidHelper::valid($data['guid'], 'joomla_plugin', $data['id'], 'componentbuilder'))
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
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_RECORD_LOAD'));

				return false;
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

		// check if the name has placeholder
		if (strpos($data['name'], '[[[') === false && strpos($data['name'], '###') === false)
		{
			// make sure the name is safe to be used as a function name
			$data['name'] = ClassfunctionHelper::safe($data['name']);
		}
		// if system name is empty create from name
		if (empty($data['system_name']) || !UtilitiesStringHelper::check($data['system_name']))
		{
			$data['system_name'] = $data['name'];
		}

		// Set the GUID if empty or not valid
		if (empty($data['guid']) && $data['id'] > 0)
		{
			// get the existing one
			$data['guid'] = (string) GetHelper::var('joomla_plugin', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "joomla_plugin", $data['id']))
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

		if (array_key_exists('method_selection', $jcbPatchStored)
			&& (!array_key_exists('method_selection', $data) || serialize($data['method_selection']) !== $jcbPatchInput['method_selection']))
		{
			unset($jcbPatchStored['method_selection']);
		}

		// Set the method_selection items to data.
		if (isset($data['method_selection']) && is_array($data['method_selection']) && !array_key_exists('method_selection', $jcbPatchStored))
		{
			$method_selection = new Registry;
			$method_selection->loadArray($data['method_selection']);
			$data['method_selection'] = (string) $method_selection;
		}
		elseif (!isset($data['method_selection']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty method_selection to data
			$data['method_selection'] = '';
		}

		if (array_key_exists('property_selection', $jcbPatchStored)
			&& (!array_key_exists('property_selection', $data) || serialize($data['property_selection']) !== $jcbPatchInput['property_selection']))
		{
			unset($jcbPatchStored['property_selection']);
		}

		// Set the property_selection items to data.
		if (isset($data['property_selection']) && is_array($data['property_selection']) && !array_key_exists('property_selection', $jcbPatchStored))
		{
			$property_selection = new Registry;
			$property_selection->loadArray($data['property_selection']);
			$data['property_selection'] = (string) $property_selection;
		}
		elseif (!isset($data['property_selection']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty property_selection to data
			$data['property_selection'] = '';
		}

		if (array_key_exists('fields', $jcbPatchStored)
			&& (!array_key_exists('fields', $data) || serialize($data['fields']) !== $jcbPatchInput['fields']))
		{
			unset($jcbPatchStored['fields']);
		}

		// Set the fields items to data.
		if (isset($data['fields']) && is_array($data['fields']) && !array_key_exists('fields', $jcbPatchStored))
		{
			$fields = new Registry;
			$fields->loadArray($data['fields']);
			$data['fields'] = (string) $fields;
		}
		elseif (!isset($data['fields']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty fields to data
			$data['fields'] = '';
		}

		if (array_key_exists('head', $jcbPatchStored)
			&& (!array_key_exists('head', $data) || serialize($data['head']) !== $jcbPatchInput['head']))
		{
			unset($jcbPatchStored['head']);
		}

		// Set the head string to base64 string.
		if (isset($data['head']) && !array_key_exists('head', $jcbPatchStored))
		{
			$data['head'] = base64_encode($data['head']);
		}

		if (array_key_exists('main_class_code', $jcbPatchStored)
			&& (!array_key_exists('main_class_code', $data) || serialize($data['main_class_code']) !== $jcbPatchInput['main_class_code']))
		{
			unset($jcbPatchStored['main_class_code']);
		}

		// Set the main_class_code string to base64 string.
		if (isset($data['main_class_code']) && !array_key_exists('main_class_code', $jcbPatchStored))
		{
			$data['main_class_code'] = base64_encode($data['main_class_code']);
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

		if (array_key_exists('php_script_construct', $jcbPatchStored)
			&& (!array_key_exists('php_script_construct', $data) || serialize($data['php_script_construct']) !== $jcbPatchInput['php_script_construct']))
		{
			unset($jcbPatchStored['php_script_construct']);
		}

		// Set the php_script_construct string to base64 string.
		if (isset($data['php_script_construct']) && !array_key_exists('php_script_construct', $jcbPatchStored))
		{
			$data['php_script_construct'] = base64_encode($data['php_script_construct']);
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

		if (array_key_exists('php_preflight_uninstall', $jcbPatchStored)
			&& (!array_key_exists('php_preflight_uninstall', $data) || serialize($data['php_preflight_uninstall']) !== $jcbPatchInput['php_preflight_uninstall']))
		{
			unset($jcbPatchStored['php_preflight_uninstall']);
		}

		// Set the php_preflight_uninstall string to base64 string.
		if (isset($data['php_preflight_uninstall']) && !array_key_exists('php_preflight_uninstall', $jcbPatchStored))
		{
			$data['php_preflight_uninstall'] = base64_encode($data['php_preflight_uninstall']);
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
