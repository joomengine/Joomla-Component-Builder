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
use VDM\Joomla\Utilities\FormHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Form\Form;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Repositories List Model
 *
 * @since  1.6
 */
class RepositoriesModel extends ListModel
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
		'administrator/components/com_componentbuilder/assets/css/repositories.css'
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
				'a.organisation','organisation',
				'a.repository','repository',
				'a.target','target',
				'a.type','type',
				'a.base','base'
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

		// Create the "read write branch" filter
		$attributes = array(
			'name' => 'branch',
			'type' => 'list',
			'onchange' => 'this.form.submit();',
		);
		$options = [
			'' => '-  ' . Text::_('COM_COMPONENTBUILDER_SELECT_BRANCH_STATE') . '  -',
			'write' => Text::_('COM_COMPONENTBUILDER_WRITE_BRANCH_SET'),
			'no_write' => Text::_('COM_COMPONENTBUILDER_NO_WRITE_BRANCH_SET'),
			'read' => Text::_('COM_COMPONENTBUILDER_READ_BRANCH_SET'),
			'no_read' => Text::_('COM_COMPONENTBUILDER_NO_READ_BRANCH_SET'),
			'both' => Text::_('COM_COMPONENTBUILDER_BOTH_BRANCHES_SET'),
			'none' => Text::_('COM_COMPONENTBUILDER_NO_BRANCHES_SET')
		];

		$form->setField(FormHelper::xml($attributes, $options),'filter');
		$form->setValue(
			'branch',
			'filter',
			$this->state->get("filter.branch")
		);
		array_push($this->filter_fields, 'branch');

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

		$organisation = $this->getUserStateFromRequest($this->context . '.filter.organisation', 'filter_organisation');
		if ($formSubmited)
		{
			$organisation = $input->post->get('organisation');
			$this->setState('filter.organisation', $organisation);
		}

		$repository = $this->getUserStateFromRequest($this->context . '.filter.repository', 'filter_repository');
		if ($formSubmited)
		{
			$repository = $input->post->get('repository');
			$this->setState('filter.repository', $repository);
		}

		$target = $this->getUserStateFromRequest($this->context . '.filter.target', 'filter_target');
		if ($formSubmited)
		{
			$target = $input->post->get('target');
			$this->setState('filter.target', $target);
		}

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
		if ($formSubmited)
		{
			$type = $input->post->get('type');
			$this->setState('filter.type', $type);
		}

		$base = $this->getUserStateFromRequest($this->context . '.filter.base', 'filter_base');
		if ($formSubmited)
		{
			$base = $input->post->get('base');
			$this->setState('filter.base', $base);
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
				$access = ($user->authorise('repository.access', 'com_componentbuilder.repository.' . (int) $item->id) && $user->authorise('repository.access', 'com_componentbuilder'));
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
				// convert target
				$item->target = $this->selectionTranslation($item->target, 'target');
				// convert type
				$item->type = $this->selectionTranslation($item->type, 'type');
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
		// Array of target language strings
		if ($name === 'target')
		{
			$targetArray = array(
				0 => 'COM_COMPONENTBUILDER_REPOSITORY_SELECT_AN_OPTION',
				4 => 'COM_COMPONENTBUILDER_REPOSITORY_PACKAGES',
				3 => 'COM_COMPONENTBUILDER_REPOSITORY_FIELD_TYPES',
				2 => 'COM_COMPONENTBUILDER_REPOSITORY_JOOMLA_POWER',
				5 => 'COM_COMPONENTBUILDER_REPOSITORY_SNIPPETS',
				1 => 'COM_COMPONENTBUILDER_REPOSITORY_SUPER_POWER',
				6 => 'COM_COMPONENTBUILDER_REPOSITORY_REPOSITORIES'
			);
			// Now check if value is found in this array
			if (isset($targetArray[$value]) && StringHelper::check($targetArray[$value]))
			{
				return $targetArray[$value];
			}
		}
		// Array of type language strings
		if ($name === 'type')
		{
			$typeArray = array(
				0 => 'COM_COMPONENTBUILDER_REPOSITORY_SELECT_AN_OPTION',
				1 => 'COM_COMPONENTBUILDER_REPOSITORY_GITEA',
				2 => 'COM_COMPONENTBUILDER_REPOSITORY_GITHUB'
			);
			// Now check if value is found in this array
			if (isset($typeArray[$value]) && StringHelper::check($typeArray[$value]))
			{
				return $typeArray[$value];
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
		$query->from($db->quoteName('#__componentbuilder_repository', 'a'));

		// Filtering by "branch"
		$filterBranch = $this->state->get('filter.branch');

		// Ensure the filter value is a string and not empty
		if (is_string($filterBranch) && $filterBranch !== '')
		{
			$readBranch  = 'CHAR_LENGTH(TRIM(' . $db->quoteName('a.read_branch') . '))';
			$writeBranch = 'CHAR_LENGTH(TRIM(' . $db->quoteName('a.write_branch') . '))';

			switch ($filterBranch)
			{
				case 'both':
					// Both read_branch and write_branch must be set
					$query->where("{$readBranch} > 1");
					$query->where("{$writeBranch} > 1");
					break;

				case 'none':
					// Neither read_branch nor write_branch must be set
					$query->where("{$readBranch} = 0");
					$query->where("{$writeBranch} = 0");
					break;

				case 'read':
					// Only read_branch must be set
					$query->where("{$readBranch} > 1");
					break;

				case 'write':
					// Only write_branch must be set
					$query->where("{$writeBranch} > 1");
					break;

				case 'no_read':
					// Only read_branch must NOT be set
					$query->where("{$readBranch} = 0");
					break;

				case 'no_write':
					// Only write_branch must NOT be set
					$query->where("{$writeBranch} = 0");
					break;
			}
		}

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
				$query->where('(a.organisation LIKE '.$search.' OR a.repository LIKE '.$search.' OR a.target LIKE '.$search.' OR a.type LIKE '.$search.' OR a.base LIKE '.$search.' OR a.guid LIKE '.$search.' OR a.author_email LIKE '.$search.' OR a.author_name LIKE '.$search.' OR a.username LIKE '.$search.')');
			}
		}

		// Filter by Organisation.
		$_organisation = $this->getState('filter.organisation');
		if (is_numeric($_organisation))
		{
			if (is_float($_organisation))
			{
				$query->where('a.organisation = ' . (float) $_organisation);
			}
			else
			{
				$query->where('a.organisation = ' . (int) $_organisation);
			}
		}
		elseif (StringHelper::check($_organisation))
		{
			$query->where('a.organisation = ' . $db->quote($db->escape($_organisation)));
		}
		// Filter by Repository.
		$_repository = $this->getState('filter.repository');
		if (is_numeric($_repository))
		{
			if (is_float($_repository))
			{
				$query->where('a.repository = ' . (float) $_repository);
			}
			else
			{
				$query->where('a.repository = ' . (int) $_repository);
			}
		}
		elseif (StringHelper::check($_repository))
		{
			$query->where('a.repository = ' . $db->quote($db->escape($_repository)));
		}
		// Filter by Target.
		$_target = $this->getState('filter.target');
		if (is_numeric($_target))
		{
			if (is_float($_target))
			{
				$query->where('a.target = ' . (float) $_target);
			}
			else
			{
				$query->where('a.target = ' . (int) $_target);
			}
		}
		elseif (StringHelper::check($_target))
		{
			$query->where('a.target = ' . $db->quote($db->escape($_target)));
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
		// Filter by Base.
		$_base = $this->getState('filter.base');
		if (is_numeric($_base))
		{
			if (is_float($_base))
			{
				$query->where('a.base = ' . (float) $_base);
			}
			else
			{
				$query->where('a.base = ' . (int) $_base);
			}
		}
		elseif (StringHelper::check($_base))
		{
			$query->where('a.base = ' . $db->quote($db->escape($_base)));
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
		$id .= ':' . $this->getState('filter.organisation');
		$id .= ':' . $this->getState('filter.repository');
		$id .= ':' . $this->getState('filter.target');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.base');

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
			$query->from($db->quoteName('#__componentbuilder_repository'));
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
				$query->update($db->quoteName('#__componentbuilder_repository'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}
	}
}
