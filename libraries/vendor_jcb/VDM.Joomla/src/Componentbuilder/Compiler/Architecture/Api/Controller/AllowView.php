<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    1st September, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Controller;


use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Builder\AccessSwitch;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Permission;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * Api Controller Allow View Class.
 * 
 * Builds the allowView method of the item API controller from the same
 * access permission the admin list uses to remove items a user may not see.
 * 
 * @since 6.1.7
 */
final class AllowView
{
	/**
	 * The Component code name.
	 *
	 * @var   string
	 * @since 6.1.7
	 */
	protected string $component;

	/**
	 * The Permission Class.
	 *
	 * @var   Permission
	 * @since 6.1.7
	 */
	protected Permission $permission;

	/**
	 * The native access-level field configuration.
	 *
	 * @var   AccessSwitch
	 * @since 6.1.7
	 */
	protected AccessSwitch $accessswitch;

	/**
	 * Constructor.
	 *
	 * @param Config       $config       The Config Class.
	 * @param Permission   $permission   The Permission Class.
	 * @param AccessSwitch $accessswitch The Access Switch Builder Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config, Permission $permission,
		AccessSwitch $accessswitch)
	{
		$this->component = $config->component_code_name;
		$this->permission = $permission;
		$this->accessswitch = $accessswitch;
	}

	/**
	 * Get the allow view code of the item API controller.
	 *
	 * @param   string  $nameSingleCode  The single code name of the view.
	 *
	 * @return  string  The allow view method body.
	 * @since   6.1.7
	 */
	public function get(string $nameSingleCode): string
	{
		$allow = [];

		if ($this->permission->actionExist($nameSingleCode, 'core.access'))
		{
			$allow[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " Get user object.";
			$allow[] = Indent::_(2) . "\$user = \$this->app->getIdentity();";
			$allow[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " Access check.";
			$allow[] = Indent::_(2) . "return "
				. $this->accessCondition($nameSingleCode, '$id') . ";";
		}
		else
		{
			$allow[] = PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " In the absence of an access permission, every authenticated user may view.";
			$allow[] = Indent::_(2) . "return true;";
		}

		return implode(PHP_EOL, $allow);
	}

	/**
	 * Build the API read guard of the shared administrator item model.
	 *
	 * Keep the controller's mapped access action and the list model's native
	 * view-level policy. Views without a configured access action remain open
	 * to authenticated callers; no edit permission is implied by a read.
	 *
	 * @param   string  $nameSingleCode  The single code name of the view.
	 *
	 * @return  string  The guard within the API branch of getItem().
	 * @since   6.1.7
	 */
	public function getModelGuard(string $nameSingleCode): string
	{
		$denied = [];

		if ($this->permission->actionExist($nameSingleCode, 'core.access'))
		{
			$denied[] = '!' . $this->accessCondition($nameSingleCode, '$item->id');
		}

		if ($this->accessswitch->exists($nameSingleCode))
		{
			$denied[] = "(!\$user->authorise('core.options', 'com_" . $this->component
				. "') && !in_array((int) \$item->access, \$user->getAuthorisedViewLevels()))";
		}

		if ($denied === [])
		{
			return '';
		}

		return PHP_EOL . Indent::_(5) . '$user = $app->getIdentity();'
			. PHP_EOL . Indent::_(5) . 'if (' . implode(' || ', $denied) . ')'
			. PHP_EOL . Indent::_(5) . '{'
			. PHP_EOL . Indent::_(6) . "throw new Joomla__" . "_2fa1c08d_c4ff_4cb0_8338_7221e95c1fe4___Power(Text::_('JERROR_ALERTNOAUTHOR'), 403);"
			. PHP_EOL . Indent::_(5) . '}';
	}

	/**
	 * Materialize the complete shared item authorization block.
	 *
	 * Preserve the edit guard for views without generated API
	 * resources. API-enabled views distinguish read permission from editing.
	 *
	 * @param   string  $nameSingleCode  The single code name of the view.
	 * @param   bool    $apiEnabled      Whether the view requests API resources.
	 *
	 * @return  string  The complete getItem authorization block.
	 * @since   6.1.7
	 */
	public function getItemGuard(string $nameSingleCode, bool $apiEnabled): string
	{
		if (!$apiEnabled)
		{
			return PHP_EOL . Indent::_(3) . '//' . Line::_(__LINE__, __CLASS__) . ' check edit access permissions'
				. PHP_EOL . Indent::_(3) . 'if (!empty($item->id) && !$this->allowEdit((array) $item))'
				. PHP_EOL . Indent::_(3) . '{'
				. PHP_EOL . Indent::_(4) . '$app = Factory::getApplication();'
				. PHP_EOL . Indent::_(4) . "\$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');"
				. PHP_EOL . Indent::_(4) . "\$app->redirect('index.php?option=com_" . $this->component . "');"
				. PHP_EOL . Indent::_(4) . 'return false;'
				. PHP_EOL . Indent::_(3) . '}';
		}

		return PHP_EOL . Indent::_(3) . '//' . Line::_(__LINE__, __CLASS__) . ' API reads use read permissions; administrator editing keeps its edit guard.'
			. PHP_EOL . Indent::_(3) . 'if (!empty($item->id))'
			. PHP_EOL . Indent::_(3) . '{'
			. PHP_EOL . Indent::_(4) . '$app = Factory::getApplication();'
			. PHP_EOL . PHP_EOL . Indent::_(4) . "if (\$app->isClient('api'))"
			. PHP_EOL . Indent::_(4) . '{' . $this->getModelGuard($nameSingleCode)
			. PHP_EOL . Indent::_(4) . '}'
			. PHP_EOL . Indent::_(4) . 'elseif (!$this->allowEdit((array) $item))'
			. PHP_EOL . Indent::_(4) . '{'
			. PHP_EOL . Indent::_(5) . "\$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');"
			. PHP_EOL . Indent::_(5) . "\$app->redirect('index.php?option=com_" . $this->component . "');"
			. PHP_EOL . Indent::_(5) . 'return false;'
			. PHP_EOL . Indent::_(4) . '}'
			. PHP_EOL . Indent::_(3) . '}';
	}

	/**
	 * Build one mapped entity/component access condition for both read guards.
	 *
	 * @param   string  $nameSingleCode  The single code name of the view.
	 * @param   string  $id              The generated record-id expression.
	 *
	 * @return  string  The mapped access expression.
	 * @since   6.1.7
	 */
	private function accessCondition(string $nameSingleCode, string $id): string
	{
		$action = $this->permission->getAction($nameSingleCode, 'core.access');

		return "(\$user->authorise('" . $action . "', 'com_" . $this->component
			. "." . $nameSingleCode . ".' . " . $id . ") && \$user->authorise('"
			. $action . "', 'com_" . $this->component . "'))";
	}
}

