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
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Components_routers List Model
 *
 * @since  1.6
 */
class Components_routersModel extends ListModel
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
		'administrator/components/com_componentbuilder/assets/css/components_routers.css'
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
				'a.mode_constructor_before_parent','mode_constructor_before_parent',
				'a.mode_constructor_after_parent','mode_constructor_after_parent',
				'a.mode_methods','mode_methods'
			);
		}

		parent::__construct($config, $factory);

		$this->app ??= Factory::getApplication();
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

		$mode_constructor_before_parent = $this->getUserStateFromRequest($this->context . '.filter.mode_constructor_before_parent', 'filter_mode_constructor_before_parent');
		if ($formSubmited)
		{
			$mode_constructor_before_parent = $input->post->get('mode_constructor_before_parent');
			$this->setState('filter.mode_constructor_before_parent', $mode_constructor_before_parent);
		}

		$mode_constructor_after_parent = $this->getUserStateFromRequest($this->context . '.filter.mode_constructor_after_parent', 'filter_mode_constructor_after_parent');
		if ($formSubmited)
		{
			$mode_constructor_after_parent = $input->post->get('mode_constructor_after_parent');
			$this->setState('filter.mode_constructor_after_parent', $mode_constructor_after_parent);
		}

		$mode_methods = $this->getUserStateFromRequest($this->context . '.filter.mode_methods', 'filter_mode_methods');
		if ($formSubmited)
		{
			$mode_methods = $input->post->get('mode_methods');
			$this->setState('filter.mode_methods', $mode_methods);
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
				$access = ($user->authorise('component_router.access', 'com_componentbuilder.component_router.' . (int) $item->id) && $user->authorise('component_router.access', 'com_componentbuilder'));
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
				// convert mode_constructor_before_parent
				$item->mode_constructor_before_parent = $this->selectionTranslation($item->mode_constructor_before_parent, 'mode_constructor_before_parent');
				// convert mode_constructor_after_parent
				$item->mode_constructor_after_parent = $this->selectionTranslation($item->mode_constructor_after_parent, 'mode_constructor_after_parent');
				// convert mode_methods
				$item->mode_methods = $this->selectionTranslation($item->mode_methods, 'mode_methods');
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
		// Array of mode_constructor_before_parent language strings
		if ($name === 'mode_constructor_before_parent')
		{
			$mode_constructor_before_parentArray = array(
				1 => 'COM_COMPONENTBUILDER_COMPONENT_ROUTER_DEFAULT',
				2 => 'COM_COMPONENTBUILDER_COMPONENT_ROUTER_MANUAL',
				3 => 'COM_COMPONENTBUILDER_COMPONENT_ROUTER_CODE'
			);
			// Now check if value is found in this array
			if (isset($mode_constructor_before_parentArray[$value]) && StringHelper::check($mode_constructor_before_parentArray[$value]))
			{
				return $mode_constructor_before_parentArray[$value];
			}
		}
		// Array of mode_constructor_after_parent language strings
		if ($name === 'mode_constructor_after_parent')
		{
			$mode_constructor_after_parentArray = array(
				1 => 'COM_COMPONENTBUILDER_COMPONENT_ROUTER_NONE',
				3 => 'COM_COMPONENTBUILDER_COMPONENT_ROUTER_CODE'
			);
			// Now check if value is found in this array
			if (isset($mode_constructor_after_parentArray[$value]) && StringHelper::check($mode_constructor_after_parentArray[$value]))
			{
				return $mode_constructor_after_parentArray[$value];
			}
		}
		// Array of mode_methods language strings
		if ($name === 'mode_methods')
		{
			$mode_methodsArray = array(
				0 => 'COM_COMPONENTBUILDER_COMPONENT_ROUTER_NONE',
				1 => 'COM_COMPONENTBUILDER_COMPONENT_ROUTER_DEFAULT',
				3 => 'COM_COMPONENTBUILDER_COMPONENT_ROUTER_CODE'
			);
			// Now check if value is found in this array
			if (isset($mode_methodsArray[$value]) && StringHelper::check($mode_methodsArray[$value]))
			{
				return $mode_methodsArray[$value];
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
		$query->from($db->quoteName('#__componentbuilder_component_router', 'a'));

		// From the componentbuilder_joomla_component table.
		$query->select($db->quoteName(['g.system_name','g.id'],['joomla_component_system_name','joomla_component_id']));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_joomla_component', 'g') . ' ON (' . $db->quoteName('a.joomla_component') . ' = ' . $db->quoteName('g.guid') . ')');

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
		// Filter by Mode_constructor_before_parent.
		$_mode_constructor_before_parent = $this->getState('filter.mode_constructor_before_parent');
		if (is_numeric($_mode_constructor_before_parent))
		{
			if (is_float($_mode_constructor_before_parent))
			{
				$query->where('a.mode_constructor_before_parent = ' . (float) $_mode_constructor_before_parent);
			}
			else
			{
				$query->where('a.mode_constructor_before_parent = ' . (int) $_mode_constructor_before_parent);
			}
		}
		elseif (StringHelper::check($_mode_constructor_before_parent))
		{
			$query->where('a.mode_constructor_before_parent = ' . $db->quote($db->escape($_mode_constructor_before_parent)));
		}
		// Filter by Mode_constructor_after_parent.
		$_mode_constructor_after_parent = $this->getState('filter.mode_constructor_after_parent');
		if (is_numeric($_mode_constructor_after_parent))
		{
			if (is_float($_mode_constructor_after_parent))
			{
				$query->where('a.mode_constructor_after_parent = ' . (float) $_mode_constructor_after_parent);
			}
			else
			{
				$query->where('a.mode_constructor_after_parent = ' . (int) $_mode_constructor_after_parent);
			}
		}
		elseif (StringHelper::check($_mode_constructor_after_parent))
		{
			$query->where('a.mode_constructor_after_parent = ' . $db->quote($db->escape($_mode_constructor_after_parent)));
		}
		// Filter by Mode_methods.
		$_mode_methods = $this->getState('filter.mode_methods');
		if (is_numeric($_mode_methods))
		{
			if (is_float($_mode_methods))
			{
				$query->where('a.mode_methods = ' . (float) $_mode_methods);
			}
			else
			{
				$query->where('a.mode_methods = ' . (int) $_mode_methods);
			}
		}
		elseif (StringHelper::check($_mode_methods))
		{
			$query->where('a.mode_methods = ' . $db->quote($db->escape($_mode_methods)));
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
		$id .= ':' . $this->getState('filter.mode_constructor_before_parent');
		$id .= ':' . $this->getState('filter.mode_constructor_after_parent');
		$id .= ':' . $this->getState('filter.mode_methods');

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
			$query->from($db->quoteName('#__componentbuilder_component_router'));
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
				$query->update($db->quoteName('#__componentbuilder_component_router'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}
	}
}
