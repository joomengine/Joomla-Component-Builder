<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Creator;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Structure;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Help Method Builder.
 * 
 * Responsible for generating the helper-layer methods related to component help:
 * - No-op help fallback
 * - Conditional help structure generation
 * - Full dynamic help resolution logic
 * 
 * This class **does not execute help logic** - it **builds PHP source code**
 * that will later be injected into compiled helper classes.
 * 
 * @since 5.1.4
 */
class Helper
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 5.1.4
	 */
	protected Config $config;

	/**
	 * The Structure Class.
	 *
	 * @var   Structure
	 * @since 5.1.4
	 */
	protected Structure $structure;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 5.1.4
	 */
	protected ContentOne $contentone;

	/**
	 * The ContentMulti Class.
	 *
	 * @var   ContentMulti
	 * @since 5.1.4
	 */
	protected ContentMulti $contentmulti;

	/**
	 * Fresh-query method emitted by this target renderer.
	 *
	 * @var   string
	 * @since 6.2.0
	 */
	protected string $queryMethod = 'getQuery(true)';

	/**
	 * Constructor.
	 *
	 * @param Config         $config         The Config Class.
	 * @param Structure      $structure      The Structure Class.
	 * @param ContentOne     $contentone     The ContentOne Class.
	 * @param ContentMulti   $contentmulti   The ContentMulti Class.
	 *
	 * @since 5.1.4
	 */
	public function __construct(Config $config, Structure $structure,
		ContentOne $contentone, ContentMulti $contentmulti)
	{
		$this->config = $config;
		$this->structure = $structure;
		$this->contentone = $contentone;
		$this->contentmulti = $contentmulti;
	}

	/**
	 * Build an empty help method.
	 *
	 * Used when no help system is present, but the helper API must still exist.
	 *
	 * @return string  Generated PHP method source.
	 * @since  5.1.4
	 */
	public function none(): string
	{
		$help   = [];
		$help[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
		$help[] = Indent::_(1) . " *" . Indent::_(1) . "Can be used to build help urls.";
		$help[] = Indent::_(1) . " **/";
		$help[] = Indent::_(1) . "public static function getHelpUrl(string \$view)";
		$help[] = Indent::_(1) . "{";
		$help[] = Indent::_(2) . "return false;";
		$help[] = Indent::_(1) . "}";

		return implode(PHP_EOL, $help);
	}

	/**
	 * Check whether help support must be generated and injected.
	 *
	 * When the help document structure exists, this method:
	 * - Builds admin and site help folders
	 * - Injects helper methods
	 * - Forces content refresh via compiler markers
	 *
	 * @param   string  $nameSingleCode
	 *
	 * @return  bool  True when help generation was successful.
	 * @since   5.1.4
	 */
	public function set(string $nameSingleCode): bool
	{
		if ($nameSingleCode !== 'help_document')
		{
			return false;
		}

		// Admin help structure
		$target    = ['admin' => 'help'];
		$admindone = $this->structure->build($target, 'help');

		// Site help structure
		$target   = ['site' => 'help'];
		$sitedone = $this->structure->build($target, 'help');

		if (!$admindone || !$sitedone)
		{
			return false;
		}

		// Inject helper methods
		$this->contentone->set('HELP', $this->help(1));
		$this->contentone->set('HELP_SITE', $this->help(2));

		// Force file update marker
		$this->contentmulti->set('help|BLABLA', 'blabla');

		return true;
	}

	/**
	 * Build the full dynamic help resolution method set.
	 *
	 * Generates:
	 * - getHelpUrl()
	 * - loadArticleLink()
	 * - loadHelpTextLink()
	 *
	 * The generated logic:
	 * - Resolves help entries by view
	 * - Filters by user groups
	 * - Dynamically returns article, text, or URL targets
	 *
	 * @param   int  $location  1 = admin, 2 = site
	 *
	 * @return  string  Generated PHP helper methods.
	 * @since   5.1.4
	 */
	protected function help(int $location): string
	{
		$target = ($location === 2) ? 'site_view' : 'admin_view';

		$help   = [];
		$help[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
		$help[] = Indent::_(1) . " *" . Indent::_(1) . "Load the Component Help URLs.";
		$help[] = Indent::_(1) . " **/";
		$help[] = Indent::_(1) . "public static function getHelpUrl(string \$view)";
		$help[] = Indent::_(1) . "{";

		// User resolution
		if ($this->config->get('joomla_version', 3) == 3)
		{
			$help[] = Indent::_(2) . "\$user" . Indent::_(1) . "= Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getUser();";
		}
		else
		{
			$help[] = Indent::_(2) . "\$user" . Indent::_(1) . "= Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getIdentity();";
		}

		$help[] = Indent::_(2) . "\$groups = \$user->groups;";

		// DB resolution
		if ($this->config->get('joomla_version', 3) == 3)
		{
			$help[] = Indent::_(2) . "\$db" . Indent::_(1) . "= Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getDbo();";
		}
		else
		{
			$help[] = Indent::_(2) . "\$db" . Indent::_(1) . "= Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->get(Joomla__"."_7bd29d76_73c9_4c07_a5da_4f7a32aff78f___Power::class);";
		}

		$help[] = Indent::_(2) . "\$query" . Indent::_(1) . "= \$db->{$this->queryMethod};";
		$help[] = Indent::_(2) . "\$query->select(array('a.id','a.groups','a.target','a.type','a.article','a.url'));";
		$help[] = Indent::_(2) . "\$query->from('#__" . $this->config->component_code_name . "_help_document AS a');";
		$help[] = Indent::_(2) . "\$query->where('a." . $target . " = '.\$db->quote(\$view));";
		$help[] = Indent::_(2) . "\$query->where('a.location = " . (int) $location . "');";
		$help[] = Indent::_(2) . "\$query->where('a.published = 1');";
		$help[] = Indent::_(2) . "\$db->setQuery(\$query);";
		$help[] = Indent::_(2) . "\$db->execute();";

		$help[] = Indent::_(2) . "if(\$db->getNumRows())";
		$help[] = Indent::_(2) . "{";
		$help[] = Indent::_(3) . "\$helps = \$db->loadObjectList();";
		$help[] = Indent::_(3) . "if (Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$helps))";
		$help[] = Indent::_(3) . "{";

		$help[] = Indent::_(4) . "foreach (\$helps as \$nr => \$help)";
		$help[] = Indent::_(4) . "{";
		$help[] = Indent::_(5) . "if (\$help->target == 1)";
		$help[] = Indent::_(5) . "{";
		$help[] = Indent::_(6) . "\$targetgroups = json_decode(\$help->groups, true);";
		$help[] = Indent::_(6) . "if (!array_intersect(\$targetgroups, \$groups))";
		$help[] = Indent::_(6) . "{";
		$help[] = Indent::_(7) . "unset(\$helps[\$nr]);";
		$help[] = Indent::_(7) . "continue;";
		$help[] = Indent::_(6) . "}";
		$help[] = Indent::_(5) . "}";

		$help[] = Indent::_(5) . "switch (\$help->type)";
		$help[] = Indent::_(5) . "{";
		$help[] = Indent::_(6) . "case 1:";
		$help[] = Indent::_(7) . "return self::loadArticleLink(\$help->article);";
		$help[] = Indent::_(6) . "case 2:";
		$help[] = Indent::_(7) . "return self::loadHelpTextLink(\$help->id);";
		$help[] = Indent::_(6) . "case 3:";
		$help[] = Indent::_(7) . "return \$help->url;";
		$help[] = Indent::_(5) . "}";
		$help[] = Indent::_(4) . "}";
		$help[] = Indent::_(3) . "}";
		$help[] = Indent::_(2) . "}";
		$help[] = Indent::_(2) . "return false;";
		$help[] = Indent::_(1) . "}";

		// Article helper
		$help[] = PHP_EOL . Indent::_(1) . "/**";
		$help[] = Indent::_(1) . " *" . Indent::_(1) . "Get the Article Link.";
		$help[] = Indent::_(1) . " **/";
		$help[] = Indent::_(1) . "protected static function loadArticleLink(\$id)";
		$help[] = Indent::_(1) . "{";
		$help[] = Indent::_(2)
			. "return Joomla__"."_eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root() . 'index.php?option=com_content&view=article&id='.\$id.'&tmpl=component&layout=modal';";
		$help[] = Indent::_(1) . "}";

		// Text helper
		$help[] = PHP_EOL . Indent::_(1) . "/**";
		$help[] = Indent::_(1) . " *" . Indent::_(1) . "Get the Help Text Link.";
		$help[] = Indent::_(1) . " **/";
		$help[] = Indent::_(1) . "protected static function loadHelpTextLink(\$id)";
		$help[] = Indent::_(1) . "{";
		$help[] = Indent::_(2) . "\$token = Joomla__"."_5ba38513_5c4f_4b0d_935e_49e986a6bce8___Power::getFormToken();";
		$help[] = Indent::_(2) . "return 'index.php?option=com_" . $this->config->component_code_name . "&task=help.getText&id=' . (int) \$id . '&' . \$token . '=1';";
		$help[] = Indent::_(1) . "}";

		return implode(PHP_EOL, $help);
	}
}

