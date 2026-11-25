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


use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Field\DatabaseName as FieldDatabaseName;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Builder\AccessSwitch;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\EximportView;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ViewsDefaultOrdering;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ItemsStringFixInterface as ItemsStringFix;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ItemsMethodInterface;


/**
 * Model Items Method Class.
 * 
 * Builds the getItems or getExportData method of an admin list view model:
 * the query it runs, the access guard, the ordering, the string fixes applied
 * to every loaded item, and the export translations.
 * 
 * Only how the user and the database are put in scope differs between Joomla
 * targets, so those are the extension points the target variants override.
 * 
 * @since  6.1.7
 */
class ItemsMethod implements ItemsMethodInterface
{
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
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 6.1.7
	 */
	protected Placeholder $placeholder;

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
	 * The Access Switch Class.
	 *
	 * @var   AccessSwitch
	 * @since 6.1.7
	 */
	protected AccessSwitch $accessswitch;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * The Views Default Ordering Class.
	 *
	 * @var   ViewsDefaultOrdering
	 * @since 6.1.7
	 */
	protected ViewsDefaultOrdering $viewsdefaultordering;

	/**
	 * The Eximport View Class.
	 *
	 * @var   EximportView
	 * @since 6.1.7
	 */
	protected EximportView $eximportview;

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
	 * @param Placeholder           $placeholder            The Placeholder Class.
	 * @param FieldDatabaseName     $fielddatabasename      The Field Database Name Class.
	 * @param CustomQuery           $customquery            The Custom Query Class.
	 * @param ItemsStringFix        $itemsstringfix         The Items String Fix Class.
	 * @param SelectionTranslation  $selectiontranslation   The Selection Translation Class.
	 * @param AccessSwitch          $accessswitch           The Access Switch Class.
	 * @param ContentOne            $contentone             The ContentOne Class.
	 * @param ViewsDefaultOrdering  $viewsdefaultordering   The Views Default Ordering Class.
	 * @param EximportView          $eximportview           The Eximport View Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Dispenser $dispenser,
		Placeholder $placeholder,
		FieldDatabaseName $fielddatabasename,
		CustomQuery $customquery,
		ItemsStringFix $itemsstringfix,
		SelectionTranslation $selectiontranslation,
		AccessSwitch $accessswitch,
		ContentOne $contentone,
		ViewsDefaultOrdering $viewsdefaultordering,
		EximportView $eximportview)
	{
		$this->config = $config;
		$this->dispenser = $dispenser;
		$this->placeholder = $placeholder;
		$this->fielddatabasename = $fielddatabasename;
		$this->customquery = $customquery;
		$this->itemsstringfix = $itemsstringfix;
		$this->selectiontranslation = $selectiontranslation;
		$this->accessswitch = $accessswitch;
		$this->contentone = $contentone;
		$this->viewsdefaultordering = $viewsdefaultordering;
		$this->eximportview = $eximportview;
	}

	/**
	 * Build the getItems or getExportData method of a list model.
	 *
	 * @param   string  $nameSingleCode  The single view code name.
	 * @param   string  $nameListCode    The list view code name.
	 * @param   array   $config          The details that adapt the method being built.
	 *
	 * @return  string
	 *
	 * @since   6.1.7
	 */
	public function get(&$nameSingleCode, &$nameListCode,
		$config = array('functionName' => 'getExportData',
			'docDesc'      => 'Method to get list export data.',
			'type'         => 'export')
	)
	{
		// start the query string
		$query = '';
		// check if this is the export method
		$isExport = ('export' === $config['type']);
		// check if this view has export feature, and or if this is not an export method
		if ($this->eximportview->get($nameListCode) || !$isExport)
		{
			$query = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
			$query .= PHP_EOL . Indent::_(1) . " * " . $config['docDesc'];
			$query .= PHP_EOL . Indent::_(1) . " *";
			$query .= PHP_EOL . Indent::_(1)
				. " * @param   array  \$pks  The ids of the items to get";
			$query .= PHP_EOL . Indent::_(1)
				. " * @param   JUser  \$user  The user making the request";
			$query .= PHP_EOL . Indent::_(1) . " *";
			$query .= PHP_EOL . Indent::_(1)
				. " * @return mixed  An array of data items on success, false on failure.";
			$query .= PHP_EOL . Indent::_(1) . " */";
			$query .= PHP_EOL . Indent::_(1) . "public function "
				. $config['functionName'] . "(\$pks, \$user = null)";
			$query .= PHP_EOL . Indent::_(1) . "{";
			$query .= PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " setup the query";
			$query .= PHP_EOL . Indent::_(2) . "if ((\$pks_size = "
				. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$pks)) !== false || 'bulk' === \$pks)";
			$query .= PHP_EOL . Indent::_(2) . "{";
			$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Set a value to know this is " . $config['type']
				. " method. (USE IN CUSTOM CODE TO ALTER OUTCOME)";
			$query .= PHP_EOL . Indent::_(3) . "\$_" . $config['type']
				. " = true;";
			$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Get the user object if not set.";
			$query .= PHP_EOL . Indent::_(3) . "if (!isset(\$user) || !"
				. "Super_" . "__91004529_94a9_4590_b842_e7c6b624ecf5___Power::check(\$user))";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= $this->getUserObject(4);
			$query .= PHP_EOL . Indent::_(3) . "}";
			$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Create a new query object.";
			$query .= $this->getDatabaseObject(3);
			$query .= PHP_EOL . Indent::_(3)
				. "\$query = \$db->{$this->queryMethod};";
			$query .= PHP_EOL . PHP_EOL . Indent::_(3) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Select some fields";
			$query .= PHP_EOL . Indent::_(3) . "\$query->select('a.*');";
			$query .= PHP_EOL . PHP_EOL . Indent::_(3) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " From the " . $this->config->component_code_name . "_"
				. $nameSingleCode . " table";
			$query .= PHP_EOL . Indent::_(3)
				. "\$query->from(\$db->quoteName('#__"
				. $this->config->component_code_name . "_" . $nameSingleCode
				. "', 'a'));";
			$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " The bulk export path";
			$query .= PHP_EOL . Indent::_(3) . "if ('bulk' === \$pks)";
			$query .= PHP_EOL . Indent::_(3)
				. "{";
			$query .= PHP_EOL . Indent::_(4)
				. "\$query->where('a.id > 0');";
			$query .= PHP_EOL . Indent::_(3)
				. "}";
			$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " A large array of ID's will not work out well";
			$query .= PHP_EOL . Indent::_(3) . "elseif (\$pks_size > 500)";
			$query .= PHP_EOL . Indent::_(3)
				. "{";
			$query .= PHP_EOL . Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " Use lowest ID";
			$query .= PHP_EOL . Indent::_(4)
				. "\$query->where('a.id >= ' . (int) min(\$pks));";
			$query .= PHP_EOL . Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " Use highest ID";
			$query .= PHP_EOL . Indent::_(4)
				. "\$query->where('a.id <= ' . (int) max(\$pks));";
			$query .= PHP_EOL . Indent::_(3)
				. "}";
			$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " The normal default path";
			$query .= PHP_EOL . Indent::_(3) . "else";
			$query .= PHP_EOL . Indent::_(3)
				. "{";
			$query .= PHP_EOL . Indent::_(4)
				. "\$query->where('a.id IN (' . implode(',',\$pks) . ')');";
			$query .= PHP_EOL . Indent::_(3)
				. "}";
			// add custom filtering php
			$query .= $this->dispenser->get(
				'php_getlistquery', $nameSingleCode,
				PHP_EOL . PHP_EOL . Indent::_(1)
			);
			// first check if we export of text only is avalable
			if ($this->config->get('export_text_only', 0))
			{
				// add switch
				$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Get global switch to activate text only export";
				$query .= PHP_EOL . Indent::_(3)
					. "\$export_text_only = ComponentHelper::getParams('com_"
					. $this->config->component_code_name
					. "')->get('export_text_only', 0);";
				// first check if we have custom queries
				$custom_query = $this->customquery->get(
					$nameListCode, $nameSingleCode, Indent::_(2), true
				);
			}
			// if values were returned add the area
			if (isset($custom_query)
				&& StringHelper::check(
					$custom_query
				))
			{
				$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Add these queries only if text only is required";
				$query .= PHP_EOL . Indent::_(3) . "if (\$export_text_only)";
				$query .= PHP_EOL . Indent::_(3) . "{";
				// add the custom fields query
				$query .= $custom_query;
				$query .= PHP_EOL . Indent::_(3) . "}";
			}
			// add access levels if the view has access set
			if ($this->accessswitch->exists($nameSingleCode))
			{
				$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Implement View Level Access";
				$query .= PHP_EOL . Indent::_(3)
					. "if (!\$user->authorise('core.options', 'com_"
					. $this->config->component_code_name . "'))";
				$query .= PHP_EOL . Indent::_(3) . "{";
				$query .= PHP_EOL . Indent::_(4)
					. "\$groups = implode(',', \$user->getAuthorisedViewLevels());";
				$query .= PHP_EOL . Indent::_(4)
					. "\$query->where('a.access IN (' . \$groups . ')');";
				$query .= PHP_EOL . Indent::_(3) . "}";
			}
			// add dynamic ordering (Exported data)
			if ($this->viewsdefaultordering->
				get("$nameListCode.add_admin_ordering", 0) == 1)
			{
				foreach ($this->viewsdefaultordering->
					get("$nameListCode.admin_ordering_fields", []) as $order_field)
				{
					if (($order_field_name = $this->fielddatabasename->get(
							$nameListCode, $order_field['field']
						)) !== false)
					{
						$query .= PHP_EOL . PHP_EOL . Indent::_(3) . "//"
							. Line::_(
								__LINE__,__CLASS__
							) . " Order the results by ordering";
						$query .= PHP_EOL . Indent::_(3)
							. "\$query->order('"
							. $order_field_name . " "
							. $order_field['direction'] . "');";
					}
				}
			}
			else
			{
				$query .= PHP_EOL . PHP_EOL . Indent::_(3) . "//"
					. Line::_(
						__LINE__,__CLASS__
					) . " Order the results by ordering";
				$query .= PHP_EOL . Indent::_(3)
					. "\$query->order('a.ordering  ASC');";
			}
			$query .= PHP_EOL . PHP_EOL . Indent::_(3) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Load the items";
			$query .= PHP_EOL . Indent::_(3) . "\$db->setQuery(\$query);";
			$query .= PHP_EOL . Indent::_(3) . "\$db->execute();";
			$query .= PHP_EOL . Indent::_(3) . "if (\$db->getNumRows())";
			$query .= PHP_EOL . Indent::_(3) . "{";
			$query .= PHP_EOL . Indent::_(4)
				. "\$items = \$db->loadObjectList();";
			// set the string fixing code
			$query .= $this->itemsstringfix->get(
				$nameSingleCode, $nameListCode,
				$this->contentone->get('Component'),
				Indent::_(2), $isExport, true
			);
			// first check if we export of text only is avalable
			if ($this->config->get('export_text_only', 0))
			{
				$query_translations = $this->selectiontranslation->get(
					$nameListCode, Indent::_(3)
				);
			}
			// add translations
			if (isset($query_translations)
				&& StringHelper::check($query_translations))
			{
				$query .= PHP_EOL . Indent::_(3) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Add these translation only if text only is required";
				$query .= PHP_EOL . Indent::_(3) . "if (\$export_text_only)";
				$query .= PHP_EOL . Indent::_(3) . "{";
				$query .= $query_translations;
				$query .= PHP_EOL . Indent::_(3) . "}";
			}
			// add custom php to getItems method after all
			$query .= $this->dispenser->get(
				'php_getitems_after_all', $nameSingleCode,
				PHP_EOL . PHP_EOL . Indent::_(2)
			);
			// in privacy export we must return array of arrays
			if ('privacy' === $config['type'])
			{
				$query .= PHP_EOL . Indent::_(4)
					. "return json_decode(json_encode(\$items), true);";
			}
			else
			{
				$query .= PHP_EOL . Indent::_(4) . "return \$items;";
			}
			$query .= PHP_EOL . Indent::_(3) . "}";
			$query .= PHP_EOL . Indent::_(2) . "}";
			$query .= PHP_EOL . Indent::_(2) . "return false;";
			$query .= PHP_EOL . Indent::_(1) . "}";
			// get the header script
			if ($isExport)
			{
				$header = ComponentbuilderHelper::getDynamicScripts('headers');

				// add getExImPortHeaders
				$query .= $this->dispenser->get(
					'php_import_headers', 'import_' . $nameListCode,
					PHP_EOL . PHP_EOL, null, true,
					// set a default script for those with no custom script
					PHP_EOL . PHP_EOL . $this->placeholder->update_(
						$header
					)
				);
			}
		}

		return $query;
	}

	/**
	 * Get the statement that puts the current user in scope.
	 *
	 * @param   int  $indent  The indentation of the generated line.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getUserObject(int $indent): string
	{
		return PHP_EOL . Indent::_($indent) . "\$user = \$this->getCurrentUser();";
	}

	/**
	 * Get the statement that puts the database in scope.
	 *
	 * @param   int  $indent  The indentation of the generated line.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getDatabaseObject(int $indent): string
	{
		return PHP_EOL . Indent::_($indent) . "\$db = \$this->getDatabase();";
	}
}

