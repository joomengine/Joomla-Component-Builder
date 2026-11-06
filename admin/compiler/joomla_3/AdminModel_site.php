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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
###BOM###

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

###SITE_ADMIN_VIEW_MODEL_HEADER###

/**
 * ###Component### ###View### Admin Model
 */
class ###Component###Model###View### extends AdminModel
{
	/**
	 * The tab layout fields array.
	 *
	 * @var      array
	 */
	protected $tabLayoutFields = ###TABLAYOUTFIELDSARRAY###;

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_###COMPONENT###';

	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_###component###.###view###';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = '###view###', $prefix = '###Component###Table', $config = [])
	{
		// add table path for when model gets used from other component
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_###component###/tables');
		// get instance of the table
		return Table::getInstance($type, $prefix, $config);
	}###ADMIN_CUSTOM_BUTTONS_METHOD###

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{###LICENSE_LOCKED_CHECK###
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->params) && !is_array($item->params))
			{
				// Convert the params field to an array.
				$registry = new Registry;
				$registry->loadString($item->params);
				$item->params = $registry->toArray();
			}

			if (!empty($item->metadata))
			{
				// Convert the metadata field to an array.
				$registry = new Registry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}###METHOD_GET_ITEM###
		}###LINKEDVIEWGLOBAL###

		return $item;
	}###LINKEDVIEWMETHODS######LICENSE_LOCKED_SET_BOOL###

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @param   array    $options   Optional array of options for the form creation.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = [], $loadData = true, $options = array('control' => 'jform'))
	{
		// set load data option
		$options['load_data'] = $loadData;###JMODELADMIN_GETFORM###
	}

	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string    script files
	 */
	public function getScript()
	{
		return 'media/com_###component###/js/###view###.js';
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{###JMODELADMIN_CANDELETE###
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{###JMODELADMIN_CANEDITSTATE###
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param    array    $data   An array of input data.
	 * @param    string   $key    The name of the key for the primary key.
	 *
	 * @return    boolean
	 * @since    2.5
	 */
	protected function allowEdit($data = [], $key = 'id')
	{###JMODELADMIN_ALLOWEDIT###
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{###LICENSE_TABLE_LOCKED_CHECK###
		$date = Factory::getDate();
		$user = Factory::getUser();

		if (isset($table->name))
		{
			$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
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
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__###component###_###view###'));
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
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_###component###.edit.###view###.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
			// run the perprocess of the data
			$this->preprocessData('com_###component###.###view###', $data);
		}

		return $data;
	}###VALIDATIONFIX######UNIQUEFIELDS###

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{###JMODELADMIN_BEFORE_DELETE###
		if (!parent::delete($pks))
		{
			return false;
		}###JMODELADMIN_AFTER_DELETE###

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{###JMODELADMIN_BEFORE_PUBLISH###
		if (!parent::publish($pks, $value))
		{
			return false;
		}###JMODELADMIN_AFTER_PUBLISH###

		return true;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of item ids.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize ids.
		$pks = array_unique($pks);
		ArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(Text::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}

		$done = false;

		// Set some needed variables.
		$this->user = Factory::getUser();
		$this->table = $this->getTable();
		$this->tableClassName = get_class($this->table);
		$this->contentType = new UCMType;
		$this->type = $this->contentType->getTypeByTable($this->tableClassName);
		$this->canDo = ###Component###Helper::getActions('###view###');
		$this->batchSet = true;

		if (!$this->canDo->get('core.batch'))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}

		if ($this->type == false)
		{
			$type = new UCMType;
			$this->type = $type->getTypeByAlias($this->typeAlias);
		}

		$this->tagsObserver = $this->table->getObserverOfClass('JTableObserverTags');

		if (!empty($commands['move_copy']))
		{
			$cmd = ArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c')
			{
				$result = $this->batchCopy($commands, $pks, $contexts);

				if (is_array($result))
				{
					foreach ($result as $old => $new)
					{
						$contexts[$new] = $contexts[$old];
					}
					$pks = array_values($result);
				}
				else
				{
					return false;
				}
			}
			elseif ($cmd == 'm' && !$this->batchMove($commands, $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!$done)
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}###MODEL_BATCH_COPY######MODEL_BATCH_MOVE###

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$input    = Factory::getApplication()->input;
		$filter   = InputFilter::getInstance();

		// set the metadata to the Item Data
		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');

			$metadata = new Registry;
			$metadata->loadArray($data['metadata']);
			$data['metadata'] = (string) $metadata;
		}###CHECKBOX_SAVE######METHOD_ITEM_SAVE###

		// Set the Params Items to data
		if (isset($data['params']) && is_array($data['params']))
		{
			$params = new Registry;
			$params->loadArray($data['params']);
			$data['params'] = (string) $params;
		}###TITLEALIASFIX###

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
		$error = 'COM_###COMPONENT###_UNIQUE_FIELD_ERROR';

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
					$error = 'COM_###COMPONENT###_UNIQUE_FIELD_LENGTH';
					break;
				}

				$loaded = $table->load([$field => $value]);

				// A failed query/bind is never evidence that a value is available.
				if ($table->getError())
				{
					$error = 'COM_###COMPONENT###_UNIQUE_FIELD_ERROR';
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
					$error = 'COM_###COMPONENT###_UNIQUE_FIELD_CONFLICT';
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
					$error = 'COM_###COMPONENT###_UNIQUE_FIELD_CONFLICT';
					break;
				}

				$error = 'COM_###COMPONENT###_UNIQUE_FIELD_LIMIT';
			}
		}
		catch (\Throwable $exception)
		{
			$error = 'COM_###COMPONENT###_UNIQUE_FIELD_ERROR';
		}

		$this->setError(Text::sprintf($error, $field));
		return false;
	}###GENERATENEWTITLE###
}
