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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Component\ComponentHelper;
use ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Site\Helper\###Component###Helper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Type### Form Field class for the ###Component### component
 *
 * @since  1.6
 */
class ###Type###Field extends ListField
{
	/**
	 * The ###type### field type.
	 *
	 * @var        string
	 */
	public $type = '###Type###';###ADD_BUTTON###

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		###JFORM_GETOPTIONS_PHP###
	}
}
