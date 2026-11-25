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
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\Queries;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryFilter;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryWhere;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryOrder;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryGroup;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Dynamic Get List Query
 * 
 * @since 5.1.2
 */
class ListQuery
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 5.1.2
	 */
	protected Config $config;

	/**
	 * The Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 5.1.2
	 */
	protected Dispenser $dispenser;

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
	 * Fresh-query method emitted by this target renderer.
	 *
	 * @var   string
	 * @since 6.2.0
	 */
	protected string $queryMethod = 'getQuery(true)';

	/**
	 * Constructor.
	 *
	 * @param Config        $config        The Config Class.
	 * @param Dispenser     $dispenser     The Dispenser Class.
	 * @param Queries       $queries       The Queries Class.
	 * @param QueryFilter   $queryfilter   The QueryFilter Class.
	 * @param QueryWhere    $querywhere    The QueryWhere Class.
	 * @param QueryOrder    $queryorder    The QueryOrder Class.
	 * @param QueryGroup    $querygroup    The QueryGroup Class.
	 *
	 * @since 5.1.2
	 */
	public function __construct(Config $config, Dispenser $dispenser, Queries $queries,
		QueryFilter $queryfilter, QueryWhere $querywhere,
		QueryOrder $queryorder, QueryGroup $querygroup)
	{
		$this->config = $config;
		$this->dispenser = $dispenser;
		$this->queries = $queries;
		$this->queryfilter = $queryfilter;
		$this->querywhere = $querywhere;
		$this->queryorder = $queryorder;
		$this->querygroup = $querygroup;
	}

	/**
	 * Build the custom view list query code block.
	 *
	 * @param  object  $get     The GET configuration object.
	 * @param  string  $code    The current code name.
	 * @param  bool    $return  Whether to include `return $query;`.
	 *
	 * @return string  The full PHP code block as a string.
	 * @since  5.1.2
	 */
	public function get($get, $code, $return = true): string
	{
		if (!ObjectHelper::check($get))
		{
			return PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. "add your custom code here.";
		}

		if (($get->pagination ?? 0) == 1)
		{
			$listQuery = PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " Get a db connection.";
		}
		else
		{
			$listQuery = PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " Make sure all records load, since no pagination allowed.";
			$listQuery .= PHP_EOL . Indent::_(2)
				. "\$this->setState('list.limit', 0);";
			$listQuery .= PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " Get a db connection.";
		}

		if ($this->config->get('joomla_version', 3) == 3)
		{
			$listQuery .= PHP_EOL . Indent::_(2) . "\$db = Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getDbo();";
		}
		else
		{
			$listQuery .= PHP_EOL . Indent::_(2) . "\$db = \$this->getDatabase();";
		}

		$listQuery .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
			. " Create a new query object.";
		$listQuery .= PHP_EOL . Indent::_(2) . "\$query = \$db->{$this->queryMethod};";

		// Main GET query
		$listQuery .= $this->queries->get($get->main_get, $code);

		// Custom script
		$listQuery .= $this->dispenser->get(
			$this->config->build_target . '_php_getlistquery',
			$code,
			'',
			PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " Filtering.",
			true
		);

		// Optional parts
		$listQuery .= $this->applyAdditionalQueries($get, $code);

		if ($return)
		{
			$listQuery .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__)
				. " return the query object"
				. PHP_EOL . Indent::_(2) . "return \$query;";
		}

		return $listQuery;
	}

	/**
	 * Apply additional query parts (filter, where, order, group).
	 *
	 * @param  object  $get    The get object values.
	 * @param  string  $code   The current code name.
	 *
	 * @return string
	 * @since  5.1.2
	 */
	private function applyAdditionalQueries(object $get, string $code): string
	{
		$listQuery = '';

		// Optional parts
		foreach (['queryfilter' => 'filter', 'querywhere' => 'where', 'queryorder' => 'order', 'querygroup' => 'group'] as $queryType => $field)
		{
			if (isset($get->{$field}))
			{
				$listQuery .= $this->{$queryType}->get($get->{$field}, $code);
			}
		}

		return $listQuery;
	}
}

