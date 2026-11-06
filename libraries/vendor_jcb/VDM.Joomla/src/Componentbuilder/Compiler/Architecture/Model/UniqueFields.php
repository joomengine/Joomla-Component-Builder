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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Model;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Builder\DatabaseTables;
use VDM\Joomla\Componentbuilder\Compiler\Builder\DatabaseUniqueGuid;
use VDM\Joomla\Componentbuilder\Compiler\Builder\DatabaseUniqueKeys;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Model Unique Fields Class.
 * 
 * Builds the statements an admin model runs to keep the fields a view was
 * built to hold unique from clashing with what is already stored.
 * 
 * @since 6.1.7
 */
final class UniqueFields
{
	/**
	 * The Database Unique Guid Builder Class.
	 *
	 * @var   DatabaseUniqueGuid
	 * @since 6.1.7
	 */
	protected DatabaseUniqueGuid $databaseuniqueguid;

	/**
	 * The Database Unique Keys Builder Class.
	 *
	 * @var   DatabaseUniqueKeys
	 * @since 6.1.7
	 */
	protected DatabaseUniqueKeys $databaseuniquekeys;

	/**
	 * The compiled database column definitions.
	 *
	 * @var    DatabaseTables
	 * @since  6.2.1
	 */
	protected DatabaseTables $databasetables;

	/**
	 * The component language configuration.
	 *
	 * @var    Config
	 * @since  6.2.1
	 */
	protected Config $config;

	/**
	 * The language registry for generated validation messages.
	 *
	 * @var    Language
	 * @since  6.2.1
	 */
	protected Language $language;

	/**
	 * Constructor.
	 *
	 * @param DatabaseUniqueGuid $databaseuniqueguid The Database Unique Guid Builder Class.
	 * @param DatabaseUniqueKeys $databaseuniquekeys The Database Unique Keys Builder Class.
	 * @param DatabaseTables     $databasetables     The compiled database column definitions.
	 * @param Config             $config             The component language configuration.
	 * @param Language           $language           The language registry.
	 *
	 * @since 6.1.7
	 */
	public function __construct(DatabaseUniqueGuid $databaseuniqueguid,
		DatabaseUniqueKeys $databaseuniquekeys,
		DatabaseTables $databasetables,
		Config $config,
		Language $language)
	{
		$this->databaseuniqueguid = $databaseuniqueguid;
		$this->databaseuniquekeys = $databaseuniquekeys;
		$this->databasetables = $databasetables;
		$this->config = $config;
		$this->language = $language;
	}

	/**
	 * Build the unique field statements of a view.
	 *
	 * A view with nothing to keep unique gets nothing.
	 *
	 * @param   string  $view  The single view name.
	 *
	 * @return  string  The statements, or nothing when the view holds no unique field.
	 *
	 * @since   6.1.7
	 */
	public function get(&$view): string
	{
		$fields   = [];
		$fields[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
		$fields[] = Indent::_(1)
			. " * Method to get the unique fields of this table.";
		$fields[] = Indent::_(1) . " *";
		$fields[] = Indent::_(1)
			. " * @return  mixed  An array of field names, boolean false if none is set.";
		$fields[] = Indent::_(1) . " *";
		$fields[] = Indent::_(1) . " * @since   3.0";
		$fields[] = Indent::_(1) . " */";
		$fields[] = Indent::_(1) . "protected function getUniqueFields()";
		$fields[] = Indent::_(1) . "{";
		if ($this->databaseuniquekeys->exists($view))
		{
			// if guid should also be added
			if ($this->databaseuniqueguid->exists($view))
			{
				$fields[] = Indent::_(2) . "return array('" . implode(
						"','", $this->databaseuniquekeys->get($view)
					) . "', 'guid');";
			}
			else
			{
				$fields[] = Indent::_(2) . "return array('" . implode(
						"','", $this->databaseuniquekeys->get($view)
					) . "');";
			}
		}
		// if only GUID is found
		elseif ($this->databaseuniqueguid->exists($view))
		{
			$fields[] = Indent::_(2) . "return array('guid');";
		}
		else
		{
			$fields[] = Indent::_(2) . "return false;";
		}
		$fields[] = Indent::_(1) . "}";

		// Keep the field-name contract and emit schema metadata for safe copy handling.
		return implode(PHP_EOL, $fields) . $this->getSettings($view);
	}

	/**
	 * Build the type, character limit and nullability used by unique generation.
	 *
	 * Include non-unique columns because custom model code and batch title
	 * handling can also call generateUnique(). Unknown types stay non-incrementable.
	 *
	 * @param   string  $view  The single view code name.
	 *
	 * @return  string  The generated metadata method.
	 * @since   6.2.1
	 */
	private function getSettings(string $view): string
	{
		$this->registerMessages();
		$fields = [];
		$fields[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
		$fields[] = Indent::_(1) . " * Database column settings used when generating unique values.";
		$fields[] = Indent::_(1) . " *";
		$fields[] = Indent::_(1) . " * @return  array  Column types, character limits and nullability.";
		$fields[] = Indent::_(1) . " * @since   6.2.1";
		$fields[] = Indent::_(1) . " */";
		$fields[] = Indent::_(1) . "protected function getUniqueFieldSettings()";
		$fields[] = Indent::_(1) . "{";
		$fields[] = Indent::_(2) . "return [";

		foreach ($this->databasetables->get($view, []) as $field => $column)
		{
			$type = strtoupper(trim((string) ($column['type'] ?? '')));
			$length = $column['lenght'] ?? null;
			if ($length === 'Other')
			{
				$length = $column['lenght_other'] ?? null;
			}
			$length = trim((string) $length);
			$length = ctype_digit($length) && (int) $length > 0 ? (int) $length : null;
			$nullable = strtoupper(trim((string) ($column['null_switch'] ?? ''))) === 'NULL';
			$fields[] = Indent::_(3) . var_export($field, true) . " => ["
				. "'type' => " . var_export($type, true)
				. ", 'length' => " . ($length === null ? 'null' : $length)
				. ", 'nullable' => " . ($nullable ? 'true' : 'false') . "],";
		}

		$fields[] = Indent::_(2) . "];";
		$fields[] = Indent::_(1) . "}";

		// Only views with a GUID column need to resolve and ship the GUID Power.
		if ($this->databasetables->exists($view . '.guid'))
		{
			$fields[] = PHP_EOL . Indent::_(1) . "/**";
			$fields[] = Indent::_(1) . " * Generate a valid GUID candidate for a copied record.";
			$fields[] = Indent::_(1) . " *";
			$fields[] = Indent::_(1) . " * @return  string  A new GUID candidate.";
			$fields[] = Indent::_(1) . " * @since   6.2.1";
			$fields[] = Indent::_(1) . " */";
			$fields[] = Indent::_(1) . "protected function generateUniqueGuid()";
			$fields[] = Indent::_(1) . "{";
			$fields[] = Indent::_(2) . "return (string) Super_"
				. "__9c513baf_b279_43fd_ae29_a585c8cbc4f0___Power::get();";
			$fields[] = Indent::_(1) . "}";
		}

		return implode(PHP_EOL, $fields);
	}

	/**
	 * Register copy failures for administrator and site edit models.
	 *
	 * @return  void
	 * @since   6.2.1
	 */
	private function registerMessages(): void
	{
		$messages = [
			'UNIQUE_FIELD_CONFLICT' => 'The value for "%s" already exists. Enter a different value before copying.',
			'UNIQUE_FIELD_LIMIT' => 'Could not generate a unique value for "%s" after 100 attempts. Enter a different value before copying.',
			'UNIQUE_FIELD_LENGTH' => 'The copied value for "%s" exceeds the column length. Enter a shorter value before copying.',
			'UNIQUE_FIELD_ERROR' => 'Could not verify a unique value for "%s". The copy was not saved.',
		];

		foreach (['admin', 'site'] as $target)
		{
			foreach ($messages as $key => $message)
			{
				$this->language->set($target, $this->config->lang_prefix . '_' . $key, $message);
			}
		}
	}
}

