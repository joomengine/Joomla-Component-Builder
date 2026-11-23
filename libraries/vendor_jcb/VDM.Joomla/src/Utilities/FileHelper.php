<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    3rd September, 2020
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Utilities;


use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Archive\Archive;
use VDM\Joomla\Utilities\Component\Helper;


/**
 * File helper
 * 
 * @since  3.0.9
 */
abstract class FileHelper
{
	/**
	 * Trigger error notice only once
	 *
	 * @var    bool
	 *
	 * @since  3.0.9
	 */
	protected static $curlError = false;

	/**
	 * Zips all contents of a directory without including full system paths.
	 *
	 * @param  string  $workingDirectory  The directory where the items must be zipped.
	 * @param  string  $filepath          The path to where the zip file must be placed.
	 *
	 * @return bool  True on success, false on failure.
	 *
	 * @since  3.0.9
	 */
	public static function zip(string $workingDirectory, string &$filepath): bool
	{
		// Normalize paths
		$workingDirectory = Path::clean($workingDirectory);
		$filepath         = Path::clean($filepath);

		// Validate working directory
		if (!is_dir($workingDirectory) || !is_readable($workingDirectory))
		{
			Log::add('ZIP failed: Working directory is invalid or not readable.', Log::ERROR, 'zip');
			return false;
		}

		// Ensure destination directory exists
		$zipDir = dirname($filepath);
		if (!is_dir($zipDir) && !Folder::create($zipDir))
		{
			Log::add('ZIP failed: Unable to create destination directory.', Log::ERROR, 'zip');
			return false;
		}

		// Remove existing archive
		if (is_file($filepath) && !File::delete($filepath))
		{
			Log::add('ZIP failed: Unable to remove existing archive.', Log::ERROR, 'zip');
			return false;
		}

		$originalDir = getcwd();

		try
		{
			if (!@chdir($workingDirectory))
			{
				throw new \RuntimeException('Unable to change to working directory.');
			}

			$files = Folder::files(
				'.',
				'',
				true,
				true,
				['.svn', 'CVS', '.DS_Store', '__MACOSX'],
				['.*~']
			);

			if (empty($files))
			{
				throw new \RuntimeException('No files found to zip.');
			}

			$zipArray = [];

			foreach ($files as $file)
			{
				$zipArray[] = [
					'name' => ltrim(str_replace('./', '', (string) $file), '/'),
					'data' => self::getContent($file),
					'time' => @filemtime($file) ?: time(),
				];
			}

			$zip = (new Archive())->getAdapter('zip');

			if (!$zip->create($filepath, $zipArray))
			{
				throw new \RuntimeException('ZIP adapter failed to create archive.');
			}

			return true;
		}
		catch (\Throwable $e)
		{
			Log::add('ZIP creation failed: ' . $e->getMessage(), Log::ERROR, 'zip');
			return false;
		}
		finally
		{
			@chdir($originalDir);
		}
	}

	/**
	 * Extracts a ZIP archive to a target directory.
	 *
	 * ZIP archives only are supported intentionally.
	 *
	 * @param  string  $archivename  The path to the ZIP archive.
	 * @param  string  $extractdir   Directory to extract into.
	 *
	 * @return bool  True on success, false on failure.
	 *
	 * @since  5.1.4
	 */
	public static function unzip(string $archivename, string $extractdir): bool
	{
		// Normalize paths
		$archivename = Path::clean($archivename);
		$extractdir  = Path::clean($extractdir);

		// Validate archive file
		if (!is_file($archivename) || !is_readable($archivename))
		{
			Log::add('UNZIP failed: Archive does not exist or is not readable.', Log::ERROR, 'zip');
			return false;
		}

		// Enforce ZIP-only extraction
		if (strtolower(pathinfo($archivename, PATHINFO_EXTENSION)) !== 'zip')
		{
			Log::add('UNZIP failed: Only ZIP archives are supported.', Log::ERROR, 'zip');
			return false;
		}

		// Ensure extraction directory exists
		if (!is_dir($extractdir) && !Folder::create($extractdir))
		{
			Log::add('UNZIP failed: Unable to create extraction directory.', Log::ERROR, 'zip');
			return false;
		}

		try
		{
			$zip = (new Archive())->getAdapter('zip');

			if (!$zip->extract($archivename, $extractdir))
			{
				throw new \RuntimeException('ZIP adapter failed to extract archive.');
			}

			return true;
		}
		catch (\Throwable $e)
		{
			Log::add('UNZIP extraction failed: ' . $e->getMessage(), Log::ERROR, 'zip');
			return false;
		}
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
	 */
	public static function getContent($path, $none = '')
	{
		if (StringHelper::check($path))
		{
			// use basic file get content for now
			if (($content = @file_get_contents($path)) !== FALSE)
			{
				return $content;
			}
			// use curl if available
			elseif (function_exists('curl_version'))
			{
				// start curl
				$ch = curl_init();
				// set the options
				$options = [];
				$options[CURLOPT_URL] = $path;
				$options[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12';
				$options[CURLOPT_RETURNTRANSFER] = TRUE;
				$options[CURLOPT_SSL_VERIFYPEER] = FALSE;
				// load the options
				curl_setopt_array($ch, $options);
				// get the content
				$content = curl_exec($ch);
				// close the connection
				curl_close($ch);

				// return if found
				if (StringHelper::check($content))
				{
					return $content;
				}
			}
			elseif (!self::$curlError)
			{
				// set the notice
				Factory::getApplication()->enqueueMessage('<h2>Curl Not Found!</h2><p>Please setup curl on your system, or the <b>Joomla Component</b> will not function correctly!</p>', 'Error');
				// load this notice only once
				self::$curlError = true;
			}
		}
		return $none;
	}

	/**
	 * Write a file to the server safely and efficiently.
	 * This function will always overwrite the existing file with new data.
	 *
	 * @param  string  $path  The full path and file name where to save the data.
	 * @param  string  $data  The data to save.
	 *
	 * @return bool  Returns true on success, false on failure.
	 * @since  3.0.9
	 */
	public static function write($path, $data): bool
	{
		if (!is_string($path) || !is_string($data) || trim($path) === '')
		{
			return false;
		}

		$handle = null;
		$success = false;

		try {
			$handle = fopen($path, "wb"); // Open in write-binary mode to ensure full overwrite
			if (!is_resource($handle))
			{
				return false;
			}

			$success = fwrite($handle, $data) !== false;
		} catch (\Throwable $e) {
			return false;
		} finally {
			if (is_resource($handle))
			{
				fclose($handle);
			}
		}

		return $success;
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
	 */
	public static function getPaths($folder, $fileTypes = array('\.php', '\.js', '\.css', '\.less'), $recurse = true, $full = true): ?array
	{
		if (is_dir($folder))
		{
			// we must first store the current woking directory
			$joomla = getcwd();
			// we are changing the working directory to the component path
			chdir($folder);

			// make sure we have file type filter
			if (ArrayHelper::check($fileTypes))
			{
				// get the files
				foreach ($fileTypes as $type)
				{
					// get a list of files in the current directory tree
					$files[] = Folder::files('.', $type, $recurse, $full);
				}
			}
			elseif (StringHelper::check($fileTypes))
			{
				// get a list of files in the current directory tree
				$files[] = Folder::files('.', $fileTypes, $recurse, $full);
			}
			else
			{
				// get a list of files in the current directory tree
				$files[] = Folder::files('.', '.', $recurse, $full);
			}

			// change back to Joomla working directory
			chdir($joomla);

			// return array of files
			return array_map( fn($file) => str_replace('./', '/', (string) $file), (array) ArrayHelper::merge($files));
		}
		return null;
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
	 */
	public static function getPath($type = 'path', $target = 'filepath', $fileType = null, $key = '', $default = '', $createIfNotSet = true): string
	{
		// make sure to always have a string/path
		if(!StringHelper::check($default))
		{
			$default = JPATH_SITE . '/images/';
		}

		// get the global settings
		$filePath = Helper::getParams()->get($target, $default);

		// check the file path (revert to default only of not a hidden file path)
		if ('hiddenfilepath' !== $target && strpos((string) $filePath, (string) JPATH_SITE) === false)
		{
			$filePath = $default;
		}

		// create the folder if it does not exist
		if ($createIfNotSet && !is_dir($filePath))
		{
			Folder::create($filePath);
		}

		// setup the file name
		$fileName = '';

		// Get basic key
		$basickey = 'Th!s_iS_n0t_sAfe_buT_b3tter_then_n0thiug';
		// get the component helper
		$helper = Helper::get();
		// check if method exist in helper class
		if ($helper && Helper::methodExists('getCryptKey')) 
		{
			$basickey = $helper::getCryptKey('basic', $basickey);
		}

		// check the key
		if (!StringHelper::check($key))
		{
			$key = 'vDm';
		}

		// set the file name
		if (StringHelper::check($fileType))
		{
			// set the name
			$fileName = trim( md5($type . $target . $basickey . $key) . '.' . trim($fileType, '.'));
		}
		else
		{
			$fileName = trim( md5($type . $target . $basickey . $key)) . '.txt';
		}

		// return the url
		if ('url' === $type)
		{
			if (\strpos((string) $filePath, (string) JPATH_SITE) !== false)
			{
				$filePath = trim( str_replace( JPATH_SITE, '', (string) $filePath), '/');

				return Uri::root() . $filePath . '/' . $fileName;
			}

			// since the path is behind the root folder of the site, return only the root url (may be used to build the link)
			return Uri::root();
		}

		// sanitize the path
		return '/' . trim((string)  $filePath, '/' ) . '/' . $fileName;
	}

	/**
	 * Check if file exist
	 *
	 * @param  string   $path   The url/path to check
	 *
	 * @return  bool      If exist true
	 *
	 * @since  3.0.9
	 */
	public static function exists($path): bool
	{
		$exists = false;
		// if this is a local path
		if (strpos($path, 'http:') === false && strpos($path, 'https:') === false)
		{
			if (file_exists($path))
			{
				$exists = true;
			}
		}
		// check if we can use curl
		elseif (function_exists('curl_version'))
		{
			// initiate curl
			$ch = curl_init($path);
			// CURLOPT_NOBODY (do not return body)
			curl_setopt($ch, CURLOPT_NOBODY, true);
			// make call
			$result = curl_exec($ch);
			// check return value
			if ($result !== false)
			{
				// get the http CODE
				$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if ($statusCode !== 404)
				{
					$exists = true;
				}
			}
			// close the connection
			curl_close($ch);
		}
		elseif ($headers = @get_headers($path))
		{
			if(isset($headers[0]) && is_string($headers[0]) && strpos($headers[0],'404') === false)
			{
				$exists = true;
			}
		}
		return $exists;
	}
}

