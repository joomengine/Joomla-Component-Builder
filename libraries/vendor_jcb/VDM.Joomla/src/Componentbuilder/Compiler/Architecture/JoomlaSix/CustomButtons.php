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


use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomButtons as BaseCustomButtons;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Joomla 6 custom buttons renderer.
 * 
 * @since  6.2.0
 */
final class CustomButtons extends BaseCustomButtons
{
	/**
	 * Register the toolbar submit handler through Joomla's asset manager.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	public function javascript(): string
	{
		$script = 'Joomla.submitbutton = function(task) {';
		$script .= PHP_EOL . Indent::_(1) . "if (task == '')";
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= PHP_EOL . Indent::_(2) . 'return false;';
		$script .= PHP_EOL . Indent::_(1) . '}';
		$script .= PHP_EOL . Indent::_(1) . 'else';
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= PHP_EOL . Indent::_(2) . 'Joomla.submitform(task);';
		$script .= PHP_EOL . Indent::_(2) . 'return true;';
		$script .= PHP_EOL . Indent::_(1) . '}';
		$script .= PHP_EOL . Indent::_(0) . '};';
		$lines = explode(PHP_EOL, $script);
		$code = PHP_EOL . Indent::_(2) . '$jcbToolbarScript = ' . var_export(array_shift($lines), true);

		foreach ($lines as $line)
		{
			$code .= PHP_EOL . Indent::_(3) . '. PHP_EOL . ' . var_export($line, true);
		}

		$code .= ';';
		$code .= PHP_EOL . Indent::_(2) . '$this->getDocument()->getWebAssetManager()->useScript(\'core\')'
			. '->addInlineScript($jcbToolbarScript'
			. ", ['name' => 'com_" . $this->config->component_code_name . ".toolbar-buttons'], [], ['core']);";

		return $code;
	}

	/**
	 * Render a button on the existing document toolbar without legacy arguments.
	 *
	 * @param   string  $task            Controller task.
	 * @param   string  $icon            Button icon and additional classes.
	 * @param   string  $label           Translation key.
	 * @param   bool    $selection       Whether a list selection is required.
	 * @param   bool    $quoteSelection  Earlier-target formatting hint, unused here.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function button(
		string $task, string $icon, string $label, bool $selection, bool $quoteSelection = false
	): string
	{
		$icon = preg_replace('#\.[^.]*$#', '', $icon);

		return '$this->getDocument()->getToolbar()->standardButton('
			. var_export($icon, true) . ', ' . var_export($label, true) . ', ' . var_export($task, true)
			. ')->listCheck(' . ($selection ? 'true' : 'false') . ');';
	}
}

