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

namespace VDM\Joomla\Componentbuilder\Compiler\Model;


use VDM\Joomla\Componentbuilder\Compiler\Factory as Compiler;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Utilities\StringHelper;


/**
 * Model Admin View Javascript Class
 * 
 * @since 3.2.0
 */
class Javascriptadminview
{
	/**
	 * The scripter add array
	 *
	 * @var    array
	 * @since 3.2.0
	 */
	protected array $scripter = [
		'javascript_view_file',
		'javascript_view_footer',
		'javascript_views_file',
		'javascript_views_footer'
	];

	/**
	 * The gui mapper array
	 *
	 * @var    array
	 * @since 3.2.0
	 */
	protected array $guiMapper = [
		'table' => 'admin_view',
		'id' => null,
		'field' => null,
		'type'  => 'js',
		'prefix' => PHP_EOL
	];

	/**
	 * Compiler Customcode Dispenser
	 *
	 * @var    Dispenser
	 * @since 3.2.0
	 */
	protected Dispenser $dispenser;

	/**
	 * Constructor
	 *
	 * @param Dispenser|null     $dispenser      The compiler customcode dispenser
	 *
	 * @since 3.2.0
	 */
	public function __construct(?Dispenser $dispenser = null)
	{
		$this->dispenser = $dispenser ?: Compiler::_('Customcode.Dispenser');
	}

	/**
	 * Set Admin View Javascript
	 *
	 * @param   object  $item  The item data
	 * @param   object  $table The table
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	public function set(object &$item, string $table = 'admin_view')
	{
		// set some gui mapper values
		$this->guiMapper['table'] = $table;
		$this->guiMapper['id'] = (int) $item->id;

		foreach ($this->scripter as $scripter)
		{
			if (isset($item->{'add_' . $scripter})
				&& $item->{'add_' . $scripter} == 1
				&& StringHelper::check($item->$scripter))
			{
				$scripter_target = str_replace(
					'javascript_', '', (string) $scripter
				);

				// update GUI mapper field
				$this->guiMapper['field'] = $scripter;
				$this->dispenser->set(
					$item->{$scripter},
					$scripter_target,
					$item->name_single_code,
					null,
					$this->guiMapper,
					true,
					true,
					true
				);

				// check if a token must be set
				if ((strpos((string) $item->$scripter, "token") !== false
					|| strpos(
						(string) $item->$scripter, "task=ajax"
					) !== false) && !$this->dispenser->hub['token'][$item->name_single_code])
				{
					$this->dispenser->hub['token'][$item->name_single_code]
						= true;
				}

				unset($item->{$scripter});
			}
		}
	}
}

