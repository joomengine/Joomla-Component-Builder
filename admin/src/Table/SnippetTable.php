<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace VDM\Component\Componentbuilder\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Access\Access as AccessRules;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\DatabaseNotFoundException;
use Joomla\Event\DispatcherInterface;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Snippets Table class
 *
 * @since  1.6
 */
class SnippetTable extends Table implements VersionableTableInterface, TaggableTableInterface, CurrentUserInterface
{
	use TaggableTableTrait;
	use CurrentUserTrait;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver        $db          Database connector object
	 * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
	 *
	 * @param object Database connector object
	 * @since  4.0
	 */
	function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
	{
		// The type alias generally is the internal component name with the
		//   content type. Ex.: com_content.article
		$this->typeAlias = 'com_componentbuilder.snippet';

		// Ensure the params and metadata in json encoded in the bind method
		$this->_jsonEncode = ['params', 'metadata'];

		// Indicates that columns fully support the NULL value in the database
		$this->_supportNullValue = true;

		parent::__construct('#__componentbuilder_snippet', 'id', $db, $dispatcher);
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   array|object  $src     An associative array or object to bind to the Table instance.
	 * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 * @throws  \InvalidArgumentException
	 */
	public function bind($array, $ignore = '')
	{
		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new AccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overload the store method for the Snippet table.
	 *
	 * @param   boolean    Toggle whether null values should be updated.
	 *
	 * @return  boolean  True on success, false on failure.
	 * @since   1.6
	 */
	public function store($updateNulls = true)
	{
		$date   = Factory::getDate()->toSql();
		$userId = $this->getCurrentUser()->id;

		if ($this->id)
		{
			// Existing item
			$this->modified       = $date;
			$this->modified_by    = $userId;
		}
		else
		{
			// New snippet. A snippet created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created)
			{
				$this->created = $date;
			}
			if (empty($this->created_by))
			{
				$this->created_by = $userId;
			}
		}

		if (isset($this->alias))
		{
			// Verify that the alias is unique
			$table = new self($this->getDatabase(), $this->getDispatcher());

			if ($table->load(['alias' => $this->alias]) && ($table->id != $this->id || $this->id == 0))
			{
				$this->setError(Text::_('COM_COMPONENTBUILDER_SNIPPET_ERROR_UNIQUE_ALIAS'));

				if ($table->published === -2)
				{
					$this->setError(Text::_('COM_COMPONENTBUILDER_SNIPPET_ERROR_UNIQUE_ALIAS_TRASHED'));
				}

				return false;
			}
		}

		if (isset($this->url))
		{
			// Convert IDN urls to punycode
			$this->url = PunycodeHelper::urlToPunycode($this->url);
		}

		if (isset($this->website))
		{
			// Convert IDN urls to punycode
			$this->website = PunycodeHelper::urlToPunycode($this->website);
		}

		return parent::store($updateNulls);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     \Joomla\CMS\Table\Table::check
	 * @since   1.5
	 */
	public function check()
	{
		try {
				parent::check();
		} catch (\Exception $e) {
				$this->setError($e->getMessage());
				return false;
		}

		if (isset($this->alias))
		{
			// Generate a valid alias
			$this->generateAlias();

			$table = new self($this->getDatabase(), $this->getDispatcher());

			while ($table->load(['alias' => $this->alias]) && ($table->id != $this->id || $this->id == 0))
			{
				$this->alias = StringHelper::increment($this->alias, 'dash');
			}
		}

		/*
		 * Clean up keywords -- eliminate extra spaces between phrases
		 * and cr (\r) and lf (\n) characters from string.
		 * Only process if not empty.
		  */
		if (!empty($this->metakey))
		{
			// Array of characters to remove.
			$bad_characters = array("\n", "\r", "\"", "<", ">");

			// Remove bad characters.
			$after_clean = StringHelper::str_ireplace($bad_characters, "", $this->metakey);

			// Create array using commas as delimiter.
			$keys = explode(',', $after_clean);
			$clean_keys = [];

			foreach ($keys as $key)
			{
				// Ignore blank keywords.
				if (trim($key))
				{
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->metakey = implode(", ", $clean_keys);
		}

		// Clean up description -- eliminate quotes and <> brackets
		if (!empty($this->metadesc))
		{
			// Only process if not empty
			$bad_characters = array("\"", "<", ">");
			$this->metadesc = StringHelper::str_ireplace($bad_characters, "", $this->metadesc);
		}

		// If we don't have any access rules set at this point just use an empty AccessRules class
		if (!$this->getRules())
		{
			$rules = $this->getDefaultAssetValues('com_componentbuilder.snippet.'.$this->id);
			$this->setRules($rules);
		}

		// Set ordering
		if ($this->published < 0)
		{
			// Set ordering to 0 if state is archived or trashed
			$this->ordering = 0;
		}

		return true;
	}

	/**
	 * Gets the default asset values for a component.
	 *
	 * @param   string  $component  The component asset name to search for
	 * @param   bool    $try        The retry flag
	 *
	 * @return  Rules  The AccessRules object for the asset
	 * @since   2.5.0
	 */
	protected function getDefaultAssetValues(string $component, bool $try = true)
	{
		// Need to find the asset id by the name of the component.
		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__assets'))
			->where($db->quoteName('name') . ' = ' . $db->quote($component));
		$db->setQuery($query);
		$db->execute();
		if ($db->loadRowList())
		{
			// asset already set so use saved rules
			$assetId = (int) $db->loadResult();
			return AccessRules::getAssetRules($assetId); // (TODO) instead of keeping inherited Allowed it becomes Allowed.
		}
		// try again
		elseif ($try)
		{
			$try = explode('.', $component);
			$result =  $this->getDefaultAssetValues($try[0], false);
			if ($result instanceof Rules)
			{
				if (isset($try[1]))
				{
					$_result = (string) $result;
					$_result = json_decode($_result);
					foreach ($_result as $name => &$rule)
					{
						$v = explode('.', $name);
						if ($try[1] !== $v[0])
						{
							// remove since it is not part of this view
							unset($_result->$name);
						}
						else
						{
							// clear the value since we inherit
							$rule = [];
						}
					}
					// check if there are any view values remaining
					if (count( (array) $_result))
					{
						$_result = json_encode($_result);
						$_result = array($_result);
						// Instantiate and return the AccessRules object for the asset rules.
						$rules = new Rules;
						$rules->mergeCollection($_result);

						return $rules;
					}
				}
				return $result;
			}
		}
		return AccessRules::getAssetRules(0);
	}

	/**
	 * Get the type alias for the history table
	 *
	 * The type alias generally is the internal component name with the
	 * content type. Ex.: com_content.article
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   3.10.0
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return $this->getTypeAlias() . '.' . (int) $this->$k;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 *
	 * By default, all assets are registered to the ROOT node with ID, which will default to 1 if none exists.
	 * An extended class can define a table and ID to lookup.  If the asset does not exist it will be created.
	 *
	 * @param   ?Table    $table  A Table object for the asset parent.
	 * @param   ?integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   1.7.0
	 */
	protected function _getAssetParentId(?Table $table = null, $id = null)
	{
		/** @var Asset $assets */
		$assets = new Asset($this->getDatabase(), $this->getDispatcher());
		$rootId = $assets->getRootId();

		// load the componentbuilder asset
		$assets->loadByName('com_componentbuilder');

		return $assets->id ?? $rootId ?? 1;
	}

	/**
	 * Retrieve the database connection in a version-safe and cached manner.
	 *
	 * Compatible with Joomla 4 -> 7:
	 * - If the parent class implements getDatabase() (DatabaseAwareTrait), that is used.
	 * - Otherwise it falls back to getDbo() for legacy versions.
	 * - The detected instance is cached to avoid repeated reflection or lookups.
	 *
	 * @return  DatabaseInterface
	 *
	 * @throws  DatabaseNotFoundException  If the database connection cannot be determined.
	 * @since   5.1.4
	 */
	protected function getDatabase(): DatabaseInterface
	{
		static $cache = null;

		// Return cached connection if available
		if ($cache instanceof DatabaseInterface)
		{
			return $cache;
		}

		// --- Step 1: Use parent::getDatabase() if available (J7+ or DatabaseAwareTrait) ---
		$parent = get_parent_class($this);

		if ($parent && method_exists($parent, 'getDatabase'))
		{
			try
			{
				$db = parent::getDatabase();

				if ($db instanceof DatabaseInterface)
				{
					$cache = $db;
					return $cache;
				}
			}
			catch (\Throwable)
			{
				// Continue to next fallback
			}
		}

		// --- Step 2: Fallback to getDbo() (J3-J6 style) ---
		if (method_exists($this, 'getDbo'))
		{
			try
			{
				$db = $this->getDbo();

				if ($db instanceof DatabaseInterface)
				{
					$cache = $db;
					return $cache;
				}
			}
			catch (\Throwable)
			{
				// Continue to next fallback
			}
		}

		// --- Step 3: No valid database found ---
		throw new DatabaseNotFoundException('Database not set in ' . static::class);
	}

	/**
	 * This view does not actually have an alias
	 *
	 * @return  bool
	 */
	public function generateAlias()
	{
		return false;
	}
}
