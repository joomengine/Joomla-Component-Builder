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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\DynamicButtons as BaseDynamicButtons;


/**
 * Joomla 6 dynamic buttons renderer.
 * 
 * @since  6.2.0
 */
final class DynamicButtons extends BaseDynamicButtons
{
	/**
	 * Render a redirect button on the existing document toolbar.
	 *
	 * @param   string  $task   Controller task.
	 * @param   string  $icon   Button icon.
	 * @param   string  $label  Translation key.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function button(string $task, string $icon, string $label): string
	{
		$icon = preg_replace('#\.[^.]*$#', '', $icon);

		return '$this->getDocument()->getToolbar()->standardButton('
			. var_export($icon, true) . ', ' . var_export($label, true) . ', ' . var_export($task, true)
			. ')->listCheck(true);';
	}
}

