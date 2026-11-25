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
use VDM\Joomla\Componentbuilder\Compiler\Builder\CategoryCode;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\BatchMoveInterface;


/**
 * Model BatchMove Class.
 * 
 * Builds the batchMove method of an admin model: the guards that
 * decide whether the current user may move a record, and the update that
 * moves it.
 * 
 * Only how the current user is put in scope differs between Joomla targets,
 * so that is the extension point the target variants override.
 * 
 * @since  6.1.7
 */
class BatchMove implements BatchMoveInterface
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
	 * @param Config         $config           The Config Class.
	 * @param Permission     $permission       The Permission Class.
	 * @param Dispenser      $dispenser        The Dispenser Class.
	 * @param CategoryCode   $categorycode     The Category Code Class.
	 * @param ContentOne     $contentone       The ContentOne Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Permission $permission,
		Dispenser $dispenser,
		CategoryCode $categorycode,
		ContentOne $contentone)
	{
		$this->config = $config;
		$this->permission = $permission;
		$this->dispenser = $dispenser;
		$this->categorycode = $categorycode;
		$this->contentone = $contentone;
	}

	/**
	 * Build the batchMove method of an admin model.
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
		$category  = $this->categorycode->getString("{$nameSingleCode}.code");
		$batchmove = [];
		$VIEW      = StringHelper::safe($nameSingleCode, 'U');
		// component helper name
		$Helper = $this->contentone->get('Component') . 'Helper';
		// prepare custom script
		$customScript = $this->dispenser->get(
			'php_batchmove', $nameSingleCode, PHP_EOL . PHP_EOL, null, true
		);

		$batchmove[] = PHP_EOL . Indent::_(1) . "/**";
		$batchmove[] = Indent::_(1) . " * Batch move items to a new category";
		$batchmove[] = Indent::_(1) . " *";
		$batchmove[] = Indent::_(1)
			. " * @param   integer  \$value     The new category ID.";
		$batchmove[] = Indent::_(1)
			. " * @param   array    \$pks       An array of row IDs.";
		$batchmove[] = Indent::_(1)
			. " * @param   array    \$contexts  An array of item contexts.";
		$batchmove[] = Indent::_(1) . " *";
		$batchmove[] = Indent::_(1)
			. " * @return  boolean  True if successful, false otherwise and internal error is set.";
		$batchmove[] = Indent::_(1) . " *";
		$batchmove[] = Indent::_(1) . " * @since 12.2";
		$batchmove[] = Indent::_(1) . " */";
		$batchmove[] = Indent::_(1)
			. "protected function batchMove(\$values, \$pks, \$contexts)";
		$batchmove[] = Indent::_(1) . "{";
		$batchmove[] = Indent::_(2) . "if (empty(\$this->batchSet))";
		$batchmove[] = Indent::_(2) . "{";
		$batchmove[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Set some needed variables.";
		$batchmove[] = $this->getUserObject();
		$batchmove[] = Indent::_(3)
			. "\$this->table" . Indent::_(2) . "= \$this->getTable();";
		$batchmove[] = Indent::_(3)
			. "\$this->tableClassName" . Indent::_(1) . "= get_class(\$this->table);";
		$batchmove[] = Indent::_(3) . "\$this->canDo" . Indent::_(2) . "= " . Selection::permittedActionsToken() . "::get('" . $nameSingleCode . "');";
		$batchmove[] = Indent::_(2) . "}";

		$batchmove[] = PHP_EOL . Indent::_(2) . "if (!\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.edit') . "') && !\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.batch') . "'))";

		$batchmove[] = Indent::_(2) . "{";
		$batchmove[] = Indent::_(3) . "\$this->setError(Text:"
			. ":_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));";
		$batchmove[] = Indent::_(3) . "return false;";
		$batchmove[] = Indent::_(2) . "}" . $customScript;

		$batchmove[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " make sure published only updates if user has the permission.";
		$batchmove[] = Indent::_(2)
			. "if (isset(\$values['published']) && !\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.edit.state') . "'))";

		$batchmove[] = Indent::_(2) . "{";
		$batchmove[] = Indent::_(3) . "unset(\$values['published']);";
		$batchmove[] = Indent::_(2) . "}";

		$batchmove[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " remove move_copy from array";
		$batchmove[] = Indent::_(2) . "unset(\$values['move_copy']);";

		if ($category !== null)
		{
			$batchmove[] = PHP_EOL . Indent::_(2)
				. "if (isset(\$values['category']) && (int) \$values['category'] > 0 && !static::checkCategoryId(\$values['category']))";
			$batchmove[] = Indent::_(2) . "{";
			$batchmove[] = Indent::_(3) . "return false;";
			$batchmove[] = Indent::_(2) . "}";
			$batchmove[] = Indent::_(2)
				. "elseif (isset(\$values['category']) && (int) \$values['category'] > 0)";
			$batchmove[] = Indent::_(2) . "{";
			$batchmove[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " move the category value to correct field name";
			$batchmove[] = Indent::_(3) . "\$values['" . $category
				. "'] = \$values['category'];";
			$batchmove[] = Indent::_(3) . "unset(\$values['category']);";
			$batchmove[] = Indent::_(2) . "}";
			$batchmove[] = Indent::_(2)
				. "elseif (isset(\$values['category']))";
			$batchmove[] = Indent::_(2) . "{";
			$batchmove[] = Indent::_(3) . "unset(\$values['category']);";
			$batchmove[] = Indent::_(2) . "}" . PHP_EOL;
		}

		$batchmove[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Parent exists so we proceed";
		$batchmove[] = Indent::_(2) . "foreach (\$pks as \$pk)";
		$batchmove[] = Indent::_(2) . "{";
		$batchmove[] = Indent::_(3) . "if (!\$this->user->authorise('"
			. $this->permission->getAction($nameSingleCode, 'core.edit') . "', \$contexts[\$pk]))";
		$batchmove[] = Indent::_(3) . "{";
		$batchmove[] = Indent::_(4) . "\$this->setError(Text:"
			. ":_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));";

		$batchmove[] = Indent::_(4) . "return false;";
		$batchmove[] = Indent::_(3) . "}";

		$batchmove[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Check that the row actually exists";
		$batchmove[] = Indent::_(3) . "if (!\$this->table->load(\$pk))";
		$batchmove[] = Indent::_(3) . "{";
		$batchmove[] = Indent::_(4)
			. "if (\$error = \$this->table->getError())";
		$batchmove[] = Indent::_(4) . "{";
		$batchmove[] = Indent::_(5) . "//" . Line::_(__Line__, __Class__)
			. " Fatal error";
		$batchmove[] = Indent::_(5) . "\$this->setError(\$error);";

		$batchmove[] = Indent::_(5) . "return false;";
		$batchmove[] = Indent::_(4) . "}";
		$batchmove[] = Indent::_(4) . "else";
		$batchmove[] = Indent::_(4) . "{";
		$batchmove[] = Indent::_(5) . "//" . Line::_(__Line__, __Class__)
			. " Not fatal error";
		$batchmove[] = Indent::_(5) . "\$this->setError(Text:"
			. ":sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', \$pk));";
		$batchmove[] = Indent::_(5) . "continue;";
		$batchmove[] = Indent::_(4) . "}";
		$batchmove[] = Indent::_(3) . "}";

		$batchmove[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " insert all set values.";
		$batchmove[] = Indent::_(3) . "if ("
			. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$values))";
		$batchmove[] = Indent::_(3) . "{";
		$batchmove[] = Indent::_(4) . "foreach (\$values as \$key => \$value)";
		$batchmove[] = Indent::_(4) . "{";
		$batchmove[] = Indent::_(5) . "//" . Line::_(__Line__, __Class__)
			. " Do special action for access.";
		$batchmove[] = Indent::_(5)
			. "if ('access' === \$key && strlen(\$value) > 0)";
		$batchmove[] = Indent::_(5) . "{";
		$batchmove[] = Indent::_(6) . "\$this->table->\$key = \$value;";
		$batchmove[] = Indent::_(5) . "}";
		$batchmove[] = Indent::_(5)
			. "elseif (strlen(\$value) > 0 && isset(\$this->table->\$key))";
		$batchmove[] = Indent::_(5) . "{";
		$batchmove[] = Indent::_(6) . "\$this->table->\$key = \$value;";
		$batchmove[] = Indent::_(5) . "}";
		$batchmove[] = Indent::_(4) . "}";
		$batchmove[] = Indent::_(3) . "}" . PHP_EOL;

		$batchmove[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Check the row.";
		$batchmove[] = Indent::_(3) . "if (!\$this->table->check())";
		$batchmove[] = Indent::_(3) . "{";
		$batchmove[] = Indent::_(4)
			. "\$this->setError(\$this->table->getError());";

		$batchmove[] = PHP_EOL . Indent::_(4) . "return false;";
		$batchmove[] = Indent::_(3) . "}";

		$batchmove[] = PHP_EOL . Indent::_(3) . "if (!empty(\$this->type))";
		$batchmove[] = Indent::_(3) . "{";
		$batchmove[] = Indent::_(4)
			. "\$this->createTagsHelper(\$this->tagsObserver, \$this->type, \$pk, \$this->typeAlias, \$this->table);";
		$batchmove[] = Indent::_(3) . "}";

		$batchmove[] = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Store the row.";
		$batchmove[] = Indent::_(3) . "if (!\$this->table->store())";
		$batchmove[] = Indent::_(3) . "{";
		$batchmove[] = Indent::_(4)
			. "\$this->setError(\$this->table->getError());";

		$batchmove[] = PHP_EOL . Indent::_(4) . "return false;";
		$batchmove[] = Indent::_(3) . "}";
		$batchmove[] = Indent::_(2) . "}";

		$batchmove[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Clean the cache";
		$batchmove[] = Indent::_(2) . "\$this->cleanCache();";

		$batchmove[] = PHP_EOL . Indent::_(2) . "return true;";
		$batchmove[] = Indent::_(1) . "}";

		return PHP_EOL . implode(PHP_EOL, $batchmove);
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
			. "\$this->user" . Indent::_(2) . "= Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getIdentity();";
	}
}

