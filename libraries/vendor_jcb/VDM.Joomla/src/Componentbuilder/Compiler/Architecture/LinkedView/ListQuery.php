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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\LinkedView;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Field\DatabaseName;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\CustomQuery;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ItemsStringFixInterface as ItemsStringFix;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\SelectionTranslation;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\SelectionTranslationMethod;
use VDM\Joomla\Componentbuilder\Compiler\Builder\AccessSwitch;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Category;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\FieldNames;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ViewsDefaultOrdering;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\LinkedView\ListQueryInterface;


/**
 * Linked View List Query Class.
 * 
 * Generates the getter a parent view's model uses to load the items of a
 * linked view: the query itself, the access joins, the ordering, and the
 * post-load filtering by the key that ties the two views together.
 * 
 * That key may be a plain column, a value inside a repeatable field, a
 * value inside an array field, or several columns joined by OR, so the
 * filtering is built from whichever spelling the link carries.
 * 
 * Only how the user and the database are obtained differs between Joomla
 * targets, so those are the extension points the target variants override.
 * 
 * @since  6.1.7
 */
class ListQuery implements ListQueryInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Customcode Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 6.1.7
	 */
	protected Dispenser $dispenser;

	/**
	 * The Field Database Name Class.
	 *
	 * @var   DatabaseName
	 * @since 6.1.7
	 */
	protected DatabaseName $fielddatabasename;

	/**
	 * The Custom Query Class.
	 *
	 * @var   CustomQuery
	 * @since 6.1.7
	 */
	protected CustomQuery $customquery;

	/**
	 * The Items String Fix Class.
	 *
	 * @var   ItemsStringFix
	 * @since 6.1.7
	 */
	protected ItemsStringFix $itemsstringfix;

	/**
	 * The Selection Translation Class.
	 *
	 * @var   SelectionTranslation
	 * @since 6.1.7
	 */
	protected SelectionTranslation $selectiontranslation;

	/**
	 * The Selection Translation Method Class.
	 *
	 * @var   SelectionTranslationMethod
	 * @since 6.1.7
	 */
	protected SelectionTranslationMethod $selectiontranslationmethod;

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
	 * @param Config                       $config                       The Config Class.
	 * @param Dispenser                    $dispenser                    The Customcode Dispenser Class.
	 * @param DatabaseName                 $fielddatabasename            The Field Database Name Class.
	 * @param CustomQuery                  $customquery                  The Custom Query Class.
	 * @param ItemsStringFix               $itemsstringfix               The Items String Fix Class.
	 * @param SelectionTranslation         $selectiontranslation         The Selection Translation Class.
	 * @param SelectionTranslationMethod   $selectiontranslationmethod   The Selection Translation Method Class.
	 * @param AccessSwitch                 $accessswitch                 The Access Switch Class.
	 * @param Category                     $category                     The Category Class.
	 * @param ContentOne                   $contentone                   The ContentOne Class.
	 * @param FieldNames                   $fieldnames                   The Field Names Class.
	 * @param ViewsDefaultOrdering         $viewsdefaultordering         The Views Default Ordering Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Dispenser $dispenser,
		DatabaseName $fielddatabasename,
		CustomQuery $customquery,
		ItemsStringFix $itemsstringfix,
		SelectionTranslation $selectiontranslation,
		SelectionTranslationMethod $selectiontranslationmethod,
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
		$this->itemsstringfix = $itemsstringfix;
		$this->selectiontranslation = $selectiontranslation;
		$this->selectiontranslationmethod = $selectiontranslationmethod;
		$this->accessswitch = $accessswitch;
		$this->category = $category;
		$this->contentone = $contentone;
		$this->fieldnames = $fieldnames;
		$this->viewsdefaultordering = $viewsdefaultordering;
	}

	/**
	 * Get the linked view getter of a model.
	 *
	 * @param   string  $nameSingleCode  The single view code name.
	 * @param   string  $nameListCode    The list view code name.
	 * @param   string  $functionName    The generated method name suffix.
	 * @param   string  $key             The key of the linked view.
	 * @param   string  $_key            The plain key column.
	 * @param   string  $parentKey       The key of the parent view.
	 * @param   string  $parent_key      The plain parent key column.
	 * @param   mixed   $globalKey       The property the parent exposes the key on.
	 *
	 * @return  string  The generated getter.
	 *
	 * @since   6.1.7
	 */
	public function get($nameSingleCode, $nameListCode,
		$functionName, $key, $_key, $parentKey, $parent_key, $globalKey)
	{
		// check if this view has category added
		if ($this->category->exists("{$nameListCode}.code"))
		{
			$categoryCodeName = $this->category->get("{$nameListCode}.code");
			$addCategory      = true;
		}
		else
		{
			$addCategory = false;
		}
		$query = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
		$query .= PHP_EOL . Indent::_(1) . " * Method to get list data.";
		$query .= PHP_EOL . Indent::_(1) . " *";
		$query .= PHP_EOL . Indent::_(1)
			. " * @return mixed  An array of data items on success, false on failure.";
		$query .= PHP_EOL . Indent::_(1) . " */";
		$query .= PHP_EOL . Indent::_(1) . "public function get" . $functionName
			. "()";
		$query .= PHP_EOL . Indent::_(1) . "{";
		// setup the query
		$query .= PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Get the user object.";
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
			) . " From the " . $this->config->component_code_name . "_"
			. $nameSingleCode
			. " table";
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
		if (StringHelper::check($globalKey) && $key
			&& strpos(
				(string) $key, '-R>'
			) === false
			&& strpos((string) $key, '-A>') === false
			&& strpos((string) $key, '-OR>') === false
			&& $parentKey
			&& strpos((string) $parentKey, '-R>') === false
			&& strpos((string) $parentKey, '-A>') === false
			&& strpos((string) $parentKey, '-OR>') === false)
		{
			$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Filter by " . $globalKey . " global.";
			$query .= PHP_EOL . Indent::_(2) . "\$" . $globalKey . " = \$this->"
				. $globalKey . ";";
			$query .= PHP_EOL . Indent::_(2) . "if (is_numeric(\$" . $globalKey
				. " ))";
			$query .= PHP_EOL . Indent::_(2) . "{";
			$query .= PHP_EOL . Indent::_(3) . "\$query->where('a." . $key
				. " = ' . (int) \$" . $globalKey . " );";
			$query .= PHP_EOL . Indent::_(2) . "}";
			$query .= PHP_EOL . Indent::_(2) . "elseif (is_string(\$"
				. $globalKey . "))";
			$query .= PHP_EOL . Indent::_(2) . "{";
			$query .= PHP_EOL . Indent::_(3) . "\$query->where('a." . $key
				. " = ' . \$db->quote(\$" . $globalKey . "));";
			$query .= PHP_EOL . Indent::_(2) . "}";
			$query .= PHP_EOL . Indent::_(2) . "else";
			$query .= PHP_EOL . Indent::_(2) . "{";
			$query .= PHP_EOL . Indent::_(3) . "\$query->where('a." . $key
				. " = -5');";
			$query .= PHP_EOL . Indent::_(2) . "}";
		}
		elseif (strpos((string) $parentKey, '-OR>') !== false
			|| strpos((string) $key, '-OR>') !== false)
		{
			// get both strings
			if (strpos((string) $key, '-OR>') !== false)
			{
				$ORarray = explode('-OR>', (string) $key);
			}
			else
			{
				$ORarray = array($key);
			}
			// make sure we have an array
			if (!ArrayHelper::check($globalKey))
			{
				$globalKey = array($globalKey);
			}
			// now load the query (this may be to much... but hey let it write the code :)
			foreach ($globalKey as $_globalKey)
			{
				// now build the query
				$ORquery = array('s' => array(), 'i' => array());
				foreach ($ORarray as $ORkey)
				{
					$ORquery['i'][] = "a." . $ORkey . " = ' . (int) \$"
						. $_globalKey;
					$ORquery['s'][] = "a." . $ORkey . " = ' . \$db->quote(\$"
						. $_globalKey . ")";
				}
				$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//"
					. Line::_(__Line__, __Class__) . " Filter by " . $_globalKey
					. " global.";
				$query .= PHP_EOL . Indent::_(2) . "\$" . $_globalKey
					. " = \$this->" . $_globalKey . ";";
				$query .= PHP_EOL . Indent::_(2) . "if (is_numeric(\$"
					. $_globalKey . " ))";
				$query .= PHP_EOL . Indent::_(2) . "{";
				$query .= PHP_EOL . Indent::_(3) . "\$query->where('" . implode(
						" . ' OR ", $ORquery['i']
					) . ", ' OR');";
				$query .= PHP_EOL . Indent::_(2) . "}";
				$query .= PHP_EOL . Indent::_(2) . "elseif (is_string(\$"
					. $_globalKey . "))";
				$query .= PHP_EOL . Indent::_(2) . "{";
				$query .= PHP_EOL . Indent::_(3) . "\$query->where('" . implode(
						" . ' OR ", $ORquery['s']
					) . ", ' OR');";
				$query .= PHP_EOL . Indent::_(2) . "}";
				$query .= PHP_EOL . Indent::_(2) . "else";
				$query .= PHP_EOL . Indent::_(2) . "{";
				$query .= PHP_EOL . Indent::_(3) . "\$query->where('a." . $ORkey
					. " = -5');";
				$query .= PHP_EOL . Indent::_(2) . "}";
			}
		}
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
		// add dynamic ordering (Linked view)
		if ($this->viewsdefaultordering->
			get("$nameListCode.add_linked_ordering", 0) == 1)
		{
			foreach ($this->viewsdefaultordering->
				get("$nameListCode.linked_ordering_fields", []) as $order_field)
			{
				// We Removed This 'listJoinBuilder' as targetArea
				// we will keep an eye on this
				$order_field_name = $this->fielddatabasename->get(
						$nameListCode, $order_field['field']
				);

				if (!empty($order_field_name))
				{
					// default ordering is by publish and ordering
					$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//"
						. Line::_(
							__LINE__,__CLASS__
						) . " Order the results by ordering";
					$query .= PHP_EOL . Indent::_(2)
						. "\$query->order('"
						. $order_field_name . " " . $order_field['direction']
						. "');";
				}
			}
		}
		else
		{
			// default ordering is by publish and ordering
			$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Order the results by ordering";
			$query .= PHP_EOL . Indent::_(2)
				. "\$query->order('a.published  ASC');";
			$query .= PHP_EOL . Indent::_(2)
				. "\$query->order('a.ordering  ASC');";
		}
		$query .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " Load the items";
		$query .= PHP_EOL . Indent::_(2) . "\$db->setQuery(\$query);";
		$query .= PHP_EOL . Indent::_(2) . "\$db->execute();";
		$query .= PHP_EOL . Indent::_(2) . "if (\$db->getNumRows())";
		$query .= PHP_EOL . Indent::_(2) . "{";
		$query .= PHP_EOL . Indent::_(3) . "\$items = \$db->loadObjectList();";
		// add the fixing strings method
		$query .= $this->itemsstringfix->get(
			$nameSingleCode, $nameListCode,
			$this->contentone->get('Component'),
			Indent::_(1)
		);
		// add translations
		$query .= $this->selectiontranslation->get(
			$nameListCode, Indent::_(1)
		);
		// filter by child repetable field values
		if (StringHelper::check($globalKey) && $key
			&& strpos(
				(string) $key, '-R>'
			) !== false
			&& strpos((string) $key, '-A>') === false)
		{
			list($field, $target) = explode('-R>', (string) $key);
			$query .= PHP_EOL . PHP_EOL . Indent::_(3) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Filter by " . $globalKey . " in this Repetable Field";
			$query .= PHP_EOL . Indent::_(3) . "if ("
				. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$items) && isset(\$this->"
				. $globalKey . "))";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= PHP_EOL . Indent::_(4)
				. "foreach (\$items as \$nr => &\$item)";
			$query .= PHP_EOL . Indent::_(4) . "{";
			$query .= PHP_EOL . Indent::_(5) . "if (isset(\$item->" . $field
				. ") && Super_" . "__4b225c51_d293_48e4_b3f6_5136cf5c3f18___Power::check(\$item->" . $field . "))";
			$query .= PHP_EOL . Indent::_(5) . "{";
			$query .= PHP_EOL . Indent::_(6)
				. "\$tmpArray = json_decode(\$item->" . $field . ",true);";
			$query .= PHP_EOL . Indent::_(6) . "if (!isset(\$tmpArray['"
				. $target . "']) || !Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$tmpArray['"
				. $target . "']) || !in_array(\$this->" . $globalKey
				. ", \$tmpArray['" . $target . "']))";
			$query .= PHP_EOL . Indent::_(6) . "{";
			$query .= PHP_EOL . Indent::_(7) . "unset(\$items[\$nr]);";
			$query .= PHP_EOL . Indent::_(7) . "continue;";
			$query .= PHP_EOL . Indent::_(6) . "}";
			$query .= PHP_EOL . Indent::_(5) . "}";
			$query .= PHP_EOL . Indent::_(5) . "else";
			$query .= PHP_EOL . Indent::_(5) . "{";
			$query .= PHP_EOL . Indent::_(6) . "unset(\$items[\$nr]);";
			$query .= PHP_EOL . Indent::_(6) . "continue;";
			$query .= PHP_EOL . Indent::_(5) . "}";
			$query .= PHP_EOL . Indent::_(4) . "}";
			$query .= PHP_EOL . Indent::_(3) . "}";
			$query .= PHP_EOL . Indent::_(3) . "else";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= PHP_EOL . Indent::_(4) . "return false;";
			$query .= PHP_EOL . Indent::_(3) . "}";
		}
		// filter by child array field values
		if (StringHelper::check($globalKey) && $key
			&& strpos(
				(string) $key, '-R>'
			) === false
			&& strpos((string) $key, '-A>') !== false)
		{
			$query .= PHP_EOL . PHP_EOL . Indent::_(3) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Filter by " . $globalKey . " Array Field";
			$query .= PHP_EOL . Indent::_(3) . "\$" . $globalKey . " = \$this->"
				. $globalKey . ";";
			$query .= PHP_EOL . Indent::_(3) . "if ("
				. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$items) && \$" . $globalKey
				. ")";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= PHP_EOL . Indent::_(4)
				. "foreach (\$items as \$nr => &\$item)";
			$query .= PHP_EOL . Indent::_(4) . "{";
			list($bin, $target) = explode('-A>', (string) $key);
			if (StringHelper::check($target))
			{
				$query .= PHP_EOL . Indent::_(5) . "if (isset(\$item->" . $target
					. ") && Super_" . "__4b225c51_d293_48e4_b3f6_5136cf5c3f18___Power::check(\$item->" . $target . "))";
				$query .= PHP_EOL . Indent::_(5) . "{";
				$query .= PHP_EOL . Indent::_(6) . "\$item->" . $target
					. " = json_decode(\$item->" . $target . ", true);";
				$query .= PHP_EOL . Indent::_(5) . "}";
				$query .= PHP_EOL . Indent::_(5) . "elseif (!isset(\$item->"
					. $target . ") || !Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$item->"
					. $target . "))";
				$query .= PHP_EOL . Indent::_(5) . "{";
				$query .= PHP_EOL . Indent::_(6) . "unset(\$items[\$nr]);";
				$query .= PHP_EOL . Indent::_(6) . "continue;";
				$query .= PHP_EOL . Indent::_(5) . "}";
				$query .= PHP_EOL . Indent::_(5) . "if (!in_array(\$"
					. $globalKey . ",\$item->" . $target . "))";
			}
			else
			{
				$query .= PHP_EOL . Indent::_(5) . "if (isset(\$item->" . $_key . ") && "
					. "Super_" . "__4b225c51_d293_48e4_b3f6_5136cf5c3f18___Power::check(\$item->" . $_key . "))";
				$query .= PHP_EOL . Indent::_(5) . "{";
				$query .= PHP_EOL . Indent::_(6) . "\$item->" . $_key
					. " = json_decode(\$item->" . $_key . ", true);";
				$query .= PHP_EOL . Indent::_(5) . "}";
				$query .= PHP_EOL . Indent::_(5) . "elseif (!isset(\$item->"
					. $_key . ") || !Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$item->"
					. $_key . "))";
				$query .= PHP_EOL . Indent::_(5) . "{";
				$query .= PHP_EOL . Indent::_(6) . "unset(\$items[\$nr]);";
				$query .= PHP_EOL . Indent::_(6) . "continue;";
				$query .= PHP_EOL . Indent::_(5) . "}";
				$query .= PHP_EOL . Indent::_(5) . "if (!in_array(\$"
					. $globalKey . ",\$item->" . $_key . "))";
			}
			$query .= PHP_EOL . Indent::_(5) . "{";
			$query .= PHP_EOL . Indent::_(6) . "unset(\$items[\$nr]);";
			$query .= PHP_EOL . Indent::_(6) . "continue;";
			$query .= PHP_EOL . Indent::_(5) . "}";
			$query .= PHP_EOL . Indent::_(4) . "}";
			$query .= PHP_EOL . Indent::_(3) . "}";
			$query .= PHP_EOL . Indent::_(3) . "else";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= PHP_EOL . Indent::_(4) . "return false;";
			$query .= PHP_EOL . Indent::_(3) . "}";
		}
		// filter by parent repetable field values
		if (StringHelper::check($globalKey) && $key
			&& strpos(
				(string) $parentKey, '-R>'
			) !== false
			&& strpos((string) $parentKey, '-A>') === false)
		{
			list($bin, $target) = explode('-R>', (string) $parentKey);
			$query .= PHP_EOL . PHP_EOL . Indent::_(3) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Filter by " . $_key . " Repetable Field";
			$query .= PHP_EOL . Indent::_(3) . "\$" . $globalKey
				. " = json_decode(\$this->" . $globalKey . ",true);";
			$query .= PHP_EOL . Indent::_(3) . "if ("
				. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$items) && isset(\$"
				. $globalKey . ") && Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$"
				. $globalKey . "))";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= PHP_EOL . Indent::_(4)
				. "foreach (\$items as \$nr => &\$item)";
			$query .= PHP_EOL . Indent::_(4) . "{";
			$query .= PHP_EOL . Indent::_(5) . "if (\$item->" . $_key
				. " && isset(\$" . $globalKey . "['" . $target . "']) && "
				. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$" . $globalKey . "['"
				. $target . "']))";
			$query .= PHP_EOL . Indent::_(5) . "{";
			$query .= PHP_EOL . Indent::_(6) . "if (!in_array(\$item->" . $_key
				. ",\$" . $globalKey . "['" . $target . "']))";
			$query .= PHP_EOL . Indent::_(6) . "{";
			$query .= PHP_EOL . Indent::_(7) . "unset(\$items[\$nr]);";
			$query .= PHP_EOL . Indent::_(7) . "continue;";
			$query .= PHP_EOL . Indent::_(6) . "}";
			$query .= PHP_EOL . Indent::_(5) . "}";
			$query .= PHP_EOL . Indent::_(5) . "else";
			$query .= PHP_EOL . Indent::_(5) . "{";
			$query .= PHP_EOL . Indent::_(6) . "unset(\$items[\$nr]);";
			$query .= PHP_EOL . Indent::_(6) . "continue;";
			$query .= PHP_EOL . Indent::_(5) . "}";
			$query .= PHP_EOL . Indent::_(4) . "}";
			$query .= PHP_EOL . Indent::_(3) . "}";
			$query .= PHP_EOL . Indent::_(3) . "else";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= PHP_EOL . Indent::_(4) . "return false;";
			$query .= PHP_EOL . Indent::_(3) . "}";
		}
		// filter by parent array field values
		if (StringHelper::check($globalKey) && $key
			&& strpos(
				(string) $parentKey, '-R>'
			) === false
			&& strpos((string) $parentKey, '-A>') !== false)
		{
			$query .= PHP_EOL . PHP_EOL . Indent::_(3) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Filter by " . $globalKey . " Array Field";
			$query .= PHP_EOL . Indent::_(3) . "\$" . $globalKey . " = \$this->"
				. $globalKey . ";";
			$query .= PHP_EOL . Indent::_(3) . "if ("
				. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$items) && "
				. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$" . $globalKey . "))";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= PHP_EOL . Indent::_(4)
				. "foreach (\$items as \$nr => &\$item)";
			$query .= PHP_EOL . Indent::_(4) . "{";
			list($bin, $target) = explode('-A>', (string) $parentKey);
			if (StringHelper::check($target))
			{
				$query .= PHP_EOL . Indent::_(5) . "if (\$item->" . $_key
					. " && Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$" . $globalKey . "['"
					. $target . "']))";
				$query .= PHP_EOL . Indent::_(5) . "{";
				$query .= PHP_EOL . Indent::_(6) . "if (!in_array(\$item->"
					. $_key . ",\$" . $globalKey . "['" . $target . "']))";
			}
			else
			{
				$query .= PHP_EOL . Indent::_(5) . "if (\$item->" . $_key . ")";
				$query .= PHP_EOL . Indent::_(5) . "{";
				$query .= PHP_EOL . Indent::_(6) . "if (!in_array(\$item->"
					. $_key . ",\$" . $globalKey . "))";
			}
			$query .= PHP_EOL . Indent::_(6) . "{";
			$query .= PHP_EOL . Indent::_(7) . "unset(\$items[\$nr]);";
			$query .= PHP_EOL . Indent::_(7) . "continue;";
			$query .= PHP_EOL . Indent::_(6) . "}";
			$query .= PHP_EOL . Indent::_(5) . "}";
			$query .= PHP_EOL . Indent::_(5) . "else";
			$query .= PHP_EOL . Indent::_(5) . "{";
			$query .= PHP_EOL . Indent::_(6) . "unset(\$items[\$nr]);";
			$query .= PHP_EOL . Indent::_(6) . "continue;";
			$query .= PHP_EOL . Indent::_(5) . "}";
			$query .= PHP_EOL . Indent::_(4) . "}";
			$query .= PHP_EOL . Indent::_(3) . "}";
			$query .= PHP_EOL . Indent::_(3) . "else";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= PHP_EOL . Indent::_(4) . "return false;";
			$query .= PHP_EOL . Indent::_(3) . "}";
		}
		// add custom php to getitems method after all
		$query .= $this->dispenser->get(
			'php_getitems_after_all', $nameSingleCode,
			PHP_EOL . PHP_EOL . Indent::_(1)
		);

		$query .= PHP_EOL . Indent::_(3) . "return \$items;";
		$query .= PHP_EOL . Indent::_(2) . "}";
		$query .= PHP_EOL . Indent::_(2) . "return false;";
		$query .= PHP_EOL . Indent::_(1) . "}";
		// SELECTIONTRANSLATIONFIXFUNC<<<DYNAMIC>>>
		$query .= $this->selectiontranslationmethod->get(
			$nameListCode
		);

		// fixe mothod name clash
		$query = str_replace(
			'selectionTranslation(',
			'selectionTranslation' . $functionName . '(', $query
		);

		return $query;
	}

	/**
	 * Get the statement that puts the user object in scope.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getUserObject(): string
	{
		return PHP_EOL . Indent::_(2)
			. "\$user = Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getIdentity();";
	}

	/**
	 * Get the statement that puts the database object in scope.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getDatabaseObject(): string
	{
		return PHP_EOL . Indent::_(2) . "\$db = \$this->getDatabase();";
	}
}

