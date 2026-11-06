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


use VDM\Joomla\Componentbuilder\Compiler\Builder\Alias;
use VDM\Joomla\Componentbuilder\Compiler\Builder\CategoryCode;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\CustomAlias;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Title;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;


/**
 * Model Alias Title Fix Class.
 * 
 * Builds the uniqueness fix a model applies before storing a record, so a
 * title and its alias stay unique within the view, and within its category
 * where the view is categorised.
 * 
 * Target variants supply the configuration lookup for Unicode slugs.
 * 
 * @since  6.1.7
 */
class AliasTitleFix
{
	/**
	 * The Alias Class.
	 *
	 * @var   Alias
	 * @since 6.1.7
	 */
	protected Alias $alias;

	/**
	 * The Title Class.
	 *
	 * @var   Title
	 * @since 6.1.7
	 */
	protected Title $title;

	/**
	 * The Custom Alias Class.
	 *
	 * @var   CustomAlias
	 * @since 6.1.7
	 */
	protected CustomAlias $customalias;

	/**
	 * The Category Code Class.
	 *
	 * @var   CategoryCode
	 * @since 6.1.7
	 */
	protected CategoryCode $categorycode;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * Constructor.
	 *
	 * @param Alias         $alias          The Alias Class.
	 * @param Title         $title          The Title Class.
	 * @param CustomAlias   $customalias    The Custom Alias Class.
	 * @param CategoryCode  $categorycode   The Category Code Class.
	 * @param ContentOne    $contentone     The ContentOne Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Alias $alias,
		Title $title,
		CustomAlias $customalias,
		CategoryCode $categorycode,
		ContentOne $contentone)
	{
		$this->alias = $alias;
		$this->title = $title;
		$this->customalias = $customalias;
		$this->categorycode = $categorycode;
		$this->contentone = $contentone;
	}

	/**
	 * Build the title and alias uniqueness fix of a model.
	 *
	 * @param   string  $nameSingleCode  The single view code name.
	 *
	 * @return  string
	 *
	 * @since   6.1.7
	 */
	public function get($nameSingleCode)
	{
		$fixUnique = [];
		// only load this if these two items are set
		if ($this->alias->exists($nameSingleCode)
			&& ($this->title->exists($nameSingleCode)
				|| $this->customalias->exists($nameSingleCode)))
		{
			// set needed defaults
			$category = $this->categorycode->getString("{$nameSingleCode}.code");
			$alias       = $this->alias->get($nameSingleCode);
			$VIEW        = StringHelper::safe(
				$nameSingleCode, 'U'
			);
			// set the title stuff
			if (($customAliasBuilder = $this->customalias->get($nameSingleCode)) !== null)
			{
				$titles = array_values(
					$customAliasBuilder
				);
			}
			else
			{
				$titles = [$this->title->get($nameSingleCode)];
			}
			// start building the fix
			$fixUnique[] = PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Alter the " . implode(', ', $titles)
				. " for save as copy";
			$fixUnique[] = Indent::_(2)
				. "if (\$input->get('task') === 'save2copy')";
			$fixUnique[] = Indent::_(2) . "{";
			$fixUnique[] = Indent::_(3)
				. "\$origTable = clone \$this->getTable();";
			$fixUnique[] = Indent::_(3)
				. "\$origTable->load(\$input->getInt('id'));";
			// reset the buckets
			$ifStatment  = [];
			$titleVars   = [];
			$titleData   = [];
			$titleUpdate = [];
			// load the dynamic title builder
			foreach ($titles as $title)
			{
				$ifStatment[]  = "\$data['" . $title . "'] == \$origTable->"
					. $title;
				$titleVars[]   = "\$" . $title;
				$titleData[]   = "\$data['" . $title . "']";
				$titleUpdate[] = Indent::_(4) . "\$data['" . $title . "'] = \$"
					. $title . ";";
			}
			$fixUnique[] = PHP_EOL . Indent::_(3) . "if (" . implode(
					' || ', $ifStatment
				) . ")";
			$fixUnique[] = Indent::_(3) . "{";
			if ($category !== null && count((array) $titles) == 1)
			{
				$fixUnique[] = Indent::_(4) . "list(" . implode('', $titleVars)
					. ", \$" . $alias . ") = \$this->generateNewTitle(\$data['"
					. $category . "'], \$data['" . $alias . "'], " . implode(
						'', $titleData
					) . ");";
			}
			elseif (count((array) $titles) == 1)
			{
				$fixUnique[] = Indent::_(4) . "list(" . implode(
						', ', $titleVars
					)
					. ", \$" . $alias . ") = \$this->_generateNewTitle(\$data['"
					. $alias . "'], " . implode('', $titleData) . ");";
			}
			else
			{
				$fixUnique[] = Indent::_(4) . "list(" . implode(
						', ', $titleVars
					)
					. ", \$" . $alias . ") = \$this->_generateNewTitle(\$data['"
					. $alias . "'], array(" . implode(', ', $titleData) . "));";
			}
			$fixUnique[] = implode(PHP_EOL, $titleUpdate);
			$fixUnique[] = Indent::_(4) . "\$data['" . $alias . "'] = \$"
				. $alias . ";";
			$fixUnique[] = Indent::_(3) . "}";
			$fixUnique[] = Indent::_(3) . "else";
			$fixUnique[] = Indent::_(3) . "{";
			$fixUnique[] = Indent::_(4) . "if (\$data['" . $alias
				. "'] == \$origTable->" . $alias . ")";
			$fixUnique[] = Indent::_(4) . "{";
			$fixUnique[] = Indent::_(5) . "\$data['" . $alias . "'] = '';";
			$fixUnique[] = Indent::_(4) . "}";
			$fixUnique[] = Indent::_(3) . "}";
			$fixUnique[] = PHP_EOL . Indent::_(3) . "\$data['published'] = 0;";
			$fixUnique[] = Indent::_(2) . "}";
			$fixUnique[] = PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Automatic handling of " . $alias . " for empty fields";
			$fixUnique[] = Indent::_(2)
				. "if (in_array(\$input->get('task'), array('apply', 'save', 'save2new')) && (int) \$input->get('id') == 0)";
			$fixUnique[] = Indent::_(2) . "{";
			$fixUnique[] = Indent::_(3) . "if (\$data['" . $alias
				. "'] == null || empty(\$data['" . $alias . "']))";
			$fixUnique[] = Indent::_(3) . "{";
			$fixUnique[] = $this->getUnicodeSlugsCondition();
			$fixUnique[] = Indent::_(4) . "{";
			$fixUnique[] = Indent::_(5) . "\$data['" . $alias
				. "'] = OutputFilter::stringURLUnicodeSlug(" . implode(
					' . " " . ', $titleData
				) . ");";
			$fixUnique[] = Indent::_(4) . "}";
			$fixUnique[] = Indent::_(4) . "else";
			$fixUnique[] = Indent::_(4) . "{";
			$fixUnique[] = Indent::_(5) . "\$data['" . $alias
				. "'] = OutputFilter::stringURLSafe(" . implode(
					' . " " . ', $titleData
				) . ");";
			$fixUnique[] = Indent::_(4) . "}";
			$fixUnique[] = PHP_EOL . Indent::_(4)
				. "\$table = clone \$this->getTable();";
			if ($category !== null && count($titles) == 1)
			{
				$fixUnique[] = PHP_EOL . Indent::_(4)
					. "if (\$table->load(['" . $alias . "' => \$data['"
					. $alias . "'], '" . $category . "' => \$data['" . $category
					. "']]) && (\$table->id != \$data['id'] || \$data['id'] == 0))";
				$fixUnique[] = Indent::_(4) . "{";
				$fixUnique[] = Indent::_(5) . "\$msg = Joomla__"."_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_"
					. $this->contentone->get('COMPONENT') . "_" . $VIEW . "_SAVE_WARNING');";
				$fixUnique[] = Indent::_(4) . "}";
				$fixUnique[] = PHP_EOL . Indent::_(4) . "list(" . implode(
						'', $titleVars
					) . ", \$" . $alias
					. ") = \$this->generateNewTitle(\$data['" . $category
					. "'], \$data['" . $alias . "'], " . implode('', $titleData)
					. ");";
				$fixUnique[] = Indent::_(4) . "\$data['" . $alias . "'] = \$"
					. $alias . ";";
			}
			else
			{
				$fixUnique[] = PHP_EOL . Indent::_(4)
					. "if (\$table->load(array('" . $alias . "' => \$data['"
					. $alias
					. "'])) && (\$table->id != \$data['id'] || \$data['id'] == 0))";
				$fixUnique[] = Indent::_(4) . "{";
				$fixUnique[] = Indent::_(5) . "\$msg = Joomla__"."_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_"
					. $this->contentone->get('COMPONENT') . "_" . $VIEW . "_SAVE_WARNING');";
				$fixUnique[] = Indent::_(4) . "}";
				$fixUnique[] = PHP_EOL . Indent::_(4) . "\$data['" . $alias
					. "'] = \$this->_generateNewTitle(\$data['" . $alias
					. "']);";
			}
			$fixUnique[] = PHP_EOL . Indent::_(4) . "if (isset(\$msg))";
			$fixUnique[] = Indent::_(4) . "{";
			$fixUnique[] = Indent::_(5)
				. "Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->enqueueMessage(\$msg, 'warning');";
			$fixUnique[] = Indent::_(4) . "}";
			$fixUnique[] = Indent::_(3) . "}";
			$fixUnique[] = Indent::_(2) . "}";

//			$fixUnique[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__) . " Update alias if still empty at this point";
//			$fixUnique[] = Indent::_(2) . "if (\$data['" . $alias . "'] == null || empty(\$data['" . $alias . "']))";
//			$fixUnique[] = Indent::_(2) . "{";
//			$fixUnique[] = Indent::_(3) . "if (Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getConfig()->get('unicodeslugs') == 1)";
//			$fixUnique[] = Indent::_(3) . "{";
//			$fixUnique[] = Indent::_(4) . "\$data['" . $alias . "'] = OutputFilter::stringURLUnicodeSlug(" . implode(' . " " . ', $titleData) . ");";
//			$fixUnique[] = Indent::_(3) . "}";
//			$fixUnique[] = Indent::_(3) . "else";
//			$fixUnique[] = Indent::_(3) . "{";
//			$fixUnique[] = Indent::_(4) . "\$data['" . $alias . "'] = OutputFilter::stringURLSafe(" . implode(' . " " . ', $titleData) . ");";
//			$fixUnique[] = Indent::_(3) . "}";
//			$fixUnique[] = Indent::_(2) . "}";
		}
		// handel other unique fields
		$fixUnique[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Alter the unique field for save as copy";
		$fixUnique[] = Indent::_(2)
			. "if (\$input->get('task') === 'save2copy')";
		$fixUnique[] = Indent::_(2) . "{";
		$fixUnique[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Automatic handling of other unique fields";
		$fixUnique[] = Indent::_(3)
			. "\$uniqueFields = \$this->getUniqueFields();";
		$fixUnique[] = Indent::_(3) . "if ("
			. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$uniqueFields))";
		$fixUnique[] = Indent::_(3) . "{";
		$fixUnique[] = Indent::_(4)
			. "foreach (\$uniqueFields as \$uniqueField)";
		$fixUnique[] = Indent::_(4) . "{";
		$fixUnique[] = Indent::_(5) . "if (!array_key_exists(\$uniqueField, \$data))";
		$fixUnique[] = Indent::_(5) . "{";
		$fixUnique[] = Indent::_(6) . "continue;";
		$fixUnique[] = Indent::_(5) . "}";
		$fixUnique[] = Indent::_(5)
			. "\$uniqueValue = \$this->generateUnique(\$uniqueField, \$data[\$uniqueField]);";
		$fixUnique[] = Indent::_(5) . "if (\$uniqueValue === false)";
		$fixUnique[] = Indent::_(5) . "{";
		$fixUnique[] = Indent::_(6) . "return false;";
		$fixUnique[] = Indent::_(5) . "}";
		$fixUnique[] = Indent::_(5) . "\$data[\$uniqueField] = \$uniqueValue;";
		$fixUnique[] = Indent::_(4) . "}";
		$fixUnique[] = Indent::_(3) . "}";
		$fixUnique[] = Indent::_(2) . "}";

		return PHP_EOL . implode(PHP_EOL, $fixUnique);
	}

	/**
	 * Get the generated condition that selects Unicode alias handling.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getUnicodeSlugsCondition(): string
	{
		return Indent::_(4) . "if (Joomla__"
			. "_39403062_84fb_46e0_bac4_0023f766e827___Power::getConfig()->get('unicodeslugs') == 1)";
	}
}

