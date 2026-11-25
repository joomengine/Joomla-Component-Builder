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


use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\GoogleChartLoader as SharedGoogleChartLoader;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Joomla six GoogleChartLoader renderer.
 * 
 * @since 6.2.0
 */
final class GoogleChartLoader extends SharedGoogleChartLoader
{
	/**
	 * Make the native chart helper and official Google loader available.
	 *
	 * @param   array  $view  The view definition.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	public function get(array &$view): string
	{
		if (!$this->googlechart->exists($this->config->build_target . '.' . $view['settings']->code))
		{
			return '';
		}

		return PHP_EOL . Indent::_(2) . "require_once JPATH_ADMINISTRATOR . '/components/com_" . $this->config->component_code_name . "/src/Helper/Chartbuilder.php';"
			. PHP_EOL . Indent::_(2) . "\$jcbChartAssets = \$this->getDocument()->getWebAssetManager();"
			. PHP_EOL . Indent::_(2) . "if (!\$jcbChartAssets->assetExists('script', 'google.charts'))"
			. PHP_EOL . Indent::_(2) . "{"
			. PHP_EOL . Indent::_(3) . "\$jcbChartAssets->registerScript('google.charts', 'https://www.gstatic.com/charts/loader.js');"
			. PHP_EOL . Indent::_(2) . "}"
			. PHP_EOL . Indent::_(2) . "\$jcbChartAssets->useScript('google.charts');";
	}
}

