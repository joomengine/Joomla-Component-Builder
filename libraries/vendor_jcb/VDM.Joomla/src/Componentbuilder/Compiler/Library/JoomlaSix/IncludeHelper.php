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

namespace VDM\Joomla\Componentbuilder\Compiler\Library\JoomlaSix;


use VDM\Joomla\Componentbuilder\Compiler\Library\IncludeHelper as SharedIncludeHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Joomla six IncludeHelper renderer.
 * 
 * @since 6.2.0
 */
final class IncludeHelper extends SharedIncludeHelper
{
	/**
	 * Register and activate a library asset while retaining declaration order.
	 *
	 * @param   string      $path      The library URI or local PHP path.
	 * @param   array|null  $pathInfo  Optional path metadata.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	public function get(string $path, ?array $pathInfo = null): string
	{
		$pathInfo ??= pathinfo((string) (parse_url($path, PHP_URL_PATH) ?? $path));
		$extension = strtolower($pathInfo['extension'] ?? '');

		if (!in_array($extension, ['js', 'css', 'less'], true))
		{
			return parent::get($path, $pathInfo);
		}

		return $this->render($path, $extension, '$jcbAssetManager', 0, false);
	}

	/**
	 * Emit a view script with the application or asset manager already in scope.
	 *
	 * @param   string  $path       The script path.
	 * @param   int     $indent     The surrounding indentation level.
	 * @param   bool    $reuseWa    Whether the template has already assigned $wa.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	public function getViewScript(string $path, int $indent, bool $reuseWa = false): string
	{
		return $this->render($path, 'js', $reuseWa ? '$wa' : '$jcbAssetManager', $indent, true);
	}

	/**
	 * Build the native registration, activation and dependency chain.
	 *
	 * @param   string  $path       The script or style URI.
	 * @param   string  $extension  The file extension.
	 * @param   string  $manager    The generated manager variable.
	 * @param   int     $indent     The surrounding indentation level.
	 * @param   bool    $view       Whether this is a view's own script.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	private function render(string $path, string $extension, string $manager, int $indent, bool $view): string
	{
		$type = $extension === 'js' ? 'script' : 'style';
		$uri = str_starts_with($path, '//') ? $path : ltrim($path, '/');
		$name = 'jcb.' . $type . '.' . hash('sha256', $uri);
		$method = ucfirst($type);
		$dependencies = '$jcb' . $method . 'Dependencies';
		$nameCode = var_export($name, true);
		$uriCode = var_export($uri, true);

		if (!str_starts_with($uri, '//') && preg_match('#^[a-z][a-z0-9+.-]*:#i', $uri) !== 1)
		{
			$uriCode = 'Joomla__' . '_eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root() . ' . $uriCode;
		}

		$outer = Indent::_($indent);
		$inner = Indent::_($indent + 1);
		$code = '';

		if (!$view)
		{
			$code = $manager . ' = Joomla__' . '_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getDocument()->getWebAssetManager();' . PHP_EOL;
		}
		elseif ($manager !== '$wa')
		{
			$code = $outer . $manager . ' = $this->app->getDocument()->getWebAssetManager();' . PHP_EOL . PHP_EOL;
		}

		$code .= $outer . "if (!{$manager}->assetExists('{$type}', {$nameCode}))"
			. PHP_EOL . $outer . '{';

		if ($view)
		{
			$args = Indent::_($indent + 2);
			$code .= PHP_EOL . $inner . "{$manager}->register{$method}("
				. PHP_EOL . $args . "{$nameCode},"
				. PHP_EOL . $args . "{$uriCode},"
				. PHP_EOL . $args . "['version' => 'auto'],"
				. PHP_EOL . $args . '[],'
				. PHP_EOL . $args . "{$dependencies} ?? []"
				. PHP_EOL . $inner . ');';
		}
		else
		{
			$code .= PHP_EOL . $inner . "{$manager}->register{$method}({$nameCode}, {$uriCode}, ['version' => 'auto'], [], {$dependencies} ?? []);";
		}

		$code .= PHP_EOL . $outer . '}'
			. ($view ? PHP_EOL : '')
			. PHP_EOL . $outer . "{$manager}->use{$method}({$nameCode});";

		if ($view)
		{
			return $code . PHP_EOL . $outer . "{$dependencies} = array_values(array_unique(array_merge("
				. PHP_EOL . $inner . "{$dependencies} ?? [],"
				. PHP_EOL . $inner . "[{$nameCode}]"
				. PHP_EOL . $outer . ')));';
		}

		return $code . PHP_EOL . $outer . "{$dependencies} = array_values(array_unique(array_merge({$dependencies} ?? [], [{$nameCode}])));";
	}
}

