<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    23rd October, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Model;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\ItemSave as SharedItemSave;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Propagate a missing Joomla 6 API PATCH record through the exception boundary.
 * 
 * @since  6.2.0
 */
final class ItemSave extends SharedItemSave
{
	/**
	 * Report failed PATCH snapshot retrieval without legacy model error storage.
	 *
	 * @return  string  The generated failure statement.
	 * @since   6.2.0
	 */
	protected function patchLoadFailure(): string
	{
		return Indent::_(4) . "throw new \\RuntimeException(Text::_('JLIB_APPLICATION_ERROR_RECORD_LOAD'), 404);";
	}
}

