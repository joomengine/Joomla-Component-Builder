<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    19th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Model;


use VDM\Joomla\Componentbuilder\Compiler\Builder\Alias;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\CustomAlias;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Title;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;


/**
 * Model Generate New Title Class.
 * 
 * Builds the method an admin model runs when an item is copied, to give the
 * copy a title of its own rather than the one it was copied from.
 * 
 * @since 6.1.7
 */
final class GenerateNewTitle
{
	/**
	 * The Content One Builder Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * The Alias Builder Class.
	 *
	 * @var   Alias
	 * @since 6.1.7
	 */
	protected Alias $alias;

	/**
	 * The Custom Alias Builder Class.
	 *
	 * @var   CustomAlias
	 * @since 6.1.7
	 */
	protected CustomAlias $customalias;

	/**
	 * The Title Builder Class.
	 *
	 * @var   Title
	 * @since 6.1.7
	 */
	protected Title $title;

	/**
	 * Constructor.
	 *
	 * @param ContentOne  $contentone  The Content One Builder Class.
	 * @param Alias       $alias       The Alias Builder Class.
	 * @param CustomAlias $customalias The Custom Alias Builder Class.
	 * @param Title       $title       The Title Builder Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(ContentOne $contentone,
		Alias $alias,
		CustomAlias $customalias,
		Title $title)
	{
		$this->contentone = $contentone;
		$this->alias = $alias;
		$this->customalias = $customalias;
		$this->title = $title;
	}

	/**
	 * Build the generate new title method of an admin model.
	 *
	 * Only a view with a title to make unique gets one.
	 *
	 * @param   string  $nameSingleCode  The single view name.
	 *
	 * @return  string  The method, or nothing when the view has no title of its own.
	 *
	 * @since   6.1.7
	 */
	public function get($nameSingleCode): string
	{
		// if category is added to this view then do nothing
		if ($this->alias->exists($nameSingleCode)
			&& ($this->title->exists($nameSingleCode)
				|| $this->customalias->exists($nameSingleCode)))
		{
			// get component name
			$Component = $this->contentone->get('Component');
			// rest the new function
			$newFunction   = [];
			$newFunction[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
			$newFunction[] = Indent::_(1)
				. " * Method to change the title/s & alias.";
			$newFunction[] = Indent::_(1) . " *";
			$newFunction[] = Indent::_(1)
				. " * @param   string         \$alias        The alias.";
			$newFunction[] = Indent::_(1)
				. " * @param   string/array   \$title        The title.";
			$newFunction[] = Indent::_(1) . " *";
			$newFunction[] = Indent::_(1)
				. " * @return" . Indent::_(1) . "array/string  Contains the modified title/s and/or alias.";
			$newFunction[] = Indent::_(1) . " *";
			$newFunction[] = Indent::_(1) . " */";
			$newFunction[] = Indent::_(1)
				. "protected function _generateNewTitle(\$alias, \$title = null)";
			$newFunction[] = Indent::_(1) . "{";
			$newFunction[] = PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Alter the title/s & alias";
			$newFunction[] = Indent::_(2) . "\$table = \$this->getTable();";
			$newFunction[] = PHP_EOL . Indent::_(2)
				. "while (\$table->load(['alias' => \$alias]))";
			$newFunction[] = Indent::_(2) . "{";
			$newFunction[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Check if this is an array of titles";
			$newFunction[] = Indent::_(3) . "if ("
				. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$title))";
			$newFunction[] = Indent::_(3) . "{";
			$newFunction[] = Indent::_(4)
				. "foreach(\$title as \$nr => &\$_title)";
			$newFunction[] = Indent::_(4) . "{";
			$newFunction[] = Indent::_(5)
				. "\$_title = StringHelper::increment(\$_title);";
			$newFunction[] = Indent::_(4) . "}";
			$newFunction[] = Indent::_(3) . "}";
			$newFunction[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Make sure we have a title";
			$newFunction[] = Indent::_(3) . "elseif (\$title)";
			$newFunction[] = Indent::_(3) . "{";
			$newFunction[] = Indent::_(4)
				. "\$title = StringHelper::increment(\$title);";
			$newFunction[] = Indent::_(3) . "}";
			$newFunction[] = Indent::_(3)
				. "\$alias = StringHelper::increment(\$alias, 'dash');";
			$newFunction[] = Indent::_(2) . "}";
			$newFunction[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Check if this is an array of titles";
			$newFunction[] = Indent::_(2) . "if ("
				. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$title))";
			$newFunction[] = Indent::_(2) . "{";
			$newFunction[] = Indent::_(3) . "\$title[] = \$alias;";
			$newFunction[] = Indent::_(3) . "return \$title;";
			$newFunction[] = Indent::_(2) . "}";
			$newFunction[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Make sure we have a title";
			$newFunction[] = Indent::_(2) . "elseif (\$title)";
			$newFunction[] = Indent::_(2) . "{";
			$newFunction[] = Indent::_(3) . "return array(\$title, \$alias);";
			$newFunction[] = Indent::_(2) . "}";
			$newFunction[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " We only had an alias";
			$newFunction[] = Indent::_(2) . "return \$alias;";
			$newFunction[] = Indent::_(1) . "}";

			return implode(PHP_EOL, $newFunction);
		}
		elseif ($this->title->exists($nameSingleCode))
		{
			$newFunction   = [];
			$newFunction[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
			$newFunction[] = Indent::_(1) . " * Method to change the title";
			$newFunction[] = Indent::_(1) . " *";
			$newFunction[] = Indent::_(1)
				. " * @param   string   \$title   The title.";
			$newFunction[] = Indent::_(1) . " *";
			$newFunction[] = Indent::_(1)
				. " * @return" . Indent::_(1) . "array  Contains the modified title and alias.";
			$newFunction[] = Indent::_(1) . " *";
			$newFunction[] = Indent::_(1) . " */";
			$newFunction[] = Indent::_(1)
				. "protected function _generateNewTitle(\$title)";
			$newFunction[] = Indent::_(1) . "{";
			$newFunction[] = PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Alter the title";
			$newFunction[] = Indent::_(2) . "\$table = \$this->getTable();";
			$newFunction[] = PHP_EOL . Indent::_(2)
				. "while (\$table->load(['title' => \$title]))";
			$newFunction[] = Indent::_(2) . "{";
			$newFunction[] = Indent::_(3)
				. "\$title = StringHelper::increment(\$title);";
			$newFunction[] = Indent::_(2) . "}";
			$newFunction[] = PHP_EOL . Indent::_(2) . "return \$title;";
			$newFunction[] = Indent::_(1) . "}";

			return implode(PHP_EOL, $newFunction);
		}

		return '';
	}
}

