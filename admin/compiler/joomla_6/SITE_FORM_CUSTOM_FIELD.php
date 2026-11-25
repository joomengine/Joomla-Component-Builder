<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Site\Field;

###SITE_FORM_CUSTOM_FIELD_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Type### Form Field class for the ###Component### component
 *
 * @since  1.6
 */
class ###Type###Field extends ###FORM_EXTENDS###
{
	/**
	 * The ###type### field type.
	 *
	 * @var        string
	 */
	public $type = '###Type###';###FORM_CUSTOM_FIELD_PHP###
}
