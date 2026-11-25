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
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\FieldonContentPrepare;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\JoinStructure;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\DecodeColumn;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\FilterColumn;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\UikitLoader;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\SiteDecrypt;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ModelExpertFieldInitiator;
use VDM\Joomla\Componentbuilder\Compiler\Builder\SiteFieldData;
use VDM\Joomla\Componentbuilder\Compiler\Builder\SiteFieldDecodeFilter;
use VDM\Joomla\Componentbuilder\Compiler\Builder\OtherJoin;
use VDM\Joomla\Componentbuilder\Compiler\Builder\OtherQuery;
use VDM\Joomla\Componentbuilder\Compiler\Builder\OtherFilter;
use VDM\Joomla\Componentbuilder\Compiler\Builder\OtherWhere;
use VDM\Joomla\Componentbuilder\Compiler\Builder\OtherOrder;
use VDM\Joomla\Componentbuilder\Compiler\Builder\OtherGroup;
use VDM\Joomla\Componentbuilder\Compiler\Builder\EventDispatcher;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Dynamic Get Custom Get Methods
 * 
 * @since 5.1.2
 */
class CustomGetMethods
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 5.1.2
	 */
	protected Config $config;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 5.1.2
	 */
	protected Placeholder $placeholder;

	/**
	 * The FieldonContentPrepare Class.
	 *
	 * @var   FieldonContentPrepare
	 * @since 5.1.2
	 */
	protected FieldonContentPrepare $fieldoncontentprepare;

	/**
	 * The JoinStructure Class.
	 *
	 * @var   JoinStructure
	 * @since 5.1.2
	 */
	protected JoinStructure $joinstructure;

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
	 * The UikitLoader Class.
	 *
	 * @var   UikitLoader
	 * @since 5.1.2
	 */
	protected UikitLoader $uikitloader;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 5.1.2
	 */
	protected ContentOne $contentone;

	/**
	 * The SiteDecrypt Class.
	 *
	 * @var   SiteDecrypt
	 * @since 5.1.2
	 */
	protected SiteDecrypt $sitedecrypt;

	/**
	 * The ModelExpertFieldInitiator Class.
	 *
	 * @var   ModelExpertFieldInitiator
	 * @since 5.1.2
	 */
	protected ModelExpertFieldInitiator $modelexpertfieldinitiator;

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
	 * The OtherJoin Class.
	 *
	 * @var   OtherJoin
	 * @since 5.1.2
	 */
	protected OtherJoin $otherjoin;

	/**
	 * The OtherQuery Class.
	 *
	 * @var   OtherQuery
	 * @since 5.1.2
	 */
	protected OtherQuery $otherquery;

	/**
	 * The OtherFilter Class.
	 *
	 * @var   OtherFilter
	 * @since 5.1.2
	 */
	protected OtherFilter $otherfilter;

	/**
	 * The OtherWhere Class.
	 *
	 * @var   OtherWhere
	 * @since 5.1.2
	 */
	protected OtherWhere $otherwhere;

	/**
	 * The OtherOrder Class.
	 *
	 * @var   OtherOrder
	 * @since 5.1.2
	 */
	protected OtherOrder $otherorder;

	/**
	 * The OtherGroup Class.
	 *
	 * @var   OtherGroup
	 * @since 5.1.2
	 */
	protected OtherGroup $othergroup;

	/**
	 * The EventDispatcher Class.
	 *
	 * @var   EventDispatcher
	 * @since 5.1.2
	 */
	protected EventDispatcher $eventdispatcher;

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
	 * @param Config                      $config                      The Config Class.
	 * @param Placeholder                 $placeholder                 The Placeholder Class.
	 * @param FieldonContentPrepare       $fieldoncontentprepare       The FieldonContentPrepare Class.
	 * @param JoinStructure               $joinstructure               The JoinStructure Class.
	 * @param DecodeColumn                $decodecolumn                The DecodeColumn Class.
	 * @param FilterColumn                $filtercolumn                The FilterColumn Class.
	 * @param UikitLoader                 $uikitloader                 The UikitLoader Class.
	 * @param ContentOne                  $contentone                  The ContentOne Class.
	 * @param SiteDecrypt                 $sitedecrypt                 The SiteDecrypt Class.
	 * @param ModelExpertFieldInitiator   $modelexpertfieldinitiator   The ModelExpertFieldInitiator Class.
	 * @param SiteFieldData               $sitefielddata               The SiteFieldData Class.
	 * @param SiteFieldDecodeFilter       $sitefielddecodefilter       The SiteFieldDecodeFilter Class.
	 * @param OtherJoin                   $otherjoin                   The OtherJoin Class.
	 * @param OtherQuery                  $otherquery                  The OtherQuery Class.
	 * @param OtherFilter                 $otherfilter                 The OtherFilter Class.
	 * @param OtherWhere                  $otherwhere                  The OtherWhere Class.
	 * @param OtherOrder                  $otherorder                  The OtherOrder Class.
	 * @param OtherGroup                  $othergroup                  The OtherGroup Class.
	 * @param EventDispatcher             $eventdispatcher             The EventDispatcher Class.
	 *
	 * @since 5.1.2
	 */
	public function __construct(Config $config, Placeholder $placeholder,
		FieldonContentPrepare $fieldoncontentprepare,
		JoinStructure $joinstructure, DecodeColumn $decodecolumn,
		FilterColumn $filtercolumn, UikitLoader $uikitloader,
		ContentOne $contentone, SiteDecrypt $sitedecrypt,
		ModelExpertFieldInitiator $modelexpertfieldinitiator,
		SiteFieldData $sitefielddata,
		SiteFieldDecodeFilter $sitefielddecodefilter,
		OtherJoin $otherjoin, OtherQuery $otherquery,
		OtherFilter $otherfilter, OtherWhere $otherwhere,
		OtherOrder $otherorder, OtherGroup $othergroup,
		EventDispatcher $eventdispatcher)
	{
		$this->config = $config;
		$this->placeholder = $placeholder;
		$this->fieldoncontentprepare = $fieldoncontentprepare;
		$this->joinstructure = $joinstructure;
		$this->decodecolumn = $decodecolumn;
		$this->filtercolumn = $filtercolumn;
		$this->uikitloader = $uikitloader;
		$this->contentone = $contentone;
		$this->sitedecrypt = $sitedecrypt;
		$this->modelexpertfieldinitiator = $modelexpertfieldinitiator;
		$this->sitefielddata = $sitefielddata;
		$this->sitefielddecodefilter = $sitefielddecodefilter;
		$this->otherjoin = $otherjoin;
		$this->otherquery = $otherquery;
		$this->otherfilter = $otherfilter;
		$this->otherwhere = $otherwhere;
		$this->otherorder = $otherorder;
		$this->othergroup = $othergroup;
		$this->eventdispatcher = $eventdispatcher;
	}

	/**
	 * Get the dynamic get custom item methods.
	 *
	 * @param   mixed   $mainGet   The main get object.
	 * @param   string  $code      The code string.
	 *
	 * @return string  The generated methods code.
	 * @since  5.1.2
	 */
	public function get($mainGet, string $code): string
	{
		if (empty($mainGet) || empty($code))
		{
			return '';
		}

		$methods = '';

		$this->eventdispatcher->remove($code);

		if ($this->isValidCustomGet($mainGet))
		{
			$methods = $this->processCustomGet($mainGet->custom_get, $code);
		}

		$methods = $this->injectDispatcherIfNeeded($methods, $code);

		if (StringHelper::check($methods))
		{
			return $methods . PHP_EOL;
		}

		return '';
	}

	/**
	 * Check if the customGet object is valid.
	 *
	 * @param   mixed  $mainGet  The main get object.
	 *
	 * @return boolean
	 * @since  5.1.2
	 */
	private function isValidCustomGet($mainGet)
	{
		return ObjectHelper::check($mainGet)
			&& isset($mainGet->custom_get)
			&& ArrayHelper::check($mainGet->custom_get);
	}

	/**
	 * Process all custom_get entries.
	 *
	 * @param   array   $customGets  The custom get definitions.
	 * @param   string  $code        The code string.
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function processCustomGet(array $customGets, string $code): string
	{
		$methods = '';

		foreach ($customGets as $get)
		{
			$this->removeCryptionTypes($code);

			if (($default = $this->joinstructure->get($get, $code)) !== null)
			{
				$methods .= $this->buildCustomMethod($get, $default);
				$methods  = $this->injectCryptionScript($methods, $default, $code);
			}
		}

		if (StringHelper::check($methods))
		{
			$methods = str_replace(Placefix::_h('CRYPT'), '', $methods);
		}

		return $methods;
	}

	/**
	 * Remove cryption types for a given code.
	 *
	 * @param   string  $code  The code string.
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
	 * Build the custom method code.
	 *
	 * @param   array  $get      The get definition.
	 * @param   array  $default  The default structure.
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function buildCustomMethod(array $get, array $default): string
	{
		$methods  = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
		$methods .= PHP_EOL . Indent::_(1) . " *" . Line::_(__Line__, __Class__) . " Method to get an array of {$default['name']} Objects.";
		$methods .= PHP_EOL . Indent::_(1) . " *";
		$methods .= PHP_EOL . Indent::_(1) . " * @return mixed  An array of {$default['name']} Objects on success, false on failure.";
		$methods .= PHP_EOL . Indent::_(1) . " *";
		$methods .= PHP_EOL . Indent::_(1) . " */";
		$methods .= PHP_EOL . Indent::_(1) . "public function get{$default['methodName']}(\${$default['on_field']})";
		$methods .= PHP_EOL . Indent::_(1) . "{" . Placefix::_h("CRYPT");

		$methods .= $this->buildDatabaseSetup($get, $default);
		$methods .= $this->applyQueryConditions($get, $default);
		$methods .= $this->applyAdditionalBuilders($default);
		$methods .= $this->buildQueryExecutionBlock($get, $default);

		$methods .= PHP_EOL . Indent::_(1) . "}";

		return $methods;
	}

	/**
	 * Build database setup section.
	 *
	 * @param   array  $get      The get definition.
	 * @param   array  $default  The default structure.
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function buildDatabaseSetup(array $get, array $default): string
	{
		$methods  = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__) . " Get a db connection.";

		if ($this->config->get('joomla_version', 3) == 3)
		{
			$methods .= PHP_EOL . Indent::_(2) . "\$db = Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getDbo();";
		}
		else
		{
			$methods .= PHP_EOL . Indent::_(2) . "\$db = \$this->getDatabase();";
		}

		$methods .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__) . " Create a new query object.";
		$methods .= PHP_EOL . Indent::_(2) . "\$query = \$db->{$this->queryMethod};";
		$methods .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__) . " Get from {$get['selection']['table']} as {$default['as']}";
		$methods .= PHP_EOL . Indent::_(2) . $get['selection']['select'];
		$methods .= PHP_EOL . Indent::_(2) . "\$query->from({$get['selection']['from']});";

		return $methods;
	}

	/**
	 * Apply query conditions.
	 *
	 * @param   array  $get      The get definition.
	 * @param   array  $default  The default structure.
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function applyQueryConditions(array $get, array $default): string
	{
		$methods = '';

		if ($get['operator'] === 'IN' || $get['operator'] === 'NOT IN')
		{
			$methods .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__) . " Check if \${$default['on_field']} is an array with values.";
			$methods .= PHP_EOL . Indent::_(2) . "\$array = (Super_" . "__4b225c51_d293_48e4_b3f6_5136cf5c3f18___Power::check(\${$default['on_field']}, true)) ? json_decode(\${$default['on_field']}, true) : \${$default['on_field']};";
			$methods .= PHP_EOL . Indent::_(2) . "if (isset(\$array) && Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$array, true))";
			$methods .= PHP_EOL . Indent::_(2) . "{";
			$methods .= PHP_EOL . Indent::_(3) . "\$query->where('{$get['join_field']} {$get['operator']} (' . implode(',', \$array) . ')');";
			$methods .= PHP_EOL . Indent::_(2) . "}";
			$methods .= PHP_EOL . Indent::_(2) . "else";
			$methods .= PHP_EOL . Indent::_(2) . "{";
			$methods .= PHP_EOL . Indent::_(3) . "return false;";
			$methods .= PHP_EOL . Indent::_(2) . "}";
		}
		else
		{
			$methods .= PHP_EOL . Indent::_(2) . "\$query->where('{$get['join_field']} {$get['operator']} ' . \$db->quote(\${$default['on_field']}));";
		}

		return $methods;
	}

	/**
	 * Apply additional builder parts (query, filter, where, order, group).
	 *
	 * @param   array  $default  The default structure.
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function applyAdditionalBuilders(array $default): string
	{
		$methods = '';

		foreach (['otherquery', 'otherfilter', 'otherwhere', 'otherorder', 'othergroup'] as $builderType)
		{
			foreach ($this->{$builderType}->get(
				$this->config->build_target . '.' . $default['code'] . '.' . $default['as'], []
				) as $string)
			{
				$methods .= $string;
			}
		}

		return $methods;
	}

	/**
	 * Build query execution and result processing.
	 *
	 * @param  array  $get      The get definition.
	 * @param  array  $default  The default structure.
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function buildQueryExecutionBlock(array $get, array $default): string
	{
		$code  = PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " Reset the query using our newly populated query object.";
		$code .= PHP_EOL . Indent::_(2) . '$db->setQuery($query);';
		$code .= PHP_EOL . Indent::_(2) . '$db->execute();';

		$code .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " check if there was data returned";
		$code .= PHP_EOL . Indent::_(2) . 'if ($db->getNumRows())';
		$code .= PHP_EOL . Indent::_(2) . '{' . Placefix::_h("DISPATCHER");

		$code .= $this->buildConditionalDecodingBlock($get, $default);

		$code .= PHP_EOL . Indent::_(2) . "}";
		$code .= PHP_EOL . Indent::_(2) . "return false;";

		return $code;
	}

	/**
	 * Build the conditional field processing block if decoding, UIkit, filters, or joins are needed.
	 *
	 * @param  array  $get      The get definition.
	 * @param  array  $default  The default structure.
	 *
	 * @return string  The formatted PHP code string to execute the conditional field logic.
	 * @since  5.1.2
	 */
	private function buildConditionalDecodingBlock(array $get, array $default): string
	{		
		if (!isset($default['code'], $get['key'], $default['as']))
		{
			return '';
		}

		$path = $default['code'] . '.' . $get['key'] . '.' . $default['as'];
		$code = $default['code'];

		$decodeChecker         = $this->sitefielddata->get('decode.' . $path);
		$decodeFilter          = $this->sitefielddecodefilter->get($this->config->build_target . '.' . $path);
		$uikitChecker          = $this->sitefielddata->get('uikit.' . $path);
		$contentprepareChecker = $this->sitefielddata->get('textareas.' . $path);
		$joinedChecker         = $this->otherjoin->get($this->config->build_target . '.' . $code . '.' . $default['as']);

		$decoder         = $this->getDecoderCode($get, $code, $decodeChecker);
		$decoderFilter   = $this->getDecoderFilterCode($get, $code, $decodeFilter);
		$contentPrepare  = $this->getContentPrepareCode($get, $code, $contentprepareChecker);
		$uikit           = $this->getUIKitCode($get, $code, $uikitChecker);
		$joinCode        = $this->getJoinCode($joinedChecker);

		if ($this->hasFieldProcessing($decoder, $decoderFilter, $contentPrepare, $uikit, $joinCode))
		{
			return $this->buildFieldProcessingBlock($decoder, $decoderFilter, $contentPrepare, $uikit, $joinCode);
		}

		return PHP_EOL . Indent::_(3) . 'return $db->loadObjectList();';
	}

	/**
	 * Generate decoder block code for the matched field set.
	 *
	 * @param  array       $get      The get definition.
	 * @param  string      $code     The code name.
	 * @param  array|null  $checker  The decoder rules to apply (if any).
	 *
	 * @return string  The decoder logic code block or an empty string.
	 * @since  5.1.2
	 */
	private function getDecoderCode(array $get, string $code, ?array $checker): string
	{
		return ($checker !== null && ArrayHelper::check($checker))
			? $this->decodecolumn->get($get, $checker, '$item', $code, Indent::_(2))
			: '';
	}

	/**
	 * Generate filter decoder block for field-specific filters.
	 *
	 * @param  array       $get      The get definition.
	 * @param  string      $code     The code name.
	 * @param  array|null  $checker  The filter configuration to apply (if any).
	 *
	 * @return string  The filtered decoder code block or an empty string.
	 * @since  5.1.2
	 */
	private function getDecoderFilterCode(array $get, string $code, ?array $checker): string
	{
		return ($checker !== null && ArrayHelper::check($checker))
			? $this->filtercolumn->get($get, $checker, '$item', '$items[$nr]', $code, Indent::_(2))
			: '';
	}

	/**
	 * Generate content preparation code for specified textarea fields.
	 *
	 * @param  array       $get      The get definition.
	 * @param  string      $code     The code name.
	 * @param  array|null  $checker  The content prepare configuration to apply (if any).
	 *
	 * @return string  The content prepare code block or an empty string.
	 * @since  5.1.2
	 */
	private function getContentPrepareCode(array $get, string $code, ?array $checker): string
	{
		return ($checker !== null && ArrayHelper::check($checker))
			? $this->fieldoncontentprepare->get($get, $checker, '$item', $code, Indent::_(2))
			: '';
	}

	/**
	 * Generate UIkit-specific field formatting code.
	 *
	 * @param  array       $get      The get definition.
	 * @param  string      $code     The code name.
	 * @param  array|null  $checker  The UIkit config for visual formatting (if any).
	 *
	 * @return string  The UIkit loader code block or an empty string.
	 * @since  5.1.2
	 */
	private function getUIKitCode(array $get, string $code, ?array $checker): string
	{
		return ($checker !== null && ArrayHelper::check($checker))
			? $this->uikitloader->get($get, $checker, '$item', $code, Indent::_(2))
			: '';
	}

	/**
	 * Generate and update placeholder code for joined field strings.
	 *
	 * @param  array|null  $joinedChecker  The joined fields configuration (if any).
	 *
	 * @return string  The join code block with placeholders replaced or an empty string.
	 * @since  5.1.2
	 */
	private function getJoinCode(?array $joinedChecker): string
	{
		if ($joinedChecker === null || !ArrayHelper::check($joinedChecker))
		{
			return '';
		}

		$code        = '';
		$placeholders = [
			Placefix::_h('TAB')    => Indent::_(2),
			Placefix::_h('STRING') => '$item',
		];

		foreach ($joinedChecker as $joinedString)
		{
			$code .= $this->placeholder->update($joinedString, $placeholders);
		}

		return $code;
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
	 * @param  string  $joinCode        The join block.
	 *
	 * @return string  The complete loop and return block for $items.
	 * @since  5.1.2
	 */
	private function buildFieldProcessingBlock(
		string $decoder,
		string $decoderFilter,
		string $contentPrepare,
		string $uikit,
		string $joinCode
	): string
	{
		$code  = PHP_EOL . Indent::_(3) . '$items = $db->loadObjectList();';
		$code .= PHP_EOL . PHP_EOL . Indent::_(3) . "//" . Line::_(__LINE__, __CLASS__) . " Convert the parameter fields into objects.";
		$code .= PHP_EOL . Indent::_(3) . 'foreach ($items as $nr => &$item)';
		$code .= PHP_EOL . Indent::_(3) . '{';

		foreach ([$decoder, $decoderFilter, $contentPrepare, $uikit, $joinCode] as $block)
		{
			if (StringHelper::check($block))
			{
				$code .= $block;
			}
		}

		$code .= PHP_EOL . Indent::_(3) . '}';
		$code .= PHP_EOL . Indent::_(3) . 'return $items;';

		return $code;
	}

	/**
	 * Inject cryption script into methods.
	 *
	 * @param   string  $methods  The current methods string.
	 * @param   array   $default  The default structure.
	 * @param   string  $code     The code string.
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function injectCryptionScript(string $methods, array $default, $code): string
	{
		// set the script if it was found
		$Component = $this->contentone->get('Component');
		$script    = '';
		foreach ($this->config->cryption_types as $cryptionType)
		{
			if ($this->sitedecrypt->get("{$cryptionType}.{$code}") !== null)
			{
				if ('expert' !== $cryptionType)
				{
					$script .= PHP_EOL . Indent::_(2) . "//"
						. Line::_(__Line__, __Class__) . " Get the "
						. $cryptionType . " encryption.";
					$script .= PHP_EOL . Indent::_(2) . "\$"
						. $cryptionType . "key = " . $Component
						. "Helper::getCryptKey('"
						. $cryptionType . "');";
					$script .= PHP_EOL . Indent::_(2) . "//"
						. Line::_(__Line__, __Class__)
						. " Get the encryption object.";
					$script .= PHP_EOL . Indent::_(2) . "\$"
						. $cryptionType
						. " = new Super_" . "__99175f6d_dba8_4086_8a65_5c4ec175e61d___Power(\$"
						. $cryptionType . "key);" . PHP_EOL;
				}
				elseif ($this->modelexpertfieldinitiator->exists("{$code}.get"))
				{
					foreach ($this->modelexpertfieldinitiator->get("{$code}.get") as $block)
					{
						$script .= PHP_EOL . Indent::_(2) . implode(
							PHP_EOL . Indent::_(2), $block
						);
					}
				}
			}
		}

		return str_replace(Placefix::_h('CRYPT'), $script, $methods);
	}

	/**
	 * Inject dispatcher code if needed.
	 *
	 * @param   string  $methods  The methods string.
	 * @param   string  $code     The code string.
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function injectDispatcherIfNeeded(string $methods, string $code): string
	{
		if (strpos($methods, (string) Placefix::_h('DISPATCHER')) !== false)
		{
			$methods = str_replace(
				Placefix::_h('DISPATCHER'),
				$this->eventdispatcher->get($code, ''),
				$methods
			);
		}

		return $methods;
	}
}

