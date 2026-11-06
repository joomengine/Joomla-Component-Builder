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


use VDM\Joomla\Componentbuilder\Compiler\Power\Selection;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Permission;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Alias;
use VDM\Joomla\Componentbuilder\Compiler\Builder\CategoryCode;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\CustomAlias;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Title;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\BatchCopyInterface;


/**
 * Model BatchCopy Class.
 * 
 * Builds the batchCopy method of an admin model: the guards that
 * decide whether the current user may copy, the per field value fixes the
 * copy needs, and the insert of the new record.
 * 
 * Only how the current user is put in scope differs between Joomla targets,
 * so that is the extension point the target variants override.
 * 
 * @since  6.1.7
 */
class BatchCopy implements BatchCopyInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Permission Class.
	 *
	 * @var   Permission
	 * @since 6.1.7
	 */
	protected Permission $permission;

	/**
	 * The Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 6.1.7
	 */
	protected Dispenser $dispenser;

	/**
	 * The Alias Class.
	 *
	 * @var   Alias
	 * @since 6.1.7
	 */
	protected Alias $alias;

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
	 * The Custom Alias Class.
	 *
	 * @var   CustomAlias
	 * @since 6.1.7
	 */
	protected CustomAlias $customalias;

	/**
	 * The Title Class.
	 *
	 * @var   Title
	 * @since 6.1.7
	 */
	protected Title $title;

	/**
	 * Constructor.
	 *
	 * @param Config         $config           The Config Class.
	 * @param Permission     $permission       The Permission Class.
	 * @param Dispenser      $dispenser        The Dispenser Class.
	 * @param Alias          $alias            The Alias Class.
	 * @param CategoryCode   $categorycode     The Category Code Class.
	 * @param ContentOne     $contentone       The ContentOne Class.
	 * @param CustomAlias    $customalias      The Custom Alias Class.
	 * @param Title          $title            The Title Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Permission $permission,
		Dispenser $dispenser,
		Alias $alias,
		CategoryCode $categorycode,
		ContentOne $contentone,
		CustomAlias $customalias,
		Title $title)
	{
		$this->config = $config;
		$this->permission = $permission;
		$this->dispenser = $dispenser;
		$this->alias = $alias;
		$this->categorycode = $categorycode;
		$this->contentone = $contentone;
		$this->customalias = $customalias;
		$this->title = $title;
	}

	/**
	 * Build the batchCopy method of an admin model.
	 *
	 * @param   string  $nameSingleCode  The single view code name.
	 *
	 * @return  string
	 *
	 * @since   6.1.7
	 */
	public function get($nameSingleCode)
	{
		// set needed defaults
		$title     = false;
		$titles    = [];
		// only load alias if set in this view
		$alias     = $this->alias->get($nameSingleCode);
		$category  = $this->categorycode->getString("{$nameSingleCode}.code");
		$batchcopy = [];
		$VIEW      = StringHelper::safe($nameSingleCode, 'U');
		// component helper name
		$Helper = $this->contentone->get('Component') . 'Helper';

		// only load title if set in this view
		if (($customAliasBuilder = $this->customalias->get($nameSingleCode)) !== null)
		{
			$titles = array_values(
				$customAliasBuilder
			);
			$title  = true;
		}
		elseif ($this->title->exists($nameSingleCode))
		{
			$titles = [$this->title->get($nameSingleCode)];
			$title  = true;
		}
		// se the dynamic title
		if ($title)
		{
			// reset the bucket
			$titleData = [];
			// load the dynamic title builder
			foreach ($titles as $_title)
			{
				$titleData[] = "\$this->table->" . $_title;
			}
		}
		// prepare custom script
		$customScript = $this->dispenser->get(
			'php_batchcopy', $nameSingleCode, PHP_EOL . PHP_EOL, null, true
		);

		$batchcopy[] = PHP_EOL . Indent::_(1) . "/**";
		$batchcopy[] = Indent::_(1)
			. " * Batch copy items to a new category or current.";
		$batchcopy[] = Indent::_(1) . " *";
		$batchcopy[] = Indent::_(1)
			. " * @param   integer  \$values    The new values.";
		$batchcopy[] = Indent::_(1)
			. " * @param   array    \$pks       An array of row IDs.";
		$batchcopy[] = Indent::_(1)
			. " * @param   array    \$contexts  An array of item contexts.";
		$batchcopy[] = Indent::_(1) . " *";
		$batchcopy[] = Indent::_(1)
			. " * @return  mixed  An array of new IDs on success, boolean false on failure.";
		$batchcopy[] = Indent::_(1) . " *";
		$batchcopy[] = Indent::_(1) . " * @since 12.2";
		$batchcopy[] = Indent::_(1) . " */";
		$batchcopy[] = Indent::_(1)
			. "protected function batchCopy(\$values, \$pks, \$contexts)";
		$batchcopy[] = Indent::_(1) . "{";

		$batchcopy[] = Indent::_(2) . "if (empty(\$this->batchSet))";
		$batchcopy[] = Indent::_(2) . "{";
		$batchcopy[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Set some needed variables.";
		$batchcopy[] = $this->getUserObject();
		$batchcopy[] = Indent::_(3)
			. "\$this->table " . Indent::_(2) . "= \$this->getTable();";
		$batchcopy[] = Indent::_(3)
			. "\$this->tableClassName" . Indent::_(1) . "= get_class(\$this->table);";
		$batchcopy[] = Indent::_(3) . "\$this->canDo" . Indent::_(2) . "= " . Selection::permittedActionsToken() . "::get('" . $nameSingleCode . "');";
		$batchcopy[] = Indent::_(2) . "}";
		$batchcopy[] = PHP_EOL . Indent::_(2) . "if (!\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.create') . "') && !\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.batch') . "'))";
		$batchcopy[] = Indent::_(2) . "{";
		$batchcopy[] = Indent::_(3) . "return false;";
		$batchcopy[] = Indent::_(2) . "}" . $customScript;

		$batchcopy[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " get list of unique fields";
		$batchcopy[] = Indent::_(2)
			. "\$uniqueFields = \$this->getUniqueFields();";
		$batchcopy[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " remove move_copy from array";
		$batchcopy[] = Indent::_(2) . "unset(\$values['move_copy']);";

		$batchcopy[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " make sure published is set";
		$batchcopy[] = Indent::_(2) . "if (!isset(\$values['published']))";
		$batchcopy[] = Indent::_(2) . "{";
		$batchcopy[] = Indent::_(3) . "\$values['published'] = 0;";
		$batchcopy[] = Indent::_(2) . "}";
		$batchcopy[] = Indent::_(2)
			. "elseif (isset(\$values['published']) && !\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.edit.state') . "'))";
		$batchcopy[] = Indent::_(2) . "{";
		$batchcopy[] = Indent::_(4) . "\$values['published'] = 0;";
		$batchcopy[] = Indent::_(2) . "}";

		if ($category)
		{
			$batchcopy[] = PHP_EOL . Indent::_(2)
				. "if (isset(\$values['category']) && (int) \$values['category'] > 0 && !static::checkCategoryId(\$values['category']))";
			$batchcopy[] = Indent::_(2) . "{";
			$batchcopy[] = Indent::_(3) . "return false;";
			$batchcopy[] = Indent::_(2) . "}";
			$batchcopy[] = Indent::_(2)
				. "elseif (isset(\$values['category']) && (int) \$values['category'] > 0)";
			$batchcopy[] = Indent::_(2) . "{";
			$batchcopy[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " move the category value to correct field name";
			$batchcopy[] = Indent::_(3) . "\$values['" . $category
				. "'] = \$values['category'];";
			$batchcopy[] = Indent::_(3) . "unset(\$values['category']);";
			$batchcopy[] = Indent::_(2) . "}";
			$batchcopy[] = Indent::_(2)
				. "elseif (isset(\$values['category']))";
			$batchcopy[] = Indent::_(2) . "{";
			$batchcopy[] = Indent::_(3) . "unset(\$values['category']);";
			$batchcopy[] = Indent::_(2) . "}";
		}

		$batchcopy[] = PHP_EOL . Indent::_(2) . "\$newIds = [];";

		$batchcopy[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Parent exists so let's proceed";
		$batchcopy[] = Indent::_(2) . "while (!empty(\$pks))";
		$batchcopy[] = Indent::_(2) . "{";
		$batchcopy[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Pop the first ID off the stack";
		$batchcopy[] = Indent::_(3) . "\$pk = array_shift(\$pks);";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "\$this->table->reset();";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " only allow copy if user may edit this item.";
		$batchcopy[] = Indent::_(3) . "if (!\$this->user->authorise('"
			. $this->permission->getAction($nameSingleCode, 'core.edit') . "', \$contexts[\$pk]))";
		$batchcopy[] = Indent::_(3) . "{";
		$batchcopy[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
			. " Not fatal error";
		$batchcopy[] = Indent::_(4) . "\$this->setError(Text:"
			. ":sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', \$pk));";
		$batchcopy[] = Indent::_(4) . "continue;";
		$batchcopy[] = Indent::_(3) . "}";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Check that the row actually exists";
		$batchcopy[] = Indent::_(3) . "if (!\$this->table->load(\$pk))";
		$batchcopy[] = Indent::_(3) . "{";
		$batchcopy[] = Indent::_(4)
			. "if (\$error = \$this->table->getError())";
		$batchcopy[] = Indent::_(4) . "{";
		$batchcopy[] = Indent::_(5) . "//" . Line::_(__Line__, __Class__)
			. " Fatal error";
		$batchcopy[] = Indent::_(5) . "\$this->setError(\$error);";

		$batchcopy[] = Indent::_(5) . "return false;";
		$batchcopy[] = Indent::_(4) . "}";
		$batchcopy[] = Indent::_(4) . "else";
		$batchcopy[] = Indent::_(4) . "{";
		$batchcopy[] = Indent::_(5) . "//" . Line::_(__Line__, __Class__)
			. " Not fatal error";
		$batchcopy[] = Indent::_(5) . "\$this->setError(Text:"
			. ":sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', \$pk));";
		$batchcopy[] = Indent::_(5) . "continue;";
		$batchcopy[] = Indent::_(4) . "}";
		$batchcopy[] = Indent::_(3) . "}";
		if ($category && $alias === 'alias'
			&& ($title && count($titles) == 1
				&& in_array('title', $titles)))
		{
			$batchcopy[] = PHP_EOL . Indent::_(3) . "if (isset(\$values['"
				. $category . "']))";
			$batchcopy[] = Indent::_(3) . "{";
			$batchcopy[] = Indent::_(4)
				. "static::generateTitle((int) \$values['" . $category
				. "'], \$this->table);";
			$batchcopy[] = Indent::_(3) . "}";
			$batchcopy[] = Indent::_(3) . "else";
			$batchcopy[] = Indent::_(3) . "{";
			$batchcopy[] = Indent::_(4)
				. "static::generateTitle((int) \$this->table->" . $category
				. ", \$this->table);";
			$batchcopy[] = Indent::_(3) . "}";
		}
		elseif ($category && $alias && ($title && count($titles) == 1))
		{
			$batchcopy[] = PHP_EOL . Indent::_(3) . "if (isset(\$values['"
				. $category . "']))";
			$batchcopy[] = Indent::_(3) . "{";
			$batchcopy[] = Indent::_(4) . "list(\$this->table->" . implode(
					'', $titles
				) . ", \$this->table->" . $alias
				. ") = \$this->generateNewTitle(\$values['" . $category
				. "'], \$this->table->" . $alias . ", \$this->table->"
				. implode('', $titles) . ");";
			$batchcopy[] = Indent::_(3) . "}";
			$batchcopy[] = Indent::_(3) . "else";
			$batchcopy[] = Indent::_(3) . "{";
			$batchcopy[] = Indent::_(4) . "list(\$this->table->" . implode(
					'', $titles
				) . ", \$this->table->" . $alias
				. ") = \$this->generateNewTitle(\$this->table->" . $category
				. ", \$this->table->" . $alias . ", \$this->table->" . implode(
					'', $titles
				) . ");";
			$batchcopy[] = Indent::_(3) . "}";
		}
		elseif (!$category && $alias && ($title && count($titles) == 1))
		{
			$batchcopy[] = Indent::_(3) . "list(\$this->table->" . implode(
					'', $titles
				) . ", \$this->table->" . $alias
				. ") = \$this->_generateNewTitle(\$this->table->" . $alias
				. ", \$this->table->" . implode('', $titles) . ");";
		}
		elseif (!$category && $alias && $title)
		{
			$batchcopy[] = Indent::_(3) . "list(" . implode(', ', $titleData)
				. ", \$this->table->" . $alias
				. ") = \$this->_generateNewTitle(\$this->table->" . $alias
				. ", array(" . implode(', ', $titleData) . "));";
		}
		elseif (!$category && !$alias
			&& ($title && count($titles) == 1
				&& !in_array('user', $titles)
				&& !in_array(
					'jobnumber', $titles
				))) // TODO [jobnumber] just for one project (not ideal)
		{
			$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Only for strings";
			$batchcopy[] = Indent::_(3) . "if ("
				. "Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$this->table->" . implode('', $titles)
				. ") && !is_numeric(\$this->table->" . implode('', $titles)
				. "))";
			$batchcopy[] = Indent::_(3) . "{";
			$batchcopy[] = Indent::_(4) . "\$uniqueValue = \$this->generateUnique('"
				. implode('', $titles) . "', \$this->table->" . implode('', $titles) . ");";
			$batchcopy[] = Indent::_(4) . "if (\$uniqueValue === false)";
			$batchcopy[] = Indent::_(4) . "{";
			$batchcopy[] = Indent::_(5) . "return false;";
			$batchcopy[] = Indent::_(4) . "}";
			$batchcopy[] = Indent::_(4) . "\$this->table->" . implode('', $titles) . " = \$uniqueValue;";
			$batchcopy[] = Indent::_(3) . "}";
		}

		$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " insert all set values";
		$batchcopy[] = Indent::_(3) . "if ("
			. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$values))";
		$batchcopy[] = Indent::_(3) . "{";
		$batchcopy[] = Indent::_(4) . "foreach (\$values as \$key => \$value)";
		$batchcopy[] = Indent::_(4) . "{";
		$batchcopy[] = Indent::_(5)
			. "if (strlen(\$value) > 0 && isset(\$this->table->\$key))";
		$batchcopy[] = Indent::_(5) . "{";
		$batchcopy[] = Indent::_(6) . "\$this->table->\$key = \$value;";
		$batchcopy[] = Indent::_(5) . "}";
		$batchcopy[] = Indent::_(4) . "}";
		$batchcopy[] = Indent::_(3) . "}" . PHP_EOL;

		$batchcopy[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " update all unique fields";
		$batchcopy[] = Indent::_(3) . "if ("
			. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$uniqueFields))";
		$batchcopy[] = Indent::_(3) . "{";
		$batchcopy[] = Indent::_(4)
			. "foreach (\$uniqueFields as \$uniqueField)";
		$batchcopy[] = Indent::_(4) . "{";
		$batchcopy[] = Indent::_(5)
			. "\$uniqueValue = \$this->generateUnique(\$uniqueField, \$this->table->\$uniqueField);";
		$batchcopy[] = Indent::_(5) . "if (\$uniqueValue === false)";
		$batchcopy[] = Indent::_(5) . "{";
		$batchcopy[] = Indent::_(6) . "return false;";
		$batchcopy[] = Indent::_(5) . "}";
		$batchcopy[] = Indent::_(5) . "\$this->table->\$uniqueField = \$uniqueValue;";
		$batchcopy[] = Indent::_(4) . "}";
		$batchcopy[] = Indent::_(3) . "}";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Reset the ID because we are making a copy";
		$batchcopy[] = Indent::_(3) . "\$this->table->id = 0;";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " TODO: Deal with ordering?";
		$batchcopy[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " \$this->table->ordering = 1;";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Check the row.";
		$batchcopy[] = Indent::_(3) . "if (!\$this->table->check())";
		$batchcopy[] = Indent::_(3) . "{";
		$batchcopy[] = Indent::_(4)
			. "\$this->setError(\$this->table->getError());";

		$batchcopy[] = PHP_EOL . Indent::_(4) . "return false;";
		$batchcopy[] = Indent::_(3) . "}";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "if (!empty(\$this->type))";
		$batchcopy[] = Indent::_(3) . "{";
		$batchcopy[] = Indent::_(4)
			. "\$this->createTagsHelper(\$this->tagsObserver, \$this->type, \$pk, \$this->typeAlias, \$this->table);";
		$batchcopy[] = Indent::_(3) . "}";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Store the row.";
		$batchcopy[] = Indent::_(3) . "if (!\$this->table->store())";
		$batchcopy[] = Indent::_(3) . "{";
		$batchcopy[] = Indent::_(4)
			. "\$this->setError(\$this->table->getError());";

		$batchcopy[] = PHP_EOL . Indent::_(4) . "return false;";
		$batchcopy[] = Indent::_(3) . "}";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Get the new item ID";
		$batchcopy[] = Indent::_(3) . "\$newId = \$this->table->get('id');";

		$batchcopy[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Add the new ID to the array";
		$batchcopy[] = Indent::_(3) . "\$newIds[\$pk] = \$newId;";
		$batchcopy[] = Indent::_(2) . "}";

		$batchcopy[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Clean the cache";
		$batchcopy[] = Indent::_(2) . "\$this->cleanCache();";

		$batchcopy[] = PHP_EOL . Indent::_(2) . "return \$newIds;";
		$batchcopy[] = Indent::_(1) . "}";

		return PHP_EOL . implode(PHP_EOL, $batchcopy);
	}

	/**
	 * Get the statement that puts the current user in scope.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getUserObject(): string
	{
		return Indent::_(3)
			. "\$this->user " . Indent::_(2) . "= Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getIdentity();";
	}
}

