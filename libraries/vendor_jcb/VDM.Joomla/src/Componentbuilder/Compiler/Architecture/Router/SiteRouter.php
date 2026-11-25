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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Router;


use VDM\Joomla\Componentbuilder\Compiler\Builder\Category;
use VDM\Joomla\Componentbuilder\Compiler\Builder\CategoryOtherName;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Structure;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;


/**
 * Site Router Class.
 * 
 * Builds the members of the generated site router that name the component's
 * own views: the case each view takes when a URL is parsed, the test that says
 * a view is one the router builds, and the map from a category extension to
 * the view that owns it.
 * 
 * Nothing here is decided by the Joomla version being compiled for, so there
 * is one class for every target.
 * 
 * @since  6.1.7
 */
final class SiteRouter
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
	 * The Structure Class.
	 *
	 * @var   Structure
	 * @since 6.1.7
	 */
	protected Structure $structure;

	/**
	 * The Category Builder Class.
	 *
	 * @var   Category
	 * @since 6.1.7
	 */
	protected Category $category;

	/**
	 * The Category Other Name Builder Class.
	 *
	 * @var   CategoryOtherName
	 * @since 6.1.7
	 */
	protected CategoryOtherName $categoryothername;

	/**
	 * The Content One Builder Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * The Content Multi Builder Class.
	 *
	 * @var   ContentMulti
	 * @since 6.1.7
	 */
	protected ContentMulti $contentmulti;

	/**
	 * Constructor.
	 *
	 * @param Config             $config             The Config Class.
	 * @param Placeholder        $placeholder        The Placeholder Class.
	 * @param Structure          $structure          The Structure Class.
	 * @param Category           $category           The Category Builder Class.
	 * @param CategoryOtherName  $categoryothername  The Category Other Name Builder Class.
	 * @param ContentOne         $contentone         The Content One Builder Class.
	 * @param ContentMulti       $contentmulti       The Content Multi Builder Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config, Placeholder $placeholder,
		Structure $structure, Category $category,
		CategoryOtherName $categoryothername, ContentOne $contentone,
		ContentMulti $contentmulti)
	{
		$this->config = $config;
		$this->placeholder = $placeholder;
		$this->structure = $structure;
		$this->category = $category;
		$this->categoryothername = $categoryothername;
		$this->contentone = $contentone;
		$this->contentmulti = $contentmulti;
	}

	/**
	 * Build one view's case in the router's parse switch.
	 *
	 * The view stays untyped and by reference, exactly as the caller passes it,
	 * and the view array is only read when the caller has one to give.
	 *
	 * @param   string  $view       The view code name.
	 * @param   mixed   $viewArray  The view being built, which the caller does not guarantee.
	 * @param   bool    $aliasView  Whether the view is reached by an alias.
	 * @param   bool    $idView     Whether the view is reached by an id.
	 *
	 * @return  string  The case, or nothing when the view takes none.
	 *
	 * @since   6.1.7
	 */
	public function parseSwitch(&$view, $viewArray = null, $aliasView = true,
		$idView = true)
	{
		// reset buckets
		$routerSwitch = [];
		$isCategory   = '';
		$viewTable    = false;
		if ($viewArray && ArrayHelper::check($viewArray)
			&& isset($viewArray['settings'])
			&& isset($viewArray['settings']->main_get))
		{
			// check if we have custom script for this router parse switch case
			if (isset($viewArray['settings']->main_get->add_php_router_parse)
				&& $viewArray['settings']->main_get->add_php_router_parse == 1
				&& isset($viewArray['settings']->main_get->php_router_parse)
				&& StringHelper::check(
					$viewArray['settings']->main_get->php_router_parse
				))
			{
				// load the custom script for the switch based on dynamic get
				$routerSwitch[] = PHP_EOL . Indent::_(3) . "case '" . $view
					. "':";
				$routerSwitch[] = $this->placeholder->update_(
					$viewArray['settings']->main_get->php_router_parse
				);
				$routerSwitch[] = Indent::_(4) . "break;";

				return implode(PHP_EOL, $routerSwitch);
			}
			// is this a catogory
			elseif (isset($viewArray['settings']->main_get->db_table_main)
				&& $viewArray['settings']->main_get->db_table_main
				=== 'categories')
			{
				$isCategory = ', true'; // TODO we will keep an eye on this....
			}
			// get the main table name
			elseif (isset($viewArray['settings']->main_get->main_get)
				&& ArrayHelper::check(
					$viewArray['settings']->main_get->main_get
				))
			{
				foreach ($viewArray['settings']->main_get->main_get as $get)
				{
					if (isset($get['as']) && $get['as'] === 'a')
					{
						if (isset($get['selection'])
							&& ArrayHelper::check(
								$get['selection']
							)
							&& isset($get['selection']['select_gets'])
							&& ArrayHelper::check(
								$get['selection']['select_gets']
							))
						{
							if (isset($get['selection']['table']))
							{
								$viewTable = str_replace(
									'#__' . $this->config->component_code_name . '_', '',
									(string) $get['selection']['table']
								);
							}
						}
						break;
					}
				}
			}
		}
		// add if tags is added, also for all front item views
		if ($aliasView)
		{
			$routerSwitch[] = PHP_EOL . Indent::_(3) . "case '" . $view . "':";
			$routerSwitch[] = Indent::_(4) . "\$vars['view'] = '" . $view
				. "';";
			$routerSwitch[] = Indent::_(4)
				. "if (is_numeric(\$segments[\$count-1]))";
			$routerSwitch[] = Indent::_(4) . "{";
			$routerSwitch[] = Indent::_(5)
				. "\$vars['id'] = (int) \$segments[\$count-1];";
			$routerSwitch[] = Indent::_(4) . "}";
			$routerSwitch[] = Indent::_(4) . "elseif (\$segments[\$count-1])";
			$routerSwitch[] = Indent::_(4) . "{";
			// we need to get from the table of this views main get the alias so we need the table name
			if ($viewTable)
			{
				$routerSwitch[] = Indent::_(5) . "\$id = \$this->getVar('"
					. $viewTable . "', \$segments[\$count-1], 'alias', 'id'"
					. $isCategory . ");";
			}
			else
			{
				$routerSwitch[] = Indent::_(5) . "\$id = \$this->getVar('"
					. $view . "', \$segments[\$count-1], 'alias', 'id'"
					. $isCategory . ");";
			}
			$routerSwitch[] = Indent::_(5) . "if(\$id)";
			$routerSwitch[] = Indent::_(5) . "{";
			$routerSwitch[] = Indent::_(6) . "\$vars['id'] = \$id;";
			$routerSwitch[] = Indent::_(5) . "}";
			$routerSwitch[] = Indent::_(4) . "}";
			$routerSwitch[] = Indent::_(4) . "break;";
		}
		elseif ($idView)
		{
			$routerSwitch[] = PHP_EOL . Indent::_(3) . "case '" . $view . "':";
			$routerSwitch[] = Indent::_(4) . "\$vars['view'] = '" . $view
				. "';";
			$routerSwitch[] = Indent::_(4)
				. "if (is_numeric(\$segments[\$count-1]))";
			$routerSwitch[] = Indent::_(4) . "{";
			$routerSwitch[] = Indent::_(5)
				. "\$vars['id'] = (int) \$segments[\$count-1];";
			$routerSwitch[] = Indent::_(4) . "}";
			$routerSwitch[] = Indent::_(4) . "break;";
		}
		else
		{
			$routerSwitch[] = PHP_EOL . Indent::_(3) . "case '" . $view . "':";
			$routerSwitch[] = Indent::_(4) . "\$vars['view'] = '" . $view
				. "';";
			$routerSwitch[] = Indent::_(4) . "break;";
		}

		return implode(PHP_EOL, $routerSwitch);
	}

	/**
	 * Build the test that says a view is one this router builds.
	 *
	 * @param   string  $view  The view code name.
	 *
	 * @return  string  The test, joined to whatever the router already tests.
	 *
	 * @since   6.1.7
	 */
	public function buildViews(string $view): string
	{
		if ($this->contentone->exists('ROUTER_BUILD_VIEWS')
			&& StringHelper::check(
				$this->contentone->get('ROUTER_BUILD_VIEWS')
			))
		{
			return " || \$view === '" . $view . "'";
		}
		else
		{
			return "\$view === '" . $view . "'";
		}
	}

	/**
	 * Build the map entry from a category extension to the view that owns it.
	 *
	 * A view that carries its own category also gets its category helper file
	 * built here, and the include that loads it added to the global helper.
	 *
	 * @param   string  $nameSingleCode  The single view code name.
	 * @param   string  $nameListCode    The list view code name.
	 *
	 * @return  string  The entry, or nothing when the view carries no category.
	 *
	 * @since   6.1.7
	 */
	public function categoryViews(string $nameSingleCode, string $nameListCode): string
	{
		if ($this->category->exists("{$nameListCode}.extension"))
		{
			// get the actual extension
			$_extension = $this->category->get("{$nameListCode}.extension");
			$_extension = explode('.', (string) $_extension);
			// set component name
			if (ArrayHelper::check($_extension))
			{
				$component = str_replace('com_', '', $_extension[0]);
			}
			else
			{
				$component = $this->config->component_code_name;
			}
			// check if category has another name
			$otherViews = $this->categoryothername->
				get($nameListCode . '.views', $nameListCode);
			$otherView  = $this->categoryothername->
				get($nameListCode . '.view', $nameSingleCode);
			// set the OtherView value
			$this->contentmulti->set('category' . $otherView . '|otherview', $otherView);
			// load the category helper details in not already loaded
			if (!$this->contentmulti->exists('category' . $otherView . '|view'))
			{
				// lets also set the category helper for this view
				$target = array('site' => 'category' . $otherView);
				$this->structure->build($target, 'category');
				// insure the file gets updated
				$this->contentmulti->set('category' . $otherView . '|view', $otherView);
				$this->contentmulti->set('category' . $otherView . '|View', ucfirst((string) $otherView));
				$this->contentmulti->set('category' . $otherView . '|views', $otherViews);
				$this->contentmulti->set('category' . $otherView . '|Views', ucfirst((string) $otherViews));
				// set script to global helper file
				$includeHelper   = [];
				$includeHelper[] = PHP_EOL . Indent::_(0) . "//" . Line::_(__Line__, __Class__)
					. "Insure this view category file is loaded.";
				$includeHelper[] = "\$classname = '" . ucfirst((string) $component)
					. ucfirst((string) $otherView) . "Categories';";
				$includeHelper[] = "if (!class_exists(\$classname))";
				$includeHelper[] = "{";
				$includeHelper[] = Indent::_(1)
					. "\$path = JPATH_SITE . '/components/com_" . $component
					. "/helpers/category" . $otherView . ".php';";
				$includeHelper[] = Indent::_(1) . "if (is_file(\$path))";
				$includeHelper[] = Indent::_(1) . "{";
				$includeHelper[] = Indent::_(2) . "include_once \$path;";
				$includeHelper[] = Indent::_(1) . "}";
				$includeHelper[] = "}";
				$this->contentone->add('CATEGORY_CLASS_TREES', implode(PHP_EOL, $includeHelper));
			}
			// return category view string
			if ($this->contentone->exists('ROUTER_CATEGORY_VIEWS')
				&& StringHelper::check(
					$this->contentone->get('ROUTER_CATEGORY_VIEWS')
				))
			{
				return "," . PHP_EOL . Indent::_(3) . '"'
					. $this->category->get("{$nameListCode}.extension")
					. '" => "' . $otherView . '"';
			}
			else
			{
				return PHP_EOL . Indent::_(3) . '"'
					. $this->category->get("{$nameListCode}.extension")
					. '" => "' . $otherView . '"';
			}
		}

		return '';
	}

	/**
	 * Build one view's case in the router's own parse switch.
	 *
	 * @param   string  $viewsCodeName  The list view code name.
	 *
	 * @return  string  The case, or nothing when there is no name to build one for.
	 *
	 * @since   6.1.7
	 */
	public function parseCase(string $viewsCodeName): string
	{
		if (strlen((string) $viewsCodeName) > 0)
		{
			$router = PHP_EOL . Indent::_(2) . "case '" . $viewsCodeName . "':";
			$router .= PHP_EOL . Indent::_(3)
				. "\$id = explode(':', \$segments[\$count-1]);";
			$router .= PHP_EOL . Indent::_(3) . "\$vars['id'] = (int) \$id[0];";
			$router .= PHP_EOL . Indent::_(3) . "\$vars['view'] = '"
				. $viewsCodeName
				. "';";
			$router .= PHP_EOL . Indent::_(2) . "break;";

			return $router;
		}

		return '';
	}
}

