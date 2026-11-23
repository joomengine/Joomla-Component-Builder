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
namespace VDM\Component\Componentbuilder\Administrator\View\Components_routers;

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
use Joomla\CMS\Toolbar\Button\DropdownButton;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Html View class for the Components_routers
 *
 * @since  1.6
 */
#[\AllowDynamicProperties]
class HtmlView extends BaseHtmlView
{
	/**
	 * The items from the model
	 *
	 * @var    mixed
	 * @since  3.10.11
	 */
	public mixed $items;

	/**
	 * The state object
	 *
	 * @var    mixed
	 * @since  3.10.11
	 */
	public mixed $state;

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
	 * The return here base64 url
	 *
	 * @var    string
	 * @since  3.10.11
	 */
	public string $return_here;

	/**
	 * The title key used in modal
	 *
	 * @var    string
	 * @since  5.2.1
	 */
	public string $modalTitleKey;

	/**
	 * The modal state
	 *
	 * @var    bool
	 * @since  5.2.1
	 */
	public bool $isModal;

	/**
	 * The empty state
	 *
	 * @var    bool
	 * @since  5.2.1
	 */
	protected bool $isEmptyState;

	/**
	 * The user object.
	 *
	 * @var    User
	 * @since  3.10.11
	 */
	public User $user;

	/**
	 * The Can Edit permission
	 *
	 * @var    ?bool
	 * @since  5.2.1
	 */
	public ?bool $canEdit = null;

	/**
	 * The Can Edit State permission
	 *
	 * @var    ?bool
	 * @since  5.2.1
	 */
	public ?bool $canState = null;

	/**
	 * The Can Create permission
	 *
	 * @var    ?bool
	 * @since  5.2.1
	 */
	public ?bool $canCreate = null;

	/**
	 * The Can Delete permission
	 *
	 * @var    ?bool
	 * @since  5.2.1
	 */
	public ?bool $canDelete = null;

	/**
	 * The Can Batch permission
	 *
	 * @var    ?bool
	 * @since  5.2.1
	 */
	public ?bool $canBatch = null;

	/**
	 * Components_routers view display method
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
		$this->items = $model->getItems();
		$this->pagination = $model->getPagination();
		$this->state = $model->getState();
		$this->isEmptyState = $model->getIsEmptyState();
		$this->styles = $model->getStyles();
		$this->scripts = $model->getScripts();
		$this->user ??= $this->getCurrentUser();
		// Load the filter form from xml for searchtools.
		$this->filterForm = $model->getFilterForm();
		// Load the active filters for searchtools.
		$this->activeFilters = $model->getActiveFilters();
		// Add the list ordering clause.
		$this->listOrder = $this->sanitize($this->state->get('list.ordering', 'a.id'));
		$this->listDirn = $this->sanitize($this->state->get('list.direction', 'DESC'));
		$this->saveOrder = $this->listOrder == 'a.ordering';
		// set the return here value
		$this->return_here = urlencode(base64_encode((string) Uri::getInstance()));
		// get the permitted actions the current user can do
		$this->canDo = Actions::get('component_router');
		$this->canEdit = $this->canDo->get('component_router.edit');
		$this->canState = $this->canDo->get('component_router.edit.state');
		$this->canCreate = $this->canDo->get('component_router.create');
		$this->canDelete = $this->canDo->get('component_router.delete');
		$this->canBatch = ($this->canDo->get('component_router.batch') && $this->canDo->get('core.batch'));

		// If we don't have items we load the empty state
		if (is_array($this->items) && !count((array) $this->items) && $this->isEmptyState)
		{
			$this->setLayout('emptystate');
		}

		// We don't need toolbar in the modal window.
		$this->isModal = true;
		if ($this->getLayout() !== 'modal')
		{
			$this->isModal = false;
			$this->addToolbar();
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
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		ToolbarHelper::title(Text::_('COM_COMPONENTBUILDER_COMPONENTS_ROUTERS'), 'joomla');
		/** @var  Toolbar $toolbar */
		$toolbar = $this->getDocument()->getToolbar();
		if ($this->canCreate)
		{
			$toolbar->addNew('component_router.add');
		}

		// Only load if there are items
		if (!$this->isEmptyState)
		{
			/** @var  DropdownButton $dropdown */
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($this->canEdit)
			{
				$childBar->edit('component_router.edit')->listCheck(true);
			}

			if ($this->canState)
			{
				$childBar->publish('components_routers.publish')->listCheck(true);
				$childBar->unpublish('components_routers.unpublish')->listCheck(true);
				$childBar->archive('components_routers.archive')->listCheck(true);

				if ($this->canDo->get('core.admin'))
				{
					$childBar->checkin('components_routers.checkin')->listCheck(true);
				}

				if ($this->state->get('filter.published') == -2 && $this->canDelete)
				{
					$toolbar->delete('components_routers.delete', 'JTOOLBAR_DELETE_FROM_TRASH')
						->message('JGLOBAL_CONFIRM_DELETE')
						->listCheck(true);
				}
				elseif ($this->canDelete)
				{
					$childBar->trash('components_routers.trash')->listCheck(true);
				}
			}
		}

		// set help url for this view if found
		$this->help_url = ComponentbuilderHelper::getHelpUrl('components_routers');
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
		// Load jQuery
		Html::_('jquery.framework');
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
	public function sanitize($var, bool $shorten = true, int $length = 50)
	{
		if (!is_string($var))
		{
			return $var;
		}

		return StringHelper::sanitize($var, $this->_charset ?? 'UTF-8', $shorten, $length);
	}

	/**
	 * Get the modal data/title key
	 *
	 * @return  string  The key value.
	 * @since   5.2.1
	 */
	public function getModalTitleKey(): string
	{
		return $this->modalTitleKey ?? 'id';
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array   containing the field name to sort by as the key and display text as value
	 * @since   1.6
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
			'a.published' => Text::_('JSTATUS'),
			'a.id' => Text::_('JGRID_HEADING_ID')
		);
	}
}
