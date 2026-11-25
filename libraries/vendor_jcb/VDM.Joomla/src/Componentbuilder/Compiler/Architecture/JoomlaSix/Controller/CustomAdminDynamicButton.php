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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Controller;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\Controller\CustomAdminDynamicButton as ExtendingCustomAdminDynamicButton;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Custom admin controller button input for Joomla 6.
 * 
 * @since  6.2.0
 */
final class CustomAdminDynamicButton extends ExtendingCustomAdminDynamicButton
{
	/**
	 * Put the controller request input in scope for the generated method.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getInputObject(): string
	{
		return Indent::_(3) . '$input = $this->input;';
	}
}

