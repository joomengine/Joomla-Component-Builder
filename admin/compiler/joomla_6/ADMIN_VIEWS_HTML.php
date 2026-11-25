<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\View\###Views###;

###ADMIN_VIEWS_HTML_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;###LICENSE_LOCKED_DEFINED###

/**
 * ###Component### Html View class for the ###Views###
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
	 * @var    Joomla___effdaf6d_2275_425d_9f52_d4952e564d34___Power
	 * @since  3.10.11
	 */
	public Joomla___effdaf6d_2275_425d_9f52_d4952e564d34___Power $user;

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
	 * ###Views### view display method
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
		$model->setUseExceptions(true);
		$this->items = $model->getItems();
		$this->pagination = $model->getPagination();
		$this->state = $model->getState();
		$this->isEmptyState = $model->getIsEmptyState();
		$this->styles = $model->getStyles();
		$this->scripts = $model->getScripts();
		$this->user ??= $this->getCurrentUser();###ADMIN_DIPLAY_METHOD###
		$this->saveOrder = $this->listOrder == 'a.ordering';
		// set the return here value
		$this->return_here = urlencode(base64_encode((string) Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::getInstance()));
		// get the permitted actions the current user can do
		$this->canDo = Super___7d95ce74_53dc_4672_bd8a_3b71cdacabea___Power::get('###view###');###JVIEWLISTCANDO###

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
		###ADDTOOLBAR###
	}

	/**
	 * Prepare some document related stuff.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function _prepareDocument(): void
	{###JQUERY###
		$wa = $this->getDocument()->getWebAssetManager();
		// add styles
		$jcbStyleDependencies = $jcbStyleDependencies ?? [];
		foreach ($this->styles as $style)
		{
			$style = str_starts_with($style, '//') ? $style : ltrim($style, '/');
			$assetName = 'jcb.style.' . hash('sha256', $style);
			if (!$wa->assetExists('style', $assetName))
			{
				$assetUri = preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $style)
					? $style : Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root() . $style;
				$wa->registerStyle($assetName, $assetUri, ['version' => 'auto'], [], $jcbStyleDependencies);
			}
			$wa->useStyle($assetName);
			$jcbStyleDependencies = array_values(array_unique([...$jcbStyleDependencies, $assetName]));
		}
		// add scripts
		$jcbScriptDependencies = $jcbScriptDependencies ?? ['core'];
		foreach ($this->scripts as $script)
		{
			$script = str_starts_with($script, '//') ? $script : ltrim($script, '/');
			$assetName = 'jcb.script.' . hash('sha256', $script);
			if (!$wa->assetExists('script', $assetName))
			{
				$assetUri = preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $script)
					? $script : Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root() . $script;
				$wa->registerScript($assetName, $assetUri, ['version' => 'auto'], [], $jcbScriptDependencies);
			}
			$wa->useScript($assetName);
			$jcbScriptDependencies = array_values(array_unique([...$jcbScriptDependencies, $assetName]));
		}###ADMIN_ADD_JAVASCRIPT_FILE###
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

		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::sanitize($var, $this->_charset ?? 'UTF-8', $shorten, $length);
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
		###SORTFIELDS###
	}###FILTERFUNCTIONS###
}
