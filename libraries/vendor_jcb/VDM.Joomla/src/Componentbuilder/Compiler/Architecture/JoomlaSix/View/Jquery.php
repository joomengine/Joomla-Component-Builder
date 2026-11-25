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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\View;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\Jquery as SharedJquery;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Joomla six Jquery renderer.
 * 
 * @since 6.2.0
 */
final class Jquery extends SharedJquery
{
	/**
	 * Load the core jQuery assets for the view.
	 *
	 * @param   array  $view  The view definition.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	public function get(&$view): string
	{
		return PHP_EOL . Indent::_(2) . "\$this->getDocument()->getWebAssetManager()->useScript('jquery')->useScript('jquery-noconflict');";
	}
}

