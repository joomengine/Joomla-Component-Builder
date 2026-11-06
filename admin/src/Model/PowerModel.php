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
use VDM\Joomla\Componentbuilder\Power\Factory as PowerFactory;
use VDM\Joomla\Utilities\SessionHelper;
use VDM\Joomla\Utilities\StringHelper as UtilitiesStringHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\GuidHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\GetHelper;
use VDM\Joomla\Utilities\String\ClassfunctionHelper;
use Joomla\CMS\Access\Exception\NotAllowed;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Power Admin Model
 *
 * @since  1.6
 */
class PowerModel extends AdminModel
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
				'extends',
				'extends_custom',
				'extendsinterfaces',
				'extendsinterfaces_custom',
				'implements',
				'implements_custom',
				'namespace',
				'add_head'
			),
			'right' => array(
				'property_selection',
				'method_selection',
				'namespace_details'
			),
			'fullwidth' => array(
				'head',
				'use_selection',
				'main_class_code',
				'load_powers_note',
				'load_selection',
				'note_linked_to_notice',
				'not_required'
			),
			'above' => array(
				'system_name',
				'type',
				'power_version'
			)
		),
		'composer' => array(
			'fullwidth' => array(
				'autoload_composer_note',
				'composer'
			)
		),
		'licensing' => array(
			'fullwidth' => array(
				'add_licensing_template',
				'licensing_template'
			)
		),
		'super_power' => array(
			'left' => array(
				'approved',
				'approved_paths'
			),
			'right' => array(
				'note_approved_paths'
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
		'media/com_componentbuilder/css/admin/power.css'
	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'media/com_componentbuilder/js/admin/admin.js',
		'media/com_componentbuilder/js/admin/power.js'
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
	public $typeAlias = 'com_componentbuilder.power';

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
	public function getTable($type = 'power', $prefix = 'Administrator', $config = [])
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
			if (($vdm = SessionHelper::get('power__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'power__' . $id);
				SessionHelper::set('power__' . $id, $this->vastDevMod);
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
					if (!($user->authorise('power.access', 'com_componentbuilder.power.' . $item->id) && $user->authorise('power.access', 'com_componentbuilder')) || (!$user->authorise('core.options', 'com_componentbuilder') && !in_array((int) $item->access, $user->getAuthorisedViewLevels())))
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

			if (!empty($item->licensing_template))
			{
				// base64 Decode licensing_template.
				$item->licensing_template = base64_decode($item->licensing_template);
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

			if (!empty($item->load_selection))
			{
				// Convert the load_selection field to an array.
				$load_selection = new Registry;
				$load_selection->loadString($item->load_selection);
				$item->load_selection = $load_selection->toArray();
			}

			if (!empty($item->composer))
			{
				// Convert the composer field to an array.
				$composer = new Registry;
				$composer->loadString($item->composer);
				$item->composer = $composer->toArray();
			}

			if (!empty($item->implements))
			{
				// Convert the implements field to an array.
				$implements = new Registry;
				$implements->loadString($item->implements);
				$item->implements = $implements->toArray();
			}

			if (!empty($item->property_selection))
			{
				// Convert the property_selection field to an array.
				$property_selection = new Registry;
				$property_selection->loadString($item->property_selection);
				$item->property_selection = $property_selection->toArray();
			}

			if (!empty($item->extendsinterfaces))
			{
				// Convert the extendsinterfaces field to an array.
				$extendsinterfaces = new Registry;
				$extendsinterfaces->loadString($item->extendsinterfaces);
				$item->extendsinterfaces = $extendsinterfaces->toArray();
			}

			if (!empty($item->method_selection))
			{
				// Convert the method_selection field to an array.
				$method_selection = new Registry;
				$method_selection->loadString($item->method_selection);
				$item->method_selection = $method_selection->toArray();
			}

			if (!empty($item->use_selection))
			{
				// Convert the use_selection field to an array.
				$use_selection = new Registry;
				$use_selection->loadString($item->use_selection);
				$item->use_selection = $use_selection->toArray();
			}

			if (!empty($item->approved_paths))
			{
				// JSON Decode approved_paths.
				$item->approved_paths = json_decode($item->approved_paths);
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
			if (($vdm = SessionHelper::get('power__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'power__' . $id);
				SessionHelper::set('power__' . $id, $this->vastDevMod);
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
		$form = $this->loadForm('com_componentbuilder.power', 'power', $options, $clear, $xpath);

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
		if ($id != 0 && (!$user->authorise('power.edit.state', 'com_componentbuilder.power.' . (int) $id))
			|| ($id == 0 && !$user->authorise('power.edit.state', 'com_componentbuilder')))
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
		if ($id != 0 && (!$user->authorise('power.edit.created_by', 'com_componentbuilder.power.' . (int) $id))
			|| ($id == 0 && !$user->authorise('power.edit.created_by', 'com_componentbuilder')))
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
		if ($id != 0 && (!$user->authorise('power.edit.created', 'com_componentbuilder.power.' . (int) $id))
			|| ($id == 0 && !$user->authorise('power.edit.created', 'com_componentbuilder')))
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
		return $this->getCurrentUser()->authorise('power.delete', 'com_componentbuilder.power.' . (int) $record->id);
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
			$permission = $user->authorise('power.edit.state', 'com_componentbuilder.power.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('power.edit.state', 'com_componentbuilder');
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
		$access = ($user->authorise('power.access', 'com_componentbuilder.power.' . (int) $recordId) && $user->authorise('power.access', 'com_componentbuilder'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('power.edit', 'com_componentbuilder.power.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('power.edit.own', 'com_componentbuilder.power.' . (int) $recordId))
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
						if ($user->authorise('power.edit.own', 'com_componentbuilder'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return $user->authorise('power.edit', $this->option);
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
					->from($db->quoteName('#__componentbuilder_power'));
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
		$data = Factory::getApplication()->getUserState('com_componentbuilder.edit.power.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the per process of the data
		$this->preprocessData('com_componentbuilder.power', $data);

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
			'namespace' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'type' => ['type' => 'VARCHAR', 'length' => 64, 'nullable' => false],
			'power_version' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
			'load_selection' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'description' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'composer' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'licensing_template' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'approved' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'extendsinterfaces_custom' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_head' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'extends' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'extends_custom' => ['type' => 'VARCHAR', 'length' => 64, 'nullable' => true],
			'implements_custom' => ['type' => 'VARCHAR', 'length' => 1024, 'nullable' => true],
			'implements' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'property_selection' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'extendsinterfaces' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'method_selection' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'approved_paths' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'head' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'use_selection' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_licensing_template' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'main_class_code' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
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

		// Set the empty approved_paths item to data
		if (!isset($data['approved_paths']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			$data['approved_paths'] = '';
		}

		// The record keys, as every line below expects them: the primary key as an
		// integer that is never taken from the request (null from the API on create).
		$data['id'] = (int) ($data['id'] ?? 0);

		// The guid is the server's: an existing record keeps the guid it was stored
		// with, and the API never takes one from the request.
		if ($data['id'] > 0)
		{
			$data['guid'] = (string) GetHelper::var('power', $data['id'], 'id', 'guid', '=', 'componentbuilder');
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
		while (!GuidHelper::valid($data['guid'], 'power', $data['id'], 'componentbuilder'))
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

		// must set the version if empty
		if (empty($data['power_version']) && $data['id'] > 0 && ($power_version = GetHelper::var('power', $data['id'], 'id', 'power_version')) !== false)
		{
			$data['power_version'] = $power_version;
		}
		// we must preserve versions (so that a change to the version number must result in save as copy)
		elseif ($data['id'] > 0 && ($old_version = GetHelper::var('power', $data['id'], 'id', 'power_version')) && $data['power_version'] != $old_version)
		{
			// lets check if we already have this version
			if (($existing_id = ComponentbuilderHelper::checkExist('power', ['power_version' => $data['power_version'], 'name' => $data['name'], 'namespace' => $data['namespace']])) !== false)
			{
				// class of that version already exist so we reset the version
				Factory::getApplication()->enqueueMessage(Text::sprintf("COM_COMPONENTBUILDER_POWERS_A_HREFS_TARGET_BLANK_TITLEOPEN_POWERSA_WITH_VERSION_S_ALREADY_EXIST", $existing_id, 'index.php?option=com_componentbuilder&view=powers&task=power.edit&id=' . $existing_id, $data['namespace'] . '\\' . $data['name'], $data['power_version']), 'error');
				$data['power_version'] = $old_version;
			}
			else
			{
				// does not exist so we allow save2copy
				$data['id'] = 0;
				Factory::getApplication()->getInput()->set('task', 'save2copy');
			}
		}

		// load dynamic code if relevant
		if (($main_class_code = PowerFactory::_('Power.Generator')->get($data)) !== null)
		{
			$data['main_class_code'] = $main_class_code;
		}

		// Set the GUID if empty or not valid
		if (empty($data['guid']) && $data['id'] > 0)
		{
			// get the existing one
			$data['guid'] = (string) GetHelper::var('power', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "power", $data['id']))
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

		if (array_key_exists('load_selection', $jcbPatchStored)
			&& (!array_key_exists('load_selection', $data) || serialize($data['load_selection']) !== $jcbPatchInput['load_selection']))
		{
			unset($jcbPatchStored['load_selection']);
		}

		// Set the load_selection items to data.
		if (isset($data['load_selection']) && is_array($data['load_selection']) && !array_key_exists('load_selection', $jcbPatchStored))
		{
			$load_selection = new Registry;
			$load_selection->loadArray($data['load_selection']);
			$data['load_selection'] = (string) $load_selection;
		}
		elseif (!isset($data['load_selection']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty load_selection to data
			$data['load_selection'] = '';
		}

		if (array_key_exists('composer', $jcbPatchStored)
			&& (!array_key_exists('composer', $data) || serialize($data['composer']) !== $jcbPatchInput['composer']))
		{
			unset($jcbPatchStored['composer']);
		}

		// Set the composer items to data.
		if (isset($data['composer']) && is_array($data['composer']) && !array_key_exists('composer', $jcbPatchStored))
		{
			$composer = new Registry;
			$composer->loadArray($data['composer']);
			$data['composer'] = (string) $composer;
		}
		elseif (!isset($data['composer']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty composer to data
			$data['composer'] = '';
		}

		if (array_key_exists('implements', $jcbPatchStored)
			&& (!array_key_exists('implements', $data) || serialize($data['implements']) !== $jcbPatchInput['implements']))
		{
			unset($jcbPatchStored['implements']);
		}

		// Set the implements items to data.
		if (isset($data['implements']) && is_array($data['implements']) && !array_key_exists('implements', $jcbPatchStored))
		{
			$implements = new Registry;
			$implements->loadArray($data['implements']);
			$data['implements'] = (string) $implements;
		}
		elseif (!isset($data['implements']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty implements to data
			$data['implements'] = '';
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

		if (array_key_exists('extendsinterfaces', $jcbPatchStored)
			&& (!array_key_exists('extendsinterfaces', $data) || serialize($data['extendsinterfaces']) !== $jcbPatchInput['extendsinterfaces']))
		{
			unset($jcbPatchStored['extendsinterfaces']);
		}

		// Set the extendsinterfaces items to data.
		if (isset($data['extendsinterfaces']) && is_array($data['extendsinterfaces']) && !array_key_exists('extendsinterfaces', $jcbPatchStored))
		{
			$extendsinterfaces = new Registry;
			$extendsinterfaces->loadArray($data['extendsinterfaces']);
			$data['extendsinterfaces'] = (string) $extendsinterfaces;
		}
		elseif (!isset($data['extendsinterfaces']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty extendsinterfaces to data
			$data['extendsinterfaces'] = '';
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

		if (array_key_exists('use_selection', $jcbPatchStored)
			&& (!array_key_exists('use_selection', $data) || serialize($data['use_selection']) !== $jcbPatchInput['use_selection']))
		{
			unset($jcbPatchStored['use_selection']);
		}

		// Set the use_selection items to data.
		if (isset($data['use_selection']) && is_array($data['use_selection']) && !array_key_exists('use_selection', $jcbPatchStored))
		{
			$use_selection = new Registry;
			$use_selection->loadArray($data['use_selection']);
			$data['use_selection'] = (string) $use_selection;
		}
		elseif (!isset($data['use_selection']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty use_selection to data
			$data['use_selection'] = '';
		}

		if (array_key_exists('approved_paths', $jcbPatchStored)
			&& (!array_key_exists('approved_paths', $data) || serialize($data['approved_paths']) !== $jcbPatchInput['approved_paths']))
		{
			unset($jcbPatchStored['approved_paths']);
		}

		// Set the approved_paths string to JSON string.
		if (isset($data['approved_paths']) && !array_key_exists('approved_paths', $jcbPatchStored))
		{
			$data['approved_paths'] = (string) json_encode($data['approved_paths']);
		}

		if (array_key_exists('licensing_template', $jcbPatchStored)
			&& (!array_key_exists('licensing_template', $data) || serialize($data['licensing_template']) !== $jcbPatchInput['licensing_template']))
		{
			unset($jcbPatchStored['licensing_template']);
		}

		// Set the licensing_template string to base64 string.
		if (isset($data['licensing_template']) && !array_key_exists('licensing_template', $jcbPatchStored))
		{
			$data['licensing_template'] = base64_encode($data['licensing_template']);
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
