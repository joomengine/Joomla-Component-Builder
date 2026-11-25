<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    16th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * View Fade In Effect Class.
 * 
 * Generates the loading overlay a view shows while the page is still
 * loading, together with the component loader container that the overlay
 * reveals once the load event fires. When the view does not use the effect
 * only the plain loader container is generated.
 * 
 * @since  6.1.7
 */
class FadeInEffect
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * Constructor.
	 *
	 * @param Config   $config   The Config Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * Get the view loading overlay and loader container.
	 *
	 * @param   array  $view  The view definition with its settings object.
	 *
	 * @return  string  The generated overlay and container markup.
	 *
	 * @since   6.1.7
	 */
	public function get(array $view): string
	{
		$component = $this->config->component_code_name;

		// check if we should load the fade in affect
		if ($view['settings']->add_fadein == 1)
		{
			$fadein   = [];
			$fadein[] = Indent::_(1) . "(function() {";
			$fadein[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " create loading overlay";
			$fadein[] = Indent::_(2) . "var loadingDiv = document.createElement('div');";
			$fadein[] = Indent::_(2) . "loadingDiv.id = 'loading';";

			// robust styling (no size calculations)
			$fadein[] = Indent::_(2) . "loadingDiv.style.position = 'fixed';";
			$fadein[] = Indent::_(2) . "loadingDiv.style.top = '0';";
			$fadein[] = Indent::_(2) . "loadingDiv.style.left = '0';";
			$fadein[] = Indent::_(2) . "loadingDiv.style.right = '0';";
			$fadein[] = Indent::_(2) . "loadingDiv.style.bottom = '0';";
			$fadein[] = Indent::_(2) . "loadingDiv.style.width = '100%';";
			$fadein[] = Indent::_(2) . "loadingDiv.style.height = '100%';";
			$fadein[] = Indent::_(2) . "loadingDiv.style.background = \"rgba(255,255,255,0.8) url('components/com_{$component}/assets/images/ajax.gif') 50% 35% no-repeat\";";
			$fadein[] = Indent::_(2) . "loadingDiv.style.opacity = '0.8';";
			$fadein[] = Indent::_(2) . "loadingDiv.style.zIndex = '9999';";
			$fadein[] = Indent::_(2) . "loadingDiv.style.display = 'block';";

			// IE fallback (harmless elsewhere)
			$fadein[] = Indent::_(2) . "loadingDiv.style.msFilter = \"progid:DXImageTransform.Microsoft.Alpha(Opacity=80)\";";
			$fadein[] = Indent::_(2) . "loadingDiv.style.filter = \"alpha(opacity=80)\";";

			$fadein[] = Indent::_(2) . "document.body.appendChild(loadingDiv);";

			$fadein[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " remove overlay when page fully loaded";
			$fadein[] = Indent::_(2) . "window.addEventListener('load', function() {";
			$fadein[] = Indent::_(3) . "var componentLoader = document.getElementById('{$component}_loader');";
			$fadein[] = Indent::_(3) . "if (componentLoader)";
			$fadein[] = Indent::_(3) . "{";
			$fadein[] = Indent::_(4) . "componentLoader.style.display = 'block';";
			$fadein[] = Indent::_(3) . "}";
			$fadein[] = Indent::_(3) . "loadingDiv.style.display = 'none';";
			$fadein[] = Indent::_(2) . "});";

			$fadein[] = Indent::_(1) . "})();";
			return $this->renderScript(implode(PHP_EOL, $fadein), $component)
				. PHP_EOL . "<div id=\"{$component}_loader\" style=\"display: none;\">";
		}

		return "<div id=\"{$component}_loader\">";
	}

	/**
	 * Wrap the generated overlay script for the selected output target.
	 *
	 * @param   string  $script     Generated overlay JavaScript.
	 * @param   string  $component  Component code name.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function renderScript(string $script, string $component): string
	{
		return '<script type="text/javascript">' . PHP_EOL . $script . PHP_EOL . '</script>';
	}
}

