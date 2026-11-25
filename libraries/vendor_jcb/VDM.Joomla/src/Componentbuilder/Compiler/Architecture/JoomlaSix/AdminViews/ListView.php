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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\AdminViews;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\ListView as SharedListView;


/**
 * Retain authored Joomla 6 list footer execution after the list body.
 * 
 * @since  6.2.0
 */
final class ListView extends SharedListView
{
	/**
	 * Keep authored JavaScript and embedded PHP at their original body position.
	 *
	 * @param   string  $script  The JavaScript body, possibly containing PHP.
	 * @param   string  $name    The list view code name.
	 *
	 * @return  string  The generated template fragment.
	 * @since   6.2.0
	 */
	protected function getFooterScript(string $script, string $name): string
	{
		return parent::getFooterScript($script, $name);
	}
}

