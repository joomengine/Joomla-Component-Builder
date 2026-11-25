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

namespace VDM\Joomla\Componentbuilder\Compiler\Field\JoomlaThree;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Permission;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Field\InputButtonInterface;


/**
 * Compiler Field Input Button
 * 
 * @since 3.2.0
 */
final class InputButton implements InputButtonInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 3.2.0
	 */
	protected Placeholder $placeholder;

	/**
	 * The Permission Class.
	 *
	 * @var   Permission
	 * @since 3.2.0
	 */
	protected Permission $permission;

	/**
	 * Constructor.
	 *
	 * @param Config        $config        The Config Class.
	 * @param Placeholder   $placeholder   The Placeholder Class.
	 * @param Permission    $permission    The Permission Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Config $config, Placeholder $placeholder,
		Permission $permission)
	{
		$this->config = $config;
		$this->placeholder = $placeholder;
		$this->permission = $permission;
	}

	/**
	 * get Add Button To List Field Input (getInput tweak)
	 *
	 * @param   array  $fieldData  The field custom data
	 *
	 * @return  string of getInput class on success empty string otherwise
	 * @since 3.2.0
	 */
	public function get(array $fieldData): string
	{
		// make sure hte view values are set
		if (isset($fieldData['add_button'])
			&& ($fieldData['add_button'] === 'true'
				|| 1 == $fieldData['add_button'])
			&& isset($fieldData['view'])
			&& isset($fieldData['views'])
			&& StringHelper::check($fieldData['view'])
			&& StringHelper::check($fieldData['views']))
		{
			// set local component
			$local_component = "com_" . $this->config->component_code_name;
			// check that the component value is set
			if (!isset($fieldData['component'])
				|| !StringHelper::check(
					$fieldData['component']
				))
			{
				$fieldData['component'] = $local_component;
			}
			// check that the component has the com_ value in it
			if (strpos((string) $fieldData['component'], 'com_') === false
				|| strpos((string) $fieldData['component'], '=') !== false)
			{
				$fieldData['component'] = "com_" . $fieldData['component'];
			}
			// make sure the component is update if # # # or [ [ [ component placeholder is used
			if (strpos((string) $fieldData['component'], (string) Placefix::h()) !== false
				|| strpos((string) $fieldData['component'], (string) Placefix::b()) !== false) // should not be needed... but
			{
				$fieldData['component'] = $this->placeholder->update_(
					$fieldData['component']
				);
			}
			// get core permissions
			$coreLoad = false;
			// add ref tags
			$refLoad = true;
			// fall back on the field component
			$component = $fieldData['component'];
			// check if we should add ref tags (since it only works well on local views)
			if ($local_component !== $component)
			{
				// do not add ref tags
				$refLoad = false;
			}
			// start building the add buttons/s
			$addButton   = array();
			$addButton[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
			$addButton[] = Indent::_(1) . " * Override to add new button";
			$addButton[] = Indent::_(1) . " *";
			$addButton[] = Indent::_(1)
				. " * @return  string  The field input markup.";
			$addButton[] = Indent::_(1) . " *";
			$addButton[] = Indent::_(1) . " * @since   3.2";
			$addButton[] = Indent::_(1) . " */";
			$addButton[] = Indent::_(1) . "protected function getInput()";
			$addButton[] = Indent::_(1) . "{";
			$addButton[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " see if we should add buttons";
			$addButton[] = Indent::_(2)
				. "\$set_button = \$this->getAttribute('button');";
			$addButton[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " get html";
			$addButton[] = Indent::_(2) . "\$html = parent::getInput();";
			$addButton[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " if true set button";
			$addButton[] = Indent::_(2) . "if (\$set_button === 'true')";
			$addButton[] = Indent::_(2) . "{";
			$addButton[] = Indent::_(3) . "\$button = [];";
			$addButton[] = Indent::_(3) . "\$script = [];";
			$addButton[] = Indent::_(3)
				. "\$button_code_name = \$this->getAttribute('name');";
			$addButton[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " get the input from url";
			$addButton[] = Indent::_(3) . "\$app = Joomla__" . "_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();";
			$addButton[] = Indent::_(3) . "\$jinput = method_exists(\$app, 'getInput') ? \$app->getInput() : \$app->input;";
			$addButton[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " get the view name & id";
			$addButton[] = Indent::_(3)
				. "\$values = \$jinput->getArray(array(";
			$addButton[] = Indent::_(4) . "'id' => 'int',";
			$addButton[] = Indent::_(4) . "'view' => 'word'";
			$addButton[] = Indent::_(3) . "));";
			$addButton[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " check if new item";
			$addButton[] = Indent::_(3) . "\$ref = '';";
			$addButton[] = Indent::_(3) . "\$refJ = '';";
			if ($refLoad)
			{
				$addButton[] = Indent::_(3)
					. "if (!is_null(\$values['id']) && strlen(\$values['view']))";
				$addButton[] = Indent::_(3) . "{";
				$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
					. " only load referral if not new item.";
				$addButton[] = Indent::_(4)
					. "\$ref = '&amp;ref=' . \$values['view'] . '&amp;refid=' . \$values['id'];";
				$addButton[] = Indent::_(4)
					. "\$refJ = '&ref=' . \$values['view'] . '&refid=' . \$values['id'];";
				$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
					. " get the return value.";
				$addButton[] = Indent::_(4)
					. "\$_uri = (string) Joomla__"."_eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::getInstance();";
				$addButton[] = Indent::_(4)
					. "\$_return = urlencode(base64_encode(\$_uri));";
				$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
					. " load return value.";
				$addButton[] = Indent::_(4)
					. "\$ref .= '&amp;return=' . \$_return;";
				$addButton[] = Indent::_(4)
					. "\$refJ .= '&return=' . \$_return;";
				$addButton[] = Indent::_(3) . "}";
			}
			else
			{
				$addButton[] = Indent::_(3)
					. "if (!is_null(\$values['id']) && strlen(\$values['view']))";
				$addButton[] = Indent::_(3) . "{";
				$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
					. " only load field details if not new item.";
				$addButton[] = Indent::_(4)
					. "\$ref = '&amp;field=' . \$values['view'] . '&amp;field_id=' . \$values['id'];";
				$addButton[] = Indent::_(4)
					. "\$refJ = '&field=' . \$values['view'] . '&field_id=' . \$values['id'];";
				$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
					. " get the return value.";
				$addButton[] = Indent::_(4)
					. "\$_uri = (string) Joomla__"."_eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::getInstance();";
				$addButton[] = Indent::_(4)
					. "\$_return = urlencode(base64_encode(\$_uri));";
				$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
					. " load return value.";
				$addButton[] = Indent::_(4)
					. "\$ref = '&amp;return=' . \$_return;";
				$addButton[] = Indent::_(4)
					. "\$refJ = '&return=' . \$_return;";
				$addButton[] = Indent::_(3) . "}";
			}
			$addButton[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " get button label";
			$addButton[] = Indent::_(3)
				. "\$button_label = trim(\$button_code_name);";
			$addButton[] = Indent::_(3)
				. "\$button_label = preg_replace('/_+/', ' ', \$button_label);";
			$addButton[] = Indent::_(3)
				. "\$button_label = preg_replace('/\s+/', ' ', \$button_label);";
			$addButton[] = Indent::_(3)
				. "\$button_label = preg_replace(\"/[^A-Za-z ]/\", '', \$button_label);";
			$addButton[] = Indent::_(3)
				. "\$button_label = ucfirst(strtolower(\$button_label));";
			$addButton[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " get user object";
			$addButton[] = Indent::_(3) . "\$user = Factory::getUser();";
			$addButton[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " only add if user allowed to create " . $fieldData['view'];
			// check if the item has permissions.
			$addButton[] = Indent::_(3) . "if (\$user->authorise('"
				. $this->permission->getGlobal($fieldData['view'], 'core.create')
				. "', '" . $component . "') && \$app->isClient('administrator')) // TODO for now only in admin area.";
			$addButton[] = Indent::_(3) . "{";
			$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " build Create button";
			$addButton[] = Indent::_(4)
				. "\$button[] = '<a id=\"'.\$button_code_name.'Create\" class=\"btn btn-small btn-success hasTooltip\" title=\"'.Text:"
				. ":sprintf('" . $this->config->lang_prefix
				. "_CREATE_NEW_S', \$button_label).'\" style=\"border-radius: 0px 4px 4px 0px; padding: 4px 4px 4px 7px;\"";
			$addButton[] = Indent::_(5) . "href=\"index.php?option="
				. $fieldData['component'] . "&amp;view=" . $fieldData['view']
				. "&amp;layout=edit'.\$ref.'\" >";
			$addButton[] = Indent::_(5)
				. "<span class=\"icon-new icon-white\"></span></a>';";
			$addButton[] = Indent::_(3) . "}";
			$addButton[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " only add if user allowed to edit " . $fieldData['view'];
			// check if the item has permissions.
			$addButton[] = Indent::_(3) . "if (\$user->authorise('"
				. $this->permission->getGlobal($fieldData['view'], 'core.edit')
				. "', '" . $component . "') && \$app->isClient('administrator')) // TODO for now only in admin area.";
			$addButton[] = Indent::_(3) . "{";
			$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " build edit button";
			$addButton[] = Indent::_(4)
				. "\$button[] = '<a id=\"'.\$button_code_name.'Edit\" class=\"btn btn-small hasTooltip\" title=\"'.Text:"
				. ":sprintf('" . $this->config->lang_prefix
				. "_EDIT_S', \$button_label).'\" style=\"display: none; padding: 3px 4px 4px 7px;\" href=\"#\" >";
			$addButton[] = Indent::_(5)
				. "<span class=\"icon-edit\"></span></a>';";
			$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " build script";
			$addButton[] = Indent::_(4) . "\$script[] = \"";
			$addButton[] = Indent::_(5) . "jQuery(document).ready(function() {";
			$addButton[] = Indent::_(6)
				. "jQuery('#adminForm').on('change', '#jform_\".\$button_code_name.\"',function (e) {";
			$addButton[] = Indent::_(7) . "e.preventDefault();";
			$addButton[] = Indent::_(7)
				. "var \".\$button_code_name.\"Value = jQuery('#jform_\".\$button_code_name.\"').val();";
			$addButton[] = Indent::_(7)
				. "\".\$button_code_name.\"Button(\".\$button_code_name.\"Value);";
			$addButton[] = Indent::_(6) . "});";
			$addButton[] = Indent::_(6)
				. "var \".\$button_code_name.\"Value = jQuery('#jform_\".\$button_code_name.\"').val();";
			$addButton[] = Indent::_(6)
				. "\".\$button_code_name.\"Button(\".\$button_code_name.\"Value);";
			$addButton[] = Indent::_(5) . "});";
			$addButton[] = Indent::_(5)
				. "function \".\$button_code_name.\"Button(value) {";
			$addButton[] = Indent::_(6)
				. "if (value > 0) {"; // TODO not ideal since value may not be an (int)
			$addButton[] = Indent::_(7) . "//" . Line::_(__LINE__, __CLASS__) . " hide the create button";
			$addButton[] = Indent::_(7)
				. "jQuery('#\".\$button_code_name.\"Create').hide();";
			$addButton[] = Indent::_(7) . "//" . Line::_(__LINE__, __CLASS__) . " show edit button";
			$addButton[] = Indent::_(7)
				. "jQuery('#\".\$button_code_name.\"Edit').show();";
			$addButton[] = Indent::_(7) . "var url = 'index.php?option="
				. $fieldData['component'] . "&view=" . $fieldData['views']
				. "&task=" . $fieldData['view']
				. ".edit&id='+value+'\".\$refJ.\"';"; // TODO this value may not be the ID
			$addButton[] = Indent::_(7)
				. "jQuery('#\".\$button_code_name.\"Edit').attr('href', url);";
			$addButton[] = Indent::_(6) . "} else {";
			$addButton[] = Indent::_(7) . "//" . Line::_(__LINE__, __CLASS__) . " show the create button";
			$addButton[] = Indent::_(7)
				. "jQuery('#\".\$button_code_name.\"Create').show();";
			$addButton[] = Indent::_(7) . "//" . Line::_(__LINE__, __CLASS__) . " hide edit button";
			$addButton[] = Indent::_(7)
				. "jQuery('#\".\$button_code_name.\"Edit').hide();";
			$addButton[] = Indent::_(6) . "}";
			$addButton[] = Indent::_(5) . "}\";";
			$addButton[] = Indent::_(3) . "}";
			$addButton[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " check if button was created for " . $fieldData['view']
				. " field.";
			$addButton[] = Indent::_(3)
				. "if (is_array(\$button) && count(\$button) > 0)";
			$addButton[] = Indent::_(3) . "{";
			$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " Load the needed script.";
			$addButton[] = Indent::_(4)
				. "\$document = Factory::getDocument();";
			$addButton[] = Indent::_(4)
				. "\$document->addScriptDeclaration(implode(' ',\$script));";
			$addButton[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " return the button attached to input field.";
			$addButton[] = Indent::_(4)
				. "return '<div class=\"input-append\">' .\$html . implode('',\$button).'</div>';";
			$addButton[] = Indent::_(3) . "}";
			$addButton[] = Indent::_(2) . "}";
			$addButton[] = Indent::_(2) . "return \$html;";
			$addButton[] = Indent::_(1) . "}";

			return implode(PHP_EOL, $addButton);
		}

		return '';
	}
}

