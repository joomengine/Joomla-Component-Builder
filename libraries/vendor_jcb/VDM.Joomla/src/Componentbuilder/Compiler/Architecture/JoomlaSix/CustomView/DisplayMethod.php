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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\CustomView;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\DisplayMethod as SharedDisplayMethod;


/**
 * Let native Joomla 6 view exception mode report model retrieval failures.
 * 
 * @since  6.2.0
 */
final class DisplayMethod extends SharedDisplayMethod
{
	/**
	 * Joomla 6 templates enable model exceptions before the first read.
	 *
	 * @var   bool
	 * @since 6.2.0
	 */
	protected bool $checkErrors = false;
}

