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
use VDM\Joomla\FOF\Encrypt\AES;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Form\FormHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Server Admin Model
 *
 * @since  1.6
 */
class ServerModel extends AdminModel
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
				'username',
				'host',
				'port',
				'path'
			),
			'right' => array(
				'authentication',
				'password',
				'private',
				'private_key',
				'secret'
			),
			'fullwidth' => array(
				'note_ftp_signature',
				'signature',
				'note_ssh_security',
				'not_required'
			),
			'above' => array(
				'name',
				'protocol'
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
		'media/com_componentbuilder/css/admin/server.css'
	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'media/com_componentbuilder/js/admin/admin.js',
		'media/com_componentbuilder/js/admin/server.js'
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
	public $typeAlias = 'com_componentbuilder.server';

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
	public function getTable($type = 'server', $prefix = 'Administrator', $config = [])
	{
		// get instance of the table
		return parent::getTable($type, $prefix, $config);
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
					if (!($user->authorise('server.access', 'com_componentbuilder.server.' . $item->id) && $user->authorise('server.access', 'com_componentbuilder')) || (!$user->authorise('core.options', 'com_componentbuilder') && !in_array((int) $item->access, $user->getAuthorisedViewLevels())))
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

			// Get the basic encryption.
			$basickey = ComponentbuilderHelper::getCryptKey('basic');
			// Get the encryption object.
			$basic = new AES($basickey);

			if (!empty($item->signature) && $basickey && !is_numeric($item->signature) && $item->signature === base64_encode(base64_decode($item->signature, true)))
			{
				// basic decrypt data signature.
				$item->signature = rtrim($basic->decryptString($item->signature), "\0");
			}

			if (!empty($item->private_key) && $basickey && !is_numeric($item->private_key) && $item->private_key === base64_encode(base64_decode($item->private_key, true)))
			{
				// basic decrypt data private_key.
				$item->private_key = rtrim($basic->decryptString($item->private_key), "\0");
			}

			if (!empty($item->secret) && $basickey && !is_numeric($item->secret) && $item->secret === base64_encode(base64_decode($item->secret, true)))
			{
				// basic decrypt data secret.
				$item->secret = rtrim($basic->decryptString($item->secret), "\0");
			}

			if (!empty($item->password) && $basickey && !is_numeric($item->password) && $item->password === base64_encode(base64_decode($item->password, true)))
			{
				// basic decrypt data password.
				$item->password = rtrim($basic->decryptString($item->password), "\0");
			}

			if (!empty($item->private) && $basickey && !is_numeric($item->private) && $item->private === base64_encode(base64_decode($item->private, true)))
			{
				// basic decrypt data private.
				$item->private = rtrim($basic->decryptString($item->private), "\0");
			}

			if (!empty($item->path) && $basickey && !is_numeric($item->path) && $item->path === base64_encode(base64_decode($item->path, true)))
			{
				// basic decrypt data path.
				$item->path = rtrim($basic->decryptString($item->path), "\0");
			}

			if (!empty($item->port) && $basickey && !is_numeric($item->port) && $item->port === base64_encode(base64_decode($item->port, true)))
			{
				// basic decrypt data port.
				$item->port = rtrim($basic->decryptString($item->port), "\0");
			}

			if (!empty($item->host) && $basickey && !is_numeric($item->host) && $item->host === base64_encode(base64_decode($item->host, true)))
			{
				// basic decrypt data host.
				$item->host = rtrim($basic->decryptString($item->host), "\0");
			}

			if (!empty($item->username) && $basickey && !is_numeric($item->username) && $item->username === base64_encode(base64_decode($item->username, true)))
			{
				// basic decrypt data username.
				$item->username = rtrim($basic->decryptString($item->username), "\0");
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
		$form = $this->loadForm('com_componentbuilder.server', 'server', $options, $clear, $xpath);

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
		if ($id != 0 && (!$user->authorise('server.edit.state', 'com_componentbuilder.server.' . (int) $id))
			|| ($id == 0 && !$user->authorise('server.edit.state', 'com_componentbuilder')))
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
		if ($id != 0 && (!$user->authorise('server.edit.created_by', 'com_componentbuilder.server.' . (int) $id))
			|| ($id == 0 && !$user->authorise('server.edit.created_by', 'com_componentbuilder')))
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
		if ($id != 0 && (!$user->authorise('server.edit.created', 'com_componentbuilder.server.' . (int) $id))
			|| ($id == 0 && !$user->authorise('server.edit.created', 'com_componentbuilder')))
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
		return $this->getCurrentUser()->authorise('server.delete', 'com_componentbuilder.server.' . (int) $record->id);
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
			$permission = $user->authorise('server.edit.state', 'com_componentbuilder.server.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('server.edit.state', 'com_componentbuilder');
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
		$access = ($user->authorise('server.access', 'com_componentbuilder.server.' . (int) $recordId) && $user->authorise('server.access', 'com_componentbuilder'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('server.edit', 'com_componentbuilder.server.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('server.edit.own', 'com_componentbuilder.server.' . (int) $recordId))
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
						if ($user->authorise('server.edit.own', 'com_componentbuilder'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return $user->authorise('server.edit', $this->option);
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
					->from($db->quoteName('#__componentbuilder_server'));
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
		$data = Factory::getApplication()->getUserState('com_componentbuilder.edit.server.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the per process of the data
		$this->preprocessData('com_componentbuilder.server', $data);

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
			$conditionGroups = [0 => ['matches' => [0 => ['name' => 'protocol', 'behavior' => 1, 'options' => [0 => '2'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'authentication', 1 => 'host', 2 => 'port', 3 => 'path', 4 => 'username'], 'show' => true, 'toggle' => true], 1 => ['matches' => [0 => ['name' => 'protocol', 'behavior' => 1, 'options' => [0 => '1'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'signature'], 'show' => true, 'toggle' => true], 2 => ['matches' => [0 => ['name' => 'protocol', 'behavior' => 1, 'options' => [0 => '2'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true], 1 => ['name' => 'authentication', 'behavior' => 1, 'options' => [0 => '1', 1 => '3', 2 => '5'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'password'], 'show' => true, 'toggle' => true], 3 => ['matches' => [0 => ['name' => 'protocol', 'behavior' => 1, 'options' => [0 => '2'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true], 1 => ['name' => 'authentication', 'behavior' => 1, 'options' => [0 => '2', 1 => '3'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'private'], 'show' => true, 'toggle' => true], 4 => ['matches' => [0 => ['name' => 'protocol', 'behavior' => 1, 'options' => [0 => '2'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true], 1 => ['name' => 'authentication', 'behavior' => 1, 'options' => [0 => '4', 1 => '5'], 'user' => false, 'checkbox' => false, 'array' => true, 'supported' => true]], 'targets' => [0 => 'private_key'], 'show' => true, 'toggle' => true]];
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
		return false;
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
			'protocol' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'signature' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'private_key' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'secret' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'password' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'private' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'authentication' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'path' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'port' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'host' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'username' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
		];
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

		// Get the basic encryption key.
		$basickey = ComponentbuilderHelper::getCryptKey('basic');
		// Get the encryption object
		$basic = new AES($basickey);

		if (array_key_exists('signature', $jcbPatchStored)
			&& (!array_key_exists('signature', $data) || serialize($data['signature']) !== $jcbPatchInput['signature']))
		{
			unset($jcbPatchStored['signature']);
		}

		// Encrypt data signature.
		if (isset($data['signature']) && $basickey && !array_key_exists('signature', $jcbPatchStored))
		{
			$data['signature'] = $basic->encryptString($data['signature']);
		}

		if (array_key_exists('private_key', $jcbPatchStored)
			&& (!array_key_exists('private_key', $data) || serialize($data['private_key']) !== $jcbPatchInput['private_key']))
		{
			unset($jcbPatchStored['private_key']);
		}

		// Encrypt data private_key.
		if (isset($data['private_key']) && $basickey && !array_key_exists('private_key', $jcbPatchStored))
		{
			$data['private_key'] = $basic->encryptString($data['private_key']);
		}

		if (array_key_exists('secret', $jcbPatchStored)
			&& (!array_key_exists('secret', $data) || serialize($data['secret']) !== $jcbPatchInput['secret']))
		{
			unset($jcbPatchStored['secret']);
		}

		// Encrypt data secret.
		if (isset($data['secret']) && $basickey && !array_key_exists('secret', $jcbPatchStored))
		{
			$data['secret'] = $basic->encryptString($data['secret']);
		}

		if (array_key_exists('password', $jcbPatchStored)
			&& (!array_key_exists('password', $data) || serialize($data['password']) !== $jcbPatchInput['password']))
		{
			unset($jcbPatchStored['password']);
		}

		// Encrypt data password.
		if (isset($data['password']) && $basickey && !array_key_exists('password', $jcbPatchStored))
		{
			$data['password'] = $basic->encryptString($data['password']);
		}

		if (array_key_exists('private', $jcbPatchStored)
			&& (!array_key_exists('private', $data) || serialize($data['private']) !== $jcbPatchInput['private']))
		{
			unset($jcbPatchStored['private']);
		}

		// Encrypt data private.
		if (isset($data['private']) && $basickey && !array_key_exists('private', $jcbPatchStored))
		{
			$data['private'] = $basic->encryptString($data['private']);
		}

		if (array_key_exists('path', $jcbPatchStored)
			&& (!array_key_exists('path', $data) || serialize($data['path']) !== $jcbPatchInput['path']))
		{
			unset($jcbPatchStored['path']);
		}

		// Encrypt data path.
		if (isset($data['path']) && $basickey && !array_key_exists('path', $jcbPatchStored))
		{
			$data['path'] = $basic->encryptString($data['path']);
		}

		if (array_key_exists('port', $jcbPatchStored)
			&& (!array_key_exists('port', $data) || serialize($data['port']) !== $jcbPatchInput['port']))
		{
			unset($jcbPatchStored['port']);
		}

		// Encrypt data port.
		if (isset($data['port']) && $basickey && !array_key_exists('port', $jcbPatchStored))
		{
			$data['port'] = $basic->encryptString($data['port']);
		}

		if (array_key_exists('host', $jcbPatchStored)
			&& (!array_key_exists('host', $data) || serialize($data['host']) !== $jcbPatchInput['host']))
		{
			unset($jcbPatchStored['host']);
		}

		// Encrypt data host.
		if (isset($data['host']) && $basickey && !array_key_exists('host', $jcbPatchStored))
		{
			$data['host'] = $basic->encryptString($data['host']);
		}

		if (array_key_exists('username', $jcbPatchStored)
			&& (!array_key_exists('username', $data) || serialize($data['username']) !== $jcbPatchInput['username']))
		{
			unset($jcbPatchStored['username']);
		}

		// Encrypt data username.
		if (isset($data['username']) && $basickey && !array_key_exists('username', $jcbPatchStored))
		{
			$data['username'] = $basic->encryptString($data['username']);
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
