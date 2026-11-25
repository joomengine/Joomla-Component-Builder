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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\UserField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Component\ComponentHelper;
use ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Helper\###Component###Helper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Type### Form Field class for the ###Component### component
 *
 * @since  1.6
 */
class ###Type###Field extends UserField
{
	/**
	 * The ###type### field type.
	 *
	 * @var        string
	 */
	public $type = '###Type###';

	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  array of filtering groups or null.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		###JFORM_GETGROUPS_PHP###
	}

	/**
	 * Method to get the users to exclude from the list of users
	 *
	 * @return  mixed  Array of users to exclude or null to not exclude them
	 *
	 * @since   1.6
	 */
	protected function getExcluded()
	{
		###JFORM_GETEXCLUDED_PHP###
	}
}
