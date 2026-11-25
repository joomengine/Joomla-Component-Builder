<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    21st October, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\ComHelperClass;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\ComHelperClass\ExcelMethods as ExtendingExcelMethods;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Component helper spreadsheet methods for Joomla 6.
 * 
 * @since  6.2.0
 */
final class ExcelMethods extends ExtendingExcelMethods
{
	/**
	 * Get the import-header session from the active application.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getSessionObject(): string
	{
		return Indent::_(2) . "\$session = Joomla__"
			. "_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getSession();";
	}
}

