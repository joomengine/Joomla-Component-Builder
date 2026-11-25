<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Version;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Database\DatabaseInterface;

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Script File of ###Component### Component
 *
 * @since  3.6
 */
class Com_###Component###InstallerScript implements InstallerScriptInterface
{
	/**
	 * The CMS Application.
	 *
	 * @since 4.4.2
	 */
	protected $app;

	/**
	 * The database class.
	 *
	 * @since 4.4.2
	 */
	protected $db;

	/**
	 * The version number of the extension.
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $release;

	/**
	 * The table the parameters are stored in.
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $paramTable;

	/**
	 * The extension name. This should be set in the installer script.
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $extension;

	/**
	 * A list of files to be deleted
	 *
	 * @var    array
	 * @since  3.6
	 */
	protected $deleteFiles = [];

	/**
	 * A list of folders to be deleted
	 *
	 * @var    array
	 * @since  3.6
	 */
	protected $deleteFolders = [];

	/**
	 * A list of CLI script files to be copied to the cli directory
	 *
	 * @var    array
	 * @since  3.6
	 */
	protected $cliScriptFiles = [];

	/**
	 * Minimum PHP version required to install the extension
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $minimumPhp;

	/**
	 * Minimum Joomla! version required to install the extension
	 *
	 * @var    string
	 * @since  3.6
	 */
	protected $minimumJoomla;

	/**
	 * Extension script constructor.
	 *
	 * @since   3.0.0
	 */
	public function __construct()
	{
		$this->minimumJoomla = '6.0.0';
		$this->minimumPhp = '8.3.0';
		$this->app ??= Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$this->db = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->get(DatabaseInterface::class);

		// check if the files exist
		if (is_file(JPATH_ROOT . '/administrator/components/com_###component###/###component###.php'))
		{
			// remove Joomla 3 files
			$this->deleteFiles = [
				'/administrator/components/com_###component###/###component###.php',
				'/administrator/components/com_###component###/controller.php',
				'/administrator/components/com_###component###/script.php',
				'/components/com_###component###/###component###.php',
				'/components/com_###component###/controller.php',
				'/components/com_###component###/router.php',
			];
		}

		// check if the Folders exist
		if (is_dir(JPATH_ROOT . '/administrator/components/com_###component###/modules'))
		{
			// remove Joomla 3 folder
			$this->deleteFolders = [
				'/administrator/components/com_###component###/controllers',
				'/administrator/components/com_###component###/helpers',
				'/administrator/components/com_###component###/modules',
				'/administrator/components/com_###component###/tables',
				'/administrator/components/com_###component###/views',
				'/components/com_###component###/controllers',
				'/components/com_###component###/helpers',
				'/components/com_###component###/modules',
				'/components/com_###component###/views',
			];
		}
	}

	/**
	 * Function called after the extension is installed.
	 *
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 * @since   4.2.0
	 */
	public function install(InstallerAdapter $adapter): bool {return true;}

	/**
	 * Function called after the extension is updated.
	 *
	 * @param   InstallerAdapter   $adapter   The adapter calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   4.2.0
	 */
	public function update(InstallerAdapter $adapter): bool {return true;}

	/**
	 * Function called after the extension is uninstalled.
	 *
	 * @param   InstallerAdapter   $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 * @since   4.2.0
	 */
	public function uninstall(InstallerAdapter $adapter): bool
	{###UNINSTALLSCRIPT###
		// little notice as after service, in case of bad experience with component.
		echo '<div style="background-color: #fff;" class="alert alert-info">
		<h2>Did something go wrong? Are you disappointed?</h2>
		<p>Please let me know at <a href="mailto:###AUTHOREMAIL###">###AUTHOREMAIL###</a>.
		<br />We at ###COMPANYNAME### are committed to building extensions that performs proficiently! You can help us, really!
		<br />Send me your thoughts on improvements that is needed, trust me, I will be very grateful!
		<br />Visit us at <a href="###AUTHORWEBSITE###" target="_blank">###AUTHORWEBSITE###</a> today!</p></div>';

		return true;
	}

	/**
	 * Function called before extension installation/update/removal procedure commences.
	 *
	 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 * @since   4.2.0
	 */
	public function preflight(string $type, InstallerAdapter $adapter): bool
	{
		// Check for the minimum PHP version before continuing
		if (!empty($this->minimumPhp) && version_compare(PHP_VERSION, $this->minimumPhp, '<'))
		{
			Log::add(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPhp), Log::WARNING, 'jerror');

			return false;
		}

		// Check for the minimum Joomla version before continuing
		if (!empty($this->minimumJoomla) && version_compare(JVERSION, $this->minimumJoomla, '<'))
		{
			Log::add(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomla), Log::WARNING, 'jerror');

			return false;
		}

		// Extension manifest file version
		$this->extension = $adapter->getName();
		$this->release   = $adapter->getManifest()->version;

		// do any updates needed
		if ($type === 'update')
		{###PREUPDATESCRIPT###
		}

		// do any install needed
		if ($type === 'install')
		{###PREINSTALLSCRIPT###
		}

		return true;
	}

	/**
	 * Function called after extension installation/update/removal procedure commences.
	 *
	 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
	 * @param   InstallerAdapter  $adapter  The adapter calling this method
	 *
	 * @return  boolean  True on success
	 * @since   4.2.0
	 */
	public function postflight(string $type, InstallerAdapter $adapter): bool
	{###MOVEFOLDERSSCRIPT###

		// set the default component settings
		if ($type === 'install')
		{###POSTINSTALLSCRIPT###
		}

		// do any updates needed
		if ($type === 'update')
		{###POSTUPDATESCRIPT###
		}

		// move CLI files
		$this->moveCliFiles();

		// remove old files and folders
		$this->removeFiles();

		return true;
	}

	/**
	 * Remove folders with files (with ignore options)
	 *
	 * @param   string	    $dir	 The path to the folder to remove.
	 * @param   array|null  $ignore  The folders and files to ignore and not remove.
	 *
	 * @return  bool   True if all specified files/folders are removed, false otherwise.
	 * @since   3.2.2
	 */
	protected function removeFolder(string $dir, ?array $ignore = null): bool
	{
		if (!is_dir($dir))
		{
			return false;
		}

		$it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
		$it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

		// Remove trailing slash
		$dir = rtrim($dir, '/');

		foreach ($it as $file)
		{
			$filePath = $file->getPathname();
			$relativePath = str_replace($dir . '/', '', $filePath);

			if ($ignore !== null && in_array($relativePath, $ignore, true))
			{
				continue;
			}

			if ($file->isDir())
			{
				Folder::delete($filePath);
			}
			else
			{
				File::delete($filePath);
			}
		}

		// Delete the root folder if there are no ignored files/folders left
		if ($ignore === null || $this->isDirEmpty($dir, $ignore))
		{
			return Folder::delete($dir);
		}

		return true;
	}

	/**
	 * Check if a directory is empty considering ignored files/folders.
	 *
	 * @param   string  $dir	 The path to the folder to check.
	 * @param   array   $ignore  The folders and files to ignore.
	 *
	 * @return  bool    True if the directory is empty or contains only ignored items, false otherwise.
     * @since   3.2.1
	 */
	protected function isDirEmpty(string $dir, array $ignore): bool
	{
		$it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
		foreach ($it as $file)
		{
			$relativePath = str_replace($dir . '/', '', $file->getPathname());
			if (!in_array($relativePath, $ignore, true))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Remove the files and folders in the given array from
	 *
	 * @return  void
	 * @since   3.6
	 */
	protected function removeFiles()
	{
		if (!empty($this->deleteFiles))
		{
			foreach ($this->deleteFiles as $file)
			{
				if (is_file(JPATH_ROOT . $file) && !File::delete(JPATH_ROOT . $file))
				{
					echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $file) . '<br>';
				}
			}
		}

		if (!empty($this->deleteFolders))
		{
			foreach ($this->deleteFolders as $folder)
			{
				if (is_dir(JPATH_ROOT . $folder) && !Folder::delete(JPATH_ROOT . $folder))
				{
					echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('JLIB_INSTALLER_ERROR_FILE_FOLDER', $folder) . '<br>';
				}
			}
		}
	}

	/**
	 * Moves the CLI scripts into the CLI folder in the CMS
	 *
	 * @return  void
	 * @since   3.6
	 */
	protected function moveCliFiles()
	{
		if (!empty($this->cliScriptFiles))
		{
			foreach ($this->cliScriptFiles as $file)
			{
				$name = basename($file);

				if (file_exists(JPATH_ROOT . $file) && !File::move(JPATH_ROOT . $file, JPATH_ROOT . '/cli/' . $name))
				{
					echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('JLIB_INSTALLER_FILE_ERROR_MOVE', $name);
				}
			}
		}
	}

	/**
	 * Set content type integration
	 *
	 * @param   string   $typeTitle
	 * @param   string   $typeAlias
	 * @param   string   $table
	 * @param   string   $rules
	 * @param   string   $fieldMappings
	 * @param   string   $router
	 * @param   string   $contentHistoryOptions
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function setContentType(
		string $typeTitle,
		string $typeAlias,
		string $table,
		string $rules,
		string $fieldMappings,
		string $router,
		string $contentHistoryOptions): void
	{
		// Create the content type object.
		$content = new stdClass();
		$content->type_title = $typeTitle;
		$content->type_alias = $typeAlias;
		$content->table = $table;
		$content->rules = $rules;
		$content->field_mappings = $fieldMappings;
		$content->router = $router;
		$content->content_history_options = $contentHistoryOptions;

		// Check if content type is already in content_type DB.
		$query = $this->db->createQuery();
		$query->select($this->db->quoteName(array('type_id')));
		$query->from($this->db->quoteName('#__content_types'));
		$query->where($this->db->quoteName('type_alias') . ' LIKE '. $this->db->quote($content->type_alias));

		$this->db->setQuery($query);
		$this->db->execute();

		// Check if the type alias is already in the content types table.
		if ($this->db->getNumRows())
		{
			$content->type_id = $this->db->loadResult();
			if ($this->db->updateObject('#__content_types', $content, 'type_id'))
			{
				// If its successfully update.
				$this->app->enqueueMessage(
					Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The (%s) was found in the <b>#__content_types</b> table, and updated.', $content->type_alias)
				);
			}
		}
		elseif ($this->db->insertObject('#__content_types', $content))
		{
			// If its successfully added.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The (%s) was added to the <b>#__content_types</b> table.', $content->type_alias)
			);
		}
	}

	/**
	 * Set action log config integration
	 *
	 * @param   string   $typeTitle
	 * @param   string   $typeAlias
	 * @param   string   $idHolder
	 * @param   string   $titleHolder
	 * @param   string   $tableName
	 * @param   string   $textPrefix
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function setActionLogConfig(
		string $typeTitle,
		string $typeAlias,
		string $idHolder,
		string $titleHolder,
		string $tableName,
		string $textPrefix): void
	{
		// Create the content action log config object.
		$content = new stdClass();
		$content->type_title = $typeTitle;
		$content->type_alias = $typeAlias;
		$content->id_holder = $idHolder;
		$content->title_holder = $titleHolder;
		$content->table_name = $tableName;
		$content->text_prefix = $textPrefix;

		// Check if the action log config is already in action_log_config DB.
		$query = $this->db->createQuery();
		$query->select($this->db->quoteName(['id']));
		$query->from($this->db->quoteName('#__action_log_config'));
		$query->where($this->db->quoteName('type_alias') . ' LIKE '. $this->db->quote($content->type_alias));

		$this->db->setQuery($query);
		$this->db->execute();

		// Check if the type alias is already in the action log config table.
		if ($this->db->getNumRows())
		{
			$content->id = $this->db->loadResult();
			if ($this->db->updateObject('#__action_log_config', $content, 'id'))
			{
				// If its successfully update.
				$this->app->enqueueMessage(
					Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The (%s) was found in the <b>#__action_log_config</b> table, and updated.', $content->type_alias)
				);
			}
		}
		elseif ($this->db->insertObject('#__action_log_config', $content))
		{
			// If its successfully added.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The (%s) was added to the <b>#__action_log_config</b> table.', $content->type_alias)
			);
		}
	}

	/**
	 * Set action logs extensions integration
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function setActionLogsExtensions(): void
	{
		// Create the extension action logs object.
		$data = new stdClass();
		$data->extension = 'com_###component###';

		// Check if ###component### action log extension is already in action logs extensions DB.
		$query = $this->db->createQuery();
		$query->select($this->db->quoteName(['id']));
		$query->from($this->db->quoteName('#__action_logs_extensions'));
		$query->where($this->db->quoteName('extension') . ' = '. $this->db->quote($data->extension));

		$this->db->setQuery($query);
		$this->db->execute();

		// Set the object into the action logs extensions table if not found.
		if ($this->db->getNumRows())
		{
			// If its already set don't set it again.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('The (com_###component###) is already in the <b>#__action_logs_extensions</b> table.')
			);
		}
		elseif ($this->db->insertObject('#__action_logs_extensions', $data))
		{
			// give a success message
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('The (com_###component###) was successfully added to the <b>#__action_logs_extensions</b> table.')
			);
		}
	}

	/**
	 * Set global extension assets permission of this component
	 *   (on install only)
	 *
	 * @param   string   $rules   The component rules
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function setAssetsRules(string $rules): void
	{
		// Condition.
		$conditions = [
			$this->db->quoteName('name') . ' = ' . $this->db->quote('com_###component###')
		];

		// Field to update.
		$fields = [
			$this->db->quoteName('rules') . ' = ' . $this->db->quote($rules),
		];

		$query = $this->db->createQuery();
		$query->update(
			$this->db->quoteName('#__assets')
		)->set($fields)->where($conditions);

		$this->db->setQuery($query);

		$done = $this->db->execute();
		if ($done)
		{
			// give a success message
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('The (com_###component###) rules was successfully added to the <b>#__assets</b> table.')
			);
		}
	}

	/**
	 * Set global extension params of this component
	 *   (on install only)
	 *
	 * @param   string   $params   The component rules
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function setExtensionsParams(string $params): void
	{
		// Condition.
		$conditions = [
			$this->db->quoteName('element') . ' = ' . $this->db->quote('com_###component###')
		];

		// Field to update.
		$fields = [
			$this->db->quoteName('params') . ' = ' . $this->db->quote($params),
		];

		$query = $this->db->createQuery();
		$query->update(
			$this->db->quoteName('#__extensions')
		)->set($fields)->where($conditions);

		$this->db->setQuery($query);

		$done = $this->db->execute();
		if ($done)
		{
			// give a success message
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('The (com_###component###) params was successfully added to the <b>#__extensions</b> table.')
			);
		}
	}

	/**
	 * Set database fix (if needed)
	 *  => WHY DO WE NEED AN ASSET TABLE FIX?
	 *   https://git.vdm.dev/joomla/Component-Builder/issues/616#issuecomment-12085
	 *   https://www.mysqltutorial.org/mysql-varchar/
	 *   https://stackoverflow.com/a/15227917/1429677
	 *   https://forums.mysql.com/read.php?24,105964,105964
	 *
	 * @param   int     $accessWorseCase   This is the max rules column size com_###component### would needs.
	 * @param   string  $dataType          This datatype we will change the rules column to if it to small.
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function setDatabaseAssetsRulesFix(int $accessWorseCase, string $dataType): void
	{
		// Get the biggest rule column in the assets table at this point.
		$length = "SELECT CHAR_LENGTH(`rules`) as rule_size FROM #__assets ORDER BY rule_size DESC LIMIT 1";
		$this->db->setQuery($length);
		if ($this->db->execute())
		{
			$rule_length = $this->db->loadResult();
			// Check the size of the rules column
			if ($rule_length <= $accessWorseCase)
			{
				// Fix the assets table rules column size
				$fix = "ALTER TABLE `#__assets` CHANGE `rules` `rules` {$dataType} NOT NULL COMMENT 'JSON encoded access control. Enlarged to {$dataType} by ###Component###';";
				$this->db->setQuery($fix);

				$done = $this->db->execute();
				if ($done)
				{
					$this->app->enqueueMessage(
						Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The <b>#__assets</b> table rules column was resized to the %s datatype for the components possible large permission rules.', $dataType)
					);
				}
			}
		}
	}

	/**
	 * Remove remnant data related to this view
	 *
	 * @param   string   $context   The view context
	 * @param   bool     $fields    The switch to also remove related field data
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeViewData(string $context, bool $fields = false): void
	{
		$this->removeContentTypes($context);
		$this->removeViewHistory($context);
		$this->removeUcmContent($context); // this might be obsolete...
		$this->removeContentItemTagMap($context);
		$this->removeActionLogConfig($context);

		if ($fields)
		{
			$this->removeFields($context);
			$this->removeFieldsGroups($context);
		}
	}

	/**
	 * Remove content types related to this view
	 *
	 * @param   string   $context   The view context
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeContentTypes(string $context): void
	{
		// Create a new query object.
		$query = $this->db->createQuery();

		// Select id from content type table
		$query->select($this->db->quoteName('type_id'));
		$query->from($this->db->quoteName('#__content_types'));

		// Where Item alias is found
		$query->where($this->db->quoteName('type_alias') . ' = '. $this->db->quote($context));
		$this->db->setQuery($query);

		// Execute query to see if alias is found
		$this->db->execute();
		$found = $this->db->getNumRows();

		// Now check if there were any rows
		if ($found)
		{
			// Since there are load the needed  item type ids
			$ids = $this->db->loadColumn();

			// Remove Item from the content type table
			$condition = [
				$this->db->quoteName('type_alias') . ' = '. $this->db->quote($context)
			];

			// Create a new query object.
			$query = $this->db->createQuery();
			$query->delete($this->db->quoteName('#__content_types'));
			$query->where($condition);
			$this->db->setQuery($query);

			// Execute the query to remove Item items
			$done = $this->db->execute();
			if ($done)
			{
				// If successfully remove Item add queued success message.
				$this->app->enqueueMessage(
					Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The (%s) type alias was removed from the <b>#__content_type</b> table.', $context)
				);
			}

			// Make sure that all the items are cleared from DB
			$this->removeUcmBase($ids);
		}
	}

	/**
	 * Remove fields related to this view
	 *
	 * @param   string   $context   The view context
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeFields(string $context): void
	{
		// Create a new query object.
		$query = $this->db->createQuery();

		// Select ids from fields
		$query->select($this->db->quoteName('id'));
		$query->from($this->db->quoteName('#__fields'));

		// Where context is found
		$query->where(
			$this->db->quoteName('context') . ' = '. $this->db->quote($context)
		);
		$this->db->setQuery($query);

		// Execute query to see if context is found
		$this->db->execute();
		$found = $this->db->getNumRows();

		// Now check if there were any rows
		if ($found)
		{
			// Since there are load the needed  release_check field ids
			$ids = $this->db->loadColumn();

			// Create a new query object.
			$query = $this->db->createQuery();

			// Remove context from the field table
			$condition = [
				$this->db->quoteName('context') . ' = '. $this->db->quote($context)
			];

			$query->delete($this->db->quoteName('#__fields'));
			$query->where($condition);

			$this->db->setQuery($query);

			// Execute the query to remove release_check items
			$done = $this->db->execute();
			if ($done)
			{
				// If successfully remove context add queued success message.
				$this->app->enqueueMessage(
					Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The fields with context (%s) was removed from the <b>#__fields</b> table.', $context)
				);
			}

			// Make sure that all the field values are cleared from DB
			$this->removeFieldsValues($context, $ids);
		}
	}

	/**
	 * Remove fields values related to fields
	 *
	 * @param   string   $context   The view context
	 * @param   array    $ids       The view context
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeFieldsValues(string $context, array $ids): void
	{
		$condition = [
			$this->db->quoteName('field_id') . ' IN ('. implode(',', $ids) .')'
		];

		// Create a new query object.
		$query = $this->db->createQuery();
		$query->delete($this->db->quoteName('#__fields_values'));
		$query->where($condition);
		$this->db->setQuery($query);

		// Execute the query to remove field values
		$done = $this->db->execute();
		if ($done)
		{
			// If successfully remove release_check add queued success message.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The fields values for (%s) was removed from the <b>#__fields_values</b> table.', $context)
			);
		}
	}

	/**
	 * Remove fields groups related to fields
	 *
	 * @param   string   $context   The view context
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeFieldsGroups(string $context): void
	{
		// Create a new query object.
		$query = $this->db->createQuery();

		// Select ids from fields
		$query->select($this->db->quoteName('id'));
		$query->from($this->db->quoteName('#__fields_groups'));

		// Where context is found
		$query->where(
			$this->db->quoteName('context') . ' = '. $this->db->quote($context)
		);
		$this->db->setQuery($query);

		// Execute query to see if context is found
		$this->db->execute();
		$found = $this->db->getNumRows();

		// Now check if there were any rows
		if ($found)
		{
			// Create a new query object.
			$query = $this->db->createQuery();

			// Remove context from the field table
			$condition = [
				$this->db->quoteName('context') . ' = '. $this->db->quote($context)
			];

			$query->delete($this->db->quoteName('#__fields_groups'));
			$query->where($condition);

			$this->db->setQuery($query);

			// Execute the query to remove release_check items
			$done = $this->db->execute();
			if ($done)
			{
				// If successfully remove context add queued success message.
				$this->app->enqueueMessage(
					Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The fields with context (%s) was removed from the <b>#__fields_groups</b> table.', $context)
				);
			}
		}
	}

	/**
	 * Remove history related to this view
	 *
	 * @param   string   $context   The view context
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeViewHistory(string $context): void
	{
		// Remove Item items from the ucm content table
		$condition = [
			$this->db->quoteName('item_id') . ' LIKE ' . $this->db->quote($context . '.%')
		];

		// Create a new query object.
		$query = $this->db->createQuery();
		$query->delete($this->db->quoteName('#__history'));
		$query->where($condition);
		$this->db->setQuery($query);

		// Execute the query to remove Item items
		$done = $this->db->execute();
		if ($done)
		{
			// If successfully removed Items add queued success message.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The (%s) items were removed from the <b>#__history</b> table.', $context)
			);
		}
	}

	/**
	 * Remove ucm base values related to these IDs
	 *
	 * @param   array   $ids   The type ids
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeUcmBase(array $ids): void
	{
		// Make sure that all the items are cleared from DB
		foreach ($ids as $type_id)
		{
			// Remove Item items from the ucm base table
			$condition = [
				$this->db->quoteName('ucm_type_id') . ' = ' . $type_id
			];

			// Create a new query object.
			$query = $this->db->createQuery();
			$query->delete($this->db->quoteName('#__ucm_base'));
			$query->where($condition);
			$this->db->setQuery($query);

			// Execute the query to remove Item items
			$this->db->execute();
		}

		$this->app->enqueueMessage(
			Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('All related items was removed from the <b>#__ucm_base</b> table.')
		);
	}

	/**
	 * Remove ucm content values related to this view
	 *
	 * @param   string   $context   The view context
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeUcmContent(string $context): void
	{
		// Remove Item items from the ucm content table
		$condition = [
			$this->db->quoteName('core_type_alias') . ' = ' . $this->db->quote($context)
		];

		// Create a new query object.
		$query = $this->db->createQuery();
		$query->delete($this->db->quoteName('#__ucm_content'));
		$query->where($condition);
		$this->db->setQuery($query);

		// Execute the query to remove Item items
		$done = $this->db->execute();
		if ($done)
		{
			// If successfully removed Item add queued success message.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The (%s) type alias was removed from the <b>#__ucm_content</b> table.', $context)
			);
		}
	}

	/**
	 * Remove content item tag map related to this view
	 *
	 * @param   string   $context   The view context
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeContentItemTagMap(string $context): void
	{
		// Create a new query object.
		$query = $this->db->createQuery();

		// Remove Item items from the contentitem tag map table
		$condition = [
			$this->db->quoteName('type_alias') . ' = '. $this->db->quote($context)
		];

		// Create a new query object.
		$query = $this->db->createQuery();
		$query->delete($this->db->quoteName('#__contentitem_tag_map'));
		$query->where($condition);
		$this->db->setQuery($query);

		// Execute the query to remove Item items
		$done = $this->db->execute();
		if ($done)
		{
			// If successfully remove Item add queued success message.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The (%s) type alias was removed from the <b>#__contentitem_tag_map</b> table.', $context)
			);
		}
	}

	/**
	 * Remove action log config related to this view
	 *
	 * @param   string   $context   The view context
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeActionLogConfig(string $context): void
	{
		// Remove ###component### view from the action_log_config table
		$condition = [
			$this->db->quoteName('type_alias') . ' = '. $this->db->quote($context)
		];

		// Create a new query object.
		$query = $this->db->createQuery();
		$query->delete($this->db->quoteName('#__action_log_config'));
		$query->where($condition);
		$this->db->setQuery($query);

		// Execute the query to remove com_###component###.view
		$done = $this->db->execute();
		if ($done)
		{
			// If successfully removed ###component### view add queued success message.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('The (%s) type alias was removed from the <b>#__action_log_config</b> table.', $context)
			);
		}
	}

	/**
	 * Remove Asset Table Integrated
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeAssetData(): void
	{
		// Remove ###component### assets from the assets table
		$condition = [
			$this->db->quoteName('name') . ' LIKE ' . $this->db->quote('com_###component###.%')
		];

		// Create a new query object.
		$query = $this->db->createQuery();
		$query->delete($this->db->quoteName('#__assets'));
		$query->where($condition);
		$this->db->setQuery($query);
		$done = $this->db->execute();
		if ($done)
		{
			// If successfully removed ###component### add queued success message.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('All related (com_###component###) items was removed from the <b>#__assets</b> table.')
			);
		}
	}

	/**
	 * Remove action logs extensions integrated
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeActionLogsExtensions(): void
	{
		// Remove ###component### from the action_logs_extensions table
		$extension = [
			$this->db->quoteName('extension') . ' = ' . $this->db->quote('com_###component###')
		];

		// Create a new query object.
		$query = $this->db->createQuery();
		$query->delete($this->db->quoteName('#__action_logs_extensions'));
		$query->where($extension);
		$this->db->setQuery($query);

		// Execute the query to remove ###component###
		$done = $this->db->execute();
		if ($done)
		{
			// If successfully remove ###component### add queued success message.
			$this->app->enqueueMessage(
				Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('The (com_###component###) extension was removed from the <b>#__action_logs_extensions</b> table.')
			);
		}
	}

	/**
	 * Remove remove database fix (if possible)
	 *
	 * @return void
	 * @since  4.4.2
	 */
	protected function removeDatabaseAssetsRulesFix(): void
	{
		// Get the biggest rule column in the assets table at this point.
		$length = "SELECT CHAR_LENGTH(`rules`) as rule_size FROM #__assets ORDER BY rule_size DESC LIMIT 1";
		$this->db->setQuery($length);
		if ($this->db->execute())
		{
			$rule_length = $this->db->loadResult();
			// Check the size of the rules column
			if ($rule_length < 5120)
			{
				// Revert the assets table rules column back to the default
				$revert_rule = "ALTER TABLE `#__assets` CHANGE `rules` `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.';";
				$this->db->setQuery($revert_rule);
				$this->db->execute();

				$this->app->enqueueMessage(
					Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('Reverted the <b>#__assets</b> table rules column back to its default size of varchar(5120).')
				);
			}
			else
			{
				$this->app->enqueueMessage(
					Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('Could not revert the <b>#__assets</b> table rules column back to its default size of varchar(5120), since there is still one or more components that still requires the column to be larger.')
				);
			}
		}
	}

	/**
	 * Ensures that a class in the namespace is available.
	 * If the class is not already loaded, it attempts to load it via the specified autoloader.
	 *
	 * @param string  $className   The fully qualified name of the class to check.
	 *
	 * @return bool True if the class exists or was successfully loaded, false otherwise.
	 * @since  4.0.1
	 */
	protected function classExists(string $className): bool
	{
		if (class_exists($className, true))
		{
			return true;
		}

		// Autoloaders to check
		$autoloaders = [###INSTALLER_POWER_AUTOLOADER_ARRAY###];

		foreach ($autoloaders as $autoloader)
		{
			if (file_exists($autoloader))
			{
				require_once $autoloader;

				if (class_exists($className, true))
				{
					return true;
				}
			}
		}

		return false;
	}###INSTALLERMETHODS###
}

return new Com_###Component###InstallerScript();
