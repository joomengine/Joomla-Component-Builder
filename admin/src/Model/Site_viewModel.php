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
use VDM\Joomla\Utilities\GetHelper;
use Joomla\CMS\Access\Exception\NotAllowed;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Site_view Admin Model
 *
 * @since  1.6
 */
class Site_viewModel extends AdminModel
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
				'name',
				'codename',
				'description',
				'note_libraries_selection',
				'libraries',
				'note_add_php_language_string'
			),
			'right' => array(
				'snippet',
				'note_uikit_snippet',
				'note_snippet_usage'
			),
			'fullwidth' => array(
				'default'
			),
			'above' => array(
				'system_name',
				'context'
			),
			'under' => array(
				'not_required'
			),
			'rightside' => array(
				'custom_get',
				'main_get',
				'dynamic_get',
				'dynamic_values'
			)
		),
		'php' => array(
			'fullwidth' => array(
				'add_php_ajax',
				'php_ajaxmethod',
				'ajax_input',
				'add_php_document',
				'php_document',
				'add_php_view',
				'php_view',
				'add_php_jview_display',
				'php_jview_display',
				'add_php_jview',
				'php_jview'
			)
		),
		'javascript_css' => array(
			'fullwidth' => array(
				'add_javascript_file',
				'javascript_file',
				'add_js_document',
				'js_document',
				'add_css_document',
				'css_document',
				'add_css',
				'css'
			)
		),
		'linked_components' => array(
			'fullwidth' => array(
				'note_linked_to_notice'
			)
		),
		'toolbar' => array(
			'left' => array(
				'add_custom_button'
			),
			'right' => array(
				'button_position'
			),
			'fullwidth' => array(
				'note_custom_toolbar_placeholder',
				'custom_button',
				'php_controller',
				'php_model',
				'add_view_toolbar',
				'view_toolbar'
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
		'administrator/components/com_componentbuilder/assets/css/admin.css',
		'administrator/components/com_componentbuilder/assets/css/site_view.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'administrator/components/com_componentbuilder/assets/js/admin.js',
		'media/com_componentbuilder/js/site_view.js'
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
	public $typeAlias = 'com_componentbuilder.site_view';

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
	public function getTable($type = 'site_view', $prefix = 'Administrator', $config = [])
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
			if (($vdm = SessionHelper::get('site_view__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'site_view__' . $id);
				SessionHelper::set('site_view__' . $id, $this->vastDevMod);
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
					if (!($user->authorise('site_view.access', 'com_componentbuilder.site_view.' . $item->id) && $user->authorise('site_view.access', 'com_componentbuilder')) || (!$user->authorise('core.options', 'com_componentbuilder') && !in_array((int) $item->access, $user->getAuthorisedViewLevels())))
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

			if (!empty($item->php_ajaxmethod))
			{
				// base64 Decode php_ajaxmethod.
				$item->php_ajaxmethod = base64_decode($item->php_ajaxmethod);
			}

			if (!empty($item->javascript_file))
			{
				// base64 Decode javascript_file.
				$item->javascript_file = base64_decode($item->javascript_file);
			}

			if (!empty($item->view_toolbar))
			{
				// base64 Decode view_toolbar.
				$item->view_toolbar = base64_decode($item->view_toolbar);
			}

			if (!empty($item->default))
			{
				// base64 Decode default.
				$item->default = base64_decode($item->default);
			}

			if (!empty($item->js_document))
			{
				// base64 Decode js_document.
				$item->js_document = base64_decode($item->js_document);
			}

			if (!empty($item->css_document))
			{
				// base64 Decode css_document.
				$item->css_document = base64_decode($item->css_document);
			}

			if (!empty($item->css))
			{
				// base64 Decode css.
				$item->css = base64_decode($item->css);
			}

			if (!empty($item->php_document))
			{
				// base64 Decode php_document.
				$item->php_document = base64_decode($item->php_document);
			}

			if (!empty($item->php_view))
			{
				// base64 Decode php_view.
				$item->php_view = base64_decode($item->php_view);
			}

			if (!empty($item->php_jview_display))
			{
				// base64 Decode php_jview_display.
				$item->php_jview_display = base64_decode($item->php_jview_display);
			}

			if (!empty($item->php_controller))
			{
				// base64 Decode php_controller.
				$item->php_controller = base64_decode($item->php_controller);
			}

			if (!empty($item->php_jview))
			{
				// base64 Decode php_jview.
				$item->php_jview = base64_decode($item->php_jview);
			}

			if (!empty($item->php_model))
			{
				// base64 Decode php_model.
				$item->php_model = base64_decode($item->php_model);
			}

			if (!empty($item->custom_get))
			{
				// Convert the custom_get field to an array.
				$custom_get = new Registry;
				$custom_get->loadString($item->custom_get);
				$item->custom_get = $custom_get->toArray();
			}

			if (!empty($item->libraries))
			{
				// Convert the libraries field to an array.
				$libraries = new Registry;
				$libraries->loadString($item->libraries);
				$item->libraries = $libraries->toArray();
			}

			if (!empty($item->ajax_input))
			{
				// Convert the ajax_input field to an array.
				$ajax_input = new Registry;
				$ajax_input->loadString($item->ajax_input);
				$item->ajax_input = $ajax_input->toArray();
			}

			if (!empty($item->custom_button))
			{
				// Convert the custom_button field to an array.
				$custom_button = new Registry;
				$custom_button->loadString($item->custom_button);
				$item->custom_button = $custom_button->toArray();
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
			if (($vdm = SessionHelper::get('site_view__' . $id)) !== null)
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = UtilitiesStringHelper::random(50);
				SessionHelper::set($this->vastDevMod, 'site_view__' . $id);
				SessionHelper::set('site_view__' . $id, $this->vastDevMod);
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
		$form = $this->loadForm('com_componentbuilder.site_view', 'site_view', $options, $clear, $xpath);

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
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_componentbuilder.site_view.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_componentbuilder')))
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
		if (!$user->authorise('core.edit.created_by', 'com_componentbuilder'))
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
		if (!$user->authorise('core.edit.created', 'com_componentbuilder'))
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

		// update the ajax_input (sub form) layout
		$form->setFieldAttribute('ajax_input', 'layout', ComponentbuilderHelper::getSubformLayout('site_view', 'ajax_input'));

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
		return $this->getCurrentUser()->authorise('core.delete', 'com_componentbuilder.site_view.' . (int) $record->id);
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
			$permission = $user->authorise('core.edit.state', 'com_componentbuilder.site_view.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return parent::canEditState($record);
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
		$access = ($user->authorise('site_view.access', 'com_componentbuilder.site_view.' . (int) $recordId) && $user->authorise('site_view.access', 'com_componentbuilder'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('core.edit', 'com_componentbuilder.site_view.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('core.edit.own', 'com_componentbuilder.site_view.' . (int) $recordId))
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
						if ($user->authorise('core.edit.own', 'com_componentbuilder'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission given, core edit must be checked.
		return $user->authorise('core.edit', $this->option);
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
			$table->created = $date->toSql();
			// set the user
			if ($table->created_by == 0 || empty($table->created_by))
			{
				$table->created_by = $user->id;
			}
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = $this->getDatabase();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__componentbuilder_site_view'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			$table->modified = $date->toSql();
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
		$data = Factory::getApplication()->getUserState('com_componentbuilder.edit.site_view.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the per process of the data
		$this->preprocessData('com_componentbuilder.site_view', $data);

		return $data;
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
			'name' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'description' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'main_get' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'add_php_view' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_ajaxmethod' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'add_php_document' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'javascript_file' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'view_toolbar' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'context' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'default' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'snippet' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'add_php_jview_display' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'custom_get' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_php_jview' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'js_document' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'codename' => ['type' => 'VARCHAR', 'length' => 255, 'nullable' => true],
			'css_document' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_javascript_file' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'css' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_js_document' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'libraries' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'add_css_document' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'dynamic_get' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'add_css' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_php_ajax' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'add_custom_button' => ['type' => 'INT', 'length' => 1, 'nullable' => false],
			'ajax_input' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'button_position' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
			'php_document' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_view' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'custom_button' => ['type' => 'TEXT', 'length' => null, 'nullable' => true],
			'php_jview_display' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_controller' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_jview' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'php_model' => ['type' => 'MEDIUMTEXT', 'length' => null, 'nullable' => true],
			'guid' => ['type' => 'VARCHAR', 'length' => 36, 'nullable' => true],
			'add_view_toolbar' => ['type' => 'TINYINT', 'length' => 1, 'nullable' => false],
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
			$data['guid'] = (string) GetHelper::var('site_view', $data['id'], 'id', 'guid', '=', 'componentbuilder');
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
		while (!GuidHelper::valid($data['guid'], 'site_view', $data['id'], 'componentbuilder'))
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
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_RECORD_LOAD'));

				return false;
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


		// always reset the snippets
		$data['snippet'] = 0;
		// if system name is empty create from name
		if (empty($data['system_name']) || !UtilitiesStringHelper::check($data['system_name']))
		{
			$data['system_name'] = $data['name'];
		}
		// if codename is empty create from name
		if (empty($data['codename']) || !UtilitiesStringHelper::check($data['codename']))
		{
			$data['codename'] = UtilitiesStringHelper::safe($data['name']);
		}
		else
		{
			// always make safe string
			$data['codename'] = UtilitiesStringHelper::safe($data['codename']);
		}
		// if context is empty create from codename
		if (empty($data['context']) || !UtilitiesStringHelper::check($data['context']))
		{
			$data['context'] = $data['codename'];
		}
		else
		{
			// always make safe string
			$data['context'] = UtilitiesStringHelper::safe($data['context']);
		}

		// Set the GUID if empty or not valid
		if (empty($data['guid']) && $data['id'] > 0)
		{
			// get the existing one
			$data['guid'] = (string) GetHelper::var('site_view', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "site_view", $data['id']))
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

		if (array_key_exists('custom_get', $jcbPatchStored)
			&& (!array_key_exists('custom_get', $data) || serialize($data['custom_get']) !== $jcbPatchInput['custom_get']))
		{
			unset($jcbPatchStored['custom_get']);
		}

		// Set the custom_get items to data.
		if (isset($data['custom_get']) && is_array($data['custom_get']) && !array_key_exists('custom_get', $jcbPatchStored))
		{
			$custom_get = new Registry;
			$custom_get->loadArray($data['custom_get']);
			$data['custom_get'] = (string) $custom_get;
		}
		elseif (!isset($data['custom_get']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty custom_get to data
			$data['custom_get'] = '';
		}

		if (array_key_exists('libraries', $jcbPatchStored)
			&& (!array_key_exists('libraries', $data) || serialize($data['libraries']) !== $jcbPatchInput['libraries']))
		{
			unset($jcbPatchStored['libraries']);
		}

		// Set the libraries items to data.
		if (isset($data['libraries']) && is_array($data['libraries']) && !array_key_exists('libraries', $jcbPatchStored))
		{
			$libraries = new Registry;
			$libraries->loadArray($data['libraries']);
			$data['libraries'] = (string) $libraries;
		}
		elseif (!isset($data['libraries']) && !($input->getMethod() === 'PATCH' && Factory::getApplication()->isClient('api')))
		{
			// Set the empty libraries to data
			$data['libraries'] = '';
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

		if (array_key_exists('javascript_file', $jcbPatchStored)
			&& (!array_key_exists('javascript_file', $data) || serialize($data['javascript_file']) !== $jcbPatchInput['javascript_file']))
		{
			unset($jcbPatchStored['javascript_file']);
		}

		// Set the javascript_file string to base64 string.
		if (isset($data['javascript_file']) && !array_key_exists('javascript_file', $jcbPatchStored))
		{
			$data['javascript_file'] = base64_encode($data['javascript_file']);
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

		if (array_key_exists('default', $jcbPatchStored)
			&& (!array_key_exists('default', $data) || serialize($data['default']) !== $jcbPatchInput['default']))
		{
			unset($jcbPatchStored['default']);
		}

		// Set the default string to base64 string.
		if (isset($data['default']) && !array_key_exists('default', $jcbPatchStored))
		{
			$data['default'] = base64_encode($data['default']);
		}

		if (array_key_exists('js_document', $jcbPatchStored)
			&& (!array_key_exists('js_document', $data) || serialize($data['js_document']) !== $jcbPatchInput['js_document']))
		{
			unset($jcbPatchStored['js_document']);
		}

		// Set the js_document string to base64 string.
		if (isset($data['js_document']) && !array_key_exists('js_document', $jcbPatchStored))
		{
			$data['js_document'] = base64_encode($data['js_document']);
		}

		if (array_key_exists('css_document', $jcbPatchStored)
			&& (!array_key_exists('css_document', $data) || serialize($data['css_document']) !== $jcbPatchInput['css_document']))
		{
			unset($jcbPatchStored['css_document']);
		}

		// Set the css_document string to base64 string.
		if (isset($data['css_document']) && !array_key_exists('css_document', $jcbPatchStored))
		{
			$data['css_document'] = base64_encode($data['css_document']);
		}

		if (array_key_exists('css', $jcbPatchStored)
			&& (!array_key_exists('css', $data) || serialize($data['css']) !== $jcbPatchInput['css']))
		{
			unset($jcbPatchStored['css']);
		}

		// Set the css string to base64 string.
		if (isset($data['css']) && !array_key_exists('css', $jcbPatchStored))
		{
			$data['css'] = base64_encode($data['css']);
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

		if (array_key_exists('php_view', $jcbPatchStored)
			&& (!array_key_exists('php_view', $data) || serialize($data['php_view']) !== $jcbPatchInput['php_view']))
		{
			unset($jcbPatchStored['php_view']);
		}

		// Set the php_view string to base64 string.
		if (isset($data['php_view']) && !array_key_exists('php_view', $jcbPatchStored))
		{
			$data['php_view'] = base64_encode($data['php_view']);
		}

		if (array_key_exists('php_jview_display', $jcbPatchStored)
			&& (!array_key_exists('php_jview_display', $data) || serialize($data['php_jview_display']) !== $jcbPatchInput['php_jview_display']))
		{
			unset($jcbPatchStored['php_jview_display']);
		}

		// Set the php_jview_display string to base64 string.
		if (isset($data['php_jview_display']) && !array_key_exists('php_jview_display', $jcbPatchStored))
		{
			$data['php_jview_display'] = base64_encode($data['php_jview_display']);
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

		if (array_key_exists('php_jview', $jcbPatchStored)
			&& (!array_key_exists('php_jview', $data) || serialize($data['php_jview']) !== $jcbPatchInput['php_jview']))
		{
			unset($jcbPatchStored['php_jview']);
		}

		// Set the php_jview string to base64 string.
		if (isset($data['php_jview']) && !array_key_exists('php_jview', $jcbPatchStored))
		{
			$data['php_jview'] = base64_encode($data['php_jview']);
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
