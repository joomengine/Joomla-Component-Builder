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
namespace VDM\Component\Componentbuilder\Administrator\View\Joomla_plugin;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Document\Document;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Joomla\Componentbuilder\Utilities\Permitted\Actions;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Joomla_plugin Html View class
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
	 * The item from the model
	 *
	 * @var    mixed
	 * @since  3.10.11
	 */
	public mixed $item;

	/**
	 * The state object
	 *
	 * @var    mixed
	 * @since  3.10.11
	 */
	public mixed $state;

	/**
	 * The form from the model
	 *
	 * @var    mixed
	 * @since  3.10.11
	 */
	public mixed $form;

	/**
	 * The toolbar object
	 *
	 * @var    Toolbar
	 * @since  3.10.11
	 */
	public Toolbar $toolbar;

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
	 * The origin referral view name
	 *
	 * @var    string|null
	 * @since  3.10.11
	 */
	public ?string $ref;

	/**
	 * The origin referral item id
	 *
	 * @var    int|null
	 * @since  3.10.11
	 */
	public ?int $refid;

	/**
	 * The referral url suffix values
	 *
	 * @var    string
	 * @since  3.10.11
	 */
	public string $referral;

	/**
	 * The modal state
	 *
	 * @var    bool
	 * @since  5.2.1
	 */
	public bool $isModal;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   6.0.0
	 */
	public function __construct(array $config)
	{
		if (empty($config['option']))
		{
			$config['option'] = 'com_componentbuilder';
		}

		parent::__construct($config);

		// get application
		$this->app ??= Factory::getApplication();
		// get input
		$this->input ??= method_exists($this->app, 'getInput') ? $this->app->getInput() : $this->app->input;
		// set params
		$this->params ??= method_exists($this->app, 'getParams')
			? $this->app->getParams()
			: ComponentHelper::getParams('com_componentbuilder');

		$this->useCoreUI = true;
	}

	/**
	 * Joomla_plugin view display method
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 * @throws \Exception
	 * @since  1.6
	 */
	public function display($tpl = null): void
	{
		// Load module values
		$model = $this->getModel();
		$this->form ??= $model->getForm();
		$this->item = $model->getItem();
		$this->styles = $model->getStyles();
		$this->scripts = $model->getScripts();
		$this->state = $model->getState();

		// get the permitted actions the current user can do.
		$this->canDo = Actions::get('joomla_plugin', $this->item);

		// Set the return
		$this->setReturn();

		// Set the toolbar
		if ($this->getLayout() !== 'modal')
		{
			$this->isModal = false;
			$this->addToolbar();
		}
		else
		{
			$this->isModal = true;
			$this->addModalToolbar();
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors), 500);
		}

		// Set the html view document stuff
		$this->_prepareDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Set the redirection details.
	 *
	 * @return  void
	 * @since   5.1.4
	 */
	protected function setReturn(): void
	{
		// This [ref,refid] will be removed in JCB.v7, use only [return]
		$this->ref = $this->input->getWord('ref', null);
		$this->refid = $this->input->getInt('refid', null);
		$this->referral = '';
		if (!empty($this->refid) && !empty($this->ref))
		{
			// return to the item that referred to this item
			$this->referral = '&ref=' . (string) $this->ref . '&refid=' . (int) $this->refid;
		}
		elseif (!empty($this->ref))
		{
			// return to the list view that referred to this item
			$this->referral = '&ref=' . (string) $this->ref;
		}

		$return = $this->input->getBase64('return', null);
		if (!empty($return))
		{
			$this->referral .= '&return=' . (string) $return;
		}
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
		$user = $this->getCurrentUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;

		ToolbarHelper::title( Text::_($isNew ? 'COM_COMPONENTBUILDER_JOOMLA_PLUGIN_NEW' : 'COM_COMPONENTBUILDER_JOOMLA_PLUGIN_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (StringHelper::check($this->referral))
		{
			if ($this->canDo->get('joomla_plugin.create') && $isNew)
			{
				// We can create the record.
				ToolbarHelper::save('joomla_plugin.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('joomla_plugin.edit'))
			{
				// We can save the record.
				ToolbarHelper::save('joomla_plugin.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not create but cancel.
				ToolbarHelper::cancel('joomla_plugin.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				ToolbarHelper::cancel('joomla_plugin.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('joomla_plugin.create'))
				{
					ToolbarHelper::apply('joomla_plugin.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('joomla_plugin.save', 'JTOOLBAR_SAVE');
					ToolbarHelper::custom('joomla_plugin.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				ToolbarHelper::cancel('joomla_plugin.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('joomla_plugin.edit'))
				{
					// We can save the new record
					ToolbarHelper::apply('joomla_plugin.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('joomla_plugin.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see
					// if we can return to make a new one.
					if ($this->canDo->get('joomla_plugin.create'))
					{
						ToolbarHelper::custom('joomla_plugin.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					}
				}
				$canVersion = ($this->canDo->get('core.version') && $this->canDo->get('joomla_plugin.version'));
				if ($this->state->params->get('save_history', 1) && $this->canDo->get('joomla_plugin.edit') && $canVersion)
				{
					ToolbarHelper::versions('com_componentbuilder.joomla_plugin', $this->item->id);
				}
				if ($this->canDo->get('joomla_plugin.create'))
				{
					ToolbarHelper::custom('joomla_plugin.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
				if ($this->canDo->get('joomla_plugin.reset'))
				{
					// add Reset button.
					ToolbarHelper::custom('joomla_plugin.resetPowers', 'joomla custom-button-resetpowers', '', 'COM_COMPONENTBUILDER_RESET', false);
				}
				if ($this->canDo->get('joomla_plugin.push'))
				{
					// add Push button.
					ToolbarHelper::custom('joomla_plugin.pushPowers', 'share custom-button-pushpowers', '', 'COM_COMPONENTBUILDER_PUSH', false);
				}
				ToolbarHelper::cancel('joomla_plugin.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		ToolbarHelper::divider();
		ToolbarHelper::inlinehelp();
		// set help url for this view if found
		$this->help_url = ComponentbuilderHelper::getHelpUrl('joomla_plugin');
		if (StringHelper::check($this->help_url))
		{
			ToolbarHelper::help('COM_COMPONENTBUILDER_HELP_MANAGER', false, $this->help_url);
		}
	}

	/**
	 * Add the modal toolbar.
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   5.0.0
	 */
	protected function addModalToolbar()
	{
		$this->input->set('hidemainmenu', true);
		$user = $this->getCurrentUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;

		ToolbarHelper::title( Text::_($isNew ? 'COM_COMPONENTBUILDER_JOOMLA_PLUGIN_NEW' : 'COM_COMPONENTBUILDER_JOOMLA_PLUGIN_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (StringHelper::check($this->referral))
		{
			if ($this->canDo->get('joomla_plugin.create') && $isNew)
			{
				// We can create the record.
				ToolbarHelper::save('joomla_plugin.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('joomla_plugin.edit'))
			{
				// We can save the record.
				ToolbarHelper::save('joomla_plugin.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not create but cancel.
				ToolbarHelper::cancel('joomla_plugin.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				ToolbarHelper::cancel('joomla_plugin.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('joomla_plugin.create'))
				{
					ToolbarHelper::apply('joomla_plugin.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('joomla_plugin.save', 'JTOOLBAR_SAVE');
					ToolbarHelper::custom('joomla_plugin.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				ToolbarHelper::cancel('joomla_plugin.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('joomla_plugin.edit'))
				{
					// We can save the new record
					ToolbarHelper::apply('joomla_plugin.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('joomla_plugin.save', 'JTOOLBAR_SAVE');
				}
				if ($this->canDo->get('joomla_plugin.reset'))
				{
					// add Reset button.
					ToolbarHelper::custom('joomla_plugin.resetPowers', 'joomla custom-button-resetpowers', '', 'COM_COMPONENTBUILDER_RESET', false);
				}
				if ($this->canDo->get('joomla_plugin.push'))
				{
					// add Push button.
					ToolbarHelper::custom('joomla_plugin.pushPowers', 'share custom-button-pushpowers', '', 'COM_COMPONENTBUILDER_PUSH', false);
				}
				ToolbarHelper::cancel('joomla_plugin.cancel', 'JTOOLBAR_CLOSE');
			}
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
		// Load jQuery
		Html::_('jquery.framework');
		$isNew = ($this->item->id < 1);
		// add styles
		foreach ($this->styles as $style)
		{
			Html::_('stylesheet', $style, ['version' => 'auto']);
		}
		// Add Ajax Token
		$this->getDocument()->getWebAssetManager()->addInlineScript("var token = '" . Session::getFormToken() . "';");
		// add scripts
		foreach ($this->scripts as $script)
		{
			Html::_('script', $script, ['version' => 'auto']);
		}

		// get Model
		$model = $this->getModel();
		// get the web asset manager :(
		$web = $this -> getDocument() -> getWebAssetManager();
		// add var key
		$web->addInlineScript("var vastDevMod = '" . $model->getVDM() . "';")
		// add return_here
		->addInlineScript("var return_here = '" . urlencode(base64_encode((string) Uri::getInstance())) . "';")
		// add joomla dialog
		->useScript('core')->useScript('joomla.dialog');
		// set some lang
		Text::script('COM_COMPONENTBUILDER_ALREADY_SELECTED_TRY_ANOTHER');
		Text::script('COM_COMPONENTBUILDER_TYPE_OR_SELECT_SOME_OPTIONS');
		Text::script('COM_COMPONENTBUILDER_NO_RESULTS_MATCH');
		Text::script('COM_COMPONENTBUILDER_SELECT_A_PROPERTY');
		Text::script('COM_COMPONENTBUILDER_NO_DESCRIPTION_FOUND');
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
	public function sanitize($var, bool $shorten = true, int $length = 30)
	{
		if (!is_string($var))
		{
			return $var;
		}

		return StringHelper::sanitize($var, $this->_charset ?? 'UTF-8', $shorten, $length);
	}
}
