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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\View\###Component###;

###DASH_VIEW_HTML_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### View class
 *
 * @since  1.6
 */
#[\AllowDynamicProperties]
class HtmlView extends BaseHtmlView
{
	/**
	 * @var array<string> List of icon identifiers to render in the dashboard view.
	 * @since 1.6
	 */
	public array $icons = [];

	/**
	 * @var array<string> List of CSS file URLs to be added to the page.
	 * @since 4.3
	 */
	public array $styles = [];

	/**
	 * @var array<string> List of JavaScript file URLs to be included on the page.
	 * @since 4.3
	 */
	public array $scripts = [];

	/**
	 * @var array<int, object> List of contributor objects fetched via the helper.
	 * @since 1.6
	 */
	public array $contributors = [];

	/**
	 * @var object|null The manifest metadata of the component as returned by `ComponentbuilderHelper::manifest()`.
	 * @since 1.6
	 */
	public $manifest = null;

	/**
	 * @var string|null Markdown content of the component's wiki page.
	 * @since 1.6
	 */
	public ?string $wiki = null;

	/**
	 * @var string|null The rendered or raw README markdown of the component.
	 * @since 1.6
	 */
	public ?string $readme = null;

	/**
	 * @var string|null The current version of the component.
	 * @since 1.6
	 */
	public ?string $version = null;

	/**
	 * @var string|null Help URL for the component dashboard view, if available.
	 * @since 1.6
	 */
	public ?string $help_url = null;

	/**
	 * View display method
	 *
	 * @return void
	 * @throws \Exception
	 * @since   1.6
	 */
	function display($tpl = null): void
	{
		// Assign data to the view
		$model = $this->getModel();
		$model->setUseExceptions(true);
		$this->icons          = $model->getIcons();
		$this->styles         = $model->getStyles();
		$this->scripts        = $model->getScripts();
		$this->contributors   = ###Component###Helper::getContributors();

		// get the manifest details of the component
		$this->manifest = ###Component###Helper::manifest();###DASH_GET_CUSTOM_DATA###

		// Set the toolbar
		$this->addToolBar();


		// Set the html view document stuff
		$this->_prepareDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		$canDo = ###Component###Helper::getActions('###component###');
		Joomla___0c1a176a_304f_433a_8233_37d01ff87815___Power::title(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_DASHBOARD'), 'grid-2');

		// set help url for this view if found
		$this->help_url = ###Component###Helper::getHelpUrl('###component###');
		if (Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($this->help_url))
		{
			Joomla___0c1a176a_304f_433a_8233_37d01ff87815___Power::help('COM_###COMPONENT###_HELP_MANAGER', false, $this->help_url);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			Joomla___0c1a176a_304f_433a_8233_37d01ff87815___Power::preferences('com_###component###');
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
		// set page title
		$this->getDocument()->setTitle(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_DASHBOARD'));
		/** \Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = $this->getDocument()->getWebAssetManager();
		// Register the inline script with properly encoded JSON
		$wa->addInlineScript(
			'var manifest = ' . json_encode($this->manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';'
		);
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
		}
	}
}
