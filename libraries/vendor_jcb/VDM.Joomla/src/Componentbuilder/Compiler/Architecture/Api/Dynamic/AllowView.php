<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    2nd September, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Dynamic;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Resources;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * The read permission of a dynamic get API controller
 * 
 * @since 6.1.7
 */
class AllowView
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
	 * @param Config  $config  The Config Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * Get the body of allowView().
	 *
	 * The check runs before the model, so a refusal is a 403 and never the
	 * redirect the model would answer with. A site view asks for its
	 * site.<code>.access permission when configured. A custom admin view
	 * retains its <code>.access permission. On Joomla versions before the
	 * component API gate, it also requires core.manage.
	 *
	 * @param   array  $resource  The resource, as the resources map names it.
	 *
	 * @return  string  The method body.
	 * @since   6.1.7
	 */
	public function get(array $resource): string
	{
		$component = 'com_' . $this->config->component_code_name;
		$code = (string) $resource['code'];
		$site = ($resource['area'] === Resources::AREA_SITE);
		$access = !empty($resource['access']);

		if ($site && !$access)
		{
			return PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " The {$code} view asks for no permission of its own."
				. PHP_EOL . Indent::_(2) . "return true;";
		}

		$body = PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
			. " Get the calling user.";
		$body .= PHP_EOL . Indent::_(2) . "\$user = Factory::getApplication()->getIdentity();";

		if (!$site)
		{
			$body .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " Joomla 5.4.11 and 6.2.1 introduced the active route and the core.api dispatcher check.";
			$body .= PHP_EOL . Indent::_(2) . "if (!method_exists(\\Joomla\\CMS\\Application\\ApiApplication::class, 'getActiveRoute')";
			$body .= PHP_EOL . Indent::_(3) . "&& !\$user->authorise('core.manage', '{$component}'))";
			$body .= PHP_EOL . Indent::_(2) . "{";
			$body .= PHP_EOL . Indent::_(3) . "return false;";
			$body .= PHP_EOL . Indent::_(2) . "}";

			if (!$access)
			{
				$body .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
					. " The {$code} view asks for no permission of its own.";
				$body .= PHP_EOL . Indent::_(2) . "return true;";

				return $body;
			}
		}

		$permission = $site ? "site.{$code}.access" : "{$code}.access";

		$body .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
			. " The {$permission} permission the {$code} view link asks for.";
		$body .= PHP_EOL . Indent::_(2) . "return \$user->authorise('{$permission}', '{$component}');";

		return $body;
	}
}

