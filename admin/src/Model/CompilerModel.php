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
namespace VDM\Component\Componentbuilder\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Componentbuilder\Compiler\Factory as CompilerFactory;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\Database\DatabaseInterface;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder List Model for Compiler
 *
 * @since  1.6
 */
class CompilerModel extends ListModel
{
	/**
	 * Represents the current user object.
	 *
	 * @var   User  The user object representing the current user.
	 * @since 3.2.0
	 */
	protected User $user;

	/**
	 * The unique identifier of the current user.
	 *
	 * @var   int|null  The ID of the current user.
	 * @since 3.2.0
	 */
	protected ?int $userId;

	/**
	 * Flag indicating whether the current user is a guest.
	 *
	 * @var   int  1 if the user is a guest, 0 otherwise.
	 * @since 3.2.0
	 */
	protected int $guest;

	/**
	 * An array of groups that the current user belongs to.
	 *
	 * @var   array|null  An array of user group IDs.
	 * @since 3.2.0
	 */
	protected ?array $groups;

	/**
	 * An array of view access levels for the current user.
	 *
	 * @var   array|null  An array of access level IDs.
	 * @since 3.2.0
	 */
	protected ?array $levels;

	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

	/**
	 * The input object, providing access to the request data.
	 *
	 * @var   Input  The input object.
	 * @since 3.2.0
	 */
	protected Input $input;

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'administrator/components/com_componentbuilder/assets/css/admin.css',
		'administrator/components/com_componentbuilder/assets/css/compiler.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'administrator/components/com_componentbuilder/assets/js/admin.js'
 	];

	/**
	 * A custom property for UI Kit components.
	 *
	 * @var   mixed  Property for storing UI Kit component-related data or objects.
	 * @since 3.2.0
	 */
	protected $uikitComp = [];

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$this->app ??= Factory::getApplication();
		$this->input ??= $this->app->getInput();

		// Set the current user for authorisation checks (for those calling this model directly)
		$this->user ??= $this->getCurrentUser();
		$this->userId = $this->user->id;
		$this->guest = $this->user->guest;
		$this->groups = $this->user->groups;
		$this->authorisedGroups = $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();

		// will be removed
		$this->initSet = true;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string  An SQL query
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Make sure all records load, since no pagination allowed.
		$this->setState('list.limit', 0);
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__componentbuilder_joomla_component as a
		$query->select($db->quoteName(
			array('a.id','a.system_name','a.name','a.name_code','a.component_version','a.debug_linenr','a.short_description','a.image','a.companyname','a.author','a.email','a.website','a.copyright','a.modified','a.created','a.version'),
			array('id','system_name','name','name_code','component_version','debug_linenr','short_description','image','companyname','author','email','website','copyright','modified','created','version')));
		$query->from($db->quoteName('#__componentbuilder_joomla_component', 'a'));
		// Get where a.published is 1
		$query->where('a.published = 1');
		$query->order('a.modified DESC');
		$query->order('a.created DESC');

		// return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6
	 */
	public function getItems()
	{
		$user = $this->user;
		// check if this user has permission to access items
		if (!$user->authorise('compiler.access', 'com_componentbuilder'))
		{
			$this->app->enqueueMessage(Text::_('Not authorised!'), 'error');
			// redirect away if not a correct to default view
			$this->app->redirect('index.php?option=com_componentbuilder');
			return false;
		}
		// load parent items
		$items = parent::getItems();

		// Get the global params
		$globalParams = ComponentHelper::getParams('com_componentbuilder', true);

		// Insure all item fields are adapted where needed.
		if (UtilitiesArrayHelper::check($items))
		{
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
			foreach ($items as $nr => &$item)
			{
				// Always create a slug for sef URL's
				$item->slug = ($item->id ?? '0') . (isset($item->alias) ? ':' . $item->alias : '');
				// Check if item has params, or pass whole item.
				$params = (isset($item->params) && JsonHelper::check($item->params)) ? json_decode($item->params) : $item;
				// Make sure the content prepare plugins fire on copyright
				$_copyright = new \stdClass();
				$_copyright->text =& $item->copyright; // value must be in text
				// Since all values are now in text (Joomla Limitation), we also add the field name (copyright) to context
				// onContentPrepare Event Trigger
				$this->getDispatcher()->dispatch('onContentPrepare',
					new ContentPrepareEvent(
						'onContentPrepare',
						[
							'context' => 'com_componentbuilder.compiler.copyright',
							'subject' => $_copyright,
							'params' => $params,
							'page' => 0
						]
					)
				);
			}
		}

		// return items
		return $items;
	}

	/**
	 * Method to get the styles that have to be included on the view
	 *
	 * @return  array    styles files
	 * @since   4.3
	 */
	public function getStyles(): array
	{
		return $this->styles;
	}

	/**
	 * Method to set the styles that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setStyles(string $path): void
	{
		$this->styles[] = $path;
	}

	/**
	 * Method to get the script that have to be included on the view
	 *
	 * @return  array    script files
	 * @since   4.3
	 */
	public function getScripts(): array
	{
		return $this->scripts;
	}

	/**
	 * Method to set the script that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setScript(string $path): void
	{
		$this->scripts[] = $path;
	}

	/**
	 * Initialize the compiler and return whether it was created successfully.
	 *
	 * @return bool  True if compiler was created, false otherwise.
	 * @since  5.1.4
	 */
	public function compile(): bool
	{
		return CompilerFactory::_('Compiler')->run();
	}

	/**
	 * Empty a folder recursively, optionally removing the folder itself.
	 *
	 * @param  string  $dir        The directory path to empty.
	 * @param  bool    $removeDir  Whether to remove the directory itself after emptying.
	 *
	 * @return bool  True on success, false on failure.
	 * @since  3.10
	 */
	public function emptyFolder(string $dir, bool $removeDir = false): bool
	{
		if (!is_dir($dir))
		{
			return false;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $file)
		{
			$basename = $file->getBasename();

			// Skip index.html for security reasons
			if ($basename === 'index.html')
			{
				continue;
			}

			if ($file->isDir())
			{
				Folder::delete($file->getPathname());
			}
			else
			{
				File::delete($file->getPathname());
			}
		}

		// Optionally remove the directory itself
		return $removeDir
			? Folder::delete($dir)
			: true;
	}

	/**
	 * Install a JCB package from the tmp folder.
	 *
	 * @param  string  $p_file  The package file name located in Joomla's tmp folder.
	 *
	 * @return bool  True on success, false on failure.
	 * @since  3.10
	 */
	public function install(string $p_file): bool
	{
		$this->setState('action', 'install');

		$config = $this->app->getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Unpack the downloaded package file.
		$package = InstallerHelper::unpack($tmp_dest . '/' . $p_file, true);

		if (!$package || empty($package['type']))
		{
			$this->app->enqueueMessage(Text::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'), 'error');
			return false;
		}

		// Get an installer instance and install the package.
		$installer = new Installer();
		$installer->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));

		if (!$installer->install($package['dir']))
		{
			$msg     = Text::sprintf('COM_INSTALLER_INSTALL_ERROR', Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result  = false;
			$msgType = 'error';
		}
		else
		{
			$msg     = Text::sprintf('COM_INSTALLER_INSTALL_SUCCESS', Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result  = true;
			$msgType = 'message';
		}

		// Output result to user
		$this->app->enqueueMessage($msg, $msgType);
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$this->app->setUserState('com_componentbuilder.message', $installer->message);
		$this->app->setUserState('com_componentbuilder.extension_message', $installer->get('extension_message'));
		$this->app->setUserState('com_componentbuilder.redirect_url', $installer->get('redirect_url'));

		// Cleanup the install files.
		if (!is_file($package['packagefile']))
		{
			$package['packagefile'] = $tmp_dest . '/' . $package['packagefile'];
		}

		InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		// Clear relevant caches
		foreach (['_system', 'com_modules', 'com_plugins', 'mod_menu'] as $cacheGroup)
		{
			$this->cleanCache($cacheGroup);
		}

		return $result;
	}

	/**
	 * Get all components in the system
	 *
	 * @return  array|null
	 * @since   3.2.0
	 **/
	public function getComponents(): ?array
	{
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select only id and system name
		$query->select($db->quoteName(['id', 'system_name'],['id', 'name']));
		$query->from($db->quoteName('#__componentbuilder_joomla_component'));

		// only the active components
		$query->where($db->quoteName('published') . ' = 1');

		// Order it by the ordering field.
		$query->order('modified DESC');
		$query->order('created DESC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		// return the result
		return $db->loadObjectList() ?? null;
	}

	/**
	 * Get all dynamic content
	 *
	 * @return  bool
	 * @since   3.2.0
	 **/
	public function getDynamicContent(&$errorMessage): bool
	{
		// convert error message to array
		$errorMessage = [];
		$searchArray = [
			// add banners (width - height)
			'banner' => [
					'728-90',
					'160-600'
				],
			// The build-gif by size (width - height)
			'builder-gif' => [
					'480-540'
				]
			];
		// start search, and get
		foreach ($searchArray as $type => $sizes)
		{
			// per size
			foreach ($sizes as $size)
			{
				// get size
				if (($set_size = ComponentbuilderHelper::getDynamicContentSize($type, $size)) !== 0)
				{
					// we loop over all type size artwork
					for ($target = 1; $target <= $set_size; $target++)
					{
    						if (!ComponentbuilderHelper::getDynamicContent($type, $size, false, 0, $target))
    						{
    							$errorMessage[] = Text::sprintf('COM_COMPONENTBUILDER_S_S_NUMBER_BSB_COULD_NOT_BE_DOWNLOADED_SUCCESSFULLY_TO_THIS_JOOMLA_INSTALL', $type, $size, $target);
    						}
					}
				}
			}
		}
		// check if we had any errors
		if (UtilitiesArrayHelper::check($errorMessage))
		{
			// flatten the error message array
			$errorMessage = implode('<br />', $errorMessage);

			return false;
		}
		return true;
	}
}
