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

namespace VDM\Joomla\Componentbuilder\Search\Database;


use Joomla\CMS\Factory as JoomlaFactory;
use VDM\Joomla\Componentbuilder\Search\Factory;
use VDM\Joomla\Componentbuilder\Search\Config;
use VDM\Joomla\Componentbuilder\Table;
use VDM\Joomla\Componentbuilder\Search\Model\Load as Model;
use VDM\Joomla\Database\Load as Database;
use VDM\Joomla\Componentbuilder\Search\Interfaces\LoadInterface;


/**
 * Search Database Load
 * 
 * @since 3.2.0
 */
class Load implements LoadInterface
{
	/**
	 * Bundle Size
	 *
	 * @var    int
	 * @since 3.2.0
	 */
	protected int $bundle = 300;

	/**
	 * Search Config
	 *
	 * @var    Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * Search Table
	 *
	 * @var    Table
	 * @since 3.2.0
	 */
	protected Table $table;

	/**
	 * Search Model
	 *
	 * @var    Model
	 * @since 3.2.0
	 */
	protected Model $model;

	/**
	 * Database load class
	 *
	 * @var    Database
	 * @since 3.2.0
	 **/
	protected Database $load;

	/**
	 * Constructor
	 *
	 * @param Config|null      $config     The search config object.
	 * @param Table|null       $table      The search table object.
	 * @param Model|null       $model      The search get model object.
	 * @param Database|null    $load       The database object.
	 *
	 * @since 3.2.0
	 */
	public function __construct(?Config $config = null, ?Table $table = null,
		?Model $model = null, ?Database $load = null)
	{
		$this->config = $config ?: Factory::_('Config');
		$this->table = $table ?: Factory::_('Table');
		$this->model = $model ?: Factory::_('Load.Model');
		$this->load = $load ?: Factory::_('Load');
	}

	/**
	 * Get a value from a given table
	 *          Example: $this->value(23, 'value_key', 'table_name');
	 *
	 * @param   int            $id       The item ID
	 * @param   string         $field    The field key
	 * @param   string|null    $table    The table
	 *
	 * @return  mixed
	 * @since 3.2.0
	 */
	public function value(int $id, string $field, ?string $table = null)
	{
		// load the table
		if (empty($table))
		{
			$table = $this->config->table_name;
		}

		// check if this is a valid table
		if ($id > 0 && $this->table->exist($table, $field) &&
			($value = $this->load->value(
				["a.{$field}" => $field],  // select
				['a' => $table],  // tables
				['a.id' => $id]  // where
			)) !== null)
		{
			return $this->model->value(
				$value, $field, $table
			);
		}

		return null;
	}

	/**
	 * Get values from a given table
	 *          Example: $this->item(23, 'table_name');
	 *
	 * @param   int           $id      The item ID
	 * @param   string| null  $table   The table
	 *
	 * @return  object|null
	 * @since 3.2.0
	 */
	public function item(int $id, ?string $table = null): ?object
	{
		// load the table
		if (empty($table))
		{
			$table = $this->config->table_name;
		}

		// check if this is a valid table
		if ($id > 0 && ($fields = $this->setDatabaseFields($table)) !== null &&
			($item = $this->load->item(
				$fields,  // select
				['a' => $table],  // tables
				['a.id' => $id]  // where
			)) !== null)
		{
			// return found values
			return $this->model->item($item, $table);
		}

		return null;
	}

	/**
	 * Get values from a given table
	 *          Example: $this->items('table_name');
	 *
	 * @param   string|null   $table   The table
	 * @param   int           $bundle  The bundle to return (0 = all)
	 *
	 * @return  array|null
	 * @since 3.2.0
	 */
	public function items(?string $table = null, int $bundle = 0): ?array
	{
		// load the table
		if (empty($table))
		{
			$table = $this->config->table_name;
		}

		// check if this is a valid table
		if ( ($fields = $this->setDatabaseFields($table)) !== null)
		{
			// add a key to the selection return set
			$fields['key'] = 'id';
			// get the title value
			$title = $this->table->titleName($table);
			// set order
			$order = ['a.' . $title => 'ASC'];
			// select all
			$where = null;
			// no limit
			$limit = null;

			// add limitation and pagination
			if ($bundle > 0)
			{
				// Continue on the same ID axis used by the last-record cursor.
				$order = ['a.id' => 'ASC'];
				// get the incremental number
				$where = ['a.id' => [
						'operator' => '>=',
						'value' => $this->next($table, $bundle)
						]
					];

				// only return a limited number
				$limit = $this->bundle;
			}

			if (($items = $this->load->items(
				$fields,  // select
				['a' => $table],  // tables
				$where,
				$order,
				$limit
			)) !== null)
			{
				// return found values
				return $this->model->items($items, $table);
			}
		}

		return null;
	}

	/**
	 * Get next id to call
	 *
	 * @param   string    $table   The table
	 * @param   int       $bundle  The bundle to return
	 *
	 * @return  int
	 * @since 3.2.0
	 */
	protected function next(string $table, int $bundle): int
	{
		if ($bundle == 1 || $bundle == 0)
		{
			return 1;
		}

		if (($number = $this->model->last($table)) !== null)
		{
			return $number + 1;
		}

		return $this->incremental($bundle);
	}

	/**
	 * Get Incremental number where the set starts
	 *
	 * @param   int    $bundle  The bundle to return
	 *
	 * @return  int
	 * @since 3.2.0
	 */
	protected function incremental(int $bundle): int
	{
		// just in case
		if ($bundle == 1 || $bundle == 0)
		{
			return 1;
		}

		/** Number two set starts at 301
		 * 2 x 300 = 600
		 * 600 - 300 = 300
		 * 300 + 1 = 301 <--
		 *  Number five set starts at 1201
		 * 5 x 300 = 1500
		 * 1500 - 300 = 1200
		 * 1200 + 1 = 1201 <--
		 **/
		return (($bundle * $this->bundle) - $this->bundle) + 1;
	}

	/**
	 * Get Fields ready to use in database call
	 *
	 * @param   string    $table  The table which fields we want to get
	 * @param   string    $key    The table key to which the fields belong
	 * @param   bool     $addId  The switch to add ID
	 *
	 * @return  array|null
	 * @since 3.2.0
	 */
	protected function setDatabaseFields(string $table, string $key = 'a', bool $addId = true): ?array
	{
		if (($fields = $this->table->fields($table)) !== null)
		{
			// add the ID
			if ($addId)
			{
				array_unshift($fields , 'id');
			}

			$bucket = [];
			foreach ($fields as $field)
			{
				$bucket[$key . '.' . $field] = $field;
			}

			return $bucket;
		}

		return null;
	}
}

