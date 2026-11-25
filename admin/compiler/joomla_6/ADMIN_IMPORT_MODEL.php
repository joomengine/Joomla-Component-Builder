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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Model;

###IMPORT_MODEL_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/***
 * ###Component### Import Base Database Model
 *
 * @since  1.6
 */
class ImportModel extends BaseDatabaseModel
{
	// set uploading values
	protected $use_streams = false;
	protected $allow_unsafe = false;
	protected $safeFileOptions = [];

	/**
	 * @var object JTable object
	 */
	protected $_table = null;

	/**
	 * @var object JTable object
	 */
	protected $_url = null;

	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_###component###.import';

	/**
	 * Import Settings
	 */
	protected $getType     = NULL;
	protected $dataType    = NULL;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 */
	protected function populateState()
	{
		$app = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication('administrator');

		$this->setState('message', $app->getUserState('com_###component###.message'));
		$app->setUserState('com_###component###.message', '');

		// Recall the 'Import from Directory' path.
		$path = $app->getUserStateFromRequest($this->_context . '.import_directory', 'import_directory', $app->get('tmp_path'));
		$this->setState('import.directory', $path);
		parent::populateState();
	}

	/**
	 * Import an spreadsheet from either folder, url or upload.
	 *
	 * @return  boolean result of import
	 *
	 */
	public function import()
	{
		$this->setState('action', 'import');
		$app = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$session = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getSession();
		$package = null;
		$continue = false;
		// get import type
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$this->getType = $input->getString('gettype', NULL);
		// get import type
		$this->dataType    = $session->get('dataType_VDM_IMPORTINTO', NULL);

		if ($package === null)
		{
			switch ($this->getType)
			{
				case 'folder':
					// Remember the 'Import from Directory' path.
					$app->getUserStateFromRequest($this->_context . '.import_directory', 'import_directory');
					$package = $this->_getPackageFromFolder();
					break;

				case 'upload':
					$package = $this->_getPackageFromUpload();
					break;

				case 'url':
					$package = $this->_getPackageFromUrl();
					break;

				case 'continue':
					$continue     = true;
					$package    = $session->get('package', null);
					$package    = json_decode($package, true);
					// clear session
					$session->remove('package');
					$session->remove('dataType');
					$session->remove('hasPackage');
					break;

				default:
					$app->setUserState('com_###component###.message', Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_NO_IMPORT_TYPE_FOUND'));

					return false;
					break;
			}
		}
		// Was the package valid?
		if (!$package || !$package['type'])
		{
			if (in_array($this->getType, array('upload', 'url')))
			{
				$this->remove($package['packagename']);
			}

			$app->setUserState('com_###component###.message', Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_UNABLE_TO_FIND_IMPORT_PACKAGE'));
			return false;
		}

		// first link data to table headers
		if(!$continue){
			$package = json_encode($package);
			$session->set('package', $package);
			$session->set('dataType', $this->dataType);
			$session->set('hasPackage', true);
			return true;
		}

		// set the data
		$headerList = json_decode($session->get($this->dataType.'_VDM_IMPORTHEADERS', false), true);
		if (!$this->setData($package,$this->dataType,$headerList))
		{
			// There was an error importing the package
			$msg = Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_ERROR');
			$back = $session->get('backto_VDM_IMPORT', NULL);
			if ($back)
			{
				$app->setUserState('com_###component###.redirect_url', 'index.php?option=com_###component###&view='.$back);
				$session->remove('backto_VDM_IMPORT');
			}
			$result = false;
		}
		else
		{
			// Package imported sucessfully
			$msg = Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('COM_###COMPONENT###_IMPORT_SUCCESS', $package['packagename']);
			$back = $session->get('backto_VDM_IMPORT', NULL);
			if ($back)
			{
				$app->setUserState('com_###component###.redirect_url', 'index.php?option=com_###component###&view='.$back);
				$session->remove('backto_VDM_IMPORT');
			}
			$result = true;
		}

		// Set some model state values
		$app->enqueueMessage($msg);

		// remove file after import
		$this->remove($package['packagename']);
		$session->remove($this->getType.'_VDM_IMPORTHEADERS');

		return $result;
	}

	/**
	 * Works out an importation spreadsheet from a HTTP upload
	 *
	 * @return spreadsheet definition or false on failure
	 */
	protected function _getPackageFromUpload()
	{
		// Get the uploaded file information
		$app = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		// Do not change the filter type 'raw'. We need this to let files containing PHP code to upload. See JInputFiles::get.
		$userfile = $input->files->get('import_package', null, 'raw');

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			$app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_MSG_WARNIMPORTFILE'), 'warning');
			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			$app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_MSG_NO_FILE_SELECTED'), 'warning');
			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			$app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_MSG_WARNIMPORTUPLOADERROR'), 'warning');
			return false;
		}

		// Build the appropriate paths
		$config = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getConfig();
		$tmp_dest = $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src = $userfile['tmp_name'];

		// Move uploaded file
		$p_file = File::upload($tmp_src, $tmp_dest, $this->use_streams, $this->allow_unsafe, $this->safeFileOptions);

		// Was the package downloaded?
		if (!$p_file)
		{
			$session = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getSession();
			$session->remove('package');
			$session->remove('dataType');
			$session->remove('hasPackage');
			// was not uploaded
			return false;
		}

		// check that this is a valid spreadsheet
		$package = $this->check($userfile['name']);

		return $package;
	}

	/**
	 * Import an spreadsheet from a directory
	 *
	 * @return  array  Spreadsheet details or false on failure
	 *
	 */
	protected function _getPackageFromFolder()
	{
		$app = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		// Get the path to the package to import
		$p_dir = $input->getString('import_directory');
		$p_dir = Path::clean($p_dir);
		// Did you give us a valid path?
		if (!file_exists($p_dir))
		{
			$app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_MSG_PLEASE_ENTER_A_PACKAGE_DIRECTORY'), 'warning');
			return false;
		}

		// Detect the package type
		$type = $this->getType;

		// Did you give us a valid package?
		if (!$type)
		{
			$app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_MSG_PATH_DOES_NOT_HAVE_A_VALID_PACKAGE'), 'warning');
		}

		// check the extention
		if(!$this->checkExtension($p_dir))
		{
			// set error message
			$app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_MSG_DOES_NOT_HAVE_A_VALID_FILE_TYPE'), 'warning');
			return false;
		}

		$package['packagename'] = null;
		$package['dir'] = $p_dir;
		$package['type'] = $type;

		return $package;
	}

	/**
	 * Import an spreadsheet from a URL
	 *
	 * @return  Package details or false on failure
	 *
	 */
	protected function _getPackageFromUrl()
	{
		$app = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		// Get the URL of the package to import
		$url = $input->getString('import_url');

		// Did you give us a URL?
		if (!$url)
		{
			$app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_MSG_ENTER_A_URL'), 'warning');
			return false;
		}

		// Download the package at the URL given
		$p_file = InstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			$app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_MSG_INVALID_URL'), 'warning');
			return false;
		}

		// check that this is a valid spreadsheet
		$package = $this->check($p_file);

		return $package;
	}

	/**
	 * Check a file and verifies it as a spreadsheet file
	 * Supports .csv .xlsx .xls and .ods
	 *
	 * @param   string  $p_filename  The uploaded package filename or import directory
	 *
	 * @return  array  of elements
	 *
	 */
	protected function check($archivename)
	{
		$app = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		// Clean the name
		$archivename = Path::clean($archivename);

		// check the extention
		if(!$this->checkExtension($archivename))
		{
			// Cleanup the import files
			$this->remove($archivename);
			$app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_MSG_DOES_NOT_HAVE_A_VALID_FILE_TYPE'), 'warning');
			return false;
		}

		$config = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getConfig();
		// set Package Name
		$check['packagename'] = $archivename;

		// set directory
		$check['dir'] = $config->get('tmp_path'). '/' .$archivename;

		// set type
		$check['type'] = $this->getType;

		return $check;
	}###IMPORT_EXT_METHOD###

	/**
	 * Clean up temporary uploaded spreadsheet
	 *
	 * @param   string  $package    Name of the uploaded spreadsheet file
	 *
	 * @return  boolean  True on success
	 *
	 */
	protected function remove($package)
	{
		$config = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getConfig();
		$package = $config->get('tmp_path'). '/' .$package;

		// Is the package file a valid file?
		if (is_file($package))
		{
			File::delete($package);
		}
		elseif (is_file(Path::clean($package)))
		{
			// It might also be just a base filename
			File::delete(Path::clean($package));
		}
	}###IMPORT_SETDATA_METHOD######IMPORT_SAVE_METHOD###

	protected function getAlias($name,$type = false)
	{
		// sanitize the name to an alias
		if (Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getConfig()->get('unicodeslugs') == 1)
		{
			$alias = OutputFilter::stringURLUnicodeSlug($name);
		}
		else
		{
			$alias = OutputFilter::stringURLSafe($name);
		}
		// must be a uniqe alias
		if ($type)
		{
			return $this->getUniqe($alias,'alias',$type);
		}
		return $alias;
	}

	/**
	 * Method to generate a uniqe value.
	 *
	 * @param   string  $field name.
	 * @param   string  $value data.
	 * @param   string  $type table.
	 *
	 * @return  string  New value.
	 */
	protected function getUniqe($value,$field,$type)
	{
		// insure the filed is always uniqe
		while (isset($this->uniqeValueArray[$type][$field][$value]))
		{
			$value = StringHelper::increment($value, 'dash');
		}
		$this->uniqeValueArray[$type][$field][$value] = $value;
		return $value;
	}

	protected function getAliasesUsed($table)
	{
		// Get a db connection.
		$db = $this->getDatabase();
		// first we check if there is a alias column
		$columns = $db->getTableColumns('#__###component###_'.$table);
		if(isset($columns['alias'])){
			// Create a new query object.
			$query = $db->createQuery();
			$query->select($db->quoteName(array('alias')));
			$query->from($db->quoteName('#__###component###_'.$table));
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				$aliases = $db->loadColumn();
				foreach($aliases as $alias)
				{
					$this->uniqeValueArray[$table]['alias'][$alias] = $alias;
				}
			}
			return true;
		}
		return false;
	}
}
