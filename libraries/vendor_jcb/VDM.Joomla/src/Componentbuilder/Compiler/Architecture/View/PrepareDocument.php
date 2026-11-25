<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    19th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\View;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomButtons;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\DocumentInlineAssetsInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\DocumentMetadataInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\LibrariesLoaderInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\UikitLoaderInterface;
use VDM\Joomla\Utilities\StringHelper;


/**
 * View Prepare Document Class.
 * 
 * Fills in everything the prepare document method of a view is built from: the
 * assets it loads, the metadata it sets, whatever it was given to run, and the
 * buttons and modules it carries.
 * 
 * The language target follows the build target while this runs, and is put
 * back the way it was found, since a view of one target may be prepared while
 * the other is being built.
 * 
 * @since  6.1.7
 */
final class PrepareDocument
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Content Multi Builder Class.
	 *
	 * @var   ContentMulti
	 * @since 6.1.7
	 */
	protected ContentMulti $contentmulti;

	/**
	 * The Libraries Loader Class.
	 *
	 * @var   LibrariesLoaderInterface
	 * @since 6.1.7
	 */
	protected LibrariesLoaderInterface $libraries;

	/**
	 * The Uikit Loader Class.
	 *
	 * @var   UikitLoaderInterface
	 * @since 6.1.7
	 */
	protected UikitLoaderInterface $uikit;

	/**
	 * The Google Chart Loader Class.
	 *
	 * @var   GoogleChartLoader
	 * @since 6.1.7
	 */
	protected GoogleChartLoader $googlechart;

	/**
	 * The Footable Scripts Loader Class.
	 *
	 * @var   FootableScriptsLoader
	 * @since 6.1.7
	 */
	protected FootableScriptsLoader $footable;

	/**
	 * The Document Metadata Class.
	 *
	 * @var   DocumentMetadataInterface
	 * @since 6.1.7
	 */
	protected DocumentMetadataInterface $metadata;

	/**
	 * The Document Custom PHP Class.
	 *
	 * @var   DocumentCustomPHP
	 * @since 6.1.7
	 */
	protected DocumentCustomPHP $customphp;

	/**
	 * The Document Inline Assets Class.
	 *
	 * @var   DocumentInlineAssetsInterface
	 * @since 6.1.7
	 */
	protected DocumentInlineAssetsInterface $inline;

	/**
	 * The Custom CSS Class.
	 *
	 * @var   CustomCSS
	 * @since 6.1.7
	 */
	protected CustomCSS $customcss;

	/**
	 * The Custom Buttons Class.
	 *
	 * @var   CustomButtons
	 * @since 6.1.7
	 */
	protected CustomButtons $buttons;

	/**
	 * The Get Modules Class.
	 *
	 * @var   GetModules
	 * @since 6.1.7
	 */
	protected GetModules $modules;

	/**
	 * The JavaScript File Class.
	 *
	 * @var   JavaScriptFile
	 * @since 6.1.7
	 */
	protected JavaScriptFile $javascript;

	/**
	 * Constructor.
	 *
	 * @param Config                         $config        The Config Class.
	 * @param ContentMulti                   $contentmulti  The Content Multi Builder Class.
	 * @param LibrariesLoaderInterface       $libraries     The Libraries Loader Class.
	 * @param UikitLoaderInterface           $uikit         The Uikit Loader Class.
	 * @param GoogleChartLoader              $googlechart   The Google Chart Loader Class.
	 * @param FootableScriptsLoader          $footable      The Footable Scripts Loader Class.
	 * @param DocumentMetadataInterface      $metadata      The Document Metadata Class.
	 * @param DocumentCustomPHP              $customphp     The Document Custom PHP Class.
	 * @param DocumentInlineAssetsInterface  $inline        The Document Inline Assets Class.
	 * @param CustomCSS                      $customcss     The Custom CSS Class.
	 * @param CustomButtons                  $buttons       The Custom Buttons Class.
	 * @param GetModules                     $modules       The Get Modules Class.
	 * @param JavaScriptFile                 $javascript    The JavaScript File Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		ContentMulti $contentmulti,
		LibrariesLoaderInterface $libraries,
		UikitLoaderInterface $uikit,
		GoogleChartLoader $googlechart,
		FootableScriptsLoader $footable,
		DocumentMetadataInterface $metadata,
		DocumentCustomPHP $customphp,
		DocumentInlineAssetsInterface $inline,
		CustomCSS $customcss,
		CustomButtons $buttons,
		GetModules $modules,
		JavaScriptFile $javascript)
	{
		$this->config = $config;
		$this->contentmulti = $contentmulti;
		$this->libraries = $libraries;
		$this->uikit = $uikit;
		$this->googlechart = $googlechart;
		$this->footable = $footable;
		$this->metadata = $metadata;
		$this->customphp = $customphp;
		$this->inline = $inline;
		$this->customcss = $customcss;
		$this->buttons = $buttons;
		$this->modules = $modules;
		$this->javascript = $javascript;
	}

	/**
	 * Fill in the prepare document method of a view.
	 *
	 * @param   array  $view  The view being built.
	 *
	 * @return  void
	 *
	 * @since   6.1.7
	 */
	public function set(array &$view): void
	{
		// fix just incase we missed it somewhere
		$tmp = $this->config->lang_target;
		if ('site' === $this->config->build_target)
		{
			$this->config->lang_target = 'site';
		}
		else
		{
			$this->config->lang_target = 'admin';
		}

		// ensure correct target is set
		$TARGET = StringHelper::safe($this->config->build_target, 'U');

		// set libraries $TARGET.'_LIBRARIES_LOADER
		$this->contentmulti->set($view['settings']->code . '|' . $TARGET . '_LIBRARIES_LOADER',
			$this->libraries->get($view)
		);

		// set uikit $TARGET.'_UIKIT_LOADER
		$this->contentmulti->set($view['settings']->code . '|' . $TARGET . '_UIKIT_LOADER',
			$this->uikit->get($view)
		);

		// set Google Charts $TARGET.'_GOOGLECHART_LOADER
		$this->contentmulti->set($view['settings']->code . '|' .$TARGET . '_GOOGLECHART_LOADER',
			$this->googlechart->get($view)
		);

		// set Footable FOOTABLE_LOADER
		$this->contentmulti->set($view['settings']->code . '|' . $TARGET . '_FOOTABLE_LOADER',
			$this->footable->get($view)
		);

		// set metadata DOCUMENT_METADATA
		$this->contentmulti->set($view['settings']->code . '|' . $TARGET . '_DOCUMENT_METADATA',
			$this->metadata->get($view)
		);

		// set custom php scripting DOCUMENT_CUSTOM_PHP
		$this->contentmulti->set($view['settings']->code . '|' . $TARGET . '_DOCUMENT_CUSTOM_PHP',
			$this->customphp->get($view)
		);

		// set custom css DOCUMENT_CUSTOM_CSS
		$this->contentmulti->set($view['settings']->code . '|' .$TARGET . '_DOCUMENT_CUSTOM_CSS',
			$this->inline->css($view)
		);

		// set custom javascript DOCUMENT_CUSTOM_JS
		$this->contentmulti->set($view['settings']->code . '|' . $TARGET . '_DOCUMENT_CUSTOM_JS',
			$this->inline->js($view)
		);

		// set custom css file VIEWCSS
		$this->contentmulti->set($view['settings']->code . '|' . $TARGET . '_VIEWCSS',
			$this->customcss->get($view)
		);

		// incase no buttons are found
		$this->contentmulti->set($view['settings']->code . '|SITE_JAVASCRIPT_FOR_BUTTONS', '');

		// set the custom buttons CUSTOM_BUTTONS
		$this->contentmulti->set($view['settings']->code . '|' . $TARGET . '_CUSTOM_BUTTONS',
			$this->buttons->get($view)
		);

		// see if we should add get modules to the view.html
		$this->contentmulti->set($view['settings']->code . '|' . $TARGET . '_GET_MODULE',
			$this->modules->get($view, $TARGET)
		);

		// set a JavaScript file if needed
		try
		{
			$this->javascript->set($view, $TARGET);
		}
		finally
		{
			$this->config->lang_target = $tmp;
		}
	}
}

