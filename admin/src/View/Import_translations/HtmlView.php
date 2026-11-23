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

		// Check for errors.
		if (count($errors = $model->getErrors()))
		{
			throw new \Exception(implode(PHP_EOL, $errors), 500);
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
		ToolbarHelper::custom('import_translations.dashboard', 'grid-2', '', 'COM_COMPONENTBUILDER_DASH', false);
		if ($this->canDo->get('import_translations.language_translations'))
		{
			// add Language Translations button.
			ToolbarHelper::custom('import_translations.backToLanguageTranslations', 'comments-2 custom-button-backtolanguagetranslations', '', 'COM_COMPONENTBUILDER_LANGUAGE_TRANSLATIONS', false);
		}
		if ($this->canDo->get('import_translations.example'))
		{
			// add Example button.
			ToolbarHelper::custom('import_translations.getLanguageTranslationsExample', 'download custom-button-getlanguagetranslationsexample', '', 'COM_COMPONENTBUILDER_EXAMPLE', false);
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

		// Only load jQuery if needed. (default is true)
		if ($this->params->get('add_jquery_framework', 1) == 1)
		{
			Html::_('jquery.framework');
		}
		// Load the header checker class.
		// Initialize the header checker.
		$HeaderCheck = new HeaderCheck();

		// always load these files.
		Html::_('stylesheet', 'media/com_componentbuilder/uikit-v3/css/uikit.min.css', ['version' => 'auto']);
		Html::_('script', 'media/com_componentbuilder/uikit-v3/js/uikit.min.js', ['version' => 'auto']);
		Html::_('script', 'media/com_componentbuilder/uikit-v3/js/uikit-icons.min.js', ['version' => 'auto']);
		Html::_('script', 'media/com_componentbuilder/uikit-v3/js/Uploader.min.js', ['version' => 'auto']);
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
