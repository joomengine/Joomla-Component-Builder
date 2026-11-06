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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Table\Table;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use Joomla\CMS\Helper\TagsHelper;
use VDM\Joomla\Utilities\SessionHelper;
use VDM\Joomla\Utilities\StringHelper as UtilitiesStringHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\GuidHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\Component\Helper;
use VDM\Joomla\Data\Factory as DataFactory;
use VDM\Joomla\Utilities\GetHelper;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Form\FormHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Admin_view Admin Model
 *
 * @since  1.6
 */
class Admin_viewModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The tab layout fields array.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected $tabLayoutFields = array(
		'details' => array(
			'left' => array(
				'name_single',
				'name_list',
				'type',
				'icon',
				'icon_add',
				'icon_category'
			),
			'right' => array(
				'short_description',
				'description',
				'add_fadein'
			),
			'fullwidth' => array(
				'note_linked_to_notice'
			),
			'above' => array(
				'system_name'
			),
			'under' => array(
				'not_required'
			)
		),
		'php' => array(
			'fullwidth' => array(
				'add_php_ajax',
				'php_ajaxmethod',
				'ajax_input',
				'add_php_getitem',
				'php_getitem',
				'add_php_getitems',
				'php_getitems',
				'add_php_getitems_after_all',
				'php_getitems_after_all',
				'add_php_getlistquery',
				'php_getlistquery',
				'add_php_getform',
				'php_getform',
				'add_php_before_save',
				'php_before_save',
				'add_php_save',
				'php_save',
				'add_php_postsavehook',
				'php_postsavehook',
				'add_php_allowadd',
				'php_allowadd',
				'add_php_allowedit',
				'php_allowedit',
				'add_php_before_cancel',
				'php_before_cancel',
				'add_php_after_cancel',
				'php_after_cancel',
				'add_php_batchcopy',
				'php_batchcopy',
				'add_php_batchmove',
				'php_batchmove',
				'add_php_before_publish',
				'php_before_publish',
				'add_php_after_publish',
				'php_after_publish',
				'add_php_before_delete',
				'php_before_delete',
				'add_php_after_delete',
				'php_after_delete',
				'add_php_document',
				'php_document'
			)
		),
		'mysql' => array(
			'left' => array(
				'mysql_table_engine',
				'mysql_table_charset',
				'mysql_table_collate',
				'mysql_table_row_format',
				'add_sql',
				'source',
				'addtables'
			),
			'fullwidth' => array(
				'sql'
			)
		),
		'settings' => array(
			'fullwidth' => array(
				'note_on_permissions',
				'addpermissions',
				'note_on_tabs',
				'addtabs',
				'note_custom_tabs_note',
				'note_on_linked_views',
				'addlinked_views'
			)
		),
		'fields' => array(
			'left' => array(
				'note_create_edit_notice',
				'alias_builder_type',
				'note_alias_builder_custom',
				'note_alias_builder_default',
				'alias_builder',
				'note_category_menu_switch',
				'add_category_submenu'
			),
			'right' => array(
				'note_create_edit_buttons'
			),
			'fullwidth' => array(
				'note_create_edit_display'
			)
		),
		'css' => array(
			'fullwidth' => array(
				'add_css_view',
				'css_view',
				'add_css_views',
				'css_views'
			)
		),
		'javascript' => array(
			'fullwidth' => array(
				'add_javascript_view_file',
				'javascript_view_file',
				'add_javascript_view_footer',
				'javascript_view_footer',
				'add_javascript_views_file',
				'javascript_views_file',
				'add_javascript_views_footer',
				'javascript_views_footer'
			)
		),
		'toolbar' => array(
			'left' => array(
				'add_custom_button'
			),
			'fullwidth' => array(
				'custom_button',
				'php_controller',
				'php_model',
				'php_controller_list',
				'php_model_list',
				'add_view_toolbar',
				'view_toolbar',
				'add_views_toolbar',
				'views_toolbar'
			)
		)
	);

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'media/com_componentbuilder/css/admin/admin.css',
		'media/com_componentbuilder/css/admin/admin_view.css'
	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'media/com_componentbuilder/js/admin/admin.js',
		'media/com_componentbuilder/js/admin/admin_view.js'
	];

	/**
	 * @var     string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_COMPONENTBUILDER';

	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_componentbuilder.admin_view';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A database object
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getTable($type = 'admin_view', $prefix = 'Administrator', $config = [])
	{
		// get instance of the table
		return parent::getTable($type, $prefix, $config);
	}


	/**
	 * The VDM view key
	 *
	 * @var    string
	 * @since   3.0.13
	 */
	protected string $vastDevMod;

	/**
	 * Retrieves or generates a Vast Development Method (VDM) key for the current item.
	 *
	 * This function performs the following operations:
	 * 1. Checks if the VDM key is already set. If not, it proceeds to generate or retrieve one.
	 * 2. Determines the item ID based on the presence of a specific argument.
	 * 3. Attempts to retrieve an existing VDM key from a helper method using the item ID.
	 * 4. If a VDM key is not found, it generates a new random VDM key.
	 * 5. Stores the VDM key and associates it with the item ID in a helper method.
	 * 6. Optionally, stores return and GUID values if available.
	 * 7. Returns the VDM key.
	 *
	 * @return string The VDM key for the current item.
	 * @since   3.0.13
	 */
	public function getVDM(): string
	{
		if (!isset($this->vastDevMod))
		{
			$_id = 0; // new item probably (since it was not set in the getItem method)

			if (empty($_id))
			{
				$id = 0;
			}
			else
			{
				$id = $_id;
			}
			// set the id and view name to session
			if (($vdm = SessionHelper::get('admin_view__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'admin_view__' . $id);
				SessionHelper::set('admin_view__' . $id, $this->vastDevMod);
				// set a return value if found
				$app = $this->app ?? Factory::getApplication();
				$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
				$return = $input->get('return', null, 'base64');
				SessionHelper::set($this->vastDevMod . '__return', $return);
				// set a GUID value if found
				if (isset($item) && ObjectHelper::check($item) && isset($item->guid)
					&& GuidHelper::valid($item->guid))
				{
					SessionHelper::set($this->vastDevMod . '__guid', $item->guid);
				}
			}
		}

		return $this->vastDevMod;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (property_exists($item, 'metadata') && !is_array($item->metadata))
			{
				// Convert the metadata field to an array.
				$metadata       = new Registry($item->metadata);
				$item->metadata = $metadata->toArray();
			}

			// API reads use read permissions; administrator editing keeps its edit guard.
			if (!empty($item->id))
			{
				$app = Factory::getApplication();

				if ($app->isClient('api'))
				{
					$user = $app->getIdentity();
					if (!($user->authorise('admin_view.access', 'com_componentbuilder.admin_view.' . $item->id) && $user->authorise('admin_view.access', 'com_componentbuilder')) || (!$user->authorise('core.options', 'com_componentbuilder') && !in_array((int) $item->access, $user->getAuthorisedViewLevels())))
					{
						throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
					}
				}
				elseif (!$this->allowEdit((array) $item))
				{
					$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
					$app->redirect('index.php?option=com_componentbuilder');
					return false;
				}
			}

			if (!empty($item->php_postsavehook))
			{
				// base64 Decode php_postsavehook.
				$item->php_postsavehook = base64_decode($item->php_postsavehook);
			}

			if (!empty($item->php_before_save))
			{
				// base64 Decode php_before_save.
				$item->php_before_save = base64_decode($item->php_before_save);
			}

			if (!empty($item->php_getlistquery))
			{
				// base64 Decode php_getlistquery.
				$item->php_getlistquery = base64_decode($item->php_getlistquery);
			}

			if (!empty($item->php_getitems))
			{
				// base64 Decode php_getitems.
				$item->php_getitems = base64_decode($item->php_getitems);
			}

			if (!empty($item->php_batchmove))
			{
				// base64 Decode php_batchmove.
				$item->php_batchmove = base64_decode($item->php_batchmove);
			}

			if (!empty($item->php_allowedit))
			{
				// base64 Decode php_allowedit.
				$item->php_allowedit = base64_decode($item->php_allowedit);
			}

			if (!empty($item->php_after_delete))
			{
				// base64 Decode php_after_delete.
				$item->php_after_delete = base64_decode($item->php_after_delete);
			}

			if (!empty($item->php_after_cancel))
			{
				// base64 Decode php_after_cancel.
				$item->php_after_cancel = base64_decode($item->php_after_cancel);
			}

			if (!empty($item->php_after_publish))
			{
				// base64 Decode php_after_publish.
				$item->php_after_publish = base64_decode($item->php_after_publish);
			}

			if (!empty($item->php_getitem))
			{
				// base64 Decode php_getitem.
				$item->php_getitem = base64_decode($item->php_getitem);
			}

			if (!empty($item->php_getitems_after_all))
			{
				// base64 Decode php_getitems_after_all.
				$item->php_getitems_after_all = base64_decode($item->php_getitems_after_all);
			}

			if (!empty($item->php_getform))
			{
				// base64 Decode php_getform.
				$item->php_getform = base64_decode($item->php_getform);
			}

			if (!empty($item->php_save))
			{
				// base64 Decode php_save.
				$item->php_save = base64_decode($item->php_save);
			}

			if (!empty($item->php_allowadd))
			{
				// base64 Decode php_allowadd.
				$item->php_allowadd = base64_decode($item->php_allowadd);
			}

			if (!empty($item->php_before_cancel))
			{
				// base64 Decode php_before_cancel.
				$item->php_before_cancel = base64_decode($item->php_before_cancel);
			}

			if (!empty($item->php_batchcopy))
			{
				// base64 Decode php_batchcopy.
				$item->php_batchcopy = base64_decode($item->php_batchcopy);
			}

			if (!empty($item->php_before_publish))
			{
				// base64 Decode php_before_publish.
				$item->php_before_publish = base64_decode($item->php_before_publish);
			}

			if (!empty($item->php_before_delete))
			{
				// base64 Decode php_before_delete.
				$item->php_before_delete = base64_decode($item->php_before_delete);
			}

			if (!empty($item->php_document))
			{
				// base64 Decode php_document.
				$item->php_document = base64_decode($item->php_document);
			}

			if (!empty($item->sql))
			{
				// base64 Decode sql.
				$item->sql = base64_decode($item->sql);
			}

			if (!empty($item->php_ajaxmethod))
			{
				// base64 Decode php_ajaxmethod.
				$item->php_ajaxmethod = base64_decode($item->php_ajaxmethod);
			}

			if (!empty($item->css_view))
			{
				// base64 Decode css_view.
				$item->css_view = base64_decode($item->css_view);
			}

			if (!empty($item->css_views))
			{
				// base64 Decode css_views.
				$item->css_views = base64_decode($item->css_views);
			}

			if (!empty($item->javascript_view_file))
			{
				// base64 Decode javascript_view_file.
				$item->javascript_view_file = base64_decode($item->javascript_view_file);
			}

			if (!empty($item->javascript_view_footer))
			{
				// base64 Decode javascript_view_footer.
				$item->javascript_view_footer = base64_decode($item->javascript_view_footer);
			}

			if (!empty($item->javascript_views_file))
			{
				// base64 Decode javascript_views_file.
				$item->javascript_views_file = base64_decode($item->javascript_views_file);
			}

			if (!empty($item->javascript_views_footer))
			{
				// base64 Decode javascript_views_footer.
				$item->javascript_views_footer = base64_decode($item->javascript_views_footer);
			}

			if (!empty($item->php_controller))
			{
				// base64 Decode php_controller.
				$item->php_controller = base64_decode($item->php_controller);
			}

			if (!empty($item->php_model))
			{
				// base64 Decode php_model.
				$item->php_model = base64_decode($item->php_model);
			}

			if (!empty($item->php_controller_list))
			{
				// base64 Decode php_controller_list.
				$item->php_controller_list = base64_decode($item->php_controller_list);
			}

			if (!empty($item->php_model_list))
			{
				// base64 Decode php_model_list.
				$item->php_model_list = base64_decode($item->php_model_list);
			}

			if (!empty($item->view_toolbar))
			{
				// base64 Decode view_toolbar.
				$item->view_toolbar = base64_decode($item->view_toolbar);
			}

			if (!empty($item->views_toolbar))
			{
				// base64 Decode views_toolbar.
				$item->views_toolbar = base64_decode($item->views_toolbar);
			}

			if (!empty($item->ajax_input))
			{
				// Convert the ajax_input field to an array.
				$ajax_input = new Registry;
				$ajax_input->loadString($item->ajax_input);
				$item->ajax_input = $ajax_input->toArray();
			}

			if (!empty($item->addpermissions))
			{
				// Convert the addpermissions field to an array.
				$addpermissions = new Registry;
				$addpermissions->loadString($item->addpermissions);
				$item->addpermissions = $addpermissions->toArray();
			}

			if (!empty($item->addtabs))
			{
				// Convert the addtabs field to an array.
				$addtabs = new Registry;
				$addtabs->loadString($item->addtabs);
				$item->addtabs = $addtabs->toArray();
			}

			if (!empty($item->addlinked_views))
			{
				// Convert the addlinked_views field to an array.
				$addlinked_views = new Registry;
				$addlinked_views->loadString($item->addlinked_views);
				$item->addlinked_views = $addlinked_views->toArray();
			}

			if (!empty($item->alias_builder))
			{
				// Convert the alias_builder field to an array.
				$alias_builder = new Registry;
				$alias_builder->loadString($item->alias_builder);
				$item->alias_builder = $alias_builder->toArray();
			}

			if (!empty($item->custom_button))
			{
				// Convert the custom_button field to an array.
				$custom_button = new Registry;
				$custom_button->loadString($item->custom_button);
				$item->custom_button = $custom_button->toArray();
			}

			if (!empty($item->addtables))
			{
				// Convert the addtables field to an array.
				$addtables = new Registry;
				$addtables->loadString($item->addtables);
				$item->addtables = $addtables->toArray();
			}


			if (empty($item->id))
			{
				$id = 0;
			}
			else
			{
				$id = $item->id;
			}
			// set the id and view name to session
			if (($vdm = SessionHelper::get('admin_view__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'admin_view__' . $id);
				SessionHelper::set('admin_view__' . $id, $this->vastDevMod);
				// set a return value if found
				$app = $this->app ?? Factory::getApplication();
				$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
				$return = $input->get('return', null, 'base64');
				SessionHelper::set($this->vastDevMod . '__return', $return);
				// set a GUID value if found
				if (isset($item) && ObjectHelper::check($item) && isset($item->guid)
					&& GuidHelper::valid($item->guid))
				{
					SessionHelper::set($this->vastDevMod . '__guid', $item->guid);
				}
			}

			// update the mysql_table_engine defaults
			if (isset($item->mysql_table_engine) && is_numeric($item->mysql_table_engine))
			{
				$item->mysql_table_engine = 'MyISAM';
			}
			// update the mysql_table_charset defaults
			if (isset($item->mysql_table_charset) && is_numeric($item->mysql_table_charset))
			{
				$item->mysql_table_charset = 'utf8';
			}
			// update the mysql_table_collate defaults
			if (isset($item->mysql_table_collate) && is_numeric($item->mysql_table_collate))
			{
				$item->mysql_table_collate = 'utf8_general_ci';
			}

		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @param   array    $options   Optional array of options for the form creation.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = [], $loadData = true, $options = ['control' => 'jform'])
	{
		// set load data option
		$options['load_data'] = $loadData;
		// check if xpath was set in options
		$xpath = false;
		if (isset($options['xpath']))
		{
			$xpath = $options['xpath'];
			unset($options['xpath']);
		}
		// check if clear form was set in options
		$clear = false;
		if (isset($options['clear']))
		{
			$clear = $options['clear'];
			unset($options['clear']);
		}

		// Get the form.
		$form = $this->loadForm('com_componentbuilder.admin_view', 'admin_view', $options, $clear, $xpath);

		if (empty($form))
		{
			return false;
		}

		$app = Factory::getApplication();

		$jinput = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		// The record being saved decides the permissions, so its id wins over the request.
		if (is_array($data) && isset($data['id']) && (int) $data['id'] > 0)
		{
			$id = (int) $data['id'];
		}
		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		elseif ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0, 'INT');
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0, 'INT');
		}

		$user = Factory::getApplication()->getIdentity();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('admin_view.edit.state', 'com_componentbuilder.admin_view.' . (int) $id))
			|| ($id == 0 && !$user->authorise('admin_view.edit.state', 'com_componentbuilder')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
		// If this is a new item insure the greated by is set.
		if (0 == $id)
		{
			// Set the created_by to this user
			$form->setValue('created_by', null, $user->id);
		}
		// Modify the form based on Edit Creaded By access controls.
		if ($id != 0 && (!$user->authorise('admin_view.edit.created_by', 'com_componentbuilder.admin_view.' . (int) $id))
			|| ($id == 0 && !$user->authorise('admin_view.edit.created_by', 'com_componentbuilder')))
		{
			if ($app->isClient('api'))
			{
				// Exclude protected metadata from API validation and binding.
				$form->removeField('created_by');
			}
			else
			{
				// Retain disabled metadata controls in administrator forms.
				$form->setFieldAttribute('created_by', 'disabled', 'true');
				$form->setFieldAttribute('created_by', 'readonly', 'true');
				$form->setFieldAttribute('created_by', 'filter', 'unset');
			}
		}
		// Modify the form based on Edit Creaded Date access controls.
		if ($id != 0 && (!$user->authorise('admin_view.edit.created', 'com_componentbuilder.admin_view.' . (int) $id))
			|| ($id == 0 && !$user->authorise('admin_view.edit.created', 'com_componentbuilder')))
		{
			if ($app->isClient('api'))
			{
				// Exclude protected metadata from API validation and binding.
				$form->removeField('created');
			}
			else
			{
				// Retain disabled metadata controls in administrator forms.
				$form->setFieldAttribute('created', 'disabled', 'true');
				$form->setFieldAttribute('created', 'filter', 'unset');
			}
		}

		// Omitted PATCH metadata must not be filtered or rebound from storage.
		if ($app->isClient('api') && $jinput->getMethod() === 'PATCH')
		{
			$submittedApiData = $jinput->get('data', json_decode($jinput->json->getRaw(), true), 'array');
			foreach (['created', 'created_by'] as $metadataField)
			{
				if (!is_array($submittedApiData) || !array_key_exists($metadataField, $submittedApiData))
				{
					$form->removeField($metadataField);
				}
			}
		}
		// Only load these values if no id is found
		if (0 == $id)
		{
			// Set redirected view name
			$redirectedView = $jinput->get('ref', null, 'STRING');
			// Set field name (or fall back to view name)
			$redirectedField = $jinput->get('field', $redirectedView, 'STRING');
			// Set redirected view id
			$redirectedId = $jinput->get('refid', 0, 'INT');
			// Set field id (or fall back to redirected view id)
			$redirectedValue = $jinput->get('field_id', $redirectedId, 'INT');
			if (0 != $redirectedValue && $redirectedField)
			{
				// Now set the local-redirected field default value
				$form->setValue($redirectedField, null, $redirectedValue);
			}
			$initDefaults = $jinput->get('init_defaults', null, 'STRING');
			if (!empty($initDefaults))
			{
				// Now check if this json values are valid
				$initDefaults = json_decode(urldecode($initDefaults), true);
				if (is_array($initDefaults))
				{
					foreach ($initDefaults as $field => $value)
					{
						$form->setValue($field, null, $value);
					}
				}
			}
		}

		// update the custom_button (sub form) layout
		$form->setFieldAttribute('custom_button', 'layout', ComponentbuilderHelper::getSubformLayout('admin_view', 'custom_button'));

		// update the ajax_input (sub form) layout
		$form->setFieldAttribute('ajax_input', 'layout', ComponentbuilderHelper::getSubformLayout('admin_view', 'ajax_input'));

		// update all editors to use this components global editor
		$global_editor = ComponentHelper::getParams('com_componentbuilder')->get('editor', 'none');
		// now get all the editor fields
		$editors = $form->getXml()->xpath("//field[@type='editor']");
		// check if we found any
		if (UtilitiesArrayHelper::check($editors))
		{
			foreach ($editors as $editor)
			{
				// get the field names
				$name = (string) $editor['name'];
				// set the field editor value (with none as fallback)
				$form->setFieldAttribute($name, 'editor', $global_editor . '|none');
			}
		}


		// Only load the GUID if new item (or empty)
		if (0 == $id || !($val = $form->getValue('guid')))
		{
			$form->setValue('guid', null, GuidHelper::get());
		}

		if ($app->isClient('api') && $jinput->getMethod() === 'PATCH')
		{
			$this->setState('jcb.api.patch.form', $form);
		}
		return $form;
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
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (empty($record->id) || ($record->published != -2))
		{
			return false;
		}

		// The record has been set. Check the record permissions.
		return $this->getCurrentUser()->authorise('admin_view.delete', 'com_componentbuilder.admin_view.' . (int) $record->id);
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = $this->getCurrentUser();
		$recordId = $record->id ?? 0;

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('admin_view.edit.state', 'com_componentbuilder.admin_view.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('admin_view.edit.state', 'com_componentbuilder');
	}

	/**
	 * Method to check if you can edit an existing record.
	 *   We know this is a double access check (Controller already does an allowEdit check)
	 *   But when the item is directly accessed the controller is skipped (2025_).
	 *
	 * @param    array    $data   An array of input data.
	 * @param    string   $key    The name of the key for the primary key.
	 *
	 * @return   boolean  True if allowed to edit the record. Defaults to the permission set in the component.
	 * @since    2.5
	 */
	protected function allowEdit(array $data = [], string $key = 'id'): bool
	{
		// get user object.
		$user = $this->getCurrentUser();
		// get record id.
		$recordId = isset($data[$key]) ? (int) $data[$key] : 0;


		// Access check.
		$access = ($user->authorise('admin_view.access', 'com_componentbuilder.admin_view.' . (int) $recordId) && $user->authorise('admin_view.access', 'com_componentbuilder'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('admin_view.edit', 'com_componentbuilder.admin_view.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('admin_view.edit.own', 'com_componentbuilder.admin_view.' . (int) $recordId))
				{
					// Now test the owner is the user.
					$ownerId = isset($data['created_by']) ? (int) $data['created_by'] : 0;
					if (empty($ownerId))
					{
						return false;
					}

					// If the owner matches 'me' then allow.
					if ($ownerId == $user->id)
					{
						if ($user->authorise('admin_view.edit.own', 'com_componentbuilder'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return $user->authorise('admin_view.edit', $this->option);
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = Factory::getDate();
		$user = $this->getCurrentUser();

		if (isset($table->name))
		{
			$table->name = \htmlspecialchars_decode($table->name, ENT_QUOTES);
		}

		if (isset($table->alias) && empty($table->alias))
		{
			$table->generateAlias();
		}

		if (empty($table->id))
		{
			$table->created = $date->toSql(false, $this->getDatabase());
			// set the user
			if ($table->created_by == 0 || empty($table->created_by))
			{
				$table->created_by = $user->id;
			}
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = $this->getDatabase();
				$query = $db->createQuery()
					->select('MAX(ordering)')
					->from($db->quoteName('#__componentbuilder_admin_view'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			$table->modified = $date->toSql(false, $this->getDatabase());
			$table->modified_by = $user->id;
		}

		if (!empty($table->id))
		{
			// Increment the items version number.
			$table->version++;
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_componentbuilder.edit.admin_view.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the per process of the data
		$this->preprocessData('com_componentbuilder.admin_view', $data);

		return $data;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		$conditionRule = FormHelper::loadRuleType('jcbconditionalrequired');
		$conditionField = '__jcb_conditional_required';
		if (!$conditionRule || $form->getFieldXml($conditionField, $group) !== false)
		{
			return false;
		}
		$conditionRan = false;
		$conditionAttributes = [];
		$conditionCallback = function (array $data) use ($form, $group, &$conditionRan, &$conditionAttributes): bool
		{
			if ($conditionRan)
			{
				return false;
			}
			$conditionRan = true;
			$conditionGroups = [0 => ['matches' => [0 => ['name' => 'add_sql', 'behavior' => 1, 'options' => [0 => '1'], 'user' => false, 'checkbox' => false, 'array' => false, 'supported' => true]], 'targets' => [0 => 'source'], 'show' => true, 'toggle' => true]];
			// The browser's not_required list is informational, never an authority.
			$conditionInput = new Registry($data);
			$conditionData = $group ? (array) $conditionInput->get($group, []) : $data;
			$conditionStored = [];
			$conditionApp = Factory::getApplication();
			$conditionPatch = $conditionApp->isClient('api') && $conditionApp->getInput()->getMethod() === 'PATCH';
			$recordId = (int) ($data['id'] ?? $this->getState($this->getName() . '.id', 0));
			if ($recordId > 0)
			{
				$stored = $this->getItem($recordId);
				if ($stored === false || $stored === null)
				{
					return false;
				}
				$conditionStored = new Registry($stored);
				$conditionStored = $group ? (array) $conditionStored->get($group, []) : $conditionStored->toArray();
			}
			$conditionPresent = static function ($value): bool
			{
				return $value !== null && $value !== '' && $value !== [];
			};
			$conditionEquals = static function ($value, $option): bool
			{
				// Selection values arrive as DOM strings; numeric/boolean options use JS equality.
				if (is_numeric($option) || $option === 'true' || $option === 'false')
				{
					if ($value === null)
					{
						return false;
					}
					$number = $option === 'true' ? 1 : ($option === 'false' ? 0 : (float) $option);
					if (is_bool($value) || (is_string($value) && trim($value) === ''))
					{
						return (float) $value === (float) $number;
					}
					return is_numeric($value) && (float) $value === (float) $number;
				}
				return is_scalar($value) && (string) $value === (string) $option;
			};
			$conditionMatch = static function ($value, array $rule) use ($conditionPresent, $conditionEquals): bool
			{
				$behavior = $rule['behavior'];
				$options = $rule['options'];
				if ($behavior >= 1 && $behavior <= 3)
				{
					if ($options !== [])
					{
						foreach ($options as $option)
						{
							$equal = $conditionEquals($value, $option);
							// Preserve the browser's OR across options, including Is Not.
							if ($behavior === 2 ? !$equal : $equal)
							{
								return true;
							}
						}
						return false;
					}
					$present = $conditionPresent($value);
					if ($behavior === 2)
					{
						return !$present;
					}
					return $present && !($behavior === 3 && $rule['user'] && $conditionEquals($value, '0'));
				}
				if ($behavior === 4 || $behavior === 5)
				{
					return $behavior === 4 ? $conditionPresent($value) : !$conditionPresent($value);
				}
				if (!is_scalar($value) && $value !== null)
				{
					return false;
				}
				$value = (string) $value;
				if ($behavior >= 6 && $behavior <= 9)
				{
					$keywords = $options['keywords'] ?? [];
					if ($keywords === [])
					{
						return $value === 'error';
					}
					$all = $behavior === 6 || $behavior === 8;
					if ($behavior === 8 || $behavior === 9)
					{
						$value = StringHelper::strtolower($value);
					}
					foreach ($keywords as $keyword)
					{
						$found = strpos($value, $keyword) !== false;
						if ($all ? !$found : $found)
						{
							return !$all;
						}
					}
					return $all;
				}
				// JavaScript length counts UTF-16 code units, including surrogate pairs.
				$length = StringHelper::strlen($value) + preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $value);
				$expected = (int) (($options['length'] ?? 0) ?: 5);
				switch ($behavior)
				{
					case 10:
						return $length >= $expected;
					case 11:
						return $length <= $expected;
					case 12:
						return $length == $expected;
				}
				return false;
			};
			$conditionalRequired = [];
			foreach ($conditionGroups as $conditionGroup)
			{
				foreach ($conditionGroup['targets'] as $target)
				{
					$conditionalRequired[$target] = true;
				}
			}
			foreach ($conditionGroups as $conditionGroup)
			{
				$matched = true;
				foreach ($conditionGroup['matches'] as $rule)
				{
					// Unsupported definitions cannot relax a required field.
					if (!$rule['supported'])
					{
						continue 2;
					}
					// ACL-denied selectors cannot change applicability through discarded input.
					$disabled = strtolower((string) $form->getFieldAttribute($rule['name'], 'disabled', '', $group));
					$filter = strtolower((string) $form->getFieldAttribute($rule['name'], 'filter', '', $group));
					$available = $form->getFieldAttribute($rule['name'], 'name', null, $group) !== null;
					$protected = !$available || in_array($disabled, ['true', '1', 'disabled'], true) || $filter === 'unset';
					$values = $protected ? $conditionStored : $conditionData;
					if (array_key_exists($rule['name'], $values))
					{
						$value = $values[$rule['name']];
					}
					elseif (!$protected && $conditionPatch && array_key_exists($rule['name'], $conditionStored))
					{
						$value = $conditionStored[$rule['name']];
					}
					elseif (!$protected && $rule['checkbox'])
					{
						// Native unchecked checkboxes omit their key on ordinary form submissions.
						$value = false;
					}
					else
					{
						$value = $form->getFieldAttribute($rule['name'], 'default', null, $group);
					}
					if ($rule['checkbox'])
					{
						$value = (bool) $value;
					}
					if ($rule['array'])
					{
						$values = $conditionPresent($value) ? (array) $value : [];
						$oneMatches = false;
						foreach ($values as $entry)
						{
							if ($conditionMatch($entry, $rule))
							{
								$oneMatches = true;
								break;
							}
						}
					}
					else
					{
						$oneMatches = $conditionMatch($value, $rule);
					}
					$matched = $matched && $oneMatches;
				}
				if ($matched || $conditionGroup['toggle'])
				{
					$required = $matched ? $conditionGroup['show'] : !$conditionGroup['show'];
					foreach ($conditionGroup['targets'] as $target)
					{
						$conditionalRequired[$target] = $required;
					}
				}
			}
			foreach ($conditionalRequired as $field => $required)
			{
				$conditionElement = $form->getFieldXml($field, $group);
				if ($conditionElement !== false)
				{
					// Snapshot after native validation plugins have finished changing the form.
					$conditionAttributes[] = [$conditionElement, isset($conditionElement['required']) ? (string) $conditionElement['required'] : null];
					$form->setFieldAttribute($field, 'required', $required ? 'true' : 'false', $group);
				}
			}
			// Inactive fields keep their values; ordinary filtering and validation still apply.
			return true;
		};
		if (!$conditionRule::attach($form, $conditionCallback, $group, $conditionField))
		{
			return false;
		}
		$conditionNode = null;
		try
		{
			$element = new \SimpleXMLElement('<field name="__jcb_conditional_required" type="hidden" filter="unset" validate="jcbconditionalrequired" />');
			if (!$form->setField($element, $group))
			{
				return false;
			}
			$element = $form->getFieldXml($conditionField, $group);
			if ($element === false)
			{
				return false;
			}
			$conditionNode = dom_import_simplexml($element);
			$container = $conditionNode->parentNode;
			while ($container->nodeName !== 'form' && $container->nodeName !== 'fields')
			{
				$container = $container->parentNode;
			}
			$container->insertBefore($conditionNode, $container->firstChild);
			// The internal rule needs no submitted value and must never reach persistence.
			$conditionPath = $group ? $group . '.' . $conditionField : $conditionField;
			$conditionInput = new Registry($data);
			$conditionInput->remove($conditionPath);
			$result = parent::validate($form, $conditionInput->toArray(), $group);
			if (!$conditionRan || $result === false)
			{
				return false;
			}
			$conditionOutput = new Registry($result);
			$conditionOutput->remove($conditionPath);
			return $conditionOutput->toArray();
		}
		finally
		{
			if ($conditionNode !== null && $conditionNode->parentNode !== null)
			{
				$conditionNode->parentNode->removeChild($conditionNode);
			}
			foreach ($conditionAttributes as [$conditionElement, $conditionRequired])
			{
				if ($conditionRequired === null)
				{
					unset($conditionElement['required']);
				}
				else
				{
					$conditionElement['required'] = $conditionRequired;
				}
			}
			$form->removeField($conditionField, $group);
			$conditionRule::detach($form);
		}
	}

	/**
	 * Method to get the unique fields of this table.
	 *
	 * @return  mixed  An array of field names, boolean false if none is set.
	 *
	 * @since   3.0
	 */
	protected function getUniqueFields()
	{
		return array('guid');
	}

	/**
	 * Database column settings used when generating unique values.
	 *
	 * @return  array  Column types, character limits and nullability.
	 * @since   6.2.1
	 */
	protected function getUniqueFieldSettings()
	{
		return [
			'system_name' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'name_single' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
			'short_description' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'php_postsavehook' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_before_save' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_getlistquery' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_getitems' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'ajax_input' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'source' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'icon' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'php_batchmove' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_fadein' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'description' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'icon_category' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'icon_add' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'php_allowedit' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_after_delete' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_after_cancel' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'type' => ['type' => 'INT', 'length' => 11, 'nullable' => false],
			'php_after_publish' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'mysql_table_charset' => ['type' => 'VARCHAR', 'length' => 64, 'nullable' => false],
			'name_list' => ['type' => 'CHAR', 'length' => 64, 'nullable' => true],
			'addpermissions' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_getitem' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_getitems_after_all' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'addtabs' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_getform' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_save' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_allowadd' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'addlinked_views' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_before_cancel' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_batchcopy' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'alias_builder_type' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_before_publish' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_before_delete' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_document' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'alias_builder' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'mysql_table_row_format' => ['type' => 'VARCHAR', 'length' => 64, 'nullable' => false],
			'sql' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_category_submenu' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_ajaxmethod' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_php_getitem' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_getitems' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_css_view' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_getitems_after_all' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'css_view' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_php_getlistquery' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_css_views' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_getform' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'css_views' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_php_before_save' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_javascript_view_file' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_save' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'javascript_view_file' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_php_postsavehook' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_javascript_view_footer' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_allowadd' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'javascript_view_footer' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_php_allowedit' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_javascript_views_file' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_before_cancel' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'javascript_views_file' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_php_after_cancel' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_javascript_views_footer' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_batchcopy' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'javascript_views_footer' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_php_batchmove' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_custom_button' => ['type' => 'INT', 'length' => 1, 'nullable' => false],
			'add_php_before_publish' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'custom_button' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_php_after_publish' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_controller' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_php_before_delete' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_model' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_php_after_delete' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_controller_list' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_php_document' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_model_list' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'mysql_table_engine' => ['type' => 'VARCHAR', 'length' => 64, 'nullable' => false],
			'add_view_toolbar' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'mysql_table_collate' => ['type' => 'VARCHAR', 'length' => 64, 'nullable' => false],
			'view_toolbar' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_sql' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_views_toolbar' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'addtables' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'views_toolbar' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'guid' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'add_php_ajax' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
		];
	}

	/**
	 * Generate a valid GUID candidate for a copied record.
	 *
	 * @return  string  A new GUID candidate.
	 * @since   6.2.1
	 */
	protected function generateUniqueGuid()
	{
		return (string) GuidHelper::get();
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (!parent::delete($pks))
		{
			return false;
		}


		// linked tables
		$_tables_array = [
			'admin_fields' => 'admin_view',
			'admin_fields_conditions' => 'admin_view',
			'admin_fields_relations' => 'admin_view',
			'admin_custom_tabs' => 'admin_view'
		];

		// Update all linked tables
		if (!empty($_tables_array) && UtilitiesArrayHelper::check($pks))
		{
			// Ensure field key
			$_field_key ??= 'guid';

			// Set active component context
			Helper::setOption('com_componentbuilder');

			// Load GUIDs once
			$_guids = DataFactory::_('Load')->values(
				['a.' . $_field_key], // selection
				['a' => 'admin_view'], // source table
				['a.id' => ['value' => (array) $pks, 'operator' => 'IN']] // where
			);

			// Abort early if nothing returned
			if (empty($_guids))
			{
				return true;
			}

			// Normalize & deduplicate GUIDs
			$_guids = array_values(array_unique((array) $_guids));

			foreach ($_tables_array as $_delete_table => $_field_name)
			{
				// Skip invalid configuration
				if (empty($_delete_table) || empty($_field_name))
				{
					continue;
				}

				// Load linked item IDs
				$_pks = DataFactory::_('Load')->values(
					['a.id' => 'id'], // selection
					['a' => $_delete_table], // table
					['a.' . $_field_name => ['value' => $_guids, 'operator' => 'IN']] // where
				);

				// Skip empty or broken relations
				if (empty($_pks))
				{
					continue;
				}

				// Normalize keys
				$_pks = array_values(array_unique((array) $_pks));

				// Load model safely (it throws; it never returns null)
				try
				{
					$_Model = Helper::getModel($_delete_table);
				}
				catch (\Throwable $e)
				{
					// Intentionally ignored (safe fail)
					continue;
				}

				// Move to trash first
				$_Model->publish($_pks, -2);

				// Delete records
				$_Model->delete($_pks);
			}
		}

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		if (!parent::publish($pks, $value))
		{
			return false;
		}


		// linked tables
		$_tables_array = [
			'admin_fields' => 'admin_view',
			'admin_fields_conditions' => 'admin_view',
			'admin_fields_relations' => 'admin_view',
			'admin_custom_tabs' => 'admin_view'
		];

		// Update all linked tables
		if (!empty($_tables_array) && UtilitiesArrayHelper::check($pks))
		{
			// Ensure field key
			$_field_key ??= 'guid';

			// Set active component context
			Helper::setOption('com_componentbuilder');

			// Load GUIDs once
			$_guids = DataFactory::_('Load')->values(
				['a.' . $_field_key], // selection
				['a' => 'admin_view'], // source table
				['a.id' => ['value' => (array) $pks, 'operator' => 'IN']] // where
			);

			// Abort early if nothing returned
			if (empty($_guids))
			{
				return true;
			}

			// Normalize & deduplicate GUIDs
			$_guids = array_values(array_unique((array) $_guids));

			foreach ($_tables_array as $_update_table => $_field_name)
			{
				// Skip invalid config
				if (empty($_update_table) || empty($_field_name))
				{
					continue;
				}

				// Load linked IDs
				$_pks = DataFactory::_('Load')->values(
					['a.id' => 'id'], // selection
					['a' => $_update_table], // source table
					['a.' . $_field_name => ['value' => $_guids, 'operator' => 'IN']] // where
				);

				// Skip empty or broken relations
				if (empty($_pks))
				{
					continue;
				}

				// Normalize keys
				$_pks = array_values(array_unique((array) $_pks));

				// Load model safely
				try {
					$_Model = Helper::getModel($_update_table);
				} catch (\Throwable $e) {
					// Intentionally ignored
					continue;
				}

				// Apply publish state
				$_Model->publish($_pks, $value);
			}
		}

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 * @since   1.6
	 */
	public function save($data)
	{
		$input    = Factory::getApplication()->getInput();
		$filter   = InputFilter::getInstance();

		// set the metadata to the Item Data
		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');

			$metadata = new Registry;
			$metadata->loadArray($data['metadata']);
			$data['metadata'] = (string) $metadata;
		}

		// The record keys, as every line below expects them: the primary key as an
		// integer that is never taken from the request (null from the API on create).
		$data['id'] = (int) ($data['id'] ?? 0);

		// The guid is the server's: an existing record keeps the guid it was stored
		// with, and the API never takes one from the request.
		if ($data['id'] > 0)
		{
			$data['guid'] = (string) GetHelper::var('admin_view', $data['id'], 'id', 'guid', '=', 'componentbuilder');
		}
		elseif (Factory::getApplication()->isClient('api'))
		{
			$data['guid'] = '';
		}
		else
		{
			$data['guid'] = (string) ($data['guid'] ?? '');
		}

		// Set the guid while it is empty, not valid, or not unique in this table.
		while (!GuidHelper::valid($data['guid'], 'admin_view', $data['id'], 'componentbuilder'))
		{
			$data['guid'] = (string) GuidHelper::get();
		}

		// Preserve omitted API PATCH values in their original storage representation.
		$jcbPatchStored = [];
		$jcbPatchInput = [];
		$jcbPatchId = $data['id'];

		if ($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api') && $jcbPatchId > 0)
		{
			$jcbPatchSubmitted = $input->get('data', json_decode($input->json->getRaw(), true), 'array');
			$jcbPatchSubmitted = is_array($jcbPatchSubmitted) ? $jcbPatchSubmitted : [];
			// Joomla's API inserts an empty tags array after validation. It must not
			// clear omitted relationships or bypass the form's field permissions.
			$jcbPatchForm = $this->getState('jcb.api.patch.form');
			if (!array_key_exists('tags', $jcbPatchSubmitted)
				|| ($jcbPatchForm !== null && (!$jcbPatchForm->getField('tags')
					|| strtolower((string) $jcbPatchForm->getFieldAttribute('tags', 'filter', '')) === 'unset'
					|| in_array(strtolower((string) $jcbPatchForm->getFieldAttribute('tags', 'disabled', '')), ['true', '1'], true))))
			{
				unset($data['tags']);
			}

			$jcbPatchTable = $this->getTable();

			if (!$jcbPatchTable->load($jcbPatchId))
			{
				throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_RECORD_LOAD'), 404);
			}

			foreach ($jcbPatchTable->getFields() as $jcbPatchField)
			{
				$jcbPatchName = $jcbPatchField->Field;

				if ($jcbPatchName !== $jcbPatchTable->getKeyName() && $jcbPatchName !== 'guid'
					&& !array_key_exists($jcbPatchName, $jcbPatchSubmitted)
					&& array_key_exists($jcbPatchName, $data))
				{
					$jcbPatchStored[$jcbPatchName] = $jcbPatchTable->{$jcbPatchName};
					$jcbPatchInput[$jcbPatchName] = serialize($data[$jcbPatchName]);
				}
			}
		}


		// if system name is empty create a system name from the name_single
		if (empty($data['system_name']) || !UtilitiesStringHelper::check($data['system_name']))
		{
			$data['system_name'] = $data['name_single'];
		}

		// validate that the list and single view name are not the same
		if ($data['name_single'] === $data['name_list'])
		{
			$data['name_list'] .= '_s';
		}

		// Set the GUID if empty or not valid
		if (empty($data['guid']) && $data['id'] > 0)
		{
			// get the existing one
			$data['guid'] = (string) GetHelper::var('admin_view', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "admin_view", $data['id']))
		{
			// must always be set
			$data['guid'] = (string) GuidHelper::get();
		}

		// A copy or a custom derivation must follow the normal storage transforms.
		if ((int) $data['id'] !== $jcbPatchId)
		{
			$jcbPatchStored = [];
		}
		else
		{
			foreach ($jcbPatchStored as $jcbPatchName => $jcbPatchValue)
			{
				if (!array_key_exists($jcbPatchName, $data)
					|| serialize($data[$jcbPatchName]) !== $jcbPatchInput[$jcbPatchName])
				{
					unset($jcbPatchStored[$jcbPatchName]);
				}
			}
		}

		if (array_key_exists('ajax_input', $jcbPatchStored)
			&& (!array_key_exists('ajax_input', $data) || serialize($data['ajax_input']) !== $jcbPatchInput['ajax_input']))
		{
			unset($jcbPatchStored['ajax_input']);
		}

		// Set the ajax_input items to data.
		if (isset($data['ajax_input']) && is_array($data['ajax_input']) && !array_key_exists('ajax_input', $jcbPatchStored))
		{
			$ajax_input = new Registry;
			$ajax_input->loadArray($data['ajax_input']);
			$data['ajax_input'] = (string) $ajax_input;
		}
		elseif (!isset($data['ajax_input']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty ajax_input to data
			$data['ajax_input'] = '';
		}

		if (array_key_exists('addpermissions', $jcbPatchStored)
			&& (!array_key_exists('addpermissions', $data) || serialize($data['addpermissions']) !== $jcbPatchInput['addpermissions']))
		{
			unset($jcbPatchStored['addpermissions']);
		}

		// Set the addpermissions items to data.
		if (isset($data['addpermissions']) && is_array($data['addpermissions']) && !array_key_exists('addpermissions', $jcbPatchStored))
		{
			$addpermissions = new Registry;
			$addpermissions->loadArray($data['addpermissions']);
			$data['addpermissions'] = (string) $addpermissions;
		}
		elseif (!isset($data['addpermissions']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty addpermissions to data
			$data['addpermissions'] = '';
		}

		if (array_key_exists('addtabs', $jcbPatchStored)
			&& (!array_key_exists('addtabs', $data) || serialize($data['addtabs']) !== $jcbPatchInput['addtabs']))
		{
			unset($jcbPatchStored['addtabs']);
		}

		// Set the addtabs items to data.
		if (isset($data['addtabs']) && is_array($data['addtabs']) && !array_key_exists('addtabs', $jcbPatchStored))
		{
			$addtabs = new Registry;
			$addtabs->loadArray($data['addtabs']);
			$data['addtabs'] = (string) $addtabs;
		}
		elseif (!isset($data['addtabs']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty addtabs to data
			$data['addtabs'] = '';
		}

		if (array_key_exists('addlinked_views', $jcbPatchStored)
			&& (!array_key_exists('addlinked_views', $data) || serialize($data['addlinked_views']) !== $jcbPatchInput['addlinked_views']))
		{
			unset($jcbPatchStored['addlinked_views']);
		}

		// Set the addlinked_views items to data.
		if (isset($data['addlinked_views']) && is_array($data['addlinked_views']) && !array_key_exists('addlinked_views', $jcbPatchStored))
		{
			$addlinked_views = new Registry;
			$addlinked_views->loadArray($data['addlinked_views']);
			$data['addlinked_views'] = (string) $addlinked_views;
		}
		elseif (!isset($data['addlinked_views']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty addlinked_views to data
			$data['addlinked_views'] = '';
		}

		if (array_key_exists('alias_builder', $jcbPatchStored)
			&& (!array_key_exists('alias_builder', $data) || serialize($data['alias_builder']) !== $jcbPatchInput['alias_builder']))
		{
			unset($jcbPatchStored['alias_builder']);
		}

		// Set the alias_builder items to data.
		if (isset($data['alias_builder']) && is_array($data['alias_builder']) && !array_key_exists('alias_builder', $jcbPatchStored))
		{
			$alias_builder = new Registry;
			$alias_builder->loadArray($data['alias_builder']);
			$data['alias_builder'] = (string) $alias_builder;
		}
		elseif (!isset($data['alias_builder']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty alias_builder to data
			$data['alias_builder'] = '';
		}

		if (array_key_exists('custom_button', $jcbPatchStored)
			&& (!array_key_exists('custom_button', $data) || serialize($data['custom_button']) !== $jcbPatchInput['custom_button']))
		{
			unset($jcbPatchStored['custom_button']);
		}

		// Set the custom_button items to data.
		if (isset($data['custom_button']) && is_array($data['custom_button']) && !array_key_exists('custom_button', $jcbPatchStored))
		{
			$custom_button = new Registry;
			$custom_button->loadArray($data['custom_button']);
			$data['custom_button'] = (string) $custom_button;
		}
		elseif (!isset($data['custom_button']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty custom_button to data
			$data['custom_button'] = '';
		}

		if (array_key_exists('addtables', $jcbPatchStored)
			&& (!array_key_exists('addtables', $data) || serialize($data['addtables']) !== $jcbPatchInput['addtables']))
		{
			unset($jcbPatchStored['addtables']);
		}

		// Set the addtables items to data.
		if (isset($data['addtables']) && is_array($data['addtables']) && !array_key_exists('addtables', $jcbPatchStored))
		{
			$addtables = new Registry;
			$addtables->loadArray($data['addtables']);
			$data['addtables'] = (string) $addtables;
		}
		elseif (!isset($data['addtables']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty addtables to data
			$data['addtables'] = '';
		}

		if (array_key_exists('php_postsavehook', $jcbPatchStored)
			&& (!array_key_exists('php_postsavehook', $data) || serialize($data['php_postsavehook']) !== $jcbPatchInput['php_postsavehook']))
		{
			unset($jcbPatchStored['php_postsavehook']);
		}

		// Set the php_postsavehook string to base64 string.
		if (isset($data['php_postsavehook']) && !array_key_exists('php_postsavehook', $jcbPatchStored))
		{
			$data['php_postsavehook'] = base64_encode($data['php_postsavehook']);
		}

		if (array_key_exists('php_before_save', $jcbPatchStored)
			&& (!array_key_exists('php_before_save', $data) || serialize($data['php_before_save']) !== $jcbPatchInput['php_before_save']))
		{
			unset($jcbPatchStored['php_before_save']);
		}

		// Set the php_before_save string to base64 string.
		if (isset($data['php_before_save']) && !array_key_exists('php_before_save', $jcbPatchStored))
		{
			$data['php_before_save'] = base64_encode($data['php_before_save']);
		}

		if (array_key_exists('php_getlistquery', $jcbPatchStored)
			&& (!array_key_exists('php_getlistquery', $data) || serialize($data['php_getlistquery']) !== $jcbPatchInput['php_getlistquery']))
		{
			unset($jcbPatchStored['php_getlistquery']);
		}

		// Set the php_getlistquery string to base64 string.
		if (isset($data['php_getlistquery']) && !array_key_exists('php_getlistquery', $jcbPatchStored))
		{
			$data['php_getlistquery'] = base64_encode($data['php_getlistquery']);
		}

		if (array_key_exists('php_getitems', $jcbPatchStored)
			&& (!array_key_exists('php_getitems', $data) || serialize($data['php_getitems']) !== $jcbPatchInput['php_getitems']))
		{
			unset($jcbPatchStored['php_getitems']);
		}

		// Set the php_getitems string to base64 string.
		if (isset($data['php_getitems']) && !array_key_exists('php_getitems', $jcbPatchStored))
		{
			$data['php_getitems'] = base64_encode($data['php_getitems']);
		}

		if (array_key_exists('php_batchmove', $jcbPatchStored)
			&& (!array_key_exists('php_batchmove', $data) || serialize($data['php_batchmove']) !== $jcbPatchInput['php_batchmove']))
		{
			unset($jcbPatchStored['php_batchmove']);
		}

		// Set the php_batchmove string to base64 string.
		if (isset($data['php_batchmove']) && !array_key_exists('php_batchmove', $jcbPatchStored))
		{
			$data['php_batchmove'] = base64_encode($data['php_batchmove']);
		}

		if (array_key_exists('php_allowedit', $jcbPatchStored)
			&& (!array_key_exists('php_allowedit', $data) || serialize($data['php_allowedit']) !== $jcbPatchInput['php_allowedit']))
		{
			unset($jcbPatchStored['php_allowedit']);
		}

		// Set the php_allowedit string to base64 string.
		if (isset($data['php_allowedit']) && !array_key_exists('php_allowedit', $jcbPatchStored))
		{
			$data['php_allowedit'] = base64_encode($data['php_allowedit']);
		}

		if (array_key_exists('php_after_delete', $jcbPatchStored)
			&& (!array_key_exists('php_after_delete', $data) || serialize($data['php_after_delete']) !== $jcbPatchInput['php_after_delete']))
		{
			unset($jcbPatchStored['php_after_delete']);
		}

		// Set the php_after_delete string to base64 string.
		if (isset($data['php_after_delete']) && !array_key_exists('php_after_delete', $jcbPatchStored))
		{
			$data['php_after_delete'] = base64_encode($data['php_after_delete']);
		}

		if (array_key_exists('php_after_cancel', $jcbPatchStored)
			&& (!array_key_exists('php_after_cancel', $data) || serialize($data['php_after_cancel']) !== $jcbPatchInput['php_after_cancel']))
		{
			unset($jcbPatchStored['php_after_cancel']);
		}

		// Set the php_after_cancel string to base64 string.
		if (isset($data['php_after_cancel']) && !array_key_exists('php_after_cancel', $jcbPatchStored))
		{
			$data['php_after_cancel'] = base64_encode($data['php_after_cancel']);
		}

		if (array_key_exists('php_after_publish', $jcbPatchStored)
			&& (!array_key_exists('php_after_publish', $data) || serialize($data['php_after_publish']) !== $jcbPatchInput['php_after_publish']))
		{
			unset($jcbPatchStored['php_after_publish']);
		}

		// Set the php_after_publish string to base64 string.
		if (isset($data['php_after_publish']) && !array_key_exists('php_after_publish', $jcbPatchStored))
		{
			$data['php_after_publish'] = base64_encode($data['php_after_publish']);
		}

		if (array_key_exists('php_getitem', $jcbPatchStored)
			&& (!array_key_exists('php_getitem', $data) || serialize($data['php_getitem']) !== $jcbPatchInput['php_getitem']))
		{
			unset($jcbPatchStored['php_getitem']);
		}

		// Set the php_getitem string to base64 string.
		if (isset($data['php_getitem']) && !array_key_exists('php_getitem', $jcbPatchStored))
		{
			$data['php_getitem'] = base64_encode($data['php_getitem']);
		}

		if (array_key_exists('php_getitems_after_all', $jcbPatchStored)
			&& (!array_key_exists('php_getitems_after_all', $data) || serialize($data['php_getitems_after_all']) !== $jcbPatchInput['php_getitems_after_all']))
		{
			unset($jcbPatchStored['php_getitems_after_all']);
		}

		// Set the php_getitems_after_all string to base64 string.
		if (isset($data['php_getitems_after_all']) && !array_key_exists('php_getitems_after_all', $jcbPatchStored))
		{
			$data['php_getitems_after_all'] = base64_encode($data['php_getitems_after_all']);
		}

		if (array_key_exists('php_getform', $jcbPatchStored)
			&& (!array_key_exists('php_getform', $data) || serialize($data['php_getform']) !== $jcbPatchInput['php_getform']))
		{
			unset($jcbPatchStored['php_getform']);
		}

		// Set the php_getform string to base64 string.
		if (isset($data['php_getform']) && !array_key_exists('php_getform', $jcbPatchStored))
		{
			$data['php_getform'] = base64_encode($data['php_getform']);
		}

		if (array_key_exists('php_save', $jcbPatchStored)
			&& (!array_key_exists('php_save', $data) || serialize($data['php_save']) !== $jcbPatchInput['php_save']))
		{
			unset($jcbPatchStored['php_save']);
		}

		// Set the php_save string to base64 string.
		if (isset($data['php_save']) && !array_key_exists('php_save', $jcbPatchStored))
		{
			$data['php_save'] = base64_encode($data['php_save']);
		}

		if (array_key_exists('php_allowadd', $jcbPatchStored)
			&& (!array_key_exists('php_allowadd', $data) || serialize($data['php_allowadd']) !== $jcbPatchInput['php_allowadd']))
		{
			unset($jcbPatchStored['php_allowadd']);
		}

		// Set the php_allowadd string to base64 string.
		if (isset($data['php_allowadd']) && !array_key_exists('php_allowadd', $jcbPatchStored))
		{
			$data['php_allowadd'] = base64_encode($data['php_allowadd']);
		}

		if (array_key_exists('php_before_cancel', $jcbPatchStored)
			&& (!array_key_exists('php_before_cancel', $data) || serialize($data['php_before_cancel']) !== $jcbPatchInput['php_before_cancel']))
		{
			unset($jcbPatchStored['php_before_cancel']);
		}

		// Set the php_before_cancel string to base64 string.
		if (isset($data['php_before_cancel']) && !array_key_exists('php_before_cancel', $jcbPatchStored))
		{
			$data['php_before_cancel'] = base64_encode($data['php_before_cancel']);
		}

		if (array_key_exists('php_batchcopy', $jcbPatchStored)
			&& (!array_key_exists('php_batchcopy', $data) || serialize($data['php_batchcopy']) !== $jcbPatchInput['php_batchcopy']))
		{
			unset($jcbPatchStored['php_batchcopy']);
		}

		// Set the php_batchcopy string to base64 string.
		if (isset($data['php_batchcopy']) && !array_key_exists('php_batchcopy', $jcbPatchStored))
		{
			$data['php_batchcopy'] = base64_encode($data['php_batchcopy']);
		}

		if (array_key_exists('php_before_publish', $jcbPatchStored)
			&& (!array_key_exists('php_before_publish', $data) || serialize($data['php_before_publish']) !== $jcbPatchInput['php_before_publish']))
		{
			unset($jcbPatchStored['php_before_publish']);
		}

		// Set the php_before_publish string to base64 string.
		if (isset($data['php_before_publish']) && !array_key_exists('php_before_publish', $jcbPatchStored))
		{
			$data['php_before_publish'] = base64_encode($data['php_before_publish']);
		}

		if (array_key_exists('php_before_delete', $jcbPatchStored)
			&& (!array_key_exists('php_before_delete', $data) || serialize($data['php_before_delete']) !== $jcbPatchInput['php_before_delete']))
		{
			unset($jcbPatchStored['php_before_delete']);
		}

		// Set the php_before_delete string to base64 string.
		if (isset($data['php_before_delete']) && !array_key_exists('php_before_delete', $jcbPatchStored))
		{
			$data['php_before_delete'] = base64_encode($data['php_before_delete']);
		}

		if (array_key_exists('php_document', $jcbPatchStored)
			&& (!array_key_exists('php_document', $data) || serialize($data['php_document']) !== $jcbPatchInput['php_document']))
		{
			unset($jcbPatchStored['php_document']);
		}

		// Set the php_document string to base64 string.
		if (isset($data['php_document']) && !array_key_exists('php_document', $jcbPatchStored))
		{
			$data['php_document'] = base64_encode($data['php_document']);
		}

		if (array_key_exists('sql', $jcbPatchStored)
			&& (!array_key_exists('sql', $data) || serialize($data['sql']) !== $jcbPatchInput['sql']))
		{
			unset($jcbPatchStored['sql']);
		}

		// Set the sql string to base64 string.
		if (isset($data['sql']) && !array_key_exists('sql', $jcbPatchStored))
		{
			$data['sql'] = base64_encode($data['sql']);
		}

		if (array_key_exists('php_ajaxmethod', $jcbPatchStored)
			&& (!array_key_exists('php_ajaxmethod', $data) || serialize($data['php_ajaxmethod']) !== $jcbPatchInput['php_ajaxmethod']))
		{
			unset($jcbPatchStored['php_ajaxmethod']);
		}

		// Set the php_ajaxmethod string to base64 string.
		if (isset($data['php_ajaxmethod']) && !array_key_exists('php_ajaxmethod', $jcbPatchStored))
		{
			$data['php_ajaxmethod'] = base64_encode($data['php_ajaxmethod']);
		}

		if (array_key_exists('css_view', $jcbPatchStored)
			&& (!array_key_exists('css_view', $data) || serialize($data['css_view']) !== $jcbPatchInput['css_view']))
		{
			unset($jcbPatchStored['css_view']);
		}

		// Set the css_view string to base64 string.
		if (isset($data['css_view']) && !array_key_exists('css_view', $jcbPatchStored))
		{
			$data['css_view'] = base64_encode($data['css_view']);
		}

		if (array_key_exists('css_views', $jcbPatchStored)
			&& (!array_key_exists('css_views', $data) || serialize($data['css_views']) !== $jcbPatchInput['css_views']))
		{
			unset($jcbPatchStored['css_views']);
		}

		// Set the css_views string to base64 string.
		if (isset($data['css_views']) && !array_key_exists('css_views', $jcbPatchStored))
		{
			$data['css_views'] = base64_encode($data['css_views']);
		}

		if (array_key_exists('javascript_view_file', $jcbPatchStored)
			&& (!array_key_exists('javascript_view_file', $data) || serialize($data['javascript_view_file']) !== $jcbPatchInput['javascript_view_file']))
		{
			unset($jcbPatchStored['javascript_view_file']);
		}

		// Set the javascript_view_file string to base64 string.
		if (isset($data['javascript_view_file']) && !array_key_exists('javascript_view_file', $jcbPatchStored))
		{
			$data['javascript_view_file'] = base64_encode($data['javascript_view_file']);
		}

		if (array_key_exists('javascript_view_footer', $jcbPatchStored)
			&& (!array_key_exists('javascript_view_footer', $data) || serialize($data['javascript_view_footer']) !== $jcbPatchInput['javascript_view_footer']))
		{
			unset($jcbPatchStored['javascript_view_footer']);
		}

		// Set the javascript_view_footer string to base64 string.
		if (isset($data['javascript_view_footer']) && !array_key_exists('javascript_view_footer', $jcbPatchStored))
		{
			$data['javascript_view_footer'] = base64_encode($data['javascript_view_footer']);
		}

		if (array_key_exists('javascript_views_file', $jcbPatchStored)
			&& (!array_key_exists('javascript_views_file', $data) || serialize($data['javascript_views_file']) !== $jcbPatchInput['javascript_views_file']))
		{
			unset($jcbPatchStored['javascript_views_file']);
		}

		// Set the javascript_views_file string to base64 string.
		if (isset($data['javascript_views_file']) && !array_key_exists('javascript_views_file', $jcbPatchStored))
		{
			$data['javascript_views_file'] = base64_encode($data['javascript_views_file']);
		}

		if (array_key_exists('javascript_views_footer', $jcbPatchStored)
			&& (!array_key_exists('javascript_views_footer', $data) || serialize($data['javascript_views_footer']) !== $jcbPatchInput['javascript_views_footer']))
		{
			unset($jcbPatchStored['javascript_views_footer']);
		}

		// Set the javascript_views_footer string to base64 string.
		if (isset($data['javascript_views_footer']) && !array_key_exists('javascript_views_footer', $jcbPatchStored))
		{
			$data['javascript_views_footer'] = base64_encode($data['javascript_views_footer']);
		}

		if (array_key_exists('php_controller', $jcbPatchStored)
			&& (!array_key_exists('php_controller', $data) || serialize($data['php_controller']) !== $jcbPatchInput['php_controller']))
		{
			unset($jcbPatchStored['php_controller']);
		}

		// Set the php_controller string to base64 string.
		if (isset($data['php_controller']) && !array_key_exists('php_controller', $jcbPatchStored))
		{
			$data['php_controller'] = base64_encode($data['php_controller']);
		}

		if (array_key_exists('php_model', $jcbPatchStored)
			&& (!array_key_exists('php_model', $data) || serialize($data['php_model']) !== $jcbPatchInput['php_model']))
		{
			unset($jcbPatchStored['php_model']);
		}

		// Set the php_model string to base64 string.
		if (isset($data['php_model']) && !array_key_exists('php_model', $jcbPatchStored))
		{
			$data['php_model'] = base64_encode($data['php_model']);
		}

		if (array_key_exists('php_controller_list', $jcbPatchStored)
			&& (!array_key_exists('php_controller_list', $data) || serialize($data['php_controller_list']) !== $jcbPatchInput['php_controller_list']))
		{
			unset($jcbPatchStored['php_controller_list']);
		}

		// Set the php_controller_list string to base64 string.
		if (isset($data['php_controller_list']) && !array_key_exists('php_controller_list', $jcbPatchStored))
		{
			$data['php_controller_list'] = base64_encode($data['php_controller_list']);
		}

		if (array_key_exists('php_model_list', $jcbPatchStored)
			&& (!array_key_exists('php_model_list', $data) || serialize($data['php_model_list']) !== $jcbPatchInput['php_model_list']))
		{
			unset($jcbPatchStored['php_model_list']);
		}

		// Set the php_model_list string to base64 string.
		if (isset($data['php_model_list']) && !array_key_exists('php_model_list', $jcbPatchStored))
		{
			$data['php_model_list'] = base64_encode($data['php_model_list']);
		}

		if (array_key_exists('view_toolbar', $jcbPatchStored)
			&& (!array_key_exists('view_toolbar', $data) || serialize($data['view_toolbar']) !== $jcbPatchInput['view_toolbar']))
		{
			unset($jcbPatchStored['view_toolbar']);
		}

		// Set the view_toolbar string to base64 string.
		if (isset($data['view_toolbar']) && !array_key_exists('view_toolbar', $jcbPatchStored))
		{
			$data['view_toolbar'] = base64_encode($data['view_toolbar']);
		}

		if (array_key_exists('views_toolbar', $jcbPatchStored)
			&& (!array_key_exists('views_toolbar', $data) || serialize($data['views_toolbar']) !== $jcbPatchInput['views_toolbar']))
		{
			unset($jcbPatchStored['views_toolbar']);
		}

		// Set the views_toolbar string to base64 string.
		if (isset($data['views_toolbar']) && !array_key_exists('views_toolbar', $jcbPatchStored))
		{
			$data['views_toolbar'] = base64_encode($data['views_toolbar']);
		}

		// Restore unchanged omitted columns without a lossy decode/encode round trip.
		foreach ($jcbPatchStored as $jcbPatchName => $jcbPatchValue)
		{
			if (array_key_exists($jcbPatchName, $data)
				&& serialize($data[$jcbPatchName]) === $jcbPatchInput[$jcbPatchName])
			{
				$data[$jcbPatchName] = $jcbPatchValue;
			}
		}

		// Set the Params Items to data
		if (isset($data['params']) && is_array($data['params']))
		{
			$params = new Registry;
			$params->loadArray($data['params']);
			$data['params'] = (string) $params;
		}

		// Alter the unique field for save as copy
		if ($input->get('task') === 'save2copy')
		{
			// Automatic handling of other unique fields
			$uniqueFields = $this->getUniqueFields();
			if (UtilitiesArrayHelper::check($uniqueFields))
			{
				foreach ($uniqueFields as $uniqueField)
				{
					if (!array_key_exists($uniqueField, $data))
					{
						continue;
					}
					$uniqueValue = $this->generateUnique($uniqueField, $data[$uniqueField]);
					if ($uniqueValue === false)
					{
						return false;
					}
					$data[$uniqueField] = $uniqueValue;
				}
			}
		}

		if (parent::save($data))
		{
			return true;
		}
		return false;
	}

	/**
	 * Generate a storable unique copy value without changing numeric identifiers.
	 *
	 * @param   string  $field  The database column name.
	 * @param   mixed   $value  The requested copy value.
	 *
	 * @return  mixed  An available value, or false after setting a model error.
	 * @since   3.0
	 */
	protected function generateUnique($field, $value)
	{
		$settings = $this->getUniqueFieldSettings();
		$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_ERROR';

		if (!isset($settings[$field]) || (!is_scalar($value) && $value !== null) || $value === false)
		{
			$this->setError(Text::sprintf($error, $field));
			return false;
		}

		$column = $settings[$field];

		// Table::load compares NULL as a quoted value, not with SQL IS NULL.
		if ($value === null)
		{
			if ($column['nullable'])
			{
				return null;
			}

			$this->setError(Text::sprintf($error, $field));
			return false;
		}

		$isText = in_array($column['type'], ['CHAR', 'VARCHAR'], true);
		$matched = [];

		try
		{
			$table = $this->getTable();

			// At most 100 collision queries, including the original requested value.
			for ($attempt = 0; $attempt < 100; $attempt++)
			{
				if ($isText && $column['length'] !== null
					&& StringHelper::strlen((string) $value) > $column['length'])
				{
					$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_LENGTH';
					break;
				}

				$loaded = $table->load([$field => $value]);

				// A failed query/bind is never evidence that a value is available.
				if ($table->getError())
				{
					$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_ERROR';
					break;
				}

				if (!$loaded)
				{
					return $value;
				}

				$key = $table->getKeyName();
				$primaryKey = (string) $table->$key;

				// Detect coercion/collation matches that suffixing cannot escape.
				if (isset($matched[$primaryKey]))
				{
					$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_CONFLICT';
					break;
				}

				$matched[$primaryKey] = true;

				if ($field === 'guid')
				{
					$value = $this->generateUniqueGuid();
				}
				elseif ($isText && $column['length'] !== null)
				{
					$value = StringHelper::increment((string) $value);
				}
				else
				{
					$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_CONFLICT';
					break;
				}

				$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_LIMIT';
			}
		}
		catch (\Throwable $exception)
		{
			$error = 'COM_COMPONENTBUILDER_UNIQUE_FIELD_ERROR';
		}

		$this->setError(Text::sprintf($error, $field));
		return false;
	}

	/**
	 * Method to change the title
	 *
	 * @param   string   $title   The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 */
	protected function _generateNewTitle($title)
	{

		// Alter the title
		$table = $this->getTable();

		while ($table->load(['title' => $title]))
		{
			$title = StringHelper::increment($title);
		}

		return $title;
	}
}
