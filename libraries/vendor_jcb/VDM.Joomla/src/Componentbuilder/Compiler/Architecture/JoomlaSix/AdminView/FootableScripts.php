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


use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\FootableScripts as SharedFootableScripts;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Joomla six Footable assets and their explicit dependencies.
 * 
 * @since 6.2.0
 */
final class FootableScripts extends SharedFootableScripts
{
	/**
	 * Build the Footable two asset graph.
	 *
	 * @param   bool  $init  Whether to initialise tables.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getVersionTwo(bool $init): string
	{
		$root = 'media/com_' . $this->config->component_code_name . '/footable-v2/';
		$code = $this->asset('style', $root . 'css/footable.core.min.css');
		$code .= PHP_EOL . Indent::_(2) . 'if (!isset($this->fooTableStyle) || 0 == $this->fooTableStyle)';
		$code .= PHP_EOL . Indent::_(2) . '{';
		$code .= $this->asset('style', $root . 'css/footable.metro.min.css', [], 3);
		$code .= PHP_EOL . Indent::_(2) . '}';
		$code .= PHP_EOL . Indent::_(2) . 'elseif (1 == $this->fooTableStyle)';
		$code .= PHP_EOL . Indent::_(2) . '{';
		$code .= $this->asset('style', $root . 'css/footable.standalone.min.css', [], 3);
		$code .= PHP_EOL . Indent::_(2) . '}';
		$dependencies = ['jquery', 'jquery-noconflict'];

		foreach (['footable.js', 'footable.sort.js', 'footable.filter.js', 'footable.paginate.js'] as $file)
		{
			$uri = $root . 'js/' . $file;
			$code .= $this->asset('script', $uri, $dependencies);
			$dependencies[] = 'jcb.script.' . hash('sha256', $uri);
		}

		if ($init)
		{
			$script = 'jQuery(document).ready(function() {';
			$script .= PHP_EOL . Indent::_(1) . "jQuery('.footable').footable();";
			$script .= PHP_EOL . Indent::_(1) . "jQuery('.nav-tabs').on('click', 'li', function() {";
			$script .= PHP_EOL . Indent::_(2) . 'setTimeout(tableFix, 10);';
			$script .= PHP_EOL . Indent::_(1) . '});';
			$script .= PHP_EOL . Indent::_(0) . '});';
			$script .= PHP_EOL . Indent::_(0) . 'function tableFix() {';
			$script .= PHP_EOL . Indent::_(1) . "jQuery('.footable').trigger('footable_resize');";
			$script .= PHP_EOL . Indent::_(0) . '}';
			$code .= $this->inline($dependencies, $script);
		}

		return $code;
	}

	/**
	 * Build the Footable three asset graph.
	 *
	 * @param   bool  $init  Whether to initialise tables.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getVersionThree(bool $init): string
	{
		$root = 'media/com_' . $this->config->component_code_name . '/footable-v3/';
		$code = $this->asset('style', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
		$code .= $this->asset('style', $root . 'css/footable.standalone.min.css');
		$uri = $root . 'js/footable.min.js';
		$code .= $this->asset('script', $uri, ['jquery', 'jquery-noconflict']);

		if ($init)
		{
			$script = 'jQuery(document).ready(function() {';
			$script .= PHP_EOL . Indent::_(1) . "jQuery('.footable').footable();";
			$script .= PHP_EOL . Indent::_(0) . '});';
			$code .= $this->inline(['jcb.script.' . hash('sha256', $uri)], $script);
		}

		return $code;
	}

	/**
	 * Register an asset once, then activate it.
	 *
	 * @param   string  $type          Asset type.
	 * @param   string  $uri           Asset URI.
	 * @param   array   $dependencies  Required asset names.
	 * @param   int     $indent        Code indentation level.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function asset(string $type, string $uri, array $dependencies = [], int $indent = 2): string
	{
		$name = var_export('jcb.' . $type . '.' . hash('sha256', $uri), true);
		$path = var_export($uri, true);
		$requires = '[' . implode(', ', array_map(static fn ($dependency) => var_export($dependency, true), $dependencies)) . ']';
		$method = ucfirst($type);

		return PHP_EOL . Indent::_($indent) . '$jcbFootableAssets = $this->getDocument()->getWebAssetManager();'
			. PHP_EOL . Indent::_($indent) . "if (!\$jcbFootableAssets->assetExists('{$type}', {$name}))"
			. PHP_EOL . Indent::_($indent) . '{'
			. PHP_EOL . Indent::_($indent + 1) . "\$jcbFootableAssets->register{$method}({$name}, {$path}, ['version' => 'auto'], [], {$requires});"
			. PHP_EOL . Indent::_($indent) . '}'
			. PHP_EOL . Indent::_($indent) . "\$jcbFootableAssets->use{$method}({$name});";
	}

	/**
	 * Initialise only after every required library has loaded.
	 *
	 * @param   array   $dependencies  Required script names.
	 * @param   string  $script        Initialisation script.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function inline(array $dependencies, string $script): string
	{
		$name = var_export('com_' . $this->config->component_code_name . '.footable.init', true);
		$requires = '[' . implode(', ', array_map(static fn ($dependency) => var_export($dependency, true), $dependencies)) . ']';
		$lines = explode(PHP_EOL, $script);
		$code = PHP_EOL . Indent::_(2) . '$footable = ' . var_export(array_shift($lines), true);

		foreach ($lines as $line)
		{
			$code .= PHP_EOL . Indent::_(3) . '. PHP_EOL . ' . var_export($line, true);
		}

		$code .= ';';
		$code .= PHP_EOL . Indent::_(2) . "\$this->getDocument()->getWebAssetManager()->addInlineScript(\$footable, ['name' => {$name}], [], {$requires});";

		return $code . PHP_EOL;
	}
}

