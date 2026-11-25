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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\AdminView;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\FadeInEffect as SharedFadeInEffect;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Joomla six loading overlay registration and document readiness.
 * 
 * @since 6.2.0
 */
final class FadeInEffect extends SharedFadeInEffect
{
	/**
	 * Register the overlay through WAM without touching the body before it exists.
	 *
	 * @param   string  $script     Generated overlay JavaScript.
	 * @param   string  $component  Component code name.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function renderScript(string $script, string $component): string
	{
		$loader = json_encode($component . '_loader', JSON_THROW_ON_ERROR);
		$name = var_export('com_' . $component . '.loading-overlay', true);
		$javascript = '(function () {';
		$javascript .= PHP_EOL . Indent::_(1) . 'const initialise = function () {';
		$javascript .= PHP_EOL . Indent::_(2) . "if (document.readyState === 'complete') {";
		$javascript .= PHP_EOL . Indent::_(3) . "const container = document.getElementById({$loader});";
		$javascript .= PHP_EOL . Indent::_(3) . 'if (container) {';
		$javascript .= PHP_EOL . Indent::_(4) . "container.style.display = 'block';";
		$javascript .= PHP_EOL . Indent::_(3) . '}';
		$javascript .= PHP_EOL . Indent::_(3) . 'return;';
		$javascript .= PHP_EOL . Indent::_(2) . '}';

		foreach (explode(PHP_EOL, $script) as $line)
		{
			$javascript .= PHP_EOL . Indent::_(1) . $line;
		}

		$javascript .= PHP_EOL . Indent::_(1) . '};';
		$javascript .= PHP_EOL . Indent::_(1) . "if (document.readyState === 'loading') {";
		$javascript .= PHP_EOL . Indent::_(2) . "document.addEventListener('DOMContentLoaded', initialise, {once: true});";
		$javascript .= PHP_EOL . Indent::_(1) . '} else {';
		$javascript .= PHP_EOL . Indent::_(2) . 'initialise();';
		$javascript .= PHP_EOL . Indent::_(1) . '}';
		$javascript .= PHP_EOL . Indent::_(0) . '})();';
		$lines = explode(PHP_EOL, $javascript);
		$code = '<?php';
		$code .= PHP_EOL . Indent::_(0) . '$jcbLoadingOverlay = ' . var_export(array_shift($lines), true);

		foreach ($lines as $line)
		{
			$code .= PHP_EOL . Indent::_(1) . '. PHP_EOL . ' . var_export($line, true);
		}

		$code .= ';';
		$code .= PHP_EOL . Indent::_(0) . '$this->getDocument()->getWebAssetManager()->addInlineScript(';
		$code .= PHP_EOL . Indent::_(1) . "\$jcbLoadingOverlay, ['name' => {$name}], [], ['core']";
		$code .= PHP_EOL . Indent::_(0) . ');';
		$code .= PHP_EOL . Indent::_(0) . '?>';

		return $code;
	}
}

