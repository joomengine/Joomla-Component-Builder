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
 * back the statement the view runs to include it.
 * 
 * @since  6.1.7
 */
final class JavaScriptFile
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
	 * Write the script file of a view and give back the statement that includes it.
	 *
	 * @param   array   $view    The view being built.
	 * @param   string  $TARGET  The upper case build target of the view.
	 *
	 * @return  string  The statement, or nothing when the view has no script file.
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
			if ('site' === $this->config->build_target)
			{
				$path = '/components/com_' . $this->config->component_code_name
					. '/assets/js/' . $view['settings']->code . '.js';
			}
			else
			{
				$path = '/administrator/components/com_'
					. $this->config->component_code_name . '/assets/js/'
					. $view['settings']->code . '.js';
			}
			// add script to file
			$this->contentmulti->set($view['settings']->code . '|' . $TARGET
				. '_JAVASCRIPT_FILE', $this->placeholder->update_(
				$view['settings']->javascript_file
			));

			// add script to view
			return PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Add View JavaScript File" . PHP_EOL . Indent::_(2)
				. $this->includehelper->get($path);
		}

		return '';
	}
}

