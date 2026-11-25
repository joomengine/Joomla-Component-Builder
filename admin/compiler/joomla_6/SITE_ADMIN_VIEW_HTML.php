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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Site\View\###View###;

###SITE_ADMIN_VIEW_HTML_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;###LICENSE_LOCKED_DEFINED###

/**
 * ###View### Html View class
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The app class
	 *
	 * @var    Joomla___a6ee04f5_33c7_4a9b_aa6d_6a03f3715a88___Power
	 * @since  5.2.1
	 */
	public Joomla___a6ee04f5_33c7_4a9b_aa6d_6a03f3715a88___Power $app;

	/**
	 * The input class
	 *
	 * @var    Joomla___59106b64_dd51_4280_be0a_1b9b9ebb7161___Power
	 * @since  5.2.1
	 */
	public Joomla___59106b64_dd51_4280_be0a_1b9b9ebb7161___Power $input;

	/**
	 * The params registry
	 *
	 * @var    Joomla___a87c432d_b5b4_428e_b7ff_14b51664c624___Power
	 * @since  5.2.1
	 */
	public Joomla___a87c432d_b5b4_428e_b7ff_14b51664c624___Power $params;

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
	 * @var    Joomla___47ee1f2b_9902_4f26_a856_04930ac9ddc3___Power
	 * @since  3.10.11
	 */
	public Joomla___47ee1f2b_9902_4f26_a856_04930ac9ddc3___Power $toolbar;

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
	 * The origin referral view item id
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
			$config['option'] = 'com_###component###';
		}

		parent::__construct($config);

		// get the application
		$this->app ??= Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		// get input
		$this->input ??= method_exists($this->app, 'getInput') ? $this->app->getInput() : $this->app->input;
		// get component params
		$this->params ??= method_exists($this->app, 'getParams')
			? $this->app->getParams()
			: Joomla___aeb8e463_291f_4445_9ac4_34b637c12dbd___Power::getParams('com_###component###');

		$this->useCoreUI = true;
		$this->isModal = false; // no modal support yet
	}

	/**
	 * ###View### view display method
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		// Load module values
		$model = $this->getModel();
		$model->setUseExceptions(true);
		$this->form ??= $model->getForm();
		$this->item = $model->getItem();
		$this->state = $model->getState();
		$this->styles = $model->getStyles() ?? [];
		$this->scripts = $model->getScripts() ?? [];

		// get the permitted actions the current user can do.
		$this->canDo = Super___7d95ce74_53dc_4672_bd8a_3b71cdacabea___Power::get('###view###', $this->item);

		// Set the return
		$this->setReturn();###LINKEDVIEWITEMS###

		// Set the toolbar
		$this->addToolBar();


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
	{###INITTOOLBAR###

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
		$isNew = ($this->item->id < 1);
		$this->setDocumentTitle(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_($isNew ? 'COM_###COMPONENT###_###VIEW###_NEW' : 'COM_###COMPONENT###_###VIEW###_EDIT'));
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
		}###AJAXTOKE######LINKEDVIEWTABLESCRIPTS###
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
		}###DOCUMENT_CUSTOM_PHP###
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

		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::sanitize($var, $this->_charset ?? 'UTF-8', $shorten, $length);
	}
}
