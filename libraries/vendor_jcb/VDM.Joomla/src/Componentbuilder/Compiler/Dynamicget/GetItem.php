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

namespace VDM\Joomla\Componentbuilder\Compiler\Dynamicget;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Builder\SiteDecrypt;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\SiteFieldData;
use VDM\Joomla\Componentbuilder\Compiler\Builder\SiteFieldDecodeFilter;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ModelExpertFieldInitiator;
use VDM\Joomla\Componentbuilder\Compiler\Builder\EventDispatcher;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\DecodeColumn;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\FilterColumn;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\FieldonContentPrepare;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\UikitLoader;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\Globals;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\CustomJoin;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\Queries;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryFilter;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryWhere;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryOrder;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryGroup;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;


/**
 * Dynamic Get GetItem
 * 
 * @since 5.1.2
 */
final class GetItem
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 5.1.2
	 */
	protected Config $config;

	/**
	 * The SiteDecrypt Class.
	 *
	 * @var   SiteDecrypt
	 * @since 5.1.2
	 */
	protected SiteDecrypt $sitedecrypt;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 5.1.2
	 */
	protected Placeholder $placeholder;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 5.1.2
	 */
	protected Language $language;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 5.1.2
	 */
	protected ContentOne $contentone;

	/**
	 * The SiteFieldData Class.
	 *
	 * @var   SiteFieldData
	 * @since 5.1.2
	 */
	protected SiteFieldData $sitefielddata;

	/**
	 * The SiteFieldDecodeFilter Class.
	 *
	 * @var   SiteFieldDecodeFilter
	 * @since 5.1.2
	 */
	protected SiteFieldDecodeFilter $sitefielddecodefilter;

	/**
	 * The ModelExpertFieldInitiator Class.
	 *
	 * @var   ModelExpertFieldInitiator
	 * @since 5.1.2
	 */
	protected ModelExpertFieldInitiator $modelexpertfieldinitiator;

	/**
	 * The EventDispatcher Class.
	 *
	 * @var   EventDispatcher
	 * @since 5.1.2
	 */
	protected EventDispatcher $eventdispatcher;

	/**
	 * The DecodeColumn Class.
	 *
	 * @var   DecodeColumn
	 * @since 5.1.2
	 */
	protected DecodeColumn $decodecolumn;

	/**
	 * The FilterColumn Class.
	 *
	 * @var   FilterColumn
	 * @since 5.1.2
	 */
	protected FilterColumn $filtercolumn;

	/**
	 * The FieldonContentPrepare Class.
	 *
	 * @var   FieldonContentPrepare
	 * @since 5.1.2
	 */
	protected FieldonContentPrepare $fieldoncontentprepare;

	/**
	 * The UikitLoader Class.
	 *
	 * @var   UikitLoader
	 * @since 5.1.2
	 */
	protected UikitLoader $uikitloader;

	/**
	 * The Globals Class.
	 *
	 * @var   Globals
	 * @since 5.1.2
	 */
	protected Globals $globals;

	/**
	 * The CustomJoin Class.
	 *
	 * @var   CustomJoin
	 * @since 5.1.2
	 */
	protected CustomJoin $customjoin;

	/**
	 * The Queries Class.
	 *
	 * @var   Queries
	 * @since 5.1.2
	 */
	protected Queries $queries;

	/**
	 * The QueryFilter Class.
	 *
	 * @var   QueryFilter
	 * @since 5.1.2
	 */
	protected QueryFilter $queryfilter;

	/**
	 * The QueryWhere Class.
	 *
	 * @var   QueryWhere
	 * @since 5.1.2
	 */
	protected QueryWhere $querywhere;

	/**
	 * The QueryOrder Class.
	 *
	 * @var   QueryOrder
	 * @since 5.1.2
	 */
	protected QueryOrder $queryorder;

	/**
	 * The QueryGroup Class.
	 *
	 * @var   QueryGroup
	 * @since 5.1.2
	 */
	protected QueryGroup $querygroup;

	/**
	 * Constructor.
	 *
	 * @param Config                      $config                      The Config Class.
	 * @param SiteDecrypt                 $sitedecrypt                 The SiteDecrypt Class.
	 * @param Placeholder                 $placeholder                 The Placeholder Class.
	 * @param Language                    $language                    The Language Class.
	 * @param ContentOne                  $contentone                  The ContentOne Class.
	 * @param SiteFieldData               $sitefielddata               The SiteFieldData Class.
	 * @param SiteFieldDecodeFilter       $sitefielddecodefilter       The SiteFieldDecodeFilter Class.
	 * @param ModelExpertFieldInitiator   $modelexpertfieldinitiator   The ModelExpertFieldInitiator Class.
	 * @param EventDispatcher             $eventdispatcher             The EventDispatcher Class.
	 * @param DecodeColumn                $decodecolumn                The DecodeColumn Class.
	 * @param FilterColumn                $filtercolumn                The FilterColumn Class.
	 * @param FieldonContentPrepare       $fieldoncontentprepare       The FieldonContentPrepare Class.
	 * @param UikitLoader                 $uikitloader                 The UikitLoader Class.
	 * @param Globals                     $globals                     The Globals Class.
	 * @param CustomJoin                  $customjoin                  The CustomJoin Class.
	 * @param Queries                     $queries                     The Queries Class.
	 * @param QueryFilter                 $queryfilter                 The QueryFilter Class.
	 * @param QueryWhere                  $querywhere                  The QueryWhere Class.
	 * @param QueryOrder                  $queryorder                  The QueryOrder Class.
	 * @param QueryGroup                  $querygroup                  The QueryGroup Class.
	 *
	 * @since 5.1.2
	 */
	public function __construct(Config $config, SiteDecrypt $sitedecrypt,
		Placeholder $placeholder, Language $language,
		ContentOne $contentone, SiteFieldData $sitefielddata,
		SiteFieldDecodeFilter $sitefielddecodefilter,
		ModelExpertFieldInitiator $modelexpertfieldinitiator,
		EventDispatcher $eventdispatcher,
		DecodeColumn $decodecolumn, FilterColumn $filtercolumn,
		FieldonContentPrepare $fieldoncontentprepare,
		UikitLoader $uikitloader, Globals $globals,
		CustomJoin $customjoin, Queries $queries,
		QueryFilter $queryfilter, QueryWhere $querywhere,
		QueryOrder $queryorder, QueryGroup $querygroup)
	{
		$this->config = $config;
		$this->sitedecrypt = $sitedecrypt;
		$this->placeholder = $placeholder;
		$this->language = $language;
		$this->contentone = $contentone;
		$this->sitefielddata = $sitefielddata;
		$this->sitefielddecodefilter = $sitefielddecodefilter;
		$this->modelexpertfieldinitiator = $modelexpertfieldinitiator;
		$this->eventdispatcher = $eventdispatcher;
		$this->decodecolumn = $decodecolumn;
		$this->filtercolumn = $filtercolumn;
		$this->fieldoncontentprepare = $fieldoncontentprepare;
		$this->uikitloader = $uikitloader;
		$this->globals = $globals;
		$this->customjoin = $customjoin;
		$this->queries = $queries;
		$this->queryfilter = $queryfilter;
		$this->querywhere = $querywhere;
		$this->queryorder = $queryorder;
		$this->querygroup = $querygroup;
	}

	/**
	 * Get the dynamic get item method.
	 *
	 * @param  mixed   $get   The get object.
	 * @param  string  $code  The code string.
	 * @param  string  $tab   The tab spacing.
	 * @param  string  $type  The type (main|custom).
	 *
	 * @return string  The generated PHP code block.
	 * @since  5.1.2
	 */
	public function get($get, string $code, string $tab = '', string $type = 'main'): string
	{
		if (!ObjectHelper::check($get) || empty($code))
		{
			return PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "//" . Line::_(__LINE__, __CLASS__) . "add your custom code here.";
		}

		$this->removeCryptionTypes($code);

		$getItem = $this->addPhpBeforeItem($get);
		$getItem .= $this->getDatabaseSetup($tab);
		$getItem .= $this->buildMainQuery($get, $code, $tab);
		$getItem .= $this->buildPostQueryPlaceholder($get);
		$getItem = $this->replaceQueryPlaceholderIfNeeded($getItem, $tab);
		$getItem .= $this->buildEmptyDataFailSafe($type, $tab, $code);
		$getItem .= Placefix::_h("DISPATCHER");

		$asBucket = [];
		if (isset($get->main_get) && ArrayHelper::check($get->main_get))
		{
			$getItem .= $this->buildPostProcessFieldChecks($get, $code, $tab, $asBucket);
		}

		$script   = $this->buildCryptionScript($code, $tab);
		$getItem  = $script . $getItem;
		$getItem .= $this->buildGlobals($get, $code, $tab, $asBucket);
		$getItem .= $this->buildCustomJoin($get, $code, $tab, $asBucket);
		$getItem .= $this->buildCalculation($get, $tab);
		$getItem .= $this->buildReturnBlock($type, $tab);
		$getItem = $this->injectDispatcherIfNeeded($getItem, $code);

		return $getItem;
	}

	/**
	 * Remove all cryption type flags for this code.
	 *
	 * @param  string  $code
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function removeCryptionTypes(string $code): void
	{
		foreach ($this->config->cryption_types as $cryptionType)
		{
			$this->sitedecrypt->remove("{$cryptionType}.{$code}");
		}
	}

	/**
	 * Add custom PHP before the get item block.
	 *
	 * @param  object  $get
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function addPhpBeforeItem(object $get): string
	{
		if (
			isset($get->add_php_before_getitem) && $get->add_php_before_getitem == 1
			&& isset($get->php_before_getitem) && StringHelper::check($get->php_before_getitem)
		)
		{
			return $this->placeholder->update_($get->php_before_getitem);
		}
		return '';
	}

	/**
	 * Build the DB connection and query setup.
	 *
	 * @param  string  $tab
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function getDatabaseSetup(string $tab): string
	{
		$dbSetup = PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "//" . Line::_(__LINE__, __CLASS__) . " Get a db connection.";

		if ($this->config->get('joomla_version', 3) == 3)
		{
			$dbSetup .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "\$db = Joomla__" . "_39403062_84fb_46e0_bac4_0023f766e827___Power::getDbo();";
		}
		else
		{
			$dbSetup .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "\$db = \$this->getDatabase();";
		}

		$dbSetup .= PHP_EOL . PHP_EOL . $tab . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " Create a new query object.";
		$dbSetup .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "\$query = \$db->getQuery(true);";

		return $dbSetup;
	}

	/**
	 * Append query logic for get, filters, where, order, group.
	 *
	 * @param  object  $get
	 * @param  string  $code
	 * @param  string  $tab
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function buildMainQuery(object $get, string $code, string $tab): string
	{
		if (empty($get->main_get))
		{
			return '';
		}

		$query = $this->queries->get($get->main_get, $code, $tab);

		// Optional parts
		foreach (['queryfilter' => 'filter', 'querywhere' => 'where', 'queryorder' => 'order', 'querygroup' => 'group'] as $queryType => $field)
		{
			if (isset($get->{$field}))
			{
				$query .= $this->{$queryType}->get($get->{$field}, $code, $tab);
			}
		}

		return $query;
	}

	/**
	 * Append custom PHP and query placeholder.
	 *
	 * @param  object  $get
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function buildPostQueryPlaceholder(object $get): string
	{
		$post = Placefix::_h("DB_SET_QUERY_DATA");
		if (
			isset($get->add_php_after_getitem) && $get->add_php_after_getitem == 1
			&& isset($get->php_after_getitem) && StringHelper::check($get->php_after_getitem)
		)
		{
			$post .= $this->placeholder->update_($get->php_after_getitem);
		}
		return $post;
	}

	/**
	 * Replace the placeholder with real DB query execution if needed.
	 *
	 * @param  string  $getItem
	 * @param  string  $tab
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function replaceQueryPlaceholderIfNeeded(string $getItem, string $tab): string
	{
		if (strpos($getItem, '$data =') === false)
		{
			$setQuery  = PHP_EOL . PHP_EOL . $tab . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " Reset the query using our newly populated query object.";
			$setQuery .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "\$db->setQuery(\$query);";
			$setQuery .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "//" . Line::_(__LINE__, __CLASS__) . " Load the results as a stdClass object.";
			$setQuery .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "\$data = \$db->loadObject();";

			return str_replace(Placefix::_h("DB_SET_QUERY_DATA"), $setQuery, $getItem);
		}

		return str_replace(Placefix::_h("DB_SET_QUERY_DATA"), '', $getItem);
	}

	/**
	 * Add conditional fallback if \$data is empty.
	 *
	 * @param  string  $type
	 * @param  string  $tab
	 * @param  string  $code
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function buildEmptyDataFailSafe(string $type, string $tab, string $code): string
	{
		$block = PHP_EOL . PHP_EOL . $tab . Indent::_(2) . "if (empty(\$data))" . PHP_EOL;
		$block .= Indent::_(1) . $tab . Indent::_(1) . "{";
		if ($type === 'main')
		{
			$langKey = $this->config->lang_prefix . '_' . StringHelper::safe('Not found or access denied', 'U');
			$this->language->set($this->config->lang_target, $langKey, 'Not found, or access denied.');

			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2)
				. "\$app = Joomla__" . "_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();";
			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2)
				. "//" . Line::_(__LINE__, __CLASS__) . " The API answers not found instead of redirecting.";
			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2) . "if (\$app->isClient('api'))";
			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2) . "{";
			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(3)
				. "throw new \\Exception(Joomla__" . "_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('{$langKey}'), 404);";
			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2) . "}";
			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2)
				. "//" . Line::_(__LINE__, __CLASS__) . " If no data is found redirect to default page and show warning.";
			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2)
				. "\$app->enqueueMessage(Joomla__" . "_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('{$langKey}'), 'warning');";

			if ('site' === $this->config->build_target)
			{
				if ($this->contentone->exists('SITE_DEFAULT_VIEW')
					&& $this->contentone->get('SITE_DEFAULT_VIEW') != $code)
				{
					$redirect = "Joomla__" . "_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_" . $this->config->component_code_name . "&view=" . $this->contentone->get('SITE_DEFAULT_VIEW') . "')";
				}
				else
				{
					$redirect = "Joomla__" . "_eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root()";
				}
				$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2) . "\$app->redirect({$redirect});";
			}
			else
			{
				$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2)
					. "\$app->redirect('index.php?option=com_" . $this->config->component_code_name . "');";
			}
			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2) . "return false;";
		}
		else
		{
			$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(2) . "return false;";
		}

		$block .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "}";

		return $block;
	}

	/**
	 * Inject dispatcher logic if placeholder exists.
	 *
	 * @param  string  $getItem
	 * @param  string  $code
	 *
	 * @return string
	 * @since 5.1.2
	 */
	private function injectDispatcherIfNeeded(string $getItem, string $code): string
	{
		if (strpos($getItem, (string) Placefix::_h('DISPATCHER')) !== false)
		{
			return str_replace(
				Placefix::_h('DISPATCHER'),
				$this->eventdispatcher->get($code, ''),
				$getItem
			);
		}
		return $getItem;
	}

	/**
	 * Handle decode, filter, prepare, UIkit field processing.
	 *
	 * @param  object   $get
	 * @param  string   $code
	 * @param  string   $tab
	 * @param  string[] &$asBucket
	 *
	 * @return string
	 * @since 5.1.2
	 */
	private function buildPostProcessFieldChecks(object $get, string $code, string $tab, array &$asBucket): string
	{
		$output = '';

		foreach ($get->main_get as $main_get)
		{
			if (!isset($main_get['key'], $main_get['as']))
			{
				continue;
			}

			$path = $code . '.' . $main_get['key'] . '.' . $main_get['as'];

			$decodeChecker = $this->sitefielddata->get('decode.' . $path);
			$decodeFilter = $this->sitefielddecodefilter->get($this->config->build_target . '.' . $path);
			$contentprepareChecker = $this->sitefielddata->get('textareas.' . $path);
			$uikitChecker = $this->sitefielddata->get('uikit.' . $path);

			$decoder         = $this->getDecoderCode($main_get, $code, $tab, $decodeChecker);
			$decoderFilter   = $this->getDecoderFilterCode($main_get, $code, $tab, $decodeFilter);
			$contentPrepare  = $this->getContentPrepareCode($main_get, $code, $tab, $contentprepareChecker);
			$uikit           = $this->getUIKitCode($main_get, $code, $tab, $uikitChecker);

			if ($this->hasFieldProcessing($decoder, $decoderFilter, $contentPrepare, $uikit))
			{
				$output .= $this->buildFieldProcessingBlock($decoder, $decoderFilter, $contentPrepare, $uikit);
			}

			$asBucket[] = $main_get['as'];
		}

		return $output;
	}

	/**
	 * Generate decoder block code for the matched field set.
	 *
	 * @param  array       $get      The get definition.
	 * @param  string      $code     The code name.
	 * @param  string      $tab      The tabing string
	 * @param  array|null  $checker  The decoder rules to apply (if any).
	 *
	 * @return string  The decoder logic code block or an empty string.
	 * @since  5.1.2
	 */
	private function getDecoderCode(array $get, string $code, string $tab, ?array $checker): string
	{
		return ($checker !== null && ArrayHelper::check($checker))
			? $this->decodecolumn->get($get, $checker, '$data', $code, $tab)
			: '';
	}

	/**
	 * Generate filter decoder block for field-specific filters.
	 *
	 * @param  array       $get      The get definition.
	 * @param  string      $code     The code name.
	 * @param  string      $tab      The tabing string
	 * @param  array|null  $checker  The filter configuration to apply (if any).
	 *
	 * @return string  The filtered decoder code block or an empty string.
	 * @since  5.1.2
	 */
	private function getDecoderFilterCode(array $get, string $code, string $tab, ?array $checker): string
	{
		return ($checker !== null && ArrayHelper::check($checker))
			? $this->filtercolumn->get($get, $checker, '$data', '$data', $code, $tab)
			: '';
	}

	/**
	 * Generate content preparation code for specified textarea fields.
	 *
	 * @param  array       $get      The get definition.
	 * @param  string      $code     The code name.
	 * @param  string      $tab      The tabing string
	 * @param  array|null  $checker  The content prepare configuration to apply (if any).
	 *
	 * @return string  The content prepare code block or an empty string.
	 * @since  5.1.2
	 */
	private function getContentPrepareCode(array $get, string $code, string $tab, ?array $checker): string
	{
		return ($checker !== null && ArrayHelper::check($checker))
			? $this->fieldoncontentprepare->get($get, $checker, '$data', $code, $tab)
			: '';
	}

	/**
	 * Generate UIkit-specific field formatting code.
	 *
	 * @param  array       $get      The get definition.
	 * @param  string      $code     The code name.
	 * @param  string      $tab      The tabing string
	 * @param  array|null  $checker  The UIkit config for visual formatting (if any).
	 *
	 * @return string  The UIkit loader code block or an empty string.
	 * @since  5.1.2
	 */
	private function getUIKitCode(array $get, string $code, string $tab, ?array $checker): string
	{
		return ($checker !== null && ArrayHelper::check($checker))
			? $this->uikitloader->get($get, $checker, '$data', $code, $tab)
			: '';
	}

	/**
	 * Check if any of the provided code parts contain executable logic.
	 *
	 * @param  string  ...$parts  The list of code strings to evaluate.
	 *
	 * @return bool  True if any string is non-empty and valid.
	 * @since  5.1.2
	 */
	private function hasFieldProcessing(string ...$parts): bool
	{
		foreach ($parts as $part)
		{
			if (StringHelper::check($part))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Build the complete foreach loop block to process all returned items.
	 *
	 * @param  string  $decoder         The decoder block.
	 * @param  string  $decoderFilter   The decoder filter block.
	 * @param  string  $contentPrepare  The content prepare block.
	 * @param  string  $uikit           The UIkit block.
	 *
	 * @return string  The complete loop and return block for $items.
	 * @since  5.1.2
	 */
	private function buildFieldProcessingBlock(
		string $decoder,
		string $decoderFilter,
		string $contentPrepare,
		string $uikit
	): string
	{
		$code = '';

		foreach ([$decoder, $decoderFilter, $contentPrepare, $uikit] as $block)
		{
			if (StringHelper::check($block))
			{
				$code .= $block;
			}
		}

		return $code;
	}

	/**
	 * Build the cryption script injection.
	 *
	 * @param  string  $code
	 * @param  string  $tab
	 *
	 * @return string
	 * @since 5.1.2
	 */
	private function buildCryptionScript(string $code, string $tab): string
	{
		$script = '';
		$component = $this->contentone->get('Component');

		foreach ($this->config->cryption_types as $cryptionType)
		{
			if ($this->sitedecrypt->get("{$cryptionType}.{$code}") !== null)
			{
				if ('expert' !== $cryptionType)
				{
					$script .= PHP_EOL . PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "//" . Line::_(__LINE__, __CLASS__) . " Get the {$cryptionType} encryption.";
					$script .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "\${$cryptionType}key = {$component}Helper::getCryptKey('{$cryptionType}');";
					$script .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "//" . Line::_(__LINE__, __CLASS__) . " Get the encryption object.";
					$script .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "\${$cryptionType} = new Super__"."_99175f6d_dba8_4086_8a65_5c4ec175e61d___Power(\${$cryptionType}key);";
				}
				elseif ($this->modelexpertfieldinitiator->exists("{$code}.get"))
				{
					foreach ($this->modelexpertfieldinitiator->get("{$code}.get") as $block)
					{
						$script .= PHP_EOL . Indent::_(1) . implode(PHP_EOL . Indent::_(1), $block);
					}
				}
			}
		}

		return $script;
	}

	/**
	 * Build the global loader.
	 *
	 * @param  object   $get
	 * @param  string   $code
	 * @param  string   $tab
	 * @param  string[] $asBucket
	 *
	 * @return string
	 * @since 5.1.2
	 */
	private function buildGlobals(object $get, string $code, string $tab, array $asBucket): string
	{
		return $this->globals->get($get->global ?? [], '$data', $asBucket, $tab);
	}

	/**
	 * Build the custom join logic.
	 *
	 * @param  object   $get
	 * @param  string   $code
	 * @param  string   $tab
	 * @param  string[] $asBucket
	 *
	 * @return string
	 * @since 5.1.2
	 */
	private function buildCustomJoin(object $get, string $code, string $tab, array $asBucket): string
	{
		return $this->customjoin->get($get->custom_get ?? [], '$data', $code, $asBucket, $tab);
	}

	/**
	 * Build the custom calculation logic block.
	 *
	 * @param  object  $get
	 * @param  string  $tab
	 *
	 * @return string
	 * @since 5.1.2
	 */
	private function buildCalculation(object $get, string $tab): string
	{
		if (isset($get->addcalculation) && $get->addcalculation == 1 && !empty($get->php_calculation))
		{
			$get->php_calculation = (array) explode(PHP_EOL, (string) $this->placeholder->update_($get->php_calculation));
			return PHP_EOL . Indent::_(1) . $tab . Indent::_(1)
				. implode(PHP_EOL . Indent::_(1) . $tab . Indent::_(1), $get->php_calculation);
		}
		return '';
	}

	/**
	 * Build return or assignment block for \$data.
	 *
	 * @param  string  $type
	 * @param  string  $tab
	 *
	 * @return string
	 * @since 5.1.2
	 */
	private function buildReturnBlock(string $type, string $tab): string
	{
		$line = PHP_EOL . PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "//" . Line::_(__LINE__, __CLASS__);

		if ($type === 'custom')
		{
			$line .= " return data object.";
			$line .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "return \$data;";
		}
		else
		{
			$line .= " set data object to item.";
			$line .= PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "\$this->_item[\$pk] = \$data;";
		}

		return $line;
	}
}

