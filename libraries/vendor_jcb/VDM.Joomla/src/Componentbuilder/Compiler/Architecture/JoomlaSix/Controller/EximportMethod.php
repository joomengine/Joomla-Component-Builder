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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Controller;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\Controller\EximportMethod as ExtendingEximportMethod;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Admin list controller export and import methods for Joomla 6.
 * 
 * @since  6.2.0
 */
final class EximportMethod extends ExtendingEximportMethod
{
	/**
	 * Get the import session from the controller's application.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getSessionObject(): string
	{
		return Indent::_(4) . '$session = $this->app->getSession();';
	}

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

