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

namespace VDM\Joomla\Componentbuilder\Compiler\Joomlamodule\JoomlaSix;


use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Customcode;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Gui;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Field;
use VDM\Joomla\Componentbuilder\Compiler\Field\Name as Fieldname;
use VDM\Joomla\Componentbuilder\Compiler\Model\Filesfolders;
use VDM\Joomla\Componentbuilder\Compiler\Model\Libraries;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\Data as Dynamicget;
use VDM\Joomla\Componentbuilder\Compiler\Templatelayout\Data as Templatelayout;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Counter;
use VDM\Joomla\Componentbuilder\Package\Builder\Get as Superpower;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\String\ClassfunctionHelper;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\GetHelper;
use VDM\Joomla\Utilities\GuidHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\ModuleDataInterface;


/**
 * Joomla 6 Module Data Class
 * 
 * @since 5.1.2
 */
final class Data implements ModuleDataInterface
{
	/**
	 * Compiler Joomla Module Data
	 *
	 * @var    array
	 * @since 3.2.0
	 */
	protected array $data = [];

	/**
	 *  Tracking GUID index
	 *
	 * @var    array
	 * @since  5.0.4
	 */
	protected array $index = [];

	/**
	 * The state of retry to loaded modules
	 *
	 * @var    array
	 * @since  5.1.4
	 **/
	protected array $retry = [];

	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * The Customcode Class.
	 *
	 * @var   Customcode
	 * @since 3.2.0
	 */
	protected Customcode $customcode;

	/**
	 * The Gui Class.
	 *
	 * @var   Gui
	 * @since 3.2.0
	 */
	protected Gui $gui;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 3.2.0
	 */
	protected Placeholder $placeholder;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 3.2.0
	 */
	protected Language $language;

	/**
	 * The Field Class.
	 *
	 * @var   Field
	 * @since 3.2.0
	 */
	protected Field $field;

	/**
	 * The Name Class.
	 *
	 * @var   Fieldname
	 * @since 3.2.0
	 */
	protected Fieldname $fieldname;

	/**
	 * The Filesfolders Class.
	 *
	 * @var   Filesfolders
	 * @since 3.2.0
	 */
	protected Filesfolders $filesfolders;

	/**
	 * The Libraries Class.
	 *
	 * @var   Libraries
	 * @since 3.2.0
	 */
	protected Libraries $libraries;

	/**
	 * The Data Class.
	 *
	 * @var   Dynamicget
	 * @since 3.2.0
	 */
	protected Dynamicget $dynamicget;

	/**
	 * The Data Class.
	 *
	 * @var   Templatelayout
	 * @since 3.2.0
	 */
	protected Templatelayout $templatelayout;

	/**
	 * The Counter Class.
	 *
	 * @var   Counter
	 * @since 5.1.4
	 */
	protected Counter $counter;

	/**
	 * Joomla Database Class.
	 *
	 * @var   DatabaseInterface
	 * @since 5.1.2
	 **/
	protected DatabaseInterface $db;

	/**
	 * The Super Class.
	 *
	 * @var   Superpower
	 * @since 5.1.4
	 */
	protected Superpower $superpower;

	/**
	 * Constructor.
	 *
	 * @param Config             $config           The Config Class.
	 * @param Customcode         $customcode       The Customcode Class.
	 * @param Gui                $gui              The Gui Class.
	 * @param Placeholder        $placeholder      The Placeholder Class.
	 * @param Language           $language         The Language Class.
	 * @param Field              $field            The Field Class.
	 * @param Fieldname          $fieldname        The Name Class.
	 * @param Filesfolders       $filesfolders     The Filesfolders Class.
	 * @param Libraries          $libraries        The Libraries Class.
	 * @param Dynamicget         $dynamicget       The Data Class.
	 * @param Templatelayout     $templatelayout   The Data Class.
	 * @param Counter            $counter          The Counter Class.
	 * @param DatabaseInterface  $db               The Joomla Database Class.
	 * @param Superpower         $superpower       A Superpower Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Config $config, Customcode $customcode, Gui $gui,
		Placeholder $placeholder, Language $language,
		Field $field, Fieldname $fieldname,
		Filesfolders $filesfolders, Libraries $libraries,
		Dynamicget $dynamicget, Templatelayout $templatelayout,
		Counter $counter, DatabaseInterface $db, Superpower $superpower)
	{
		$this->config = $config;
		$this->customcode = $customcode;
		$this->gui = $gui;
		$this->placeholder = $placeholder;
		$this->language = $language;
		$this->field = $field;
		$this->fieldname = $fieldname;
		$this->filesfolders = $filesfolders;
		$this->libraries = $libraries;
		$this->dynamicget = $dynamicget;
		$this->templatelayout = $templatelayout;
		$this->counter = $counter;
		$this->db = $db;
		$this->superpower = $superpower;
	}

	/**
	 * Get the Joomla Module/s
	 *
	 * @param   mixed   $module   The module id/guid
	 *
	 * @return  object|array|null    if ID|GUID found it returns object, if no ID|GUID given it returns all set
	 * @since 3.2.0
	 */
	public function get(mixed $module = null)
	{
		if ($module === null && $this->exists())
		{
			return $this->data;
		}
		elseif ($this->exists($module))
		{
			$id = $this->index[$module];
			return $this->data[$id];
		}

		return null;
	}

	/**
	 * Check if the Joomla Module/s exists
	 *
	 * @param   mixed   $module   The module id|guid
	 *
	 * @return  bool    if ID|GUID found it returns true, if no ID|GUID given it returns true if any are set
	 * @since 3.2.0
	 */
	public function exists(mixed $module = null): bool
	{
		if ($module === null)
		{
			return ArrayHelper::check($this->data);
		}
		elseif (isset($this->index[$module]))
		{
			return true;
		}

		return $this->set($module);
	}

	/**
	 * Set the module
	 *
	 * @param   mixed  $module  The module ID/GUID
	 *
	 * @return  bool    true on success
	 * @since   5.0.4
	 */
	public function set(mixed $module): bool
	{
		if (!GuidHelper::valid($module) && !is_numeric($module))
		{
			return false;
		}
		elseif (isset($this->index[$module]))
		{
			return true;
		}

		if (GuidHelper::valid($module))
		{
			$query = $this->getQuery($module, 'guid');
		}
		else
		{
			$query = $this->getQuery($module);
		}

		$data = $this->getData($query);

		if ($data !== null)
		{
			$this->data[$data->id] = $data;
			$this->index[$data->id] = $data->id;
			$this->index[$data->guid] = $data->id;

			$this->counter->module++;

			return true;
		}

		if ($this->attemptRemoteFetch($module))
		{
			return $this->set($module);
		}

		return false;
	}

	/**
	 * Attempt a one-time remote fetch via Superpower.
	 *
	 * @param   mixed  $guid
	 *
	 * @return  bool
	 * @since   5.1.4
	 */
	private function attemptRemoteFetch(mixed $guid): bool
	{
		if (!GuidHelper::valid($guid))
		{
			return false;
		}

		if (!empty($this->retry[$guid]))
		{
			return false;
		}

		$this->retry[$guid] = true;

		$result = $this->superpower->get('joomla_module', [$guid]);

		return !empty($result['added'][$guid]);
	}

	/**
	 * get current module data query
	 *
	 * @param   mixed    $value   The module ID/GUID
	 * @param   string   $key     The type of value
	 *
	 * @return  string  The module data query
	 * @since   5.0.4
	 */
	private function getQuery(mixed $value, string $key = 'id')
	{
		// Create a new query object.
		$query = $this->db->getQuery(true);

		$query->select('a.*');
		$query->select(
			$this->db->quoteName(
				array(
					'f.addfiles',
					'f.addfolders',
					'f.addfilesfullpath',
					'f.addfoldersfullpath',
					'f.addurls',
					'u.version_update',
					'u.id'
				), array(
					'addfiles',
					'addfolders',
					'addfilesfullpath',
					'addfoldersfullpath',
					'addurls',
					'version_update',
					'version_update_id'
				)
			)
		);
		// from these tables
		$query->from('#__componentbuilder_joomla_module AS a');
		$query->join(
			'LEFT', $this->db->quoteName(
				'#__componentbuilder_joomla_module_updates', 'u'
			) . ' ON (' . $this->db->quoteName('a.guid') . ' = '
			. $this->db->quoteName('u.joomla_module') . ')'
		);
		$query->join(
			'LEFT', $this->db->quoteName(
				'#__componentbuilder_joomla_module_files_folders_urls', 'f'
			) . ' ON (' . $this->db->quoteName('a.guid') . ' = '
			. $this->db->quoteName('f.joomla_module') . ')'
		);
		$query->where($this->db->quoteName('a.' . $key) . ' = ' . $this->db->quote($value));
		$query->where($this->db->quoteName('a.published') . ' >= 1');

		return $query;
	}

	/**
	 * get module data
	 *
	 * @param   string   $query   The module query
	 *
	 * @return  object|null  The module data
	 * @since   5.0.4
	 */
	private function getData($query): ?object
	{
		$this->db->setQuery($query);
		$this->db->execute();
		if ($this->db->getNumRows())
		{
			// get the module data
			$module = $this->db->loadObject();
			$id = $module->id;

			// tweak system to set stuff to the module domain
			$_backup_target     = $this->config->build_target;
			$_backup_lang       = $this->config->lang_target;
			$_backup_langPrefix = $this->config->lang_prefix;

			// set some keys
			$module->target_type = 'M0dUl3';
			$module->key         = $module->id . '_' . $module->target_type;

			// update to point to module
			$this->config->build_target = $module->key;
			$this->config->lang_target = $module->key;

			// set version if not set
			if (empty($module->module_version))
			{
				$module->module_version = '1.0.0';
			}

			// set target client
			if ($module->target == 2)
			{
				$module->target_client = 'administrator';
				$module->target_client_namespace = 'Administrator';
			}
			else
			{
				// default is site area
				$module->target_client = 'site';
				$module->target_client_namespace = 'Site';
			}

			// set GUI mapper
			$guiMapper = [
				'table' => 'joomla_module',
				'id'    => (int) $id,
				'type' => 'php'
			];

			// update the name if it has dynamic values
			$module->name = $this->placeholder->update_(
				$this->customcode->update($module->name)
			);

			// set safe class function name
			$module->code_name = ClassfunctionHelper::safe(
				$module->name
			);

			// alias of code name
			$module->class_name = ucfirst(
				$module->code_name
			);

			// set module namespace
			$module->namespace = $module->class_name;

			// set official name
			$module->official_name = StringHelper::safe(
				$module->name, 'W'
			);
			$this->config->set('lang_prefix', 'MOD_' . strtoupper((string) $module->code_name));

			// set lang prefix
			$module->lang_prefix = $this->config->lang_prefix;

			// set module class name
			$module->class_helper_name = ucfirst((string) $module->code_name)
				. 'Helper';
			$module->class_data_name   = ucfirst((string) $module->code_name)
				. 'Data';

			// set module install class name
			$module->installer_class_name = 'mod_' . ucfirst(
					(string) $module->code_name
				) . 'InstallerScript';

			// set module folder name
			$module->folder_name = 'mod_' . strtolower((string) $module->code_name);

			// set the zip name
			$module->zip_name = $module->folder_name . '_v' . str_replace(
					'.', '_', (string) $module->module_version
				) . '__J' . $this->config->joomla_version;

			// set module file name
			$module->file_name = $module->folder_name;

			// set module context
			$module->context = $module->file_name . '.' . $module->id;

			// set official_name lang strings
			$this->language->set(
				$module->key, $this->config->lang_prefix, $module->official_name
			);

			// set some placeholder for this module
			$this->placeholder->set('Module_name', $module->official_name);
			$this->placeholder->set('Module', ucfirst(
				(string) $module->code_name
			));
			$this->placeholder->set('module', strtolower(
				(string) $module->code_name
			));
			$this->placeholder->set('module.version', $module->module_version);
			$this->placeholder->set('module_version', str_replace(
				'.', '_', (string) $module->module_version
			));
			// set description (TODO) add description field to module
			if (!isset($module->description)
				|| !StringHelper::check(
					$module->description
				))
			{
				$module->description = '';
			}
			else
			{
				$module->description = $this->placeholder->update_(
					$this->customcode->update($module->description)
				);
				$this->language->set(
					$module->key, $module->lang_prefix . '_DESCRIPTION',
					$module->description
				);
				$module->description = '<p>' . $module->description
					. '</p>';
			}

			// get author name
			$project_author = $this->config->project_author;

			// set the description
			$module->xml_description = "<h1>" . $module->official_name
				. " (v." . $module->module_version
				. ")</h1> <div style='clear: both;'></div>"
				. $module->description . "<p>Created by <a href='" . trim(
					(string) $this->config->project_website
				) . "' target='_blank'>" . trim(
					(string) OutputFilter::cleanText($project_author)
				) . "</a><br /><small>Development started "
				. Factory::getDate($module->created)->format("jS F, Y")
				. "</small></p>";

			// set xml description
			$this->language->set(
				$module->key, $module->lang_prefix . '_XML_DESCRIPTION',
				$module->xml_description
			);

			// update the readme if set
			if ($module->addreadme == 1 && !empty($module->readme))
			{
				$module->readme = $this->placeholder->update_(
					$this->customcode->update(base64_decode((string) $module->readme))
				);
			}
			else
			{
				$module->addreadme = 0;
				unset($module->readme);
			}

			// get the custom_get
			$module->custom_get = (isset($module->custom_get)
				&& JsonHelper::check($module->custom_get))
				? json_decode((string) $module->custom_get, true) : null;

			if (ArrayHelper::check($module->custom_get))
			{
				$module->custom_get = $this->dynamicget->get(
					$module->custom_get, $module->key, $module->key
				);
			}
			else
			{
				$module->custom_get = null;
			}

			// set helper class details
			if ($module->add_class_helper >= 1
				&& StringHelper::check(
					$module->class_helper_code
				))
			{
				if ($module->add_class_helper_header == 1
					&& StringHelper::check(
						$module->class_helper_header
					))
				{
					// set GUI mapper field
					$guiMapper['field'] = 'class_helper_header';
					// base64 Decode code
					$module->class_helper_header = PHP_EOL
						. $this->gui->set(
							$this->placeholder->update_(
								$this->customcode->update(
									base64_decode(
										(string) $module->class_helper_header
									)
								)
							),
							$guiMapper
						) . PHP_EOL;
				}
				else
				{
					$module->add_class_helper_header = 0;
					$module->class_helper_header     = '';
				}
				// set GUI mapper field
				$guiMapper['field'] = 'class_helper_code';
				// base64 Decode code
				$module->class_helper_code = $this->gui->set(
					$this->placeholder->update_(
						$this->customcode->update(
							base64_decode((string) $module->class_helper_code)
						)
					),
					$guiMapper
				);
				// set class type
				if ($module->add_class_helper == 2)
				{
					$module->class_helper_type = 'abstract class ';
				}
				else
				{
					$module->class_helper_type = 'class ';
				}
			}
			else
			{
				$module->add_class_helper    = 0;
				$module->class_helper_code   = '';
				$module->class_helper_header = '';
			}

			// base64 Decode layout_data
			if (isset($module->layout_data)
				&& StringHelper::check($module->layout_data))
			{
				// set GUI mapper field
				$guiMapper['field'] = 'layout_data';
				$module->layout_data   = $this->gui->set(
					$this->placeholder->update_(
						$this->customcode->update(
							base64_decode((string) $module->layout_data)
						)
					),
					$guiMapper
				);

				// check if we have template or layouts to load
				$this->templatelayout->set(
					$module->layout_data , $module->code_name
				);
			}
			else
			{
				$module->layout_data = '';
			}

			// base64 Decode mod_code
			if (isset($module->mod_code)
				&& StringHelper::check($module->mod_code))
			{
				// set GUI mapper field
				$guiMapper['field'] = 'mod_code';
				$module->mod_code   = $this->gui->set(
					$this->placeholder->update_(
						$this->customcode->update(
							base64_decode((string) $module->mod_code)
						)
					),
					$guiMapper
				);

				// check if we have template or layouts to load
				$this->templatelayout->set(
					$module->mod_code , $module->code_name
				);
			}
			else
			{
				$module->mod_code = "// get the module class sfx";
				$module->mod_code .= PHP_EOL
					. "\$moduleclass_sfx = htmlspecialchars(\$params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');";
				$module->mod_code .= PHP_EOL . "// load the default Tmpl";
				$module->mod_code .= PHP_EOL
					. "require JModuleHelper::getLayoutPath('mod_"
					. strtolower((string) $module->code_name)
					. "', \$params->get('layout', 'default'));";
			}

			// base64 Decode default header
			if (isset($module->default_header)
				&& StringHelper::check(
					$module->default_header
				) && $module->add_default_header == 1)
			{
				// set GUI mapper field
				$guiMapper['field']     = 'default_header';
				$module->default_header = $this->gui->set(
					$this->placeholder->update_(
						$this->customcode->update(
							base64_decode((string) $module->default_header)
						)
					),
					$guiMapper
				);
			}
			else
			{
				$module->add_default_header = 0;
				$module->default_header = '';
			}

			// base64 Decode default
			if (isset($module->default)
				&& StringHelper::check($module->default))
			{
				// set GUI mapper field
				$guiMapper['field'] = 'default';
				$guiMapper['type']  = 'html';
				$module->default    = $this->gui->set(
					$this->placeholder->update_(
						$this->customcode->update(
							base64_decode((string) $module->default)
						)
					),
					$guiMapper
				);

				// check if we have template or layouts to load
				$this->templatelayout->set(
					$module->default , $module->code_name
				);
			}
			else
			{
				$module->default = '<h1>No Tmpl set</h1>';
			}

			// set advance config field languages strings
			$module->moduleclass_sfx_label = $module->lang_prefix . '_FIELD_MODULECLASS_SFX_LABEL';
			$this->language->set(
				$module->key, $module->moduleclass_sfx_label, 'Module Class'
			);
			$module->caching_label = $module->lang_prefix . '_FIELD_CACHING_LABEL';
			$this->language->set(
				$module->key, $module->caching_label, 'Caching'
			);
			$module->value_nocaching = $module->lang_prefix . '_FIELD_VALUE_NOCACHING';
			$this->language->set(
				$module->key, $module->value_nocaching, 'No caching'
			);
			$module->cache_time_label = $module->lang_prefix . '_FIELD_CACHE_TIME_LABEL';
			$this->language->set(
				$module->key, $module->cache_time_label, 'Cache Time'
			);

			// start the config array
			$module->config_fields = [];
			// create the form arrays
			$module->form_files      = [];
			$module->fieldsets_label = [];
			$module->fieldsets_paths = [];
			$module->add_rule_path = [];
			$module->add_field_path = [];
			// set global fields rule to default component path
			$module->fields_rules_paths = 1;
			// set the fields data
			$module->fields = (isset($module->fields)
				&& JsonHelper::check($module->fields))
				? json_decode((string) $module->fields, true) : null;
			if (ArrayHelper::check($module->fields))
			{
				// ket global key
				$key            = $module->key;
				$dynamic_fields = array('fieldset'    => 'basic',
				                        'fields_name' => 'params',
				                        'file'        => 'config');
				foreach ($module->fields as $n => &$form)
				{
					if (isset($form['fields'])
						&& ArrayHelper::check(
							$form['fields']
						))
					{
						// make sure the dynamic_field is set to dynamic_value by default
						foreach (
							$dynamic_fields as $dynamic_field =>
							$dynamic_value
						)
						{
							if (!isset($form[$dynamic_field])
								|| !StringHelper::check(
									$form[$dynamic_field]
								))
							{
								$form[$dynamic_field] = $dynamic_value;
							}
							else
							{
								if ('fields_name' === $dynamic_field
									&& strpos((string) $form[$dynamic_field], '.')
									!== false)
								{
									$form[$dynamic_field]
										= $form[$dynamic_field];
								}
								else
								{
									$form[$dynamic_field]
										= StringHelper::safe(
										$form[$dynamic_field]
									);
								}
							}
						}
						// check if field is external form file
						if (!isset($form['module']) || $form['module'] != 1)
						{
							// now build the form key
							$unique = $form['file'] . $form['fields_name']
								. $form['fieldset'];
						}
						else
						{
							// now build the form key
							$unique = $form['fields_name']
								. $form['fieldset'];
						}
						// set global fields rule path switches
						if ($module->fields_rules_paths == 1
							&& isset($form['fields_rules_paths'])
							&& $form['fields_rules_paths'] == 2)
						{
							$module->fields_rules_paths = 2;
						}
						// set where to path is pointing
						$module->fieldsets_paths[$unique]
							= $form['fields_rules_paths'];
						// check for extra rule paths
						if (isset($form['addrulepath'])
							&& ArrayHelper::check($form['addrulepath']))
						{
							foreach ($form['addrulepath'] as $add_rule_path)
							{
								if (StringHelper::check($add_rule_path['path']))
								{
									$module->add_rule_path[$unique] = $add_rule_path['path'];
								}
							}
						}
						// check for extra field paths
						if (isset($form['addfieldpath'])
							&& ArrayHelper::check($form['addfieldpath']))
						{
							foreach ($form['addfieldpath'] as $add_field_path)
							{
								if (StringHelper::check($add_field_path['path']))
								{
									$module->add_field_path[$unique] = $add_field_path['path'];
								}
							}
						}
						// add the label if set to lang
						if (isset($form['label'])
							&& StringHelper::check(
								$form['label']
							))
						{
							$module->fieldsets_label[$unique]
								= $this->language->key($form['label']);
						}
						// build the fields
						$form['fields'] = array_map(
							function ($field) use ($key, $unique) {
								// make sure the alias and title is 0
								$field['alias'] = 0;
								$field['title'] = 0;
								// set the field details
								$this->field->set(
									$field, $key, $key, $unique
								);
								// update the default if set
								if (StringHelper::check(
										$field['custom_value']
									)
									&& isset($field['settings']))
								{
									if (($old_default
											= GetHelper::between(
											$field['settings']->xml,
											'default="', '"', false
										)) !== false)
									{
										// replace old default
										$field['settings']->xml
											= str_replace(
											'default="' . $old_default
											. '"', 'default="'
											. $field['custom_value'] . '"',
											(string) $field['settings']->xml
										);
									}
									else
									{
										// add the default (hmmm not ideal but okay it should work)
										$field['settings']->xml
											= 'default="'
											. $field['custom_value'] . '" '
											. $field['settings']->xml;
									}
								}
								unset($field['custom_value']);

								// return field
								return $field;
							}, array_values($form['fields'])
						);
						// check if field is external form file
						if (!isset($form['module']) || $form['module'] != 1)
						{
							// load the form file
							if (!isset($module->form_files[$form['file']]))
							{
								$module->form_files[$form['file']]
									= [];
							}
							if (!isset($module->form_files[$form['file']][$form['fields_name']]))
							{
								$module->form_files[$form['file']][$form['fields_name']]
									= [];
							}
							if (!isset($module->form_files[$form['file']][$form['fields_name']][$form['fieldset']]))
							{
								$module->form_files[$form['file']][$form['fields_name']][$form['fieldset']]
									= [];
							}
							// do some house cleaning (for fields)
							foreach ($form['fields'] as $field)
							{
								// so first we lock the field name in
								$this->fieldname->get(
									$field, $module->key, $unique
								);
								// add the fields to the global form file builder
								$module->form_files[$form['file']][$form['fields_name']][$form['fieldset']][]
									= $field;
							}
							// remove form
							unset($module->fields[$n]);
						}
						else
						{
							// load the config form
							if (!isset($module->config_fields[$form['fields_name']]))
							{
								$module->config_fields[$form['fields_name']]
									= [];
							}
							if (!isset($module->config_fields[$form['fields_name']][$form['fieldset']]))
							{
								$module->config_fields[$form['fields_name']][$form['fieldset']]
									= [];
							}
							// do some house cleaning (for fields)
							foreach ($form['fields'] as $field)
							{
								// so first we lock the field name in
								$this->fieldname->get(
									$field, $module->key, $unique
								);
								// add the fields to the config builder
								$module->config_fields[$form['fields_name']][$form['fieldset']][]
									= $field;
							}
							// remove form
							unset($module->fields[$n]);
						}
					}
					else
					{
						unset($module->fields[$n]);
					}
				}
			}
			unset($module->fields);

			// set files and folders
			$this->filesfolders->set($module);

			// set libraries
			$this->libraries->set($module->code_name, $module);

			// add PHP in module install
			$module->add_install_script = true;
			$addScriptMethods = [
				'php_script',
				'php_preflight',
				'php_postflight',
				'php_method'
			];
			$addScriptTypes = [
				'install',
				'update',
				'uninstall'
			];
			// the next are php placeholders
			$guiMapper['type'] = 'php';
			foreach ($addScriptMethods as $scriptMethod)
			{
				foreach ($addScriptTypes as $scriptType)
				{
					if (isset($module->{'add_' . $scriptMethod . '_' . $scriptType})
						&& $module->{'add_' . $scriptMethod . '_' . $scriptType} == 1
						&& StringHelper::check(
							$module->{$scriptMethod . '_' . $scriptType}
						))
					{
						// set GUI mapper field
						$guiMapper['field'] = $scriptMethod . '_' . $scriptType;
						$module->{$scriptMethod . '_' . $scriptType} = $this->gui->set(
							$this->placeholder->update_(
								$this->customcode->update(
									base64_decode(
										(string) $module->{$scriptMethod . '_' . $scriptType}
									)
								)
							),
							$guiMapper
						);
					}
					else
					{
						unset($module->{$scriptMethod . '_' . $scriptType});
						$module->{'add_' . $scriptMethod . '_' . $scriptType} = 0;
					}
				}
			}

			// add_sql
			if ($module->add_sql == 1
				&& StringHelper::check($module->sql))
			{
				$module->sql = $this->placeholder->update_(
					$this->customcode->update(base64_decode((string) $module->sql))
				);
			}
			else
			{
				unset($module->sql);
				$module->add_sql = 0;
			}

			// add_sql_uninstall
			if ($module->add_sql_uninstall == 1
				&& StringHelper::check(
					$module->sql_uninstall
				))
			{
				$module->sql_uninstall = $this->placeholder->update_(
					$this->customcode->update(
						base64_decode((string) $module->sql_uninstall)
					)
				);
			}
			else
			{
				unset($module->sql_uninstall);
				$module->add_sql_uninstall = 0;
			}

			// update the URL of the update_server if set
			if ($module->add_update_server == 1
				&& StringHelper::check(
					$module->update_server_url
				))
			{
				$module->update_server_url = $this->placeholder->update_(
					$this->customcode->update($module->update_server_url)
				);
			}

			// add the update/sales server FTP details if that is the expected protocol
			$serverArray = array('update_server', 'sales_server');
			foreach ($serverArray as $server)
			{
				if ($module->{'add_' . $server} == 1
					&& is_numeric(
						$module->{$server}
					)
					&& $module->{$server} > 0)
				{
					// get the server protocol
					$module->{$server . '_protocol'}
						= GetHelper::var(
						'server', (int) $module->{$server}, 'id', 'protocol'
					);
				}
				else
				{
					$module->{$server} = 0;
					// only change this for sales server (update server can be added locally to the zip file)
					if ('sales_server' === $server)
					{
						$module->{'add_' . $server} = 0;
					}
					$module->{$server . '_protocol'} = 0;
				}
			}

			// set the update server stuff (TODO)
			// update_server_xml_path
			// update_server_xml_file_name

			// rest globals
			$this->config->build_target = $_backup_target;
			$this->config->lang_target = $_backup_lang;
			$this->config->lang_prefix = $_backup_langPrefix;

			$this->placeholder->remove('Module_name');
			$this->placeholder->remove('Module');
			$this->placeholder->remove('module');
			$this->placeholder->remove('module.version');
			$this->placeholder->remove('module_version');

			return $module;
		}

		return null;
	}
}

