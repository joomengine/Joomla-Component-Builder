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

namespace VDM\Joomla\Componentbuilder\Compiler\Language;


use Joomla\Database\DatabaseInterface;


/**
 * Compiler Insert New Language Strings
 * 
 * @since 5.0.2
 */
final class Insert
{
	/**
	 * The items to insert
	 *
	 * @var    array
	 * @since  5.0.2
	 **/
	protected array $items = [];

	/**
	 * Joomla Database Class.
	 *
	 * @var   DatabaseInterface
	 * @since 5.1.2
	 **/
	protected DatabaseInterface $db;

	/**
	 * Constructor.
	 *
	 * @since 5.0.2
	 */
	public function __construct(DatabaseInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * Sets a value in a multi-dimensional array within the `items` property.
	 *
	 * This method ensures that the array structure is properly initialized before
	 * inserting the value at the specified target and counter position.
	 *
	 * @param string $target    The key in the first dimension of the array.
	 * @param int	 $counter   The key in the second dimension of the array.
	 * @param string $value     The value to be inserted.
	 *
	 * @return void
	 * @since 5.0.2
	 */
	public function set(string $target, int $counter, string $value): void
	{
		// Ensure the target key exists in the items array
		if (!isset($this->items[$target]))
		{
			$this->items[$target] = [];
		}

		// Ensure the counter key exists within the target array
		if (!isset($this->items[$target][$counter]))
		{
			$this->items[$target][$counter] = [];
		}

		// Append the value to the array at the specified target and counter position
		$this->items[$target][$counter][] = $this->db->quote($value);
	}

	/**
	 * Store the language placeholders and execute the database insert operation.
	 *
	 * This method checks if the target key exists in the `items` array and if the count
	 * of its elements meets or exceeds the specified threshold (`$when`). If the conditions
	 * are met, it constructs and executes a database insert query to store the language
	 * placeholders. The array of items for the target is then cleared.
	 *
	 * @param string $target The target extension type.
	 * @param int    $when   The threshold count to determine when to update. Default is 1.
	 *
	 * @return void
	 * @since  5.0.2
	 */
	public function execute(string $target, int $when = 1): void
	{
		if (isset($this->items[$target]) && count((array) $this->items[$target]) >= $when)
		{
			// Create a new query object.
			$query = $this->db->getQuery(true);
			$continue = false;

			// Insert columns.
			$columns = array($target, 'source', 'published', 'created', 'created_by', 'version', 'access', 'translation');

			// Prepare the insert query.
			$query->insert($this->db->quoteName('#__componentbuilder_language_translation'));
			$query->columns($this->db->quoteName($columns));

			foreach ($this->items[$target] as $values)
			{
				if (count((array) $values) == 7)
				{
					$values[] = $this->db->quote('');
					$query->values(implode(',', $values));
					$continue = true;
				}
				else
				{
					// TODO: Handle line mismatch, this should not happen.
				}
			}

			// Clear the values array.
			$this->items[$target] = [];

			if (!$continue)
			{
				// Ensure we don't continue if no values were loaded.
				return;
			}

			// Set the query using our newly populated query object and execute it.
			$this->db->setQuery($query);
			$this->db->execute();
		}
	}
}

