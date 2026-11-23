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

namespace VDM\Joomla\Componentbuilder\Compiler\Power;


use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplicationInterface as CMSApplication;
use Joomla\CMS\Language\Text;
use VDM\Joomla\Componentbuilder\Compiler\Factory as Compiler;
use VDM\Joomla\Componentbuilder\Compiler\Power;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Registry;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Counter;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Paths;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Folder;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\File;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Files;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Utilities\FileHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;


/**
 * Power Structure Builder Class
 * 
 * @since 3.2.0
 */
class Structure
{
	/**
	 * we track the creation of htaccess files
	 *
	 * @var    array
	 * @since 3.2.0
	 **/
	protected array $htaccess = [];

	/**
	 * Power Build Tracker
	 *
	 * @var    array
	 * @since 3.2.0
	 **/
	protected array $done = [];

	/**
	 * Path Build Tracker
	 *
	 * @var    array
	 * @since 3.2.0
	 **/
	protected array $path_done = [];

	/**
	 * Power Objects
	 *
	 * @var    Power
	 * @since 3.2.0
	 **/
	protected Power $power;

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
	 * Compiler Counter
	 *
	 * @var    Counter
	 * @since 3.2.0
	 */
	protected Counter $counter;

	/**
	 * Compiler Utilities Paths
	 *
	 * @var    Paths
	 * @since 3.2.0
	 */
	protected Paths $paths;

	/**
	 * Compiler Utilities Folder
	 *
	 * @var    Folder
	 * @since 3.2.0
	 */
	protected Folder $folder;

	/**
	 * Compiler Utilities File
	 *
	 * @var    File
	 * @since 3.2.0
	 */
	protected File $file;

	/**
	 * Compiler Utilities Files
	 *
	 * @var    Files
	 * @since 3.2.0
	 */
	protected Files $files;

	/**
	 * Database object to query local DB
	 *
	 * @var    CMSApplication
	 * @since 3.2.0
	 **/
	protected CMSApplication $app;

	/**
	 * Constructor
	 *
	 * @param Power|null             $power        The power object.
	 * @param Config|null            $config       The compiler config object.
	 * @param Registry|null          $registry     The compiler registry object.
	 * @param EventInterface|null    $event        The compiler event api object.
	 * @param Counter|null           $counter      The compiler counter object.
	 * @param Paths|null             $paths        The compiler paths object.
	 * @param Folder|null            $folder       The compiler folder object.
	 * @param File|null              $file         The compiler file object.
	 * @param Files|null             $files        The compiler files object.
	 * @param CMSApplication|null    $app          The CMS Application object.
	 *
	 * @throws \Exception
	 * @since 3.2.0
	 */
	public function __construct(?Power $power = null, ?Config $config = null,
		?Registry $registry = null, ?EventInterface $event = null,
		?Counter $counter = null, ?Paths $paths = null, ?Folder $folder = null,
		?File $file = null, ?Files $files = null, ?CMSApplication $app = null)
	{
		$this->power = $power ?: Compiler::_('Power');
		$this->config = $config ?: Compiler::_('Config');
		$this->registry = $registry ?: Compiler::_('Registry');
		$this->event = $event ?: Compiler::_('Event');
		$this->counter = $counter ?: Compiler::_('Utilities.Counter');
		$this->paths = $paths ?: Compiler::_('Utilities.Paths');
		$this->folder = $folder ?: Compiler::_('Utilities.Folder');
		$this->file = $file ?: Compiler::_('Utilities.File');
		$this->files = $files ?: Compiler::_('Utilities.Files');
		$this->app = $app ?: Factory::getApplication();
	}

	/**
	 * Build the Powers files, folders
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	public function build()
	{
		if (ArrayHelper::check($this->power->active))
		{
			// Trigger Event: jcb_ce_onBeforeSetModules
			$this->event->trigger(
				'jcb_ce_onBeforeBuildPowers'
			);

			// set super power details
			$this->setSuperPowerDetails();

			foreach ($this->power->active as $guid => $power)
			{
				if (isset($this->done[$guid]))
				{
					continue;
				}

				if (ObjectHelper::check($power)
					&& isset($power->path)
					&& StringHelper::check(
						$power->path
					))
				{
					// activate dynamic folders
					$this->setDynamicFolders();

					// power path
					$pathRoot = $power->path_root ?? 'component';
					if ($pathRoot === 'component')
					{
						$power->full_path        = $this->paths->component_path . '/'
							. $power->path;
						$power->full_path_jcb    = $this->paths->component_path . '/'
							. $power->path_jcb;
						$power->full_path_parent = $this->paths->component_path . '/'
							. $power->path_parent;
					}
					else
					{
						$power->full_path        = $pathRoot . '/'
							. $power->path;
						$power->full_path_jcb    = $pathRoot . '/'
							. $power->path_jcb;
						$power->full_path_parent = $pathRoot . '/'
							. $power->path_parent;
					}

					// set the power paths
					$this->registry->set('dynamic_paths.' . $power->key, $power->full_path_parent);

					// create the power folder if it does not exist
					// we do it like this to add html files to each part
					$this->folder->create($power->full_path_jcb);
					$this->folder->create($power->full_path_parent);
					$this->folder->create($power->full_path);

					$bom = '<?php' . PHP_EOL . '// A POWER FILE' .
						PHP_EOL . Placefix::_h('BOM') . PHP_EOL;

					// add custom override if found
					if ($power->add_licensing_template == 2)
					{
						$bom = '<?php' . PHP_EOL . $power->licensing_template;
					}

					// set the main power php file
					$this->createFile($bom . PHP_EOL . Placefix::_h('POWERCODE') . PHP_EOL,
						$power->full_path, $power->file_name . '.php', $power->key);

					// set super power files
					$this->setSuperPowerFiles($power, $bom);

					// set htaccess once per path
					$this->setHtaccess($power);

					// do each power just once
					$this->done[$guid] = true;
				}
			}
		}
	}

	/**
	 * Create a file with optional custom content and save it to the given path.
	 *
	 * @param string $content      The content.
	 * @param string $fullPath     The full path to the destination folder.
	 * @param string $fileName     The file name without the extension.
	 * @param string $key          The key to append the file details.
	 *
	 * @return void
	 * @since 3.2.0
	 */
	private function createFile(string $content, string $fullPath, string $fileName, string $key)
	{
		$file_details = [
			'path' => $fullPath . '/' . $fileName,
			'name' => $fileName,
			'zip' => $fileName
		];

		// Write the content to the file
		$this->file->write($file_details['path'], $content);

		// Append the file details to the files array
		$this->files->appendArray($key, $file_details);

		// Increment the file counter
		$this->counter->file++;

		// Increment the power counter
		$this->counter->power++;
	}

	/**
	 * Set the .htaccess for this power path
	 *
	 * @param object   $power    The power object
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function setHtaccess(object &$power)
	{
		if (!isset($this->htaccess[$power->path_jcb]))
		{
			// set the htaccess data
			$data = '# Apache 2.4+' . PHP_EOL .
				'<IfModule mod_authz_core.c>' . PHP_EOL .
				'  Require all denied' . PHP_EOL .
				'</IfModule>' . PHP_EOL . PHP_EOL .
				'# Apache 2.0-2.2' . PHP_EOL .
				'<IfModule !mod_authz_core.c>' . PHP_EOL .
				'  Deny from all' . PHP_EOL .
				'</IfModule>' . PHP_EOL;

			// now we must add the .htaccess file
			$fileDetails = array('path' => $power->full_path_jcb . '/.htaccess',
			                     'name' => '.htaccess',
			                     'zip'  => '.htaccess');
			$this->file->write(
				$fileDetails['path'], $data
			);
			$this->files->appendArray($power->key, $fileDetails);

			// count the file created
			$this->counter->file++;

			// now we must add the htaccess.txt file where the zip package my not get the [.] files
			$fileDetails = array('path' => $power->full_path_jcb . '/htaccess.txt',
			                     'name' => 'htaccess.txt',
			                     'zip'  => 'htaccess.txt');
			$this->file->write(
				$fileDetails['path'], $data
			);
			$this->files->appendArray($power->key, $fileDetails);

			// count the file created
			$this->counter->file++;

			// now we must add the web.config file
			$fileDetails = array('path' => $power->full_path_jcb . '/web.config',
			                     'name' => 'web.config',
			                     'zip'  => 'web.config');
			$this->file->write(
				$fileDetails['path'],
				'<?xml version="1.0"?>' . PHP_EOL .
				'<configuration>' . PHP_EOL .
				'    <system.web>' . PHP_EOL .
				'        <authorization>' . PHP_EOL .
				'            <deny users="*" />' . PHP_EOL .
				'        </authorization>' . PHP_EOL .
				'    </system.web>' . PHP_EOL .
				'</configuration>' . PHP_EOL
			);
			$this->files->appendArray($power->key, $fileDetails);

			// count the file created
			$this->counter->file++;

			// we set these files only once
			$this->htaccess[$power->path_jcb] = true;
		}
	}

	/**
	 * Add the dynamic folders
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function setDynamicFolders()
	{
		// check if we should add the dynamic folder moving script to the installer script
		if (!$this->registry->get('set_move_folders_install_script'))
		{
			$function = 'setDynamicF0ld3rs';
			$script = 'script.php';
			if ($this->config->get('joomla_version', 3) != 3)
			{
				$function = 'moveFolders';
				$script = 'ComponentnameInstallerScript.php';
			}

			// add the setDynamicF0ld3rs() method to the install script.php file
			$this->registry->set('set_move_folders_install_script', true);

			// set message that this was done (will still add a tutorial link later)
			$this->app->enqueueMessage(
				Text::_('COM_COMPONENTBUILDER_HR_HTHREEDYNAMIC_FOLDERS_WERE_DETECTEDHTHREE'),
				'Notice'
			);
			$this->app->enqueueMessage(
				Text::sprintf('COM_COMPONENTBUILDER_A_METHOD_S_WAS_ADDED_TO_THE_INSTALL_BSB_OF_THIS_PACKAGE_TO_INSURE_THAT_THE_FOLDERS_ARE_COPIED_INTO_THE_CORRECT_PLACE_WHEN_THIS_COMPONENT_IS_INSTALLED',
					$function, $script
				),
				'Notice'
			);
		}
	}

	/**
	 * Set the super powers details structure
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function setSuperPowerDetails()
	{
		if ($this->config->add_super_powers && ArrayHelper::check($this->power->superpowers))
		{
			foreach ($this->power->superpowers as $path => $powers)
			{
				if (isset($this->path_done[$path]))
				{
					continue;
				}

				// get existing files
				$this->loadExistingSuperPower($path);

				// create the path if it does not exist
				$this->folder->create($path, false);

				$key = StringHelper::safe($path);

				// set the super powers readme file
				$this->createFile(Placefix::_h('POWERREADME'),
					$path, 'README.md', $key);

				// set the super power index file
				$this->createFile(Placefix::_h('POWERINDEX'), $path,
					'super-powers.json', $key);

				// do each path just once
				$this->path_done[$path] = true;
			}
		}
	}

	/**
	 * Set the super power file paths
	 *
	 * @param object   $power     The power object
	 * @param string   $bom       The bom for the top of the PHP files
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function setSuperPowerFiles(object &$power, string $bom)
	{
		if ($this->config->add_super_powers && is_array($power->super_power_paths) && $power->super_power_paths !== [])
		{
			foreach ($power->super_power_paths as $path)
			{
				// create the path if it does not exist
				$this->folder->create($path, false);

				// set the super power php file
				$this->createFile($bom . PHP_EOL . Placefix::_h('POWERCODE') . PHP_EOL,
					$path, 'code.php', $power->key);

				// set the super power php RAW file
				$this->createFile(Placefix::_h('CODEPOWER'),
					$path, 'code.power', $power->key);

				// set the super power json file
				$this->createFile(Placefix::_h('POWERLINKER'), $path,
					'settings.json', $power->key);

				// set the super power readme file
				$this->createFile(Placefix::_h('POWERREADME'), $path,
					'README.md', $power->key);
			}
		}
	}

	/**
	 * Set the super power file paths
	 *
	 * @param string   $repository    The super power repository
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function loadExistingSuperPower(string $repository)
	{
		if (!isset($this->power->old_superpowers[$repository]) && ($content = FileHelper::getContent($repository . '/super-powers.json', null)) !== null &&
			JsonHelper::check($content))
		{
			$this->power->old_superpowers[$repository] = json_decode($content, true);
		}
	}
}

