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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\View;


use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * View jQuery Class.
 * 
 * Builds the statement a view loads jQuery with.
 * 
 * @since 6.1.7
 */
final class Jquery
{
	/**
	 * Build the jQuery loading statement of a view.
	 *
	 * @param   array  $view  The view being built.
	 *
	 * @return  string  The statement.
	 *
	 * @since   6.1.7
	 */
	public function get(&$view): string
	{
		$addJQuery = '';
		if (true) // TODO we just add it everywhere for now.
		{
			$addJQuery .= PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " Load jQuery";
			$addJQuery .= PHP_EOL . Indent::_(2) . "Html::_('jquery.framework');";
		}

		return $addJQuery;
	}
}

