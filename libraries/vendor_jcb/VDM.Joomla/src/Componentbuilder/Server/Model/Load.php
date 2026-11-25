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

namespace VDM\Joomla\Componentbuilder\Server\Model;


use Joomla\Registry\Registry;
use VDM\Joomla\Componentbuilder\Compiler\Factory;
use VDM\Joomla\Componentbuilder\Crypt;
use VDM\Joomla\Componentbuilder\Table;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Interfaces\ModelInterface;
use VDM\Joomla\Abstraction\Model;


/**
 * Server Model Load Class
 * 
 * @since 3.2.0
 */
class Load extends Model implements ModelInterface
{
	/**
	 * Decryption Class
	 *
	 * @var    Crypt
	 * @since 3.2.0
	 */
	protected Crypt $crypt;

	/**
	 * Constructor
	 *
	 * @param Crypt|null      $crypt     The core crypt object.
	 * @param Table|null      $table     The search table object.
	 *
	 * @since 3.2.0
	 */
	public function __construct(?Crypt $crypt = null, ?Table $table = null)
	{
		parent::__construct($table ?? Factory::_('Table'));

		$this->crypt = $crypt ?: Factory::_('Crypt');
	}

	/**
	 * Model the value
	 *          Example: $this->value(value, 'value_key', 'table_name');
	 *
	 * @param   mixed          $value    The value to model
	 * @param   string         $field    The field key
	 * @param   string|null    $table    The table
	 *
	 * @return  mixed
	 * @since 3.2.0
	 */
	public function value($value, string $field, ?string $table = null)
	{
		// load the table
		if (empty($table))
		{
			$table = $this->getTable();
		}

		// check if this is a valid table
		if (StringHelper::check($value) && ($store = $this->table->get($table, $field, 'store')) !== null)
		{
			// open the value based on the store method
			switch($store)
			{
				case 'basic_encryption':
					$value = $this->crypt->decrypt($value, 'basic');
					if ($value !== null)
					{
						$value = rtrim($value, "\0");
					}
				break;
				case 'medium_encryption':
					$value = $this->crypt->decrypt($value, 'medium');
					if ($value !== null)
					{
						$value = rtrim($value, "\0");
					}
				break;
				case 'base64':
					$value = base64_decode((string) $value);
				break;
				case 'json':
					// check if there is a json string
					if (JsonHelper::check($value))
					{
						$registry = new Registry;
						$registry->loadString($value);
						$value = $registry->toArray();
					}
				break;
				default:
					if ($this->crypt->exist($store))
					{
						$value = $this->crypt->decrypt($value, $store);
					}
				break;
			}
		}

		return $value;
	}

	/**
	 * Validate before the value is modelled
	 *
	 * @param   mixed         $value   The field value
	 * @param   string|null   $field     The field key
	 * @param   string|null   $table   The table
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	protected function validateBefore(&$value, ?string $field = null, ?string $table = null): bool
	{
		// remove none
		return true;
	}

	/**
	 * Validate after the value is modelled
	 *
	 * @param   mixed         $value   The field value
	 * @param   string|null   $field     The field key
	 * @param   string|null   $table   The table
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	protected function validateAfter(&$value, ?string $field = null, ?string $table = null): bool
	{
		return true;
	}

	/**
	 * Get the current active table
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function getTable(): string
	{
		return 'server';
	}
}

