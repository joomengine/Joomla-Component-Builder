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
namespace VDM\Component\Componentbuilder\Administrator\Helper;

// The power autoloader for this project (JPATH_ADMINISTRATOR) area.
$power_autoloader = JPATH_ADMINISTRATOR . '/components/com_componentbuilder/src/Helper/PowerloaderHelper.php';
if (file_exists($power_autoloader))
{
	require_once $power_autoloader;
}

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Access\Rules as AccessRules;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Language;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Archive\Archive;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\GuidHelper;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Utilities\StringHelper as UtilitiesStringHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\FileHelper;
use VDM\Joomla\Utilities\Component\Helper;
use VDM\Joomla\Utilities\GetHelper;
use VDM\Joomla\Utilities\SessionHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\FieldHelper;
use VDM\Joomla\Componentbuilder\Compiler\Factory as CompilerFactory;
use VDM\Joomla\Utilities\Base64Helper;
use VDM\Joomla\FOF\Encrypt\AES;
use VDM\Joomla\Utilities\DateHelper;
use VDM\Joomla\Utilities\String\ClassfunctionHelper;
use VDM\Joomla\Utilities\String\FieldHelper as StringFieldHelper;
use VDM\Joomla\Utilities\String\TypeHelper;
use VDM\Joomla\Utilities\String\NamespaceHelper;
use VDM\Joomla\Utilities\MathHelper;
use VDM\Joomla\Utilities\String\PluginHelper;
use VDM\Joomla\Data\Factory as DataFactory;
use VDM\Joomla\Componentbuilder\Utilities\Permitted\Actions;
use VDM\Joomla\Utilities\FormHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Client\FtpClient;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder component helper.
 *
 * @since   3.0
 */
abstract class ComponentbuilderHelper
{
	/**
	 * Composer Switch
	 *
	 * @var      array
	 */
	protected static $composer = [];

	/**
	 * The Main Active Language
	 *
	 * @var      string
	 */
	public static $langTag;

	/**
	*	The Global Admin Event Method.
	**/
	public static function globalEvent($document)
	{

	}


	/**
	* Locked Libraries (we can not have these change)
	**/
	public static $libraryNames = [1 => 'No Library', 2 => 'Bootstrap v4', 3 => 'Uikit v3', 4 => 'Uikit v2', 5 => 'FooTable v2', 6 => 'FooTable v3'];

	/**
	* Array of php fields Allowed (16)
	**/
	public static $phpFieldArray = ['', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'x', 'HEADER'];

	/**
	* The global params
	**/
	protected static $params = false;

	/**
	* The global updater
	**/
	protected static $globalUpdater = array();

	/**
	* The local company details
	**/
	protected static $localCompany = array();

	/**
	* The excluded powers
	**/
	protected static $exPowers= array();

	/**
	 * The array of constant paths
	 * 
	 * JPATH_SITE is meant to represent the root path of the JSite application,
	 * just as JPATH_ADMINISTRATOR is mean to represent the root path of the JAdministrator application.
	 * 
	 *    JPATH_BASE is the root path for the current requested application.... so if you are in the administrator application:
	 * 
	 *    JPATH_BASE == JPATH_ADMINISTRATOR
	 * 
	 * If you are in the site application:
	 * 
	 *    JPATH_BASE == JPATH_SITE
	 * 
	 * If you are in the installation application:
	 * 
	 *    JPATH_BASE == JPATH_INSTALLATION.
	 * 
	 *    JPATH_ROOT is the root path for the Joomla install and does not depend upon any application.
	 * 
	 * @var     array
	 */
	public static $constantPaths = array(
		// The path to the administrator folder.
		'JPATH_ADMINISTRATOR' => JPATH_ADMINISTRATOR,
		// The path to the installed Joomla! site, or JPATH_ROOT/administrator if executed from the backend.
		'JPATH_BASE' => JPATH_BASE,
		// The path to the cache folder.
		'JPATH_CACHE' => JPATH_CACHE,
		// The path to the administration folder of the current component being executed.
		'JPATH_COMPONENT_ADMINISTRATOR' => JPATH_ADMINISTRATOR . '/components/com_componentbuilder',
		// The path to the site folder of the current component being executed.
		'JPATH_COMPONENT_SITE' => JPATH_SITE . '/components/com_componentbuilder',
		// The path to the current component being executed.
		'JPATH_COMPONENT' => JPATH_BASE . '/components/com_componentbuilder',
		// The path to folder containing the configuration.php file.
		'JPATH_CONFIGURATION' => JPATH_CONFIGURATION,
		// The path to the installation folder.
		'JPATH_INSTALLATION' => JPATH_INSTALLATION,
		// The path to the libraries folder.
		'JPATH_LIBRARIES' => JPATH_LIBRARIES,
		// The path to the plugins folder.
		'JPATH_PLUGINS' => JPATH_PLUGINS,
		// The path to the installed Joomla! site.
		'JPATH_ROOT' => JPATH_ROOT,
		// The path to the installed Joomla! site.
		'JPATH_SITE' => JPATH_SITE,
		// The path to the templates folder.
		'JPATH_THEMES' => JPATH_THEMES
	);

	/**
	 * Get the array of existing Joomla validation rule class names
	 *
	 * @param  bool  $lowercase  If true, converts names to lowercase
	 *
	 * @return array|null  Array of rule names or null on failure
	 * @since  3.0.0
	 */
	public static function getExistingValidationRuleNames(bool $lowercase = false): ?array
	{
		// Try to retrieve from static cache
		$cacheKey = '_existing_validation_rules_VDM';
		$cached = self::get($cacheKey, null);

		if (!empty($cached) && is_string($cached))
		{
			$items = json_decode($cached, true);
		}
		else
		{
			$path = JPATH_LIBRARIES . '/src/Form/Rule';

			if (!is_dir($path))
			{
				return null;
			}

			$original_dir = getcwd();
			if (!@chdir($path))
			{
				return null;
			}

			try {
				$files = Folder::files(
					'.',
					'\.php',
					true,
					true
				);
			} catch (\Throwable $e) {
				return null;
			} finally {
				@chdir($original_dir);
			}

			if (!UtilitiesArrayHelper::check($files)) {
				return null;
			}

			$items = array_map(
				fn(string $file) => str_replace(['./', 'Rule.php'], '', $file),
				$files
			);

			// Cache it for next time
			self::set($cacheKey, json_encode($items));
		}

		// Normalize casing if requested
		if ($lowercase && is_array($items))
		{
			$items = array_map('strtolower', $items);
		}

		return is_array($items) ? $items : null;
	}

	/**
	 * Get the library files
	 * 
	 * @param  string   $guid   The library guid to target
	 *
	 * @return  array    On success the array of files that belong to this library
	 * @since   3.0.0
	 */
	public static function getLibraryFiles(string $guid): ?array
	{
		if (!GuidHelper::valid($guid))
		{
			return null;
		}

		// get the library files, folders, and urls
		$files = [];

		// Get a db connection.
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(['l.name','f.addurls','f.addfolders','f.addfiles']));
		$query->from($db->quoteName('#__componentbuilder_library_files_folders_urls', 'f'));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_library', 'l') . ' ON (' . $db->quoteName('f.library') . ' = ' . $db->quoteName('l.guid') . ')');
		$query->where($db->quoteName('f.library') . ' = ' . $db->quote($guid));

		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{			
			// prepare the files
 			$result = $db->loadObject();

 			// first we load the URLs
			if (JsonHelper::check($result->addurls))
			{
				// convert to array
				$result->addurls = json_decode($result->addurls, true);

				// set urls
				if (UtilitiesArrayHelper::check($result->addurls))
				{
					// build media folder path
					$mediaPath = '/media/' . strtolower( preg_replace('/\s+/', '-', UtilitiesStringHelper::safe($result->name, 'filename', ' ', false)));

					// load the urls
					foreach($result->addurls as $url)
					{
						if (isset($url['url']) && UtilitiesStringHelper::check($url['url']))
						{
							// set the path if needed
							if (isset($url['type']) && $url['type'] > 1)
							{
								$fileName = basename($url['url']);
								// build sub path
								if (strpos($fileName, '.js') !== false)
								{
									$path = '/js';
								}
								elseif (strpos($fileName, '.css') !== false)
								{
									$path = '/css';
								}
								else
								{
									$path = '';
								}
								// set the path to library file
								$url['path'] = $mediaPath . $path . '/' . $fileName; // we need this for later
							}

							// if local path is set, then use it first
							if (isset($url['path']))
							{
								// load document script
								$files[md5($url['path'])] =  '(' . Text::_('URL') . ') ' . basename($url['url']) . ' - ' . Text::_('COM_COMPONENTBUILDER_LOCAL');
							}

							// check if link must be added
							if (isset($url['url']) && ((isset($url['type']) && $url['type'] == 1) || (isset($url['type']) && $url['type'] == 3) || !isset($url['type'])))
							{
								// load url also if not building document
								$files[md5($url['url'])] = '(' . Text::_('URL') . ') ' . basename($url['url']) . ' - ' . Text::_('COM_COMPONENTBUILDER_LINK');
							}
						}
					}
				}
			}

			// load the local files
			if (JsonHelper::check($result->addfiles))
			{
				// convert to array
				$result->addfiles = json_decode($result->addfiles, true);

				// set files
				if (UtilitiesArrayHelper::check($result->addfiles))
				{
					foreach($result->addfiles as $file)
					{
						if (isset($file['file']) && isset($file['path']))
						{
							$path = '/'.trim($file['path'], '/');

							// check if path has new file name (has extension)
							$pathInfo = pathinfo($path);
							if (isset($pathInfo['extension']) && $pathInfo['extension'])
							{
								// load document script
								$files[md5($path)] = '(' . Text::_('COM_COMPONENTBUILDER_FILE') . ') ' . $file['file'];
							}
							else
							{
								// load document script
								$files[md5($path.'/'.trim($file['file'],'/'))] = '(' . Text::_('COM_COMPONENTBUILDER_FILE') . ') ' . $file['file'];
							}
						}
					}
				}
			}

 			// load the files in the folder	
			if (JsonHelper::check($result->addfolders))
			{
				// convert to array
				$result->addfolders = json_decode($result->addfolders, true);

				// set folder
				if (UtilitiesArrayHelper::check($result->addfolders))
				{
					// get the global settings
					if (!ObjectHelper::check(self::$params))
					{
						self::$params = ComponentHelper::getParams('com_componentbuilder');
					}

					// reset bucket
					$bucket = [];

					// get custom folder path
					$customPath = '/'.trim(self::$params->get('custom_folder_path', JPATH_ADMINISTRATOR . '/components/com_componentbuilder/custom'), '/');

					// get all the file paths
					foreach ($result->addfolders as $folder)
					{
						if (isset($folder['path']) && isset($folder['folder']))
						{
							$_path = '/'.trim($folder['path'], '/');
							$customFolder = '/'.trim($folder['folder'], '/');
							if (isset($folder['rename']) && 1 == $folder['rename'])
							{
								if ($_paths = FileHelper::getPaths($customPath.$customFolder))
								{
									$bucket[$_path] = $_paths;
								}
							}
							else
							{
								$path = $_path.$customFolder;
								if ($_paths = FileHelper::getPaths($customPath.$customFolder))
								{
									$bucket[$path] = $_paths;
								}
							}
						}
					}

					// now load the script
					if (UtilitiesArrayHelper::check($bucket))
					{
						foreach ($bucket as $root => $paths)
						{
							// load per path
							foreach($paths as $path)
							{
								$files[md5($root.'/'.trim($path, '/'))] = '(' . Text::_('COM_COMPONENTBUILDER_FOLDER') . ') ' . basename($path) . ' - ' . basename($root);
							}
						}
					}
				}
			}

			// return files if found
			if (UtilitiesArrayHelper::check($files))
			{
				return $files;
			}
		}

		return null;
	}

	/**
	 * Fix the path to work in the JCB script <-- (main issue here)
	 *	Since we need / slash in all paths, for the JCB script even if it is Windows
	 *	and since MS works with both forward and back slashes
	 *	we just convert all slashes to forward slashes
	 * 
	 * THIS is just my hack (fix) if you know a better way! speak-up!
	 * 
	 * @param   mix    $values   the array of paths or the path as a string
	 * @param   array  $targets  paths to target
	 *
	 * @return  string
	 * @since  3.0.0
	 */
	public static function fixPath(&$values, array $targets = [])
	{
		// if multiple to gets searched and fixed
		if (UtilitiesArrayHelper::check($values) && UtilitiesArrayHelper::check($targets))
		{
			foreach ($targets as $target)
			{
				if (isset($values[$target]) && strpos($values[$target], '\\') !== false)
				{
					$values[$target] = str_replace('\\', '/', $values[$target]);
				}
			}
		}
		// if just a string
		elseif (UtilitiesStringHelper::check($values) && strpos($values, '\\') !== false)
		{
			$values = str_replace('\\', '/', $values);
		}
	}

	/**
	 * Retrieves all published component IDs.
	 *
	 * This static method fetches the IDs of all published components from the database.
	 * It queries the `#__componentbuilder_joomla_component` table and excludes components that are trashed or unpublished.
	 *
	 * @return array|null Returns an array of component IDs on success. Returns `false` if no components are found or if an error occurs.
	 *
	 * @throws \RuntimeException If the database query fails.
	 * @since  3.0.0
	 */
	public static function getComponentIDs(): ?array
	{
		// Get a database connection
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		try {
			// Create a new query object
			$query = $db->getQuery(true);

			// Select the IDs of published components
			$query->select($db->quoteName('id'))
				  ->from($db->quoteName('#__componentbuilder_joomla_component'))
				  ->where($db->quoteName('published') . ' >= 1'); // Exclude trashed and unpublished components

			// Set and execute the query
			$db->setQuery($query);
			$componentIDs = $db->loadColumn();

			// Return the result or false if no components are found
			return !empty($componentIDs) ? $componentIDs : null;
		} catch (\RuntimeException $e) {
			// Log the error (optional) and rethrow for higher-level handling
			Log::add('Database query failed: ' . $e->getMessage(), Log::ERROR, 'jerror');
			throw new \RuntimeException(Text::_('COM_COMPONENTBUILDER_ERROR_FETCHING_COMPONENT_IDS_FROM_THE_DATABASE'));
		}
	}

	/**
	 * Retrieves all published component GUIDs.
	 *
	 * This static method fetches the GUIDs of all published components from the database.
	 * It queries the `#__componentbuilder_joomla_component` table and excludes components that are trashed or unpublished.
	 *
	 * @return array|null Returns an array of component GUIDs on success. Returns `false` if no components are found or if an error occurs.
	 *
	 * @throws \RuntimeException If the database query fails.
	 * @since  5.0.4
	 */
	public static function getComponentGUIDs(): ?array
	{
		// Get a database connection
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		try {
			// Create a new query object
			$query = $db->getQuery(true);

			// Select the IDs of published components
			$query->select($db->quoteName('guid'))
				  ->from($db->quoteName('#__componentbuilder_joomla_component'))
				  ->where($db->quoteName('published') . ' >= 1'); // Exclude trashed and unpublished components

			// Set and execute the query
			$db->setQuery($query);
			$componentGUIDs = $db->loadColumn();

			// Return the result or false if no components are found
			return !empty($componentGUIDs) ? $componentGUIDs : null;
		} catch (\RuntimeException $e) {
			// Log the error (optional) and rethrow for higher-level handling
			Log::add('Database query failed: ' . $e->getMessage(), Log::ERROR, 'jerror');
			throw new \RuntimeException(Text::_('COM_COMPONENTBUILDER_ERROR_FETCHING_COMPONENT_GUIDS_FROM_THE_DATABASE'));
		}
	}

	/*
	 * Convert repeatable field to subform
	 * 
	 * @param   array    $item       The array to convert
	 * @param   string   $name      The main field name
	 *
	 * @return  array
	 */
	public static function convertRepeatable($item, $name)
	{
		// continue only if we have an array
		if (UtilitiesArrayHelper::check($item))
		{
			$bucket = array();
			foreach ($item as $key => $values)
			{
				foreach ($values as $nr => $value)
				{
					if (!isset($bucket[$name . $nr]) || !UtilitiesArrayHelper::check($bucket[$name . $nr]))
					{
						$bucket[$name . $nr] = array();
					}
					$bucket[$name . $nr][$key] = $value;
				}
			}
			return $bucket;
		}
		return $item;
	}

	/*
	 * Convert repeatable field to subform
	 * 
	 * @param   object     $item            The item to update
	 * @param   array      $searcher        The fields to check and update
	 * @param   array      $updater         To update the local table
	 *
	 * @return void
	 */
	public static function convertRepeatableFields($object, $searcher, $updater = array())
	{
		// update the repeatable fields
		foreach ($searcher as  $key => $sleutel)
		{
			if (isset($object->{$key}))
			{
				$isJson = false;
				if (JsonHelper::check($object->{$key}))
				{
					$object->{$key} = json_decode($object->{$key}, true);
					$isJson = true;
				}
				// check if this is old values for repeatable fields
				if (UtilitiesArrayHelper::check($object->{$key}) && isset($object->{$key}[$sleutel]))
				{
					// load it back
					$object->{$key} = self::convertRepeatable($object->{$key}, $key);
					// add to global updater
					if (
						UtilitiesArrayHelper::check($object->{$key}) && UtilitiesArrayHelper::check($updater) && 
						(
							( isset($updater['table']) && isset($updater['val']) && isset($updater['key']) ) || 
							( isset($updater['unique']) && isset($updater['unique'][$key]) && isset($updater['unique'][$key]['table']) && isset($updater['unique'][$key]['val']) && isset($updater['unique'][$key]['key']) )
						)
					   )
					{
						$_key = null;
						$_value = null;
						$_table = null;
						// check if we have unique id table for this repeatable/subform field
						if ( isset($updater['unique']) && isset($updater['unique'][$key]) && isset($updater['unique'][$key]['table']) && isset($updater['unique'][$key]['val']) && isset($updater['unique'][$key]['key']) )
						{
							$_key = $updater['unique'][$key]['key'];
							$_value = $updater['unique'][$key]['val'];
							$_table = $updater['unique'][$key]['table'];
						}
						elseif ( isset($updater['table']) && isset($updater['val']) && isset($updater['key']) )
						{
							$_key = $updater['key'];
							$_value = $updater['val'];
							$_table = $updater['table'];
						}
						// continue only if values are valid
						if (UtilitiesStringHelper::check($_table) && UtilitiesStringHelper::check($_key) && $_value > 0)
						{
							// set target table & item
							$target = trim($_table) . '.' . trim($_key) . '.' . trim($_value);
							if (!isset(self::$globalUpdater[$target]))
							{
								self::$globalUpdater[$target] = new \stdClass;
								self::$globalUpdater[$target]->{$_key} = (int) $_value;
							}
							// load the new subform values to global updater
							self::$globalUpdater[$target]->{$key} = json_encode($object->{$key});
						}
					}
				}
				// no set back to json if came in as json
				if ($isJson && UtilitiesArrayHelper::check($object->{$key}))
				{
					$object->{$key} = json_encode($object->{$key}); 
				}
				// remove if not json or array
				elseif (!UtilitiesArrayHelper::check($object->{$key}) && !JsonHelper::check($object->{$key}))
				{
					unset($object->{$key});
				}
			}
		}
		return $object;
	}

	/**
	 * Run Global Updater if any are set
	 * 
	 * @return  void
	 * 
	 */
	public static function runGlobalUpdater()
	{
		// check if any updates are set to run
		if (UtilitiesArrayHelper::check(self::$globalUpdater))
		{
			// get the database object
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			foreach (self::$globalUpdater as $tableKeyID => $object)
			{
				// get the table
				$table = explode('.', $tableKeyID);
				// update the item
				$db->updateObject('#__componentbuilder_' . (string) $table[0] , $object, (string) $table[1]);
			}
			// rest updater
			self::$globalUpdater = array();
		}
	}

	/**
	 * Copy Any Item (only use for direct database copying)
	 * 
	 * @param   int        $id         The item to copy
	 * @param   string   $type     The type and model to copy from and with
	 * @param   array    $config   The values that should change
	 *
	 * @return  boolean   True if success
	 * @since  3.0.0
	 */
	public static function copyItem($id, string $type, array $config = [])
	{
		// only continue if we have an id
		if (is_numeric($id) && (int) $id > 0)
		{
			// get the model
			Helper::setOption('com_componentbuilder');
			$model = Helper::getModel($type);
			$app = Factory::getApplication();

			// get item
			if ($item = $model->getItem($id))
			{
				// update values that should change
				if (UtilitiesArrayHelper::check($config))
				{
					foreach($config as $key => $value)
					{
						if (isset($item->{$key}))
						{
							$item->{$key} = $value;
						}
					}
				}

				// clone the object
				$data = [];
				foreach ($item as $key => $value)
				{
					$data[$key] = $value;
				}

				// reset some values
				$data['id'] = 0;
				$data['version'] = 1;
				if (isset($data['tags']))
				{
					$data['tags'] = null;
				}
				if (isset($data['associations']))
				{
					$data['associations'] = array();
				}

				// remove some unneeded values
				unset($data['params']);
				unset($data['asset_id']);
				unset($data['checked_out']);
				unset($data['checked_out_time']);

				// Attempt to save the data.
				if ($model->save($data))
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	* the basic localkey
	**/
	protected static $localkey = false;

	/**
	* get the localkey
	**/	
	public static function getLocalKey()
	{
		if (!self::$localkey)
		{
			// get the basic key
			self::$localkey = md5(self::getCryptKey('basic', 'localKey34fdWEkl'));
		}
		return self::$localkey;
	}

	/**
	 * indent HTML
	 */
	public static function indent($html)
	{
		// load the class
		require_once JPATH_ADMINISTRATOR.'/components/com_componentbuilder/helpers/indenter.php';
		// set new indenter
		$indenter = new Indenter();
		// return indented html
		return $indenter->indent($html);
	}

	public static function checkFileType($file, $sufix)
	{
		// now check if the file ends with the sufix
		return $sufix === "" || ($sufix == substr(strrchr($file, "."), -strlen($sufix)));
	}

	public static function imageInfo($path, $request = 'type')
	{
		// set image
		$image = JPATH_SITE.'/'.$path;
		// check if exists
		if (file_exists($image) && $result = @getimagesize($image))
		{
			// return type request
			switch ($request)
			{
				case 'width':
					return $result[0];
					break;
				case 'height':
					return $result[1];
					break;
				case 'type':
					$extensions = array(
						IMAGETYPE_GIF => "gif",
						IMAGETYPE_JPEG => "jpg",
						IMAGETYPE_PNG => "png",
						IMAGETYPE_SWF => "swf",
						IMAGETYPE_PSD => "psd",
						IMAGETYPE_BMP => "bmp",
						IMAGETYPE_TIFF_II => "tiff",
						IMAGETYPE_TIFF_MM => "tiff",
						IMAGETYPE_JPC => "jpc",
						IMAGETYPE_JP2 => "jp2",
						IMAGETYPE_JPX => "jpx",
						IMAGETYPE_JB2 => "jb2",
						IMAGETYPE_SWC => "swc",
						IMAGETYPE_IFF => "iff",
						IMAGETYPE_WBMP => "wbmp",
						IMAGETYPE_XBM => "xbm",
						IMAGETYPE_ICO => "ico"
					);
					return $extensions[$result[2]];
					break;
				case 'attr':
					return $result[3];
					break;
				case 'all':
				default:
					return $result;
					break;
			}
		}
		return false;
	}

	/**
	* check if it is a new hash
	**/
	public static function newHash($hash, $name = 'backup', $type = 'hash', $key = '',  $fileType = 'txt')
	{
		// make sure we have a hash
		if (UtilitiesStringHelper::check($hash))
		{
			// first get the file path
			$path_filename = FileHelper::getPath('path', $name.$type, $fileType, $key, JPATH_ADMINISTRATOR . '/components/com_componentbuilder');
			// set as read if not already set
			if ($content = FileHelper::getContent($path_filename, false))
			{
				if ($hash == $content)
				{
					return false;
				}
			}
			// set the hash
			return FileHelper::write($path_filename, $hash);
		}
		return false;
	}

	/**
	* get the database table columns
	**/
	public static function getDbTableColumns($tableName, $as, $type)
	{
		// Get a db connection.
		$db = Factory::getContainer()->get(DatabaseInterface::class);
        	// get the columns
		$columns = $db->getTableColumns("#__" . $tableName);
		// set the type (multi or single)
		$unique = '';
		if (1 == $type)
		{
			$unique = UtilitiesStringHelper::safe($tableName) . '_';
		}
		if (UtilitiesArrayHelper::check($columns))
		{
        		// build the return string
			$tableColumns = [];
			foreach ($columns as $column => $typeCast)
			{
				$tableColumns[] =  $as . "." . $column . ' AS ' . $unique . $column;
			}
			return implode("\n", $tableColumns);
		}
		return false;
	}

	/**
	* get the view table columns
	**/
	public static function getViewTableColumns($admin_view, $as, $type)
	{
		// Get a db connection.
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.addfields', 'b.name_single')));
		$query->from($db->quoteName('#__componentbuilder_admin_fields', 'a'));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_admin_view', 'b') . ' ON (' . $db->quoteName('a.admin_view') . ' = ' . $db->quoteName('b.guid') . ')');
		$query->where($db->quoteName('b.published') . ' = 1');
		$query->where($db->quoteName('a.admin_view') . ' = ' . $db->quote($admin_view));

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			$result = $db->loadObject();
			$tableName = '';
			if (1 == $type)
			{
				$tableName = UtilitiesStringHelper::safe($result->name_single) . '_';
			}
			$addfields = json_decode($result->addfields, true);
			if (UtilitiesArrayHelper::check($addfields))
			{
				// reset all buckets
				$field = [];
				$fields = [];
				// get data
				foreach ($addfields as $nr => $value)
				{
					$tmp = self::getFieldNameAndType($value['field']);
					if (UtilitiesArrayHelper::check($tmp))
					{
						$field[$nr] = $tmp;
					}
					// insure it is set to alias if needed
					if (isset($value['alias']) && $value['alias'] == 1)
					{
						$field[$nr]['name'] = 'alias';
					}
					// remove a field that is not being stored in the database TODO: we should base this on field type and then list, not only list
					if (!isset($value['list']) || $value['list'] == 2)
					{
						unset($field[$nr]);
					}
				}
				// add the basic defaults
				$fields[] = $as . ".id AS " . $tableName . "id";
				$fields[] = $as . ".asset_id AS " . $tableName . "asset_id";
				// load data
				foreach ($field as $n => $f)
				{
					if (UtilitiesArrayHelper::check($f))
					{
						$fields[] = $as . "." . $f['name'] . " AS " . $tableName . $f['name'];
					}
				}
				// add the basic defaults
				$fields[] = $as . ".published AS " . $tableName . "published";
				$fields[] = $as . ".created_by AS " . $tableName . "created_by";
				$fields[] = $as . ".modified_by AS " . $tableName . "modified_by";
				$fields[] = $as . ".created AS " . $tableName . "created";
				$fields[] = $as . ".modified AS " . $tableName . "modified";
				$fields[] = $as . ".version AS " . $tableName . "version";
				$fields[] = $as . ".hits AS " . $tableName . "hits";
				if (0) // TODO access is not set here but per/view in the form linking this admin view to which these field belong to the components (boooo I know but that is the case and so we can't ever really know at this point if this view has access set)
				{
					$fields[] = $as . ".access AS " . $tableName . "access";
				}
				$fields[] = $as . ".ordering AS " . $tableName . "ordering";
				// return the field of this view
				return implode("\n", $fields);
			}
		}
		return false;
	}

	/**
	 * Retrieves the field name and its type based on the provided target.
	 *
	 * This method fetches a field's name and type from the database, processes additional metadata
	 * from the field's XML definition, and returns a sanitized array containing the field's name 
	 * and type. It supports options for excluding spacers and notes.
	 *
	 * @param mixed $target The field identifier. Can either be:
	 *                      - A GUID (globally unique identifier) as a string, or
	 *                      - A numeric ID (integer).
	 * @param bool $spacers Optional. Whether to include spacers and notes in the result. Defaults to `false`.
	 *
	 * @return array|null Returns an associative array with the following keys on success:
	 *                    - `name` (string): The sanitized field name.
	 *                    - `type` (string): The sanitized field type.
	 *                    Returns `null` if the target is invalid or no matching field is found.
	 *
	 * @throws \RuntimeException If the database query fails.
	 * @since  3.0.9
	 */
	public static function getFieldNameAndType($target, bool $spacers = false): ?array
	{
		if (GuidHelper::valid($target))
		{
			$key = 'guid';
		}
		elseif (is_numeric($target))
		{
			$key = 'id';
		}
		else
		{
			return null;
		}

		// Get a db connection.
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		// Create a new query object.
		$query = $db->getQuery(true);

		// Order it by the ordering field.
		$query->select($db->quoteName(['a.name', 'a.xml']));
		$query->select($db->quoteName(['c.name'], ['type_name']));
		$query->from('#__componentbuilder_field AS a');
		$query->join('LEFT', $db->quoteName('#__componentbuilder_fieldtype', 'c') . ' ON (' . $db->quoteName('a.fieldtype') . ' = ' . $db->quoteName('c.guid') . ')');
		$query->where($db->quoteName('a.' . $key) . ' = '. $db->quote($target));

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			// Load the results as a list of stdClass objects (see later for more options on retrieving data).
			$field = $db->loadObject();

			// load the values form params
			$field->xml = json_decode($field->xml);
			$field->type_name = self::safeTypeName($field->type_name);
			$load = true;

			// if category then name must be catid (only one per view)
			if ($field->type_name === 'category')
			{
				$name = 'catid';
			}

			// if tag is set then enable all tag options for this view (only one per view)
			elseif ($field->type_name === 'tag')
			{
				$name = 'tags';
			}

			// don't add spacers or notes
			elseif (!$spacers && ($field->type_name == 'spacer' || $field->type_name == 'note'))
			{
				// make sure the name is unique
				return null;
			}
			else
			{
				$name = self::safeFieldName(GetHelper::between($field->xml,'name="','"'));
			}

			// use field core name only if not found in xml
			if (!UtilitiesStringHelper::check($name))
			{
				$name = self::safeFieldName($field->name);
			}

			return ['name' => $name, 'type' => $field->type_name];
		}

		return null;
	}

	/**
	* validate that a placeholder is unique
	**/
	public static function validateUniquePlaceholder($id, $name, $bool = false)
	{
		// make sure no padding is set
		$name = preg_replace("/[^A-Za-z0-9_]/", '', $name);
		// this list may grow as we find more cases that break the compiler (just open an issue on github)
		if (in_array($name, array('component', 'view', 'views')))
		{
			// check if we must return boolean
			if (!$bool)
			{
				return array (
					'message' => Text::_('COM_COMPONENTBUILDER_SORRY_THIS_PLACEHOLDER_IS_ALREADY_IN_USE_IN_THE_COMPILER'),
					'status' => 'danger');
			}
			return false;
		}
		// add the padding (needed)
		$name = '[[[' . trim($name) . ']]]';
		if (self::placeholderIsSet($id, $name))
		{
			// check if we must return boolean
			if (!$bool)
			{
				return array (
					'message' => Text::_('COM_COMPONENTBUILDER_SORRY_THIS_PLACEHOLDER_IS_ALREADY_IN_USE'),
					'status' => 'danger');
			}
			return false;
		}
		// check if we must return boolean
		if (!$bool)
		{
			return array (
				'name' => $name,
				'message' => Text::_('COM_COMPONENTBUILDER_GREAT_THIS_PLACEHOLDER_WILL_WORK'),
				'status' => 'success');
		}
		return true;
	}

	/**
	* search for placeholder in table
	**/
	protected static function placeholderIsSet($id, $name)
	{
		// query the table for result array
		if (($results = self::getPlaceholderTarget($id, $name)) !== false)
		{
			// check if we must continue the search
			foreach ($results as $_id => $target)
			{
				if ($name === $target)
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	* get placeholder target
	**/
	protected static function getPlaceholderTarget($id, $name)
	{
		// Get a db connection.
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'target')));
		$query->from($db->quoteName('#__componentbuilder_placeholder'));
		$query->where($db->quoteName('target') . ' = '. $db->quote($name));
		// check if we have id
		if (is_numeric($id))
		{
			$query->where($db->quoteName('id') . ' <> ' . (int) $id);
		}
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			return $db->loadAssocList('id', 'target');
		}
		return false;
	}

	/**
	* Powers to exclude
	**/
	public static function excludePowers($id)
	{
		// first check if this power set is already found
		if (!isset(self::$exPowers[$id]))
		{
			// Get a db connection.
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('a.id')));
			$query->from($db->quoteName('#__componentbuilder_power', 'a'));
			$query->join('LEFT', $db->quoteName('#__componentbuilder_power', 'b') . ' ON (' . $db->quoteName('a.name') . ' = ' . $db->quoteName('b.name') . ' AND ' . $db->quoteName('a.namespace') . ' = ' . $db->quoteName('b.namespace') . ')');
			$query->where($db->quoteName('b.id') . ' = ' . (int) $id);
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				self::$exPowers[$id] = $db->loadColumn();
			}
			// all ways add itself aswell
			self::$exPowers[$id][] = $id;
		}
		// if found return
		if (isset(self::$exPowers[$id]))
		{
			return self::$exPowers[$id];
		}
		return false;
	}

	/**
	 * The array of dynamic content
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected static array $dynamicContent = [
		// The banners by size (width - height)
		'banner' => [
			'728-90' => [
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/banner/joomla-heart-wide.gif',
					'hash' => 'f857e3a38facaeac9eba3cffa912b620',
					'html' => '<a href="https://vdm.bz/joomla-volunteers" target="_blank" title="Joomla! Volunteers Portal"><img class="jcb-sponsor-banner" src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/banner/joomla-heart-wide.gif" alt="Joomla! Volunteers Portal" width="728" height="90" border="0"></a>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/banner/JCM_2010_120x600.png',
					'hash' => '5389cf3be8569cb3f6793e8bd4013d19',
					'html' => '<a href="https://vdm.bz/joomla-magazine" target="_blank" title="Joomla! Community Magazine | Because community matters..."><img class="jcb-sponsor-banner" alt="Joomla! Community Magazine | Because community matters..." src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/banner/JCM_2010_728x90.png" width="728" height="90" border="0" /></a>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/banner/tlwebdesign_jcb_sponsor_728_90.png',
					'hash' => 'd19be1f9f5b2049ff901096aafc246be',
					'html' => '<a href="https://vdm.bz/jcb-sponsor-tlwebdesign" target="_blank" title="tlwebdesign a JCB sponsor | Because community matters..."><img class="jcb-sponsor-banner" alt="tlwebdesign a JCB sponsor | Because community matters..." src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/banner/tlwebdesign_jcb_sponsor_728_90.png" width="728" height="90" border="0" /></a>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/banner/vdm_jcb_sponsor_728_90.gif',
					'hash' => '84478dfa0cd880395815e0ee026812a4',
					'html' => '<a href="https://vdm.bz/jcb-sponsor-vdm" target="_blank" title="VDM a JCB sponsor | Because community matters..."><img class="jcb-sponsor-banner" alt="VDM a JCB sponsor | Because community matters..." src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/banner/vdm_jcb_sponsor_728_90.gif" width="728" height="90" border="0" /></a>'],
				[
					'url' => 'https://cms-experts.org/images/banners/agerix/agerix-loves-jcb-728-90.gif',
					'hash' => 'b24c0726aa809cdcc04bcffe7e1e1529',
					'html' => '<a href="https://vdm.bz/jcb-sponsor-agerix" target="_blank" title="Agerix a JCB sponsor | Because community matters..."><img class="jcb-sponsor-banner" alt="Agerix a JCB sponsor | Because community matters..." src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/banner/agerix-loves-jcb-728-90.gif" width="728" height="90" border="0" /></a>']
			],
			'160-600' => [
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/banner/joomla-heart-tall.gif',
					'hash' => '9a75e4929b86c318128b53cf78251678',
					'html' => '<a href="https://vdm.bz/joomla-volunteers" target="_blank" title="Joomla! Volunteers Portal"><img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/banner/joomla-heart-tall.gif" alt="Joomla! Volunteers Portal" width="160" height="600" border="0"></a>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/banner/JCM_2010_120x600.png',
					'hash' => '5389cf3be8569cb3f6793e8bd4013d19',
					'html' => '<a href="https://vdm.bz/joomla-magazine" target="_blank" title="Joomla! Community Magazine | Because community matters..."><img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/banner/JCM_2010_120x600.png" alt="Joomla! Community Magazine | Because community matters..." width="120" height="600" border="0"/></a>']
			]
		],
		// The build-gif by size (width - height)
		'builder-gif' => [
			// original gif ;)
			'480-272' => [
				[
					'url' => 'https://www.joomlacomponentbuilder.com/images/builder/original.gif',
					'hash' => '676e37a949add8f4573381195cd1061c',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/original.gif" />'
				]
			],
			// new gif artwork since 2021
			'480-540' => [
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/1.gif',
					'hash' => 'ce6e36456fa794ba95d981547b2f54f8',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/1.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/2.gif',
					'hash' => '0a54dbc393359747e33db90cabb1e2d7',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/2.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/3.gif',
					'hash' => '4e5498713ff69a64a0a79dbf620372a3',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/3.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/4.gif',
					'hash' => '3554ffab2a6df95a116fd9f0db63925c',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/4.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/5.gif',
					'hash' => '08f0cdf188593eca65c6dafd7af27ef9',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/5.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/6.gif',
					'hash' => '103b46a7ac3fcb974e25d06f417a4e87',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/6.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/7.gif',
					'hash' => 'ffa8547099b7286f89ab7ff5a140dd90',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/7.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/8.gif',
					'hash' => '316df780f9e4ce81200a65d3c4303c41',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/8.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/9.gif',
					'hash' => '9ab6ba78b6e63a285fdef2ff5e447c93',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/9.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/10.gif',
					'hash' => 'cd9abaa1cb95f51a70916da6b70614f2',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/10.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/11.gif',
					'hash' => 'cfe53095b5249618e2348223b89262b9',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/11.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/12.gif',
					'hash' => '15a6690647d5160d67c80ce4dd1f5602',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/12.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/13.gif',
					'hash' => '2f77562e92c8a3b7c47664c98f551fe8',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/13.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/14.gif',
					'hash' => '46db15517ef5bd063be85134e1cc575d',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/14.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/15.gif',
					'hash' => 'e6c96eff157ea648ceb1583f2cc22544',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/15.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/16.gif',
					'hash' => '76010b7d1f99952eb9645df660467ae8',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/16.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/17.gif',
					'hash' => '021219ddd72d8fcfc7f80bd4a874d651',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/17.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/18.gif',
					'hash' => '383af3179d4ae27301c1292e630d7155',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/18.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/19.gif',
					'hash' => '8537e6d7be93447241b521f851e8a61d',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/19.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/20.gif',
					'hash' => '10d96f70e3d43086a925b00a7dc0022e',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/20.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/21.gif',
					'hash' => '161de9865b171b44039353b8d50491d3',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/21.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/22.gif',
					'hash' => '6a2354e43eb97d278d74bb2c12890988',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/22.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/23.gif',
					'hash' => '2cb6e2f9562a8dc8eef6d5d8d1a84f5e',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/23.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>'],
				[
					'url' => 'https://raw.githubusercontent.com/vdm-io/jcb-external/refs/heads/master/src/images/builder/24.gif',
					'hash' => '745b3fb5e16515689132432bf02ab1b4',
					'html' => '<img src="[[[ROOT-URL]]]administrator/components/com_componentbuilder/assets/images/builder-gif/24.gif" /><br /><div style="text-align: right; font-size: smaller;">Animation produced with 3D Particle Explorations by Jack Rugile.</div>']
			]
		]
	];

	/**
	 * get the dynamic content array size
	 * 
	 * @param   string   $type      The type of content
	 * @param   string   $size      The size of the content
	 *
	 * @return  int   on success number of items in array type,size
	 * @since   3.0.0
	 */
	public static function getDynamicContentSize(string $type, string $size): int
	{
		if (isset(self::$dynamicContent[$type]) && isset(self::$dynamicContent[$type][$size])
			&& ($nr = UtilitiesArrayHelper::check(self::$dynamicContent[$type][$size])))
		{
			return $nr;
		}
		return 0;
	}

	/**
	 * get the dynamic content
	 * 
	 * @param   string    $type      The type of content
	 * @param   string    $size      The size of the content
	 * @param   mixed     $default   The default to return
	 * @param   int       $try       Retry tracker (when bigger then array size it stops)
	 * @param   mixed     $getter    The specific getter number (not zero based)
	 *
	 * @return  string   on success html string
	 * @since   3.0.0
	 */
	public static function getDynamicContent(string $type, string $size, $default = '', int $try = 1, $getter = null)
	{
		if (($nr = self::getDynamicContentSize($type, $size)) !== 0)
		{
			// use specific getter
			if ($getter)
			{
				$get = --$getter;
			}
			// get the random getter number
			elseif ($nr > 1)
			{
				$get = (int) rand(0, --$nr);
			}
			else
			{
				$get = 0;
			}
			// get the current target if found
			if (isset(self::$dynamicContent[$type][$size][$get]))
			{
				$target = self::$dynamicContent[$type][$size][$get];
				// set file name
				$file_name = basename($target['url']);
				// set the local path (in admin area so when the component uninstall these images get removed as well)
				$path = JPATH_ROOT . "/administrator/components/com_componentbuilder/assets/images/$type/$file_name";
				// check if file exist or if it changed
				if (($image_data = FileHelper::getContent($path, false)) === false ||
					md5($image_data) !== $target['hash'])
				{
					// since the file does not exist or has changed (so we have a new hash)
					// therefore we download it to validate
					if (($image_data = FileHelper::getContent($target['url'], false)) !== false &&
						md5($image_data) === $target['hash'])
					{
						// create the JCB type path if it does not exist
						if (!is_dir(JPATH_ROOT . "/administrator/components/com_componentbuilder/assets/images/$type"))
						{
							Folder::create(JPATH_ROOT . "/administrator/components/com_componentbuilder/assets/images/$type");
						}
						// only set the image if the data match the hash
						FileHelper::write($path, $image_data);
					}
					// we retry array size times (unless specific getter is used)
					elseif ($try <= $nr && !$getter)
					{
						// the first time around failed so we try again (the size of the array times)
						return self::getDynamicContent($type, $size, $default, ++$try);
					}
				}
				// return found content
				return str_replace('[[[ROOT-URL]]]', Uri::root(), $target['html']);
			}
		}
		return $default;
	}

	/**
	 * Tab/spacer bucket (to speed-up the build)
	 * 
	 * @var   array
	 */
	protected static $tabSpacerBucket = array();

	/**
	 * Set tab/spacer
	 * 
	 * @var   string
	 */
	protected static $tabSpacer = "\t";

	/**
	 * Set the tab/space
	 * 
	 * @param   int   $nr  The number of tag/space
	 * 
	 * @return  string
	 * 
	 */
	public static function _t($nr)
	{
		// check if we already have the string
		if (!isset(self::$tabSpacerBucket[$nr]))
		{
			// get the string
			self::$tabSpacerBucket[$nr] = str_repeat(self::$tabSpacer, (int) $nr);
		}
		// return stored string
		return self::$tabSpacerBucket[$nr];
	}


	/**
	 * Local in-memory cache of session values for faster access.
	 *
	 * @var array<string, mixed>
	 * @since 3.5.2
	 */
	protected static array $localSession = [];

	/**
	 * Initialize the session and set default values.
	 *
	 * This ensures the session is ready and can be used safely.
	 * Defaults can be loaded or checked here if needed.
	 *
	 * @return void
	 * @throws \RuntimeException if the session cannot be initialized
	 * @since 3.5.2
	 */
	public static function loadSession(): void
	{
		// Ensure the session is initialized (handled by the session() method).
		SessionHelper::session();

		// Set default session values if needed
		if (method_exists(static::class, 'setSessionDefaults'))
		{
			static::setSessionDefaults();
		}
	}

	/**
	 * Store a key-value pair in the session and local memory.
	 *
	 * @param string $key The session key name
	 * @param mixed  $value  The value to store
	 *
	 * @return mixed The previous session value if it existed
	 * @since 3.5.2
	 */
	public static function set(string $key, $value)
	{
		static::$localSession[$key] = $value;

		return SessionHelper::set($key, $value);
	}

	/**
	 * Retrieve a value from the session.
	 * Uses local cache if already fetched during this request.
	 *
	 * @param string $key The session key name
	 * @param mixed  $default  Default value if the key is not found
	 *
	 * @return mixed The session value
	 * @since 3.5.2
	 */
	public static function get(string $key, $default = null)
	{
		if (!array_key_exists($key, static::$localSession))
		{
			static::$localSession[$key] = SessionHelper::get($key, $default);
		}

		return static::$localSession[$key];
	}

	/**
	 * Get field type properties.
	 *
	 * @param   string       $value       The lookup value.
	 * @param   string       $type        The field type lookup column.
	 * @param   array|null   $settings    The field settings override values.
	 * @param   string|null  $xml         The XML string to inspect for current values.
	 * @param   bool         $dbDefaults  Whether database defaults should be loaded.
	 *
	 * @return  array|null  The field type properties on success.
	 * @since   3.0.0
	 */
	public static function getFieldTypeProperties(
		string $value,
		string $type,
		?array $settings = [],
		?string $xml = null,
		bool $dbDefaults = false
	): ?array
	{
		// Get a db connection
		$db = Factory::getContainer()
			->get(DatabaseInterface::class);

		// Create a new query object.
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					[
						'properties',
						'short_description',
						'description'
					]
				)
			)
			->from($db->quoteName('#__componentbuilder_fieldtype'))
			->where($db->quoteName('published') . ' = 1')
			->where($db->quoteName($type) . ' = ' . $db->quote($value));

		// Load database default values.
		if ($dbDefaults)
		{
			$query->select(
				$db->quoteName(
					[
						'datadefault',
						'datadefault_other',
						'datalenght',
						'datalenght_other',
						'datatype',
						'has_defaults',
						'indexes',
						'null_switch',
						'store'
					]
				)
			);
		}

		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result)
		{
			return null;
		}

		$properties = json_decode((string) $result->properties, true);

		if (!is_array($properties) || $properties === [])
		{
			return null;
		}

		$field = [
			'subform' => [],
			'nameListOptions' => [],
			'php' => [],
			'values' => '<field',
			'values_description' => self::buildFieldTypePropertiesTableStart(),
			'short_description' => (string) $result->short_description,
			'description' => (string) $result->description
		];

		$nr = 0;
		$phpTracker = [];
		$forceAll = self::shouldForceLoadAllProperties($xml);
		$confirmation = '8qvZHoyuFYQqpj0YQbc6F3o5DhBlmS-_-a8pmCZfOVSfANjkmV5LG8pCdAY2JNYu6cB';

		foreach ($properties as $property)
		{
			if (!is_array($property) || empty($property['name']))
			{
				continue;
			}

			$propertyName = (string) $property['name'];
			$propertyDescription = isset($property['description']) ? (string) $property['description'] : '';
			$example = self::getPropertyExample($property);

			$field['values_description'] .= self::buildFieldTypePropertyRow(
				$propertyName,
				$example,
				$propertyDescription
			);

			$currentValue = FieldHelper::getValue(
				$xml,
				$propertyName,
				$confirmation
			);

			$isPhpProperty = false;
			$phpLine = null;
			$phpKey = null;

			if (strpos($propertyName, 'type_php') !== false)
			{
				$isPhpProperty = true;
				$phpLine = (int) preg_replace('/[^0-9]/', '', $propertyName);
				$phpKey = trim((string) preg_replace('/[0-9]+/', '', $propertyName), '_');

				if (!isset($field['php'][$phpKey]))
				{
					$field['php'][$phpKey] = [
						'value' => [],
						'desc' => $propertyDescription
					];

					$phpTracker[$phpKey] = 1;
				}
			}

			$resolvedValue = null;

			if (
				UtilitiesArrayHelper::check($settings)
				&& isset($settings[$propertyName])
			)
			{
				$resolvedValue = (string) $settings[$propertyName];
			}
			elseif ($forceAll || !$xml || $confirmation !== $currentValue)
			{
				$resolvedValue = ($confirmation !== $currentValue)
					? (string) $currentValue
					: $example;
			}

			if ($resolvedValue !== null)
			{
				$field['values'] .= self::buildXmlAttributeLine($propertyName, $resolvedValue);

				if ($isPhpProperty && $phpKey !== null && $phpLine !== null)
				{
					$field['php'][$phpKey]['value'][$phpLine] = $resolvedValue;
					$phpTracker[$phpKey]++;
				}
				elseif (!$isPhpProperty)
				{
					$field['subform']['properties' . $nr] = [
						'name' => $propertyName,
						'value' => $resolvedValue,
						'desc' => $propertyDescription
					];
				}
			}

			if (!$isPhpProperty)
			{
				$field['nameListOptions'][$propertyName] = $propertyName;
			}

			$nr++;
		}

		self::loadMissingPhpPropertyLines($field, $phpTracker, $xml, $confirmation);

		$field['values'] .= PHP_EOL . '/>';
		$field['values_description'] .= '</tbody></table>';

		if (
			$dbDefaults
			&& isset($result->has_defaults)
			&& (int) $result->has_defaults === 1
		)
		{
			$field['database'] = [
				'datatype' => $result->datatype,
				'datadefault' => $result->datadefault,
				'datadefault_other' => $result->datadefault_other,
				'datalenght' => $result->datalenght,
				'datalenght_other' => $result->datalenght_other,
				'indexes' => $result->indexes,
				'null_switch' => $result->null_switch,
				'store' => $result->store
			];
		}

		return $field;
	}

	/**
	 * Build the start of the Bootstrap field type properties table.
	 *
	 * @return  string
	 * @since   6.1.6
	 */
	protected static function buildFieldTypePropertiesTableStart(): string
	{
		return '<table class="table table-striped table-hover table-sm">'
			. '<thead>'
			. '<tr>'
			. '<th class="text-end">' . Text::_('COM_COMPONENTBUILDER_PROPERTY') . '</th>'
			. '<th>' . Text::_('COM_COMPONENTBUILDER_EXAMPLE') . '</th>'
			. '<th>' . Text::_('COM_COMPONENTBUILDER_DESCRIPTION') . '</th>'
			. '</tr>'
			. '</thead>'
			. '<tbody>';
	}

	/**
	 * Build a single Bootstrap table row for a field property.
	 *
	 * @param   string  $name         The property name.
	 * @param   string  $example      The example value.
	 * @param   string  $description  The property description.
	 *
	 * @return  string
	 * @since   6.1.6
	 */
	protected static function buildFieldTypePropertyRow(
		string $name,
		string $example,
		string $description
	): string
	{
		return '<tr>'
			. '<td class="text-end"><code>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</code></td>'
			. '<td>' . htmlspecialchars($example, ENT_QUOTES, 'UTF-8') . '</td>'
			. '<td>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</td>'
			. '</tr>';
	}

	/**
	 * Build a formatted XML attribute line.
	 *
	 * @param   string  $name   The attribute name.
	 * @param   string  $value  The attribute value.
	 *
	 * @return  string
	 * @since   6.1.6
	 */
	protected static function buildXmlAttributeLine(string $name, string $value): string
	{
		return PHP_EOL . "\t"
			. $name
			. '="'
			. $value
			. '"';
	}

	/**
	 * Get the example value for a property.
	 *
	 * @param   array  $property  The property array.
	 *
	 * @return  string
	 * @since   6.1.6
	 */
	protected static function getPropertyExample(array $property): string
	{
		if (
			isset($property['example'])
			&& UtilitiesStringHelper::check($property['example'])
		)
		{
			return (string) $property['example'];
		}

		return '';
	}

	/**
	 * Check whether all properties should be force-loaded from the XML context.
	 *
	 * @param   string|null  $xml  The XML string.
	 *
	 * @return  bool
	 * @since   6.1.6
	 */
	protected static function shouldForceLoadAllProperties(?string $xml): bool
	{
		return ($xml !== null && strpos($xml, '..__FORCE_LOAD_ALL_PROPERTIES__..') !== false);
	}

	/**
	 * Load any missing PHP property lines from the XML.
	 *
	 * @param   array        $field         The field data array.
	 * @param   array        $phpTracker    The PHP property tracker.
	 * @param   string|null  $xml           The XML string.
	 * @param   string       $confirmation  The confirmation token.
	 *
	 * @return  void
	 * @since   6.1.6
	 */
	protected static function loadMissingPhpPropertyLines(
		array &$field,
		array $phpTracker,
		?string $xml,
		string $confirmation
	): void
	{
		if (
			!UtilitiesStringHelper::check($xml)
			|| !UtilitiesArrayHelper::check($phpTracker)
		)
		{
			return;
		}

		foreach ($phpTracker as $phpKey => $start)
		{
			if ($start >= 30)
			{
				continue;
			}

			foreach (range(2, 30) as $tNr)
			{
				$get = $phpKey . '_' . $tNr;

				if (
					!isset($field['php'][$phpKey]['value'][$tNr])
					&& ($value = FieldHelper::getValue($xml, $get, $confirmation)) !== $confirmation
				)
				{
					$field['php'][$phpKey]['value'][$tNr] = (string) $value;
				}
			}
		}
	}

	/**
	 * Get a field value from the XML stored string.
	 *
	 * @param   string  $xml           The xml string of the field.
	 * @param   string  $get           The value key to get from the string.
	 * @param   string  $confirmation  The value to confirm found value.
	 *
	 * @return  string  The field value from xml.
	 * @deprecated  3.3  Use FieldHelper::getValue($xml, $get, $confirmation);
	 * @since       6.1.6
	 */
	public static function getValueFromXMLstring(string &$xml, string &$get, string $confirmation = ''): string
	{
		return FieldHelper::getValue($xml, $get, $confirmation);
	}

	/**
	 * Retrieves field type properties from the database.
	 *
	 * This method fetches field types and their properties from the `#__componentbuilder_fieldtype` table.
	 * It supports filtering, excluding, and querying by specific IDs or GUIDs. The method also decodes 
	 * the `properties` JSON for each field type and processes additional attributes like `mandatory` and `translatable`.
	 *
	 * @param array  $targets   An array of target IDs or GUIDs to filter by. Leave empty to retrieve all.
	 * @param array  $filter    Key-value pairs for filtering field properties. For example: ['mandatory' => 1].
	 * @param array  $exclude   An array of property names to exclude.
	 * @param string $type      The column to filter on (e.g., 'id' or 'guid').
	 * @param string $operator  The SQL operator to use for filtering (`IN`, `NOT IN`, `IN_STRINGS`, `NOT IN_STRINGS`).
	 *
	 * @return array|false Returns an array with the following keys on success:
	 *                     - `types` (array): An associative array where the key is the GUID and the value is 
	 *                       an array of properties with their metadata (name, example, description, mandatory, translatable).
	 *                     - `properties` (array): A flat associative array of property names (key) and their display names (value).
	 *                     Returns `false` if no data is found or an error occurs.
	 *
	 * @throws \RuntimeException If the database query fails.
	 * @since  3.0.0
	 */
	public static function getFieldTypesProperties(
		array $targets = [],
		array $filter = [],
		array $exclude = [],
		string $type = 'id',
		string $operator = 'IN'
	) {
		// Get a database connection
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		try {
			// Create a new query object
			$query = $db->getQuery(true);

			// Select fields
			$query->select($db->quoteName(['guid', 'properties']))
				  ->from($db->quoteName('#__componentbuilder_fieldtype'))
				  ->where($db->quoteName('published') . ' = 1');

			// Add target filtering
			if (!empty($targets))
			{
				$operator = strtoupper($operator);

				if ($operator === 'IN_STRINGS' || $operator === 'NOT IN_STRINGS')
				{
					$query->where(
						$db->quoteName($type) . ' ' . str_replace('_STRINGS', '', $operator) . ' ("' . implode('","', $targets) . '")'
					);
				}
				else
				{
					$query->where(
						$db->quoteName($type) . ' ' . $operator . ' (' . implode(',', $targets) . ')'
					);
				}
			}

			// Set and execute the query
			$db->setQuery($query);
			$types = $db->loadObjectList('guid');

			// Return early if no data is found
			if (empty($types))
			{
				return null;
			}

			// Process field types and properties
			$_types = [];
			$_properties = [];

			foreach ($types as $guid => $type)
			{
				$properties = json_decode($type->properties ?? '[]');

				foreach ($properties as $property)
				{
					$add = true;

					// Skip excluded properties
					if (UtilitiesArrayHelper::check($exclude) && in_array($property->name, $exclude))
					{
						continue;
					}

					// check if we have filter
					if (UtilitiesArrayHelper::check($filter))
					{
						// Apply filters
						foreach ($filter as $key => $val)
						{
							if (!isset($property->$key) || $property->$key != $val)
							{
								$add = false;
								break;
							}
						}
					}

					// Add property if it passes all checks
					if ($add)
					{
						$_types[$guid][$property->name] = [
							'name' => ucfirst($property->name),
							'example' => $property->example ?? '',
							'description' => $property->description ?? '',
							'mandatory' => isset($property->mandatory) && $property->mandatory == 1,
							'translatable' => isset($property->translatable) && $property->translatable == 1,
						];

						$_properties[$property->name] = ucfirst($property->name);
					}
				}
			}

			// Return processed types and properties
			return ['types' => $_types, 'properties' => $_properties];
		} catch (\RuntimeException $e) {
			// Log the error (optional) and rethrow
			Log::add('Database query failed: ' . $e->getMessage(), Log::ERROR, 'jerror');
			throw new \RuntimeException(Text::_('COM_COMPONENTBUILDER_ERROR_FETCHING_FIELD_TYPES_PROPERTIES_FROM_THE_DATABASE'));
		}
	}

	/**
	 * Remove folders with files
	 * 
	 * @param   string      $path    The path to folder to remove
	 * @param   array|null  $ignore  The folders and files to ignore and not remove
	 *
	 * @return  bool   True if all are removed
	 * @since  3.0.0
	 */
	public static function removeFolder(string $path, ?array $ignore = null): bool
	{
		if (!is_dir($path))
		{
			return false;
		}

		$it = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

		// Prepare a base path without trailing slash for comparison
		$basePath = rtrim($path, '/');

		foreach ($files as $fileinfo)
		{
			$filePath = $fileinfo->getRealPath();

			if (self::removeFolderShouldIgnore($basePath, $filePath, $ignore))
			{
				continue;
			}

			if ($fileinfo->isDir())
			{
				Folder::delete($filePath);
			}
			else
			{
				File::delete($filePath);
			}
		}

		// Delete the root folder if ignore not set
		if (!UtilitiesArrayHelper::check($ignore))
		{
			return Folder::delete($path);
		}

		return true;
	}

	/**
	 * Check if the current path should be ignored.
	 * 
	 * @param   string      $basePath  The base directory path
	 * @param   string      $filePath  The current file or directory path
	 * @param   array|null  $ignore    List of items to ignore
	 *
	 * @return  boolean   True if the path should be ignored
	 * @since 3.2.0
	 */
	protected static function removeFolderShouldIgnore(string $basePath, string $filePath, ?array $ignore = null): bool
	{
		if (!$ignore || !UtilitiesArrayHelper::check($ignore))
		{
			return false;
		}

		foreach ($ignore as $item)
		{
			if (strpos($filePath, $basePath . '/' . $item) !== false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	* The github access token
	**/
	protected static $gitHubAccessToken = "";

	/**
	* The github repo get data errors
	**/
	public static $githubRepoDataErrors = array();

	/**
	 * get the github repo file list
	 *
	 * @return  array on success
	 * 
	 */
	public static function getGithubRepoFileList($type, $target)
	{
		// get the repo data
		if (($repoData = self::getGithubRepoData($type, $target, 'tree')) !== false)
		{
			return $repoData->tree;
		}
		return false;
	}

	/**
	 * get the github repo file list
	 *
	 * @return  array on success
	 * 
	 */
	public static function getGithubRepoData($type, $url, $target = null, $return_type = 'object')
	{
		// always reset errors per/request
		self::$githubRepoDataErrors = array();
		// get the current Packages (public)
		if ('nomemory' === $type || !$repoData = self::get($type))
		{
			// add the token if not already added
			$_url = self::setGithubToken($url);
			// check if the url exist
			if (self::urlExists($_url))
			{
				// get the data from github
				if (($repoData = FileHelper::getContent($_url)) !== false && JsonHelper::check($repoData))
				{
					$github_returned = json_decode($repoData);
					if (UtilitiesStringHelper::check($target) &&
						( (ObjectHelper::check($github_returned) && isset($github_returned->{$target}) && UtilitiesArrayHelper::check($github_returned->{$target})) ||
						(UtilitiesArrayHelper::check($github_returned) && isset($github_returned[$target]) && UtilitiesArrayHelper::check($github_returned[$target])) ))
					{
						if ('nomemory' !== $type)
						{
							// remember to set it
							self::set($type, $repoData);
						}
					}
					elseif (!UtilitiesStringHelper::check($target) && (UtilitiesArrayHelper::check($github_returned) || (ObjectHelper::check($github_returned) && !isset($github_returned->message))))
					{
						if ('nomemory' !== $type)
						{
							// remember to set it
							self::set($type, $repoData);
						}
					}
					// check if we have error message from github
					elseif (($errorMessage = self::githubErrorHandeler(array('error' => null), $github_returned, $type)) !== false)
					{
						if (isset($errorMessage['error']) && UtilitiesStringHelper::check($errorMessage['error']))
						{
							// set the error in the application
							Factory::getApplication()->enqueueMessage($errorMessage['error'], 'Error');
							// set the error also in the class encase it is and Ajax call
							self::$githubRepoDataErrors[] = $errorMessage['error'];
						}
						return false;
					}
					elseif (UtilitiesStringHelper::check($target))
					{
						// setup error string
						$error = Text::sprintf('COM_COMPONENTBUILDER_THE_URL_S_SET_TO_RETRIEVE_THE_PACKAGES_DID_NOT_RETURN_S_DATA', $url, $target);
						// set the error in the application
						Factory::getApplication()->enqueueMessage($error, 'Error');
						// set the error also in the class encase it is and Ajax call
						self::$githubRepoDataErrors[] = $error;
						// we are done here
						return false;
					}
					elseif ('nomemory' !== $type)
					{
						// setup error string
						$error = Text::sprintf('COM_COMPONENTBUILDER_THE_URL_S_SET_TO_RETRIEVE_THE_PACKAGES_DID_NOT_RETURN_S_DATA', $url, $type);
						// set the error in the application
						Factory::getApplication()->enqueueMessage($error, 'Error');
						// set the error also in the class encase it is and Ajax call
						self::$githubRepoDataErrors[] = $error;
						// we are done here
						return false;
					}
					else
					{
						// setup error string
						$error = Text::sprintf('COM_COMPONENTBUILDER_THE_URL_S_SET_TO_RETRIEVE_THE_PACKAGES_DID_NOT_RETURN_VALID_DATA', $url, $type);
						// set the error in the application
						Factory::getApplication()->enqueueMessage($error, 'Error');
						// set the error also in the class encase it is and Ajax call
						self::$githubRepoDataErrors[] = $error;
						// we are done here
						return false;
					}
				}
				else
				{
					// setup error string
					$error = Text::sprintf('COM_COMPONENTBUILDER_THE_URL_S_SET_TO_RETRIEVE_THE_PACKAGES_DOES_NOT_RETURN_ANY_DATA', $url);
					// set the error in the application
					Factory::getApplication()->enqueueMessage($error, 'Error');
					// set the error also in the class encase it is and Ajax call
					self::$githubRepoDataErrors[] = $error;
					// we are done here
					return false;
				}
			}
			else
			{
				// setup error string
				$error = Text::sprintf('COM_COMPONENTBUILDER_THE_URL_S_SET_TO_RETRIEVE_THE_PACKAGES_DOES_NOT_EXIST', $url);
				// set the error in the application
				Factory::getApplication()->enqueueMessage($error, 'Error');
				// set the error also in the class encase it is and Ajax call
				self::$githubRepoDataErrors[] = $error;
				// we are done here
				return false;
			}
		}
		// check if we could find packages
		if (isset($repoData) && JsonHelper::check($repoData))
		{
			if ('object' === $return_type)
			{
				return json_decode($repoData);
			}
			elseif ('array' === $return_type)
			{
				return json_decode($repoData, true);
			}
			return $repoData;
		}
		return false;
	}

	/**
	 * get the github error messages
	 *
	 * @return  array of errors on success
	 * 
	 */
	protected static function githubErrorHandeler($message, &$github, $type)
	{
		if (ObjectHelper::check($github) && isset($github->message) && UtilitiesStringHelper::check($github->message))
		{
			// set the message
			$errorMessage = $github->message;
			// add the documentation URL
			if (isset($github->documentation_url) && UtilitiesStringHelper::check($github->documentation_url))
			{
				$errorMessage = $errorMessage . '<br />' . $github->documentation_url;
			}
			// check the message
			if (strpos($errorMessage, 'Authenticated') !== false)
			{
				if ('nomemory' === $type)
				{
					$type = 'data';
				}
				// add little more help if it is an access token issue
				$errorMessage = Text::sprintf('COM_COMPONENTBUILDER_SBR_YOU_CAN_ADD_A_BGITHUB_ACCESS_TOKENB_TO_COMPONENTBUILDER_GLOBAL_OPTIONS_TO_MAKE_AUTHENTICATED_REQUESTS_TO_GITHUB_AN_ACCESS_TOKEN_WITH_ONLY_PUBLIC_ACCESS_WILL_DO_TO_RETRIEVE_S', $errorMessage, $type);
			}
			// set error notice
			$message['error'] = $errorMessage;
			// we have error message
			return $message;
		}
		return false;
	}

	/**
	 * set the github token
	 *
	 * @return  array of errors on success
	 * 
	 */
	protected static function setGithubToken($url)
	{
		// first check if token already set
		if (strpos($url, 'access_token=') !== false)
		{
			// make sure the token is loaded
			if (!UtilitiesStringHelper::check(self::$gitHubAccessToken))
			{
				// get the global settings
				if (!ObjectHelper::check(self::$params))
				{
					self::$params = ComponentHelper::getParams('com_componentbuilder');
				}
				self::$gitHubAccessToken = self::$params->get('github_access_token', null);
			}
			// make sure the token is loaded at this point
			if (UtilitiesStringHelper::check(self::$gitHubAccessToken))
			{
				$url .= '&access_token=' . self::$gitHubAccessToken;
			}
		}
		return $url;
	}


	/**
	 * get Dynamic Scripts
	 * 
	 * @param   string   $type  The target type of string
	 * @param   string   $fieldName  The target field name of string
	 * 
	 * @return  void
	 * @since  3.0.0
	 */
	public static function getDynamicScripts($type, $fieldName = false)
	{
		// if field name is passed the convert to type
		if ($fieldName)
		{
			$fieldNames = array(
				'php_import_display' => 'display',
				'php_import_setdata' => 'setdata',
				'php_import_save' => 'save',
				'html_import_view' => 'view',
				'php_import' => 'import',
				'php_import_ext' => 'ext',
				'php_import_headers' => 'headers'
			);
			// first check if the field name is found
			if (isset($fieldNames[$type]))
			{
				$type = $fieldNames[$type];
			}
			else
			{
				return '';
			}
		}
		$script = array();
		if ('display' === $type)
		{
			// set the display script
			$script['display'][] = self::_t(1) . "protected \$headerList;";
			$script['display'][] = self::_t(1) . "protected \$hasPackage = false;";
			$script['display'][] = self::_t(1) . "protected \$headers;";
			$script['display'][] = self::_t(1) . "protected \$hasHeader = 0;";
			$script['display'][] = self::_t(1) . "protected \$dataType;";
			$script['display'][] = self::_t(1) . "public function display(\$tpl = null)";
			$script['display'][] = self::_t(1) . "{";
			$script['display'][] = self::_t(2) . "if (\$this->getLayout() !== 'modal')";
			$script['display'][] = self::_t(2) . "{";
			$script['display'][] = self::_t(3) . "// Include helper submenu";
			$script['display'][] = self::_t(3) . "[[[-#-#-Component]]]Helper::addSubmenu('import');";
			$script['display'][] = self::_t(2) . "}";
			$script['display'][] = PHP_EOL . self::_t(2) . "\$paths = new \stdClass;";
			$script['display'][] = self::_t(2) . "\$paths->first = '';";
			$script['display'][] = self::_t(2) . "\$state = \$this->get('state');";
			$script['display'][] = PHP_EOL . self::_t(2) . "\$this->paths = &\$paths;";
			$script['display'][] = self::_t(2) . "\$this->state = &\$state;";
			$script['display'][] = self::_t(2) . "// get global action permissions";
			$script['display'][] = self::_t(2) . "\$this->canDo = [[[-#-#-Component]]]Helper::getActions('import');";
			$script['display'][] = PHP_EOL . self::_t(2) . "// We don't need toolbar in the modal window.";
			$script['display'][] = self::_t(2) . "if (\$this->getLayout() !== 'modal')";
			$script['display'][] = self::_t(2) . "{";
			$script['display'][] = self::_t(3) . "\$this->addToolbar();";
			$script['display'][] = self::_t(3) . "\$this->sidebar = Sidebar::render();";
			$script['display'][] = self::_t(2) . "}";
			$script['display'][] = PHP_EOL . self::_t(2) . "// get the session object";
			$script['display'][] = self::_t(2) . "\$session = Factory::getSession();";
			$script['display'][] = self::_t(2) . "// check if it has package";
			$script['display'][] = self::_t(2) . "\$this->hasPackage" . self::_t(1) . "= \$session->get('hasPackage', false);";
			$script['display'][] = self::_t(2) . "\$this->dataType" . self::_t(1) . "= \$session->get('dataType', false);";
			$script['display'][] = self::_t(2) . "if(\$this->hasPackage && \$this->dataType)";
			$script['display'][] = self::_t(2) . "{";
			$script['display'][] = self::_t(3) . "\$this->headerList" . self::_t(1) . "= json_decode(\$session->get(\$this->dataType.'_VDM_IMPORTHEADERS', false),true);";
			$script['display'][] = self::_t(3) . "\$this->headers" . self::_t(2) . "= [[[-#-#-Component]]]Helper::getFileHeaders(\$this->dataType);";
			$script['display'][] = self::_t(3) . "// clear the data type";
			$script['display'][] = self::_t(3) . "\$session->clear('dataType');";
			$script['display'][] = self::_t(2) . "}";
			$script['display'][] = PHP_EOL . self::_t(2) . "// Check for errors.";
			$script['display'][] = self::_t(2) . "if (count(\$errors = \$this->get('Errors'))){";
			$script['display'][] = self::_t(3) . "throw new Exception(implode(PHP_EOL, \$errors), 500);";
			$script['display'][] = self::_t(2) . "}";
			$script['display'][] = PHP_EOL . self::_t(2) . "// Display the template";
			$script['display'][] = self::_t(2) . "parent::display(\$tpl);";
			$script['display'][] = self::_t(1) . "}";
		}
		elseif ('setdata' === $type)
		{
			// set the setdata script
			$script['setdata'] = array();
			$script['setdata'][] = self::_t(1) . "/**";
			$script['setdata'][] = self::_t(1) . "* Set the data from the spreadsheet to the database";
			$script['setdata'][] = self::_t(1) . "*";
			$script['setdata'][] = self::_t(1) . "* @param string  \$package Paths to the uploaded package file";
			$script['setdata'][] = self::_t(1) . "*";
			$script['setdata'][] = self::_t(1) . "* @return  boolean false on failure";
			$script['setdata'][] = self::_t(1) . "*";
			$script['setdata'][] = self::_t(1) . "**/";
			$script['setdata'][] = self::_t(1) . "protected function setData(\$package,\$table,\$target_headers)";
			$script['setdata'][] = self::_t(1) . "{";
			$script['setdata'][] = self::_t(2) . "if (Super-#-#-___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$target_headers))";
			$script['setdata'][] = self::_t(2) . "{";
			$script['setdata'][] = self::_t(3) . "// make sure the file is loaded";
			$script['setdata'][] = self::_t(3) . "[[[-#-#-Component]]]Helper::composerAutoload('phpspreadsheet');";
			$script['setdata'][] = self::_t(3) . "\$jinput = Factory::getApplication()->getInput();";
			$script['setdata'][] = self::_t(3) . "foreach(\$target_headers as \$header)";
			$script['setdata'][] = self::_t(3) . "{";
			$script['setdata'][] = self::_t(4) . "if ((\$column = \$jinput->getString(\$header, false)) !== false ||";
			$script['setdata'][] = self::_t(5) . "(\$column = \$jinput->getString(strtolower(\$header), false)) !== false)";
			$script['setdata'][] = self::_t(4) . "{";
			$script['setdata'][] = self::_t(5) . "\$data['target_headers'][\$header] = \$column;";
			$script['setdata'][] = self::_t(4) . "}";
			$script['setdata'][] = self::_t(4) . "else";
			$script['setdata'][] = self::_t(4) . "{";
			$script['setdata'][] = self::_t(5) . "\$data['target_headers'][\$header] = null;";
			$script['setdata'][] = self::_t(4) . "}";
			$script['setdata'][] = self::_t(3) . "}";
			$script['setdata'][] = self::_t(3) . "// set the data";
			$script['setdata'][] = self::_t(3) . "if(isset(\$package['dir']))";
			$script['setdata'][] = self::_t(3) . "{";
			$script['setdata'][] = self::_t(4) . "\$inputFileType = IOFactory::identify(\$package['dir']);";
			$script['setdata'][] = self::_t(4) . "\$excelReader = IOFactory::createReader(\$inputFileType);";
			$script['setdata'][] = self::_t(4) . "\$excelReader->setReadDataOnly(true);";
			$script['setdata'][] = self::_t(4) . "\$excelObj = \$excelReader->load(\$package['dir']);";
			$script['setdata'][] = self::_t(4) . "\$data['array'] = \$excelObj->getActiveSheet()->toArray(null, true,true,true);";
			$script['setdata'][] = self::_t(4) . "\$excelObj->disconnectWorksheets();";
			$script['setdata'][] = self::_t(4) . "unset(\$excelObj);";
			$script['setdata'][] = self::_t(4) . "return \$this->save(\$data, \$table);";
			$script['setdata'][] = self::_t(3) . "}";
			$script['setdata'][] = self::_t(2) . "}";
			$script['setdata'][] = self::_t(2) . "return false;";
			$script['setdata'][] = self::_t(1) . "}";
		}
		elseif ('headers' === $type)
		{
			$script['headers'] = array();
			$script['headers'][] = self::_t(1) . "/**";
			$script['headers'][] = self::_t(1) . "* Method to get header.";
			$script['headers'][] = self::_t(1) . "*";
			$script['headers'][] = self::_t(1) . "* @return mixed  An array of data items on success, false on failure.";
			$script['headers'][] = self::_t(1) . "*/";
			$script['headers'][] = self::_t(1) . "public function getExImPortHeaders()";
			$script['headers'][] = self::_t(1) . "{";
			$script['headers'][] = self::_t(2) . "// Get a db connection.";
			$script['headers'][] = self::_t(2) . "\$db = Factory::getContainer()->get(DatabaseInterface::class);";
			$script['headers'][] = self::_t(2) . "// get the columns";
			$script['headers'][] = self::_t(2) . "\$columns = \$db->getTableColumns(\"#__[[[-#-#-component]]]_[[[-#-#-view]]]\");";
			$script['headers'][] = self::_t(2) . "if (Super-#-#-___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$columns))";
			$script['headers'][] = self::_t(2) . "{";
			$script['headers'][] = self::_t(3) . "// remove the headers you don't import/export.";
			$script['headers'][] = self::_t(3) . "unset(\$columns['asset_id']);";
			$script['headers'][] = self::_t(3) . "unset(\$columns['checked_out']);";
			$script['headers'][] = self::_t(3) . "unset(\$columns['checked_out_time']);";
			$script['headers'][] = self::_t(3) . "\$headers = new \stdClass();";
			$script['headers'][] = self::_t(3) . "foreach (\$columns as \$column => \$type)";
			$script['headers'][] = self::_t(3) . "{";
			$script['headers'][] = self::_t(4) . "\$headers->{\$column} = \$column;";
			$script['headers'][] = self::_t(3) . "}";
			$script['headers'][] = self::_t(3) . "return \$headers;";
			$script['headers'][] = self::_t(2) . "}";
			$script['headers'][] = self::_t(2) . "return false;";
			$script['headers'][] = self::_t(1) . "}";
		}
		elseif ('save' === $type)
		{
			$script['save'] = array();
			$script['save'][] = self::_t(1) . "/**";
			$script['save'][] = self::_t(1) . "* Save the data from the file to the database";
			$script['save'][] = self::_t(1) . "*";
			$script['save'][] = self::_t(1) . "* @param string  \$package Paths to the uploaded package file";
			$script['save'][] = self::_t(1) . "*";
			$script['save'][] = self::_t(1) . "* @return  boolean false on failure";
			$script['save'][] = self::_t(1) . "*";
			$script['save'][] = self::_t(1) . "**/";
			$script['save'][] = self::_t(1) . "protected function save(\$data,\$table)";
			$script['save'][] = self::_t(1) . "{";
			$script['save'][] = self::_t(2) . "// import the data if there is any";
			$script['save'][] = self::_t(2) . "if(Super-#-#-___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$data['array']))";
			$script['save'][] = self::_t(2) . "{";
			$script['save'][] = self::_t(3) . "// get user object";
			$script['save'][] = self::_t(3) . "\$user" . self::_t(2) . "= Factory::getApplication()->getIdentity();";
			$script['save'][] = self::_t(3) . "// remove header if it has headers";
			$script['save'][] = self::_t(3) . "\$id_key" . self::_t(1) . "= \$data['target_headers']['id'];";
			$script['save'][] = self::_t(3) . "\$published_key" . self::_t(1) . "= \$data['target_headers']['published'];";
			$script['save'][] = self::_t(3) . "\$ordering_key" . self::_t(1) . "= \$data['target_headers']['ordering'];";
			$script['save'][] = self::_t(3) . "// get the first array set";
			$script['save'][] = self::_t(3) . "\$firstSet = reset(\$data['array']);";
			$script['save'][] = "";
			$script['save'][] = self::_t(3) . "// check if first array is a header array and remove if true";
			$script['save'][] = self::_t(3) . "if(\$firstSet[\$id_key] == 'id' || \$firstSet[\$published_key] == 'published' || \$firstSet[\$ordering_key] == 'ordering')";
			$script['save'][] = self::_t(3) . "{";
			$script['save'][] = self::_t(4) . "array_shift(\$data['array']);";
			$script['save'][] = self::_t(3) . "}";
			$script['save'][] = self::_t(3) . "";
			$script['save'][] = self::_t(3) . "// make sure there is still values in array and that it was not only headers";
			$script['save'][] = self::_t(3) . "if(Super-#-#-___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$data['array']) && \$user->authorise(\$table.'.import', 'com_[[[-#-#-component]]]') && \$user->authorise('core.import', 'com_[[[-#-#-component]]]'))";
			$script['save'][] = self::_t(3) . "{";
			$script['save'][] = self::_t(4) . "// set target.";
			$script['save'][] = self::_t(4) . "\$target" . self::_t(1) . "= array_flip(\$data['target_headers']);";
			$script['save'][] = self::_t(4) . "// Get a db connection.";
			$script['save'][] = self::_t(4) . "\$db = Factory::getContainer()->get(DatabaseInterface::class);";
			$script['save'][] = self::_t(4) . "// set some defaults";
			$script['save'][] = self::_t(4) . "\$todayDate" . self::_t(2) . "= Factory::getDate()->toSql();";
			$script['save'][] = self::_t(4) . "// get global action permissions";
			$script['save'][] = self::_t(4) . "\$canDo" . self::_t(3) . "= [[[-#-#-Component]]]Helper::getActions(\$table);";
			$script['save'][] = self::_t(4) . "\$canEdit" . self::_t(2) . "= \$canDo->get('core.edit');";
			$script['save'][] = self::_t(4) . "\$canState" . self::_t(2) . "= \$canDo->get('core.edit.state');";
			$script['save'][] = self::_t(4) . "\$canCreate" . self::_t(2) . "= \$canDo->get('core.create');";
			$script['save'][] = self::_t(4) . "\$hasAlias" . self::_t(2) . "= \$this->getAliasesUsed(\$table);";
			$script['save'][] = self::_t(4) . "// prosses the data";
			$script['save'][] = self::_t(4) . "foreach(\$data['array'] as \$row)";
			$script['save'][] = self::_t(4) . "{";
			$script['save'][] = self::_t(5) . "\$found = false;";
			$script['save'][] = self::_t(5) . "if (isset(\$row[\$id_key]) && is_numeric(\$row[\$id_key]) && \$row[\$id_key] > 0)";
			$script['save'][] = self::_t(5) . "{";
			$script['save'][] = self::_t(6) . "// raw items import & update!";
			$script['save'][] = self::_t(6) . "\$query = \$db->getQuery(true);";
			$script['save'][] = self::_t(6) . "\$query";
			$script['save'][] = self::_t(7) . "->select('version')";
			$script['save'][] = self::_t(7) . "->from(\$db->quoteName('#__[[[-#-#-component]]]_'.\$table))";
			$script['save'][] = self::_t(7) . "->where(\$db->quoteName('id') . ' = '. \$db->quote(\$row[\$id_key]));";
			$script['save'][] = self::_t(6) . "// Reset the query using our newly populated query object.";
			$script['save'][] = self::_t(6) . "\$db->setQuery(\$query);";
			$script['save'][] = self::_t(6) . "\$db->execute();";
			$script['save'][] = self::_t(6) . "\$found = \$db->getNumRows();";
			$script['save'][] = self::_t(5) . "}";
			$script['save'][] = self::_t(5) . "";
			$script['save'][] = self::_t(5) . "if(\$found && \$canEdit)";
			$script['save'][] = self::_t(5) . "{";
			$script['save'][] = self::_t(6) . "// update item";
			$script['save'][] = self::_t(6) . "\$id" . self::_t(2) . "= \$row[\$id_key];";
			$script['save'][] = self::_t(6) . "\$version" . self::_t(1) . "= \$db->loadResult();";
			$script['save'][] = self::_t(6) . "// reset all buckets";
			$script['save'][] = self::_t(6) . "\$query" . self::_t(2) . "= \$db->getQuery(true);";
			$script['save'][] = self::_t(6) . "\$fields" . self::_t(1) . "= array();";
			$script['save'][] = self::_t(6) . "// Fields to update.";
			$script['save'][] = self::_t(6) . "foreach(\$row as \$key => \$cell)";
			$script['save'][] = self::_t(6) . "{";
			$script['save'][] = self::_t(7) . "// ignore column";
			$script['save'][] = self::_t(7) . "if ('IGNORE' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "continue;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// update modified";
			$script['save'][] = self::_t(7) . "if ('modified_by' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "continue;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// update modified";
			$script['save'][] = self::_t(7) . "if ('modified' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "continue;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// update version";
			$script['save'][] = self::_t(7) . "if ('version' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "\$cell = (int) \$version + 1;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// verify publish authority";
			$script['save'][] = self::_t(7) . "if ('published' == \$target[\$key] && !\$canState)";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "continue;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// set to update array";
			$script['save'][] = self::_t(7) . "if(in_array(\$key, \$data['target_headers']) && is_numeric(\$cell))";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "\$fields[] = \$db->quoteName(\$target[\$key]) . ' = ' . \$cell;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "elseif(in_array(\$key, \$data['target_headers']) && is_string(\$cell))";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "\$fields[] = \$db->quoteName(\$target[\$key]) . ' = ' . \$db->quote(\$cell);";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "elseif(in_array(\$key, \$data['target_headers']) && is_null(\$cell))";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "// if import data is null then set empty";
			$script['save'][] = self::_t(8) . "\$fields[] = \$db->quoteName(\$target[\$key]) . \" = ''\";";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(6) . "}";
			$script['save'][] = self::_t(6) . "// load the defaults";
			$script['save'][] = self::_t(6) . "\$fields[]" . self::_t(1) . "= \$db->quoteName('modified_by') . ' = ' . \$db->quote(\$user->id);";
			$script['save'][] = self::_t(6) . "\$fields[]" . self::_t(1) . "= \$db->quoteName('modified') . ' = ' . \$db->quote(\$todayDate);";
			$script['save'][] = self::_t(6) . "// Conditions for which records should be updated.";
			$script['save'][] = self::_t(6) . "\$conditions = array(";
			$script['save'][] = self::_t(7) . "\$db->quoteName('id') . ' = ' . \$id";
			$script['save'][] = self::_t(6) . ");";
			$script['save'][] = self::_t(6) . "";
			$script['save'][] = self::_t(6) . "\$query->update(\$db->quoteName('#__[[[-#-#-component]]]_'.\$table))->set(\$fields)->where(\$conditions);";
			$script['save'][] = self::_t(6) . "\$db->setQuery(\$query);";
			$script['save'][] = self::_t(6) . "\$db->execute();";
			$script['save'][] = self::_t(5) . "}";
			$script['save'][] = self::_t(5) . "elseif (\$canCreate)";
			$script['save'][] = self::_t(5) . "{";
			$script['save'][] = self::_t(6) . "// insert item";
			$script['save'][] = self::_t(6) . "\$query = \$db->getQuery(true);";
			$script['save'][] = self::_t(6) . "// reset all buckets";
			$script['save'][] = self::_t(6) . "\$columns" . self::_t(1) . "= array();";
			$script['save'][] = self::_t(6) . "\$values" . self::_t(1) . "= array();";
			$script['save'][] = self::_t(6) . "\$version" . self::_t(1) . "= false;";
			$script['save'][] = self::_t(6) . "// Insert columns. Insert values.";
			$script['save'][] = self::_t(6) . "foreach(\$row as \$key => \$cell)";
			$script['save'][] = self::_t(6) . "{";
			$script['save'][] = self::_t(7) . "// ignore column";
			$script['save'][] = self::_t(7) . "if ('IGNORE' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "continue;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// remove id";
			$script['save'][] = self::_t(7) . "if ('id' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "continue;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// update created";
			$script['save'][] = self::_t(7) . "if ('created_by' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "continue;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// update created";
			$script['save'][] = self::_t(7) . "if ('created' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "continue;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// Make sure the alias is incremented";
			$script['save'][] = self::_t(7) . "if ('alias' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "\$cell = \$this->getAlias(\$cell,\$table);";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// update version";
			$script['save'][] = self::_t(7) . "if ('version' == \$target[\$key])";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "\$cell = 1;";
			$script['save'][] = self::_t(8) . "\$version = true;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "// set to insert array";
			$script['save'][] = self::_t(7) . "if(in_array(\$key, \$data['target_headers']) && is_numeric(\$cell))";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "\$columns[]" . self::_t(1) . "= \$target[\$key];";
			$script['save'][] = self::_t(8) . "\$values[]" . self::_t(1) . "= \$cell;";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "elseif(in_array(\$key, \$data['target_headers']) && is_string(\$cell))";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "\$columns[]" . self::_t(1) . "= \$target[\$key];";
			$script['save'][] = self::_t(8) . "\$values[]" . self::_t(1) . "= \$db->quote(\$cell);";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(7) . "elseif(in_array(\$key, \$data['target_headers']) && is_null(\$cell))";
			$script['save'][] = self::_t(7) . "{";
			$script['save'][] = self::_t(8) . "// if import data is null then set empty";
			$script['save'][] = self::_t(8) . "\$columns[]" . self::_t(1) . "= \$target[\$key];";
			$script['save'][] = self::_t(8) . "\$values[]" . self::_t(1) . "= \"''\";";
			$script['save'][] = self::_t(7) . "}";
			$script['save'][] = self::_t(6) . "}";
			$script['save'][] = self::_t(6) . "// load the defaults";
			$script['save'][] = self::_t(6) . "\$columns[]" . self::_t(1) . "= 'created_by';";
			$script['save'][] = self::_t(6) . "\$values[]" . self::_t(1) . "= \$db->quote(\$user->id);";
			$script['save'][] = self::_t(6) . "\$columns[]" . self::_t(1) . "= 'created';";
			$script['save'][] = self::_t(6) . "\$values[]" . self::_t(1) . "= \$db->quote(\$todayDate);";
			$script['save'][] = self::_t(6) . "if (!\$version)";
			$script['save'][] = self::_t(6) . "{";
			$script['save'][] = self::_t(7) . "\$columns[]" . self::_t(1) . "= 'version';";
			$script['save'][] = self::_t(7) . "\$values[]" . self::_t(1) . "= 1;";
			$script['save'][] = self::_t(6) . "}";
			$script['save'][] = self::_t(6) . "// Prepare the insert query.";
			$script['save'][] = self::_t(6) . "\$query";
			$script['save'][] = self::_t(7) . "->insert(\$db->quoteName('#__[[[-#-#-component]]]_'.\$table))";
			$script['save'][] = self::_t(7) . "->columns(\$db->quoteName(\$columns))";
			$script['save'][] = self::_t(7) . "->values(implode(',', \$values));";
			$script['save'][] = self::_t(6) . "// Set the query using our newly populated query object and execute it.";
			$script['save'][] = self::_t(6) . "\$db->setQuery(\$query);";
			$script['save'][] = self::_t(6) . "\$done = \$db->execute();";
			$script['save'][] = self::_t(6) . "if (\$done)";
			$script['save'][] = self::_t(6) . "{";
			$script['save'][] = self::_t(7) . "\$aId = \$db->insertid();";
			$script['save'][] = self::_t(7) . "// make sure the access of asset is set";
			$script['save'][] = self::_t(7) . "[[[-#-#-Component]]]Helper::setAsset(\$aId,\$table);";
			$script['save'][] = self::_t(6) . "}";
			$script['save'][] = self::_t(5) . "}";
			$script['save'][] = self::_t(5) . "else";
			$script['save'][] = self::_t(5) . "{";
			$script['save'][] = self::_t(6) . "return false;";
			$script['save'][] = self::_t(5) . "}";
			$script['save'][] = self::_t(4) . "}";
			$script['save'][] = self::_t(4) . "return true;";
			$script['save'][] = self::_t(3) . "}";
			$script['save'][] = self::_t(2) . "}";
			$script['save'][] = self::_t(2) . "return false;";
			$script['save'][] = self::_t(1) . "}";
		}
		elseif ('view' === $type)
		{
			$script['view'] = array();
			$script['view'][] = "<script type=\"text/javascript\">";
			$script['view'][] = "<?php if (\$this->hasPackage && Super-#-#-___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$this->headerList)) : ?>";
			$script['view'][] = self::_t(1) . "Joomla.continueImport = function()";
			$script['view'][] = self::_t(1) . "{";
			$script['view'][] = self::_t(2) . "var form = document.getElementById('adminForm');";
			$script['view'][] = self::_t(2) . "var error = false;";
			$script['view'][] = self::_t(2) . "var therequired = [<?php \$i = 0; foreach(\$this->headerList as \$name => \$title) { echo (\$i != 0)? ', \"vdm_'.\$name.'\"':'\"vdm_'.\$name.'\"'; \$i++; } ?>];";
			$script['view'][] = self::_t(2) . "for(i = 0; i < therequired.length; i++)";
			$script['view'][] = self::_t(2) . "{";
			$script['view'][] = self::_t(3) . "if(jQuery('#'+therequired[i]).val() == \"\" )";
			$script['view'][] = self::_t(3) . "{";
			$script['view'][] = self::_t(4) . "error = true;";
			$script['view'][] = self::_t(4) . "break;";
			$script['view'][] = self::_t(3) . "}";
			$script['view'][] = self::_t(2) . "}";
			$script['view'][] = self::_t(2) . "// do field validation";
			$script['view'][] = self::_t(2) . "if (error)";
			$script['view'][] = self::_t(2) . "{";
			$script['view'][] = self::_t(3) . "alert(\"<?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_MSG_PLEASE_SELECT_ALL_COLUMNS', true); ?>\");";
			$script['view'][] = self::_t(2) . "}";
			$script['view'][] = self::_t(2) . "else";
			$script['view'][] = self::_t(2) . "{";
			$script['view'][] = self::_t(3) . "jQuery('#loading').css('display', 'block');";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(3) . "form.gettype.value = 'continue';";
			$script['view'][] = self::_t(3) . "form.submit();";
			$script['view'][] = self::_t(2) . "}";
			$script['view'][] = self::_t(1) . "};";
			$script['view'][] = "<?php else: ?>";
			$script['view'][] = self::_t(1) . "Joomla.submitbutton = function()";
			$script['view'][] = self::_t(1) . "{";
			$script['view'][] = self::_t(2) . "var form = document.getElementById('adminForm');";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(2) . "// do field validation";
			$script['view'][] = self::_t(2) . "if (form.import_package.value == \"\")";
			$script['view'][] = self::_t(2) . "{";
			$script['view'][] = self::_t(3) . "alert(\"<?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_MSG_PLEASE_SELECT_A_FILE', true); ?>\");";
			$script['view'][] = self::_t(2) . "}";
			$script['view'][] = self::_t(2) . "else";
			$script['view'][] = self::_t(2) . "{";
			$script['view'][] = self::_t(3) . "jQuery('#loading').css('display', 'block');";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(3) . "form.gettype.value = 'upload';";
			$script['view'][] = self::_t(3) . "form.submit();";
			$script['view'][] = self::_t(2) . "}";
			$script['view'][] = self::_t(1) . "};";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(1) . "Joomla.submitbutton3 = function()";
			$script['view'][] = self::_t(1) . "{";
			$script['view'][] = self::_t(2) . "var form = document.getElementById('adminForm');";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(2) . "// do field validation";
			$script['view'][] = self::_t(2) . "if (form.import_directory.value == \"\"){";
			$script['view'][] = self::_t(3) . "alert(\"<?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_MSG_PLEASE_SELECT_A_DIRECTORY', true); ?>\");";
			$script['view'][] = self::_t(2) . "}";
			$script['view'][] = self::_t(2) . "else";
			$script['view'][] = self::_t(2) . "{";
			$script['view'][] = self::_t(3) . "jQuery('#loading').css('display', 'block');";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(3) . "form.gettype.value = 'folder';";
			$script['view'][] = self::_t(3) . "form.submit();";
			$script['view'][] = self::_t(2) . "}";
			$script['view'][] = self::_t(1) . "};";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(1) . "Joomla.submitbutton4 = function()";
			$script['view'][] = self::_t(1) . "{";
			$script['view'][] = self::_t(2) . "var form = document.getElementById('adminForm');";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(2) . "// do field validation";
			$script['view'][] = self::_t(2) . "if (form.import_url.value == \"\" || form.import_url.value == \"http://\")";
			$script['view'][] = self::_t(2) . "{";
			$script['view'][] = self::_t(3) . "alert(\"<?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_MSG_ENTER_A_URL', true); ?>\");";
			$script['view'][] = self::_t(2) . "}";
			$script['view'][] = self::_t(2) . "else";
			$script['view'][] = self::_t(2) . "{";
			$script['view'][] = self::_t(3) . "jQuery('#loading').css('display', 'block');";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(3) . "form.gettype.value = 'url';";
			$script['view'][] = self::_t(3) . "form.submit();";
			$script['view'][] = self::_t(2) . "}";
			$script['view'][] = self::_t(1) . "};";
			$script['view'][] = "<?php endif; ?>";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . "// Add spindle-wheel for importations:";
			$script['view'][] = "jQuery(document).ready(function(\$) {";
			$script['view'][] = self::_t(1) . "var outerDiv = \$('body');";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(1) . "\$('<div id=\"loading\"></div>')";
			$script['view'][] = self::_t(2) . ".css(\"background\", \"rgba(255, 255, 255, .8) url('components/com_[[[-#-#-component]]]/assets/images/ajax.gif') 50% 35% no-repeat\")";
			$script['view'][] = self::_t(2) . ".css(\"top\", outerDiv.position().top - \$(window).scrollTop())";
			$script['view'][] = self::_t(2) . ".css(\"left\", outerDiv.position().left - \$(window).scrollLeft())";
			$script['view'][] = self::_t(2) . ".css(\"width\", outerDiv.width())";
			$script['view'][] = self::_t(2) . ".css(\"height\", outerDiv.height())";
			$script['view'][] = self::_t(2) . ".css(\"position\", \"fixed\")";
			$script['view'][] = self::_t(2) . ".css(\"opacity\", \"0.80\")";
			$script['view'][] = self::_t(2) . ".css(\"-ms-filter\", \"progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)\")";
			$script['view'][] = self::_t(2) . ".css(\"filter\", \"alpha(opacity = 80)\")";
			$script['view'][] = self::_t(2) . ".css(\"display\", \"none\")";
			$script['view'][] = self::_t(2) . ".appendTo(outerDiv);";
			$script['view'][] = "});";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . "</script>";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . "<div id=\"installer-import\" class=\"clearfix\">";
			$script['view'][] = "<form enctype=\"multipart/form-data\" action=\"<?php echo Route::_('index.php?option=com_[[[-#-#-component]]]&view=import_[[[-#-#-views]]]');?>\" method=\"post\" name=\"adminForm\" id=\"adminForm\" class=\"form-horizontal form-validate\">";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(1) . "<?php if (!empty( \$this->sidebar)) : ?>";
			$script['view'][] = self::_t(2) . "<div id=\"j-sidebar-container\" class=\"span2\">";
			$script['view'][] = self::_t(3) . "<?php echo \$this->sidebar; ?>";
			$script['view'][] = self::_t(2) . "</div>";
			$script['view'][] = self::_t(2) . "<div id=\"j-main-container\" class=\"span10\">";
			$script['view'][] = self::_t(1) . "<?php else : ?>";
			$script['view'][] = self::_t(2) . "<div id=\"j-main-container\">";
			$script['view'][] = self::_t(1) . "<?php endif;?>";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(1) . "<?php if (\$this->hasPackage && Super-#-#-___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$this->headerList) && Super-#-#-___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$this->headers)) : ?>";
			$script['view'][] = self::_t(2) . "<fieldset class=\"uploadform\">";
			$script['view'][] = self::_t(3) . "<legend><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_LINK_FILE_TO_TABLE_COLUMNS'); ?></legend>";
			$script['view'][] = self::_t(3) . "<div class=\"control-group\">";
			$script['view'][] = self::_t(4) . "<label class=\"control-label\" ><h4><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_TABLE_COLUMNS'); ?></h4></label>";
			$script['view'][] = self::_t(4) . "<div class=\"controls\">";
			$script['view'][] = self::_t(5) . "<label class=\"control-label\" ><h4><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_FILE_COLUMNS'); ?></h4></label>";
			$script['view'][] = self::_t(4) . "</div>";
			$script['view'][] = self::_t(3) . "</div>";
			$script['view'][] = self::_t(3) . "<?php foreach(\$this->headerList as \$name => \$title): ?>";
			$script['view'][] = self::_t(4) . "<div class=\"control-group\">";
			$script['view'][] = self::_t(5) . "<label for=\"<?php echo \$name; ?>\" class=\"control-label\" ><?php echo \$title; ?></label>";
			$script['view'][] = self::_t(5) . "<div class=\"controls\">";
			$script['view'][] = self::_t(6) . "<select  name=\"<?php echo \$name; ?>\"  id=\"vdm_<?php echo \$name; ?>\" required class=\"required input_box\" >";
			$script['view'][] = self::_t(7) . "<option value=\"\"><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_PLEASE_SELECT_COLUMN'); ?></option>";
			$script['view'][] = self::_t(7) . "<option value=\"IGNORE\"><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_IGNORE_COLUMN'); ?></option>";
			$script['view'][] = self::_t(7) . "<?php foreach(\$this->headers as \$value => \$option): ?>";
			$script['view'][] = self::_t(8) . "<?php \$selected = (strtolower(\$option) ==  strtolower (\$title) || strtolower(\$option) == strtolower(\$name))? 'selected=\"selected\"':''; ?>";
			$script['view'][] = self::_t(8) . "<option value=\"<?php echo [[[-#-#-Component]]]Helper::htmlEscape(\$value); ?>\" class=\"required\" <?php echo \$selected ?>><?php echo [[[-#-#-Component]]]Helper::htmlEscape(\$option); ?></option>";
			$script['view'][] = self::_t(7) . "<?php endforeach; ?>";
			$script['view'][] = self::_t(6) . "</select>";
			$script['view'][] = self::_t(5) . "</div>";
			$script['view'][] = self::_t(4) . "</div>";
			$script['view'][] = self::_t(3) . "<?php endforeach; ?>";
			$script['view'][] = self::_t(3) . "<div class=\"form-actions\">";
			$script['view'][] = self::_t(4) . "<input class=\"btn btn-primary\" type=\"button\" value=\"<?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_CONTINUE'); ?>\" onclick=\"Joomla.continueImport()\" />";
			$script['view'][] = self::_t(3) . "</div>";
			$script['view'][] = self::_t(2) . "</fieldset>";
			$script['view'][] = self::_t(2) . "<input type=\"hidden\" name=\"gettype\" value=\"continue\" />";
			$script['view'][] = self::_t(1) . "<?php else: ?>";
			$script['view'][] = self::_t(2) . "<?php echo Html::_('bootstrap.startTabSet', 'myTab', array('active' => 'upload')); ?>";
			$script['view'][] = self::_t(2) . "";
			$script['view'][] = self::_t(2) . "<?php echo Html::_('bootstrap.addTab', 'myTab', 'upload', JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_FROM_UPLOAD', true)); ?>";
			$script['view'][] = self::_t(3) . "<fieldset class=\"uploadform\">";
			$script['view'][] = self::_t(4) . "<legend><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_UPDATE_DATA'); ?></legend>";
			$script['view'][] = self::_t(4) . "<div class=\"control-group\">";
			$script['view'][] = self::_t(5) . "<label for=\"import_package\" class=\"control-label\"><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_SELECT_FILE'); ?></label>";
			$script['view'][] = self::_t(5) . "<div class=\"controls\">";
			$script['view'][] = self::_t(6) . "<input class=\"input_box\" id=\"import_package\" name=\"import_package\" type=\"file\" size=\"57\" />";
			$script['view'][] = self::_t(5) . "</div>";
			$script['view'][] = self::_t(4) . "</div>";
			$script['view'][] = self::_t(4) . "<div class=\"form-actions\">";
			$script['view'][] = self::_t(5) . "<input class=\"btn btn-primary\" type=\"button\" value=\"<?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_UPLOAD_BOTTON'); ?>\" onclick=\"Joomla.submitbutton()\" />&nbsp;&nbsp;&nbsp;<small><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_FORMATS_ACCEPTED'); ?> (.csv .xls .ods)</small>";
			$script['view'][] = self::_t(4) . "</div>";
			$script['view'][] = self::_t(3) . "</fieldset>";
			$script['view'][] = self::_t(2) . "<?php echo Html::_('bootstrap.endTab'); ?>";
			$script['view'][] = self::_t(2) . "";
			$script['view'][] = self::_t(2) . "<?php echo Html::_('bootstrap.addTab', 'myTab', 'directory', JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_FROM_DIRECTORY', true)); ?>";
			$script['view'][] = self::_t(3) . "<fieldset class=\"uploadform\">";
			$script['view'][] = self::_t(4) . "<legend><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_UPDATE_DATA'); ?></legend>";
			$script['view'][] = self::_t(4) . "<div class=\"control-group\">";
			$script['view'][] = self::_t(5) . "<label for=\"import_directory\" class=\"control-label\"><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_SELECT_FILE_DIRECTORY'); ?></label>";
			$script['view'][] = self::_t(5) . "<div class=\"controls\">";
			$script['view'][] = self::_t(6) . "<input type=\"text\" id=\"import_directory\" name=\"import_directory\" class=\"span5 input_box\" size=\"70\" value=\"<?php echo \$this->state->get('import.directory'); ?>\" />";
			$script['view'][] = self::_t(5) . "</div>";
			$script['view'][] = self::_t(4) . "</div>";
			$script['view'][] = self::_t(4) . "<div class=\"form-actions\">";
			$script['view'][] = self::_t(5) . "<input type=\"button\" class=\"btn btn-primary\" value=\"<?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_GET_BOTTON'); ?>\" onclick=\"Joomla.submitbutton3()\" />&nbsp;&nbsp;&nbsp;<small><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_FORMATS_ACCEPTED'); ?> (.csv .xls .ods)</small>";
			$script['view'][] = self::_t(4) . "</div>";
			$script['view'][] = self::_t(4) . "</fieldset>";
			$script['view'][] = self::_t(2) . "<?php echo Html::_('bootstrap.endTab'); ?>";
			$script['view'][] = "";
			$script['view'][] = PHP_EOL . self::_t(2) . "<?php echo Html::_('bootstrap.addTab', 'myTab', 'url', JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_FROM_URL', true)); ?>";
			$script['view'][] = self::_t(3) . "<fieldset class=\"uploadform\">";
			$script['view'][] = self::_t(4) . "<legend><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_UPDATE_DATA'); ?></legend>";
			$script['view'][] = self::_t(4) . "<div class=\"control-group\">";
			$script['view'][] = self::_t(5) . "<label for=\"import_url\" class=\"control-label\"><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_SELECT_FILE_URL'); ?></label>";
			$script['view'][] = self::_t(5) . "<div class=\"controls\">";
			$script['view'][] = self::_t(6) . "<input type=\"text\" id=\"import_url\" name=\"import_url\" class=\"span5 input_box\" size=\"70\" value=\"http://\" />";
			$script['view'][] = self::_t(5) . "</div>";
			$script['view'][] = self::_t(4) . "</div>";
			$script['view'][] = self::_t(4) . "<div class=\"form-actions\">";
			$script['view'][] = self::_t(5) . "<input type=\"button\" class=\"btn btn-primary\" value=\"<?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_GET_BOTTON'); ?>\" onclick=\"Joomla.submitbutton4()\" />&nbsp;&nbsp;&nbsp;<small><?php echo JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_FORMATS_ACCEPTED'); ?> (.csv .xls .ods)</small>";
			$script['view'][] = self::_t(4) . "</div>";
			$script['view'][] = self::_t(3) . "</fieldset>";
			$script['view'][] = self::_t(2) . "<?php echo Html::_('bootstrap.endTab'); ?>";
			$script['view'][] = self::_t(2) . "<?php echo Html::_('bootstrap.endTabSet'); ?>";
			$script['view'][] = self::_t(2) . "<input type=\"hidden\" name=\"gettype\" value=\"upload\" />";
			$script['view'][] = self::_t(1) . "<?php endif; ?>";
			$script['view'][] = self::_t(1) . "<input type=\"hidden\" name=\"task\" value=\"import_[[[-#-#-views]]].import\" />";
			$script['view'][] = self::_t(1) . "<?php echo Html::_('form.token'); ?>";
			$script['view'][] = "</form>";
			$script['view'][] = "</div>";
		}
		elseif ('import' === $type)
		{
			$script['import'] = array();
			$script['import'][] = self::_t(1) . "/**";
			$script['import'][] = self::_t(1) . " * Import an spreadsheet from either folder, url or upload.";
			$script['import'][] = self::_t(1) . " *";
			$script['import'][] = self::_t(1) . " * @return  boolean result of import";
			$script['import'][] = self::_t(1) . " *";
			$script['import'][] = self::_t(1) . " */";
			$script['import'][] = self::_t(1) . "public function import()";
			$script['import'][] = self::_t(1) . "{";
			$script['import'][] = self::_t(2) . "\$this->setState('action', 'import');";
			$script['import'][] = self::_t(2) . "\$app" . self::_t(2) . "= Factory::getApplication();";
			$script['import'][] = self::_t(2) . "\$session" . self::_t(1) . "= Factory::getSession();";
			$script['import'][] = self::_t(2) . "\$package" . self::_t(1) . "= null;";
			$script['import'][] = self::_t(2) . "\$continue" . self::_t(1) . "= false;";
			$script['import'][] = self::_t(2) . "// get import type";
			$script['import'][] = self::_t(2) . "\$this->getType = \$app->input->getString('gettype', NULL);";
			$script['import'][] = self::_t(2) . "// get import type";
			$script['import'][] = self::_t(2) . "\$this->dataType" . self::_t(1) . "= \$session->get('dataType_VDM_IMPORTINTO', NULL);";
			$script['import'][] = PHP_EOL . self::_t(2) . "if (\$package === null)";
			$script['import'][] = self::_t(2) . "{";
			$script['import'][] = self::_t(3) . "switch (\$this->getType)";
			$script['import'][] = self::_t(3) . "{";
			$script['import'][] = self::_t(4) . "case 'folder':";
			$script['import'][] = self::_t(5) . "// Remember the 'Import from Directory' path.";
			$script['import'][] = self::_t(5) . "\$app->getUserStateFromRequest(\$this->_context . '.import_directory', 'import_directory');";
			$script['import'][] = self::_t(5) . "\$package = \$this->_getPackageFromFolder();";
			$script['import'][] = self::_t(5) . "break;";
			$script['import'][] = PHP_EOL . self::_t(4) . "case 'upload':";
			$script['import'][] = self::_t(5) . "\$package = \$this->_getPackageFromUpload();";
			$script['import'][] = self::_t(5) . "break;";
			$script['import'][] = PHP_EOL . self::_t(4) . "case 'url':";
			$script['import'][] = self::_t(5) . "\$package = \$this->_getPackageFromUrl();";
			$script['import'][] = self::_t(5) . "break;";
			$script['import'][] = PHP_EOL . self::_t(4) . "case 'continue':";
			$script['import'][] = self::_t(5) . "\$continue" . self::_t(1) . "= true;";
			$script['import'][] = self::_t(5) . "\$package" . self::_t(1) . "= \$session->get('package', null);";
			$script['import'][] = self::_t(5) . "\$package" . self::_t(1) . "= json_decode(\$package, true);";
			$script['import'][] = self::_t(5) . "// clear session";
			$script['import'][] = self::_t(5) . "\$session->clear('package');";
			$script['import'][] = self::_t(5) . "\$session->clear('dataType');";
			$script['import'][] = self::_t(5) . "\$session->clear('hasPackage');";
			$script['import'][] = self::_t(5) . "break;";
			$script['import'][] = PHP_EOL . self::_t(4) . "default:";
			$script['import'][] = self::_t(5) . "\$app->setUserState('com_[[[-#-#-component]]].message', JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_NO_IMPORT_TYPE_FOUND'));";
			$script['import'][] = PHP_EOL . self::_t(5) . "return false;";
			$script['import'][] = self::_t(5) . "break;";
			$script['import'][] = self::_t(3) . "}";
			$script['import'][] = self::_t(2) . "}";
			$script['import'][] = self::_t(2) . "// Was the package valid?";
			$script['import'][] = self::_t(2) . "if (!\$package || !\$package['type'])";
			$script['import'][] = self::_t(2) . "{";
			$script['import'][] = self::_t(3) . "if (in_array(\$this->getType, array('upload', 'url')))";
			$script['import'][] = self::_t(3) . "{";
			$script['import'][] = self::_t(4) . "\$this->remove(\$package['packagename']);";
			$script['import'][] = self::_t(3) . "}";
			$script['import'][] = PHP_EOL . self::_t(3) . "\$app->setUserState('com_[[[-#-#-component]]].message', JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_UNABLE_TO_FIND_IMPORT_PACKAGE'));";
			$script['import'][] = self::_t(3) . "return false;";
			$script['import'][] = self::_t(2) . "}";
			$script['import'][] = self::_t(2) . "";
			$script['import'][] = self::_t(2) . "// first link data to table headers";
			$script['import'][] = self::_t(2) . "if(!\$continue){";
			$script['import'][] = self::_t(3) . "\$package" . self::_t(1) . "= json_encode(\$package);";
			$script['import'][] = self::_t(3) . "\$session->set('package', \$package);";
			$script['import'][] = self::_t(3) . "\$session->set('dataType', \$this->dataType);";
			$script['import'][] = self::_t(3) . "\$session->set('hasPackage', true);";
			$script['import'][] = self::_t(3) . "return true;";
			$script['import'][] = self::_t(2) . "}";
			$script['import'][] = self::_t(2) . "// set the data";
			$script['import'][] = self::_t(2) . "\$headerList = json_decode(\$session->get(\$this->dataType.'_VDM_IMPORTHEADERS', false), true);";
			$script['import'][] = self::_t(2) . "if (!\$this->setData(\$package,\$this->dataType,\$headerList))";
			$script['import'][] = self::_t(2) . "{";
			$script['import'][] = self::_t(3) . "// There was an error importing the package";
			$script['import'][] = self::_t(3) . "\$msg = JTe-#-#-xt::_('COM_[[[-#-#-COMPONENT]]]_IMPORT_ERROR');";
			$script['import'][] = self::_t(3) . "\$back = \$session->get('backto_VDM_IMPORT', NULL);";
			$script['import'][] = self::_t(3) . "if (\$back)";
			$script['import'][] = self::_t(3) . "{";
			$script['import'][] = self::_t(4) . "\$app->setUserState('com_[[[-#-#-component]]].redirect_url', 'index.php?option=com_[[[-#-#-component]]]&view='.\$back);";
			$script['import'][] = self::_t(4) . "\$session->clear('backto_VDM_IMPORT');";
			$script['import'][] = self::_t(3) . "}";
			$script['import'][] = self::_t(3) . "\$result = false;";
			$script['import'][] = self::_t(2) . "}";
			$script['import'][] = self::_t(2) . "else";
			$script['import'][] = self::_t(2) . "{";
			$script['import'][] = self::_t(3) . "// Package imported sucessfully";
			$script['import'][] = self::_t(3) . "\$msg = JTe-#-#-xt::sprintf('COM_[[[-#-#-COMPONENT]]]_IMPORT_SUCCESS', \$package['packagename']);";
			$script['import'][] = self::_t(3) . "\$back = \$session->get('backto_VDM_IMPORT', NULL);";
			$script['import'][] = self::_t(3) . "if (\$back)";
			$script['import'][] = self::_t(3) . "{";
			$script['import'][] = self::_t(4) . "\$app->setUserState('com_[[[-#-#-component]]].redirect_url', 'index.php?option=com_[[[-#-#-component]]]&view='.\$back);";
			$script['import'][] = self::_t(4) . "\$session->clear('backto_VDM_IMPORT');";
			$script['import'][] = self::_t(3) . "}";
			$script['import'][] = self::_t(3) . "\$result = true;";
			$script['import'][] = self::_t(2) . "}";
			$script['import'][] = PHP_EOL . self::_t(2) . "// Set some model state values";
			$script['import'][] = self::_t(2) . "\$app->enqueueMessage(\$msg);";
			$script['import'][] = PHP_EOL . self::_t(2) . "// remove file after import";
			$script['import'][] = self::_t(2) . "\$this->remove(\$package['packagename']);";
			$script['import'][] = self::_t(2) . "\$session->clear(\$this->getType.'_VDM_IMPORTHEADERS');";
			$script['import'][] = self::_t(2) . "return \$result;";
			$script['import'][] = self::_t(1) . "}";
		}
		elseif ('ext' === $type)
		{
			$script['ext'][] = self::_t(1) . "/**";
			$script['ext'][] = self::_t(1) . " * Check the extension";
			$script['ext'][] = self::_t(1) . " *";
			$script['ext'][] = self::_t(1) . " * @param   string  \$file    Name of the uploaded file";
			$script['ext'][] = self::_t(1) . " *";
			$script['ext'][] = self::_t(1) . " * @return  boolean  True on success";
			$script['ext'][] = self::_t(1) . " *";
			$script['ext'][] = self::_t(1) . " */";
			$script['ext'][] = self::_t(1) . "protected function checkExtension(\$file)";
			$script['ext'][] = self::_t(1) . "{";
			$script['ext'][] = self::_t(2) . "// check the extention";
			$script['ext'][] = self::_t(2) . "switch(strtolower(pathinfo(\$file, PATHINFO_EXTENSION)))";
			$script['ext'][] = self::_t(2) . "{";
			$script['ext'][] = self::_t(3) . "case 'xls':";
			$script['ext'][] = self::_t(3) . "case 'ods':";
			$script['ext'][] = self::_t(3) . "case 'csv':";
			$script['ext'][] = self::_t(3) . "return true;";
			$script['ext'][] = self::_t(3) . "break;";
			$script['ext'][] = self::_t(2) . "}";
			$script['ext'][] = self::_t(2) . "return false;";
			$script['ext'][] = self::_t(1) . "}";
		}
		elseif ('routerparse' === $type)
		{
			$script['routerparse'][] = self::_t(4) . "// default script in switch for this view";
			$script['routerparse'][] = self::_t(4) . "\$vars['view'] = '[[[-#-#-sview]]]';";
			$script['routerparse'][] = self::_t(4) . "if (is_numeric(\$segments[\$count-1]))";
			$script['routerparse'][] = self::_t(4) . "{";
			$script['routerparse'][] = self::_t(5) . "\$vars['id'] = (int) \$segments[\$count-1];";
			$script['routerparse'][] = self::_t(4) . "}";
			$script['routerparse'][] = self::_t(4) . "elseif (\$segments[\$count-1])";
			$script['routerparse'][] = self::_t(4) . "{";
			$script['routerparse'][] = self::_t(5) . "\$id = \$this->getVar('[[[-#-#-sview]]]', \$segments[\$count-1], 'alias', 'id');";
			$script['routerparse'][] = self::_t(5) . "if(\$id)";
			$script['routerparse'][] = self::_t(5) . "{";
			$script['routerparse'][] = self::_t(6) . "\$vars['id'] = \$id;";
			$script['routerparse'][] = self::_t(5) . "}";
			$script['routerparse'][] = self::_t(4) . "}";
		}
		// return the needed script
		if (isset($script[$type]))
		{
			return str_replace('-#-#-', '', implode(PHP_EOL, $script[$type]));
		}
		return false;
	}

	/**
	 * Field Grouping https://docs.joomla.org/Form_field
	 * @deprecated 3.3 
	 **/
	protected static $fieldGroups = array(
		'default' => array(
			'accesslevel', 'cachehandler', 'calendar', 'captcha', 'category', 'checkbox', 'checkboxes', 'chromestyle',
			'color', 'combo', 'componentlayout', 'contentlanguage', 'contenttype', 'databaseconnection', 'components',
			'editor', 'editors', 'email', 'file', 'file', 'filelist', 'folderlist', 'groupedlist', 'headertag', 'helpsite', 'hidden', 'imagelist',
			'integer', 'language', 'list', 'media', 'menu', 'modal_menu', 'menuitem', 'meter', 'modulelayout', 'moduleorder', 'moduleposition',
			'moduletag', 'note', 'number', 'password', 'plugins', 'predefinedlist', 'radio', 'range', 'repeatable', 'rules',
			'sessionhandler', 'spacer', 'sql', 'subform', 'tag', 'tel', 'templatestyle', 'text', 'textarea', 'timezone', 'url', 'user', 'usergroup', 'usergrouplist'
		),
		'plain' => array(
			'cachehandler', 'calendar', 'checkbox', 'chromestyle', 'color', 'componentlayout', 'contenttype', 'editor', 'editors', 'captcha',
			'email', 'file', 'headertag', 'helpsite', 'hidden', 'integer', 'language', 'media', 'menu', 'modal_menu', 'menuitem', 'meter', 'modulelayout', 'templatestyle',
			'moduleorder', 'moduletag', 'number', 'password', 'range', 'rules', 'tag', 'tel', 'text', 'textarea', 'timezone', 'url', 'user', 'usergroup', 'usergrouplist'
		),
		'option' => array(
			'accesslevel', 'category', 'checkboxes', 'combo', 'contentlanguage', 'databaseconnection', 'components',
			'filelist', 'folderlist', 'imagelist', 'list', 'plugins', 'predefinedlist', 'radio', 'sessionhandler', 'sql', 'groupedlist'
		),
		'text' => array(
			'calendar', 'color', 'editor', 'email', 'number', 'password', 'range', 'tel', 'text', 'textarea', 'url'
		),
		'list' => array(
			'checkbox', 'checkboxes', 'list', 'radio', 'groupedlist', 'combo'
		),
		'dynamic' => array(
			'category', 'file', 'filelist', 'folderlist', 'headertag', 'imagelist', 'integer', 'media', 'meter', 'rules', 'tag', 'timezone', 'user'
		),
		'spacer' => array(
			'note', 'spacer'
		),
		'special' => array(
			'contentlanguage', 'moduleposition', 'plugin', 'repeatable', 'subform'
		),
		'search' => array(
			'editor', 'email', 'tel', 'text', 'textarea', 'url', 'subform'
		)
	);

	/**
	 * Field Checker
	 *
	 * @param   string   $type The field type
	 * @param   boolean  $option The field grouping
	 *
	 * @return  boolean if the field was found
	 * @deprecated 3.3 Use CompilerFactory::_('Field.Groups')->check($type, $option);
	 */
	public static function fieldCheck($type, $option = 'default')
	{
		return CompilerFactory::_('Field.Groups')->check($type, $option);
	}

	/**
	 * get the field types $key -> name of a group or groups
	 *
	 * @return  array  the field types
	 * @deprecated 3.3 Use CompilerFactory::_('Field.Groups')->types($groups, $key);
	 */
	public static function getFieldTypesByGroup($groups = [], $key = 'id')
	{
		return CompilerFactory::_('Field.Groups')->types($groups, $key);
	}

	/**
	 * get the field types IDs of a group or groups
	 *
	 * @return  array  ids of the field types
	 * @deprecated 3.3 Use CompilerFactory::_('Field.Groups')->typesIds($groups);
	 */
	public static function getFieldTypesIdsByGroup($groups = array())
	{
		return CompilerFactory::_('Field.Groups')->typesIds($groups);
	}

	/**
	 * get the spacer IDs
	 *
	 * @return  array  ids of the spacer field types
	 * @deprecated 3.3 Use CompilerFactory::_('Field.Groups')->spacerIds();
	 */
	public static function getSpacerIds()
	{
		return CompilerFactory::_('Field.Groups')->spacerIds();
	}

	/**
	 * get the field types Guid's of a group or groups
	 *
	 * @return  array  Guid of the field types
	 * @deprecated 3.3 Use CompilerFactory::_('Field.Groups')->typesGuids($groups);
	 */
	public static function getFieldTypesGuidsByGroup($groups = [])
	{
		return CompilerFactory::_('Field.Groups')->typesGuids($groups);
	}

	/**
	 * get the spacer Guid's
	 *
	 * @return  array  Guids of the spacer field types
	 * @deprecated 3.3 Use CompilerFactory::_('Field.Groups')->spacerGuids();
	 */
	public static function getSpacerGuids()
	{
		return CompilerFactory::_('Field.Groups')->spacerGuids();
	}

	/**
	 * open base64 string if stored as base64
	 *
	 * @param   string   $data   The base64 string
	 * @param   string   $key    We store the string with that suffix :)
	 * @param   string   $default    The default switch
	 *
	 * @return  string   The opened string
	 * @deprecated 3.3 Use Base64Helper::open($data, $key, $default);
	 */
	public static function openValidBase64($data, $key = '__.o0=base64=Oo.__', $default = 'string')
	{
		return Base64Helper::open($data, $key, $default);
	}

	/**
	 * prepare base64 string for url
	 *
	 * @deprecate Use urlencode();
	 */
	public static function base64_urlencode($string, $encode = false)
	{
		if ($encode)
		{
			$string = base64_encode($string);
		}
		return str_replace(array('+', '/'), array('-', '_'), $string);
	}

	/**
	 * prepare base64 string form url
	 *
	 * @deprecate
	 */
	public static function base64_urldecode($string, $decode = false)
	{
		$string = str_replace(array('-', '_'), array('+', '/'), $string);
		if ($decode)
		{
			$string = base64_decode($string);
		}
		return $string;
	}


	/**
	* Get the file path or url
	* 
	* @param  string   $type              The (url/path) type to return
	* @param  string   $target            The Params Target name (if set)
	* @param  string   $default           The default path if not set in Params (fallback path)
	* @param  bool     $createIfNotSet    The switch to create the folder if not found
	*
	* @return  string    On success the path or url is returned based on the type requested
	* 
	*/
	public static function getFolderPath($type = 'path', $target = 'folderpath', $default = '', $createIfNotSet = true)
	{
		// make sure to always have a string/path
		if(!UtilitiesStringHelper::check($default))
		{
			$default = JPATH_SITE . '/images/';
		}
		// get the global settings
		if (!ObjectHelper::check(self::$params))
		{
			self::$params = ComponentHelper::getParams('com_componentbuilder');
		}
		$folderPath = self::$params->get($target, $default);
		// create the folder if it does not exist
		if ($createIfNotSet && !is_dir($folderPath))
		{
			Folder::create($folderPath);
		}
		// return the url
		if ('url' === $type)
		{
			if (strpos($folderPath, JPATH_SITE) !== false)
			{
				$folderPath = trim( str_replace( JPATH_SITE, '', $folderPath), '/');
				return Uri::root() . $folderPath . '/';
			}
			// since the path is behind the root folder of the site, return only the root url (may be used to build the link)
			return Uri::root();
		}
		// sanitize the path
		return '/' . trim( $folderPath, '/' ) . '/';
	}


	/**
	* the Crypt objects
	**/
	protected static $CRYPT = array();

	/**
	* the Cipher MODE switcher (list of ciphers)
	**/
	protected static $setCipherMode = array(
		'AES' => true,
		'Rijndael' => true,
		'Twofish' => false, // can but not good idea
		'Blowfish' => false,  // can but not good idea
		'RC4' => false, // nope
		'RC2' => false,  // can but not good idea
		'TripleDES' => false,  // can but not good idea
		'DES' => true
	);

	/**
	* get the Crypt object
	*
	* @return  object on success with Crypt power
	**/
	public static function crypt($type, $mode = null)
	{
		// set key based on mode
		if ($mode)
		{
			$key = $type . $mode;
		}
		else
		{
			$key = $type;
		}
		// check if it was already set
		if (isset(self::$CRYPT[$key]) && ObjectHelper::check(self::$CRYPT[$key]))
		{
			return self::$CRYPT[$key];
		}
		// make sure we have the composer classes loaded
		self::composerAutoload('phpseclib');
		// build class name
		$CLASS = '\phpseclib\Crypt\\' . $type;
		// make sure we have the phpseclib classes
		if (!class_exists($CLASS))
		{
			// class not in place so send out error
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_BSB_LIBRARYCLASS_IS_NOT_AVAILABLE_THIS_LIBRARYCLASS_SHOULD_HAVE_BEEN_ADDED_TO_YOUR_BLIBRARIESPHPSECLIBVENDORB_FOLDER_PLEASE_CONTACT_YOUR_SYSTEM_ADMINISTRATOR_FOR_MORE_INFO', $CLASS), 'Error');
			return false;
		}
		// does this crypt class use mode
		if ($mode && isset(self::$setCipherMode[$type]) && self::$setCipherMode[$type])
		{
			switch ($mode)
			{
				case 'CTR':
					self::$CRYPT[$key] = new $CLASS('ctr');
				break;
				case 'ECB':
					self::$CRYPT[$key] = new $CLASS('ecb');
				break;
				case 'CBC':
					self::$CRYPT[$key] = new $CLASS('cbc');
				break;
				case 'CBC3':
					self::$CRYPT[$key] = new $CLASS('cbc3');
				break;
				case 'CFB':
					self::$CRYPT[$key] = new $CLASS('cfb');
				break;
				case 'CFB8':
					self::$CRYPT[$key] = new $CLASS('cfb8');
				break;
				case 'OFB':
					self::$CRYPT[$key] = new $CLASS('ofb');
				break;
				case 'GCM':
					self::$CRYPT[$key] = new $CLASS('gcm');
				break;
				case 'STREAM':
					self::$CRYPT[$key] = new $CLASS('stream');
				break;
				default:
					// No valid mode has been specified
					Factory::getApplication()->enqueueMessage(Text::_('COM_COMPONENTBUILDER_NO_VALID_MODE_HAS_BEEN_SPECIFIED'), 'Error');
					return false;
				break;
			}
		}
		else
		{
			// set the default
			self::$CRYPT[$key] = new $CLASS();
		}
		// return the object
		return self::$CRYPT[$key];
	}

	/**
	* Move File to Server
	*
	* @param   string    $localPath     The local path to the file
	* @param   string    $fileName      The the actual file name
	* @param   int         $serverID       The server local id to use
	* @param   int         $protocol        The server protocol to use
	* @param   string    $permission    The permission validation area
	*
	* @return  bool      true on success
	**/
	public static function moveToServer($localPath, $fileName, $serverID, $protocol = null, $permission = 'core.export')
	{
		// get the server
		if ($server = self::getServer( (int) $serverID, $protocol, $permission))
		{
			// use the FTP protocol
			if (1 == $server->jcb_protocol)
			{
				// now move the file
				if (!$server->store($localPath, $fileName))
				{
					Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_BSB_FILE_COULD_NOT_BE_MOVED_TO_BSB_SERVER', $fileName, $server->jcb_remote_server_name[(int) $serverID]), 'Error');
					return false;
				}
				// close the connection
				$server->quit();
			}
			// use the SFTP protocol
			elseif (2 == $server->jcb_protocol)
			{
				// get the remote path
				$remote_path = '/' . trim($server->jcb_remote_server_path[(int) $serverID], '/') . '/' . $fileName;
				// now move the file
				if (!$server->put($remote_path, FileHelper::getContent($localPath, null)))
				{
					Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_BSB_FILE_COULD_NOT_BE_MOVED_TO_BSB_PATH_ON_BSB_SERVER', $fileName, $server->jcb_remote_server_path[(int) $serverID], $server->jcb_remote_server_name[(int) $serverID]), 'Error');
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	* the SFTP objects
	**/
	protected static $sftp = array();

	/**
	* the FTP objects
	**/
	protected static $ftp = array();

	/**
	* get the server object
	*
	* @param   int         $serverID       The server local id to use
	* @param   int         $protocol        The server protocol to use
	* @param   string    $permission    The permission validation area
	*
	* @return  object     on success server object
	**/
	public static function getServer($serverID, $protocol = null, $permission = 'core.export')
	{
		// if not protocol is given get it (sad I know)
		if (!$protocol)
		{
			$protocol = self::getVar('server', (int) $serverID, 'id', 'protocol');
		}
		// return the server object
		switch ($protocol)
		{
			case 1: // FTP
				return self::getFtp($serverID, $permission);
			break;
			case 2: // SFTP
				return self::getSftp($serverID, $permission);
			break;
		}
		return false;
	}

	/**
	* get the sftp object
	*
	* @param   int         $serverID       The server local id to use
	* @param   string    $permission    The permission validation area
	*
	* @return  object on success with sftp power
	**/
	public static function getSftp($serverID, $permission = 'core.export')
	{
		// check if we have a server with that id
		if ($server = self::getServerDetails($serverID, 2, $permission))
		{
			// check if it was already set
			if (!isset(self::$sftp[$server->cache]) || !ObjectHelper::check(self::$sftp[$server->cache]))
			{
				// make sure we have the composer classes loaded
				self::composerAutoload('phpseclib');
				// make sure we have the phpseclib classes
				if (!class_exists('\phpseclib\Net\SFTP'))
				{
					// class not in place so send out error
					Factory::getApplication()->enqueueMessage(Text::_('COM_COMPONENTBUILDER_THE_BPHPSECLIBNETSFTPB_LIBRARYCLASS_IS_NOT_AVAILABLE_THIS_LIBRARYCLASS_SHOULD_HAVE_BEEN_ADDED_TO_YOUR_BLIBRARIESVDM_IOVENDORB_FOLDER_PLEASE_CONTACT_YOUR_SYSTEM_ADMINISTRATOR_FOR_MORE_INFO'), 'Error');
					return false;
				}
				// insure the port is set
				$server->port = (isset($server->port) && is_numeric($server->port) && $server->port > 0) ? (int) $server->port : 22;
				// open the connection
				self::$sftp[$server->cache] = new phpseclib\Net\SFTP($server->host, $server->port);
				// heads-up on protocol
				self::$sftp[$server->cache]->jcb_protocol = 2; // SFTP <-- if called not knowing what type of protocol is being used
				// now login based on authentication type
				switch($server->authentication)
				{
					case 1: // password
						if (!self::$sftp[$server->cache]->login($server->username, $server->password))
						{
							Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_LOGIN_TO_BSB_HAS_FAILED_PLEASE_CHECK_THAT_YOUR_DETAILS_ARE_CORRECT', $server->name), 'Error');
							unset(self::$sftp[$server->cache]);
							return false;
						}
					break;
					case 2: // private key file
						if (ObjectHelper::check(self::crypt('RSA')))
						{
							// check if we have a passprase
							if (UtilitiesStringHelper::check($server->secret))
							{
								self::crypt('RSA')->setPassword($server->secret);
							}
							// now load the key file
							if (!self::crypt('RSA')->loadKey(FileHelper::getContent($server->private, null)))
							{
								Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_PRIVATE_KEY_FILE_COULD_NOT_BE_LOADEDFOUND_FOR_BSB_SERVER', $server->name), 'Error');
								unset(self::$sftp[$server->cache]);
								return false;
							}
							// now login
							if (!self::$sftp[$server->cache]->login($server->username, self::crypt('RSA')))
							{
								Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_LOGIN_TO_BSB_HAS_FAILED_PLEASE_CHECK_THAT_YOUR_DETAILS_ARE_CORRECT', $server->name), 'Error');
								unset(self::$sftp[$server->cache]);
								return false;
							}
						}
					break;
					case 3: // both password and private key file
						if (ObjectHelper::check(self::crypt('RSA')))
						{
							// check if we have a passphrase
							if (UtilitiesStringHelper::check($server->secret))
							{
								self::crypt('RSA')->setPassword($server->secret);
							}
							// now load the key file
							if (!self::crypt('RSA')->loadKey(FileHelper::getContent($server->private, null)))
							{
								Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_PRIVATE_KEY_FILE_COULD_NOT_BE_LOADEDFOUND_FOR_BSB_SERVER', $server->name), 'Error');
								unset(self::$sftp[$server->cache]);
								return false;
							}
							// now login
							if (!self::$sftp[$server->cache]->login($server->username, $server->password, self::crypt('RSA')))
							{
								Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_LOGIN_TO_BSB_HAS_FAILED_PLEASE_CHECK_THAT_YOUR_DETAILS_ARE_CORRECT', $server->name), 'Error');
								unset(self::$sftp[$server->cache]);
								return false;
							}
						}
					break;
					case 4: // private key field
						if (ObjectHelper::check(self::crypt('RSA')))
						{
							// check if we have a passprase
							if (UtilitiesStringHelper::check($server->secret))
							{
								self::crypt('RSA')->setPassword($server->secret);
							}
							// now load the key field
							if (!self::crypt('RSA')->loadKey($server->private_key))
							{
								Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_PRIVATE_KEY_FIELD_COULD_NOT_BE_LOADED_FOR_BSB_SERVER', $server->name), 'Error');
								unset(self::$sftp[$server->cache]);
								return false;
							}
							// now login
							if (!self::$sftp[$server->cache]->login($server->username, self::crypt('RSA')))
							{
								Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_LOGIN_TO_BSB_HAS_FAILED_PLEASE_CHECK_THAT_YOUR_DETAILS_ARE_CORRECT', $server->name), 'Error');
								unset(self::$sftp[$server->cache]);
								return false;
							}
						}
					break;
					case 5: // both password and private key field
						if (ObjectHelper::check(self::crypt('RSA')))
						{
							// check if we have a passphrase
							if (UtilitiesStringHelper::check($server->secret))
							{
								self::crypt('RSA')->setPassword($server->secret);
							}
							// now load the key file
							if (!self::crypt('RSA')->loadKey($server->private_key))
							{
								Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_PRIVATE_KEY_FIELD_COULD_NOT_BE_LOADED_FOR_BSB_SERVER', $server->name), 'Error');
								unset(self::$sftp[$server->cache]);
								return false;
							}
							// now login
							if (!self::$sftp[$server->cache]->login($server->username, $server->password, self::crypt('RSA')))
							{
								Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_LOGIN_TO_BSB_HAS_FAILED_PLEASE_CHECK_THAT_YOUR_DETAILS_ARE_CORRECT', $server->name), 'Error');
								unset(self::$sftp[$server->cache]);
								return false;
							}
						}
					break;
				}
			}
			// only continue if object is set
			if (isset(self::$sftp[$server->cache]) && ObjectHelper::check(self::$sftp[$server->cache]))
			{
				// set the unique buckets
				if (!isset(self::$sftp[$server->cache]->jcb_remote_server_name))
				{
					self::$sftp[$server->cache]->jcb_remote_server_name = array();
					self::$sftp[$server->cache]->jcb_remote_server_path = array();
				}
				// always set the name and remote server path
				self::$sftp[$server->cache]->jcb_remote_server_name[$serverID] = $server->name;
				self::$sftp[$server->cache]->jcb_remote_server_path[$serverID] = (UtilitiesStringHelper::check($server->path) && $server->path !== '/') ? $server->path : '';
				// return the sftp object
				return self::$sftp[$server->cache];
			}
		}
		return false;
	}

	/**
	* get the JClientFtp object
	*
	* @param   int        $serverID         The server local id to use
	* @param   string    $permission    The permission validation area
	*
	* @return  object on success with ftp power
	**/
	public static function getFtp($serverID, $permission)
	{
		// check if we have a server with that id
		if ($server = self::getServerDetails($serverID, 1, $permission))
		{
			// check if we already have the server instance
			if (isset(self::$ftp[$server->cache]) && self::$ftp[$server->cache] instanceof FtpClient)
			{
				// always set the name and remote server path
				self::$ftp[$server->cache]->jcb_remote_server_name[$serverID] = $server->name;
				// if still connected we are ready to go
				if (self::$ftp[$server->cache]->isConnected())
				{
					// return the FTP instance
					return self::$ftp[$server->cache];
				}
				// check if we can reinitialise the server
				if (self::$ftp[$server->cache]->reinit())
				{
					// return the FTP instance
					return self::$ftp[$server->cache];
				}
			}
			// make sure we have a string and it is not default or empty
			if (UtilitiesStringHelper::check($server->signature))
			{
				// turn into variables
				parse_str($server->signature); // because of this I am using strange variable naming to avoid any collisions.
				// set options
				if (isset($options) && UtilitiesArrayHelper::check($options))
				{
					foreach ($options as $o__p0t1on => $vAln3)
					{
						if ('timeout' === $o__p0t1on)
						{
							$options[$o__p0t1on] = (int) $vAln3;
						}
						if ('type' === $o__p0t1on)
						{
							$options[$o__p0t1on] = (string) $vAln3;
						}
					}
				}
				else
				{
					$options = array();
				}
				// get ftp object
				if (isset($host) && $host != 'HOSTNAME' && isset($port) && $port != 'PORT_INT' && isset($username) && $username != 'user@name.com' && isset($password) && $password != 'password')
				{
					// load for reuse
					self::$ftp[$server->cache] = FtpClient::getInstance($host, $port, $options, $username, $password);
				}
				else
				{
					// load error to indicate signature was in error
					Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_FTP_SIGNATURE_FOR_BSB_WAS_NOT_WELL_FORMED_PLEASE_CHECK_YOUR_SIGNATURE_DETAILS', $server->name), 'Error');
					return false;
				}
				// check if we are connected
				if (self::$ftp[$server->cache] instanceof FtpClient && self::$ftp[$server->cache]->isConnected())
				{
					// heads-up on protocol
					self::$ftp[$server->cache]->jcb_protocol = 1; // FTP <-- if called not knowing what type of protocol is being used
					// set the unique buckets
					if (!isset(self::$ftp[$server->cache]->jcb_remote_server_name))
					{
						self::$ftp[$server->cache]->jcb_remote_server_name = array();
					}
					// always set the name and remote server path
					self::$ftp[$server->cache]->jcb_remote_server_name[$serverID] = $server->name;
					// return the FTP instance
					return self::$ftp[$server->cache];
				}
				// reset since we have no connection
				unset(self::$ftp[$server->cache]);
			}
			// load error to indicate signature was in error
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_FTP_CONNECTION_FOR_BSB_COULD_NOT_BE_MADE_PLEASE_CHECK_YOUR_SIGNATURE_DETAILS', $server->name), 'Error');
		}
		return false;
	}

	/**
	* get the server details
	*
	* @param   int         $serverID       The server local id to use
	* @param   int         $protocol        The server protocol to use
	* @param   string    $permission    The permission validation area
	*
	* @return  object    on success with server details
	**/
	public static function getServerDetails($serverID, $protocol = 2, $permission = 'core.export')
	{
		// check if this user has permission to access items
		if (!Factory::getApplication()->getIdentity()->authorise($permission, 'com_componentbuilder'))
		{
			// set message to inform the user that permission was denied
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_YOU_DO_NOT_HAVE_PERMISSION_TO_ACCESS_THE_SERVER_DETAILS_BS_DENIEDB_PLEASE_CONTACT_YOUR_SYSTEM_ADMINISTRATOR_FOR_MORE_INFO', UtilitiesStringHelper::safe($permission, 'w')), 'Error');
			return false;
		}
		// now insure we have correct values 
		if (is_int($serverID) && is_int($protocol))
		{
			// Get a db connection
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			// start the query
			$query = $db->getQuery(true);
			// select based to protocol
			if (2 == $protocol)
			{
				// SFTP
				$query->select($db->quoteName(array('name','authentication','username','host','password','path','port','private','private_key','secret')));
				// cache builder
				$cache = array('authentication','username','host','password','port','private','private_key','secret');
			}
			else
			{
				// FTP
				$query->select($db->quoteName(array('name','signature')));
				// cache builder
				$cache = array('signature');
			}
			$query->from($db->quoteName('#__componentbuilder_server'));
			$query->where($db->quoteName('id') . ' = ' . (int) $serverID);
			$query->where($db->quoteName('protocol') . ' = ' . (int) $protocol);
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				$server = $db->loadObject();
				// Get the basic encryption.
				$basickey = self::getCryptKey('basic', 'Th1sMnsTbL0ck@d');
				// Get the encryption object.
				$basic = new AES($basickey, 128);
				// start cache keys
				$keys = array();
				// unlock the needed fields
				foreach($server as $name => &$value)
				{
					// unlock the needed fields
					if ($name !== 'name' && !empty($value) && $basickey && !is_numeric($value) && $value === base64_encode(base64_decode($value, true)))
					{
						// basic decrypt of data
						$value = rtrim($basic->decryptString($value), "\0");
					}
					// build cache (keys) for lower connection latency
					if (in_array($name, $cache))
					{
						$keys[] = $value;
					}
				}
				// check if cache keys were found
				if (UtilitiesArrayHelper::check($keys))
				{
					// now set cache
					$server->cache = md5(implode('', $keys));
				}
				else
				{
					// default is ID
					$server->cache = $serverID;
				}
				// return the server details
				return $server;
			}
		}
		Factory::getApplication()->enqueueMessage(Text::sprintf('COM_COMPONENTBUILDER_THE_SERVER_DETAILS_FOR_BID_SB_COULD_NOT_BE_RETRIEVED', $serverID), 'Error');
		return false;
	}

	/**
	 * Load the Composer Vendor phpseclib
	 */
	protected static function composephpseclib()
	{
		// load the autoloader for phpseclib
		require_once JPATH_SITE . '/libraries/phpseclib/vendor/autoload.php';
		// do not load again
		self::$composer['phpseclib'] = true;

		return  true;
	}


	/**
	 * the locker
	 *
	 * @var array
	 * @since   3.1
	 */
	protected static array $locker = [];

	/**
	 * the dynamic replacement salt
	 *
	 * @var array
	 * @since   3.1
	 */
	protected static array $globalSalt = [];

	/**
	 * the timer
	 *
	 * @var object
	 * @since   3.1
	 */
	protected static $keytimer;

	/**
	 * To Lock string
	 *
	 * @param string       $string     The string/array to lock
	 * @param string|null  $key        The custom key to use
	 * @param int          $salt       The switch to add salt and type of salt
	 * @param int|null     $dynamic    The dynamic replacement array of salt build string
	 * @param int          $urlencode  The switch to control url encoding
	 *
	 * @return string    Encrypted String
	 * @since   3.1
	 */
	public static function lock(string $string, ?string $key = null, int $salt = 2, ?int $dynamic = null, $urlencode = true): string
	{
		// get the global settings
		if (!$key || !UtilitiesStringHelper::check($key))
		{
			// set temp timer
			$timer = 2;
			// if we have a timer use it
			if ($salt > 0)
			{
				$timer = $salt;
			}
			// set the default key
			$key = self::salt($timer, $dynamic);
			// try getting the system key
			if (method_exists(get_called_class(), "getCryptKey")) 
			{
				// try getting the medium key first the fall back to basic, and then default
				$key = self::getCryptKey('medium', self::getCryptKey('basic', $key));
			}
		}
		// check if we have a salt timer
		if ($salt > 0)
		{
			$key .= self::salt($salt, $dynamic);
		}
		// get the locker settings
		if (!isset(self::$locker[$key]) || !ObjectHelper::check(self::$locker[$key]))
		{
			self::$locker[$key] = new AES($key, 128);
		}
		// convert array or object to string
		if (UtilitiesArrayHelper::check($string) || ObjectHelper::check($string))
		{
			$string = serialize($string);
		}
		// prep for url
		if ($urlencode && method_exists(get_called_class(), "base64_urlencode"))
		{
			return self::base64_urlencode(self::$locker[$key]->encryptString($string));
		}
		return self::$locker[$key]->encryptString($string);
	}

	/**
	 * To un-Lock string
	 *
	 * @param string  $string       The string to unlock
	 * @param string  $key          The custom key to use
	 * @param int      $salt           The switch to add salt and type of salt
	 * @param int      $dynamic    The dynamic replacement array of salt build string
	 * @param int      $urlencode  The switch to control url decoding
	 *
	 * @return string    Decrypted String
	 * @since   3.1
	 */
	public static function unlock($string, $key = null, $salt = 2, $dynamic = null, $urlencode = true): string
	{
		// get the global settings
		if (!$key || !UtilitiesStringHelper::check($key))
		{
			// set temp timer
			$timer = 2;
			// if we have a timer use it
			if ($salt > 0)
			{
				$timer = $salt;
			}
			// set the default key
			$key = self::salt($timer, $dynamic);
			// try getting the system key
			if (method_exists(get_called_class(), "getCryptKey")) 
			{
				// try getting the medium key first the fall back to basic, and then default
				$key = self::getCryptKey('medium', self::getCryptKey('basic', $key));
			}
		}
		// check if we have a salt timer
		if ($salt > 0)
		{
			$key .= self::salt($salt, $dynamic);
		}
		// get the locker settings
		if (!isset(self::$locker[$key]) || !ObjectHelper::check(self::$locker[$key]))
		{
			self::$locker[$key] = new AES($key, 128);
		}
		// make sure we have real base64
		if ($urlencode && method_exists(get_called_class(), "base64_urldecode"))
		{
			$string = self::base64_urldecode($string);
		}
		// basic decrypt string.
		if (!empty($string) && !is_numeric($string) && $string === base64_encode(base64_decode($string, true)))
		{
			$string = rtrim(self::$locker[$key]->decryptString($string), "\0");
			// convert serial string to array
			if (self::is_serial($string))
			{
				$string = unserialize($string);
			}
		}

		return $string;
	}

	/**
	 * The Salt
	 *
	 * @param int   $type      The type of length the salt should be valid
	 * @param int   $dynamic   The dynamic replacement array of salt build string
	 *
	 * @return string
	 * @since   3.1
	 */
	public static function salt(int $type = 1, $dynamic = null): string
	{
		// get dynamic replacement salt
		$dynamic = self::getDynamicSalt($dynamic);
		// get the key timer
		if (!ObjectHelper::check(self::$keytimer))
		{
			// load the date time object
			self::$keytimer = new DateTime;
			// set the correct time stamp
			$vdmLocalTime = new DateTimeZone('Africa/Windhoek');
			self::$keytimer->setTimezone($vdmLocalTime);
		}
		// set type
		if ($type == 2)
		{
			// hour
			$format = 'Y-m-d \o\n ' . self::periodFix(self::$keytimer->format('H'));
		}
		elseif ($type == 3)
		{
			// day
			$format = 'Y-m-' . self::periodFix(self::$keytimer->format('d'));
		}
		elseif ($type == 4)
		{
			// month
			$format = 'Y-' . self::periodFix(self::$keytimer->format('m'));
		}
		else
		{
			// minute
			$format = 'Y-m-d \o\n H:' . self::periodFix(self::$keytimer->format('i'));
		}
		// get key
		if (UtilitiesArrayHelper::check($dynamic))
		{
			return md5(str_replace(array_keys($dynamic), array_values($dynamic), self::$keytimer->format($format) . ' @ VDM.I0'));
		}
		return md5(self::$keytimer->format($format) . ' @ VDM.I0');
	}

	/**
	 * The function to insure the salt is valid within the given period (third try)
	 *
	 * @param   int $main    The main number
	 * @since   3.1
	 */
	protected static function periodFix(int $main): int
	{
		return intdiv($main + 1, 3) * 3;
	}

	/**
	 * Check if a string is serialized
	 *
	 * @param  string   $string
	 *
	 * @return Boolean
	 * @since   3.1
	 */
	public static function is_serial(string $string): bool
	{
		return (@unserialize($string) !== false);
	}

	/**
	 * Get dynamic replacement salt
	 * @since   3.1
	 */
	public static function getDynamicSalt($dynamic = null)
	{
		// load global if not manually set
		if (!UtilitiesArrayHelper::check($dynamic))
		{
			return self::getGlobalSalt();
		}
		// return manual values if set
		else
		{
			return $dynamic;
		}
	}

	/**
	 * The random or dynamic secret salt
	 * @since   3.1
	 */
	public static function getSecretSalt($string = null, $size = 9)
	{
		// set the string
		if (!$string)
		{
			// get random string 
			$string = self::randomkey($size);
		}
		// convert string to array
		$string = UtilitiesStringHelper::safe($string);
		// convert string to array
		$array = str_split($string);
		// insure only unique values are used
		$array = array_unique($array);
		// set the size
		$size = ($size <= count($array)) ? $size : count($array);
		// down size the 
		return array_slice($array, 0, $size);
	}

	/**
	 * Get global replacement salt
	 * @since   3.1
	 */
	public static function getGlobalSalt()
	{
		// load from memory if found
		if (!UtilitiesArrayHelper::check(self::$globalSalt))
		{
			// get the global settings
			if (!ObjectHelper::check(self::$params))
			{
				self::$params = ComponentHelper::getParams('com_componentbuilder');
			}
			// check if we have a global dynamic replacement array available (format -->  ' 1->!,3->E,4->A')
			$tmp = self::$params->get('dynamic_salt', null);
			if (UtilitiesStringHelper::check($tmp) && strpos($tmp, ',') !== false && strpos($tmp, '->') !== false)
			{
				$salt = array_map('trim', (array) explode(',', $tmp));
				if (UtilitiesArrayHelper::check($salt ))
				{
					foreach($salt as $replace)
					{
						$dynamic = array_map('trim', (array) explode('->', $replace));
						if (isset($dynamic[0]) && isset($dynamic[1]))
						{
							self::$globalSalt[$dynamic[0]] = $dynamic[1];
						}
					}
				}
			}
		}
		// return global if found
		if (UtilitiesArrayHelper::check(self::$globalSalt))
		{
			return self::$globalSalt;
		}
		// return default as fail safe
		return array('1' => '!', '3' => 'E', '4' => 'A');	
	}

	/**
	 * Close public protocol
	 * @since   3.1
	 */
	public static function closePublicProtocol($id, $public)
	{
		// get secret salt
		$secretSalt = self::getSecretSalt(self::salt(1,array('4' => 'R','1' => 'E','2' => 'G','7' => 'J','8' => 'A')));
		// get the key
		$key = self::salt(1, $secretSalt);
		// get secret salt
		$secret = self::getSecretSalt();
		// set the secret
		$close['SECRET'] = self::lock($secret, $key, 1, array('1' => 's', '3' => 'R', '4' => 'D'));
		// get the key
		$key = self::salt(1, $secret);
		// get the public key
		$close['PUBLIC'] = self::lock($public, $key, 1, array('1' => '!', '3' => 'E', '4' => 'A'));
		// get secret salt
		$secretSalt = self::getSecretSalt($public);
		// get the key
		$key = self::salt(1, $secretSalt);
		// get the ID
		$close['ID'] = self::unlock($id, $key, 1, array('1' => 'i', '3' => 'e', '4' => 'B'));
		// return closed values
		return $close;
	}

	/**
	 * Open public protocol
	 * @since   3.1
	 */
	public static function openPublicProtocol($SECRET, $ID, $PUBLIC)
	{
		// get secret salt
		$secretSalt = self::getSecretSalt(self::salt(1,array('4' => 'R','1' => 'E','2' => 'G','7' => 'J','8' => 'A')));
		// get the key
		$key = self::salt(1, $secretSalt);
		// get the $SECRET
		$SECRET = self::unlock($SECRET, $key, 1, array('1' => 's', '3' => 'R', '4' => 'D'));
		// get the key
		$key = self::salt(1, $SECRET);
		// get the public key
		$open['public'] = self::unlock($PUBLIC, $key, 1, array('1' => '!', '3' => 'E', '4' => 'A'));
		// get secret salt
		$secretSalt = self::getSecretSalt($open['public']);
		// get the key
		$key = self::salt(1, $secretSalt);
		// get the ID
		$open['id'] = self::unlock($ID, $key, 1, array('1' => 'i', '3' => 'e', '4' => 'B'));
		// return opened values
		return $open;
	}

	/**
	 * Get an edit button.
	 *
	 * @param  mixed   $item       The item to edit.
	 * @param  string  $view       The type of item to edit.
	 * @param  string  $views      The list view controller name.
	 * @param  string  $ref        The return path.
	 * @param  string  $component  The component these views belong to.
	 * @param  string  $headsup    The message to show on click of button.
	 *
	 * @return  string  On success the full HTML link.
	 * @since   3.0.9
	 */
	public static function getEditButton(
		$item,
		string $view,
		string $views,
		string $ref = '',
		string $component = 'com_componentbuilder',
		string $headsup = 'COM_COMPONENTBUILDER_ALL_UNSAVED_WORK_ON_THIS_PAGE_WILL_BE_LOST_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE'
	): string
	{
		$url = self::getEditURL($item, $view, $views, $ref, $component);

		if ($url === null)
		{
			return '';
		}

		$checkedOut = self::getCheckedOut($item, $view, $component);
		$title = UtilitiesStringHelper::safe(Text::_('COM_COMPONENTBUILDER_EDIT') . ' ' . $view, 'W');
		$href = self::buildEditHref($url, $headsup);

		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($checkedOut > 0)
		{
			if ($checkedOut === (int) $user->id)
			{
				return ' <a ' . $href . ' class="btn btn-sm btn-outline-secondary" title="' . $title . '">'
					. '<span class="icon-lock" aria-hidden="true"></span>'
					. '<span class="visually-hidden">' . $title . '</span>'
					. '</a>';
			}

			$lockedTitle = self::getCheckedOutTitle($checkedOut, $view);

			return ' <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary disabled"'
				. ' aria-disabled="true" tabindex="-1" role="button" title="' . $lockedTitle . '">'
				. '<span class="icon-lock" aria-hidden="true"></span>'
				. '<span class="visually-hidden">' . $lockedTitle . '</span>'
				. '</a>';
		}

		return ' <a ' . $href . ' class="btn btn-sm btn-outline-primary" title="' . $title . '">'
			. '<span class="icon-edit" aria-hidden="true"></span>'
			. '<span class="visually-hidden">' . $title . '</span>'
			. '</a>';
	}

	/**
	 * Get an edit text button.
	 *
	 * @param  string  $text       The button text.
	 * @param  mixed   $item       The item|id to edit.
	 * @param  string  $view       The type of item to edit.
	 * @param  string  $views      The list view controller name.
	 * @param  string  $ref        The return path.
	 * @param  string  $component  The component these views belong to.
	 * @param  bool    $jRoute     The switch to use the Route class.
	 * @param  string  $class      The button class.
	 * @param  string  $headsup    The message to show on click of button.
	 *
	 * @return  string  On success the full HTML link.
	 * @since   3.0.9
	 */
	public static function getEditTextButton(
		string $text,
		&$item,
		string $view,
		string $views,
		string $ref = '',
		string $component = 'com_componentbuilder',
		bool $jRoute = true,
		string $class = 'btn btn-primary',
		string $headsup = 'COM_COMPONENTBUILDER_ALL_UNSAVED_WORK_ON_THIS_PAGE_WILL_BE_LOST_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE'
	): string
	{
		if (!UtilitiesStringHelper::check($text))
		{
			return self::getEditButton($item, $view, $views, $ref, $component, $headsup);
		}

		$url = self::getEditURL($item, $view, $views, $ref, $component, $jRoute);

		if ($url === null)
		{
			return '';
		}

		$checkedOut = self::getCheckedOut($item, $view, $component);
		$title = UtilitiesStringHelper::safe(Text::_('COM_COMPONENTBUILDER_EDIT') . ' ' . $view, 'W');
		$href = self::buildEditHref($url, $headsup);

		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($checkedOut > 0)
		{
			if ($checkedOut === (int) $user->id)
			{
				return ' <a class="' . $class . '" ' . $href . ' title="' . $title . '">' . $text . '</a>';
			}

			$lockedTitle = self::getCheckedOutTitle($checkedOut, $view);

			return ' <a class="' . $class . ' disabled" href="javascript:void(0)"'
				. ' aria-disabled="true" tabindex="-1" role="button" title="' . $lockedTitle . '">'
				. $text
				. '</a>';
		}

		return ' <a class="' . $class . '" ' . $href . ' title="' . $title . '">' . $text . '</a>';
	}

	/**
	 * Get the edit URL.
	 *
	 * @param  mixed   $item       The item to edit.
	 * @param  string  $view       The type of item to edit.
	 * @param  string  $views      The list view controller name.
	 * @param  string  $ref        The return path.
	 * @param  string  $component  The component these views belong to.
	 * @param  bool    $jRoute     The switch to add use JRoute or not.
	 *
	 * @return  string|null  On success the edit URL.
	 * @since   3.0.9
	 */
	public static function getEditURL(
		&$item,
		string $view,
		string $views,
		string $ref = '',
		string $component = 'com_componentbuilder',
		bool $jRoute = true
	): ?string
	{
		$record = new \stdClass();

		if (self::canEditItem($record, $item, $view, $views, $component))
		{
			$link = 'index.php?option=' . $component . '&view=' . $views . '&task=' . $view . '.edit&id=' . $record->id . $ref;

			if ($jRoute)
			{
				return Route::_($link);
			}

			return $link;
		}

		return null;
	}

	/**
	 * Can edit (either any, or own).
	 *
	 * @param  mixed   $item       The item to edit.
	 * @param  string  $view       The type of item to edit.
	 * @param  string  $views      The list view controller name.
	 * @param  string  $component  The component these views belong to.
	 *
	 * @return  bool  If user can edit returns true.
	 * @since   3.0.9
	 */
	public static function allowEdit(&$item, string $view, string $views, string $component = 'com_componentbuilder'): bool
	{
		$record = new \stdClass();

		return self::canEditItem($record, $item, $view, $views, $component);
	}

	/**
	 * Can edit item (either any, or own).
	 *
	 * @param  object  $record     The record object.
	 * @param  mixed   $item       The item to edit.
	 * @param  string  $view       The type of item to edit.
	 * @param  string  $views      The list view controller name.
	 * @param  string  $component  The component these views belong to.
	 *
	 * @return  bool  If user can edit returns true.
	 * @since   3.0.9
	 */
	protected static function canEditItem(&$record, &$item, $view, $views, $component = 'com_componentbuilder')
	{
		if (!Factory::getApplication()->getIdentity()->authorise($view . '.access', $component))
		{
			return false;
		}

		$can_edit = false;

		if (ObjectHelper::check($item) && isset($item->id))
		{
			$record->id = (int) $item->id;

			if (isset($item->created_by) && $item->created_by > 0)
			{
				$record->created_by = (int) $item->created_by;
			}
		}
		elseif (UtilitiesArrayHelper::check($item) && isset($item['id']))
		{
			$record->id = (int) $item['id'];

			if (isset($item['created_by']) && $item['created_by'] > 0)
			{
				$record->created_by = (int) $item['created_by'];
			}
		}
		elseif (is_numeric($item))
		{
			$record->id = (int) $item;
		}

		if (isset($record->id) && $record->id > 0)
		{
			$action = self::getActions($view, $record, $views, 'edit', str_replace('com_', '', $component));

			if (($can_edit = $action->get($view . '.edit', 'none-set')) === 'none-set')
			{
				$can_edit = ($action->get('core.edit', false) || $action->get('core.edit.own', false));
			}
		}

		return (bool) $can_edit;
	}

	/**
	 * Get the checked out user ID for the item.
	 *
	 * @param  mixed   $item       The item or ID.
	 * @param  string  $view       The view name.
	 * @param  string  $component  The component option.
	 *
	 * @return  int  The checked out user ID or zero.
	 * @since   6.1.6
	 */
	protected static function getCheckedOut($item, string $view, string $component): int
	{
		if (ObjectHelper::check($item) && isset($item->id))
		{
			if (isset($item->checked_out))
			{
				return (int) $item->checked_out;
			}

			return (int) GetHelper::var(
				$view,
				$item->id,
				'id',
				'checked_out',
				'=',
				str_replace('com_', '', $component)
			);
		}

		if (UtilitiesArrayHelper::check($item) && isset($item['id']))
		{
			if (isset($item['checked_out']))
			{
				return (int) $item['checked_out'];
			}

			return (int) GetHelper::var(
				$view,
				$item['id'],
				'id',
				'checked_out',
				'=',
				str_replace('com_', '', $component)
			);
		}

		if (is_numeric($item) && $item > 0)
		{
			return (int) GetHelper::var(
				$view,
				$item,
				'id',
				'checked_out',
				'=',
				str_replace('com_', '', $component)
			);
		}

		return 0;
	}

	/**
	 * Build the href attribute set for edit links.
	 *
	 * @param  string  $url      The target URL.
	 * @param  string  $headsup  The confirmation message.
	 *
	 * @return  string  The rendered href/onclick attributes.
	 * @since   6.1.6
	 */
	public static function buildEditHref(string $url, string $headsup): string
	{
		if (!UtilitiesStringHelper::check($headsup))
		{
			return 'href="' . $url . '"';
		}

		$onclick = '(customElements.get(\'joomla-dialog\')).confirm(\'' . Text::_($headsup) . '\')'
			. '.then(function(result){'
			. 'if(result){window.location.href=\'' . $url . '\';}});';

		return 'href="javascript:void(0)" onclick="' . $onclick . '"';
	}

	/**
	 * Get the checked out title.
	 *
	 * @param  int     $checkedOut  The user ID who checked out the item.
	 * @param  string  $view        The view name.
	 *
	 * @return  string  The checked out title text.
	 * @since   6.1.6
	 */
	protected static function getCheckedOutTitle(int $checkedOut, string $view): string
	{
		$user = Factory::getApplication()->getIdentity($checkedOut);

		return Text::sprintf('COM_COMPONENTBUILDER_S_HAS_BEEN_CHECKED_OUT_BY_S',
			UtilitiesStringHelper::safe($view, 'W'),
			$user->name
		);
	}

	/**
	 * Get an edit button with GUID
	 * 
	 * @param  string   $guid       The GUID to edit
	 * @param  string   $field       The item to edit
	 * @param  string   $view       The type of item to edit
	 * @param  string   $views      The list view controller name
	 * @param  string   $ref        The return path
	 * @param  string   $component  The component these views belong to
	 * @param  string   $headsup    The message to show on click of button
	 *
	 * @return  string    On success the full html link
	 * @since  3.0.9
	 */
	public static function getEditButtonGUID(
		string $guid, 
		string $field,
		string $view,
		string $views,
		string $ref = '',
		string $component = 'com_componentbuilder',
		string $headsup = 'COM_COMPONENTBUILDER_ALL_UNSAVED_WORK_ON_THIS_PAGE_WILL_BE_LOST_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE'
	): string
	{
		// check that the validGUID
		if (UtilitiesStringHelper::check($field) && GuidHelper::valid($guid))
		{
			$item = GetHelper::var($view, $guid, $field, 'id', '=', str_replace('com_', '', $component));
			// now return the getEditButton string
			return self::getEditButton($item, $view, $views, $ref, $component, $headsup);
		}

		return false;
	}

	/**
	 * Get an edit text button with GUID
	 * 
	 * @param  string   $text       The button text
	 * @param  string   $guid       The GUID to edit
	 * @param  string   $field      The field name of the guid
	 * @param  string   $view       The type of item to edit
	 * @param  string   $views      The list view controller name
	 * @param  string   $ref        The return path
	 * @param  string   $component  The component these views belong to
	 * @param  bool     $jRoute     The switch to use the Route class
	 * @param  string   $headsup    The message to show on click of button
	 *
	 * @return  string|null   On success the full html link
	 * @since   3.0.9
	 */
	public static function getEditTextButtonGUID(
		string $text,
		string $guid,
		string $field,
		string $view,
		string $views,
		string $ref = '',
		string $component = 'com_componentbuilder',
		bool $jRoute = true, 
		string $class = 'btn btn-primary',
		string $headsup = 'COM_COMPONENTBUILDER_ALL_UNSAVED_WORK_ON_THIS_PAGE_WILL_BE_LOST_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE'
	): ?string
	{
		// check that the validGUID
		if (UtilitiesStringHelper::check($field) && GuidHelper::valid($guid))
		{
			$item = GetHelper::var($view, $guid, $field, 'id', '=', str_replace('com_', '', $component));
			// now return the getEditButton string
			return self::getEditTextButton($text, $item, $view, $views, $ref, $component, $jRoute, $class, $headsup);
		}

		return null;
	}

	/**
	 * Get the edit URL with GUID
	 * 
	 * @param  string   $guid        The GUID to edit
	 * @param  string   $field        The item to edit
	 * @param  string   $view        The type of item to edit
	 * @param  string   $views       The list view controller name
	 * @param  string   $ref           The return path
	 * @param  string   $component   The component these views belong to
	 * @param  bool     $jRoute      The switch to add use JRoute or not
	 *
	 * @return  string|null    On success the edit url
	 * @since   3.0.9
	 */
	public static function getEditURLGUID($guid, $field, $view, $views, $ref = '', $component = 'com_componentbuilder', $jRoute = true): ?string
	{
		// check that the validGUID
		if (UtilitiesStringHelper::check($field) && GuidHelper::valid($guid))
		{
			$item = GetHelper::var($view, $guid, $field, 'id', '=', str_replace('com_', '', $component));
			// now return the getEditButton string
			return self::getEditURL($item, $view, $views, $ref, $component, $jRoute);
		}
		return false;
	}

	/**
	 * Set subform type table.
	 *
	 * @param  array   $head    The header names.
	 * @param  array   $rows    The row values as prebuilt <td>...</td> strings.
	 * @param  string  $idName  The prefix to the table id.
	 *
	 * @return string
	 * @since  6.1.6
	 */
	public static function setSubformTable(array $head, array $rows, string $idName): string
	{
		$table = [];

		$table[] = "<div id=\"vdm_table_display_" . $idName . "\">";
		$table[] = "\t<div class=\"subform-repeatable-wrapper subform-table-layout subform-table-sublayout-section-byfieldsets\">";
		$table[] = "\t\t<div class=\"subform-repeatable\">";
		$table[] = "\t\t\t<div class=\"table-responsive\">";
		$table[] = "\t\t\t\t<table class=\"adminlist table table-striped table-bordered table-hover table-sm align-middle\">";
		$table[] = "\t\t\t\t\t<thead>";
		$table[] = "\t\t\t\t\t\t<tr>";
		$table[] = "\t\t\t\t\t\t\t<th>" . implode("</th><th>", $head) . "</th>";
		$table[] = "\t\t\t\t\t\t</tr>";
		$table[] = "\t\t\t\t\t</thead>";
		$table[] = "\t\t\t\t\t<tbody>";

		foreach ($rows as $row)
		{
			$table[] = "\t\t\t\t\t\t<tr class=\"subform-repeatable-group\">";
			$table[] = "\t\t\t\t\t\t\t" . $row;
			$table[] = "\t\t\t\t\t\t</tr>";
		}

		$table[] = "\t\t\t\t\t</tbody>";
		$table[] = "\t\t\t\t</table>";
		$table[] = "\t\t\t</div>";
		$table[] = "\t\t</div>";
		$table[] = "\t</div>";
		$table[] = "</div>";

		return implode("\n", $table);
	}

	/**
	 * Convert a date to a human-readable fancy format (e.g., "1st of January 2024").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Formatted date.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::fancyDate($date, $checkStamp);
	 */
	public static function fancyDate($date, bool $checkStamp = true): string
	{
		return DateHelper::fancyDate($date, $checkStamp);
	}

	/**
	 * Get a formatted date based on the time period (dynamic format based on age of the date).
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Formatted date.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::fancyDynamicDate($date, $checkStamp);
	 */
	public static function fancyDynamicDate($date, bool $checkStamp = true): string
	{
		return DateHelper::fancyDynamicDate($date, $checkStamp);
	}

	/**
	 * Convert a date to a human-readable day, time, and date format (e.g., "Mon 12am 1st of January 2024").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Formatted day, time, and date.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::fancyDayTimeDate($date, $checkStamp);
	 */
	public static function fancyDayTimeDate($date, bool $checkStamp = true): string
	{
		return DateHelper::fancyDayTimeDate($date, $checkStamp);
	}

	/**
	 * Convert a date to a human-readable time and date format (e.g., "(12:00) 1st of January 2024").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Formatted time and date.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::fancyDateTime($date, $checkStamp);
	 */
	public static function fancyDateTime($date, bool $checkStamp = true): string
	{
		return DateHelper::fancyDateTime($date, $checkStamp);
	}

	/**
	 * Convert a time to a human-readable format (e.g., "12:00").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Formatted time.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::fancyTime($date, $checkStamp);
	 */
	public static function fancyTime($date, bool $checkStamp = true): string
	{
		return DateHelper::fancyTime($date, $checkStamp);
	}

	/**
	 * Convert a date to the day name (e.g., "Sunday").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Day name.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::setDayName($date, $checkStamp);
	 */
	public static function setDayName($date, bool $checkStamp = true): string
	{
		return DateHelper::setDayName($date, $checkStamp);
	}

	/**
	 * Convert a date to the month name (e.g., "January").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Month name.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::setMonthName($date, $checkStamp);
	 */
	public static function setMonthName($date, bool $checkStamp = true): string
	{
		return DateHelper::setMonthName($date, $checkStamp);
	}

	/**
	 * Convert a date to the day with suffix (e.g., "1st").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Day with suffix.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::setDay($date, $checkStamp);
	 */
	public static function setDay($date, bool $checkStamp = true): string
	{
		return DateHelper::setDay($date, $checkStamp);
	}

	/**
	 * Convert a date to the numeric month (e.g., "5").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Numeric month.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::setMonth($date, $checkStamp);
	 */
	public static function setMonth($date, bool $checkStamp = true): string
	{
		return DateHelper::setMonth($date, $checkStamp);
	}

	/**
	 * Convert a date to the full year (e.g., "2024").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Full year.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::setYear($date, $checkStamp);
	 */
	public static function setYear($date, bool $checkStamp = true): string
	{
		return DateHelper::setYear($date, $checkStamp);
	}

	/**
	 * Convert a date to a year/month format (e.g., "2024/05").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param string      $spacer       The spacer between year and month.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Year/Month format.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::setYearMonth($date, $spacer, $checkStamp);
	 */
	public static function setYearMonth($date, string $spacer = '/', bool $checkStamp = true): string
	{
		return DateHelper::setYearMonth($date, $spacer, $checkStamp);
	}

	/**
	 * Convert a date to a year/month/day format (e.g., "2024/05/03").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param string      $spacer       The spacer between year and month.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Year/Month/Day format.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::setYearMonthDay($date, $spacer, $checkStamp);
	 */
	public static function setYearMonthDay($date, string $spacer = '/', bool $checkStamp = true): string
	{
		return DateHelper::setYearMonthDay($date, $spacer, $checkStamp);
	}

	/**
	 * Convert a date to a day/month/year format (e.g., "03/05/2024").
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param string      $spacer       The spacer between year and month.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return string Day/Month/Year format.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::setDayMonthYear($date, $spacer, $checkStamp);
	 */
	public static function setDayMonthYear($date, string $spacer = '/', bool $checkStamp = true): string
	{
		return DateHelper::setDayMonthYear($date, $spacer, $checkStamp);
	}

	/**
	 * Convert a date string to a valid timestamp.
	 *
	 * @param string|int  $date         The date as a string or timestamp.
	 * @param bool        $checkStamp   Whether to check if the input is a timestamp.
	 *
	 * @return int The valid timestamp.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::getValidTimestamp($date, $checkStamp);
	 */
	public static function getValidTimestamp($date, bool $checkStamp): int
	{
		return DateHelper::getValidTimestamp($date, $checkStamp);
	}

	/**
	 * Check if the input is a valid Unix timestamp.
	 *
	 * @param mixed $timestamp The timestamp to validate.
	 *
	 * @return bool True if valid timestamp, false otherwise.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::isValidTimeStamp($timestamp);
	 */
	public static function isValidTimeStamp($timestamp): bool
	{
		return DateHelper::isValidTimeStamp($timestamp);
	}

	/**
	 * Check if a string is a valid date according to the specified format.
	 *
	 * @param string $date The date string to validate.
	 * @param string $format The format to check against (default is 'Y-m-d H:i:s').
	 *
	 * @return bool True if valid date, false otherwise.
	 * @since 3.0.0
	 * @deprecated 4.0.0 Use DateHelper::isValidateDate($date, $format);
	 */
	public static function isValidateDate($date, string $format = 'Y-m-d H:i:s'): bool
	{
		return DateHelper::isValidateDate($date, $format);
	}

	/**
	* The subform layouts
	**/
	protected static $subformLayouts = false;

	/**
	* get the subform layout
	*
	* @input	string      The view name
	* @input	string      The string name
	*
	* @returns string on success
	**/
	public static function getSubformLayout($view, $field, $default = 'joomla.form.field.subform.repeatable-table')
	{
		// get global values
		if (self::$subformLayouts === false)
		{
			self::$subformLayouts = ComponentHelper::getParams('com_componentbuilder')->get('subform_layouts', false);
		}
		// check what we found (else) return default
		if (ObjectHelper::check(self::$subformLayouts))
		{
			// looking for
			$target = $view . '.' . $field;
			foreach (self::$subformLayouts as $subform)
			{
				if ($target === $subform->view_field)
				{
					return $subform->layout;
				}
				elseif ('default' === $subform->view_field)
				{
					$default = $subform->layout;
				}
			}
		}
		return $default;
	}


	/**
	 * Check if a row already exist
	 *
	 * @param   string   $table        The table from which to get the variable
	 * @param   array   $where        The value where
	 * @param   string   $main         The component in which the table is found
	 *
	 * @return  int   the id, or false
	 *
	 */
	public static function checkExist($table, $where, $what = 'id', $operator = '=', $main = 'componentbuilder')
	{
		// Get a db connection.
		$db = Factory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array($what)));
		if (empty($table))
		{
			$query->from($db->quoteName('#__'.$main));
		}
		else
		{
			$query->from($db->quoteName('#__'.$main.'_'.$table));
		}
		if (UtilitiesArrayHelper::check($where))
		{
			foreach ($where as $key => $value)
			{
				if (is_numeric($value))
				{
					if (is_float($value + 0))
					{
						$query->where($db->quoteName($key) . ' ' . $operator . ' ' . (float) $value);
					}
					else
					{
						$query->where($db->quoteName($key) . ' ' . $operator . ' ' . (int) $value);
					}
				}
				elseif (is_bool($value))
				{
					$query->where($db->quoteName($key) . ' ' . $operator . ' ' . (bool) $value);
				}
				// we do not allow arrays at this point
				elseif (!UtilitiesArrayHelper::check($value))
				{
					$query->where($db->quoteName($key) . ' ' . $operator . ' ' . $db->quote( (string) $value));
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			return false;
		}
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			return $db->loadResult();
		}
		return false;
	}


	/**
	 * Making class or function name safe
	 *
	 * @input	string       The name you would like to make safe
	 *
	 * @returns string on success
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use ClassfunctionHelper::safe($name);
	 */
	public static function safeClassFunctionName($name)
	{
		return ClassfunctionHelper::safe($name);
	}

	/**
	 * Making field names safe
	 *
	 * @input	string       The you would like to make safe
	 * @input	boolean      The switch to return an ALL UPPER CASE string
	 * @input	string       The string to use in white space
	 *
	 * @returns string on success
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use StringFieldHelper::safe($string, $allcap, $spacer);
	 */
	public static function safeFieldName($string, $allcap = false, $spacer = '_')
	{
		// set the local component option
		self::setComponentOption();

		return StringFieldHelper::safe($string, $allcap, $spacer);
	}

	/**
	 * Making field type name safe
	 *
	 * @input	string       The you would like to make safe
	 *
	 * @returns string on success
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use TypeHelper::safe($string);
	 */
	public static function safeTypeName($string)
	{
		// set the local component option
		self::setComponentOption();

		return TypeHelper::safe($string);
	}

	/**
	 * Making namespace safe
	 *
	 * @input	string       The you would like to make safe
	 *
	 * @returns string on success
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use NamespaceHelper::safe($string);
	 */
	public static function safeNamespace($string)
	{
		return NamespaceHelper::safe($string);
	}

	/**
	 * get all strings between two other strings
	 *
	 * @param  string          $content    The content to search
	 * @param  string          $start        The starting value
	 * @param  string          $end         The ending value
	 *
	 * @return  array          On success
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use GetHelper::allBetween($content, $start, $end);
	 */
	public static function getAllBetween($content, $start, $end)
	{
		return GetHelper::allBetween($content, $start, $end);
	}

	/**
	 * get a string between two other strings
	 * 
	 * @param  string          $content    The content to search
	 * @param  string          $start        The starting value
	 * @param  string          $end         The ending value
	 * @param  string          $default     The default value if none found
	 *
	 * @return  string          On success / empty string on failure
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use GetHelper::between($content, $start, $end, $default);
	 */
	public static function getBetween($content, $start, $end, $default = '')
	{
		return GetHelper::between($content, $start, $end, $default);
	}

	/**
	 * bc math wrapper (very basic not for accounting)
	 *
	 * @param   string   $type    The type bc math
	 * @param   int      $val1    The first value
	 * @param   int      $val2    The second value
	 * @param   int      $scale   The scale value
	 *
	 * @return float|int
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use MathHelper::bc($type, $val1, $val2, $scale);
	 */
	public static function bcmath($type, $val1, $val2, $scale = 0)
	{
		return MathHelper::bc($type, $val1, $val2, $scale);
	}

	/**
	 * Basic sum of an array with more precision
	 *
	 * @param   array   $array    The values to sum
	 * @param   int      $scale   The scale value
	 *
	 * @return float|int
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use MathHelper::sum($array, $scale);
	 */
	public static function bcsum($array, $scale = 4)
	{
		return MathHelper::sum($array, $scale);
	}

	/**
	 * create plugin class name
	 *
	 * @input	string       The group name
	 * @input	string       The name
	 *
	 * @return string
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use PluginHelper::safeClassName($name, $group);
         */
        public static function createPluginClassName($group, $name)
	{
		return PluginHelper::safeClassName($name, $group);
	}

	/**
	 * Returns a GUIDv4 string
	 * 
	 * Thanks to Dave Pearson (and other)
	 * https://www.php.net/manual/en/function.com-create-guid.php#119168 
	 *
	 * Uses the best cryptographically secure method
	 * for all supported platforms with fallback to an older,
	 * less secure version.
	 *
	 * @param bool $trim
	 *
	 * @return string
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use GuidHelper::get($trim);
	 */
	public static function GUID($trim = true)
	{
		return GuidHelper::get($trim);
	}

	/**
	 * Validate the Globally Unique Identifier ( and check if table already has this identifier)
	 *
	 * @param string       $guid
	 * @param string       $table
	 * @param int            $id
	 * @param string|null $component
	 *
	 * @return bool
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use GuidHelper::valid($guid, $table, $id, $component);
	 */
	public static function validGUID($guid, $table = null, $id = 0, $component = null)
	{
		// set the local component option
		self::setComponentOption();

		return GuidHelper::valid($guid, $table, $id, $component);
	}

	/**
	 * get the ITEM of a GUID by table
	 *
	 * @param string           $guid
	 * @param string           $table
	 * @param string/array  $what
	 * @param string|null    $component
	 *
	 * @return mixed
	 * 
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use GuidHelper::valid($guid, $table, $id, $component);
	 */
	public static function getGUID($guid, $table, $what = 'a.id', $component = null)
	{
		// set the local component option
		self::setComponentOption();

		return GuidHelper::item($guid, $table, $what, $component);
	}

	/**
	 * Validate the Globally Unique Identifier
	 *
	 * Thanks to Lewie
	 * https://stackoverflow.com/a/1515456/1429677
	 *
	 * @param string $guid
	 *
	 * @return bool
	 *
	 * @deprecated  4.0 - Use GuidHelper::validate($guid);
	 */
	protected static function validateGUID($guid)
	{
		return GuidHelper::valid($guid);
	}

	/**
	 * The zipper method
	 * 
	 * @param  string   $workingDIR    The directory where the items must be zipped
	 * @param  string   $filepath          The path to where the zip file must be placed
	 *
	 * @return  bool true   On success
	 *
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use FileHelper::zip($workingDIR, $filepath);
	 */
	public static function zip($workingDIR, &$filepath)
	{
		return FileHelper::zip($workingDIR, $filepath);
	}

	/**
	 * get the content of a file
	 *
	 * @param  string    $path   The path to the file
	 * @param  mixed     $none   The return value if no content was found
	 *
	 * @return  string   On success
	 *
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use FileHelper::getContent($path, $none);
	 */
	public static function getFileContents($path, $none = '')
	{
		return FileHelper::getContent($path, $none);
	}

	/**
	 * Write a file to the server
	 *
	 * @param  string   $path    The path and file name where to safe the data
	 * @param  string   $data    The data to safe
	 *
	 * @return  bool true   On success
	 *
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use FileHelper::write($path, $data);
	 */
	public static function writeFile($path, $data)
	{
		return FileHelper::write($path, $data);
	}

	/**
	 * get all the file paths in folder and sub folders
	 * 
	 * @param   string  $folder     The local path to parse
	 * @param   array   $fileTypes  The type of files to get
	 *
	 * @return  array|null
	 *
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use FileHelper::getPaths($folder, $fileTypes , $recurse, $full);
	 */
	public static function getAllFilePaths($folder, $fileTypes = array('\.php', '\.js', '\.css', '\.less'), $recurse = true, $full = true)
	{
		return FileHelper::getPaths($folder, $fileTypes , $recurse, $full);
	}

	/**
	 * Get the file path or url
	 *
	 * @param  string   $type              The (url/path) type to return
	 * @param  string   $target            The Params Target name (if set)
	 * @param  string   $fileType          The kind of filename to generate (if not set no file name is generated)
	 * @param  string   $key               The key to adjust the filename (if not set ignored)
	 * @param  string   $default           The default path if not set in Params (fallback path)
	 * @param  bool     $createIfNotSet    The switch to create the folder if not found
	 *
	 * @return  string    On success the path or url is returned based on the type requested
	 *
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use FileHelper::getPath($type, $target, $fileType, $key, $default, $createIfNotSet);
	 */
	public static function getFilePath($type = 'path', $target = 'filepath', $fileType = null, $key = '', $default = '', $createIfNotSet = true)
	{
		// set the local component option
		self::setComponentOption();

		return FileHelper::getPath($type, $target, $fileType, $key, $default, $createIfNotSet);
	}

	/**
	 * Check if file exist
	 *
	 * @param  string   $path   The url/path to check
	 *
	 * @return  bool      If exist true
	 *
	 * @since  3.0.9
	 *
	 * @deprecated  4.0 - Use FileHelper::exists($path);
	 */
	public static function urlExists($path)
	{
		return FileHelper::exists($path);
	}

	/**
	 * Set the component option
	 *
	 * @param   String|null       $option    The option for the component.
	 *
	 * @since  3.0.11
	 */
	public static function setComponentOption($option = null)
	{
		// set the local component option
		if (empty($option))
		{
			if (empty(Helper::$option) && property_exists(__CLASS__, 'ComponentCodeName'))
			{
				Helper::$option = 'com_' . self::$ComponentCodeName;
			}
		}
		else
		{
			Helper::$option = $option;
		}
	}


	/**
	 * The Spreadsheet Headers
	 *
	 * @var   array
	 * @since 5.0.2
	 */
	protected static array $SpreadsheetHeaders = [];

	/**
	 * Get the list of headers to show in column field
	 *
	 * @return  array  The list of headers
	 * @since   5.0.2
	 */
	public static function getSpreadsheetHeaders(): array
	{
		$endColumn = 'DA';
		$column = 'A';
		$options = [];
		while ($column !== $endColumn)
		{
			$options[$column] = self::getSpreadsheetHeader($column);
			$column = ++$column;
		}
		return $options;
	}

	/**
	 * Get the header column value (name)
	 *
	 * @return  string  The header value
	 * @since   5.0.2
	 */
	protected static function getSpreadsheetHeader(string $column): string
	{
		return self::$SpreadsheetHeaders[$column] ?? $column;
	}

	/**
	 * Set the header column values
	 *
	 * @return  void
	 * @since   5.0.2
	 */
	public static function setSpreadsheetHeaders(array $headers): void
	{
		self::$SpreadsheetHeaders = $headers;
	}

	/**
	 * Retrieves an associative array of published language tags as headers.
	 *
	 * The returned array is used to build header labels for language translations.
	 * Includes a default 'source' => 'source' entry.
	 *
	 * @return array<string, string>  Associative array of language headers
	 * @since  5.1.1
	 */
	public static function getLanguageTranslationsHeaders(bool $db = false): array
	{
		$values = DataFactory::_('Load')->values(
			['langtag'], ['language'], ['published' => 1], ['name' => 'ASC']
		);

		$prefix = '';
		$prefix_subform = '';
		if ($db)
		{
			$prefix = 'language_translation.';
			$prefix_subform = 'language_translation.translation|translation|language|'; // two value sub-from mapping [table.field|value|column|column_value]
		}

		if (empty($values))
		{
			// return default array
			return [
				"{$prefix}source" => 'Source',
				"{$prefix_subform}af-ZA" => 'af-ZA',
				"{$prefix_subform}nl-NL" => 'nl-NL',
				"{$prefix_subform}fr-FR" => 'fr-FR',
				"{$prefix_subform}de-DE" => 'de-DE',
				"{$prefix_subform}pt-PT" => 'pt-PT',
				"{$prefix_subform}ru-RU" => 'ru-RU'
			];
		}

		$headers = ["{$prefix}source" => 'Source'];

		foreach ($values as $value)
		{
			if (is_string($value) && trim($value) !== '')
			{
				$headers["{$prefix_subform}{$value}"] = $value;
			}
		}

		return $headers;
	}

	/**
	 * Retrieves a dynamic values of the import subform for item mapping
	 *
	 * @return array|null the values
	 * @since  5.0.2
	 */
	public static function getItemImportSubformValues(): ?array
	{
		$subform_name = 'maps';
		$values = [];
		$headers = self::getSpreadsheetHeaders();
		$target_fields = self::getLanguageTranslationsHeaders(true);
		$number = 0;
		foreach ($headers as $header => $text)
		{
			$values["{$subform_name}{$number}"] = ['column' => $header, 'target' => self::getImportFieldMatch($text, $target_fields)];
			$number++;
		}
		return $values;
	}

	/**
	 * Matches a name value against the keys and values of a given array.
	 * The key must match strictly (===) and the value must start with the name (case-insensitive).
	 *
	 * @param string $name   The name to search for.
	 * @param array  $fields The array of fields to match against. The array should have keys and values.
	 *
	 * @return string|null Returns the key if a match is found, or null if no match is found.
	 * @since  5.0.2
	 */
	protected static function getImportFieldMatch(string $name, array $fields): string
	{
		foreach ($fields as $key => $value)
		{
			$field = self::getImportFieldMatchKey($key);

			// Check if the name strictly matches the key
			if ($key === $name || $field === $name)
			{
				return $key;
			}

			// Check if the name starts with the value (case-insensitive)
			if (self::startsWithIgnoreCase($value, $name . ' '))
			{
				return $key;
			}

			// Check if the name starts with the value (case-insensitive)
			if (self::startsWithIgnoreCase($field, $name . ' '))
			{
				return $key;
			}
		}

		// Return null if no match is found
		return '';
	}

	/**
	 * Helper function to check if a string starts with another string (case-insensitive).
	 *
	 * @param string $haystack  The string to check.
	 * @param string $needle    The string to match the beginning.
	 *
	 * @return bool Returns true if $needle matches the start of $haystack, false otherwise.
	 * @since  5.0.2
	 */
	protected static function startsWithIgnoreCase(string $haystack, string $needle): bool
	{
		return stripos($haystack, $needle) === 0;
	}

	/**
	 * Get the table and field name from an import key.
	 *
	 * Expected format:
	 *   table.field
	 *   table.field.subfield.another
	 *
	 * Only the first dot separates table and field.
	 *
	 * @param   string  $key  The import file key.
	 *
	 * @return  string|null  Object with table and field properties, or null if invalid.
	 * @since   4.0.3
	 */
	protected static function getImportFieldMatchKey(string $key): ?string
	{
		// Split only on the first dot
		$parts = explode('.', $key, 2);

		// Must contain a table and a field
		if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '')
		{
			return $key;
		}

		[$dump, $field] = $parts;

		// Sub-from support (ONLY two values in subform)
		if (strpos($field, '|') !== false)
		{
			$parts_ = array_map('trim', explode('|', $field));

			if (count($parts_) === 4)
			{
				[$dump, $me, $to, $field] = $parts_;
			}
		}

		return $field ?? null;
	}

	/**
	 *  UIKIT Component Classes
	 **/
	public static $uk_components = array(
			'data-uk-grid' => array(
				'grid' ),
			'uk-accordion' => array(
				'accordion' ),
			'uk-autocomplete' => array(
				'autocomplete' ),
			'data-uk-datepicker' => array(
				'datepicker' ),
			'uk-form-password' => array(
				'form-password' ),
			'uk-form-select' => array(
				'form-select' ),
			'data-uk-htmleditor' => array(
				'htmleditor' ),
			'data-uk-lightbox' => array(
				'lightbox' ),
			'uk-nestable' => array(
				'nestable' ),
			'UIkit.notify' => array(
				'notify' ),
			'data-uk-parallax' => array(
				'parallax' ),
			'uk-search' => array(
				'search' ),
			'uk-slider' => array(
				'slider' ),
			'uk-slideset' => array(
				'slideset' ),
			'uk-slideshow' => array(
				'slideshow',
				'slideshow-fx' ),
			'uk-sortable' => array(
				'sortable' ),
			'data-uk-sticky' => array(
				'sticky' ),
			'data-uk-timepicker' => array(
				'timepicker' ),
			'data-uk-tooltip' => array(
				'tooltip' ),
			'uk-placeholder' => array(
				'placeholder' ),
			'uk-dotnav' => array(
				'dotnav' ),
			'uk-slidenav' => array(
				'slidenav' ),
			'uk-form' => array(
				'form-advanced' ),
			'uk-progress' => array(
				'progress' ),
			'upload-drop' => array(
				'upload', 'form-file' )
			);

	/**
	 *  Add UIKIT Components
	 **/
	public static $uikit = false;

	/**
	 *  Get UIKIT Components
	 **/
	public static function getUikitComp($content,$classes = array())
	{
		if (strpos($content ?? '','class="uk-') !== false)
		{
			// reset
			$temp = [];
			foreach (self::$uk_components as $looking => $add)
			{
				if (strpos($content,$looking) !== false)
				{
					$temp[] = $looking;
				}
			}
			// make sure uikit is loaded to config
			if (strpos($content,'class="uk-') !== false)
			{
				self::$uikit = true;
			}
			// sorter
			if (UtilitiesArrayHelper::check($temp))
			{
				// merger
				if (UtilitiesArrayHelper::check($classes))
				{
					$newTemp = array_merge($temp,$classes);
					$temp = array_unique($newTemp);
				}
				return $temp;
			}
		}
		if (UtilitiesArrayHelper::check($classes))
		{
			return $classes;
		}
		return false;
	}

	/**
	 * Load the Composer Vendors
	 */
	public static function composerAutoload($target)
	{
		// insure we load the composer vendor only once
		if (!isset(self::$composer[$target]))
		{
			// get the function name
			$functionName = UtilitiesStringHelper::safe('compose' . $target);
			// check if method exist
			if (method_exists(__CLASS__, $functionName))
			{
				return self::{$functionName}();
			}
			return false;
		}
		return self::$composer[$target];
	}

	/**
	 * Load the Component xml manifest.
	 */
	public static function manifest()
	{
		$manifestUrl = JPATH_ADMINISTRATOR."/components/com_componentbuilder/componentbuilder.xml";
		return simplexml_load_file($manifestUrl);
	}

	/**
	 * Joomla version object
	 */
	protected static $JVersion;

	/**
	 * set/get Joomla version
	 */
	public static function jVersion()
	{
		// check if set
		if (!ObjectHelper::check(self::$JVersion))
		{
			self::$JVersion = new Version();
		}
		return self::$JVersion;
	}

	/**
	 * Load the Contributors details.
	 */
	public static function getContributors()
	{
		// get params
		$params    = ComponentHelper::getParams('com_componentbuilder');
		// start contributors array
		$contributors = [];
		// get all Contributors (max 20)
		$searchArray = range('0','20');
		foreach($searchArray as $nr)
		{
			if ((NULL !== $params->get("showContributor".$nr)) && ($params->get("showContributor".$nr) == 1 || $params->get("showContributor".$nr) == 3))
			{
				// set link based of selected option
				if($params->get("useContributor".$nr) == 1)
				{
					$link_front = '<a href="mailto:'.$params->get("emailContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
				elseif($params->get("useContributor".$nr) == 2)
				{
					$link_front = '<a href="'.$params->get("linkContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
				else
				{
					$link_front = '';
					$link_back = '';
				}
				$contributors[$nr]['title']   = UtilitiesStringHelper::html($params->get("titleContributor".$nr));
				$contributors[$nr]['name']    = $link_front.UtilitiesStringHelper::html($params->get("nameContributor".$nr)).$link_back;
			}
		}
		return $contributors;
	}

	/**
	 *	Load the Component Help URLs.
	 **/
	public static function getHelpUrl(string $view)
	{
		$user	= Factory::getApplication()->getIdentity();
		$groups = $user->get('groups');
		$db	= Factory::getContainer()->get(DatabaseInterface::class);
		$query	= $db->getQuery(true);
		$query->select(array('a.id','a.groups','a.target','a.type','a.article','a.url'));
		$query->from('#__componentbuilder_help_document AS a');
		$query->where('a.admin_view = '.$db->quote($view));
		$query->where('a.location = 1');
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		if($db->getNumRows())
		{
			$helps = $db->loadObjectList();
			if (UtilitiesArrayHelper::check($helps))
			{
				foreach ($helps as $nr => $help)
				{
					if ($help->target == 1)
					{
						$targetgroups = json_decode($help->groups, true);
						if (!array_intersect($targetgroups, $groups))
						{
							unset($helps[$nr]);
							continue;
						}
					}
					switch ($help->type)
					{
						case 1:
							return self::loadArticleLink($help->article);
						case 2:
							return self::loadHelpTextLink($help->id);
						case 3:
							return $help->url;
					}
				}
			}
		}
		return false;
	}

	/**
	 *	Get the Article Link.
	 **/
	protected static function loadArticleLink($id)
	{
		return Uri::root() . 'index.php?option=com_content&view=article&id='.$id.'&tmpl=component&layout=modal';
	}

	/**
	 *	Get the Help Text Link.
	 **/
	protected static function loadHelpTextLink($id)
	{
		$token = Session::getFormToken();
		return 'index.php?option=com_componentbuilder&task=help.getText&id=' . (int) $id . '&' . $token . '=1';
	}

	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu)
	{
		// load user for access menus
		$user = Factory::getApplication()->getIdentity();
		// load the submenus to sidebar
		Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_DASHBOARD'), 'index.php?option=com_componentbuilder&view=componentbuilder', $submenu === 'componentbuilder');
		// Access control (compiler.access && compiler.submenu).
		if ($user->authorise('compiler.access', 'com_componentbuilder') && $user->authorise('compiler.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_COMPILER'), 'index.php?option=com_componentbuilder&view=compiler', $submenu === 'compiler');
		}
		// Access control (extrusion.access && extrusion.submenu).
		if ($user->authorise('extrusion.access', 'com_componentbuilder') && $user->authorise('extrusion.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_EXTRUSION'), 'index.php?option=com_componentbuilder&view=extrusion', $submenu === 'extrusion');
		}
		if ($user->authorise('joomla_component.access', 'com_componentbuilder') && $user->authorise('joomla_component.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_JOOMLA_COMPONENTS'), 'index.php?option=com_componentbuilder&view=joomla_components', $submenu === 'joomla_components');
		}
		if ($user->authorise('joomla_module.access', 'com_componentbuilder') && $user->authorise('joomla_module.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_JOOMLA_MODULES'), 'index.php?option=com_componentbuilder&view=joomla_modules', $submenu === 'joomla_modules');
		}
		if ($user->authorise('joomla_plugin.access', 'com_componentbuilder') && $user->authorise('joomla_plugin.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_JOOMLA_PLUGINS'), 'index.php?option=com_componentbuilder&view=joomla_plugins', $submenu === 'joomla_plugins');
		}
		if ($user->authorise('joomla_power.access', 'com_componentbuilder') && $user->authorise('joomla_power.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_JOOMLA_POWERS'), 'index.php?option=com_componentbuilder&view=joomla_powers', $submenu === 'joomla_powers');
		}
		if ($user->authorise('power.access', 'com_componentbuilder') && $user->authorise('power.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_POWERS'), 'index.php?option=com_componentbuilder&view=powers', $submenu === 'powers');
		}
		// Access control (search.access && search.submenu).
		if ($user->authorise('search.access', 'com_componentbuilder') && $user->authorise('search.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_SEARCH'), 'index.php?option=com_componentbuilder&view=search', $submenu === 'search');
		}
		if ($user->authorise('admin_view.access', 'com_componentbuilder') && $user->authorise('admin_view.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_ADMIN_VIEWS'), 'index.php?option=com_componentbuilder&view=admin_views', $submenu === 'admin_views');
		}
		if ($user->authorise('custom_admin_view.access', 'com_componentbuilder') && $user->authorise('custom_admin_view.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_CUSTOM_ADMIN_VIEWS'), 'index.php?option=com_componentbuilder&view=custom_admin_views', $submenu === 'custom_admin_views');
		}
		if ($user->authorise('site_view.access', 'com_componentbuilder') && $user->authorise('site_view.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_SITE_VIEWS'), 'index.php?option=com_componentbuilder&view=site_views', $submenu === 'site_views');
		}
		if ($user->authorise('template.access', 'com_componentbuilder') && $user->authorise('template.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_TEMPLATES'), 'index.php?option=com_componentbuilder&view=templates', $submenu === 'templates');
		}
		if ($user->authorise('layout.access', 'com_componentbuilder') && $user->authorise('layout.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_LAYOUTS'), 'index.php?option=com_componentbuilder&view=layouts', $submenu === 'layouts');
		}
		if ($user->authorise('dynamic_get.access', 'com_componentbuilder') && $user->authorise('dynamic_get.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_DYNAMIC_GETS'), 'index.php?option=com_componentbuilder&view=dynamic_gets', $submenu === 'dynamic_gets');
		}
		if ($user->authorise('custom_code.access', 'com_componentbuilder') && $user->authorise('custom_code.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_CUSTOM_CODES'), 'index.php?option=com_componentbuilder&view=custom_codes', $submenu === 'custom_codes');
		}
		if ($user->authorise('placeholder.access', 'com_componentbuilder') && $user->authorise('placeholder.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_PLACEHOLDERS'), 'index.php?option=com_componentbuilder&view=placeholders', $submenu === 'placeholders');
		}
		if ($user->authorise('library.access', 'com_componentbuilder') && $user->authorise('library.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_LIBRARIES'), 'index.php?option=com_componentbuilder&view=libraries', $submenu === 'libraries');
		}
		if ($user->authorise('snippet.access', 'com_componentbuilder') && $user->authorise('snippet.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_SNIPPETS'), 'index.php?option=com_componentbuilder&view=snippets', $submenu === 'snippets');
		}
		if ($user->authorise('validation_rule.access', 'com_componentbuilder') && $user->authorise('validation_rule.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_VALIDATION_RULES'), 'index.php?option=com_componentbuilder&view=validation_rules', $submenu === 'validation_rules');
		}
		if ($user->authorise('field.access', 'com_componentbuilder') && $user->authorise('field.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_FIELDS'), 'index.php?option=com_componentbuilder&view=fields', $submenu === 'fields');
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_FIELD_FIELDS_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_componentbuilder.field', $submenu === 'categories.field');
		}
		if ($user->authorise('fieldtype.access', 'com_componentbuilder') && $user->authorise('fieldtype.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_FIELDTYPES'), 'index.php?option=com_componentbuilder&view=fieldtypes', $submenu === 'fieldtypes');
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_FIELDTYPE_FIELDTYPES_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_componentbuilder.fieldtype', $submenu === 'categories.fieldtype');
		}
		if ($user->authorise('language_translation.access', 'com_componentbuilder') && $user->authorise('language_translation.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_LANGUAGE_TRANSLATIONS'), 'index.php?option=com_componentbuilder&view=language_translations', $submenu === 'language_translations');
		}
		if ($user->authorise('language.access', 'com_componentbuilder') && $user->authorise('language.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_LANGUAGES'), 'index.php?option=com_componentbuilder&view=languages', $submenu === 'languages');
		}
		if ($user->authorise('server.access', 'com_componentbuilder') && $user->authorise('server.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_SERVERS'), 'index.php?option=com_componentbuilder&view=servers', $submenu === 'servers');
		}
		if ($user->authorise('repository.access', 'com_componentbuilder') && $user->authorise('repository.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_REPOSITORIES'), 'index.php?option=com_componentbuilder&view=repositories', $submenu === 'repositories');
		}
		if ($user->authorise('help_document.access', 'com_componentbuilder') && $user->authorise('help_document.submenu', 'com_componentbuilder'))
		{
			Sidebar::addEntry(Text::_('COM_COMPONENTBUILDER_SUBMENU_HELP_DOCUMENTS'), 'index.php?option=com_componentbuilder&view=help_documents', $submenu === 'help_documents');
		}
	}

	/**
	 * Get a Variable
	 *
	 * @param   string   $table        The table from which to get the variable
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 *
	 * @return  mix string/int/float
	 * @deprecated 3.3 Use GetHelper::var(...);
	 */
	public static function getVar($table, $where = null, $whereString = 'user', $what = 'id', $operator = '=', $main = 'componentbuilder')
	{
		return GetHelper::var(
			$table,
			$where,
			$whereString,
			$what,
			$operator,
			$main
		);
	}

	/**
	 * Get array of variables
	 *
	 * @param   string   $table        The table from which to get the variables
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 * @param   bool     $unique       The switch to return a unique array
	 *
	 * @return  array
	 * @deprecated 3.3 Use GetHelper::vars(...);
	 */
	public static function getVars($table, $where = null, $whereString = 'user', $what = 'id', $operator = 'IN', $main = 'componentbuilder', $unique = true)
	{
		return GetHelper::vars(
			$table,
			$where,
			$whereString,
			$what,
			$operator,
			$main,
			$unique
		);
	}

	/**
	 * Convert a json object to a string
	 *
	 * @input    string  $value  The json string to convert
	 *
	 * @returns a string
	 * @deprecated 3.3 Use JsonHelper::string(...);
	 */
	public static function jsonToString($value, $sperator = ", ", $table = null, $id = 'id', $name = 'name')
	{
		return JsonHelper::string(
			$value,
			$sperator,
			$table,
			$id,
			$name
		);
	}

	public static function isPublished($id,$type)
	{
		if ($type == 'raw')
		{
			$type = 'item';
		}
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select(array('a.published'));
		$query->from('#__componentbuilder_'.$type.' AS a');
		$query->where('a.id = '. (int) $id);
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		{
			return true;
		}
		return false;
	}

	public static function getGroupName($id)
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select(array('a.title'));
		$query->from('#__usergroups AS a');
		$query->where('a.id = '. (int) $id);
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		  {
			return $db->loadResult();
		}
		return $id;
	}

	/**
	 * Get the permitted actions of a user.
	 *
	 * @param  string   $view        The related view name
	 * @param  ?object  $record      The item to act upon
	 * @param  ?string  $views       The related list view name
	 * @param  mixed    $target      Only get this permission (like edit, create, delete)
	 * @param  string   $component   The target component
	 * @param  object   $user        The user whose permissions we are loading
	 *
	 * @return  object   The Registry of permission/authorised actions
	 * @since   2.5.0
	 *
	 * @deprecated 5.1.4 Use Actions::get(...);
	 */
	public static function getActions($view, &$record = null, $views = null, $target = null, $component = 'componentbuilder', $user = 'null')
	{
		return Actions::get($view, $record, $views, $target, $component, $user);
	}

	/**
	 * Returns any Model object.
	 *
	 * @param   string  $type       The model type to instantiate
	 * @param   string  $prefix     Prefix for the model class name. Optional.
	 * @param   string  $component  Component name the model belongs to. Optional.
	 * @param   array   $config     Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @throws \Exception
	 * @since   4.4
	 */
	public static function getModel(string $type, string $prefix = 'Administrator',
		string $component = 'componentbuilder', array $config = [])
	{
		// make sure the name is correct
		$type = UtilitiesStringHelper::safe($type, 'F');
		$component = strtolower($component);

		if ($prefix !== 'Site' && $prefix !== 'Administrator')
		{
			$prefix = self::getPrefixFromModelPath($prefix);
		}

		// Get the model through the MVCFactory
		return Factory::getApplication()->bootComponent('com_' . $component)->getMVCFactory()->createModel($type, $prefix, $config);
	}

	/**
	 * Get the prefix from the model path
	 *
	 * @param   string  $path    The model path
	 *
	 * @return  string  The prefix value
	 * @since    4.4
	 */
	protected static function getPrefixFromModelPath(string $path): string
	{
		// Check if $path starts with JPATH_ADMINISTRATOR path
		if (str_starts_with($path, JPATH_ADMINISTRATOR . '/components/'))
		{
			return 'Administrator';
		}
		// Check if $path starts with JPATH_SITE path
		elseif (str_starts_with($path, JPATH_SITE . '/components/'))
		{
			return 'Site';
		}

		return 'Administrator';
	}

	/**
	 * Add to asset Table
	 */
	public static function setAsset($id, $table, $inherit = true)
	{
		$parent = Table::getInstance('Asset');
		$parent->loadByName('com_componentbuilder');

		$parentId = $parent->id;
		$name     = 'com_componentbuilder.'.$table.'.'.$id;
		$title    = '';

		$asset = Table::getInstance('Asset');
		$asset->loadByName($name);

		// Check for an error.
		$error = $asset->getError();

		if ($error)
		{
			return false;
		}
		else
		{
			// Specify how a new or moved node asset is inserted into the tree.
			if ($asset->parent_id != $parentId)
			{
				$asset->setLocation($parentId, 'last-child');
			}

			// Prepare the asset to be stored.
			$asset->parent_id = $parentId;
			$asset->name      = $name;
			$asset->title     = $title;
			// get the default asset rules
			$rules = self::getDefaultAssetRules('com_componentbuilder', $table, $inherit);
			if ($rules instanceof AccessRules)
			{
				$asset->rules = (string) $rules;
			}

			if (!$asset->check() || !$asset->store())
			{
				Factory::getApplication()->enqueueMessage($asset->getError(), 'warning');
				return false;
			}
			else
			{
				// Create an asset_id or heal one that is corrupted.
				$object = new \StdClass();

				// Must be a valid primary key value.
				$object->id = $id;
				$object->asset_id = (int) $asset->id;

				// Update their asset_id to link to the asset table.
				return Factory::getDbo()->updateObject('#__componentbuilder_'.$table, $object, 'id');
			}
		}
		return false;
	}

	/**
	 * Gets the default asset Rules for a component/view.
	 */
	protected static function getDefaultAssetRules($component, $view, $inherit = true)
	{
		// if new or inherited
		$assetId = 0;
		// Only get the actual item rules if not inheriting
		if (!$inherit)
		{
			// Need to find the asset id by the name of the component.
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('name') . ' = ' . $db->quote($component));
			$db->setQuery($query);
			$db->execute();
			// check that there is a value
			if ($db->getNumRows())
			{
				// asset already set so use saved rules
				$assetId = (int) $db->loadResult();
			}
		}
		// get asset rules
		$result =  Access::getAssetRules($assetId);
		if ($result instanceof AccessRules)
		{
			$_result = (string) $result;
			$_result = json_decode($_result);
			foreach ($_result as $name => &$rule)
			{
				$v = explode('.', $name);
				if ($view !== $v[0])
				{
					// remove since it is not part of this view
					unset($_result->$name);
				}
				elseif ($inherit)
				{
					// clear the value since we inherit
					$rule = [];
				}
			}
			// check if there are any view values remaining
			if (count((array) $_result))
			{
				$_result = json_encode($_result);
				$_result = array($_result);
				// Instantiate and return the AccessRules object for the asset rules.
				$rules = new AccessRules($_result);
				// return filtered rules
				return $rules;
			}
		}
		return $result;
	}

	/**
	 * xmlAppend
	 *
	 * @param   SimpleXMLElement   $xml      The XML element reference in which to inject a comment
	 * @param   mixed              $node     A SimpleXMLElement node to append to the XML element reference, or a stdClass object containing a comment attribute to be injected before the XML node and a fieldXML attribute containing a SimpleXMLElement
	 *
	 * @return  void
	 * @deprecated 3.3 Use FormHelper::append($xml, $node);
	 */
	public static function xmlAppend(&$xml, $node)
	{
		FormHelper::append($xml, $node);
	}

	/**
	 * xmlComment
	 *
	 * @param   SimpleXMLElement   $xml        The XML element reference in which to inject a comment
	 * @param   string             $comment    The comment to inject
	 *
	 * @return  void
	 * @deprecated 3.3 Use FormHelper::comment($xml, $comment);
	 */
	public static function xmlComment(&$xml, $comment)
	{
		FormHelper::comment($xml, $comment);
	}

	/**
	 * xmlAddAttributes
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $attributes   The attributes to apply to the XML element
	 *
	 * @return  null
	 * @deprecated 3.3 Use FormHelper::attributes($xml, $attributes);
	 */
	public static function xmlAddAttributes(&$xml, $attributes = [])
	{
		FormHelper::attributes($xml, $attributes);
	}

	/**
	 * xmlAddOptions
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $options      The options to apply to the XML element
	 *
	 * @return  void
	 * @deprecated 3.3 Use FormHelper::options($xml, $options);
	 */
	public static function xmlAddOptions(&$xml, $options = [])
	{
		FormHelper::options($xml, $options);
	}

	/**
	 * get the field object
	 *
	 * @param   array      $attributes   The array of attributes
	 * @param   string     $default      The default of the field
	 * @param   array      $options      The options to apply to the XML element
	 *
	 * @return  object
	 * @deprecated 3.3 Use FormHelper::field($attributes, $default, $options);
	 */
	public static function getFieldObject(&$attributes, $default = '', $options = null)
	{
		return FormHelper::field($attributes, $default, $options);
	}

	/**
	 * get the field xml
	 *
	 * @param   array      $attributes   The array of attributes
	 * @param   array      $options      The options to apply to the XML element
	 *
	 * @return  object
	 * @deprecated 3.3 Use FormHelper::xml($attributes, $options);
	 */
	public static function getFieldXML(&$attributes, $options = null)
	{
		return FormHelper::xml($attributes, $options);
	}

	/**
	 * Render Bool Button
	 *
	 * @param   array   $args   All the args for the button
	 *                             0) name
	 *                             1) additional (options class) // not used at this time
	 *                             2) default
	 *                             3) yes (name)
	 *                             4) no (name)
	 *
	 * @return  string    The input html of the button
	 *
	 */
	public static function renderBoolButton()
	{
		$args = func_get_args();
		// check if there is additional button class
		$additional = isset($args[1]) ? (string) $args[1] : ''; // not used at this time
		// button attributes
		$buttonAttributes = array(
			'type' => 'radio',
			'name' => isset($args[0]) ? UtilitiesStringHelper::html($args[0]) : 'bool_button',
			'label' => isset($args[0]) ? UtilitiesStringHelper::safe(UtilitiesStringHelper::html($args[0]), 'Ww') : 'Bool Button', // not seen anyway
			'class' => 'btn-group',
			'filter' => 'INT',
			'default' => isset($args[2]) ? (int) $args[2] : 0);
		// set the button options
		$buttonOptions = array(
			'1' => isset($args[3]) ? UtilitiesStringHelper::html($args[3]) : 'JYES',
			'0' => isset($args[4]) ? UtilitiesStringHelper::html($args[4]) : 'JNO');
		// return the input
		return FormHelper::field($buttonAttributes, $buttonAttributes['default'], $buttonOptions)->input;
	}

	/**
	 * Check if have an json string
	 *
	 * @input    string   The json string to check
	 *
	 * @returns bool true on success
	 * @deprecated 3.3 Use JsonHelper::check($string);
	 */
	public static function checkJson($string)
	{
		return JsonHelper::check($string);
	}

	/**
	 * Check if have an object with a length
	 *
	 * @input    object   The object to check
	 *
	 * @returns bool true on success
	 * @deprecated 3.3 Use ObjectHelper::check($object);
	 */
	public static function checkObject($object)
	{
		return ObjectHelper::check($object);
	}

	/**
	 * Check if have an array with a length
	 *
	 * @input    array   The array to check
	 *
	 * @returns bool/int  number of items in array on success
	 * @deprecated 3.3 Use UtilitiesArrayHelper::check($array, $removeEmptyString);
	 */
	public static function checkArray($array, $removeEmptyString = false)
	{
		return UtilitiesArrayHelper::check($array, $removeEmptyString);
	}

	/**
	 * Check if have a string with a length
	 *
	 * @input    string   The string to check
	 *
	 * @returns bool true on success
	 * @deprecated 3.3 Use UtilitiesStringHelper::check($string);
	 */
	public static function checkString($string)
	{
		return UtilitiesStringHelper::check($string);
	}

	/**
	 * Check if we are connected
	 * Thanks https://stackoverflow.com/a/4860432/1429677
	 *
	 * @returns bool true on success
	 */
	public static function isConnected()
	{
		// If example.com is down, then probably the whole internet is down, since IANA maintains the domain. Right?
		$connected = @fsockopen("www.example.com", 80);
		// website, port  (try 80 or 443)
		if ($connected)
		{
			//action when connected
			$is_conn = true;
			fclose($connected);
		}
		else
		{
			//action in connection failure
			$is_conn = false;
		}
		return $is_conn;
	}

	/**
	 * Merge an array of array's
	 *
	 * @input    array   The arrays you would like to merge
	 *
	 * @returns array on success
	 * @deprecated 3.3 Use UtilitiesArrayHelper::merge($arrays);
	 */
	public static function mergeArrays($arrays)
	{
		return UtilitiesArrayHelper::merge($arrays);
	}

	// typo sorry!
	public static function sorten($string, $length = 40, $addTip = true)
	{
		return self::shorten($string, $length, $addTip);
	}

	/**
	 * Shorten a string
	 *
	 * @input    string   The you would like to shorten
	 *
	 * @returns string on success
	 * @deprecated 3.3 Use UtilitiesStringHelper::shorten(...);
	 */
	public static function shorten($string, $length = 40, $addTip = true)
	{
		return UtilitiesStringHelper::shorten($string, $length, $addTip);
	}

	/**
	 * Making strings safe (various ways)
	 *
	 * @input    string   The you would like to make safe
	 *
	 * @returns string on success
	 * @deprecated 3.3 Use UtilitiesStringHelper::safe(...);
	 */
	public static function safeString($string, $type = 'L', $spacer = '_', $replaceNumbers = true, $keepOnlyCharacters = true)
	{
		return UtilitiesStringHelper::safe(
			$string,
			$type,
			$spacer,
			$replaceNumbers,
			$keepOnlyCharacters
		);
	}

	/**
	 * Convert none English strings to code usable string
	 *
	 * @input    an string
	 *
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::transliterate($string);
	 */
	public static function transliterate($string)
	{
		return UtilitiesStringHelper::transliterate($string);
	}

	/**
	 * make sure a string is HTML save
	 *
	 * @input    an html string
	 *
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::html(...);
	 */
	public static function htmlEscape($var, $charset = 'UTF-8', $shorten = false, $length = 40)
	{
		return UtilitiesStringHelper::html(
			$var,
			$charset,
			$shorten,
			$length
		);
	}

	/**
	 * Convert all int in a string to an English word string
	 *
	 * @input    an string with numbers
	 *
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::numbers($string);
	 */
	public static function replaceNumbers($string)
	{
		return UtilitiesStringHelper::numbers($string);
	}

	/**
	 * Convert an integer into an English word string
	 * Thanks to Tom Nicholson <http://php.net/manual/en/function.strval.php#41988>
	 *
	 * @input    an int
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::number($x);
	 */
	public static function numberToString($x)
	{
		return UtilitiesStringHelper::number($x);
	}

	/**
	 * Random Key
	 *
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::random($size);
	 */
	public static function randomkey($size)
	{
		return UtilitiesStringHelper::random($size);
	}

	/**
	 *	Get The Encryption Keys
	 *
	 *	@param  string        $type     The type of key
	 *	@param  string/bool   $default  The return value if no key was found
	 *
	 *	@return  string   On success
	 *
	 **/
	public static function getCryptKey($type, $default = false)
	{
		// Get the global params
		$params = ComponentHelper::getParams('com_componentbuilder', true);
		// Basic Encryption Type
		if ('basic' === $type)
		{
			$basic_key = $params->get('basic_key', $default);
			if (UtilitiesStringHelper::check($basic_key))
			{
				return $basic_key;
			}
		}

		return $default;
	}
}
