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

namespace VDM\Joomla\Componentbuilder\Compiler\Extension\JoomlaSix;


use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Extension\InstallInterface;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\GetScriptInterface;


/**
 * Loading the Extension Installation Script Class
 * 
 * @since 5.1.2
 */
final class InstallScript implements GetScriptInterface
{
	/**
	 * The extension
	 *
	 * @var     InstallInterface|Object
	 * @since 3.2.0
	 */
	protected object $extension;

	/**
	 * The methods
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $methods = ['php_script', 'php_preflight', 'php_postflight', 'php_method'];

	/**
	 * The types
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $types = ['construct', 'install', 'update', 'uninstall', 'discover_install'];

	/**
	 * The construct bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $construct = [];

	/**
	 * The install bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $install = [];

	/**
	 * The update bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $update = [];

	/**
	 * The uninstall bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $uninstall = [];

	/**
	 * The preflight switch
	 *
	 * @var     bool
	 * @since 3.2.0
	 */
	protected bool $preflightActive = false;

	/**
	 * The preflight bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $preflightBucket = ['install' => [], 'uninstall' => [], 'discover_install' => [], 'update' => []];

	/**
	 * The postflight switch
	 *
	 * @var     bool
	 * @since 3.2.0
	 */
	protected bool $postflightActive = false;

	/**
	 * The postflight bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $postflightBucket = ['install' => [], 'uninstall' => [], 'discover_install' => [], 'update' => []];

	/**
	 * The paths of the old plugin class files
	 *
	 * @var     array
	 * @since  5.0.2
	 */
	protected array $removeFilePaths = [];

	/**
	 * The paths of the old plugin folders
	 *
	 * @var     array
	 * @since  5.0.2
	 */
	protected array $removeFolderPaths = [];

	/**
	 * get install script
	 *
	 * @param   Object       $extension     The extension object
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	public function get(object $extension): string
	{
		// purge the object
		$this->rest();

		// set the remove path
		$this->removeFilePaths = $extension->remove_file_paths ?? [];
		$this->removeFolderPaths = $extension->remove_folder_paths ?? [];

		// loop over methods and types
		foreach ($this->methods as $method)
		{
			foreach ($this->types as $type)
			{
				if (isset($extension->{'add_' . $method . '_' . $type})
					&& $extension->{'add_' . $method . '_' . $type} == 1
					&& StringHelper::check(
						$extension->{$method . '_' . $type}
					))
				{
					// add to the main methods
					if ('php_method' === $method || 'php_script' === $method)
					{
						$this->{$type}[] = $extension->{$method . '_' . $type};
					}
					else
					{
						// get the flight key
						$flight = str_replace('php_', '', (string) $method);
						// load the script to our bucket
						$this->{$flight . 'Bucket'}[$type][]  = $extension->{$method . '_' . $type};
						// show that the method is active
						$this->{$flight . 'Active'} = true;
					}
				}
			}
		}

		$this->extension = $extension;

		// return the class
		return $this->build();
	}

	/**
	 * Reset all bucket at the start of each build
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	protected function rest(): void
	{
		$this->removeFilePaths = [];
		$this->removeFolderPaths = [];
		$this->construct = [];
		$this->install = [];
		$this->update = [];
		$this->uninstall = [];
		$this->preflightActive = false;
		$this->preflightBucket = ['install' => [], 'uninstall' => [], 'discover_install' => [], 'update' => []];
		$this->postflightActive = false;
		$this->postflightBucket = ['install' => [], 'uninstall' => [], 'discover_install' => [], 'update' => []];
	}

	/**
	 * build the install class
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function build(): string
	{
		// start build
		$script = $this->head();

		// load constructor if set
		$script .= $this->construct();

		// load install method if set
		$script .= $this->main('install');

		// load update method if set
		$script .= $this->main('update');

		// load uninstall method if set
		$script .= $this->main('uninstall');

		// load preflight method if set
		$script .= $this->flight('preflight');

		// load postflight method if set
		$script .= $this->flight('postflight');

		// load remove files method
		$script .= $this->removeFiles();

		// close the class
		$script .= PHP_EOL . '}' . PHP_EOL;

		$script .= PHP_EOL . 'return new ' . $this->extension->installer_class_name . '();' . PHP_EOL;

		return $script;
	}

	/**
	 * get install script head
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function head(): string
	{
		// get the extension
		$extension = $this->extension;

		// start build
		$script = PHP_EOL . 'use Joomla\CMS\Factory;';
		$script .= PHP_EOL . 'use Joomla\CMS\Version;';
		$script .= PHP_EOL . 'use Joomla\CMS\Installer\InstallerAdapter;';

		$script .= PHP_EOL . 'use Joomla\CMS\Installer\InstallerScriptInterface;';

		$script .= PHP_EOL . 'use Joomla\CMS\Language\Text;';
		$script .= PHP_EOL . 'use Joomla\Filesystem\File;';
		$script .= PHP_EOL . 'use Joomla\Filesystem\Folder;' . PHP_EOL;
		$script .= PHP_EOL . '/**';
		$script .= PHP_EOL . ' * ' . $extension->official_name
			. ' script file.';
		$script .= PHP_EOL . ' *';
		$script .= PHP_EOL . ' * @package ' . $extension->class_name;
		$script .= PHP_EOL . ' */';
		$script .= PHP_EOL . 'class ' . $extension->installer_class_name;

		$script .= ' implements InstallerScriptInterface';

		$script .= PHP_EOL . '{';

		return $script;
	}

	/**
	 * get constructor
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function construct(): string
	{
		// the __construct script
		$script = PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . ' *' . Line::_(__Line__, __Class__)
				.' The CMS Application.';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1) . ' * @since  4.4.2';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'protected $app;';

		$script .= PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . ' *' . Line::_(__Line__, __Class__)
				.' A list of files to be deleted';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1) . ' * @var    array';
		$script .= PHP_EOL . Indent::_(1) . ' * @since  3.6';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'protected array $deleteFiles = [];';

		$script .= PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . ' *' . Line::_(__Line__, __Class__)
				.' A list of folders to be deleted';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1) . ' * @var    array';
		$script .= PHP_EOL . Indent::_(1) . ' * @since  3.6';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'protected array $deleteFolders = [];';

		$script .= PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . ' * Constructor';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1)
			. 'public function __construct()';
		$script .= PHP_EOL . Indent::_(1) . '{';

		$script .= PHP_EOL . Indent::_(2) . '//' . Line::_(__Line__, __Class__)
			. ' get application';
		$script .= PHP_EOL . Indent::_(2)
			. '$this->app = Factory::getApplication();' . PHP_EOL;

		if (ArrayHelper::check($this->construct))
		{
			// Native script loading has no installer adapter until the first lifecycle call.
			$script .= PHP_EOL . Indent::_(1) . '}';
			$script .= PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
			$script .= PHP_EOL . Indent::_(1) . ' *' . Line::_(__LINE__, __CLASS__)
				. ' Whether adapter-dependent initialization is running or completed.';
			$script .= PHP_EOL . Indent::_(1) . ' *';
			$script .= PHP_EOL . Indent::_(1) . ' * @var    bool';
			$script .= PHP_EOL . Indent::_(1) . ' * @since  6.2.0';
			$script .= PHP_EOL . Indent::_(1) . ' */';
			$script .= PHP_EOL . Indent::_(1) . 'private bool $jcbInstallerInitialized = false;';
			$script .= PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
			$script .= PHP_EOL . Indent::_(1) . ' *' . Line::_(__LINE__, __CLASS__)
				. ' Initialize custom installer state once the adapter is available.';
			$script .= PHP_EOL . Indent::_(1) . ' *';
			$script .= PHP_EOL . Indent::_(1) . ' * @param   InstallerAdapter  $adapter  The calling installer adapter.';
			$script .= PHP_EOL . Indent::_(1) . ' *';
			$script .= PHP_EOL . Indent::_(1) . ' * @return  void';
			$script .= PHP_EOL . Indent::_(1) . ' * @since   6.2.0';
			$script .= PHP_EOL . Indent::_(1) . ' */';
			$script .= PHP_EOL . Indent::_(1) . 'private function initializeInstaller(InstallerAdapter $adapter): void';
			$script .= PHP_EOL . Indent::_(1) . '{';
			$script .= PHP_EOL . Indent::_(2) . 'if ($this->jcbInstallerInitialized)';
			$script .= PHP_EOL . Indent::_(2) . '{';
			$script .= PHP_EOL . Indent::_(3) . 'return;';
			$script .= PHP_EOL . Indent::_(2) . '}';
			$script .= PHP_EOL . PHP_EOL . Indent::_(2) . '$this->jcbInstallerInitialized = true;';
			$script .= PHP_EOL . Indent::_(2) . 'try';
			$script .= PHP_EOL . Indent::_(2) . '{';
			$script .= PHP_EOL . Indent::_(3) . '$this->initializeInstallerHook($adapter);';
			$script .= PHP_EOL . Indent::_(2) . '}';
			$script .= PHP_EOL . Indent::_(2) . 'catch (\\Throwable $error)';
			$script .= PHP_EOL . Indent::_(2) . '{';
			$script .= PHP_EOL . Indent::_(3) . '$this->jcbInstallerInitialized = false;';
			$script .= PHP_EOL . Indent::_(3) . 'throw $error;';
			$script .= PHP_EOL . Indent::_(2) . '}';
			$script .= PHP_EOL . Indent::_(1) . '}';
			$script .= PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
			$script .= PHP_EOL . Indent::_(1) . ' *' . Line::_(__LINE__, __CLASS__)
				. ' Run the configured constructor body without rewriting its control flow.';
			$script .= PHP_EOL . Indent::_(1) . ' *';
			$script .= PHP_EOL . Indent::_(1) . ' * @param   InstallerAdapter  $adapter  The calling installer adapter.';
			$script .= PHP_EOL . Indent::_(1) . ' *';
			$script .= PHP_EOL . Indent::_(1) . ' * @return  mixed  A constructor return value is ignored.';
			$script .= PHP_EOL . Indent::_(1) . ' * @since   6.2.0';
			$script .= PHP_EOL . Indent::_(1) . ' */';
			$script .= PHP_EOL . Indent::_(1) . 'private function initializeInstallerHook(InstallerAdapter $adapter)';
			$script .= PHP_EOL . Indent::_(1) . '{';
			$script .= PHP_EOL . implode(PHP_EOL . PHP_EOL, $this->construct);
		}

		// check if custom remove file is set
		if ($this->removeFilePaths !== [] && strpos($script, '$this->deleteFiles') === false)
		{
			// add the default delete files
			foreach ($this->removeFilePaths as $filePath)
			{
				$script .= PHP_EOL . Indent::_(2) . "if (is_file(JPATH_ROOT . '$filePath'))";
				$script .= PHP_EOL . Indent::_(2) . "{";
				$script .= PHP_EOL . Indent::_(3) . "\$this->deleteFiles[] = '$filePath';";
				$script .= PHP_EOL . Indent::_(2) . "}";
			}
		}

		// check if custom remove file is set
		if ($this->removeFolderPaths !== [] && strpos($script, '$this->deleteFolders') === false)
		{
			// add the default delete folders
			foreach ($this->removeFolderPaths as $folderPath)
			{
				$script .= PHP_EOL . Indent::_(2) . "if (is_dir(JPATH_ROOT . '$folderPath'))";
				$script .= PHP_EOL . Indent::_(2) . "{";
				$script .= PHP_EOL . Indent::_(3) . "\$this->deleteFolders[] = '$folderPath';";
				$script .= PHP_EOL . Indent::_(2) . "}";
			}
		}

		// close the function
		$script .= PHP_EOL . Indent::_(1) . '}';

		// add remove files
		$this->preflightBucket['bottom'][] = Indent::_(2) . '//' . Line::_(__Line__, __Class__)
				.' remove old files and folders';
		$this->preflightBucket['bottom'][] = Indent::_(2) . '$this->removeFiles();';

		return $script;
	}

	/**
	 * build main methods
	 *
	 * @param   string  $name   the method being called
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function main(string $name): string
	{
		// load the install method
		$script = PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . " * Called on $name";
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @param   InstallerAdapter  $adapter  The object responsible for running this script';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @return  boolean  True on success';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'public function '
			. $name . '(InstallerAdapter $adapter): bool';
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= $this->nativeLifecycle($name, false);
		$script .= PHP_EOL . Indent::_(1) . '}';
		$script .= $this->hookHeader($name, false);
		$script .= PHP_EOL . implode(PHP_EOL . PHP_EOL, $this->{$name});
		// return true
		if ('uninstall' !== $name)
		{
			$script .= PHP_EOL . Indent::_(2) . 'return true;';
		}
		// close the function
		$script .= PHP_EOL . Indent::_(1) . '}';

		return $script;
	}

	/**
	 * build flight methods
	 *
	 * @param   string  $name   the method being called
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function flight(string $name): string
	{
		// the pre/post function types
		$script = PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1)
			. ' * Called before any type of action';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @param   InstallerAdapter  $adapter  The object responsible for running this script';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @return  boolean  True on success';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'public function '
			. $name . '(string $route, InstallerAdapter $adapter): bool';
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= $this->nativeLifecycle($name, true);
		$script .= PHP_EOL . Indent::_(1) . '}';
		$script .= $this->hookHeader($name, true);
		$script .= PHP_EOL . Indent::_(2) . '//' . Line::_(__Line__, __Class__)
			. ' retain the application variable available to configured hook bodies';
		$script .= PHP_EOL . Indent::_(2)
			. '$app = $this->app;' . PHP_EOL;

		// add the default version check (TODO) must make this dynamic
		if ('preflight' === $name)
		{
			$script .= PHP_EOL . Indent::_(2) . '//' . Line::_(__Line__, __Class__)
				.' the default for both install and update';
			$script .= PHP_EOL . Indent::_(2)
				. '$jversion = new Version();';
			$script .= PHP_EOL . Indent::_(2)
				. "if (!\$jversion->isCompatible('6.0.0'))";
			$script .= PHP_EOL . Indent::_(2) . '{';
			$script .= PHP_EOL . Indent::_(3)
				. "\$app->enqueueMessage('Please upgrade to at least Joomla! 6.0.0 before continuing!', 'error');";
			$script .= PHP_EOL . Indent::_(3) . 'return false;';
			$script .= PHP_EOL . Indent::_(2) . '}' . PHP_EOL;
		}

		if (!empty($this->{$name . 'Active'}))
		{
			// now add the scripts
			foreach ($this->{$name . 'Bucket'} as $route => $_script)
			{
				if (ArrayHelper::check($_script) && $route !== 'bottom')
				{
					// set the if and script
					$script .= PHP_EOL . Indent::_(2) . "if ('" . $route
						. "' === \$route)";
					$script .= PHP_EOL . Indent::_(2) . '{';
					$script .= PHP_EOL . implode(
							PHP_EOL . PHP_EOL, $_script
						);
					$script .= PHP_EOL . Indent::_(2) . '}' . PHP_EOL;
				}
			}
		}

		if (isset($this->{$name . 'Bucket'}['bottom']) && ArrayHelper::check($this->{$name . 'Bucket'}['bottom']))
		{
			$script .= PHP_EOL . implode(
				PHP_EOL , $this->{$name . 'Bucket'}['bottom']
			) . PHP_EOL;
		}

		// return true
		$script .= PHP_EOL . Indent::_(2) . 'return true;';
		// close the function
		$script .= PHP_EOL . Indent::_(1) . '}';

		return $script;
	}


	/**
	 * Emit the native lifecycle boundary without changing a configured hook body.
	 *
	 * @param   string  $name    Lifecycle method name.
	 * @param   bool    $flight  Whether the lifecycle receives an operation route.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function nativeLifecycle(string $name, bool $flight): string
	{
		$script = '';

		if (ArrayHelper::check($this->construct))
		{
			$script .= PHP_EOL . Indent::_(2) . '$this->initializeInstaller($adapter);';
		}

		$arguments = $flight ? '$route, $adapter' : '$adapter';
		$script .= PHP_EOL . Indent::_(2) . '$result = $this->' . $name . 'Hook(' . $arguments . ');';
		$script .= PHP_EOL . PHP_EOL . Indent::_(2) . '//' . Line::_(__LINE__, __CLASS__)
			. ' A hook with no return value completed successfully; explicit failures remain failures.';
		$script .= PHP_EOL . Indent::_(2) . 'return $result === null ? true : (bool) $result;';

		return $script;
	}

	/**
	 * Emit a private hook retaining the stored body and its original return values.
	 *
	 * @param   string  $name    Lifecycle method name.
	 * @param   bool    $flight  Whether the lifecycle receives an operation route.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function hookHeader(string $name, bool $flight): string
	{
		$script = PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . ' *' . Line::_(__LINE__, __CLASS__)
			. ' Run the ' . $name . ' hook and preserve its explicit return value.';
		$script .= PHP_EOL . Indent::_(1) . ' *';

		if ($flight)
		{
			$script .= PHP_EOL . Indent::_(1) . ' * @param   string            $route    The installation operation.';
		}

		$script .= PHP_EOL . Indent::_(1) . ' * @param   InstallerAdapter  $adapter  The calling installer adapter.';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1) . ' * @return  mixed';
		$script .= PHP_EOL . Indent::_(1) . ' * @since   6.2.0';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$arguments = $flight ? 'string $route, InstallerAdapter $adapter' : 'InstallerAdapter $adapter';
		$script .= PHP_EOL . Indent::_(1) . 'private function ' . $name . 'Hook(' . $arguments . ')';
		$script .= PHP_EOL . Indent::_(1) . '{';

		return $script;
	}

	/**
	 * build remove files methods
	 *
	 * @return  string
	 * @since   5.0.2
	 */
	protected function removeFiles(): string
	{
		$script = PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . ' * Remove the files and folders in the given array from';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1) . ' * @return  void';
		$script .= PHP_EOL . Indent::_(1) . ' * @since   5.0.2';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'protected function removeFiles()';
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= PHP_EOL . Indent::_(2) . 'if (!empty($this->deleteFiles))';
		$script .= PHP_EOL . Indent::_(2) . '{';
		$script .= PHP_EOL . Indent::_(3) . 'foreach ($this->deleteFiles as $file)';
		$script .= PHP_EOL . Indent::_(3) . '{';
		$script .= PHP_EOL . Indent::_(4) . 'if (is_file(JPATH_ROOT . $file) && !File::delete(JPATH_ROOT . $file))';
		$script .= PHP_EOL . Indent::_(4) . '{';
		$script .= PHP_EOL . Indent::_(5) . 'echo Text::sprintf(\'JLIB_INSTALLER_ERROR_FILE_FOLDER\', $file) . \'<br>\';';
		$script .= PHP_EOL . Indent::_(4) . '}';
		$script .= PHP_EOL . Indent::_(3) . '}';
		$script .= PHP_EOL . Indent::_(2) . '}';
		$script .= PHP_EOL . PHP_EOL . Indent::_(2) . 'if (!empty($this->deleteFolders))';
		$script .= PHP_EOL . Indent::_(2) . '{';
		$script .= PHP_EOL . Indent::_(3) . 'foreach ($this->deleteFolders as $folder)';
		$script .= PHP_EOL . Indent::_(3) . '{';
		$script .= PHP_EOL . Indent::_(4) . 'if (is_dir(JPATH_ROOT . $folder) && !Folder::delete(JPATH_ROOT . $folder))';
		$script .= PHP_EOL . Indent::_(4) . '{';
		$script .= PHP_EOL . Indent::_(5) . 'echo Text::sprintf(\'JLIB_INSTALLER_ERROR_FILE_FOLDER\', $folder) . \'<br>\';';
		$script .= PHP_EOL . Indent::_(4) . '}';
		$script .= PHP_EOL . Indent::_(3) . '}';
		$script .= PHP_EOL . Indent::_(2) . '}';
		$script .= PHP_EOL . Indent::_(1) . '}';

		return $script;
	}
}

