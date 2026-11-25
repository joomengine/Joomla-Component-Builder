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


use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Structure;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Library\IncludeHelper;
use VDM\Joomla\Componentbuilder\Compiler\Model\Createdate;
use VDM\Joomla\Componentbuilder\Compiler\Model\Modifieddate;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;
use VDM\Joomla\Utilities\StringHelper;


/**
 * View JavaScript File Class.
 * 
 * Writes the script file of a view into the component being built, and gives
 * back the target-specific fragment that includes it in the view.
 * 
 * @since  6.1.7
 */
class JavaScriptFile
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 6.1.7
	 */
	protected Placeholder $placeholder;

	/**
	 * The Content Multi Builder Class.
	 *
	 * @var   ContentMulti
	 * @since 6.1.7
	 */
	protected ContentMulti $contentmulti;

	/**
	 * The Structure Class.
	 *
	 * @var   Structure
	 * @since 6.1.7
	 */
	protected Structure $structure;

	/**
	 * The Create Date Class.
	 *
	 * @var   Createdate
	 * @since 6.1.7
	 */
	protected Createdate $createdate;

	/**
	 * The Modified Date Class.
	 *
	 * @var   Modifieddate
	 * @since 6.1.7
	 */
	protected Modifieddate $modifieddate;

	/**
	 * The Include Helper Class.
	 *
	 * @var   IncludeHelper
	 * @since 6.1.7
	 */
	protected IncludeHelper $includehelper;

	/**
	 * Constructor.
	 *
	 * @param Config         $config         The Config Class.
	 * @param Placeholder    $placeholder    The Placeholder Class.
	 * @param ContentMulti   $contentmulti   The Content Multi Builder Class.
	 * @param Structure      $structure      The Structure Class.
	 * @param Createdate     $createdate     The Create Date Class.
	 * @param Modifieddate   $modifieddate   The Modified Date Class.
	 * @param IncludeHelper  $includehelper  The Include Helper Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Placeholder $placeholder,
		ContentMulti $contentmulti,
		Structure $structure,
		Createdate $createdate,
		Modifieddate $modifieddate,
		IncludeHelper $includehelper)
	{
		$this->config = $config;
		$this->placeholder = $placeholder;
		$this->contentmulti = $contentmulti;
		$this->structure = $structure;
		$this->createdate = $createdate;
		$this->modifieddate = $modifieddate;
		$this->includehelper = $includehelper;
	}

	/**
	 * Write the script file and give back its target-specific document fragment.
	 *
	 * @param   array   $view    The view being built.
	 * @param   string  $TARGET  The upper case build target of the view.
	 *
	 * @return  string  The fragment, or nothing when the view has no script file.
	 *
	 * @since   6.1.7
	 */
	public function get(array &$view, string $TARGET): string
	{
		if ($view['settings']->add_javascript_file == 1
			&& StringHelper::check(
				$view['settings']->javascript_file
			))
		{
			// get dates
			$created  = $this->createdate->get($view);
			$modified = $this->modifieddate->get($view);
			// add file to view
			$target = array($this->config->build_target => $view['settings']->code);
			$config = array(Placefix::_h('CREATIONDATE')                          => $created,
				Placefix::_h('BUILDDATE') => $modified,
				Placefix::_h('VERSION')                          => $view['settings']->version);
			$this->structure->build($target, 'javascript_file', false, $config);
			// set path
			$path = $this->getPath($view['settings']->code);
			// add script to file
			$this->contentmulti->set($view['settings']->code . '|' . $TARGET
				. '_JAVASCRIPT_FILE', $this->placeholder->update_(
				$view['settings']->javascript_file
			));

			// add script to view
			return $this->render($path);
		}

		return '';
	}

	/**
	 * Render the legacy include without changing file creation.
	 *
	 * @param   string  $path  The installed asset path.
	 *
	 * @return  string  The include statement.
	 * @since   6.2.0
	 */
	protected function render(string $path): string
	{
		return PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__, __CLASS__
			) . " Add View JavaScript File" . PHP_EOL
			. $this->includehelper->getViewScript($path, 2);
	}

	/**
	 * Resolve the public script path for this view.
	 *
	 * @param   string  $name  The view code name.
	 *
	 * @return  string  The installed asset path.
	 * @since   6.2.0
	 */
	protected function getPath(string $name): string
	{
		if ('site' === $this->config->build_target)
		{
			return '/components/com_' . $this->config->component_code_name
				. '/assets/js/' . $name . '.js';
		}
		else
		{
			return '/administrator/components/com_'
				. $this->config->component_code_name . '/assets/js/'
				. $name . '.js';
		}
	}

	/**
	 * Build the optional file and retain the legacy include position.
	 *
	 * @param   array   $view    The current view definition.
	 * @param   string  $TARGET  The upper-case build target.
	 *
	 * @return  void
	 * @since   6.2.1
	 */
	public function set(array &$view, string $TARGET): void
	{
		$this->contentmulti->add($view['settings']->code . '|' . $TARGET . '_LIBRARIES_LOADER',
			$this->get($view, $TARGET), false
		);
	}
}

