<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    17th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminView\FootableScriptsInterface;


/**
 * Admin View Footable Scripts Class.
 * 
 * Loads the stylesheets and scripts a view needs for its Footable tables,
 * and optionally the inline call that initialises them.
 * 
 * Two axes meet here and they are not the same thing. Which Footable
 * release to load is a component setting, so both releases are built here.
 * How an inline script reaches the document is a Joomla target concern, so
 * that is the extension point the target variants override.
 * 
 * @since  6.1.7
 */
class FootableScripts implements FootableScriptsInterface
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
	 * Get the Footable assets of a view.
	 *
	 * @param   bool  $init  Whether to also emit the initialisation script.
	 *
	 * @return  string  The generated asset loader.
	 *
	 * @since   6.1.7
	 */
	public function get(bool $init = true): string
	{
		$footable_version = $this->config->get('footable_version', 2);

		if (2 == $footable_version) // loading version 2
		{
			return $this->getVersionTwo($init);
		}

		if (3 == $footable_version) // loading version 3
		{
			return $this->getVersionThree($init);
		}

		// only 2 and 3 can be configured, so no release means no assets
		return '';
	}

	/**
	 * Get the Footable 2 assets of a view.
	 *
	 * @param   bool  $init  Whether to also emit the initialisation script.
	 *
	 * @return  string  The generated asset loader.
	 *
	 * @since   6.1.7
	 */
	protected function getVersionTwo(bool $init): string
	{
		$component = $this->config->component_code_name;

		$foo = PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " Add the CSS for Footable.";
		$foo .= PHP_EOL . Indent::_(2)
			. "Html::_('stylesheet', 'media/com_"
			. $component
			. "/footable-v2/css/footable.core.min.css', ['version' => 'auto']);";
		$foo .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " Use the Metro Style";
		$foo .= PHP_EOL . Indent::_(2)
			. "if (!isset(\$this->fooTableStyle) || 0 == \$this->fooTableStyle)";
		$foo .= PHP_EOL . Indent::_(2) . "{";
		$foo .= PHP_EOL . Indent::_(3)
			. "Html::_('stylesheet', 'media/com_"
			. $component
			. "/footable-v2/css/footable.metro.min.css', ['version' => 'auto']);";
		$foo .= PHP_EOL . Indent::_(2) . "}";
		$foo .= PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Use the Legacy Style.";
		$foo .= PHP_EOL . Indent::_(2)
			. "elseif (isset(\$this->fooTableStyle) && 1 == \$this->fooTableStyle)";
		$foo .= PHP_EOL . Indent::_(2) . "{";
		$foo .= PHP_EOL . Indent::_(3)
			. "Html::_('stylesheet', 'media/com_"
			. $component
			. "/footable-v2/css/footable.standalone.min.css', ['version' => 'auto']);";
		$foo .= PHP_EOL . Indent::_(2) . "}";
		$foo .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " Add the JavaScript for Footable";
		$foo .= PHP_EOL . Indent::_(2)
			. "Html::_('script', 'media/com_"
			. $component . "/footable-v2/js/footable.js', ['version' => 'auto']);";
		$foo .= PHP_EOL . Indent::_(2)
			. "Html::_('script', 'media/com_"
			. $component
			. "/footable-v2/js/footable.sort.js', ['version' => 'auto']);";
		$foo .= PHP_EOL . Indent::_(2)
			. "Html::_('script', 'media/com_"
			. $component
			. "/footable-v2/js/footable.filter.js', ['version' => 'auto']);";
		$foo .= PHP_EOL . Indent::_(2)
			. "Html::_('script', 'media/com_"
			. $component
			. "/footable-v2/js/footable.paginate.js', ['version' => 'auto']);";
		if ($init)
		{
			$javascript = 'jQuery(document).ready(function() {';
			$javascript .= PHP_EOL . Indent::_(1) . 'jQuery(function () {';
			$javascript .= PHP_EOL . Indent::_(2) . "jQuery('.footable').footable();";
			$javascript .= PHP_EOL . Indent::_(1) . '});';
			$javascript .= PHP_EOL . Indent::_(1) . "jQuery('.nav-tabs').on('click', 'li', function() {";
			$javascript .= PHP_EOL . Indent::_(2) . 'setTimeout(tableFix, 10);';
			$javascript .= PHP_EOL . Indent::_(1) . '});';
			$javascript .= PHP_EOL . Indent::_(0) . '});';
			$javascript .= PHP_EOL . Indent::_(0) . 'function tableFix() {';
			$javascript .= PHP_EOL . Indent::_(1) . "jQuery('.footable').trigger('footable_resize');";
			$javascript .= PHP_EOL . Indent::_(0) . '}';
			$lines = explode(PHP_EOL, $javascript);
			$foo .= PHP_EOL . PHP_EOL . Indent::_(2) . '$footable = ' . var_export(array_shift($lines), true);

			foreach ($lines as $line)
			{
				$foo .= PHP_EOL . Indent::_(3) . '. PHP_EOL . ' . var_export($line, true);
			}

			$foo .= ';';
			$foo .= $this->getInlineScript();
		}

		return $foo;
	}

	/**
	 * Get the Footable 3 assets of a view.
	 *
	 * @param   bool  $init  Whether to also emit the initialisation script.
	 *
	 * @return  string  The generated asset loader.
	 *
	 * @since   6.1.7
	 */
	protected function getVersionThree(bool $init): string
	{
		$component = $this->config->component_code_name;

		$foo = PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " Add the CSS for Footable";
		$foo .= PHP_EOL . Indent::_(2)
			. "Html::_('stylesheet', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', ['version' => 'auto']);";
		$foo .= PHP_EOL . Indent::_(2)
			. "Html::_('stylesheet', 'media/com_"
			. $component
			. "/footable-v3/css/footable.standalone.min.css', ['version' => 'auto']);";
		$foo .= PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Add the JavaScript for Footable (adding all functions)";
		$foo .= PHP_EOL . Indent::_(2)
			. "Html::_('script', 'media/com_"
			. $component
			. "/footable-v3/js/footable.min.js', ['version' => 'auto']);";
		if ($init)
		{
			$javascript = 'jQuery(document).ready(function() {';
			$javascript .= PHP_EOL . Indent::_(1) . 'jQuery(function () {';
			$javascript .= PHP_EOL . Indent::_(2) . "jQuery('.footable').footable();";
			$javascript .= PHP_EOL . Indent::_(1) . '});';
			$javascript .= PHP_EOL . Indent::_(0) . '});';
			$lines = explode(PHP_EOL, $javascript);
			$foo .= PHP_EOL . PHP_EOL . Indent::_(2) . '$footable = ' . var_export(array_shift($lines), true);

			foreach ($lines as $line)
			{
				$foo .= PHP_EOL . Indent::_(3) . '. PHP_EOL . ' . var_export($line, true);
			}

			$foo .= ';';
			$foo .= $this->getInlineScript();
		}

		return $foo;
	}

	/**
	 * Get the call that puts the initialisation script on the document.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getInlineScript(): string
	{
		return PHP_EOL . Indent::_(2)
			. "\$this->getDocument()->getWebAssetManager()->addInlineScript(\$footable);"
			. PHP_EOL;
	}
}

