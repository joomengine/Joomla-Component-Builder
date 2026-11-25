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
namespace VDM\Component\Componentbuilder\Administrator\View\Pull_selection;

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
use VDM\Joomla\Componentbuilder\Utilities\Permitted\Actions;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;

// No direct access to this file
\defined('_JEXEC') or die; 

/**
 * Componentbuilder Html View class for the Pull_selection
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
		$this->canDo = Actions::get('pull_selection');

		// Load module values
		$model = $this->getModel();
		$model->setUseExceptions(true);
		$this->styles = $model->getStyles();
		$this->scripts = $model->getScripts();
		// Initialise variables.
		$this->item = $model->getItem();

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
			$title = Text::_('COM_COMPONENTBUILDER_PULL_SELECTION');
		}

		// add title to the page
		ToolbarHelper::title($title, 'puzzle');
		// add cpanel button
		$this->getDocument()->getToolbar()->standardButton('grid-2', 'COM_COMPONENTBUILDER_DASH', 'pull_selection.dashboard')->listCheck(false);
		// set help url for this view if found
		$this->help_url = ComponentbuilderHelper::getHelpUrl('pull_selection');
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
if (!$jcbAssetManager->assetExists('style', 'jcb.style.a9622e4a3af730668368d2c19e86c1658b0fbf3d0e289f8baa614a36c81029f5'))
{
	$jcbAssetManager->registerStyle('jcb.style.a9622e4a3af730668368d2c19e86c1658b0fbf3d0e289f8baa614a36c81029f5', Uri::root() . 'media/com_componentbuilder/datatable-bootstrap5/css/datatables.min.css', ['version' => 'auto'], [], $jcbStyleDependencies ?? []);
}
$jcbAssetManager->useStyle('jcb.style.a9622e4a3af730668368d2c19e86c1658b0fbf3d0e289f8baa614a36c81029f5');
$jcbStyleDependencies = array_values(array_unique(array_merge($jcbStyleDependencies ?? [], ['jcb.style.a9622e4a3af730668368d2c19e86c1658b0fbf3d0e289f8baa614a36c81029f5'])));
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('script', 'jcb.script.2b27c98692bdb1a3b25cd45faf86e51ed7086b2d30172ce2c4a8fd91b83e7df4'))
{
	$jcbAssetManager->registerScript('jcb.script.2b27c98692bdb1a3b25cd45faf86e51ed7086b2d30172ce2c4a8fd91b83e7df4', Uri::root() . 'media/com_componentbuilder/datatable-bootstrap5/js/pdfmake.min.js', ['version' => 'auto'], [], $jcbScriptDependencies ?? []);
}
$jcbAssetManager->useScript('jcb.script.2b27c98692bdb1a3b25cd45faf86e51ed7086b2d30172ce2c4a8fd91b83e7df4');
$jcbScriptDependencies = array_values(array_unique(array_merge($jcbScriptDependencies ?? [], ['jcb.script.2b27c98692bdb1a3b25cd45faf86e51ed7086b2d30172ce2c4a8fd91b83e7df4'])));
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('script', 'jcb.script.98542e2bdc8c49143e9a620f4d4d335ed8290da399ea0f14576672d858ae13ef'))
{
	$jcbAssetManager->registerScript('jcb.script.98542e2bdc8c49143e9a620f4d4d335ed8290da399ea0f14576672d858ae13ef', Uri::root() . 'media/com_componentbuilder/datatable-bootstrap5/js/vfs_fonts.js', ['version' => 'auto'], [], $jcbScriptDependencies ?? []);
}
$jcbAssetManager->useScript('jcb.script.98542e2bdc8c49143e9a620f4d4d335ed8290da399ea0f14576672d858ae13ef');
$jcbScriptDependencies = array_values(array_unique(array_merge($jcbScriptDependencies ?? [], ['jcb.script.98542e2bdc8c49143e9a620f4d4d335ed8290da399ea0f14576672d858ae13ef'])));
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('script', 'jcb.script.e929ceac1025b3b0d4ece44a86310dbd892f3cc57dffdac75e70c62541ec73f4'))
{
	$jcbAssetManager->registerScript('jcb.script.e929ceac1025b3b0d4ece44a86310dbd892f3cc57dffdac75e70c62541ec73f4', Uri::root() . 'media/com_componentbuilder/datatable-bootstrap5/js/datatables.min.js', ['version' => 'auto'], [], $jcbScriptDependencies ?? []);
}
$jcbAssetManager->useScript('jcb.script.e929ceac1025b3b0d4ece44a86310dbd892f3cc57dffdac75e70c62541ec73f4');
$jcbScriptDependencies = array_values(array_unique(array_merge($jcbScriptDependencies ?? [], ['jcb.script.e929ceac1025b3b0d4ece44a86310dbd892f3cc57dffdac75e70c62541ec73f4'])));
				// set some language strings for the JS class: [PullManager]
				Text::script("COM_COMPONENTBUILDER_MISSING_REPOSITORY_OR_AREA_DATA");
				Text::script("COM_COMPONENTBUILDER_FAILED_TO_RETRIEVE_REPOSITORY_INDEX");
				Text::script("COM_COMPONENTBUILDER_NETWORK_OR_SERVER_ERROR_OCCURRED_WHILE_FETCHING_INDEX");
				Text::script("COM_COMPONENTBUILDER_NO_ITEMS_SELECTED");
				Text::script("COM_COMPONENTBUILDER_FAILED_TO_PULL_SELECTED_POWERS");
				Text::script("COM_COMPONENTBUILDER_ERROR_OCCURRED_WHILE_PULLING_POWERS");
				Text::script('COM_COMPONENTBUILDER_THESE_ITEMS_WERE_ALREADY_PRESENT_LOCALLY_AND_WERE_NOT_PULLED'); // ?
				Text::script('COM_COMPONENTBUILDER_THESE_ITEMS_COULD_NOT_BE_FOUND_IN_THE_REMOTE_REPOSITORY_AND_WERE_NOT_PULLED');
				Text::script('COM_COMPONENTBUILDER_THESE_ITEMS_WERE_SUCCESSFULLY_PULLED');

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
			'media/com_componentbuilder/js/admin/pull_selection.js',

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
