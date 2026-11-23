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
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Joomlaplugins Form Field class for the Componentbuilder component
 *
 * @since  1.6
 */
class JoomlapluginsField extends ListField
{
	/**
	 * The joomlaplugins field type.
	 *
	 * @var        string
	 */
	public $type = 'Joomlaplugins';

	/**
	 * Override to add new button
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		// see if we should add buttons
		$set_button = $this->getAttribute('button');
		// get html
		$html = parent::getInput();
		// if true set button
		if ($set_button === 'true')
		{
			$button = array();
			$script = array();
			$button_code_name = $this->getAttribute('name');
			// get the input from url
			$app = Factory::getApplication();
			$jinput = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
			// get the view name & id
			$values = $jinput->getArray(array(
				'id' => 'int',
				'view' => 'word'
			));
			// check if new item
			$ref = '';
			$refJ = '';
			if (!is_null($values['id']) && strlen($values['view']))
			{
				// only load referral if not new item.
				$ref = '&amp;ref=' . $values['view'] . '&amp;refid=' . $values['id'];
				$refJ = '&ref=' . $values['view'] . '&refid=' . $values['id'];
				// get the return value.
				$_uri = (string) Uri::getInstance();
				$_return = urlencode(base64_encode($_uri));
				// load return value.
				$ref .= '&amp;return=' . $_return;
				$refJ .= '&return=' . $_return;
			}
			// get button label
			$button_label = trim($button_code_name);
			$button_label = preg_replace('/_+/', ' ', $button_label);
			$button_label = preg_replace('/\s+/', ' ', $button_label);
			$button_label = preg_replace("/[^A-Za-z ]/", '', $button_label);
			$button_label = ucfirst(strtolower($button_label));
			// get user object
			$user = Factory::getApplication()->getIdentity();
			// only add if user allowed to create joomla_plugin
			if ($user->authorise('joomla_plugin.create', 'com_componentbuilder') && $app->isClient('administrator')) // TODO for now only in admin area.
			{
				// build Create button
				$button[] = '<a id="'.$button_code_name.'Create" class="btn btn-small btn-success hasTooltip" title="'.Text::sprintf('PLG_EXTENSION_COMPONENTBUILDERPOWERSAUTOLOADERCOMPILER_CREATE_NEW_S', $button_label).'" style="border-radius: 0px 4px 4px 0px;"
					href="index.php?option=com_componentbuilder&amp;view=joomla_plugin&amp;layout=edit'.$ref.'" >
					<span class="icon-new icon-white"></span></a>';
			}
			// only add if user allowed to edit joomla_plugin
			if ($user->authorise('joomla_plugin.edit', 'com_componentbuilder') && $app->isClient('administrator')) // TODO for now only in admin area.
			{
				// build edit button
				$button[] = '<a id="'.$button_code_name.'Edit" class="btn btn-small btn-outline-success button-select hasTooltip" title="'.Text::sprintf('PLG_EXTENSION_COMPONENTBUILDERPOWERSAUTOLOADERCOMPILER_EDIT_S', $button_label).'" style="display: none; border-radius: 0px 4px 4px 0px;" href="#" >
					<span class="icon-edit"></span></a>';
				// build script
				$script[] = "
					document.addEventListener('DOMContentLoaded', function() {
						let  ".$button_code_name."Field = document.getElementById('jform_".$button_code_name."');
						if (!".$button_code_name."Field) { return; }
						".$button_code_name."Field.addEventListener('change', function(e) {
							e.preventDefault();
							let ".$button_code_name."Value = this.value;
							".$button_code_name."Button(".$button_code_name."Value);
						});
						let ".$button_code_name."Value = ".$button_code_name."Field.value;
						".$button_code_name."Button(".$button_code_name."Value);
					});
					function ".$button_code_name."Button(value) {
						var createButton = document.getElementById('".$button_code_name."Create');
						var editButton = document.getElementById('".$button_code_name."Edit');
						if (value > 0) {
							// hide the create button
							createButton.style.display = 'none';
							// show edit button
							editButton.style.display = 'block';
							let url = 'index.php?option=com_componentbuilder&view=joomla_plugins&task=joomla_plugin.edit&id='+value+'".$refJ."';
							editButton.setAttribute('href', url);
						} else {
							// show the create button
							createButton.style.display = 'block';
							// hide edit button
							editButton.style.display = 'none';
						}
					}";
			}
			// check if button was created for joomla_plugin field.
			if (is_array($button) && count($button) > 0)
			{
				// Load the needed script.
				$document = Factory::getApplication()->getDocument();
				$document->addScriptDeclaration(implode(' ',$script));
				// return the button attached to input field.
				return '<div class="input-group">' .$html . implode('',$button).'</div>';
			}
		}
		return $html;
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// Get the user object.
		$user = Factory::getApplication()->getIdentity();
		// Get the databse object.
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.guid','a.system_name','a.name','b.name','c.name'),array('guid','plugin_system_name','name','class_extends_name','joomla_plugin_group_name')));
		$query->from($db->quoteName('#__componentbuilder_joomla_plugin', 'a'));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_class_extends', 'b') . ' ON (' . $db->quoteName('a.class_extends') . ' = ' . $db->quoteName('b.id') . ')');
		$query->join('LEFT', $db->quoteName('#__componentbuilder_joomla_plugin_group', 'c') . ' ON (' . $db->quoteName('a.joomla_plugin_group') . ' = ' . $db->quoteName('c.id') . ')');
		$query->where($db->quoteName('a.published') . ' >= 1');
		$query->order('a.system_name ASC');
		// Implement View Level Access (if set in table)
		if (!$user->authorise('core.options', 'com_componentbuilder'))
		{
			$columns = $db->getTableColumns('#__componentbuilder_joomla_plugin');
			if(isset($columns['access']))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');
			}
		}
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = [];
		if ($items)
		{
			$options[] = Html::_('select.option', '', 'Select a plugin');
			foreach($items as $item)
			{
				// set a full class name
				$group_name = $item->joomla_plugin_group_name ?? '';
				$name = $item->name ?? '';
				$options[] = Html::_('select.option', $item->guid, '( ' . $item->plugin_system_name . ' ) class Plg' . $group_name . $name . ' extends ' . $item->class_extends_name);
			}
		}
		return $options;
	}
}
