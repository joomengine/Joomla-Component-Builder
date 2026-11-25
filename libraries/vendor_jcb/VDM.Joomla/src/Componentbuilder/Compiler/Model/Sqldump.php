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

namespace VDM\Joomla\Componentbuilder\Compiler\Model;


use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface as JoomlaDatabase;
use VDM\Joomla\Componentbuilder\Compiler\Registry;
use VDM\Joomla\Database\QuoteTrait;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * SQL Dump Class
 * 
 * @since 3.2.0
 */
class Sqldump
{
	/**
	 * Function to quote values
	 *
	 * @since 5.1.1
	 */
	use QuoteTrait;

	/**
	 * The compiler registry
	 *
	 * @var    Registry
	 * @since 3.2.0
	 */
	protected Registry $registry;

	/**
	 * Database object to query local DB
	 *
	 * @var    JoomlaDatabase
	 * @since 3.2.0
	 **/
	protected JoomlaDatabase $db;

	/**
	 * Constructor
	 *
	 * @param Registry             $registry  The compiler registry object.
	 * @param JoomlaDatabase|null  $db        The joomla database object.
	 * @since 3.2.0
	 */
	public function __construct(Registry $registry, ?JoomlaDatabase $db = null)
	{
		$this->registry = $registry;
		$this->db = $db ?: Factory::getContainer()->get(JoomlaDatabase::class);
	}

	/**
	 * Generate SQL dump for given view data.
	 *
	 * @param   array   $tables     Tables configuration array.
	 * @param   string  $view       Target view name.
	 * @param   string  $viewGuid   Unique GUID for view (used in registry path).
	 *
	 * @return  string|null         SQL dump or null on failure.
	 * @since   3.2.0
	 */
	public function get(array $tables, string $view, string $viewGuid): ?string
	{
		if (empty($tables) || !$this->shouldBuildDump($viewGuid))
		{
			return null;
		}

		$query = $this->db->getQuery(true);
		$runQuery = false;
		$alias = 'a';
		$fieldsAdded = false;

		foreach ($tables as $tableConfig)
		{
			if (empty($tableConfig['table']) || empty($tableConfig['sourcemap']))
			{
				continue;
			}

			$fieldMappings = $this->parseFieldMappings($tableConfig['sourcemap'], $alias);

			if ($alias === 'a')
			{
				if (!empty($fieldMappings['select']))
				{
					$query->select($this->db->quoteName($fieldMappings['select'], $fieldMappings['alias']));
					$query->from($this->db->quoteName('#__' . $tableConfig['table'], $alias));
					$this->applyWhereFilter($query, $viewGuid);
					$fieldsAdded = true;
					$runQuery = true;
				}
			}
			else
			{
				$this->applyJoins($query, $tableConfig['table'], $alias, $fieldMappings['joins']);
				if (!empty($fieldMappings['select']))
				{
					$query->select($this->db->quoteName($fieldMappings['select'], $fieldMappings['alias']));
					$fieldsAdded = true;
				}
			}

			$alias++;
		}

		if ($runQuery && $fieldsAdded)
		{
			try {
				$this->db->setQuery($query)->execute();

				if ($this->db->getNumRows())
				{
					$data = $this->db->loadObjectList();
					return $this->buildSqlDump($view, $data);
				}
			} catch (\Throwable $e) {
				// Log or handle exception if needed
			}
		}

		return null;
	}

	/**
	 * Determine if a dump should be built.
	 *
	 * @param   string  $viewGuid
	 *
	 * @return  bool
	 * @since   5.1.1
	 */
	protected function shouldBuildDump(string $viewGuid): bool
	{
		return (bool) $this->registry->get("builder.sql_tweak.{$viewGuid}.add", true);
	}

	/**
	 * Apply optional WHERE clause if set in registry.
	 *
	 * @param   $query
	 * @param   string     $viewGuid
	 *
	 * @return  void
	 * @since   5.1.1
	 */
	protected function applyWhereFilter($query, string $viewGuid): void
	{
		if ($ids = $this->registry->get("builder.sql_tweak.{$viewGuid}.where"))
		{
			$query->where("a.id IN ({$ids})");
		}
	}

	/**
	 * Parse sourcemap lines into SELECT and JOIN definitions.
	 *
	 * @param   string  $map
	 * @param   string  $alias
	 *
	 * @return  array{select: string[], alias: string[], joins: array<int, array{from: string, to: string}>}
	 * @since   5.1.1
	 */
	protected function parseFieldMappings(string $map, string $alias): array
	{
		$lines = explode(PHP_EOL, trim($map));
		$select = [];
		$aliasFields = [];
		$joins = [];

		foreach ($lines as $line)
		{
			$line = trim($line);

			if (str_contains($line, '=>'))
			{
				[$from, $to] = array_map('trim', explode('=>', $line));
				$select[] = "{$alias}.{$from}";
				$aliasFields[] = $to;
			}
			elseif (str_contains($line, '=='))
			{
				[$left, $right] = array_map('trim', explode('==', $line));
				$joins[] = ['from' => $left, 'to' => $right];
			}
		}

		return [
			'select' => $select,
			'alias' => $aliasFields,
			'joins' => $joins,
		];
	}

	/**
	 * Apply JOINs to the query.
	 *
	 * @param   $query
	 * @param   string           $table
	 * @param   string           $alias
	 * @param   array            $joins
	 *
	 * @return  void
	 * @since   5.1.1
	 */
	protected function applyJoins($query, string $table, string $alias, array $joins): void
	{
		foreach ($joins as $join)
		{
			$query->join(
				'LEFT',
				$this->db->quoteName("#__{$table}", $alias) . ' ON (' .
				$this->db->quoteName("a.{$join['from']}") . ' = ' .
				$this->db->quoteName("{$alias}.{$join['to']}") . ')'
			);
		}
	}

	/**
	 * Build the SQL INSERT DUMP statement from data.
	 *
	 * Automatically chunks large data sets into multiple INSERT statements
	 * to improve database import performance and avoid query size limits.
	 *
	 * @param   string         $view  The table view name (without prefix).
	 * @param   array<object>  $data  The dataset to dump.
	 *
	 * @return  string  The full SQL dump string.
	 * @since   5.1.2
	 */
	protected function buildSqlDump(string $view, array $data): string
	{
		// No data to process
		if (empty($data))
		{
			return Indent::_(0) . '--' . Line::_(__LINE__, __CLASS__)
				. " No data available for `{$view}`" . PHP_EOL;
		}

		// Define table and fields
		$tableName = "#__" . Placefix::_("component") . "_{$view}";
		$fields    = array_keys((array) $data[0]);

		// Header
		$header = Indent::_(0) . '--' . Line::_(__LINE__, __CLASS__) . ' --------------------------------------------------------'
			. PHP_EOL . Indent::_(0) . '--' . Line::_(__LINE__, __CLASS__) . " Dumping data for table `{$tableName}`"
			. PHP_EOL . Indent::_(0) . '--' . Line::_(__LINE__, __CLASS__) . ' --------------------------------------------------------';

		// Base insert prefix
		$insertPrefix = "INSERT INTO `{$tableName}` (" . implode(', ', array_map([$this->db, 'quoteName'], $fields)) . ') VALUES';

		// Determine optimal chunk size based on dataset size
		$totalRows  = count($data);
		$chunkSize  = $this->determineOptimalChunkSize($totalRows);

		// Split data into chunks
		$chunks = array_chunk($data, $chunkSize);

		// Build SQL dump
		$sqlDump = $header . PHP_EOL;
		foreach ($chunks as $i => $chunk)
		{
			$rows = array_map(function ($row)
			{
				$values = array_map([$this, 'escape'], (array) $row);

				return '(' . implode(', ', $values) . ')';
			}, $chunk);

			$sqlDump .= PHP_EOL . Indent::_(0) . '--' . Line::_(__LINE__, __CLASS__)
				. sprintf(' Batch %d of %d (%d rows)', $i + 1, count($chunks), count($chunk))
				. PHP_EOL . Indent::_(0) . $insertPrefix
				. PHP_EOL . Indent::_(0) . implode(',' . PHP_EOL . Indent::_(0), $rows) . ';' . PHP_EOL;
		}

		return $sqlDump . PHP_EOL;
	}

	/**
	 * Determine optimal chunk size for SQL insert batching.
	 *
	 * @param   int  $totalRows  Total number of rows to dump.
	 *
	 * @return  int  Recommended chunk size.
	 * @since   5.1.2
	 */
	protected function determineOptimalChunkSize(int $totalRows): int
	{
		// General MySQL safe limits: 1MB per query or ~1,000 rows
		// Adjust dynamically based on dataset size.
		if ($totalRows > 100000)
		{
			return 1000; // Large dataset
		}
		elseif ($totalRows > 10000)
		{
			return 500; // Medium dataset
		}
		elseif ($totalRows > 1000)
		{
			return 300; // Smaller large set
		}

		return $totalRows; // For small sets, single insert is fine
	}

	/**
	 * Escape SQL value for safe dump using strict quoting rules.
	 *
	 * @param   mixed  $value  The value to escape.
	 *
	 * @return  mixed         Escaped SQL-safe literal or quoted string.
	 * @since   3.2.0
	 */
	protected function escape($value)
	{
		if (is_array($value))
		{
			return implode(', ', array_map([$this, 'escape'], $value));
		}

		return $this->quote($value);
	}
}

