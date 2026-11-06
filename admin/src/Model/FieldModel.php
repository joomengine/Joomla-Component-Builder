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
use VDM\Joomla\Utilities\GetHelper;
use VDM\Joomla\Utilities\String\FieldHelper;
use VDM\Joomla\Utilities\String\TypeHelper;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Form\FormHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Field Admin Model
 *
 * @since  1.6
 */
class FieldModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The tab layout fields array.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected $tabLayoutFields = array(
		'set_properties' => array(
			'fullwidth' => array(
				'note_select_field_type',
				'note_filter_information'
			),
			'above' => array(
				'fieldtype',
				'name',
				'catid'
			),
			'under' => array(
				'not_required'
			)
		),
		'database' => array(
			'left' => array(
				'datatype',
				'datalenght',
				'datalenght_other',
				'datadefault',
				'datadefault_other'
			),
			'right' => array(
				'indexes',
				'null_switch',
				'store',
				'medium_encryption_note',
				'basic_encryption_note',
				'note_expert_field_save_mode',
				'initiator_on_save_model',
				'initiator_on_get_model',
				'on_save_model_field',
				'on_get_model_field'
			),
			'fullwidth' => array(
				'note_no_database_settings_needed',
				'note_database_settings_needed'
			)
		),
		'type_info' => array(
			'fullwidth' => array(
				'helpnote',
				'xml'
			)
		),
		'scripts' => array(
			'left' => array(
				'add_css_view',
				'css_view',
				'add_css_views',
				'css_views'
			),
			'right' => array(
				'add_javascript_view_footer',
				'javascript_view_footer',
				'add_javascript_views_footer',
				'javascript_views_footer'
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
		'media/com_componentbuilder/css/admin/field.css'
	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'media/com_componentbuilder/js/admin/admin.js',
		'media/com_componentbuilder/js/admin/field.js'
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
	public $typeAlias = 'com_componentbuilder.field';

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
	public function getTable($type = 'field', $prefix = 'Administrator', $config = [])
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
			if (($vdm = SessionHelper::get('field__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'field__' . $id);
				SessionHelper::set('field__' . $id, $this->vastDevMod);
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
					if (!($user->authorise('field.access', 'com_componentbuilder.field.' . $item->id) && $user->authorise('field.access', 'com_componentbuilder')) || (!$user->authorise('core.options', 'com_componentbuilder') && !in_array((int) $item->access, $user->getAuthorisedViewLevels())))
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

			if (!empty($item->on_get_model_field))
			{
				// base64 Decode on_get_model_field.
				$item->on_get_model_field = base64_decode($item->on_get_model_field);
			}

			if (!empty($item->on_save_model_field))
			{
				// base64 Decode on_save_model_field.
				$item->on_save_model_field = base64_decode($item->on_save_model_field);
			}

			if (!empty($item->initiator_on_get_model))
			{
				// base64 Decode initiator_on_get_model.
				$item->initiator_on_get_model = base64_decode($item->initiator_on_get_model);
			}

			if (!empty($item->javascript_view_footer))
			{
				// base64 Decode javascript_view_footer.
				$item->javascript_view_footer = base64_decode($item->javascript_view_footer);
			}

			if (!empty($item->css_views))
			{
				// base64 Decode css_views.
				$item->css_views = base64_decode($item->css_views);
			}

			if (!empty($item->css_view))
			{
				// base64 Decode css_view.
				$item->css_view = base64_decode($item->css_view);
			}

			if (!empty($item->javascript_views_footer))
			{
				// base64 Decode javascript_views_footer.
				$item->javascript_views_footer = base64_decode($item->javascript_views_footer);
			}

			if (!empty($item->initiator_on_save_model))
			{
				// base64 Decode initiator_on_save_model.
				$item->initiator_on_save_model = base64_decode($item->initiator_on_save_model);
			}

			if (!empty($item->xml))
			{
				// JSON Decode xml.
				$item->xml = json_decode($item->xml);
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
			if (($vdm = SessionHelper::get('field__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'field__' . $id);
				SessionHelper::set('field__' . $id, $this->vastDevMod);
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
		$form = $this->loadForm('com_componentbuilder.field', 'field', $options, $clear, $xpath);

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
		if ($id != 0 && (!$user->authorise('field.edit.state', 'com_componentbuilder.field.' . (int) $id))
			|| ($id == 0 && !$user->authorise('field.edit.state', 'com_componentbuilder')))
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
		if (!$user->authorise('core.edit.created_by', 'com_componentbuilder'))
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
		if (!$user->authorise('core.edit.created', 'com_componentbuilder'))
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
		return $this->getCurrentUser()->authorise('field.delete', 'com_componentbuilder.field.' . (int) $record->id);
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
			$permission = $user->authorise('field.edit.state', 'com_componentbuilder.field.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('field.edit.state', 'com_componentbuilder');
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
	{		// get user object.
		$user = $this->getCurrentUser();
		// get record id.
		$recordId = isset($data[$key]) ? (int) $data[$key] : 0;


		// Access check.
		$access = ($user->authorise('field.access', 'com_componentbuilder.field.' . (int) $recordId) && $user->authorise('field.access', 'com_componentbuilder'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('field.edit', 'com_componentbuilder.field.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('field.edit.own', 'com_componentbuilder.field.' . (int) $recordId))
				{
					// Fallback on edit.own. Now test the owner is the user.
					$ownerId = isset($data['created_by']) ? (int) $data['created_by'] : 0;
					if (empty($ownerId))
					{
						return false;
					}

					// If the owner matches 'me' then do the test.
					if ($ownerId == $user->id)
					{
						if ($user->authorise('field.edit.own', $this->option))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return $user->authorise('field.edit', $this->option);
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
					->from($db->quoteName('#__componentbuilder_field'));
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
		$data = Factory::getApplication()->getUserState('com_componentbuilder.edit.field.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the per process of the data
		$this->preprocessData('com_componentbuilder.field', $data);

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
			$conditionGroups = [0 => ['matches' => [0 => ['name' => 'datalenght', 'behavior' => 1, 'options' => [0 => 'Other'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'datalenght_other'], 'show' => true, 'toggle' => true], 1 => ['matches' => [0 => ['name' => 'datadefault', 'behavior' => 1, 'options' => [0 => 'Other'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'datadefault_other'], 'show' => true, 'toggle' => true], 2 => ['matches' => [0 => ['name' => 'datatype', 'behavior' => 1, 'options' => [0 => 'CHAR', 1 => 'VARCHAR', 2 => 'DATETIME', 3 => 'DATE', 4 => 'TIME', 5 => 'INT', 6 => 'TINYINT', 7 => 'BIGINT', 8 => 'FLOAT', 9 => 'DECIMAL', 10 => 'DOUBLE'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'indexes'], 'show' => true, 'toggle' => true], 3 => ['matches' => [0 => ['name' => 'datatype', 'behavior' => 1, 'options' => [0 => 'CHAR', 1 => 'VARCHAR', 2 => 'INT', 3 => 'TINYINT', 4 => 'BIGINT', 5 => 'FLOAT', 6 => 'DECIMAL', 7 => 'DOUBLE'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'datalenght'], 'show' => true, 'toggle' => true], 4 => ['matches' => [0 => ['name' => 'store', 'behavior' => 1, 'options' => [0 => '6'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'on_get_model_field', 1 => 'on_save_model_field'], 'show' => true, 'toggle' => true]];
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
			'name' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'fieldtype' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'datatype' => ['type' => 'CHAR', 'length' => 64, 'nullable' => false],
			'indexes' => ['type' => 'CHAR', 'length' => 64, 'nullable' => false],
			'null_switch' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
			'store' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'catid' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'on_get_model_field' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'on_save_model_field' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'initiator_on_get_model' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'xml' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'datalenght' => ['type' => 'CHAR', 'length' => 64, 'nullable' => false],
			'javascript_view_footer' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'css_views' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'css_view' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'datadefault_other' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
			'datadefault' => ['type' => 'CHAR', 'length' => 64, 'nullable' => false],
			'datalenght_other' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
			'javascript_views_footer' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_css_view' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_css_views' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_javascript_view_footer' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_javascript_views_footer' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'initiator_on_save_model' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'guid' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
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
			$data['guid'] = (string) GetHelper::var('field', $data['id'], 'id', 'guid', '=', 'componentbuilder');
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
		while (!GuidHelper::valid($data['guid'], 'field', $data['id'], 'componentbuilder'))
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


		// get the properties
		$properties = $input->get('properties', null, 'ARRAY');
		// get the extra properties
		$extraproperties = $input->get('extraproperties', null, 'ARRAY');
		// get the type php property
		$typephp = [];
		foreach (ComponentbuilderHelper::$phpFieldArray as $x)
		{
			$typephp[$x] = $input->get('property_type_php' . $x, null, 'RAW');
		}
		// make sure we have an array
		if (UtilitiesArrayHelper::check($properties))
		{
			// set the bucket
			$bucket = [];
			foreach($properties as $property)
			{
				// make sure we have the correct values
				if (UtilitiesArrayHelper::check($property) && isset($property['name']) && UtilitiesStringHelper::check($property['name']) && (isset($property['value']) || 'default' === $property['name']))
				{
					// some fixes, just in case (more can be added)
					switch ($property['name'])
					{
						// fix the values
						case 'name':
							// check if we have placeholder in name
							if (strpos($property['value'], '[[[') !== false || strpos($property['value'], '###') !== false)
							{
								$property['value'] = trim($property['value']);
							}
							else
							{
								$property['value'] = FieldHelper::safe($property['value']);
							}
						break;
						case 'type':
							$property['value'] = TypeHelper::safe($property['value'], 'com_componentbuilder');
						break;
					}
					// load the property
					$bucket[] = "\t" . $property['name'] . '="' . str_replace('"', "&quot;", $property['value']) . '"';
				}
			}
			// make sure we have an array
			if (UtilitiesArrayHelper::check($extraproperties))
			{
				foreach($extraproperties as $xproperty)
				{
					// make sure we have the correct values
					if (UtilitiesArrayHelper::check($xproperty) && isset($xproperty['name']) && UtilitiesStringHelper::check($xproperty['name']) && isset($xproperty['value']))
					{
						// load the extra property
						$bucket[] = "\t" . UtilitiesStringHelper::safe($xproperty['name']) . '="' . str_replace('"', "&quot;", $xproperty['value']) . '"';
					}
				}
			}
			// load the PHP
			foreach ($typephp as $x => $phpvalue)
			{
				// make sure we have a string
				if (UtilitiesStringHelper::check($phpvalue))
				{
					// load the type_php property
					$bucket[] = "\t" . 'type_php' . $x . '_1="__.o0=base64=Oo.__' . base64_encode($phpvalue) . '"';
				}
			}
			// if the bucket has been loaded
			if (UtilitiesArrayHelper::check($bucket))
			{
				$data['xml'] = "<field" . PHP_EOL . implode(PHP_EOL, $bucket) . PHP_EOL . "/>";
			}
		}

		// Set the GUID if empty or not valid
		if (empty($data['guid']) && $data['id'] > 0)
		{
			// get the existing one
			$data['guid'] = (string) GetHelper::var('field', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "field", $data['id']))
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

		if (array_key_exists('xml', $jcbPatchStored)
			&& (!array_key_exists('xml', $data) || serialize($data['xml']) !== $jcbPatchInput['xml']))
		{
			unset($jcbPatchStored['xml']);
		}

		// Set the xml string to JSON string.
		if (isset($data['xml']) && !array_key_exists('xml', $jcbPatchStored))
		{
			$data['xml'] = (string) json_encode($data['xml']);
		}

		if (array_key_exists('on_get_model_field', $jcbPatchStored)
			&& (!array_key_exists('on_get_model_field', $data) || serialize($data['on_get_model_field']) !== $jcbPatchInput['on_get_model_field']))
		{
			unset($jcbPatchStored['on_get_model_field']);
		}

		// Set the on_get_model_field string to base64 string.
		if (isset($data['on_get_model_field']) && !array_key_exists('on_get_model_field', $jcbPatchStored))
		{
			$data['on_get_model_field'] = base64_encode($data['on_get_model_field']);
		}

		if (array_key_exists('on_save_model_field', $jcbPatchStored)
			&& (!array_key_exists('on_save_model_field', $data) || serialize($data['on_save_model_field']) !== $jcbPatchInput['on_save_model_field']))
		{
			unset($jcbPatchStored['on_save_model_field']);
		}

		// Set the on_save_model_field string to base64 string.
		if (isset($data['on_save_model_field']) && !array_key_exists('on_save_model_field', $jcbPatchStored))
		{
			$data['on_save_model_field'] = base64_encode($data['on_save_model_field']);
		}

		if (array_key_exists('initiator_on_get_model', $jcbPatchStored)
			&& (!array_key_exists('initiator_on_get_model', $data) || serialize($data['initiator_on_get_model']) !== $jcbPatchInput['initiator_on_get_model']))
		{
			unset($jcbPatchStored['initiator_on_get_model']);
		}

		// Set the initiator_on_get_model string to base64 string.
		if (isset($data['initiator_on_get_model']) && !array_key_exists('initiator_on_get_model', $jcbPatchStored))
		{
			$data['initiator_on_get_model'] = base64_encode($data['initiator_on_get_model']);
		}

		if (array_key_exists('javascript_view_footer', $jcbPatchStored)
			&& (!array_key_exists('javascript_view_footer', $data) || serialize($data['javascript_view_footer']) !== $jcbPatchInput['javascript_view_footer']))
		{
			unset($jcbPatchStored['javascript_view_footer']);
		}

		// Set the javascript_view_footer string to base64 string.
		if (isset($data['javascript_view_footer']) && !array_key_exists('javascript_view_footer', $jcbPatchStored))
		{
			$data['javascript_view_footer'] = base64_encode($data['javascript_view_footer']);
		}

		if (array_key_exists('css_views', $jcbPatchStored)
			&& (!array_key_exists('css_views', $data) || serialize($data['css_views']) !== $jcbPatchInput['css_views']))
		{
			unset($jcbPatchStored['css_views']);
		}

		// Set the css_views string to base64 string.
		if (isset($data['css_views']) && !array_key_exists('css_views', $jcbPatchStored))
		{
			$data['css_views'] = base64_encode($data['css_views']);
		}

		if (array_key_exists('css_view', $jcbPatchStored)
			&& (!array_key_exists('css_view', $data) || serialize($data['css_view']) !== $jcbPatchInput['css_view']))
		{
			unset($jcbPatchStored['css_view']);
		}

		// Set the css_view string to base64 string.
		if (isset($data['css_view']) && !array_key_exists('css_view', $jcbPatchStored))
		{
			$data['css_view'] = base64_encode($data['css_view']);
		}

		if (array_key_exists('javascript_views_footer', $jcbPatchStored)
			&& (!array_key_exists('javascript_views_footer', $data) || serialize($data['javascript_views_footer']) !== $jcbPatchInput['javascript_views_footer']))
		{
			unset($jcbPatchStored['javascript_views_footer']);
		}

		// Set the javascript_views_footer string to base64 string.
		if (isset($data['javascript_views_footer']) && !array_key_exists('javascript_views_footer', $jcbPatchStored))
		{
			$data['javascript_views_footer'] = base64_encode($data['javascript_views_footer']);
		}

		if (array_key_exists('initiator_on_save_model', $jcbPatchStored)
			&& (!array_key_exists('initiator_on_save_model', $data) || serialize($data['initiator_on_save_model']) !== $jcbPatchInput['initiator_on_save_model']))
		{
			unset($jcbPatchStored['initiator_on_save_model']);
		}

		// Set the initiator_on_save_model string to base64 string.
		if (isset($data['initiator_on_save_model']) && !array_key_exists('initiator_on_save_model', $jcbPatchStored))
		{
			$data['initiator_on_save_model'] = base64_encode($data['initiator_on_save_model']);
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
