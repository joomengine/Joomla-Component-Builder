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

namespace VDM\Joomla\Componentbuilder\Compiler\Model\JoomlaSix;


use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Model\Javascriptadminview as SharedJavascriptadminview;


/**
 * Collect Joomla 6 view scripts with their original authored footer semantics.
 * 
 * @since 6.2.0
 */
final class Javascriptadminview extends SharedJavascriptadminview
{
	/**
	 * Inject the shared custom-code collector for the Joomla 6 target.
	 *
	 * @param   Dispenser  $dispenser  Existing custom-code collection service.
	 *
	 * @since   6.2.0
	 */
	public function __construct(Dispenser $dispenser)
	{
		parent::__construct($dispenser);
	}
}

