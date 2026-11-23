<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Library;


/**
 * Library Include Helper
 * 
 * @since 5.1.2
 */
final class IncludeHelper
{
	/**
	 * Get the Library Include string for a given path.
	 *
	 * @param   string      $path      The path to the library.
	 * @param   array|null  $pathInfo  Optional path info (optimization if already known).
	 *
	 * @return  string  The include string (JS, CSS, PHP, etc.)
	 * @since   5.1.2
	 */
	public function get(string $path, ?array $pathInfo = null): string
	{
		$pathInfo ??= pathinfo($path);
		$extension = strtolower($pathInfo['extension'] ?? '');

		$trimmedPath = ltrim($path, '/');

		return match ($extension) {
			'js'   => sprintf("Joomla__"."_34690c75_1090_47eb_8c06_7228dc7eedd6___Power::_('script', '%s', ['version' => 'auto']);", $trimmedPath),
			'css',
			'less' => sprintf("Joomla__"."_34690c75_1090_47eb_8c06_7228dc7eedd6___Power::_('stylesheet', '%s', ['version' => 'auto']);", $trimmedPath),
			'php'  => str_starts_with($path, 'http') ? '' : sprintf('require_once("%s");', $path),
			default => '',
		};
	}
}

