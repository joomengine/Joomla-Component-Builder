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
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Form\FormHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Library Admin Model
 *
 * @since  1.6
 */
class LibraryModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The tab layout fields array.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected $tabLayoutFields = array(
		'behaviour' => array(
			'left' => array(
				'note_library_instruction',
				'libraries'
			),
			'right' => array(
				'description'
			),
			'fullwidth' => array(
				'note_no_behaviour_one',
				'note_yes_behaviour_one',
				'note_build_in_behaviour_one',
				'note_yes_behaviour_library',
				'addconditions',
				'php_setdocument'
			),
			'above' => array(
				'name',
				'target',
				'how',
				'type'
			),
			'under' => array(
				'not_required'
			)
		),
		'config' => array(
			'fullwidth' => array(
				'note_no_behaviour_two',
				'note_yes_behaviour_two',
				'note_build_in_behaviour_two',
				'note_display_library_config'
			)
		),
		'files_folders_urls' => array(
			'fullwidth' => array(
				'note_no_behaviour_three',
				'note_build_in_behaviour_three',
				'note_display_library_files_folders_urls'
			)
		),
		'linked' => array(
			'fullwidth' => array(
				'note_linked_to_notice'
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
		'media/com_componentbuilder/css/admin/library.css'
	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'media/com_componentbuilder/js/admin/admin.js',
		'media/com_componentbuilder/js/admin/library.js'
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
	public $typeAlias = 'com_componentbuilder.library';

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
	public function getTable($type = 'library', $prefix = 'Administrator', $config = [])
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
			if (($vdm = SessionHelper::get('library__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'library__' . $id);
				SessionHelper::set('library__' . $id, $this->vastDevMod);
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
					if (!($user->authorise('library.access', 'com_componentbuilder.library.' . $item->id) && $user->authorise('library.access', 'com_componentbuilder')) || (!$user->authorise('core.options', 'com_componentbuilder') && !in_array((int) $item->access, $user->getAuthorisedViewLevels())))
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

			if (!empty($item->php_setdocument))
			{
				// base64 Decode php_setdocument.
				$item->php_setdocument = base64_decode($item->php_setdocument);
			}

			if (!empty($item->libraries))
			{
				// Convert the libraries field to an array.
				$libraries = new Registry;
				$libraries->loadString($item->libraries);
				$item->libraries = $libraries->toArray();
			}

			if (!empty($item->addconditions))
			{
				// Convert the addconditions field to an array.
				$addconditions = new Registry;
				$addconditions->loadString($item->addconditions);
				$item->addconditions = $addconditions->toArray();
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
			if (($vdm = SessionHelper::get('library__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'library__' . $id);
				SessionHelper::set('library__' . $id, $this->vastDevMod);
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
		$form = $this->loadForm('com_componentbuilder.library', 'library', $options, $clear, $xpath);

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
		if ($id != 0 && (!$user->authorise('library.edit.state', 'com_componentbuilder.library.' . (int) $id))
			|| ($id == 0 && !$user->authorise('library.edit.state', 'com_componentbuilder')))
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

		// Only load the GUID if new item (or empty)
		if (0 == $id || !($val = $form->getValue('guid')))
		{
			$form->setValue('guid', null, GuidHelper::get());
		}


		// update the addconditions (sub form) layout
		$form->setFieldAttribute('addconditions', 'layout', ComponentbuilderHelper::getSubformLayout('library', 'addconditions'));
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
		return $this->getCurrentUser()->authorise('library.delete', 'com_componentbuilder.library.' . (int) $record->id);
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
			$permission = $user->authorise('library.edit.state', 'com_componentbuilder.library.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('library.edit.state', 'com_componentbuilder');
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
		$access = ($user->authorise('library.access', 'com_componentbuilder.library.' . (int) $recordId) && $user->authorise('library.access', 'com_componentbuilder'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('library.edit', 'com_componentbuilder.library.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('library.edit.own', 'com_componentbuilder.library.' . (int) $recordId))
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
						if ($user->authorise('library.edit.own', 'com_componentbuilder'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return $user->authorise('library.edit', $this->option);
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
					->from($db->quoteName('#__componentbuilder_library'));
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
		$data = Factory::getApplication()->getUserState('com_componentbuilder.edit.library.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the per process of the data
		$this->preprocessData('com_componentbuilder.library', $data);

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
			$conditionGroups = [0 => ['matches' => [0 => ['name' => 'how', 'behavior' => 1, 'options' => [0 => '3'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true], 1 => ['name' => 'target', 'behavior' => 1, 'options' => [0 => '1'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'php_setdocument'], 'show' => true, 'toggle' => true], 1 => ['matches' => [0 => ['name' => 'target', 'behavior' => 1, 'options' => [0 => '1'], 'user' => false, 'checkbox' => false, 'array' => false, 'supported' => true]], 'targets' => [0 => 'how', 1 => 'type'], 'show' => true, 'toggle' => true]];
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
			'target' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'how' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'type' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'description' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'libraries' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_setdocument' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'addconditions' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
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
		// insure the locked library are not deleted
		$app = Factory::getApplication();
		foreach ($pks as $nr => $pk)
		{
			// remove if it is a locked library
			if ($pk > 0 && isset(ComponentbuilderHelper::$libraryNames[$pk]))
			{
				// do not allow delete
				unset($pks[$nr]);
				// set a message to remind them not to delete these libraries (since they are locked)
				$app->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_BSB_LIBRARY_CAN_NOT_BE_DELETED_OR_THINGS_WILL_BREAK', ComponentbuilderHelper::$libraryNames[$pk]), 'warning');
			}
		}
		// check if we can still continue
		if (!UtilitiesArrayHelper::check($pks))
		{
			return false;
		}
		if (!parent::delete($pks))
		{
			return false;
		}

		// linked tables to update
		$_tablesArray = [
			'snippet' => 'library',
			'library_config' => 'library',
			'library_files_folders_urls' => 'library'
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
				['a' => 'library'], // source table
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
		$_tablesArray = [
			'snippet' => 'library',
			'library_config' => 'library',
			'library_files_folders_urls' => 'library'
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
				['a' => 'library'], // source table
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
			$data['guid'] = (string) GetHelper::var('library', $data['id'], 'id', 'guid', '=', 'componentbuilder');
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
		while (!GuidHelper::valid($data['guid'], 'library', $data['id'], 'componentbuilder'))
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


		// Set the GUID if empty or not valid
		if (empty($data['guid']) && $data['id'] > 0)
		{
			// get the existing one
			$data['guid'] = (string) GetHelper::var('library', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "library", $data['id']))
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

		if (array_key_exists('libraries', $jcbPatchStored)
			&& (!array_key_exists('libraries', $data) || serialize($data['libraries']) !== $jcbPatchInput['libraries']))
		{
			unset($jcbPatchStored['libraries']);
		}

		// Set the libraries items to data.
		if (isset($data['libraries']) && is_array($data['libraries']) && !array_key_exists('libraries', $jcbPatchStored))
		{
			$libraries = new Registry;
			$libraries->loadArray($data['libraries']);
			$data['libraries'] = (string) $libraries;
		}
		elseif (!isset($data['libraries']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty libraries to data
			$data['libraries'] = '';
		}

		if (array_key_exists('addconditions', $jcbPatchStored)
			&& (!array_key_exists('addconditions', $data) || serialize($data['addconditions']) !== $jcbPatchInput['addconditions']))
		{
			unset($jcbPatchStored['addconditions']);
		}

		// Set the addconditions items to data.
		if (isset($data['addconditions']) && is_array($data['addconditions']) && !array_key_exists('addconditions', $jcbPatchStored))
		{
			$addconditions = new Registry;
			$addconditions->loadArray($data['addconditions']);
			$data['addconditions'] = (string) $addconditions;
		}
		elseif (!isset($data['addconditions']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty addconditions to data
			$data['addconditions'] = '';
		}

		if (array_key_exists('php_setdocument', $jcbPatchStored)
			&& (!array_key_exists('php_setdocument', $data) || serialize($data['php_setdocument']) !== $jcbPatchInput['php_setdocument']))
		{
			unset($jcbPatchStored['php_setdocument']);
		}

		// Set the php_setdocument string to base64 string.
		if (isset($data['php_setdocument']) && !array_key_exists('php_setdocument', $jcbPatchStored))
		{
			$data['php_setdocument'] = base64_encode($data['php_setdocument']);
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

		// insure the locked library names are not changed
		if ($data['id'] > 0 && isset(ComponentbuilderHelper::$libraryNames[$data['id']]))
		{
			// check if it has or is being changed
			if (ComponentbuilderHelper::$libraryNames[$data['id']] !== $data['name'])
			{
				// the wrong name
				$name_ = $data['name'];
				// change it back
				$data['name'] = ComponentbuilderHelper::$libraryNames[$data['id']];
				// give a notice that the name can not be changed
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_NAME_OF_THIS_LIBRARY_BSB_CAN_NOT_BE_CHANGED_TO_BSB_OR_THINGS_WILL_BREAK', $data['name'], $name_), 'warning');
			}
			// always insure they remain set a main libraries
			$data['type'] = 1;
		}
		// also check to insure these names are not used again
		if (!isset(ComponentbuilderHelper::$libraryNames[$data['id']]) && in_array($data['name'], ComponentbuilderHelper::$libraryNames))
		{
			$data['name'] = $this->generateUnique('name', $data['name']);
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
