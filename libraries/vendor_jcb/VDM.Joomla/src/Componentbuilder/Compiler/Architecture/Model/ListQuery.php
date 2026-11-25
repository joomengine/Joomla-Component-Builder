<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    17th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Model;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Field\DatabaseName as FieldDatabaseName;
use VDM\Joomla\Componentbuilder\Compiler\Builder\AccessSwitch;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Category;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\FieldNames;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ViewsDefaultOrdering;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ListQueryInterface;


/**
 * Model List Query Class.
 * 
 * Builds the getListQuery method of an admin list view model: the select and
 * from clauses, the access join and view level guard, the category join, the
 * search and filter clauses, and the ordering.
 * 
 * Only how the user and the database are put in scope differs between Joomla
 * targets, so those are the extension points the target variants override.
 * 
 * @since  6.1.7
 */
class ListQuery implements ListQueryInterface
{
	/**
	 * Category table construction expression for the generated target.
	 *
	 * @var   string
	 * @since 6.2.0
	 */
	protected string $categoryTable = "Table::getInstance('Category', 'JTable')";

	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 6.1.7
	 */
	protected Dispenser $dispenser;

	/**
	 * The Field Database Name Class.
	 *
	 * @var   FieldDatabaseName
	 * @since 6.1.7
	 */
	protected FieldDatabaseName $fielddatabasename;

	/**
	 * The Custom Query Class.
	 *
	 * @var   CustomQuery
	 * @since 6.1.7
	 */
	protected CustomQuery $customquery;

	/**
	 * The Search Query Class.
	 *
	 * @var   SearchQuery
	 * @since 6.1.7
	 */
	protected SearchQuery $searchquery;

	/**
	 * The Filter Query Class.
	 *
	 * @var   FilterQuery
	 * @since 6.1.7
	 */
	protected FilterQuery $filterquery;

	/**
	 * The Access Switch Class.
	 *
	 * @var   AccessSwitch
	 * @since 6.1.7
	 */
	protected AccessSwitch $accessswitch;

	/**
	 * The Category Class.
	 *
	 * @var   Category
	 * @since 6.1.7
	 */
	protected Category $category;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * The Field Names Class.
	 *
	 * @var   FieldNames
	 * @since 6.1.7
	 */
	protected FieldNames $fieldnames;

	/**
	 * The Views Default Ordering Class.
	 *
	 * @var   ViewsDefaultOrdering
	 * @since 6.1.7
	 */
	protected ViewsDefaultOrdering $viewsdefaultordering;

	/**
	 * Fresh-query method emitted by this target renderer.
	 *
	 * @var   string
	 * @since 6.2.0
	 */
	protected string $queryMethod = 'getQuery(true)';

	/**
	 * Constructor.
	 *
	 * @param Config                $config                 The Config Class.
	 * @param Dispenser             $dispenser              The Dispenser Class.
	 * @param FieldDatabaseName     $fielddatabasename      The Field Database Name Class.
	 * @param CustomQuery           $customquery            The Custom Query Class.
	 * @param SearchQuery           $searchquery            The Search Query Class.
	 * @param FilterQuery           $filterquery            The Filter Query Class.
	 * @param AccessSwitch          $accessswitch           The Access Switch Class.
	 * @param Category              $category               The Category Class.
	 * @param ContentOne            $contentone             The ContentOne Class.
	 * @param FieldNames            $fieldnames             The Field Names Class.
	 * @param ViewsDefaultOrdering  $viewsdefaultordering   The Views Default Ordering Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Dispenser $dispenser,
		FieldDatabaseName $fielddatabasename,
		CustomQuery $customquery,
		SearchQuery $searchquery,
		FilterQuery $filterquery,
		AccessSwitch $accessswitch,
		Category $category,
		ContentOne $contentone,
		FieldNames $fieldnames,
		ViewsDefaultOrdering $viewsdefaultordering)
	{
		$this->config = $config;
		$this->dispenser = $dispenser;
		$this->fielddatabasename = $fielddatabasename;
		$this->customquery = $customquery;
		$this->searchquery = $searchquery;
		$this->filterquery = $filterquery;
		$this->accessswitch = $accessswitch;
		$this->category = $category;
		$this->contentone = $contentone;
		$this->fieldnames = $fieldnames;
		$this->viewsdefaultordering = $viewsdefaultordering;
	}

	/**
	 * Build the getListQuery method of an admin list view model.
	 *
	 * @param   string  $nameSingleCode  The single view code name.
	 * @param   string  $nameListCode    The list view code name.
	 *
	 * @return  string
	 *
	 * @since   6.1.7
	 */
	public function get(&$nameSingleCode, &$nameListCode)
	{
		// check if this view has category added
		if ($this->category->exists("{$nameListCode}.code"))
		{
			$categoryCodeName = $this->category->get("{$nameListCode}.code");
			$addCategory      = true;
			$addCategoryFilter
				= $this->category->get("{$nameListCode}.filter", 'error');
		}
		else
		{
			$addCategory       = false;
			$addCategoryFilter = 0;
		}
		// setup the query
		$query = "//" . Line::_(__Line__, __Class__) . " Get the user object.";
		$query .= $this->getUserObject();
		$query .= PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Create a new query object.";
		$query .= $this->getDatabaseObject();
		$query .= PHP_EOL . Indent::_(2) . "\$query = \$db->{$this->queryMethod};";
		$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " Select some fields";
		$query .= PHP_EOL . Indent::_(2) . "\$query->select('a.*');";
		// add the category
		if ($addCategory)
		{
			$query .= PHP_EOL . Indent::_(2)
				. "\$query->select(\$db->quoteName('c.title','category_title'));";
		}
		$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " From the " . $this->config->component_code_name . "_item table";
		$query .= PHP_EOL . Indent::_(2) . "\$query->from(\$db->quoteName('#__"
			. $this->config->component_code_name . "_" . $nameSingleCode . "', 'a'));";
		// add the category
		if ($addCategory)
		{
			$query .= PHP_EOL . Indent::_(2)
				. "\$query->join('LEFT', \$db->quoteName('#__categories', 'c') . ' ON (' . \$db->quoteName('a."
				. $categoryCodeName
				. "') . ' = ' . \$db->quoteName('c.id') . ')');";
		}
		// add custom filtering php
		$query .= $this->dispenser->get(
			'php_getlistquery', $nameSingleCode, PHP_EOL . PHP_EOL
		);
		// add the custom fields query
		$query .= $this->customquery->get($nameListCode, $nameSingleCode);
		$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " Filter by published state";
		$query .= PHP_EOL . Indent::_(2)
			. "\$published = \$this->getState('filter.published');";
		$query .= PHP_EOL . Indent::_(2) . "if (is_numeric(\$published))";
		$query .= PHP_EOL . Indent::_(2) . "{";
		$query .= PHP_EOL . Indent::_(3)
			. "\$query->where('a.published = ' . (int) \$published);";
		$query .= PHP_EOL . Indent::_(2) . "}";
		$query .= PHP_EOL . Indent::_(2) . "elseif (\$published === '')";
		$query .= PHP_EOL . Indent::_(2) . "{";
		$query .= PHP_EOL . Indent::_(3)
			. "\$query->where('(a.published = 0 OR a.published = 1)');";
		$query .= PHP_EOL . Indent::_(2) . "}";
		if ($this->accessswitch->exists($nameSingleCode))
		{
			$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Join over the asset groups.";
			$query .= PHP_EOL . Indent::_(2)
				. "\$query->select('ag.title AS access_level');";
			$query .= PHP_EOL . Indent::_(2)
				. "\$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');";
			// check if the access field was over ridden
			if (!$this->fieldnames->isString($nameSingleCode . '.access'))
			{
				// component helper name
				$Helper = $this->contentone->get('Component') . 'Helper';
				// load the access filter query code
				$query .= PHP_EOL . Indent::_(2) . "//" . Line::_(
						__LINE__,__CLASS__
					)
					. " Filter by access level.";
				$query .= PHP_EOL . Indent::_(2)
					. "\$_access = \$this->getState('filter.access');";
				$query .= PHP_EOL . Indent::_(2)
					. "if (\$_access && is_numeric(\$_access))";
				$query .= PHP_EOL . Indent::_(2) . "{";
				$query .= PHP_EOL . Indent::_(3)
					. "\$query->where('a.access = ' . (int) \$_access);";
				$query .= PHP_EOL . Indent::_(2) . "}";
				$query .= PHP_EOL . Indent::_(2) . "elseif ("
					. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$_access))";
				$query .= PHP_EOL . Indent::_(2) . "{";
				$query .= PHP_EOL . Indent::_(3) . "//"
					. Line::_(__Line__, __Class__)
					. " Secure the array for the query";
				$query .= PHP_EOL . Indent::_(3)
					. "\$_access = ArrayHelper::toInteger(\$_access);";
				$query .= PHP_EOL . Indent::_(3) . "//"
					. Line::_(__Line__, __Class__) . " Filter by the Access Array.";
				$query .= PHP_EOL . Indent::_(3)
					. "\$query->where('a.access IN (' . implode(',', \$_access) . ')');";
				$query .= PHP_EOL . Indent::_(2) . "}";
			}
			// TODO the following will fight against the above access filter
			$query .= PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Implement View Level Access";
			$query .= PHP_EOL . Indent::_(2)
				. "if (!\$user->authorise('core.options', 'com_"
				. $this->config->component_code_name . "'))";
			$query .= PHP_EOL . Indent::_(2) . "{";
			$query .= PHP_EOL . Indent::_(3)
				. "\$groups = implode(',', \$user->getAuthorisedViewLevels());";
			$query .= PHP_EOL . Indent::_(3)
				. "\$query->where('a.access IN (' . \$groups . ')');";
			$query .= PHP_EOL . Indent::_(2) . "}";
		}
		// set the search query
		$query .= $this->searchquery->get($nameListCode);
		// set other filters
		$query .= $this->filterquery->get($nameListCode);
		// add the category
		if ($addCategory && $addCategoryFilter >= 1)
		{
			$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Filter by a single or group of categories.";
			$query .= PHP_EOL . Indent::_(2) . "\$baselevel = 1;";
			$query .= PHP_EOL . Indent::_(2)
				. "\$categoryId = \$this->getState('filter.category_id');";
			$query .= PHP_EOL;
			$query .= PHP_EOL . Indent::_(2) . "if (is_numeric(\$categoryId))";
			$query .= PHP_EOL . Indent::_(2) . "{";
			$query .= PHP_EOL . Indent::_(3)
				. "\$cat_tbl = " . $this->categoryTable . ";";
			$query .= PHP_EOL . Indent::_(3) . "\$cat_tbl->load(\$categoryId);";
			$query .= PHP_EOL . Indent::_(3) . "\$rgt = \$cat_tbl->rgt;";
			$query .= PHP_EOL . Indent::_(3) . "\$lft = \$cat_tbl->lft;";
			$query .= PHP_EOL . Indent::_(3)
				. "\$baselevel = (int) \$cat_tbl->level;";
			$query .= PHP_EOL . Indent::_(3)
				. "\$query->where('c.lft >= ' . (int) \$lft)";
			$query .= PHP_EOL . Indent::_(4)
				. "->where('c.rgt <= ' . (int) \$rgt);";
			$query .= PHP_EOL . Indent::_(2) . "}";
			$query .= PHP_EOL . Indent::_(2)
				. "elseif (is_array(\$categoryId))";
			$query .= PHP_EOL . Indent::_(2) . "{";
			$query .= PHP_EOL . Indent::_(3)
				. "\$categoryId = ArrayHelper::toInteger(\$categoryId);";
			$query .= PHP_EOL . Indent::_(3)
				. "\$categoryId = implode(',', \$categoryId);";
			$query .= PHP_EOL . Indent::_(3)
				. "\$query->where('a." . $categoryCodeName
				. " IN (' . \$categoryId . ')');";
			$query .= PHP_EOL . Indent::_(2) . "}";
			$query .= PHP_EOL;
		}
		// setup values for the view ordering
		// add dynamic ordering (Admin view)
		if ($this->viewsdefaultordering->
			get("$nameListCode.add_admin_ordering", 0) == 1)
		{
			// the first is from the state
			$order_first = true;
			foreach ($this->viewsdefaultordering->
				get("$nameListCode.admin_ordering_fields", []) as $order_field)
			{
				if (($order_field_name = $this->fielddatabasename->get(
						$nameListCode, $order_field['field']
					)) !== false)
				{
					if ($order_first)
					{
						// just the first field is based on state
						$order_first = false;
						$query       .= PHP_EOL . PHP_EOL . Indent::_(2) . "//"
							. Line::_(
								__LINE__,__CLASS__
							) . " Add the list ordering clause.";
						$query       .= PHP_EOL . Indent::_(2)
							. "\$orderCol = \$this->getState('list.ordering', '"
							. $order_field_name . "');";
						$query       .= PHP_EOL . Indent::_(2)
							. "\$orderDirn = \$this->getState('list.direction', '"
							. $order_field['direction'] . "');";
						$query       .= PHP_EOL . Indent::_(2)
							. "if (\$orderCol != '')";
						$query       .= PHP_EOL . Indent::_(2) . "{";
						$query       .= PHP_EOL . Indent::_(3) . "//" . Line::_(__LINE__,__CLASS__
							) . " Check that the order direction is valid encase we have a field called direction as part of filers.";
						$query .= PHP_EOL . Indent::_(3)
							. "\$orderDirn = (is_string(\$orderDirn) && in_array(strtolower(\$orderDirn), ['asc', 'desc'])) ? \$orderDirn : '"
							. $order_field['direction'] . "';";
						$query       .= PHP_EOL . Indent::_(3)
							. "\$query->order(\$db->escape(\$orderCol . ' ' . \$orderDirn));";
						$query       .= PHP_EOL . Indent::_(2) . "}";
					}
					else
					{
						$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//"
							. Line::_(
								__LINE__,__CLASS__
							) . " Add a permanent list ordering.";
						$query .= PHP_EOL . Indent::_(2)
							. "\$query->order(\$db->escape('"
							. $order_field_name . " "
							. $order_field['direction'] . "'));";
					}
				}
			}
		}
		else
		{
			$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Add the list ordering clause.";
			$query .= PHP_EOL . Indent::_(2)
				. "\$orderCol = \$this->getState('list.ordering', 'a.id');";
			$query .= PHP_EOL . Indent::_(2)
				. "\$orderDirn = \$this->getState('list.direction', 'desc');";
			$query .= PHP_EOL . Indent::_(2) . "if (\$orderCol != '')";
			$query .= PHP_EOL . Indent::_(2) . "{";
			$query       .= PHP_EOL . Indent::_(3) . "//" . Line::_(__LINE__,__CLASS__
				) . " Check that the order direction is valid encase we have a field called direction as part of filers.";
			$query .= PHP_EOL . Indent::_(3)
				. "\$orderDirn = (is_string(\$orderDirn) && in_array(strtolower(\$orderDirn), ['asc', 'desc'])) ? \$orderDirn : 'desc';";
			$query .= PHP_EOL . Indent::_(3)
				. "\$query->order(\$db->escape(\$orderCol . ' ' . \$orderDirn));";
			$query .= PHP_EOL . Indent::_(2) . "}";
		}
		$query .= PHP_EOL;
		$query .= PHP_EOL . Indent::_(2) . "return \$query;";

		return $query;
	}

	/**
	 * Get the statement that puts the current user in scope.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getUserObject(): string
	{
		return PHP_EOL . Indent::_(2) . "\$user = \$this->getCurrentUser();";
	}

	/**
	 * Get the statement that puts the database in scope.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getDatabaseObject(): string
	{
		return PHP_EOL . Indent::_(2) . "\$db = \$this->getDatabase();";
	}
}

