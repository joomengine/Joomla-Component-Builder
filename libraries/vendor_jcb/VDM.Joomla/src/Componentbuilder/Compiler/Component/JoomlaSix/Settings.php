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

namespace VDM\Joomla\Componentbuilder\Compiler\Component\JoomlaSix;


use VDM\Joomla\Componentbuilder\Compiler\Factory as Compiler;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Registry;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Component;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Paths;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Dynamicpath;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Pathfix;
use VDM\Joomla\Utilities\FileHelper;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Component\SettingsInterface;


/**
 * Compiler Component Joomla 6 Settings
 * 
 * @since 5.1.2
 */
final class Settings implements SettingsInterface
{
	/**
	 * The standard folders
	 *
	 * @var      array
	 * @since 3.2.0
	 */
	protected array $standardFolders = [
		'site',
		'admin',
		'media'
	];

	/**
	 * The standard root files
	 *
	 * @var      array
	 * @since 3.2.0
	 */
	protected array $standardRootFiles = [
		'access.xml',
		'config.xml',
		'controller.php',
		'index.html',
		'README.txt'
	];

	/**
	 * Compiler Joomla Version Data
	 *
	 * @var    object|null
	 * @since 3.2.0
	 */
	protected ?object $data = null;

	/**
	 * Compiler Config
	 *
	 * @var    Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * The compiler registry
	 *
	 * @var    Registry
	 * @since 3.2.0
	 */
	protected Registry $registry;

	/**
	 * Compiler Event
	 *
	 * @var    EventInterface
	 * @since 3.2.0
	 */
	protected EventInterface $event;

	/**
	 * Compiler Placeholder
	 *
	 * @var    Placeholder
	 * @since 3.2.0
	 */
	protected Placeholder $placeholder;

	/**
	 * Compiler Component
	 *
	 * @var    Component
	 * @since 3.2.0
	 **/
	protected Component $component;

	/**
	 * Compiler Utilities Paths
	 *
	 * @var    Paths
	 * @since 3.2.0
	 */
	protected Paths $paths;

	/**
	 * Compiler Component Dynamic Path
	 *
	 * @var    Dynamicpath
	 * @since 3.2.0
	 **/
	protected Dynamicpath $dynamicpath;

	/**
	 * Compiler Component Pathfix
	 *
	 * @var    Pathfix
	 * @since 3.2.0
	 **/
	protected Pathfix $pathfix;

	/**
	 * Constructor
	 *
	 * @param Config|null           $config       The compiler config object.
	 * @param Registry|null         $registry     The compiler registry object.
	 * @param EventInterface|null   $event        The compiler event api object.
	 * @param Placeholder|null      $placeholder  The compiler placeholder object.
	 * @param Component|null        $component    The component class.
	 * @param Paths|null            $paths        The compiler paths object.
	 * @param Dynamicpath|null      $dynamicpath  The compiler dynamic path object.
	 * @param Pathfix|null          $pathfix      The compiler path fixing object.
	 *
	 * @since 3.2.0
	 */
	public function __construct(?Config $config = null, ?Registry $registry = null,
		?EventInterface $event = null, ?Placeholder $placeholder = null,
		?Component $component = null, ?Paths $paths = null,
		?Dynamicpath $dynamicpath = null, ?Pathfix $pathfix = null)
	{
		$this->config = $config ?: Compiler::_('Config');
		$this->registry = $registry ?: Compiler::_('Registry');
		$this->event = $event ?: Compiler::_('Event');
		$this->placeholder = $placeholder ?: Compiler::_('Placeholder');
		$this->component = $component ?: Compiler::_('Component');
		$this->paths = $paths ?: Compiler::_('Utilities.Paths');
		$this->dynamicpath = $dynamicpath ?: Compiler::_('Utilities.Dynamicpath');
		$this->pathfix = $pathfix ?: Compiler::_('Utilities.Pathfix');

		// add component endpoint file to stander list of root files
		$this->standardRootFiles[] = $this->component->get('name_code') . '.php';
	}

	/**
	 * Check if data set is loaded
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	public function exists(): bool
	{
		if (!$this->isSet())
		{
			// load the data
			$this->data = $this->get();

			if (!$this->isSet())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Get Joomla - Folder Structure to Create
	 *
	 * @return  object The version related structure
	 * @since 3.2.0
	 */
	public function structure(): object
	{
		return $this->data->create;
	}

	/**
	 * Get Joomla - Move Multiple Structure
	 *
	 * @return  object The version related multiple structure
	 * @since 3.2.0
	 */
	public function multiple(): object
	{
		return $this->data->move->dynamic;
	}

	/**
	 * Get Joomla - Move Single Structure
	 *
	 * @return  object  The version related single structure
	 * @since 3.2.0
	 */
	public function single(): object
	{
		return $this->data->move->static;
	}

	/**
	 * Check if Folder is a Standard Folder
	 *
	 * @param   string  $folder    The folder name
	 *
	 * @return  bool  true if the folder exists
	 * @since 3.2.0
	 */
	public function standardFolder(string $folder): bool
	{
		return in_array($folder, $this->standardFolders);
	}

	/**
	 * Check if File is a Standard Root File
	 *
	 * @param   string  $file    The file name
	 *
	 * @return  bool  true if the file exists
	 * @since 3.2.0
	 */
	public function standardRootFile(string $file): bool
	{
		return in_array($file, $this->standardRootFiles);
	}

	/**
	 * Check if Data is Set
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	private function isSet(): bool
	{
		return is_object($this->data) &&
			isset($this->data->create) && 
			isset($this->data->move) && 
			isset($this->data->move->static) && 
			isset($this->data->move->dynamic);
	}

	/**
	 * get the Joomla Version Data
	 *
	 * @return  object|null The version data
	 * @since 3.2.0
	 */
	private function get(): ?object
	{
		// override option
		$customSettings = $this->paths->template_path . '/settings_' .
			$this->config->component_code_name . '.json';

		// get the data
		$version_data = $this->readJsonFile($customSettings);

		if (is_null($version_data) || !$this->isValidData($version_data))
		{
			return null;
		}

		// The native chart helper is packaged only when a view requests charts.
		if (!$this->config->get('google_chart', false))
		{
			unset($version_data->move->static->{'ADMIN_CHARTBUILDER.php'});
		}

		if (!$this->component->get('add_email_helper'))
		{
			unset($version_data->move->static->{'ADMIN_DKIM_FIELD.php'});
			unset($version_data->move->static->{'dkim.js'});
		}

		$this->addFolders($version_data);
		$this->addFiles($version_data);

		// Trigger Event: jcb_ce_onAfterSetJoomlaVersionData
		$this->event->trigger(
			'jcb_ce_onAfterSetJoomlaVersionData', [&$version_data]
		);

		return $version_data;
	}

	/**
	 * Read the Json file data
	 *
	 * @param string  $filePath
	 *
	 * @return  object|null The version data
	 * @since 3.2.0
	 */
	private function readJsonFile(string $filePath): ?object
	{
		if (FileHelper::exists($filePath))
		{
			$jsonContent = FileHelper::getContent($filePath);
		}
		else
		{
			$jsonContent = FileHelper::getContent($this->paths->template_path . '/settings.json');
		}

		if (JsonHelper::check($jsonContent))
		{
			return json_decode((string) $jsonContent);
		}

		return null;
	}

	/**
	 * Check if this is valid data
	 *
	 * @param object $versionData
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	private function isValidData(object $versionData): bool
	{
		return isset($versionData->create) && 
			isset($versionData->move) && 
			isset($versionData->move->static) && 
			isset($versionData->move->dynamic);
	}

	/**
	 * Add Folders
	 *
	 * @param object $versionData
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function addFolders(object &$versionData)
	{
		if (!$this->component->isArray('folders'))
		{
			return;
		}

		// pointer tracker
		$pointer_tracker = 'h';
		foreach ($this->component->get('folders') as $custom)
		{
			// check type of target type
			$_target_type = 'c0mp0n3nt';
			if (isset($custom['target_type']))
			{
				$_target_type = $custom['target_type'];
			}

			// for good practice
			$this->pathfix->set(
				$custom, ['path', 'folder', 'folderpath']
			);

			// fix custom path
			if (isset($custom['path'])
				&& StringHelper::check($custom['path']))
			{
				$custom['path'] = trim((string) $custom['path'], '/');
			}

			// by default custom path is true
			$customPath = 'custom';

			// set full path if this is a full path folder
			if (!isset($custom['folder']) && isset($custom['folderpath']))
			{
				// update the dynamic path
				$custom['folderpath'] = $this->dynamicpath->update(
					$custom['folderpath']
				);

				// set the folder path with / if does not have a drive/windows full path
				$custom['folder'] = (preg_match(
					'/^[a-z]:/i', $custom['folderpath']
				)) ? trim($custom['folderpath'], '/')
					: '/' . trim($custom['folderpath'], '/');

				// remove the file path
				unset($custom['folderpath']);

				// triget fullpath
				$customPath = 'full';
			}

			// make sure we use the correct name
			$pathArray   = (array) explode('/', (string) $custom['path']);
			$lastFolder  = end($pathArray);

			// only rename folder if last has folder name
			if (isset($custom['rename']) && $custom['rename'] == 1)
			{
				$custom['path'] = str_replace(
					'/' . $lastFolder, '', (string) $custom['path']
				);
				$rename         = 'new';
				$newname        = $lastFolder;
			}
			elseif ('full' === $customPath)
			{
				// make sure we use the correct name
				$folderArray = (array) explode('/', (string) $custom['folder']);
				$lastFolder  = end($folderArray);
				$rename      = 'new';
				$newname     = $lastFolder;
			}
			else
			{
				$rename     = false;
				$newname    = '';
			}

			// insure we have no duplicates
			$key_pointer = StringHelper::safe(
					$custom['folder']
				) . '_f' . $pointer_tracker;

			$pointer_tracker++;

			// fix custom path
			$custom['path'] = ltrim((string) $custom['path'], '/');

			// set new folder to object
			$versionData->move->static->{$key_pointer} = new \stdClass();
			$versionData->move->static->{$key_pointer}->naam = str_replace('//', '/', (string) $custom['folder']);
			$versionData->move->static->{$key_pointer}->path = $_target_type . '/' . $custom['path'];
			$versionData->move->static->{$key_pointer}->rename = $rename;
			$versionData->move->static->{$key_pointer}->newName = $newname;
			$versionData->move->static->{$key_pointer}->type = 'folder';
			$versionData->move->static->{$key_pointer}->custom = $customPath;

			// set the target if type and id is found
			if (isset($custom['target_id']) && isset($custom['target_type']))
			{
				$versionData->move->static->{$key_pointer}->_target = [
					'key'  => $custom['target_id'] . '_' . $custom['target_type'],
					'type' => $custom['target_type']
				];
			}
		}

		$this->component->remove('folders');
	}

	/**
	 * Add Files
	 *
	 * @param object $versionData
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function addFiles(object &$versionData)
	{
		if (!$this->component->isArray('files')) {
			return;
		}

		// pointer tracker
		$pointer_tracker = 'h';
		foreach ($this->component->get('files') as $custom)
		{
			// check type of target type
			$_target_type = 'c0mp0n3nt';
			if (isset($custom['target_type']))
			{
				$_target_type = $custom['target_type'];
			}

			// for good practice
			$this->pathfix->set(
				$custom, ['path', 'file', 'filepath']
			);

			// by default custom path is true
			$customPath = 'custom';

			// set full path if this is a full path file
			if (!isset($custom['file']) && isset($custom['filepath']))
			{
				// update the dynamic path
				$custom['filepath'] = $this->dynamicpath->update(
					$custom['filepath']
				);

				// set the file path with / if does not have a drive/windows full path
				$custom['file'] = (preg_match('/^[a-z]:/i', $custom['filepath']))
					? trim($custom['filepath'], '/') : '/' . trim($custom['filepath'], '/');

				// remove the file path
				unset($custom['filepath']);

				// triget fullpath
				$customPath = 'full';
			}

			// make sure we have not duplicates
			$key_pointer = StringHelper::safe(
					$custom['file']
				) . '_g' . $pointer_tracker;

			$pointer_tracker++;

			// set new file to object
			$versionData->move->static->{$key_pointer} = new \stdClass();
			$versionData->move->static->{$key_pointer}->naam = str_replace('//', '/', (string) $custom['file']);

			// update the dynamic component name placholders in file names
			$custom['path'] = $this->placeholder->update_(
				$custom['path']
			);

			// get the path info
			$pathInfo = pathinfo((string) $custom['path']);
			if (isset($pathInfo['extension']) && $pathInfo['extension'])
			{
				$pathInfo['dirname'] = trim($pathInfo['dirname'], '/');

				// set the info
				$versionData->move->static->{$key_pointer}->path = $_target_type . '/' . $pathInfo['dirname'];
				$versionData->move->static->{$key_pointer}->rename = 'new';
				$versionData->move->static->{$key_pointer}->newName = $pathInfo['basename'];
			}
			elseif ('full' === $customPath)
			{
				// fix custom path
				$custom['path'] = ltrim((string) $custom['path'], '/');

				// get file array
				$fileArray = (array) explode('/', (string) $custom['file']);

				// set the info
				$versionData->move->static->{$key_pointer}->path = $_target_type . '/' . $custom['path'];
				$versionData->move->static->{$key_pointer}->rename = 'new';
				$versionData->move->static->{$key_pointer}->newName = end($fileArray);
			}
			else
			{
				// fix custom path
				$custom['path'] = ltrim((string) $custom['path'], '/');

				// set the info
				$versionData->move->static->{$key_pointer}->path = $_target_type . '/' . $custom['path'];
				$versionData->move->static->{$key_pointer}->rename = false;
			}

			$versionData->move->static->{$key_pointer}->type = 'file';
			$versionData->move->static->{$key_pointer}->custom = $customPath;

			// set the target if type and id is found
			if (isset($custom['target_id'])
				&& isset($custom['target_type']))
			{
				$versionData->move->static->{$key_pointer}->_target = [
					'key'  => $custom['target_id'] . '_' . $custom['target_type'],
					'type' => $custom['target_type']
				];
			}

			// check if file should be updated
			if (!isset($custom['notnew']) || $custom['notnew'] == 0
				|| $custom['notnew'] != 1)
			{
				$this->registry->appendArray('files.not.new', $key_pointer);
			}
			else
			{
				// update the file content
				$this->registry->set('update.file.content.' . $key_pointer, true);
			}
		}

		$this->component->remove('files');
	}
}

