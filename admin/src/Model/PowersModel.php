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
use VDM\Joomla\Componentbuilder\Utilities\FilterHelper as JCBFilterHelper;
use VDM\Joomla\Utilities\FormHelper as JCBFormHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Form\Form;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Powers List Model
 *
 * @since  1.6
 */
class PowersModel extends ListModel
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
		'administrator/components/com_componentbuilder/assets/css/powers.css'
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
				'a.type','type',
				'a.approved','approved',
				'a.system_name','system_name',
				'a.namespace','namespace',
				'a.power_version','power_version'
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

		// Create the "namegroup" filter
		$attributes = array(
			'name' => 'namegroup',
			'type' => 'list',
			'onchange' => 'this.form.submit();',
		);
		$options = array(
			'' => '-  ' . Text::_('COM_COMPONENTBUILDER_NO_NAMESPACE_FOUND') . '  -'
		);
		// check if we have namespace
		if (($namespaces = JCBFilterHelper::namespaces()) !== null)
		{
			$options = array(
				'' => '-  ' . Text::_('COM_COMPONENTBUILDER_SELECT_A_NAMESPACE') . '  -'
			);
			// make sure we do not lose the key values in normal merge
			$options = $options + $namespaces;
		}

		$form->setField(JCBFormHelper::xml($attributes, $options),'filter');
		$form->setValue(
			'namegroup',
			'filter',
			$this->state->get("filter.namegroup")
		);
		array_push($this->filter_fields, 'namegroup');

		// Create the "approved_paths" filter
		$attributes = array(
			'name' => 'approved_paths',
			'type' => 'list',
			'onchange' => 'this.form.submit();',
		);
		$options = array(
			'' => '-  ' . Text::_('COM_COMPONENTBUILDER_NO_PATHS_FOUND') . '  -'
		);
		// check if we have approved_paths = 1
		if (($approvedpaths = JCBFilterHelper::repositories(1)) !== null)
		{
			$options = array(
				'' => '-  ' . Text::_('COM_COMPONENTBUILDER_SELECT_APPROVED_PATH') . '  -'
			);
			// make sure we do not lose the key values in normal merge
			$options = $options + $approvedpaths;
		}

		$form->setField(JCBFormHelper::xml($attributes, $options),'filter');
		$form->setValue(
			'approved_paths',
			'filter',
			$this->state->get("filter.approved_paths")
		);
		array_push($this->filter_fields, 'approved_paths');

		return $form;
	}

	/**
	 * Check if a power can be used in linking
	 *
	 * @param   string    $type  the type of power
	 *
	 * @return  bool
	 * @since   JCB 3.1.23
	 */
	protected function isSuperPower(string $type): bool
	{
		return in_array($type, ['class', 'abstract class', 'final class', 'trait']);
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

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
		if ($formSubmited)
		{
			$type = $input->post->get('type');
			$this->setState('filter.type', $type);
		}

		$approved = $this->getUserStateFromRequest($this->context . '.filter.approved', 'filter_approved');
		if ($formSubmited)
		{
			$approved = $input->post->get('approved');
			$this->setState('filter.approved', $approved);
		}

		$system_name = $this->getUserStateFromRequest($this->context . '.filter.system_name', 'filter_system_name');
		if ($formSubmited)
		{
			$system_name = $input->post->get('system_name');
			$this->setState('filter.system_name', $system_name);
		}

		$namespace = $this->getUserStateFromRequest($this->context . '.filter.namespace', 'filter_namespace');
		if ($formSubmited)
		{
			$namespace = $input->post->get('namespace');
			$this->setState('filter.namespace', $namespace);
		}

		$power_version = $this->getUserStateFromRequest($this->context . '.filter.power_version', 'filter_power_version');
		if ($formSubmited)
		{
			$power_version = $input->post->get('power_version');
			$this->setState('filter.power_version', $power_version);
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
				$access = ($user->authorise('power.access', 'com_componentbuilder.power.' . (int) $item->id) && $user->authorise('power.access', 'com_componentbuilder'));
				if (!$access)
				{
					unset($items[$nr]);
					continue;
				}

				// create the GUID placeholder key
				if ($this->isSuperPower($item->type))
				{
					$item->super_power_key = 'Super_'.'_'.'_' . str_replace('-', '_', $item->guid) . '_'.'_'.'_Power';
				}
				// remove dots
				$item->namespace = str_replace('.','\\', $item->namespace);
			}
		}

		// set selection value to a translatable value
		if (UtilitiesArrayHelper::check($items))
		{
			foreach ($items as $nr => &$item)
			{
				// convert type
				$item->type = $this->selectionTranslation($item->type, 'type');
				// convert approved
				$item->approved = $this->selectionTranslation($item->approved, 'approved');
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
		// Array of type language strings
		if ($name === 'type')
		{
			$typeArray = array(
				'class' => 'COM_COMPONENTBUILDER_POWER_CLASS',
				'abstract class' => 'COM_COMPONENTBUILDER_POWER_ABSTRACT_CLASS',
				'final class' => 'COM_COMPONENTBUILDER_POWER_FINAL_CLASS',
				'interface' => 'COM_COMPONENTBUILDER_POWER_INTERFACE',
				'trait' => 'COM_COMPONENTBUILDER_POWER_TRAIT'
			);
			// Now check if value is found in this array
			if (isset($typeArray[$value]) && StringHelper::check($typeArray[$value]))
			{
				return $typeArray[$value];
			}
		}
		// Array of approved language strings
		if ($name === 'approved')
		{
			$approvedArray = array(
				0 => 'COM_COMPONENTBUILDER_POWER_NOT_APPROVED',
				1 => 'COM_COMPONENTBUILDER_POWER_APPROVED'
			);
			// Now check if value is found in this array
			if (isset($approvedArray[$value]) && StringHelper::check($approvedArray[$value]))
			{
				return $approvedArray[$value];
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

		// From the componentbuilder_item table
		$query->from($db->quoteName('#__componentbuilder_power', 'a'));

		// do not use these filters in the export method
		if (!isset($_export) || !$_export)
		{
			// Filtering "namegroup"
			$filter_namegroup = $this->state->get("filter.namegroup");
			if ($filter_namegroup !== null && !empty($filter_namegroup))
			{
				if (($ids = JCBFilterHelper::namegroup($filter_namegroup)) !== null)
				{
					$query->where($db->quoteName('a.id') . ' IN (' . implode(',', $ids) . ')');
				}
				else
				{
					// there is none
					$query->where($db->quoteName('a.id') . ' = ' . 0);
				}
			}

			// Filtering "approved paths"
			$filter_approved_paths = $this->state->get("filter.approved_paths");
			if ($filter_approved_paths !== null && !empty($filter_approved_paths))
			{
				if (($ids = JCBFilterHelper::paths($filter_approved_paths)) !== null)
				{
					$query->where($db->quoteName('a.id') . ' IN (' . implode(',', $ids) . ')');
				}
				else
				{
					// there is none
					$query->where($db->quoteName('a.id') . ' = ' . 0);
				}
			}
		}

		// From the componentbuilder_power table.
		$query->select($db->quoteName(['g.name','g.id'],['extends_name','extends_id']));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_power', 'g') . ' ON (' . $db->quoteName('a.extends') . ' = ' . $db->quoteName('g.guid') . ')');

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
				$query->where('(a.system_name LIKE '.$search.' OR a.type LIKE '.$search.' OR a.description LIKE '.$search.' OR a.extendsinterfaces_custom LIKE '.$search.' OR a.extends LIKE '.$search.' OR a.extends_custom LIKE '.$search.' OR a.extendsinterfaces LIKE '.$search.' OR a.approved_paths LIKE '.$search.' OR a.guid LIKE '.$search.' OR a.name LIKE '.$search.')');
			}
		}

		// Filter by Type.
		$_type = $this->getState('filter.type');
		if (is_numeric($_type))
		{
			if (is_float($_type))
			{
				$query->where('a.type = ' . (float) $_type);
			}
			else
			{
				$query->where('a.type = ' . (int) $_type);
			}
		}
		elseif (StringHelper::check($_type))
		{
			$query->where('a.type = ' . $db->quote($db->escape($_type)));
		}
		elseif (UtilitiesArrayHelper::check($_type))
		{
			// Secure the array for the query
			$_type = array_map( function ($val) use(&$db) {
				if (is_numeric($val))
				{
					if (is_float($val))
					{
						return (float) $val;
					}
					else
					{
						return (int) $val;
					}
				}
				elseif (StringHelper::check($val))
				{
					return $db->quote($db->escape($val));
				}
			}, $_type);
			// Filter by the Type Array.
			$query->where('a.type IN (' . implode(',', $_type) . ')');
		}
		// Filter by Approved.
		$_approved = $this->getState('filter.approved');
		if (is_numeric($_approved))
		{
			if (is_float($_approved))
			{
				$query->where('a.approved = ' . (float) $_approved);
			}
			else
			{
				$query->where('a.approved = ' . (int) $_approved);
			}
		}
		elseif (StringHelper::check($_approved))
		{
			$query->where('a.approved = ' . $db->quote($db->escape($_approved)));
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
		// Check if the value is an array
		$_type = $this->getState('filter.type');
		if (UtilitiesArrayHelper::check($_type))
		{
			$id .= ':' . implode(':', $_type);
		}
		// Check if this is only an number or string
		elseif (is_numeric($_type)
		 || StringHelper::check($_type))
		{
			$id .= ':' . $_type;
		}
		$id .= ':' . $this->getState('filter.approved');
		$id .= ':' . $this->getState('filter.system_name');
		$id .= ':' . $this->getState('filter.namespace');
		$id .= ':' . $this->getState('filter.power_version');

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
			$query->from($db->quoteName('#__componentbuilder_power'));
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
				$query->update($db->quoteName('#__componentbuilder_power'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}
	}
}
