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
namespace VDM\Component\Componentbuilder\Administrator\View\Compiler;

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
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\FormHelper;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\CMS\Version;
use Joomla\CMS\Toolbar\Toolbar;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Html View class for the Compiler
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
		$this->canDo = Actions::get('compiler');

		// Load module values
		$model = $this->getModel();
		$this->styles = $model->getStyles() ?? [];
		$this->scripts = $model->getScripts() ?? [];
		// Initialise variables.
		$this->items = $model->getItems();
		// get the success message if set
		$this->SuccessMessage = $this->app->getUserState('com_componentbuilder.success_message', false);
		
		// get active components
		$this->Components = $model->getComponents();
		
		// set the "dankie" state
		$this->dankie = $this->rotativeRandom();
		
		// get the needed form fields
		$this->form = $this->getDynamicForm();
		
		// set the compiler artwork from global settings
		$this->builder_gif_size = $this->params->get('builder_gif_size', '480-272');
		
		// only run these checks if he has access
		if ($this->canDo->get('compiler.compiler_animations'))
		{
			// if the new artwork is not being targeted hide download option of artwork
			if ('480-540' !== $this->builder_gif_size)
			{
				$this->canDo->set('compiler.compiler_animations', false);
			}
			// we count of all the files are already there
			else
			{
				$directory_path = JPATH_ROOT . "/administrator/components/com_componentbuilder/assets/images/builder-gif";
				// get all the gif files in the gif folder
				$all_gifs = null;
				if (is_dir($directory_path) && is_readable($directory_path))
				{
					$all_gifs = scandir($directory_path);
				
				}
		
				// check if we have any values
				if ($all_gifs !== null && ArrayHelper::check($all_gifs))
				{
					// count number of files but remove the 2 dot values
					$num_gifs = count($all_gifs) - 2;
					// if we have more or the same number of files that are in the array, the we hide the download option
					if ($num_gifs >= ComponentbuilderHelper::getDynamicContentSize('builder-gif', '480-540'))
					{
						$this->canDo->set('compiler.compiler_animations', false);
					}
				}
			}
		}
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
	 * @return  Form|null  The form fields
	 *
	 * @since   3.2.0
	 */
	public function getDynamicForm(): ?Form
	{		
		if(empty($this->Components))
		{
			return null;
		}

		// start the form
		$form = new Form('Builder');

		$form->load('<form
			addruleprefix="VDM\Component\Componentbuilder\Administrator\Rule"
			addfieldprefix="VDM\Component\Componentbuilder\Administrator\Field">
				<config><inlinehelp button="show"/></config>
				<fieldset name="builder"></fieldset>
				<fieldset name="advanced"></fieldset></form>');

		// sales attributes
		$attributes = [
			'type' => 'radio',
			'name' => 'backup',
			'label' => 'COM_COMPONENTBUILDER_ADD_TO_BACKUP_FOLDER_AMP_SALES_SERVER_SMALLIF_SETSMALL',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_SHOULD_THE_ZIPPED_PACKAGE_OF_THE_COMPONENT_BE_MOVED_TO_THE_LOCAL_BACKUP_AND_REMOTE_SALES_SERVER_THIS_IS_ONLY_APPLICABLE_IF_THIS_COMPONENT_HAS_THOSE_VALUES_SET',
			'default' => '0'];
		// set the sales options
		$options = [
			'1' => 'COM_COMPONENTBUILDER_YES',
			'0' => 'COM_COMPONENTBUILDER_NO'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'builder');
		}

		// repository attributes
		$attributes = [
			'type' => 'radio',
			'name' => 'repository',
			'label' => 'COM_COMPONENTBUILDER_ADD_TO_REPOSITORY_FOLDER',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_SHOULD_THE_COMPONENT_BE_MOVED_TO_YOUR_LOCAL_REPOSITORY_FOLDER',
			'default' => '1'];
		// start the repository options
		$options = [
			'1' => 'COM_COMPONENTBUILDER_YES',
			'0' => 'COM_COMPONENTBUILDER_NO'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'builder');
		}

		// placeholders attributes
		$attributes = [
			'type' => 'radio',
			'name' => 'add_placeholders',
			'label' => 'COM_COMPONENTBUILDER_ADD_CUSTOM_CODE_PLACEHOLDERS',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_SHOULD_JCB_INSERT_THE_CUSTOM_CODE_PLACEHOLDERS_THIS_IS_ONLY_APPLICABLE_IF_THIS_COMPONENT_HAS_CUSTOM_CODE',
			'default' => '2'];
		// start the placeholders options
		$options = [
			'2' => 'COM_COMPONENTBUILDER_GLOBAL',
			'1' => 'COM_COMPONENTBUILDER_YES',
			'0' => 'COM_COMPONENTBUILDER_NO'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'builder');
		}

		// debuglinenr attributes
		$attributes = [
			'type' => 'radio',
			'name' => 'debug_line_nr',
			'label' => 'COM_COMPONENTBUILDER_DEBUG_LINE_NUMBERS',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_ADD_CORRESPONDING_LINE_NUMBERS_TO_THE_DYNAMIC_COMMENTS_SO_TO_SEE_WHERE_IN_THE_COMPILER_THE_LINES_OF_CODE_WAS_BUILD_THIS_WILL_HELP_IF_YOU_NEED_TO_GET_MORE_TECHNICAL_WITH_AN_ISSUE_ON_GITHUB_OR_EVEN_FOR_YOUR_OWN_DEBUGGING',
			'default' => '2'];
		$options = [
			'2' => 'COM_COMPONENTBUILDER_GLOBAL',
			'1' => 'COM_COMPONENTBUILDER_YES',
			'0' => 'COM_COMPONENTBUILDER_NO'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'builder');
		}

		// minify attributes
		$attributes = [
			'type' => 'radio',
			'name' => 'minify',
			'label' => 'COM_COMPONENTBUILDER_MINIFY_JAVASCRIPT',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_SHOULD_THE_JAVASCRIPT_BE_MINIFIED_IN_THE_COMPONENT',
			'default' => '2'];
		$options = [
			'2' => 'COM_COMPONENTBUILDER_GLOBAL',
			'1' => 'COM_COMPONENTBUILDER_YES',
			'0' => 'COM_COMPONENTBUILDER_NO'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'builder');
		}

		// powers attributes
		$attributes = [
			'type' => 'radio',
			'name' => 'powers',
			'label' => 'COM_COMPONENTBUILDER_ADD_POWERS',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_SHOULD_JCB_ADD_ANY_POWERS_THAT_ARE_CONNECTED_TO_THIS_COMPONENT_THIS_MAY_BE_HELPFUL_IF_YOU_ARE_LOADING_POWERS_VIA_ANOTHER_COMPONENT_AND_WOULD_LIKE_TO_AVOID_ADDING_IT_TO_BOTH_JUST_REMEMBER_THAT_IN_THIS_CASE_YOU_NEED_TO_LOAD_THE_POWERS_VIA_A_PLUGIN',
			'default' => '2'];
		$options = [
			'2' => 'COM_COMPONENTBUILDER_GLOBAL',
			'1' => 'COM_COMPONENTBUILDER_YES',
			'0' => 'COM_COMPONENTBUILDER_NO'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'builder');
		}

		// component attributes
		$attributes = [
			'type' => 'list',
			'name' => 'component_id',
			'label' => 'COM_COMPONENTBUILDER_COMPONENTS',
			'class' => 'list_class',
			'description' => 'COM_COMPONENTBUILDER_SELECT_THE_COMPONENT_TO_COMPILE',
			'required' => 'true'];
		// start the component options
		$options = [];
		$options[''] = 'COM_COMPONENTBUILDER__SELECT_COMPONENT_';
		// load component options from array
		foreach($this->Components as $component)
		{
			$options[(int) $component->id] = $this->escape($component->name);
		}

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'builder');
		}

		// Joomla Versions attributes
		$attributes = [
			'type' => 'list',
			'name' => 'joomla_version',
			'label' => 'COM_COMPONENTBUILDER_JOOMLA_VERSION',
			'class' => 'list_class',
			'description' => 'COM_COMPONENTBUILDER_WHAT_VERSION_OF_JOOMLA_WOULD_YOU_LIKE_TO_TARGET',
			'default' => (string) Version::MAJOR_VERSION];
		// start the joomla versions options
		$options = [
			'3' => 'COM_COMPONENTBUILDER_JOOMLA_THREE',
			'4' => 'COM_COMPONENTBUILDER_JOOMLA_FOUR',
			'5' => 'COM_COMPONENTBUILDER_JOOMLA_FIVE',
			'6' => 'COM_COMPONENTBUILDER_JOOMLA_SIX'
		];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'builder');
		}

		// Advanced Options
		$attributes = [
			'type' => 'radio',
			'name' => 'show_advanced_options',
			'label' => 'COM_COMPONENTBUILDER_SHOW_ADVANCED_OPTIONS',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_WOULD_YOU_LIKE_TO_SEE_THE_ADVANCED_COMPILER_OPTIONS',
			'default' => '0'];
		// start the advanced options switch
		$options = [
			'1' => 'COM_COMPONENTBUILDER_YES',
			'0' => 'COM_COMPONENTBUILDER_NO'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'builder');
		}

		// Advanced Options note attributes
		$attributes = [
			'type' => 'note',
			'name' => 'show_advanced_options_note',
			'label' => "COM_COMPONENTBUILDER_ADVANCED_OPTIONS",
			'heading' => 'h3',
			'showon' => 'show_advanced_options:1'];

		// add to form
		$xml = FormHelper::xml($attributes);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// powers repository attributes
		$attributes = [
			'type' => 'radio',
			'name' => 'powers_repository',
			'label' => 'COM_COMPONENTBUILDER_ACTIVATE_SUPER_POWERS',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_THIS_ADDS_POWERS_TO_A_LOCAL_REPOSITORY_FOLDER_ALL_BAPPROVEDB_POWERS_LINKED_TO_THIS_COMPONENT_WILL_BE_MOVED_TO_YOUR_BLOCALB_POWERS_REPOSITORY_FOLDER_INTO_THEIR_SELECTIVE_TARGET_PATHS_THIS_LOCAL_FOLDER_PATH_MUST_BE_SET_IN_THE_GLOBAL_OPTIONS_OF_JCB_UNDER_THE_BSUPER_POWERB_TAB',
			'default' => '2',
			'showon' => 'show_advanced_options:1'];
		// start the repository options
		$options = [
			'2' => 'COM_COMPONENTBUILDER_GLOBAL',
			'1' => 'COM_COMPONENTBUILDER_YES',
			'0' => 'COM_COMPONENTBUILDER_NO'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// powers local path to repositories attributes
		$attributes = [
			'type' => 'text',
			'name' => 'local_powers_repository_path',
			'label' => 'COM_COMPONENTBUILDER_LOCAL_POWERS_REPOSITORY_PATH',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_HERE_YOU_CAN_SET_THE_PATH_TO_THE_SUPER_POWERS_LOCAL_REPOSITORY_FOLDER_WHERE_BLAYERCOREB_AND_ALL_TARGETED_BLAYEROWNB_SUB_PATHS_WILL_BE_PLACED_WITH_THEIR_SELECTIVE_BSWITCHAPPROVEDB_POWERS',
			'default' => $this->params->get('local_powers_repository_path', ''),
			'showon' => 'show_advanced_options:1[AND]powers_repository:1'];

		// add to form
		$xml = FormHelper::xml($attributes);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// Indentation attributes
		$attributes = [
			'type' => 'radio',
			'name' => 'indentation_value',
			'label' => 'COM_COMPONENTBUILDER_INDENTATION_OPTIONS',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_WHICH_TYPE_OF_INDENTATION_WOULD_YOU_LIKE_TO_USE_PLEASE_NOTE_THAT_THIS_DOES_NOT_YET_IMPACT_THE_STATIC_TEMPLATES',
			'default' => '1',
			'showon' => 'show_advanced_options:1'];

		// start the indentation options
		$options = [
			'1' => 'COM_COMPONENTBUILDER_TAB',
			'2' => 'COM_COMPONENTBUILDER_TWO_SPACES',
			'4' => 'COM_COMPONENTBUILDER_FOUR_SPACES'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// Build date attributes
		$attributes = [
			'type' => 'radio',
			'name' => 'add_build_date',
			'label' => 'COM_COMPONENTBUILDER_BUILD_DATE',
			'class' => 'btn-group btn-group-yesno',
			'description' => 'COM_COMPONENTBUILDER_WOULD_YOU_LIKE_TO_OVERRIDE_THE_BUILD_DATE',
			'default' => '1',
			'showon' => 'show_advanced_options:1'];
		// start the build date options
		$options = [
			'1' => 'COM_COMPONENTBUILDER_DEFAULT',
			'2' => 'COM_COMPONENTBUILDER_MANUAL',
			'3' => 'COM_COMPONENTBUILDER_COMPONENT'];

		// add to form
		$xml = FormHelper::xml($attributes, $options);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// Build date note attributes
		$attributes = [
			'type' => 'note',
			'name' => 'add_build_date_note_two',
			'description' => 'COM_COMPONENTBUILDER_ALLOWS_YOU_TO_OVERRIDE_THE_BUILD_DATE_BY_SELECTING_A_DATE_MANUALLY_FROM_THE_CALENDER',
			'class' => 'alert alert-info',
			'showon' => 'show_advanced_options:1[AND]add_build_date:2'];

		// add to form
		$xml = FormHelper::xml($attributes);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// Build date note attributes
		$attributes = [
			'type' => 'note',
			'name' => 'add_build_date_note_three',
			'description' => "COM_COMPONENTBUILDER_THE_COMPONENTS_LAST_MODIFIED_DATE_WILL_BE_USED",
			'class' => 'alert alert-info',
			'showon' => 'show_advanced_options:1[AND]add_build_date:3'];

		// add to form
		$xml = FormHelper::xml($attributes);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// Build date calendar attributes
		$attributes = [
			'type' => 'calendar',
			'name' => 'build_date',
			'label' => 'COM_COMPONENTBUILDER_SELECT_BUILD_DATE',
			'format' => '%Y-%m-%d',
			'filter' => 'user_utc',
			'default' => 'now',
			'size' => '22',
			'showon' => 'show_advanced_options:1[AND]add_build_date:2'];

		// add to form
		$xml = FormHelper::xml($attributes);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// Build date note attributes
		if ($this->dankie == 1)
		{
			$attributes = [
				'type' => 'note',
				'name' => 'donations_note',
				'label' => "COM_COMPONENTBUILDER_DONATIONS",
				'description' =>  $this->getSupportMessage(),
				'class' => 'alert alert-success',
				'heading' => 'h1',
				'showon' => 'show_advanced_options:1'];
		}
		else
		{
			$attributes = [
				'type' => 'note',
				'name' => 'partner_note',
				'description' =>  $this->getSupportMessage(),
				'showon' => 'show_advanced_options:1'];
		}

		// add to form
		$xml = FormHelper::xml($attributes);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// Build date note line attributes
		$attributes = [
			'type' => 'note',
			'name' => 'donations_note_line',
			'description' => '<hr />',
			'showon' => 'show_advanced_options:1'];

		// add to form
		$xml = FormHelper::xml($attributes);
		if ($xml instanceof \SimpleXMLElement)
		{
			$form->setField($xml, null, true, 'advanced');
		}

		// return the form array
		return $form;
	}

	/**
	 * Get the dynamic support request/gratitude message
	 *
	 * @return  string  The support message
	 *
	 * @since   3.2.0
	 */
	protected function getSupportMessage(): string
	{
		if ($this->dankie == 1)
		{
			return LayoutHelper::render('jcbsupportmessage', []);
		}
		else
		{
			return ComponentbuilderHelper::getDynamicContent('banner', '728-90');
		}
	}

	/**
	 * Rotative Random Number Generator (1 or 2) - In-Memory
	 *
	 * This version uses a static variable to remember the last value
	 * during the lifetime of the PHP process. No files or sessions needed.
	 *
	 * @return int  Either 1 or 2
	 * @since  5.1.2
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
		ToolbarHelper::title(Text::_('COM_COMPONENTBUILDER_COMPILER'), 'cogs');
		// add cpanel button
		ToolbarHelper::custom('compiler.dashboard', 'grid-2', '', 'COM_COMPONENTBUILDER_DASH', false);
		if ($this->canDo->get('compiler.compiler_animations'))
		{
			// add Compiler Animations button.
			ToolbarHelper::custom('compiler.getDynamicContent', 'download custom-button-getdynamiccontent', '', 'COM_COMPONENTBUILDER_COMPILER_ANIMATIONS', false);
		}
		if ($this->canDo->get('compiler.clear_tmp'))
		{
			// add Clear tmp button.
			ToolbarHelper::custom('compiler.clearTmp', 'purge custom-button-cleartmp', '', 'COM_COMPONENTBUILDER_CLEAR_TMP', false);
		}
		if ($this->canDo->get('compiler.health_check'))
		{
			// add Health Check button.
			ToolbarHelper::custom('compiler.healthCheck', 'health custom-button-healthcheck', '', 'COM_COMPONENTBUILDER_HEALTH_CHECK', false);
		}
		// set help url for this view if found
		$this->help_url = ComponentbuilderHelper::getHelpUrl('compiler');
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
		Html::_('script', 'administrator/components/com_componentbuilder/assets/js/compiler.js', ['version' => 'auto']);
		// add marked library
		Html::_('script', 'administrator/components/com_componentbuilder/custom/marked.js', ['version' => 'auto']);
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
		// Set the Custom JS script to view
		$this->getDocument()->getWebAssetManager()->addInlineScript("
			function getComponentDetails_server(id) {
				let getUrl = JRouter(\"index.php?option=com_componentbuilder&task=ajax.getComponentDetails&format=json&raw=true\");
				let request = new URLSearchParams();
				if (token.length > 0 && id > 0) {
					request.append(token, '1');
					request.append('id', id);
				}
				return fetch(getUrl + '&'  + request.toString(), {
					method: 'GET',
					headers: {
						'Accept': 'application/json'
					}
				}).then(response => response.json());
			}
			function getComponentDetails(id) {
				getComponentDetails_server(id).then(function(result) {
					if (result.html) {
						document.getElementById('component-details').innerHTML = result.html;
						if (result.preferred_joomla_version) {
							jQuery('#joomla_version').val(result.preferred_joomla_version);
							jQuery('#joomla_version').trigger('liszt:updated');
							jQuery('#joomla_version').trigger('change');
						}
					}
				});
			}
		");
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
