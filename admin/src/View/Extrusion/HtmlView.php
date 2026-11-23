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
namespace VDM\Component\Componentbuilder\Administrator\View\Extrusion;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\Document\Document;
use VDM\Component\Componentbuilder\Administrator\Helper\HeaderCheck;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use Joomla\CMS\Form\Form;
use Joomla\Filesystem\File;
use Joomla\CMS\Layout\LayoutHelper;
use VDM\Joomla\Componentbuilder\Utilities\Permitted\Actions;
use VDM\Joomla\Utilities\FormHelper;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\CMS\Toolbar\Toolbar;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Html View class for the Extrusion
 *
 * @since  1.6
 */
#[\AllowDynamicProperties]
class HtmlView extends BaseHtmlView
{
	/**
	 * The app class
	 *
	 * @var    CMSApplicationInterface
	 * @since  5.2.1
	 */
	public CMSApplicationInterface $app;

	/**
	 * The input class
	 *
	 * @var    Input
	 * @since  5.2.1
	 */
	public Input $input;

	/**
	 * The params registry
	 *
	 * @var    Registry
	 * @since  5.2.1
	 */
	public Registry $params;

	/**
	 * The user object.
	 *
	 * @var    User
	 * @since  3.10.11
	 */
	public User $user;

	/**
	 * The styles url array
	 *
	 * @var    array
	 * @since  5.0.0
	 */
	protected array $styles;

	/**
	 * The scripts url array
	 *
	 * @var    array
	 * @since  5.0.0
	 */
	protected array $scripts;

	/**
	 * The actions object
	 *
	 * @var    object
	 * @since  3.10.11
	 */
	public object $canDo;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 * @throws \Exception
	 * @since  1.6
	 */
	public function display($tpl = null): void
	{
		// get the application
		$this->app ??= Factory::getApplication();
		// get input
		$this->input ??= method_exists($this->app, 'getInput') ? $this->app->getInput() : $this->app->input;
		// get component params
		$this->params ??= method_exists($this->app, 'getParams')
			? $this->app->getParams()
			: ComponentHelper::getParams('com_componentbuilder');
		// get the user object
		$this->user ??= $this->getCurrentUser();

		// get the permitted actions the current user can do.
		$this->canDo = Actions::get('extrusion');

		// Load module values
		$model = $this->getModel();
		$this->styles = $model->getStyles() ?? [];
		$this->scripts = $model->getScripts() ?? [];
		// Initialise variables.
		$this->items = $model->getItems();
		// get active components
		$this->Components = $model->getComponents();
		
		// set the "dankie" state
		$this->dankie = $this->rotativeRandom();
		
		// get the needed form fields
		$this->form = $this->getDynamicForm();
		
		// just get it on the page for now....
		ToolbarHelper::inlinehelp();

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			// add the tool bar
			$this->addToolBar();
		}

		// Check for errors.
		if (count($errors = $model->getErrors()))
		{
			throw new \Exception(implode(PHP_EOL, $errors), 500);
		}

		// Set the html view document stuff
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Get the dynamic build form fields needed on the page
	 *
	 * Three fieldsets carry the whole configuration surface of the two
	 * extrusion engines: the source (where to read), the switches (what to
	 * take), and the advanced options (how carefully to take it).
	 *
	 * @return  Form|null  The form fields
	 *
	 * @since   6.1.7
	 */
	public function getDynamicForm(): ?Form
	{
		// start the form
		$form = new Form('Extrusion');

		$form->load('<form
			addruleprefix="VDM\Component\Componentbuilder\Administrator\Rule"
			addfieldprefix="VDM\Component\Componentbuilder\Administrator\Field">
				<config><inlinehelp button="show"/></config>
				<fieldset name="source"></fieldset>
				<fieldset name="switches"></fieldset>
				<fieldset name="advanced"></fieldset></form>');

		// the yes/no options every switch shares
		$yesno = [
			'1' => Text::_('COM_COMPONENTBUILDER_YES'),
			'0' => Text::_('COM_COMPONENTBUILDER_NO')];

		// admin folder attributes
		$this->field($form, 'source', [
			'type' => 'text',
			'name' => 'admin_path',
			'label' => Text::_('COM_COMPONENTBUILDER_ADMIN_FOLDER'),
			'size' => '60',
			'hint' => 'administrator/components/com_component',
			'description' => Text::_('COM_COMPONENTBUILDER_THE_ADMINISTRATOR_FOLDER_OF_THE_COMPONENT_SELECTED_FROM_THE_SITE_ROOT_EVERYTHING_INSIDE_IS_DISCOVERED_ON_ITS_OWN_INCLUDING_THE_INSTALL_SQL_THE_FOLDER_CARRIES')]);

		// site folder attributes
		$this->field($form, 'source', [
			'type' => 'text',
			'name' => 'site_path',
			'label' => Text::_('COM_COMPONENTBUILDER_SITE_FOLDER'),
			'size' => '60',
			'hint' => 'components/com_component',
			'description' => Text::_('COM_COMPONENTBUILDER_THE_SITE_FOLDER_OF_THE_COMPONENT_SELECTED_FROM_THE_SITE_ROOT_FOR_THE_SITE_VIEWS_TEMPLATES_AND_LAYOUTS_IT_HOLDS')]);

		// library folders attributes
		$this->field($form, 'source', [
			'type' => 'textarea',
			'name' => 'libraries',
			'label' => Text::_('COM_COMPONENTBUILDER_LIBRARY_FOLDERS_TO_HARVEST_AS_POWERS'),
			'rows' => '3',
			'cols' => '80',
			'description' => Text::_('COM_COMPONENTBUILDER_ONE_FOLDER_PER_LINE_SELECTED_FROM_THE_SITE_ROOT_EVERY_PHP_CLASS_INTERFACE_AND_TRAIT_FOUND_IN_THESE_FOLDERS_IS_HARVESTED_AS_A_POWER_LEAVE_THIS_EMPTY_TO_ONLY_PULL_IN_THE_COMPONENT')]);

		// component attributes
		$attributes = [
			'type' => 'list',
			'name' => 'component_id',
			'label' => Text::_('COM_COMPONENTBUILDER_TARGET_COMPONENT'),
			'class' => 'list_class',
			'description' => Text::_('COM_COMPONENTBUILDER_THE_JCB_COMPONENT_THE_HARVEST_IS_PAIRED_AGAINST_LEAVE_IT_ON_DETECTION_AND_THE_TOOL_WILL_RECOGNISE_A_COMPONENT_JCB_ALREADY_KNOWS_BY_ITS_CODE_NAME')];
		// start the component options
		$options = [];
		$options[''] = Text::_('COM_COMPONENTBUILDER_DETECT_FROM_THE_SOURCE');
		$options['0'] = Text::_('COM_COMPONENTBUILDER_NONE_EVERYTHING_IS_CREATED_NEW');
		// load component options from array
		if (!empty($this->Components))
		{
			foreach($this->Components as $component)
			{
				$options[(int) $component->id] = $this->escape($component->system_name ?? $component->name);
			}
		}
		$this->field($form, 'source', $attributes, $options);

		// component code name attributes
		$this->field($form, 'source', [
			'type' => 'text',
			'name' => 'component_code',
			'label' => Text::_('COM_COMPONENTBUILDER_COMPONENT_CODE_NAME'),
			'size' => '40',
			'hint' => 'com_component',
			'description' => Text::_('COM_COMPONENTBUILDER_THE_COMPONENT_THE_HARVESTED_CLASSES_BELONG_TO_WHEN_EVERYTHING_IS_CREATED_NEW_AND_NO_TARGET_COMPONENT_IS_SELECTED_THIS_NAME_IS_WHAT_THE_COMPONENT_NAMESPACE_PLACEHOLDER_STANDS_ON_SO_EVERY_CLASS_KEEPS_THE_PLACEHOLDER_INSTEAD_OF_A_HARDCODED_SEGMENT')]);

		// mode attributes
		$this->field($form, 'switches', [
			'type' => 'radio',
			'name' => 'mode',
			'label' => Text::_('COM_COMPONENTBUILDER_MODE'),
			'class' => 'btn-group btn-group-yesno',
			'default' => 'create',
			'description' => Text::_('COM_COMPONENTBUILDER_IN_CREATE_MODE_THE_HARVEST_PROPOSES_NEW_DEFINITIONS_WHEREVER_NOTHING_MATCHES_IN_UPDATE_MODE_ONLY_WHAT_ALREADY_EXISTS_IN_JCB_IS_TOUCHED')],
			['create' => Text::_('COM_COMPONENTBUILDER_CREATE'), 'update' => Text::_('COM_COMPONENTBUILDER_UPDATE')]);

		// on existing attributes
		$this->field($form, 'switches', [
			'type' => 'radio',
			'name' => 'on_existing',
			'label' => Text::_('COM_COMPONENTBUILDER_WHEN_A_DEFINITION_ALREADY_EXISTS'),
			'class' => 'btn-group btn-group-yesno',
			'default' => 'update',
			'description' => Text::_('COM_COMPONENTBUILDER_SKIP_LEAVES_THE_EXISTING_DEFINITION_UNTOUCHED_AND_ONLY_MENTIONS_IT_UPDATE_REFRESHES_IT_WITH_WHAT_WAS_HARVESTED_REPLACE_OVERWRITES_IT_COMPLETELY')],
			['skip' => Text::_('COM_COMPONENTBUILDER_SKIP'), 'update' => Text::_('COM_COMPONENTBUILDER_UPDATE'), 'replace' => Text::_('COM_COMPONENTBUILDER_REPLACE')]);

		// the scope switches, each one engine scope
		$scopes = [
			'scope_admin' => [Text::_('COM_COMPONENTBUILDER_ADMIN_VIEWS'), '1', Text::_('COM_COMPONENTBUILDER_HARVEST_THE_ADMIN_VIEWS_OF_THE_COMPONENT')],
			'scope_site' => [Text::_('COM_COMPONENTBUILDER_SITE_CODE'), '0', Text::_('COM_COMPONENTBUILDER_HARVEST_THE_SITE_AREA_OF_THE_COMPONENT')],
			'scope_site_views' => [Text::_('COM_COMPONENTBUILDER_SITE_VIEWS'), '1', Text::_('COM_COMPONENTBUILDER_HARVEST_THE_SITE_VIEWS_TEMPLATES_AND_LAYOUTS')],
			'scope_tabs' => [Text::_('COM_COMPONENTBUILDER_TABS'), '1', Text::_('COM_COMPONENTBUILDER_CARRY_THE_FIELD_GROUPINGS_OVER_AS_ADMIN_VIEW_TABS')],
			'scope_conditions' => [Text::_('COM_COMPONENTBUILDER_CONDITIONS'), '1', Text::_('COM_COMPONENTBUILDER_CARRY_THE_FIELD_SHOWON_RULES_OVER_AS_CONDITIONS')],
			'scope_language' => [Text::_('COM_COMPONENTBUILDER_LANGUAGE_STRINGS'), '1', Text::_('COM_COMPONENTBUILDER_RESOLVE_LABELS_AND_DESCRIPTIONS_THROUGH_THE_LANGUAGE_FILES_OF_THE_SOURCE')],
			'scope_translations' => [Text::_('COM_COMPONENTBUILDER_TRANSLATIONS'), '0', Text::_('COM_COMPONENTBUILDER_ALSO_IMPORT_THE_TRANSLATED_LANGUAGE_STRINGS_OF_THE_SOURCE')],
			'scope_relations' => [Text::_('COM_COMPONENTBUILDER_RELATIONS'), '1', Text::_('COM_COMPONENTBUILDER_LINK_THE_HARVESTED_VIEWS_FIELDS_AND_POWERS_TO_THE_TARGET_COMPONENT')],
			'scope_component_details' => [Text::_('COM_COMPONENTBUILDER_COMPONENT_DETAILS'), '1', Text::_('COM_COMPONENTBUILDER_ALSO_HARVEST_THE_COMPONENT_MANIFEST_DETAILS_NAME_AUTHOR_VERSION_AND_DESCRIPTION')]];
		foreach ($scopes as $name => [$label, $default, $description])
		{
			$this->field($form, 'switches', [
				'type' => 'radio',
				'name' => $name,
				'label' => $label,
				'class' => 'btn-group btn-group-yesno',
				'default' => $default,
				'description' => $description], $yesno);
		}

		// Advanced Options
		$this->field($form, 'switches', [
			'type' => 'radio',
			'name' => 'show_advanced_options',
			'label' => Text::_('COM_COMPONENTBUILDER_SHOW_ADVANCED_OPTIONS'),
			'class' => 'btn-group btn-group-yesno',
			'default' => '0',
			'description' => Text::_('COM_COMPONENTBUILDER_WOULD_YOU_LIKE_TO_SEE_THE_ADVANCED_EXTRUSION_OPTIONS')], $yesno);

		// Advanced Options note attributes
		$this->field($form, 'advanced', [
			'type' => 'note',
			'name' => 'show_advanced_options_note',
			'label' => Text::_('COM_COMPONENTBUILDER_ADVANCED_OPTIONS'),
			'heading' => 'h3',
			'showon' => 'show_advanced_options:1']);

		// layout attributes
		$this->field($form, 'advanced', [
			'type' => 'list',
			'name' => 'layout',
			'label' => Text::_('COM_COMPONENTBUILDER_SOURCE_LAYOUT_CONVENTION'),
			'class' => 'list_class',
			'default' => 'auto',
			'showon' => 'show_advanced_options:1',
			'description' => Text::_('COM_COMPONENTBUILDER_WHICH_JOOMLA_FOLDER_CONVENTION_THE_SOURCE_FOLLOWS_LEAVE_IT_ON_DETECTION_UNLESS_THE_TOOL_READS_THE_WRONG_FOLDERS')],
			['auto' => Text::_('COM_COMPONENTBUILDER_DETECT'),
				'j3' => Text::_('COM_COMPONENTBUILDER_JOOMLA_THREE'),
				'j4' => Text::_('COM_COMPONENTBUILDER_JOOMLA_FOUR'),
				'j5' => Text::_('COM_COMPONENTBUILDER_JOOMLA_FIVE'),
				'j6' => Text::_('COM_COMPONENTBUILDER_JOOMLA_SIX')]);

		// language tag attributes
		$this->field($form, 'advanced', [
			'type' => 'text',
			'name' => 'language_tag',
			'label' => Text::_('COM_COMPONENTBUILDER_LANGUAGE_TAG'),
			'default' => 'en-GB',
			'size' => '10',
			'showon' => 'show_advanced_options:1',
			'description' => Text::_('COM_COMPONENTBUILDER_THE_LANGUAGE_OF_THE_SOURCE_THE_LABELS_AND_DESCRIPTIONS_ARE_RESOLVED_FROM')]);

		// table class attributes
		$this->field($form, 'advanced', [
			'type' => 'radio',
			'name' => 'table_class',
			'label' => Text::_('COM_COMPONENTBUILDER_TABLE_CLASS_ANALYSIS'),
			'class' => 'btn-group btn-group-yesno',
			'default' => 'auto',
			'showon' => 'show_advanced_options:1',
			'description' => Text::_('COM_COMPONENTBUILDER_WHETHER_THE_TABLE_CLASSES_OF_THE_SOURCE_ARE_READ_TO_STRENGTHEN_THE_FIELD_RESOLUTION')],
			['auto' => Text::_('COM_COMPONENTBUILDER_DETECT'), 'off' => Text::_('COM_COMPONENTBUILDER_OFF')]);

		// dry run attributes
		$this->field($form, 'advanced', [
			'type' => 'radio',
			'name' => 'dry_run',
			'label' => Text::_('COM_COMPONENTBUILDER_DRY_RUN'),
			'class' => 'btn-group btn-group-yesno',
			'default' => '0',
			'showon' => 'show_advanced_options:1',
			'description' => Text::_('COM_COMPONENTBUILDER_A_DRY_RUN_WALKS_THE_WHOLE_IMPORT_AND_REPORTS_EVERY_STEP_BUT_WRITES_NOTHING_TO_THE_DATABASE')], $yesno);

		// strict attributes
		$this->field($form, 'advanced', [
			'type' => 'radio',
			'name' => 'strict',
			'label' => Text::_('COM_COMPONENTBUILDER_STRICT'),
			'class' => 'btn-group btn-group-yesno',
			'default' => '0',
			'showon' => 'show_advanced_options:1',
			'description' => Text::_('COM_COMPONENTBUILDER_IN_STRICT_MODE_ANY_FAILURE_STOPS_THE_RUN_INSTEAD_OF_BEING_REPORTED_AND_SKIPPED')], $yesno);

		// depth attributes
		$this->field($form, 'advanced', [
			'type' => 'number',
			'name' => 'depth',
			'label' => Text::_('COM_COMPONENTBUILDER_FOLDER_DEPTH_LIMIT'),
			'default' => '12',
			'min' => '1',
			'showon' => 'show_advanced_options:1',
			'description' => Text::_('COM_COMPONENTBUILDER_HOW_DEEP_THE_FOLDER_SCAN_MAY_WALK_INTO_THE_SOURCE')]);

		// max files attributes
		$this->field($form, 'advanced', [
			'type' => 'number',
			'name' => 'max_files',
			'label' => Text::_('COM_COMPONENTBUILDER_FILE_COUNT_LIMIT'),
			'default' => '20000',
			'min' => '1',
			'showon' => 'show_advanced_options:1',
			'description' => Text::_('COM_COMPONENTBUILDER_THE_MOST_FILES_ONE_SCAN_MAY_READ_AS_A_GUARD_AGAINST_AIMING_THE_TOOL_AT_A_FOLDER_FAR_LARGER_THAN_ONE_EXTENSION')]);

		// return the form array
		return $form;
	}

	/**
	 * Add one field to the dynamic form
	 *
	 * @param   Form         $form        The form being built
	 * @param   string       $fieldset    The fieldset to add the field to
	 * @param   array        $attributes  The field attributes
	 * @param   array|null   $options     The field options
	 *
	 * @return  void
	 * @since   6.1.7
	 */
	protected function field(Form $form, string $fieldset, array $attributes, ?array $options = null): void
	{
		$xml = $options === null
			? FormHelper::xml($attributes)
			: FormHelper::xml($attributes, $options);

		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, $fieldset);
		}
	}

	/**
	 * Rotative Random Number Generator (1 or 2) - In-Memory
	 *
	 * This version uses a static variable to remember the last value
	 * during the lifetime of the PHP process. No files or sessions needed.
	 *
	 * @return int  Either 1 or 2
	 * @since  6.1.7
	 */
	protected function rotativeRandom(): int
	{
		static $lastValue = null;

		if ($lastValue === 1) {
			// 70% chance to flip to 2, 30% chance to stay on 1
			$value = (mt_rand(1, 100) <= 70) ? 2 : 1;
		} elseif ($lastValue === 2) {
			// 70% chance to flip to 1, 30% chance to stay on 2
			$value = (mt_rand(1, 100) <= 70) ? 1 : 2;
		} else {
			// First run: pick random
			$value = mt_rand(1, 2);
		}

		$lastValue = $value;
		return $value;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		$this->input->set('hidemainmenu', true);

		/** @var Toolbar $toolbar */
		$toolbar = $this->getDocument()->getToolbar();

		// add title to the page
		ToolbarHelper::title(Text::_('COM_COMPONENTBUILDER_EXTRUSION'), 'shuffle');
		// add cpanel button
		ToolbarHelper::custom('extrusion.dashboard', 'grid-2', '', 'COM_COMPONENTBUILDER_DASH', false);
		if ($this->canDo->get('extrusion.health_check'))
		{
			// add Health Check button.
			ToolbarHelper::custom('extrusion.healthCheck', 'health custom-button-healthcheck', '', 'COM_COMPONENTBUILDER_HEALTH_CHECK', false);
		}
		// set help url for this view if found
		$this->help_url = ComponentbuilderHelper::getHelpUrl('extrusion');
		if (StringHelper::check($this->help_url))
		{
			$toolbar->help('COM_COMPONENTBUILDER_HELP_MANAGER', false, $this->help_url);
		}

		// add the options comp button
		if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			$toolbar->preferences('com_componentbuilder');
		}
	}

	/**
	 * Prepare some document related stuff.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function _prepareDocument(): void
	{

		// Only load jQuery if needed. (default is true)
		if ($this->params->get('add_jquery_framework', 1) == 1)
		{
			Html::_('jquery.framework');
		}
		// Load the header checker class.
		// Initialize the header checker.
		$HeaderCheck = new HeaderCheck();

		// Add View JavaScript File
		Html::_('script', 'administrator/components/com_componentbuilder/assets/js/extrusion.js', ['version' => 'auto']);
		// add styles
		foreach ($this->styles as $style)
		{
			Html::_('stylesheet', $style, ['version' => 'auto']);
		}
		// add scripts
		foreach ($this->scripts as $script)
		{
			Html::_('script', $script, ['version' => 'auto']);
		}
	}



	/**
	 * Sanitises a value to plain text for output in a view script.
	 *
	 * @param   mixed  $var     The output to escape.
	 * @param   bool   $shorten The switch to shorten.
	 * @param   int    $length  The shorting length.
	 *
	 * @return  mixed  The value as plain text.
	 * @since   1.6
	 */
	public function sanitize($var, bool $shorten = false, int $length = 40)
	{
		if (!is_string($var))
		{
			return $var;
		}

		return StringHelper::sanitize($var, $this->_charset ?? 'UTF-8', $shorten, $length);
	}
}
