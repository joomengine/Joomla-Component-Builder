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
namespace VDM\Component\Componentbuilder\Administrator\View\Search;

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
use VDM\Joomla\Componentbuilder\Search\Factory as SearchFactory;
use Joomla\CMS\Form\Form;
use VDM\Joomla\Componentbuilder\Utilities\Permitted\Actions;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\FormHelper;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;

// No direct access to this file
\defined('_JEXEC') or die; 

/**
 * Componentbuilder Html View class for the Search
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
	 * @since  3.10.11
	 */
	protected array $styles;

	/**
	 * The scripts url array
	 *
	 * @var    array
	 * @since  3.10.11
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
		$this->canDo = Actions::get('search');

		// Load module values
		$model = $this->getModel();
		$model->setUseExceptions(true);
		$this->styles = $model->getStyles();
		$this->scripts = $model->getScripts();
		// Initialise variables.
		$this->item = $model->getItem();
		$this->urlvalues = $model->getUrlValues();
		// get the needed form fields
		$this->form = $this->getDynamicForm();
		
		// build our table headers
		$this->table_headers = array(
			'edit' => 'E',
			'code' => Text::_('COM_COMPONENTBUILDER_FOUND_TEXT'),
			'table' => Text::_('COM_COMPONENTBUILDER_TABLE'),
			'field' => Text::_('COM_COMPONENTBUILDER_FIELD'),
			'id' => Text::_('ID'),
			'line' => Text::_('COM_COMPONENTBUILDER_LINE')
		);
		
		// set some JavaScript Language
		Text::script('COM_COMPONENTBUILDER_YOUR_ARE_ABOUT_TO_UPDATE_ROW');
		Text::script('COM_COMPONENTBUILDER_FIELD_IN_THE');
		Text::script('COM_COMPONENTBUILDER_TABLE');
		Text::script('COM_COMPONENTBUILDER_THIS_CAN_NOT_BE_UNDONE_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE');
		Text::script('COM_COMPONENTBUILDER_YOUR_ARE_ABOUT_TO_UPDATE_BALLB_VALUES_THAT_CAN_BE_FOUND_IN_THE_DATABASE');
		Text::script('COM_COMPONENTBUILDER_YOU_WILL_REPLACE');
		Text::script('COM_COMPONENTBUILDER_WITH');
		Text::script('COM_COMPONENTBUILDER_THIS_CAN_NOT_BE_UNDONE_BYOU_HAVE_BEEN_WARNEDB');
		Text::script('COM_COMPONENTBUILDER_ARE_YOU_THEREFORE_ABSOLUTELY_SURE_YOU_WANT_TO_CONTINUE');
		Text::script('COM_COMPONENTBUILDER_THE_SEARCH_PROCESS_HAD_AN_ERROR_WITH_TABLE');
		Text::script('COM_COMPONENTBUILDER_THE_REPLACE_PROCESS_HAD_AN_ERROR_WITH_TABLE');
		Text::script('COM_COMPONENTBUILDER_REPLACE_PROCESS_COMPLETE');
		Text::script('COM_COMPONENTBUILDER_SEARCHING');
		Text::script('COM_COMPONENTBUILDER_TABLES_WITH');
		Text::script('COM_COMPONENTBUILDER_FIELDS_THAT_HAD');
		Text::script('COM_COMPONENTBUILDER_LINES');
		Text::script('COM_COMPONENTBUILDER_AND_FINISHED_THE_SEARCH_IN');
		Text::script('COM_COMPONENTBUILDER_SECONDS');
		Text::script('COM_COMPONENTBUILDER_WOULD_YOU_LIKE_TO_DO_A_REVERSE_SEARCH');
		Text::script('COM_COMPONENTBUILDER_WOULD_YOU_LIKE_TO_REPEAT_THE_SAME_SEARCH');
		Text::script('COM_COMPONENTBUILDER_YES_UPDATE_ALL');
		Text::script('COM_COMPONENTBUILDER_NO');
		Text::script('COM_COMPONENTBUILDER_YES');
		// just get it on the page for now....
		ToolbarHelper::inlinehelp();
		
		// get the web asset manager
		$web = Factory::getApplication()->getDocument()->getWebAssetManager();
		// load Joomla dialog only
		$web->useScript('joomla.dialog');

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			// add the tool bar
			$this->addToolBar();
		}

		// Set the html view document stuff
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Get the dynamic build form fields needed on the page
	 *
	 * @return  Form|null  The array of form fields
	 *
	 * @since   3.2.0
	 */
	public function getDynamicForm(): ?Form
	{
		if(ArrayHelper::check($this->item) &&
			ArrayHelper::check($this->item['tables']) &&
			ArrayHelper::check($this->item['components']))
		{
			// start the form
			$form = new Form('Search');

			$form->load('<form
				addruleprefix="VDM\Component\Componentbuilder\Administrator\Rule"
				addfieldprefix="VDM\Component\Componentbuilder\Administrator\Field">
					<config><inlinehelp button="show"/></config>
					<fieldset name="search"></fieldset>
					<fieldset name="settings"></fieldset>
					<fieldset name="view"></fieldset>
			</form>');

			// Search Mode
			$attributes = [
				'type' => 'radio',
				'name' => 'type_search',
				'hiddenLabel' => true,
				'label' => 'COM_COMPONENTBUILDER_MODE',
				'class' => 'btn-group',
				'description' => 'COM_COMPONENTBUILDER_SEARCH_OR_SEARCH_AND_REPLACE',
				'default' => $this->urlvalues['type_search']];
			// set the mode options
			$options = [
				1 => 'COM_COMPONENTBUILDER_SEARCH',
				2 => 'COM_COMPONENTBUILDER_REPLACE',
				0 => 'COM_COMPONENTBUILDER_CLEAR'];
			// add to form
			$xml = FormHelper::xml($attributes, $options);
			if ($xml instanceof \SimpleXMLElement)
			{
				$form->setField($xml, null, true, 'search');
			}

			// search text attributes
			$attributes = [
				'type' => 'text',
				'name' => 'search_value',
				'hiddenLabel' => 'true',
				'label' => 'COM_COMPONENTBUILDER_SEARCH',
				'size' => 150,
				'maxlength' => 200,
				'description' => 'COM_COMPONENTBUILDER_HERE_YOU_CAN_ENTER_YOUR_SEARCH_TEXT',
				'filter' => 'RAW',
				'class' => 'search-value',
				'hint' => 'COM_COMPONENTBUILDER_ENTER_YOUR_SEARCH_TEXT',
				'autocomplete' => true,
				'default' => $this->urlvalues['search_value']];
			// add to form
			$xml = FormHelper::xml($attributes);
			if ($xml instanceof \SimpleXMLElement)
			{
				$form->setField($xml, null, true, 'search');
			}

			// replace text attributes
			$attributes = [
				'type' => 'text',
				'name' => 'replace_value',
				'hiddenLabel' => 'true',
				'label' => 'COM_COMPONENTBUILDER_REPLACE',
				'size' => 150,
				'maxlength' => 200,
				'description' => 'COM_COMPONENTBUILDER_HERE_YOU_CAN_ENTER_THE_REPLACE_TEXT_THAT_YOU_WOULD_LIKE_TO_USE_AS_REPLACEMENT_FOR_THE_SEARCH_TEXT_FOUND',
				'filter' => 'RAW',
				'class' => 'replace-value',
				'hint' => 'COM_COMPONENTBUILDER_ENTER_YOUR_REPLACE_TEXT',
				'autocomplete' => true,
				'showon' => 'type_search:2',
				'default' => $this->urlvalues['replace_value']];
			// add to form
			$xml = FormHelper::xml($attributes);
			if ($xml instanceof \SimpleXMLElement)
			{
				$form->setField($xml, null, true, 'search');
			}

			// Search Behaviour
			$default = [];
			if ($this->urlvalues['match_case'] == 1)
			{
				$default[] = 'match_case';
			}
			if ($this->urlvalues['whole_word'] == 1)
			{
				$default[] = 'whole_word';
			}
			if ($this->urlvalues['regex_search'] == 1)
			{
				$default[] = 'regex_search';
			}
			$attributes = [
				'type' => 'checkboxes',
				'name' => 'search_behaviour',
				'label' => 'COM_COMPONENTBUILDER_BEHAVIOUR',
				'description' => 'COM_COMPONENTBUILDER_SET_THE_SEARCH_BEHAVIOUR_HERE'];
			if (ArrayHelper::check($default))
			{
				$attributes['default'] = implode(',', $default);
			}
			// set the mode options
			$options = [
				'match_case' => 'COM_COMPONENTBUILDER_MATCH_CASE',
				'whole_word' => 'COM_COMPONENTBUILDER_WHOLE_WORD',
				'regex_search' => 'COM_COMPONENTBUILDER_REGEX_SEARCH'];
			// add to form
			$xml = FormHelper::xml($attributes, $options);
			if ($xml instanceof \SimpleXMLElement)
			{
				$form->setField($xml, null, true, 'settings');
			}

			// component attributes
			$attributes = [
				'type' => 'list',
				'name' => 'component_id',
				'label' => 'COM_COMPONENTBUILDER_COMPONENTS_BR_SMALLDISABLED_SOONSMALL',
				'class' => 'list_class',
				'description' => 'COM_COMPONENTBUILDER_SELECT_THE_COMPONENT_TO_SEARCH',
				'required' => 'true',
				'disable' => 'true',
				'readonly' => 'true',
				'default' => -1];
			// start the component options
			$options = [];
			$options['-1'] = 'COM_COMPONENTBUILDER__SEARCH_ALL_';
			// load component options from array
			foreach($this->item['components'] as $component)
			{
				$options[(int) $component->id] = $this->escape($component->name);
			}
			// add to form
			$xml = FormHelper::xml($attributes, $options);
			if ($xml instanceof \SimpleXMLElement)
			{
				$form->setField($xml, null, true, 'settings');
			}

			// table attributes
			$attributes = [
				'type' => 'list',
				'name' => 'table_name',
				'label' => 'COM_COMPONENTBUILDER_TABLES',
				'class' => 'list_class',
				'description' => 'COM_COMPONENTBUILDER_SELECT_THE_TABLE_TO_SEARCH',
				'required' => 'true',
				'default' => $this->urlvalues['table_name']];
			// start the component options
			$options = [];
			$options['-1'] = 'COM_COMPONENTBUILDER__SEARCH_ALL_';
			// load table options from array
			foreach($this->item['tables'] as $table)
			{
				$options[$table] = $this->escape($table);
			}
			// add to form
			$xml = FormHelper::xml($attributes, $options);
			if ($xml instanceof \SimpleXMLElement)
			{
				$form->setField($xml, null, true, 'settings');
			}

			// editor attributes
			$attributes = [
				'type' => 'editor',
				'name' => 'item_code',
				'label' => 'COM_COMPONENTBUILDER_ITEM_CODE',
				'width' => '100%',
				'height' => '350px',
				'class' => 'item_code_editor',
				'syntax' => 'php',
				'buttons' => 'false',
				'filter' => 'raw',
				'editor' => 'codemirror|none'];
			// add to form
			$xml = FormHelper::xml($attributes, $options);
			if ($xml instanceof \SimpleXMLElement)
			{
				$form->setField($xml, null, true, 'view');
			}

			// return the form array
			return $form;
		}

		return null;
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

		// set the title
		if (!empty($this->item->name))
		{
			$title = $this->item->name;
		}

		// check for empty title to add the view name
		if (empty($title))
		{
			$title = Text::_('COM_COMPONENTBUILDER_SEARCH');
		}

		// add title to the page
		ToolbarHelper::title($title, 'search');
		// add cpanel button
		$this->getDocument()->getToolbar()->standardButton('grid-2', 'COM_COMPONENTBUILDER_DASH', 'search.dashboard')->listCheck(false);
		if ($this->canDo->get('search.compiler'))
		{
			// add Compiler button.
			$this->getDocument()->getToolbar()->standardButton('cogs custom-button-opencompiler', 'COM_COMPONENTBUILDER_COMPILER', 'search.openCompiler')->listCheck(false);
		}
		// set help url for this view if found
		$this->help_url = ComponentbuilderHelper::getHelpUrl('search');
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
		$wa = $this->getDocument()->getWebAssetManager();
		$root = Uri::root();

		$jcbStyleDependencies = [];
		$jcbScriptDependencies = ['core'];

		// Only load jQuery if needed. (default is true)
		if ($this->params->get('add_jquery_framework', 1) == 1)
		{
			$wa->useScript('jquery')->useScript('jquery-noconflict');

			$jcbScriptDependencies[] = 'jquery';
			$jcbScriptDependencies[] = 'jquery-noconflict';
		}

		// Load the header checker class.
		// Initialize the header checker.
		$HeaderCheck = new HeaderCheck();

		// always load these files.
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('style', 'jcb.style.597bee57aa6f693c319af305b27ff02d28543be95a85fb2d5c13442d72c98413'))
{
	$jcbAssetManager->registerStyle('jcb.style.597bee57aa6f693c319af305b27ff02d28543be95a85fb2d5c13442d72c98413', Uri::root() . 'media/com_componentbuilder/datatable/css/datatables.min.css', ['version' => 'auto'], [], $jcbStyleDependencies ?? []);
}
$jcbAssetManager->useStyle('jcb.style.597bee57aa6f693c319af305b27ff02d28543be95a85fb2d5c13442d72c98413');
$jcbStyleDependencies = array_values(array_unique(array_merge($jcbStyleDependencies ?? [], ['jcb.style.597bee57aa6f693c319af305b27ff02d28543be95a85fb2d5c13442d72c98413'])));
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('script', 'jcb.script.54fe4c7c5c4dddd7936d1c5cf5115b8593cb2ff2a1c3aa6cd4b86d7344225359'))
{
	$jcbAssetManager->registerScript('jcb.script.54fe4c7c5c4dddd7936d1c5cf5115b8593cb2ff2a1c3aa6cd4b86d7344225359', Uri::root() . 'media/com_componentbuilder/datatable/js/pdfmake.min.js', ['version' => 'auto'], [], $jcbScriptDependencies ?? []);
}
$jcbAssetManager->useScript('jcb.script.54fe4c7c5c4dddd7936d1c5cf5115b8593cb2ff2a1c3aa6cd4b86d7344225359');
$jcbScriptDependencies = array_values(array_unique(array_merge($jcbScriptDependencies ?? [], ['jcb.script.54fe4c7c5c4dddd7936d1c5cf5115b8593cb2ff2a1c3aa6cd4b86d7344225359'])));
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('script', 'jcb.script.e4de5929a23cbb6c3e76294aaab49150cc6b6a086e2f20aa48f70e6b107d2000'))
{
	$jcbAssetManager->registerScript('jcb.script.e4de5929a23cbb6c3e76294aaab49150cc6b6a086e2f20aa48f70e6b107d2000', Uri::root() . 'media/com_componentbuilder/datatable/js/vfs_fonts.js', ['version' => 'auto'], [], $jcbScriptDependencies ?? []);
}
$jcbAssetManager->useScript('jcb.script.e4de5929a23cbb6c3e76294aaab49150cc6b6a086e2f20aa48f70e6b107d2000');
$jcbScriptDependencies = array_values(array_unique(array_merge($jcbScriptDependencies ?? [], ['jcb.script.e4de5929a23cbb6c3e76294aaab49150cc6b6a086e2f20aa48f70e6b107d2000'])));
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('script', 'jcb.script.f5545f934edee7769abf593a2918481038a311b8a235a5051bd9efc3da1cbe35'))
{
	$jcbAssetManager->registerScript('jcb.script.f5545f934edee7769abf593a2918481038a311b8a235a5051bd9efc3da1cbe35', Uri::root() . 'media/com_componentbuilder/datatable/js/datatables.min.js', ['version' => 'auto'], [], $jcbScriptDependencies ?? []);
}
$jcbAssetManager->useScript('jcb.script.f5545f934edee7769abf593a2918481038a311b8a235a5051bd9efc3da1cbe35');
$jcbScriptDependencies = array_values(array_unique(array_merge($jcbScriptDependencies ?? [], ['jcb.script.f5545f934edee7769abf593a2918481038a311b8a235a5051bd9efc3da1cbe35'])));

		// Add styles
		foreach ($this->styles as $style)
		{
			$style = str_starts_with($style, '//') ? $style : ltrim($style, '/');
			$assetName = 'jcb.style.' . hash('sha256', $style);

			if (!$wa->assetExists('style', $assetName))
			{
				$assetUri = preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $style)
					? $style : $root . $style;

				$wa->registerStyle(
					$assetName,
					$assetUri,
					['version' => 'auto'],
					[],
					$jcbStyleDependencies
				);
			}

			$wa->useStyle($assetName);

			if (!in_array($assetName, $jcbStyleDependencies, true))
			{
				$jcbStyleDependencies[] = $assetName;
			}
		}

		$viewScripts = [
			// Add View JavaScript File
			'media/com_componentbuilder/js/admin/search.js',

			// Add scripts
			...$this->scripts,
		];

		foreach ($viewScripts as $script)
		{
			$script = str_starts_with($script, '//') ? $script : ltrim($script, '/');
			$assetName = 'jcb.script.' . hash('sha256', $script);

			if (!$wa->assetExists('script', $assetName))
			{
				$assetUri = preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $script)
					? $script : $root . $script;

				$wa->registerScript(
					$assetName,
					$assetUri,
					['version' => 'auto'],
					[],
					$jcbScriptDependencies
				);
			}

			$wa->useScript($assetName);

			if (!in_array($assetName, $jcbScriptDependencies, true))
			{
				$jcbScriptDependencies[] = $assetName;
			}
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
