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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\View;


use VDM\Joomla\Componentbuilder\Compiler\Builder\GoogleChart;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * View Google Chart Loader Class.
 * 
 * Builds the statements a view runs to load the google chart builder and the
 * scripts it draws with. Only a view that was found to have a chart on it gets
 * them.
 * 
 * @since  6.1.7
 */
final class GoogleChartLoader
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Google Chart Builder Class.
	 *
	 * @var   GoogleChart
	 * @since 6.1.7
	 */
	protected GoogleChart $googlechart;

	/**
	 * Constructor.
	 *
	 * @param Config       $config       The Config Class.
	 * @param GoogleChart  $googlechart  The Google Chart Builder Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config, GoogleChart $googlechart)
	{
		$this->config = $config;
		$this->googlechart = $googlechart;
	}

	/**
	 * Build the statements that load the google chart assets a view needs.
	 *
	 * @param   array  $view  The view being built.
	 *
	 * @return  string  The statements, or nothing when the view has no chart.
	 *
	 * @since   6.1.7
	 */
	public function get(array &$view): string
	{
		if ($this->googlechart->
			exists($this->config->build_target . '.' . $view['settings']->code))
		{
			$chart   = [];
			$chart[] = PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " add the google chart builder class.";
			$chart[] = Indent::_(2)
				. "require_once JPATH_ADMINISTRATOR . '/components/com_" . $this->config->component_code_name . "/helpers/chartbuilder.php';";
			$chart[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " load the google chart js.";
			$chart[] = Indent::_(2)
				. "Html::_('script', 'media/com_"
				. $this->config->component_code_name . "/js/google.jsapi.js', ['version' => 'auto']);";
			$chart[] = Indent::_(2)
				. "Html::_('script', 'https://canvg.googlecode.com/svn/trunk/rgbcolor.js', ['version' => 'auto']);";
			$chart[] = Indent::_(2)
				. "Html::_('script', 'https://canvg.googlecode.com/svn/trunk/canvg.js', ['version' => 'auto']);";

			return implode(PHP_EOL, $chart);
		}

		return '';
	}
}

