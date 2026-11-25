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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews;


use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Field\CustomFieldCode;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Builder\AdminFilterType;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Filter;
use VDM\Joomla\Componentbuilder\Compiler\Builder\SelectionTranslation;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\FilterFieldHelperInterface;


/**
 * Admin View Filter Field Helper Class.
 * 
 * Builds the filter option getters a list view helper carries, one per
 * filtered field, resolving each field type to the query or lookup that
 * produces its options.
 * 
 * Two things differ between Joomla targets — how a database connection is
 * opened, and how a user name is resolved for a user filter — so those are
 * the extension points the target variants override.
 * 
 * @since  6.1.7
 */
class FilterFieldHelper implements FilterFieldHelperInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Filter Class.
	 *
	 * @var   Filter
	 * @since 6.1.7
	 */
	protected Filter $filter;

	/**
	 * The Admin Filter Type Class.
	 *
	 * @var   AdminFilterType
	 * @since 6.1.7
	 */
	protected AdminFilterType $adminfiltertype;

	/**
	 * The Selection Translation Class.
	 *
	 * @var   SelectionTranslation
	 * @since 6.1.7
	 */
	protected SelectionTranslation $selectiontranslation;

	/**
	 * The Custom Field Code Class.
	 *
	 * @var   CustomFieldCode
	 * @since 6.1.7
	 */
	protected CustomFieldCode $customfieldcode;

	/**
	 * The Filter Field File Class.
	 *
	 * @var   FilterFieldFile
	 * @since 6.1.7
	 */
	protected FilterFieldFile $filterfieldfile;

	/**
	 * The Application Class.
	 *
	 * @var   CMSApplicationInterface
	 * @since 6.1.7
	 */
	protected CMSApplicationInterface $app;

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
	 * @param Filter                $filter                 The Filter Class.
	 * @param AdminFilterType       $adminfiltertype        The Admin Filter Type Class.
	 * @param SelectionTranslation  $selectiontranslation   The Selection Translation Class.
	 * @param CustomFieldCode       $customfieldcode        The Custom Field Code Class.
	 * @param FilterFieldFile       $filterfieldfile        The Filter Field File Class.
	 * @param CMSApplicationInterface|null  $app            The Application Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Filter $filter,
		AdminFilterType $adminfiltertype,
		SelectionTranslation $selectiontranslation,
		CustomFieldCode $customfieldcode,
		FilterFieldFile $filterfieldfile,
		?CMSApplicationInterface $app = null)
	{
		$this->config = $config;
		$this->filter = $filter;
		$this->adminfiltertype = $adminfiltertype;
		$this->selectiontranslation = $selectiontranslation;
		$this->customfieldcode = $customfieldcode;
		$this->filterfieldfile = $filterfieldfile;
		$this->app = $app ?: Factory::getApplication();
	}

	/**
	 * Build the filter option getters of a list view helper.
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
		// the old filter type uses these functions
		if ($this->filter->exists($nameListCode))
		{
			// set the function or file path (2 = topbar)
			$funtion_path = true;
			if ($this->adminfiltertype->get($nameListCode, 1) == 2)
			{
				$funtion_path = false;
			}
			$function = [];
			// set component name
			$component = $this->config->component_code_name;
			$Component = ucfirst((string) $component);
			foreach ($this->filter->get($nameListCode) as $filter)
			{
				if ($filter['type'] != 'category'
					&& ArrayHelper::check($filter['custom'])
					&& $filter['custom']['extends'] === 'user')
				{
					// add if this is a function path
					if ($funtion_path)
					{
						$function[] = PHP_EOL . Indent::_(1)
							. "protected function getThe" . $filter['function']
							. StringHelper::safe(
								$filter['custom']['text'], 'F'
							) . "Selections()";
						$function[] = Indent::_(1) . "{";
					}
					$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
						. " Get a db connection.";
					$function[] = $this->getDatabaseObject();
					$function[] = PHP_EOL . Indent::_(2) . "//"
						. Line::_(__Line__, __Class__)
						. " Create a new query object.";
					$function[] = Indent::_(2)
						. "\$query = \$db->{$this->queryMethod};";
					$function[] = PHP_EOL . Indent::_(2) . "//"
						. Line::_(__Line__, __Class__) . " Select the text.";
					$function[] = Indent::_(2)
						. "\$query->select(\$db->quoteName(array('a."
						. $filter['custom']['id'] . "','a."
						. $filter['custom']['text'] . "')));";
					$function[] = Indent::_(2)
						. "\$query->from(\$db->quoteName('"
						. $filter['custom']['table'] . "', 'a'));";
					$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
						. " get the targeted groups";
					$function[] = Indent::_(2)
						. "\$groups= ComponentHelper::getParams('com_"
						. $component . "')->get('" . $filter['type'] . "');";
					$function[] = Indent::_(2)
						. "if (!empty(\$groups) && count((array) \$groups) > 0)";
					$function[] = Indent::_(2) . "{";
					$function[] = Indent::_(3)
						. "\$query->join('LEFT', \$db->quoteName('#__user_usergroup_map', 'group') . ' ON (' . \$db->quoteName('group.user_id') . ' = ' . \$db->quoteName('a.id') . ')');";
					$function[] = Indent::_(3)
						. "\$query->where('group.group_id IN (' . implode(',', \$groups) . ')');";
					$function[] = Indent::_(2) . "}";
					$function[] = Indent::_(2) . "\$query->order('a."
						. $filter['custom']['text'] . " ASC');";
					$function[] = PHP_EOL . Indent::_(2) . "//"
						. Line::_(__Line__, __Class__)
						. " Reset the query using our newly populated query object.";
					$function[] = Indent::_(2) . "\$db->setQuery(\$query);";
					$function[] = PHP_EOL . Indent::_(2)
						. "\$results = \$db->loadObjectList();";
					$function[] = Indent::_(2) . "\$_filter = [];";
					// if this is not a multi field
					if (!$funtion_path && $filter['multi'] == 1)
					{
						$function[] = Indent::_(2)
							. "\$_filter[] = Html::_('select.option', '', '- Select ' . Text:"
							. ":_('" . $filter['lang'] . "') . ' -');";
					}
					$function[] = Indent::_(2) . "if (\$results)";
					$function[] = Indent::_(2) . "{";
					$function[] = Indent::_(3)
						. "foreach (\$results as \$result)";
					$function[] = Indent::_(3) . "{";
					$function[] = Indent::_(4)
						. "\$_filter[] = Html::_('select.option', \$result->"
						. $filter['custom']['id'] . ", \$result->"
						. $filter['custom']['text'] . ");";
					$function[] = Indent::_(3) . "}";
					$function[] = Indent::_(2) . "}";
					$function[] = Indent::_(2) . "return  \$_filter;";
					// add if this is a function path
					if ($funtion_path)
					{
						$function[] = Indent::_(1) . "}";
					}

					/* else
					  {
					  $function[] = PHP_EOL.Indent::_(1) . "protected function getThe".$filter['function'].StringHelper::safe($filter['custom']['text'],'F')."Selections()";
					  $function[] = Indent::_(1) . "{";
					  $function[] = Indent::_(2) . "//".Line::_(__Line__, __Class__)." Get a db connection.";
					  $function[] = Indent::_(2) . "\$db = Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getDbo();";
					  $function[] = PHP_EOL.Indent::_(2) . "//".Line::_(__Line__, __Class__)." Select the text.";
					  $function[] = Indent::_(2) . "\$query = \$db->getQuery(true);";
					  $function[] = PHP_EOL.Indent::_(2) . "//".Line::_(__Line__, __Class__)." Select the text.";
					  $function[] = Indent::_(2) . "\$query->select(\$db->quoteName(array('".$filter['custom']['id']."','".$filter['custom']['text']."')));";
					  $function[] = Indent::_(2) . "\$query->from(\$db->quoteName('".$filter['custom']['table']."'));";
					  $function[] = Indent::_(2) . "\$query->where(\$db->quoteName('published') . ' = 1');";
					  $function[] = Indent::_(2) . "\$query->order(\$db->quoteName('".$filter['custom']['text']."') . ' ASC');";
					  $function[] = PHP_EOL.Indent::_(2) . "//".Line::_(__Line__, __Class__)." Reset the query using our newly populated query object.";
					  $function[] = Indent::_(2) . "\$db->setQuery(\$query);";
					  $function[] = PHP_EOL.Indent::_(2) . "\$results = \$db->loadObjectList();";
					  $function[] = PHP_EOL.Indent::_(2) . "if (\$results)";
					  $function[] = Indent::_(2) . "{";
					  $function[] = Indent::_(3) . "\$filter = [];";
					  $function[] = Indent::_(3) . "\$batch = [];";
					  $function[] = Indent::_(3) . "foreach (\$results as \$result)";
					  $function[] = Indent::_(3) . "{";
					  if ($filter['custom']['text'] === 'user')
					  {
					  $function[] = Indent::_(4) . "\$filter[] = Html::_('select.option', \$result->".$filter['custom']['text'].", Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getUser(\$result->".$filter['custom']['text'].")->name);";
					  $function[] = Indent::_(4) . "\$batch[] = Html::_('select.option', \$result->".$filter['custom']['id'].", Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getUser(\$result->".$filter['custom']['text'].")->name);";
					  }
					  else
					  {
					  $function[] = Indent::_(4) . "\$filter[] = Html::_('select.option', \$result->".$filter['custom']['text'].", \$result->".$filter['custom']['text'].");";
					  $function[] = Indent::_(4) . "\$batch[] = Html::_('select.option', \$result->".$filter['custom']['id'].", \$result->".$filter['custom']['text'].");";
					  }
					  $function[] = Indent::_(3) . "}";
					  $function[] = Indent::_(3) . "return array('filter' => \$filter, 'batch' => \$batch);";
					  $function[] = Indent::_(2) . "}";
					  $function[] = Indent::_(2) . "return false;";
					  $function[] = Indent::_(1) . "}";
					  } */
				}
				elseif ($filter['type'] != 'category'
					&& !ArrayHelper::check($filter['custom']))
				{
					$translation = false;
					if ($this->selectiontranslation->
						exists($nameListCode . '.' . $filter['code']))
					{
						$translation = true;
					}
					// add if this is a function path
					if ($funtion_path)
					{
						$function[] = PHP_EOL . Indent::_(1)
							. "protected function getThe" . $filter['function']
							. "Selections()";
						$function[] = Indent::_(1) . "{";
						$function[] = Indent::_(2) . "//" . Line::_(
								__LINE__,__CLASS__
							)
							. " Get a db connection.";
					}
					else
					{
						$function[] = "//" . Line::_(__Line__, __Class__)
							. " Get a db connection.";
					}
					$function[] = $this->getDatabaseObject();
					$function[] = PHP_EOL . Indent::_(2) . "//"
						. Line::_(__Line__, __Class__)
						. " Create a new query object.";
					$function[] = Indent::_(2)
						. "\$query = \$db->{$this->queryMethod};";

					// check if usergroup as we change to an object query
					if ($filter['type'] === 'usergroup' || $filter['type'] === 'usergrouplist')
					{
						$function[] = PHP_EOL . Indent::_(2) . "//"
							. Line::_(__Line__, __Class__) . " Select the text.";
						$function[] = Indent::_(2)
							. "\$query->select(\$db->quoteName('g."
							. $filter['code'] . "', 'id'));";
						$function[] = Indent::_(2)
							. "\$query->select(\$db->quoteName('ug.title', 'title'));";
						$function[] = Indent::_(2)
							. "\$query->from(\$db->quoteName('#__" . $component
							. "_" . $filter['database'] . "', 'g'));";
						$function[] = Indent::_(2)
							. "\$query->join('LEFT', \$db->quoteName('#__usergroups', 'ug') . ' ON (' . (\$db->quoteName('g."
							. $filter['code']
							. "') . ' = ' . \$db->quoteName('ug.id') . ')'));";
						$function[] = Indent::_(2)
							. "\$query->order(\$db->quoteName('title') . ' ASC');";
						$function[] = Indent::_(2)
							. "\$query->group(\$db->quoteName('ug.id'));";
						$function[] = PHP_EOL . Indent::_(2) . "//"
							. Line::_(__Line__, __Class__)
							. " Reset the query using our newly populated query object.";
						$function[] = Indent::_(2) . "\$db->setQuery(\$query);";
						$function[] = PHP_EOL . Indent::_(2)
							. "\$_results = \$db->loadObjectList();";
					}
					else
					{
						$function[] = PHP_EOL . Indent::_(2) . "//"
							. Line::_(__Line__, __Class__) . " Select the text.";
						$function[] = Indent::_(2)
							. "\$query->select(\$db->quoteName('"
							. $filter['code'] . "'));";
						$function[] = Indent::_(2)
							. "\$query->from(\$db->quoteName('#__" . $component
							. "_" . $filter['database'] . "'));";
						$function[] = Indent::_(2)
							. "\$query->order(\$db->quoteName('"
							. $filter['code'] . "') . ' ASC');";
						$function[] = PHP_EOL . Indent::_(2) . "//"
							. Line::_(__Line__, __Class__)
							. " Reset the query using our newly populated query object.";
						$function[] = Indent::_(2) . "\$db->setQuery(\$query);";
						$function[] = PHP_EOL . Indent::_(2)
							. "\$_results = \$db->loadColumn();";
					}
					$function[] = Indent::_(2) . "\$_filter = [];";
					// if this is not a multi field
					if (!$funtion_path && $filter['multi'] == 1)
					{
						$function[] = Indent::_(2)
							. "\$_filter[] = Html::_('select.option', '', '- ' . Text:"
							. ":_('" . $filter['lang_select'] . "') . ' -');";
					}
					$function[] = PHP_EOL . Indent::_(2) . "if (\$_results)";
					$function[] = Indent::_(2) . "{";

					// check if translated value is used
					if ($funtion_path && $translation)
					{
						$function[] = Indent::_(3) . "//" . Line::_(
								__LINE__,__CLASS__
							) . " get model";
						$function[] = Indent::_(3)
							. "\$_model = \$this->getModel();";
					}
					elseif ($translation)
					{
						$function[] = Indent::_(3) . "//" . Line::_(
								__LINE__,__CLASS__
							) . " get " . $nameListCode . "model";
						$function[] = Indent::_(3)
							. "\$_model = " . $Component . "Helper::getModel('"
							. $nameListCode . "');";
					}
					// check if usergroup as we change to an object query
					if ($filter['type'] !== 'usergroup' && $filter['type'] !== 'usergrouplist')
					{
						$function[] = Indent::_(3)
							. "\$_results = array_unique(\$_results);";
					}
					$function[] = Indent::_(3) . "foreach (\$_results as \$"
						. $filter['code'] . ")";
					$function[] = Indent::_(3) . "{";

					// check if translated value is used
					if ($translation)
					{
						$function[] = Indent::_(4) . "//" . Line::_(
								__LINE__,__CLASS__
							) . " Translate the " . $filter['code']
							. " selection";
						$function[] = Indent::_(4)
							. "\$_text = \$_model->selectionTranslation(\$"
							. $filter['code'] . ",'" . $filter['code'] . "');";
						$function[] = Indent::_(4) . "//" . Line::_(
								__LINE__,__CLASS__
							) . " Now add the " . $filter['code']
							. " and its text to the options array";
						$function[] = Indent::_(4)
							. "\$_filter[] = Html::_('select.option', \$"
							. $filter['code'] . ", Joomla__"."_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_(\$_text));";
					}
					elseif ($filter['type'] === 'user')
					{
						$function[] = Indent::_(4) . "//" . Line::_(
								__LINE__,__CLASS__
							) . " Now add the " . $filter['code']
							. " and its text to the options array";
						$function = array_merge(
							$function, $this->getUserNameOption($filter['code'])
						);
					}
					else
					{
						if ($filter['type'] === 'usergroup' || $filter['type'] === 'usergrouplist')
						{
							$function[] = Indent::_(4) . "//" . Line::_(
									__LINE__,__CLASS__
								) . " Now add the " . $filter['code']
								. " and its text to the options array";
							$function[] = Indent::_(4)
								. "\$_filter[] = Html::_('select.option', \$"
								. $filter['code'] . "->id, \$" . $filter['code']
								. "->title);";
						}
						else
						{
							$function[] = Indent::_(4) . "//" . Line::_(
									__LINE__,__CLASS__
								) . " Now add the " . $filter['code']
								. " and its text to the options array";
							$function[] = Indent::_(4)
								. "\$_filter[] = Html::_('select.option', \$"
								. $filter['code'] . ", \$" . $filter['code']
								. ");";
						}
					}
					$function[] = Indent::_(3) . "}";
					$function[] = Indent::_(2) . "}";
					$function[] = Indent::_(2) . "return \$_filter;";
					// add if this is a function path
					if ($funtion_path)
					{
						$function[] = Indent::_(1) . "}";
					}
				}
				// we check if this is a multi field
				// and if there is a blank option
				// and give a notice that this will cause an issue
				elseif (!$funtion_path && $filter['type'] != 'category'
					&& $filter['multi'] == 2
					&& ArrayHelper::check($filter['custom']))
				{
					// get the field code
					$field_code = $this->customfieldcode->get(
						$filter['custom']
					)['JFORM_TYPE_PHP'];
					// check for the [Html::_('select.option', '',] code
					if (strpos((string) $field_code, "Html::_('select.option', '',")
						!== false
						&& strpos((string) $field_code, '($this->multiple === false)')
						=== false)
					{
						// for now we just give an error message (don't fix it)
						$this->app->enqueueMessage(
							Text::_('COM_COMPONENTBUILDER_HR_HTHREEMULTI_FILTER_ERRORHTHREE'),
							'Error'
						);
						$field_url
							= '"index.php?option=com_componentbuilder&view=fields&task=field.edit&id='
							. $filter['id'] . '" target="_blank"';
						$field_fix
							= "<pre>if (\$this->multiple === false) { // <-- this if statement is needed";
						$field_fix .= PHP_EOL . Indent::_(1)
							. "\$options[] = Html::_('select.option', '', 'Select an option'); // <-- the empty option";
						$field_fix .= PHP_EOL . "}</pre>";
						$this->app->enqueueMessage(
							Text::sprintf(
								'We detected that you have an empty option in a <a href=%s>custom field (%s)</a> that is used in a multi filter.<br />This will cause a problem, you will need to add the following code to it.<br />%s',
								$field_url,
								$filter['code'],
								$field_fix
							), 'Error'
						);
					}
				}
				// divert the code to a file if this is not a funtion path
				if (!$funtion_path
					&& ArrayHelper::check(
						$function
					))
				{
					// set the filter file
					$this->filterfieldfile->set(
						implode(PHP_EOL, $function), $filter
					);
					// clear the filter out
					$function = [];
				}
			}
			// if this is a function path, return the function if set
			if ($funtion_path && ArrayHelper::check($function))
			{
				// return the function
				return PHP_EOL . implode(PHP_EOL, $function);
			}
		}

		return '';
	}

	/**
	 * Get the statement that opens a database connection.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getDatabaseObject(): string
	{
		return Indent::_(2) . "\$db = Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->get(Joomla__"."_7bd29d76_73c9_4c07_a5da_4f7a32aff78f___Power::class);";
	}

	/**
	 * Get the lines that add one user filter option and its name.
	 *
	 * @param   string  $code  The filter field code name.
	 *
	 * @return  array<string>
	 * @since   6.1.7
	 */
	protected function getUserNameOption(string $code): array
	{
		return [
			Indent::_(4)
				. "\$_filter[] = Html::_('select.option', \$"
				. $code . ",",
			Indent::_(5)
				. "Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->",
			Indent::_(5)
				. "get(Joomla__"."_c2980d12_c3ef_4e23_b4a2_e6af1f5900a9___Power::class)->",
			Indent::_(5)
				. "loadUserById((int) (\$" . $code . " ?? 0))->name",
			Indent::_(5) . ");",
		];
	}
}

