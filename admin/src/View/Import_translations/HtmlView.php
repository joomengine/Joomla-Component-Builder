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
namespace VDM\Component\Componentbuilder\Administrator\View\Import_translations;

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
 * Componentbuilder Html View class for the Import_translations
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
		$this->canDo = Actions::get('import_translations');

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

	// These are subform layouts used in JCB
	// LayoutHelper::render('sectionjcbjsix', [?]); // added to ensure the layout are loaded
	// LayoutHelper::render('repeatablejcbjsix', [?]); // added to ensure the layout are loaded
	// LayoutHelper::render('jcbbuildersuccessmessage', [?]); // added to ensure the layout are loaded

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
			$title = Text::_('COM_COMPONENTBUILDER_IMPORT_TRANSLATIONS');
		}

		// add title to the page
		ToolbarHelper::title($title, 'upload');
		// add cpanel button
		$this->getDocument()->getToolbar()->standardButton('grid-2', 'COM_COMPONENTBUILDER_DASH', 'import_translations.dashboard')->listCheck(false);
		if ($this->canDo->get('import_translations.language_translations'))
		{
			// add Language Translations button.
			$this->getDocument()->getToolbar()->standardButton('comments-2 custom-button-backtolanguagetranslations', 'COM_COMPONENTBUILDER_LANGUAGE_TRANSLATIONS', 'import_translations.backToLanguageTranslations')->listCheck(false);
		}
		if ($this->canDo->get('import_translations.example'))
		{
			// add Example button.
			$this->getDocument()->getToolbar()->standardButton('download custom-button-getlanguagetranslationsexample', 'COM_COMPONENTBUILDER_EXAMPLE', 'import_translations.getLanguageTranslationsExample')->listCheck(false);
		}
		// set help url for this view if found
		$this->help_url = ComponentbuilderHelper::getHelpUrl('import_translations');
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
if (!$jcbAssetManager->assetExists('style', 'jcb.style.d94cea8fcc25784cf34348940ba27c48ecd071018a1f16190adee122f606f368'))
{
	$jcbAssetManager->registerStyle('jcb.style.d94cea8fcc25784cf34348940ba27c48ecd071018a1f16190adee122f606f368', Uri::root() . 'media/com_componentbuilder/uikit-v3/css/uikit.min.css', ['version' => 'auto'], [], $jcbStyleDependencies ?? []);
}
$jcbAssetManager->useStyle('jcb.style.d94cea8fcc25784cf34348940ba27c48ecd071018a1f16190adee122f606f368');
$jcbStyleDependencies = array_values(array_unique(array_merge($jcbStyleDependencies ?? [], ['jcb.style.d94cea8fcc25784cf34348940ba27c48ecd071018a1f16190adee122f606f368'])));
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('script', 'jcb.script.4f556f00229ce771c899fcb5d64497c448e51f4ece7a6024c1d3c495ae876d6e'))
{
	$jcbAssetManager->registerScript('jcb.script.4f556f00229ce771c899fcb5d64497c448e51f4ece7a6024c1d3c495ae876d6e', Uri::root() . 'media/com_componentbuilder/uikit-v3/js/uikit.min.js', ['version' => 'auto'], [], $jcbScriptDependencies ?? []);
}
$jcbAssetManager->useScript('jcb.script.4f556f00229ce771c899fcb5d64497c448e51f4ece7a6024c1d3c495ae876d6e');
$jcbScriptDependencies = array_values(array_unique(array_merge($jcbScriptDependencies ?? [], ['jcb.script.4f556f00229ce771c899fcb5d64497c448e51f4ece7a6024c1d3c495ae876d6e'])));
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('script', 'jcb.script.b6425190aa9f1cfedd1b898ade03d7f748878e8c27ba1fbb8d05ab9a9b57ec0f'))
{
	$jcbAssetManager->registerScript('jcb.script.b6425190aa9f1cfedd1b898ade03d7f748878e8c27ba1fbb8d05ab9a9b57ec0f', Uri::root() . 'media/com_componentbuilder/uikit-v3/js/uikit-icons.min.js', ['version' => 'auto'], [], $jcbScriptDependencies ?? []);
}
$jcbAssetManager->useScript('jcb.script.b6425190aa9f1cfedd1b898ade03d7f748878e8c27ba1fbb8d05ab9a9b57ec0f');
$jcbScriptDependencies = array_values(array_unique(array_merge($jcbScriptDependencies ?? [], ['jcb.script.b6425190aa9f1cfedd1b898ade03d7f748878e8c27ba1fbb8d05ab9a9b57ec0f'])));
		$jcbAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();
if (!$jcbAssetManager->assetExists('script', 'jcb.script.eddf5574101290c7ec01586124d5d2b8efaff2af8e87f793bb23482609a6b63b'))
{
	$jcbAssetManager->registerScript('jcb.script.eddf5574101290c7ec01586124d5d2b8efaff2af8e87f793bb23482609a6b63b', Uri::root() . 'media/com_componentbuilder/uikit-v3/js/Uploader.min.js', ['version' => 'auto'], [], $jcbScriptDependencies ?? []);
}
$jcbAssetManager->useScript('jcb.script.eddf5574101290c7ec01586124d5d2b8efaff2af8e87f793bb23482609a6b63b');
$jcbScriptDependencies = array_values(array_unique(array_merge($jcbScriptDependencies ?? [], ['jcb.script.eddf5574101290c7ec01586124d5d2b8efaff2af8e87f793bb23482609a6b63b'])));
		 		// these comments are here so the layouts will be linked and loaded for the ajax (don't remove it)
		
				// change this to the layout of your custom importer columns display
				// LayoutHelper::render('translationimportercolumnsdisplayjsix', [?]);
		
				// change this to the layout of your custom importer easy mapping
				// LayoutHelper::render('translationimportereasymappingjsix', [?]);
		
				// add the libs for subform (since not adding it via xml but ajax)
				$this->getDocument()
					->getWebAssetManager()
					->useScript('webcomponent.field-subform')
					->usePreset('choicesjs')
					->useScript('webcomponent.field-fancy-select');
				$this->getDocument()
					->getWebAssetManager()
					->addInlineStyle('.subform-table-sublayout-section .controls { margin-left: 0px }')
					->addInlineStyle('.subform-table-sublayout-section .table-responsive { overflow-x: visible }');

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
