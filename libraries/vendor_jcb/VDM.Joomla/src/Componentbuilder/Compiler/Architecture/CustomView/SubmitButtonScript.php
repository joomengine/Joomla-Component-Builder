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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView;


use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Utilities\StringHelper;


/**
 * Custom View Submit Button Script Class.
 * 
 * A custom view that submits its form is given the script that does it, unless
 * the view was drawn with a script of its own that already does.
 * 
 * @since 6.1.7
 */
class SubmitButtonScript
{
	/**
	 * Build the submit button script of a custom view.
	 *
	 * @param   array  $view  The view definition.
	 *
	 * @return  string  The script.
	 *
	 * @since   6.1.7
	 */
	public function get(&$view): string
	{
		if (StringHelper::check($view['settings']->default))
		{
			// add the script only if there is none set
			if (strpos(
					(string) $view['settings']->default,
					'Joomla.submitbutton = function('
				) === false)
			{
				$script   = [];
				$script[] = Indent::_(1)
					. "Joomla.submitbutton = function(task) {";
				$script[] = Indent::_(2) . "if (task === '"
					. $view['settings']->code . ".back') {";
				$script[] = Indent::_(3) . "parent.history.back();";
				$script[] = Indent::_(3) . "return false;";
				$script[] = Indent::_(2) . "} else {";
				$script[] = Indent::_(3)
					. "var form = document.getElementById('adminForm');";
				$script[] = Indent::_(3) . "form.task.value = task;";
				$script[] = Indent::_(3) . "form.submit();";
				$script[] = Indent::_(2) . "}";
				$script[] = Indent::_(1) . "}";

				return $this->renderScript(implode(PHP_EOL, $script), $view['settings']->code);
			}
		}

		return '';
	}

	/**
	 * Wrap the generated handler for the target view template.
	 *
	 * @param   string  $script  The submit handler source.
	 * @param   string  $name    The custom view code name.
	 *
	 * @return  string  The generated template fragment.
	 * @since   6.2.0
	 */
	protected function renderScript(string $script, string $name): string
	{
		return PHP_EOL . '<script type="text/javascript">' . PHP_EOL
			. $script . PHP_EOL . '</script>';
	}
}

