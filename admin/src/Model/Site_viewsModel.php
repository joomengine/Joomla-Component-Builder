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
 * Site_views List Model
 *
 * @since  1.6
 */
class Site_viewsModel extends ListModel
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
		'administrator/components/com_componentbuilder/assets/css/site_views.css'
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
				'a.add_php_ajax','add_php_ajax',
				'a.add_custom_button','add_custom_button',
				'a.system_name','system_name',
				'a.name','name',
				'a.description','description',
				'g.name','main_get',
				'a.context','context'
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

		// Create the "joomla_component" filter
		$attributes = array(
			'name' => 'joomla_component',
			'type' => 'list',
			'onchange' => 'this.form.submit();',
		);
		$options = array(
			'' => '-  ' . Text::_('COM_COMPONENTBUILDER_NO_COMPONENTS_FOUND') . '  -'
		);
		// check if we have joomla components
		if (($joomla_components = JCBFilterHelper::names('joomla_component')) !== null)
		{
			$options = array(
				'' => '-  ' . Text::_('COM_COMPONENTBUILDER_SELECT_COMPONENT') . '  -'
			);
			// make sure we do not lose the key values in normal merge
			$options = $options + $joomla_components;
		}

		$form->setField(JCBFormHelper::xml($attributes, $options),'filter');
		$form->setValue(
			'joomla_component',
			'filter',
			$this->state->get("filter.joomla_component")
		);
		array_push($this->filter_fields, 'joomla_component');

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

		$add_php_ajax = $this->getUserStateFromRequest($this->context . '.filter.add_php_ajax', 'filter_add_php_ajax');
		if ($formSubmited)
		{
			$add_php_ajax = $input->post->get('add_php_ajax');
			$this->setState('filter.add_php_ajax', $add_php_ajax);
		}

		$add_custom_button = $this->getUserStateFromRequest($this->context . '.filter.add_custom_button', 'filter_add_custom_button');
		if ($formSubmited)
		{
			$add_custom_button = $input->post->get('add_custom_button');
			$this->setState('filter.add_custom_button', $add_custom_button);
		}

		$system_name = $this->getUserStateFromRequest($this->context . '.filter.system_name', 'filter_system_name');
		if ($formSubmited)
		{
			$system_name = $input->post->get('system_name');
			$this->setState('filter.system_name', $system_name);
		}

		$name = $this->getUserStateFromRequest($this->context . '.filter.name', 'filter_name');
		if ($formSubmited)
		{
			$name = $input->post->get('name');
			$this->setState('filter.name', $name);
		}

		$description = $this->getUserStateFromRequest($this->context . '.filter.description', 'filter_description');
		if ($formSubmited)
		{
			$description = $input->post->get('description');
			$this->setState('filter.description', $description);
		}

		$main_get = $this->getUserStateFromRequest($this->context . '.filter.main_get', 'filter_main_get');
		if ($formSubmited)
		{
			$main_get = $input->post->get('main_get');
			$this->setState('filter.main_get', $main_get);
		}

		$context = $this->getUserStateFromRequest($this->context . '.filter.context', 'filter_context');
		if ($formSubmited)
		{
			$context = $input->post->get('context');
			$this->setState('filter.context', $context);
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
				$access = ($user->authorise('site_view.access', 'com_componentbuilder.site_view.' . (int) $item->id) && $user->authorise('site_view.access', 'com_componentbuilder'));
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
				// convert add_php_ajax
				$item->add_php_ajax = $this->selectionTranslation($item->add_php_ajax, 'add_php_ajax');
				// convert add_custom_button
				$item->add_custom_button = $this->selectionTranslation($item->add_custom_button, 'add_custom_button');
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
		// Array of add_php_ajax language strings
		if ($name === 'add_php_ajax')
		{
			$add_php_ajaxArray = array(
				1 => 'COM_COMPONENTBUILDER_SITE_VIEW_YES',
				0 => 'COM_COMPONENTBUILDER_SITE_VIEW_NO'
			);
			// Now check if value is found in this array
			if (isset($add_php_ajaxArray[$value]) && StringHelper::check($add_php_ajaxArray[$value]))
			{
				return $add_php_ajaxArray[$value];
			}
		}
		// Array of add_custom_button language strings
		if ($name === 'add_custom_button')
		{
			$add_custom_buttonArray = array(
				1 => 'COM_COMPONENTBUILDER_SITE_VIEW_YES',
				0 => 'COM_COMPONENTBUILDER_SITE_VIEW_NO'
			);
			// Now check if value is found in this array
			if (isset($add_custom_buttonArray[$value]) && StringHelper::check($add_custom_buttonArray[$value]))
			{
				return $add_custom_buttonArray[$value];
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
		$query->from($db->quoteName('#__componentbuilder_site_view', 'a'));


		// Filtering "joomla components"
		$filter_joomla_component = $this->state->get("filter.joomla_component");
		if ($filter_joomla_component !== null && !empty($filter_joomla_component))
		{
			if (($guids = JCBFilterHelper::linked((string) $filter_joomla_component, 'joomla_component_site_views')) !== null)
			{
				$query->where($db->quoteName('a.guid') . ' IN ("' . implode('","', $guids) . '")');
			}
			else
			{
				// there is none
				$query->where($db->quoteName('a.id') . ' = ' . 0);
			}
		}

		// From the componentbuilder_dynamic_get table.
		$query->select($db->quoteName(['g.name','g.id'],['main_get_name','main_get_id']));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_dynamic_get', 'g') . ' ON (' . $db->quoteName('a.main_get') . ' = ' . $db->quoteName('g.guid') . ')');

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
				$query->where('(a.system_name LIKE '.$search.' OR a.name LIKE '.$search.' OR a.description LIKE '.$search.' OR a.main_get LIKE '.$search.' OR g.name LIKE '.$search.' OR a.context LIKE '.$search.' OR a.codename LIKE '.$search.')');
			}
		}

		// Filter by Add_php_ajax.
		$_add_php_ajax = $this->getState('filter.add_php_ajax');
		if (is_numeric($_add_php_ajax))
		{
			if (is_float($_add_php_ajax))
			{
				$query->where('a.add_php_ajax = ' . (float) $_add_php_ajax);
			}
			else
			{
				$query->where('a.add_php_ajax = ' . (int) $_add_php_ajax);
			}
		}
		elseif (StringHelper::check($_add_php_ajax))
		{
			$query->where('a.add_php_ajax = ' . $db->quote($db->escape($_add_php_ajax)));
		}
		// Filter by Add_custom_button.
		$_add_custom_button = $this->getState('filter.add_custom_button');
		if (is_numeric($_add_custom_button))
		{
			if (is_float($_add_custom_button))
			{
				$query->where('a.add_custom_button = ' . (float) $_add_custom_button);
			}
			else
			{
				$query->where('a.add_custom_button = ' . (int) $_add_custom_button);
			}
		}
		elseif (StringHelper::check($_add_custom_button))
		{
			$query->where('a.add_custom_button = ' . $db->quote($db->escape($_add_custom_button)));
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
		$id .= ':' . $this->getState('filter.add_php_ajax');
		$id .= ':' . $this->getState('filter.add_custom_button');
		$id .= ':' . $this->getState('filter.system_name');
		$id .= ':' . $this->getState('filter.name');
		$id .= ':' . $this->getState('filter.description');
		$id .= ':' . $this->getState('filter.main_get');
		$id .= ':' . $this->getState('filter.context');

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
			$query->from($db->quoteName('#__componentbuilder_site_view'));
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
				$query->update($db->quoteName('#__componentbuilder_site_view'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}
	}
}
