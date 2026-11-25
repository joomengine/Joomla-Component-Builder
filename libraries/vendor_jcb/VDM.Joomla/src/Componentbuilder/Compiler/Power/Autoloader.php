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


use VDM\Joomla\Componentbuilder\Compiler\Power;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne as Content;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Utilities\ArrayHelper;


/**
 * Compiler Autoloader
 * 
 * @since 3.2.0
 */
class Autoloader
{
	/**
	 * The Power Class.
	 *
	 * @var   Power
	 * @since 3.2.0
	 */
	protected Power $power;

	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * The ContentOne Class.
	 *
	 * @var   Content
	 * @since 3.2.0
	 */
	protected Content $content;

	/**
	 * Installer Class Autoloader
	 *
	 * @var    string
	 * @since 5.0.2
	 **/
	protected string $installerhelper = '';

	/**
	 * Helper Class Autoloader
	 *
	 * @var    string
	 * @since 3.2.0
	 **/
	protected string $helper = '';

	/**
	 * Constructor.
	 *
	 * @param Power     $power     The Power Class.
	 * @param Config    $config    The Config Class.
	 * @param Content   $content   The Content One Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Power $power, Config $config, Content $content)
	{
		$this->power = $power;
		$this->config = $config;
		$this->content = $content;

		// reset all autoloaders power placeholders
		$this->content->set('ADMIN_POWER_HELPER', '');
		$this->content->set('SITE_POWER_HELPER', '');
		$this->content->set('INSTALLER_POWER_HELPER', '');
		$this->content->set('PLUGIN_POWER_AUTOLOADER', '');
		$this->content->set('SITE_PLUGIN_POWER_AUTOLOADER', '');
		$this->content->set('POWER_AUTOLOADER', '');
		$this->content->set('ONE_POWER_AUTOLOADER', '');
		$this->content->set('TWO_POWER_AUTOLOADER', '');
		$this->content->set('THREE_POWER_AUTOLOADER', '');
		$this->content->set('FOUR_POWER_AUTOLOADER', '');
		$this->content->set('SITE_POWER_AUTOLOADER', '');
		$this->content->set('SITE_ONE_POWER_AUTOLOADER', '');
		$this->content->set('SITE_TWO_POWER_AUTOLOADER', '');
		$this->content->set('SITE_THREE_POWER_AUTOLOADER', '');
		$this->content->set('SITE_FOUR_POWER_AUTOLOADER', '');
		$this->content->set('INSTALLER_POWER_AUTOLOADER_ARRAY', '');
	}

	/**
	 * Set the autoloader into the active content array
	 *
	 * @return void
	 * @since 3.2.0
	 */
	public function setFiles()
	{
		// for plugins
		$this->content->set('PLUGIN_POWER_AUTOLOADER', PHP_EOL . PHP_EOL . $this->getAutoloaderFile(2));
		$this->content->set('SITE_PLUGIN_POWER_AUTOLOADER', PHP_EOL . PHP_EOL . $this->getAutoloaderFile(2, 'JPATH_SITE'));

		// for site spaced special cases
		$this->content->set('SITE_ONE_POWER_AUTOLOADER', $this->getAutoloaderFile(1, 'JPATH_SITE'));
		$this->content->set('SITE_TWO_POWER_AUTOLOADER', $this->getAutoloaderFile(2, 'JPATH_SITE'));
		$this->content->set('SITE_THREE_POWER_AUTOLOADER', $this->getAutoloaderFile(3, 'JPATH_SITE'));
		$this->content->set('SITE_FOUR_POWER_AUTOLOADER', $this->getAutoloaderFile(4, 'JPATH_SITE'));

		// for admin spaced special cases
		$this->content->set('ONE_POWER_AUTOLOADER', $this->getAutoloaderFile(1));
		$this->content->set('TWO_POWER_AUTOLOADER', $this->getAutoloaderFile(2));
		$this->content->set('THREE_POWER_AUTOLOADER', $this->getAutoloaderFile(3));
		$this->content->set('FOUR_POWER_AUTOLOADER', $this->getAutoloaderFile(4));

		// to add to custom files
		$this->content->add('POWER_AUTOLOADER', $this->getAutoloaderFile(0));
		$this->content->add('SITE_POWER_AUTOLOADER', $this->getAutoloaderFile(0, 'JPATH_SITE'));

		// to add to install file
		$this->content->add('INSTALLER_POWER_AUTOLOADER_ARRAY', $this->getAutoloaderInstallArray());
	}

	/**
	 * Set the autoloader into the active content array
	 *
	 * @return void
	 * @since 3.2.0
	 */
	public function set()
	{
		// make sure we only load this once
		if (ArrayHelper::check($this->power->namespace) && !$this->content->isString('ADMIN_POWER_HELPER'))
		{
			/* ********************** IMPORTANT SORT NOTICE *****************************************
			 *   make sure the name space values are sorted from the longest string to the shortest
			 *   so that the search do not mistakenly match a shorter namespace before a longer one
			 *   that has the same short namespace for example:
			 *        NameSpace\SubName\Sub <- will always match first
			 *        NameSpace\SubName\SubSubName
			 *   Should the shorter namespace be listed [first] it will match both of these:
			 *        NameSpace\SubName\Sub\ClassName
			 *        ^^^^^^^^^^^^^^^^^^^^^^
			 *        NameSpace\SubName\SubSubName\ClassName
			 *        ^^^^^^^^^^^^^^^^^^^^^^
			 ** *********************************************************************************************/

			uksort($this->power->namespace, fn($a, $b) => strlen((string) $b) - strlen((string) $a));

			// load to admin helper class
			$this->content->add('ADMIN_POWER_HELPER', $this->getAutoloaderCode());

			// load to site helper class if needed
			$this->content->add('SITE_POWER_HELPER', $this->getAutoloaderCode());

			// load to installer helper class if needed
			$this->content->add('INSTALLER_POWER_HELPER', $this->getAutoloaderInstallerCode());
		}
	}

	/**
	 * Get autoloader code
	 *
	 * @return string
	 * @since 3.2.0
	 */
	private function getAutoloaderCode(): string
	{
		// check if it was already build
		if (!empty($this->helper))
		{
			return $this->helper;
		}

		// load the code
		$code = [];

		// add the composer stuff here
		if (($script = $this->getComposer()) !== null)
		{
			$code[] = $script;
		}

		// get the helper autoloader
		if (($script = $this->getAutoloader()) !== null)
		{
			$code[] = $script;
		}

		// if we have any
		if (!empty($code))
		{
			$this->helper = PHP_EOL . PHP_EOL . implode(PHP_EOL . PHP_EOL, $code);
		}

		return $this->helper;
	}

	/**
	 * Get autoloader file
	 *
	 * @param int     $tabSpace   The dynamic tab spacer
	 * @param string  $area       The target area
	 *
	 * @return string|null
	 * @since 3.2.1
	 */
	private function getAutoloaderFile(int $tabSpace, string $area = 'JPATH_ADMINISTRATOR'): ?string
	{
		// we start building the autoloader file loader
		$autoload_file = [];
		$autoload_file[] = Indent::_($tabSpace) . '//'
			. Line::_(__Line__, __Class__) . " The power autoloader for this project ($area) area.";
		$autoload_file[] = Indent::_($tabSpace) . "\$power_autoloader = $area . '/components/com_"
			. $this->config->get('component_code_name', 'ERROR') . '/'
			. $this->config->get('component_autoloader_path', 'ERROR') . "';";
		$autoload_file[] = Indent::_($tabSpace) . 'if (file_exists($power_autoloader))';
		$autoload_file[] = Indent::_($tabSpace) . '{';
		$autoload_file[] = Indent::_($tabSpace) . Indent::_(1) . 'require_once $power_autoloader;';
		$autoload_file[] = Indent::_($tabSpace) . '}';

		return implode(PHP_EOL, $autoload_file);
	}

	/**
	 * Get autoloader code
	 *
	 * @return string|null
	 * @since 3.2.0
	 */
	private function getAutoloader(): ?string
	{
		if (($size = ArrayHelper::check($this->power->namespace)) > 0)
		{
			// we start building the spl_autoload_register function call
			$autoload_method = [];
			$autoload_method[] = '//'
				. Line::_(__Line__, __Class__) . ' register additional namespace';
			$autoload_method[] = 'spl_autoload_register(function ($class) {';
			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' project-specific base directories and namespace prefix';
			$autoload_method[] = Indent::_(1) . '$search = [';

			// counter to manage the comma in the actual array
			$counter = 1;
			foreach ($this->power->namespace as $base_dir => $prefix)
			{
				// don't add the ending comma on last value
				if ($size == $counter)
				{
					$autoload_method[] = Indent::_(2) . "'" . $this->config->get('jcb_powers_path', 'libraries/jcb_powers') . "/$base_dir' => '" . implode('\\\\', $prefix) . "'";
				}
				else
				{
					$autoload_method[] = Indent::_(2) . "'" . $this->config->get('jcb_powers_path', 'libraries/jcb_powers') . "/$base_dir' => '" . implode('\\\\', $prefix) . "',";
				}
				$counter++;
			}
			$autoload_method[] = Indent::_(1) . '];';
			$autoload_method[] = Indent::_(1) . '//' . Line::_(__LINE__, __CLASS__) . ' Start the search and load if found';
			$autoload_method[] = Indent::_(1) . '$found = false;';
			$autoload_method[] = Indent::_(1) . '$found_base_dir = "";';
			$autoload_method[] = Indent::_(1) . '$found_len = 0;';
			$autoload_method[] = Indent::_(1) . 'foreach ($search as $base_dir => $prefix)';
			$autoload_method[] = Indent::_(1) . '{';
			$autoload_method[] = Indent::_(2) . '//'
				. Line::_(__Line__, __Class__) . ' does the class use the namespace prefix?';
			$autoload_method[] = Indent::_(2) . '$len = strlen($prefix);';
			$autoload_method[] = Indent::_(2) . 'if (strncmp($prefix, $class, $len) === 0)';
			$autoload_method[] = Indent::_(2) . '{';
			$autoload_method[] = Indent::_(3) . '//'
				. Line::_(__Line__, __Class__) . ' we have a match so load the values';
			$autoload_method[] = Indent::_(3) . '$found = true;';
			$autoload_method[] = Indent::_(3) . '$found_base_dir = $base_dir;';
			$autoload_method[] = Indent::_(3) . '$found_len = $len;';
			$autoload_method[] = Indent::_(3) . '//'
				. Line::_(__Line__, __Class__) . ' done here';
			$autoload_method[] = Indent::_(3) . 'break;';
			$autoload_method[] = Indent::_(2) . '}';
			$autoload_method[] = Indent::_(1) . '}';

			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' check if we found a match';
			$autoload_method[] = Indent::_(1) . 'if (!$found)';
			$autoload_method[] = Indent::_(1) . '{';

			$autoload_method[] = Indent::_(2) . '//'
				. Line::_(__Line__, __Class__) . ' not found so move to the next registered autoloader';
			$autoload_method[] = Indent::_(2) . 'return;';

			$autoload_method[] = Indent::_(1) . '}';

			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' get the relative class name';
			$autoload_method[] = Indent::_(1) . '$relative_class = substr($class, $found_len);';
			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' replace the namespace prefix with the base directory, replace namespace';
			$autoload_method[] = Indent::_(1) . '// separators with directory separators in the relative class name, append';
			$autoload_method[] = Indent::_(1) . '// with .php';
			$autoload_method[] = Indent::_(1) . "\$file = JPATH_ROOT . '/' . \$found_base_dir . '/src' . str_replace('\\\\', '/', \$relative_class) . '.php';";
			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' if the file exists, require it';
			$autoload_method[] = Indent::_(1) . 'if (file_exists($file))';
			$autoload_method[] = Indent::_(1) . '{';
			$autoload_method[] = Indent::_(2) . 'require $file;';
			$autoload_method[] = Indent::_(1) . '}';
			$autoload_method[] = '});';

			return implode(PHP_EOL, $autoload_method);
		}

		return null;
	}

	/**
	 * Get the composer autoloader routine
	 *
	 * @return string|null
	 * @since 3.2.0
	 */
	private function getComposer(): ?string
	{
		if (ArrayHelper::check($this->power->composer))
		{
			// load the composer routine
			$composer_routine = [];

			// counter to manage the comma in the actual array
			$add_once = [];
			foreach ($this->power->composer as $access_point)
			{
				// don't add the ending comma on last value
				if (empty($add_once[$access_point]))
				{
					$composer_routine[] = "\$composer_autoloader = JPATH_LIBRARIES . '/$access_point';";
					$composer_routine[] = 'if (file_exists($composer_autoloader))';
					$composer_routine[] = "{";
					$composer_routine[] = Indent::_(1) . 'require_once $composer_autoloader;';
					$composer_routine[] = "}";

					$add_once[$access_point] = true;
				}
			}

			// this is just about the [autoloader or autoloaders] in the comment ;)
			if (count($add_once) == 1)
			{
				array_unshift($composer_routine, '//'
					. Line::_(__Line__, __Class__) . ' add the autoloader for the composer classes');
			}
			else
			{
				array_unshift($composer_routine, '//'
					. Line::_(__Line__, __Class__) . ' add the autoloaders for the composer classes');
			}

			return implode(PHP_EOL, $composer_routine);
		}

		return null;
	}

	/**
	 * Get autoloaders for install file
	 *
	 * @return string
	 * @since 5.0.2
	 */
	private function getAutoloaderInstallArray(): string
	{
		// we start building the autoloader file loader
		$autoload_file = [];
		$autoload_file[] = PHP_EOL . Indent::_(3) . "__DIR__ . '/" .
			$this->config->get('component_installer_autoloader_path', 'ERROR') . "',";
		$autoload_file[] = Indent::_(3) . "JPATH_ADMINISTRATOR . '/components/com_"
			. $this->config->get('component_code_name', 'ERROR') . '/'
			. $this->config->get('component_autoloader_path', 'ERROR') . "'" . PHP_EOL . Indent::_(2);

		return implode(PHP_EOL, $autoload_file);
	}

	/**
	 * Get installer autoloader code
	 *
	 * @return string
	 * @since  5.0.2
	 */
	private function getAutoloaderInstallerCode(): string
	{
		// check if it was already build
		if (!empty($this->installerhelper))
		{
			return $this->installerhelper;
		}

		// load the code
		$code = [];

		// add the composer stuff here
		// if (($script = $this->getInstallerComposer()) !== null)
		// {
			// $code[] = $script;
		// }

		// get the helper autoloader
		if (($script = $this->getInstallerAutoloader()) !== null)
		{
			$code[] = $script;
		}

		// if we have any
		if (!empty($code))
		{
			$this->installerhelper = PHP_EOL . PHP_EOL . implode(PHP_EOL . PHP_EOL, $code);
		}

		return $this->installerhelper;
	}

	/**
	 * Get autoloader code
	 *
	 *
	 * @return string|null
	 * @since 3.2.0
	 */
	private function getInstallerAutoloader(): ?string
	{
		if (($size = ArrayHelper::check($this->power->namespace)) > 0)
		{
			// we start building the spl_autoload_register function call
			$autoload_method = [];
			$autoload_method[] = '//'
				. Line::_(__Line__, __Class__) . ' register additional namespace';
			$autoload_method[] = 'spl_autoload_register(function ($class) {';
			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' project-specific base directories and namespace prefix';
			$autoload_method[] = Indent::_(1) . '$search = [';

			// counter to manage the comma in the actual array
			$counter = 1;
			foreach ($this->power->namespace as $base_dir => $prefix)
			{
				// don't add the ending comma on last value
				if ($size == $counter)
				{
					$autoload_method[] = Indent::_(2) . "'" . $this->config->get('jcb_powers_path', 'libraries/jcb_powers') . "/$base_dir' => '" . implode('\\\\', $prefix) . "'";
				}
				else
				{
					$autoload_method[] = Indent::_(2) . "'" . $this->config->get('jcb_powers_path', 'libraries/jcb_powers') . "/$base_dir' => '" . implode('\\\\', $prefix) . "',";
				}
				$counter++;
			}
			$autoload_method[] = Indent::_(1) . '];';
			$autoload_method[] = Indent::_(1) . '//' . Line::_(__LINE__, __CLASS__) . ' Start the search and load if found';
			$autoload_method[] = Indent::_(1) . '$found = false;';
			$autoload_method[] = Indent::_(1) . '$found_base_dir = "";';
			$autoload_method[] = Indent::_(1) . '$found_len = 0;';
			$autoload_method[] = Indent::_(1) . 'foreach ($search as $base_dir => $prefix)';
			$autoload_method[] = Indent::_(1) . '{';
			$autoload_method[] = Indent::_(2) . '//'
				. Line::_(__Line__, __Class__) . ' does the class use the namespace prefix?';
			$autoload_method[] = Indent::_(2) . '$len = strlen($prefix);';
			$autoload_method[] = Indent::_(2) . 'if (strncmp($prefix, $class, $len) === 0)';
			$autoload_method[] = Indent::_(2) . '{';
			$autoload_method[] = Indent::_(3) . '//'
				. Line::_(__Line__, __Class__) . ' we have a match so load the values';
			$autoload_method[] = Indent::_(3) . '$found = true;';
			$autoload_method[] = Indent::_(3) . '$found_base_dir = $base_dir;';
			$autoload_method[] = Indent::_(3) . '$found_len = $len;';
			$autoload_method[] = Indent::_(3) . '//'
				. Line::_(__Line__, __Class__) . ' done here';
			$autoload_method[] = Indent::_(3) . 'break;';
			$autoload_method[] = Indent::_(2) . '}';
			$autoload_method[] = Indent::_(1) . '}';

			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' check if we found a match';
			$autoload_method[] = Indent::_(1) . 'if (!$found)';
			$autoload_method[] = Indent::_(1) . '{';

			$autoload_method[] = Indent::_(2) . '//'
				. Line::_(__Line__, __Class__) . ' not found so move to the next registered autoloader';
			$autoload_method[] = Indent::_(2) . 'return;';

			$autoload_method[] = Indent::_(1) . '}';

			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' get the relative class name';
			$autoload_method[] = Indent::_(1) . '$relative_class = substr($class, $found_len);';
			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' replace the namespace prefix with the base directory, replace namespace';
			$autoload_method[] = Indent::_(1) . '// separators with directory separators in the relative class name, append';
			$autoload_method[] = Indent::_(1) . '// with .php';
			$autoload_method[] = Indent::_(1) . "\$file = __DIR__ . '/' . \$found_base_dir . '/src' . str_replace('\\\\', '/', \$relative_class) . '.php';";
			$autoload_method[] = Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' if the file exists, require it';
			$autoload_method[] = Indent::_(1) . 'if (file_exists($file))';
			$autoload_method[] = Indent::_(1) . '{';
			$autoload_method[] = Indent::_(2) . 'require $file;';
			$autoload_method[] = Indent::_(1) . '}';
			$autoload_method[] = '});';

			return implode(PHP_EOL, $autoload_method);
		}

		return null;
	}

	/**
	 * Get the composer autoloader routine (NOT READY)
	 *
	 *
	 * @return string|null
	 * @since 3.2.0
	 */
	private function getInstallerComposer(): ?string
	{
		if (ArrayHelper::check($this->power->composer))
		{
			// load the composer routine
			$composer_routine = [];

			// counter to manage the comma in the actual array
			$add_once = [];
			foreach ($this->power->composer as $access_point)
			{
				// don't add the ending comma on last value
				if (empty($add_once[$access_point]))
				{
					$composer_routine[] = "\$composer_autoloader = __DIR__ . '/libraries/$access_point';";
					$composer_routine[] = 'if (file_exists($composer_autoloader))';
					$composer_routine[] = "{";
					$composer_routine[] = Indent::_(1) . 'require_once $composer_autoloader;';
					$composer_routine[] = "}";

					$add_once[$access_point] = true;
				}
			}

			// this is just about the [autoloader or autoloaders] in the comment ;)
			if (count($add_once) == 1)
			{
				array_unshift($composer_routine, '//'
					. Line::_(__Line__, __Class__) . ' add the autoloader for the composer classes');
			}
			else
			{
				array_unshift($composer_routine, '//'
					. Line::_(__Line__, __Class__) . ' add the autoloaders for the composer classes');
			}

			return implode(PHP_EOL, $composer_routine);
		}

		return null;
	}
}

