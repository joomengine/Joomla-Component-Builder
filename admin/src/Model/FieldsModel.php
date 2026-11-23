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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use Joomla\CMS\Helper\TagsHelper;
use VDM\Joomla\Utilities\FormHelper as JCBFormHelper;
use VDM\Joomla\Componentbuilder\Utilities\FilterHelper as JCBFilterHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Form\Form;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Fields List Model
 *
 * @since  1.6
 */
class FieldsModel extends ListModel
{
	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'administrator/components/com_componentbuilder/assets/css/admin.css',
		'administrator/components/com_componentbuilder/assets/css/fields.css'
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
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.id','id',
				'a.published','published',
				'a.access','access',
				'a.ordering','ordering',
				'a.created_by','created_by',
				'a.modified_by','modified_by',
				'g.name','fieldtype',
				'a.datatype','datatype',
				'a.indexes','indexes',
				'a.null_switch','null_switch',
				'a.store','store',
				'c.title','category_title',
				'c.id', 'category_id',
				'a.catid','catid',
				'a.name','name'
			);
		}

		parent::__construct($config, $factory);

		$this->app ??= Factory::getApplication();
	}


	/**
	 * Get the filter form - Override the parent method
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  Form|boolean  The Form object or false on error
	 *
	 * @since   JCB 2.12.5
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		// load form from the parent class
		$form = parent::getFilterForm($data, $loadData);

		// Create the "extension" filter
		$form->setField(new \SimpleXMLElement(
			JCBFilterHelper::extensions()
			),'filter');
		$form->setValue(
			'extension',
			'filter',
			$this->state->get("filter.extension")
		);
		array_push($this->filter_fields, 'extension');

		// Create the "admin_view" filter
		$attributes = [
			'name' => 'admin_view',
			'type' => 'list',
			'onchange' => 'this.form.submit();',
		];
		$options = [
			'' => '-  ' . Text::_('COM_COMPONENTBUILDER_NO_ADMIN_VIEWS_FOUND') . '  -'
		];
		// check if we have admin views (and limit to an extension if it is set)
		if (($admin_views = JCBFilterHelper::names('admin_view', $this->state->get("filter.extension"))) !== null)
		{
			$options = [
				'' => '-  ' . Text::_('COM_COMPONENTBUILDER_SELECT_ADMIN_VIEW') . '  -'
			];
			// make sure we do not lose the key values in normal merge
			$options = $options + $admin_views;
		}

		$form->setField(JCBFormHelper::xml($attributes, $options), 'filter');
		$form->setValue(
			'admin_view',
			'filter',
			$this->state->get("filter.admin_view")
		);
		array_push($this->filter_fields, 'admin_view');

		return $form;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 * @since   1.7.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = $this->app;
		$input = $this->app->getInput();

		// Adjust the context to support modal layouts.
		if ($layout = $input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// Check if the form was submitted
		$formSubmited = $input->post->get('form_submited');

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		if ($formSubmited)
		{
			$access = $input->post->get('access');
			$this->setState('filter.access', $access);
		}

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
		$this->setState('filter.created_by', $created_by);

		$created = $this->getUserStateFromRequest($this->context . '.filter.created', 'filter_created');
		$this->setState('filter.created', $created);

		$sorting = $this->getUserStateFromRequest($this->context . '.filter.sorting', 'filter_sorting', 0, 'int');
		$this->setState('filter.sorting', $sorting);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$fieldtype = $this->getUserStateFromRequest($this->context . '.filter.fieldtype', 'filter_fieldtype');
		if ($formSubmited)
		{
			$fieldtype = $input->post->get('fieldtype');
			$this->setState('filter.fieldtype', $fieldtype);
		}

		$datatype = $this->getUserStateFromRequest($this->context . '.filter.datatype', 'filter_datatype');
		if ($formSubmited)
		{
			$datatype = $input->post->get('datatype');
			$this->setState('filter.datatype', $datatype);
		}

		$indexes = $this->getUserStateFromRequest($this->context . '.filter.indexes', 'filter_indexes');
		if ($formSubmited)
		{
			$indexes = $input->post->get('indexes');
			$this->setState('filter.indexes', $indexes);
		}

		$null_switch = $this->getUserStateFromRequest($this->context . '.filter.null_switch', 'filter_null_switch');
		if ($formSubmited)
		{
			$null_switch = $input->post->get('null_switch');
			$this->setState('filter.null_switch', $null_switch);
		}

		$store = $this->getUserStateFromRequest($this->context . '.filter.store', 'filter_store');
		if ($formSubmited)
		{
			$store = $input->post->get('store');
			$this->setState('filter.store', $store);
		}

		$category = $app->getUserStateFromRequest($this->context . '.filter.category', 'filter_category');
		$this->setState('filter.category', $category);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		$catid = $this->getUserStateFromRequest($this->context . '.filter.catid', 'filter_catid');
		if ($formSubmited)
		{
			$catid = $input->post->get('catid');
			$this->setState('filter.catid', $catid);
		}

		$name = $this->getUserStateFromRequest($this->context . '.filter.name', 'filter_name');
		if ($formSubmited)
		{
			$name = $input->post->get('name');
			$this->setState('filter.name', $name);
		}

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6
	 */
	public function getItems()
	{
		// Check in items
		$this->checkInNow();

		// load parent items
		$items = parent::getItems();

		// Set values to display correctly.
		if (UtilitiesArrayHelper::check($items))
		{
			// Get the user object if not set.
			if (!isset($user) || !ObjectHelper::check($user))
			{
				$user = $this->getCurrentUser();
			}
			foreach ($items as $nr => &$item)
			{
				// Remove items the user can't access.
				$access = ($user->authorise('field.access', 'com_componentbuilder.field.' . (int) $item->id) && $user->authorise('field.access', 'com_componentbuilder'));
				if (!$access)
				{
					unset($items[$nr]);
					continue;
				}

			}
		}

		// set selection value to a translatable value
		if (UtilitiesArrayHelper::check($items))
		{
			foreach ($items as $nr => &$item)
			{
				// convert datatype
				$item->datatype = $this->selectionTranslation($item->datatype, 'datatype');
				// convert indexes
				$item->indexes = $this->selectionTranslation($item->indexes, 'indexes');
				// convert null_switch
				$item->null_switch = $this->selectionTranslation($item->null_switch, 'null_switch');
				// convert store
				$item->store = $this->selectionTranslation($item->store, 'store');
			}
		}


		// return items
		return $items;
	}

	/**
	 * Method to convert selection values to translatable string.
	 *
	 * @return  string   The translatable string.
	 */
	public function selectionTranslation($value,$name)
	{
		// Array of datatype language strings
		if ($name === 'datatype')
		{
			$datatypeArray = array(
				0 => 'COM_COMPONENTBUILDER_FIELD_SELECT_AN_OPTION',
				'CHAR' => 'COM_COMPONENTBUILDER_FIELD_CHAR',
				'VARCHAR' => 'COM_COMPONENTBUILDER_FIELD_VARCHAR',
				'TEXT' => 'COM_COMPONENTBUILDER_FIELD_TEXT',
				'MEDIUMTEXT' => 'COM_COMPONENTBUILDER_FIELD_MEDIUMTEXT',
				'LONGTEXT' => 'COM_COMPONENTBUILDER_FIELD_LONGTEXT',
				'BLOB' => 'COM_COMPONENTBUILDER_FIELD_BLOB',
				'TINYBLOB' => 'COM_COMPONENTBUILDER_FIELD_TINYBLOB',
				'MEDIUMBLOB' => 'COM_COMPONENTBUILDER_FIELD_MEDIUMBLOB',
				'LONGBLOB' => 'COM_COMPONENTBUILDER_FIELD_LONGBLOB',
				'DATETIME' => 'COM_COMPONENTBUILDER_FIELD_DATETIME',
				'DATE' => 'COM_COMPONENTBUILDER_FIELD_DATE',
				'TIME' => 'COM_COMPONENTBUILDER_FIELD_TIME',
				'INT' => 'COM_COMPONENTBUILDER_FIELD_INT',
				'TINYINT' => 'COM_COMPONENTBUILDER_FIELD_TINYINT',
				'BIGINT' => 'COM_COMPONENTBUILDER_FIELD_BIGINT',
				'FLOAT' => 'COM_COMPONENTBUILDER_FIELD_FLOAT',
				'DECIMAL' => 'COM_COMPONENTBUILDER_FIELD_DECIMAL',
				'DOUBLE' => 'COM_COMPONENTBUILDER_FIELD_DOUBLE'
			);
			// Now check if value is found in this array
			if (isset($datatypeArray[$value]) && StringHelper::check($datatypeArray[$value]))
			{
				return $datatypeArray[$value];
			}
		}
		// Array of indexes language strings
		if ($name === 'indexes')
		{
			$indexesArray = array(
				1 => 'COM_COMPONENTBUILDER_FIELD_UNIQUE_KEY',
				2 => 'COM_COMPONENTBUILDER_FIELD_KEY',
				0 => 'COM_COMPONENTBUILDER_FIELD_NONE'
			);
			// Now check if value is found in this array
			if (isset($indexesArray[$value]) && StringHelper::check($indexesArray[$value]))
			{
				return $indexesArray[$value];
			}
		}
		// Array of null_switch language strings
		if ($name === 'null_switch')
		{
			$null_switchArray = array(
				'NULL' => 'COM_COMPONENTBUILDER_FIELD_NULL',
				'NOT NULL' => 'COM_COMPONENTBUILDER_FIELD_NOT_NULL'
			);
			// Now check if value is found in this array
			if (isset($null_switchArray[$value]) && StringHelper::check($null_switchArray[$value]))
			{
				return $null_switchArray[$value];
			}
		}
		// Array of store language strings
		if ($name === 'store')
		{
			$storeArray = array(
				0 => 'COM_COMPONENTBUILDER_FIELD_DEFAULT',
				1 => 'COM_COMPONENTBUILDER_FIELD_JSON',
				2 => 'COM_COMPONENTBUILDER_FIELD_BASE64',
				3 => 'COM_COMPONENTBUILDER_FIELD_BASIC_ENCRYPTION_LOCALDBKEY',
				5 => 'COM_COMPONENTBUILDER_FIELD_MEDIUM_ENCRYPTION_LOCALFILEKEY',
				6 => 'COM_COMPONENTBUILDER_FIELD_EXPERT_MODE_CUSTOM'
			);
			// Now check if value is found in this array
			if (isset($storeArray[$value]) && StringHelper::check($storeArray[$value]))
			{
				return $storeArray[$value];
			}
		}
		return $value;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string    An SQL query
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Get the user object.
		$user = $this->getCurrentUser();
		// Create a new query object.
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('a.*');
		$query->select($db->quoteName('c.title','category_title'));

		// From the componentbuilder_item table
		$query->from($db->quoteName('#__componentbuilder_field', 'a'));
		$query->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id') . ')');


		// Filtering "extension"
		$filter_extension = $this->state->get("filter.extension");
		$field_guids = [];
		$get_ids = true;
		if ($get_ids && $filter_extension !== null && !empty($filter_extension))
		{
			// column name, and id
			$type_extension = explode('__', $filter_extension);
			if (($guids = JCBFilterHelper::linked((string) $type_extension[1], (string) $type_extension[0])) !== null)
			{
				$field_guids = $guids;
			}
			else
			{
				// there is none
				$query->where($db->quoteName('a.id') . ' = ' . 0);
				$get_ids = false;
			}
		}

		// Filtering "admin_view"
		$filter_admin_view = $this->state->get("filter.admin_view");
		if ($get_ids && $filter_admin_view !== null && !empty($filter_admin_view))
		{
			if (($guids = JCBFilterHelper::linked((string) $filter_admin_view, 'admin_view')) !== null)
			{
				// view will return less fields, so we ignore the component
				$field_guids = $guids;
			}
			else
			{
				// there is none
				$query->where($db->quoteName('a.id') . ' = ' . 0);
				$get_ids = false;
			}
		}
		// now check if we have GUIDs
		if ($get_ids && UtilitiesArrayHelper::check($field_guids))
		{
			$query->where($db->quoteName('a.guid') . ' IN ("' . implode('","', $field_guids) . '")');
		}

		// From the componentbuilder_fieldtype table.
		$query->select($db->quoteName(['g.name','g.id'],['fieldtype_name','fieldtype_id']));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_fieldtype', 'g') . ' ON (' . $db->quoteName('a.fieldtype') . ' = ' . $db->quoteName('g.guid') . ')');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		// Filter by access level.
		$_access = $this->getState('filter.access');
		if ($_access && is_numeric($_access))
		{
			$query->where('a.access = ' . (int) $_access);
		}
		elseif (UtilitiesArrayHelper::check($_access))
		{
			// Secure the array for the query
			$_access = ArrayHelper::toInteger($_access);
			// Filter by the Access Array.
			$query->where('a.access IN (' . implode(',', $_access) . ')');
		}
		// Implement View Level Access
		if (!$user->authorise('core.options', 'com_componentbuilder'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search) . '%');
				$query->where('(a.name LIKE '.$search.' OR a.fieldtype LIKE '.$search.' OR g.name LIKE '.$search.' OR a.datatype LIKE '.$search.' OR a.indexes LIKE '.$search.' OR a.null_switch LIKE '.$search.' OR a.store LIKE '.$search.' OR a.catid LIKE '.$search.' OR a.xml LIKE '.$search.' OR a.guid LIKE '.$search.')');
			}
		}

		// Filter by Fieldtype.
		$_fieldtype = $this->getState('filter.fieldtype');
		if (is_numeric($_fieldtype))
		{
			if (is_float($_fieldtype))
			{
				$query->where('a.fieldtype = ' . (float) $_fieldtype);
			}
			else
			{
				$query->where('a.fieldtype = ' . (int) $_fieldtype);
			}
		}
		elseif (StringHelper::check($_fieldtype))
		{
			$query->where('a.fieldtype = ' . $db->quote($db->escape($_fieldtype)));
		}
		// Filter by Datatype.
		$_datatype = $this->getState('filter.datatype');
		if (is_numeric($_datatype))
		{
			if (is_float($_datatype))
			{
				$query->where('a.datatype = ' . (float) $_datatype);
			}
			else
			{
				$query->where('a.datatype = ' . (int) $_datatype);
			}
		}
		elseif (StringHelper::check($_datatype))
		{
			$query->where('a.datatype = ' . $db->quote($db->escape($_datatype)));
		}
		// Filter by Indexes.
		$_indexes = $this->getState('filter.indexes');
		if (is_numeric($_indexes))
		{
			if (is_float($_indexes))
			{
				$query->where('a.indexes = ' . (float) $_indexes);
			}
			else
			{
				$query->where('a.indexes = ' . (int) $_indexes);
			}
		}
		elseif (StringHelper::check($_indexes))
		{
			$query->where('a.indexes = ' . $db->quote($db->escape($_indexes)));
		}
		// Filter by Null_switch.
		$_null_switch = $this->getState('filter.null_switch');
		if (is_numeric($_null_switch))
		{
			if (is_float($_null_switch))
			{
				$query->where('a.null_switch = ' . (float) $_null_switch);
			}
			else
			{
				$query->where('a.null_switch = ' . (int) $_null_switch);
			}
		}
		elseif (StringHelper::check($_null_switch))
		{
			$query->where('a.null_switch = ' . $db->quote($db->escape($_null_switch)));
		}
		// Filter by Store.
		$_store = $this->getState('filter.store');
		if (is_numeric($_store))
		{
			if (is_float($_store))
			{
				$query->where('a.store = ' . (float) $_store);
			}
			else
			{
				$query->where('a.store = ' . (int) $_store);
			}
		}
		elseif (StringHelper::check($_store))
		{
			$query->where('a.store = ' . $db->quote($db->escape($_store)));
		}

		// Filter by a single or group of categories.
		$baselevel = 1;
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$cat_tbl = Table::getInstance('Category', 'JTable');
			$cat_tbl->load($categoryId);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$query->where('c.lft >= ' . (int) $lft)
				->where('c.rgt <= ' . (int) $rgt);
		}
		elseif (is_array($categoryId))
		{
			$categoryId = ArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$query->where('a.catid IN (' . $categoryId . ')');
		}


		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'a.id');
		$orderDirn = $this->getState('list.direction', 'desc');
		if ($orderCol != '')
		{
			// Check that the order direction is valid encase we have a field called direction as part of filers.
			$orderDirn = (is_string($orderDirn) && in_array(strtolower($orderDirn), ['asc', 'desc'])) ? $orderDirn : 'desc';
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @return  string  A store id.
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		// Check if the value is an array
		$_access = $this->getState('filter.access');
		if (UtilitiesArrayHelper::check($_access))
		{
			$id .= ':' . implode(':', $_access);
		}
		// Check if this is only an number or string
		elseif (is_numeric($_access)
		 || StringHelper::check($_access))
		{
			$id .= ':' . $_access;
		}
		$id .= ':' . $this->getState('filter.ordering');
		$id .= ':' . $this->getState('filter.created_by');
		$id .= ':' . $this->getState('filter.modified_by');
		$id .= ':' . $this->getState('filter.fieldtype');
		$id .= ':' . $this->getState('filter.datatype');
		$id .= ':' . $this->getState('filter.indexes');
		$id .= ':' . $this->getState('filter.null_switch');
		$id .= ':' . $this->getState('filter.store');
		// Check if the value is an array
		$_category = $this->getState('filter.category');
		if (UtilitiesArrayHelper::check($_category))
		{
			$id .= ':' . implode(':', $_category);
		}
		// Check if this is only an number or string
		elseif (is_numeric($_category)
		 || StringHelper::check($_category))
		{
			$id .= ':' . $_category;
		}
		// Check if the value is an array
		$_category_id = $this->getState('filter.category_id');
		if (UtilitiesArrayHelper::check($_category_id))
		{
			$id .= ':' . implode(':', $_category_id);
		}
		// Check if this is only an number or string
		elseif (is_numeric($_category_id)
		 || StringHelper::check($_category_id))
		{
			$id .= ':' . $_category_id;
		}
		// Check if the value is an array
		$_catid = $this->getState('filter.catid');
		if (UtilitiesArrayHelper::check($_catid))
		{
			$id .= ':' . implode(':', $_catid);
		}
		// Check if this is only an number or string
		elseif (is_numeric($_catid)
		 || StringHelper::check($_catid))
		{
			$id .= ':' . $_catid;
		}
		$id .= ':' . $this->getState('filter.name');

		return parent::getStoreId($id);
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
	 * Build an SQL query to check in all items left checked out longer then a set time.
	 *
	 * @return void
	 * @throws \DateMalformedStringException
	 * @since 3.2.0
	 */
	protected function checkInNow(): void
	{
		// Get set check in time
		$time = ComponentHelper::getParams('com_componentbuilder')->get('check_in');

		if ($time)
		{
			// Get a db connection.
			$db = $this->getDatabase();
			// Reset query.
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__componentbuilder_field'));
			// Only select items that are checked out.
			$query->where($db->quoteName('checked_out') . ' >= 0');
			// Query only to see if we have a rows
			$db->setQuery($query, 0, 1);
			$db->execute();
			if ($db->getNumRows())
			{
				// Get target date in the past.
				$date = Factory::getDate()->modify($time)->toSql();
				// Reset query.
				$query = $db->getQuery(true);

				// Fields to update.
				$fields = [
					$db->quoteName('checked_out_time') . ' = NULL',
					$db->quoteName('checked_out') . ' = NULL'
				];

				// Conditions for which records should be updated.
				$conditions = [
					$db->quoteName('checked_out') . ' = 0 OR ' . $db->quoteName('checked_out') . ' > 0',
					$db->quoteName('checked_out_time') . ' < ' . $db->quote($date)
				];

				// Check table.
				$query->update($db->quoteName('#__componentbuilder_field'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}
	}
}
