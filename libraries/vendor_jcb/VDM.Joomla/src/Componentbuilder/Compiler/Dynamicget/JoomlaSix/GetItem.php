<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    21st October, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Dynamicget\JoomlaSix;


use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\GetItem as SharedGetItem;


/**
 * Emit fresh database queries through the native Joomla 6 query factory.
 * 
 * @since  6.2.0
 */
final class GetItem extends SharedGetItem
{
	/**
	 * Fresh-query method available in Joomla 6 without deprecated arguments.
	 *
	 * @var   string
	 * @since 6.2.0
	 */
	protected string $queryMethod = 'createQuery()';
}

