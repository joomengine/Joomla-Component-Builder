<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace VDM\Component\Componentbuilder\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Component\ComponentHelper;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use Joomla\Database\DatabaseInterface;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Customadminviewsfilteraddcustombutton Form Field class for the Componentbuilder component
 *
 * @since  1.6
 */
class CustomadminviewsfilteraddcustombuttonField extends ListField
{
	/**
	 * The customadminviewsfilteraddcustombutton field type.
	 *
	 * @var        string
	 */
	public $type = 'Customadminviewsfilteraddcustombutton';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// Get a db connection.
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select the text.
		$query->select($db->quoteName('add_custom_button'));
		$query->from($db->quoteName('#__componentbuilder_custom_admin_view'));
		$query->order($db->quoteName('add_custom_button') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$_results = $db->loadColumn();
		$_filter = [];
		$_filter[] = Html::_('select.option', '', '- ' . Text::_('COM_COMPONENTBUILDER_FILTER_SELECT_ADD_CUSTOM_BUTTONS') . ' -');

		if ($_results)
		{
			// get custom_admin_viewsmodel
			$_model = ComponentbuilderHelper::getModel('custom_admin_views');
			$_results = array_unique($_results);
			foreach ($_results as $add_custom_button)
			{
				// Translate the add_custom_button selection
				$_text = $_model->selectionTranslation($add_custom_button,'add_custom_button');
				// Now add the add_custom_button and its text to the options array
				$_filter[] = Html::_('select.option', $add_custom_button, Text::_($_text));
			}
		}
		return $_filter;
	}
}
