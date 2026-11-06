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

namespace VDM\Joomla\Componentbuilder\Utilities;


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Utilities\GetHelper;


/**
 * Filter Helper
 * 
 * @since 3.2.0
 */
abstract class FilterHelper
{
	/**
	 * get extensions grouped list xml
	 *
	 * @return string   The XML string of Extentions
	 * @since 3.2.0
	 */
	public static function extensions(): string
	{
		// the extension types
		$extensions = array(
			'joomla_component' => 'COM_COMPONENTBUILDER_COMPONENT',
			'joomla_module' => 'COM_COMPONENTBUILDER_MODULE',
			'joomla_plugin' => 'COM_COMPONENTBUILDER_PLUGIN'
		);

		// get the extension values
		foreach ($extensions as $extension => $label)
		{
			${$extension} = self::names($extension);
		}

		$xml = new \DOMDocument();
		$xml->formatOutput = true;

		$root = $xml->createElement('field');
		$root->setAttributeNode(new \DOMAttr('name', 'extension'));
		$root->setAttributeNode(new \DOMAttr('type', 'groupedlist'));
		$root->setAttributeNode(new \DOMAttr('onchange', 'this.form.submit();'));

		$root
			->appendChild($xml->createElement('option', '- ' . Text::_('COM_COMPONENTBUILDER_SELECT_EXTENSION') . ' -'))
			->setAttributeNode(new \DOMAttr('value', ''));

		foreach ($extensions as $extension => $label)
		{
			$extension_node = $xml->createElement('group');
			$extension_node->setAttributeNode(new \DOMAttr('label', $label));
			if (!ArrayHelper::check(${$extension}))
			{
				$extension_node
					->appendChild($xml->createElement('option', '- ' . Text::_('COM_COMPONENTBUILDER_NONE') . ' -'))
					->setAttributeNode(new \DOMAttr('disabled', 'true'));
			}
			else
			{
				foreach (${$extension} as $guid => $element)
				{
					$extension_node
						->appendChild($xml->createElement('option', $element))
						->setAttributeNode(new \DOMAttr('value', $extension . '__' . $guid));
				}
			}
			$root->appendChild($extension_node);
		}
		$xml->appendChild($root);

		return $xml->saveXML();
	}

	/**
	 * Get by type the guids and system names
	 *
	 * @param string       $type       The table name to get system names for
	 * @param string|null  $limiter    The to limit by limiter table
	 *
	 * @return array|null   The array of system name and GUIDs
	 * @since 3.2.0
	 */
	public static function names(string $type, ?string $limiter = null): ?array
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName(array('guid', 'system_name')))
			->from($db->quoteName('#__componentbuilder_' . $type))
			->where($db->quoteName('published') . ' >= 1')
			->order($db->quoteName('modified') . ' desc')
			->order($db->quoteName('created') . ' desc');

		// check if we have a limiter for admin views
		if ($type === 'admin_view' && $limiter)
		{
			// first get all views
			$admin_view_guids = array();

			// if this is a plugin or a module, then no views
			if (strpos($limiter, 'joomla_component') !== false)
			{
				$component = str_replace('joomla_component__', '', $limiter);
				// get the views of this component
				if ($add_views = GetHelper::var('component_admin_views', $component, 'joomla_component', 'addadmin_views'))
				{
					if (JsonHelper::check($add_views))
					{
						$add_views = json_decode($add_views, true);
						if (ArrayHelper::check($add_views))
						{
							foreach($add_views as $add_view)
							{
								if (isset($add_view['adminview']))
								{
									$admin_view_guids[$add_view['adminview']] = $add_view['adminview'];
								}
							}
						}
					}
				}
			}
			// now check if we still have admin views
			if (ArrayHelper::check($admin_view_guids))
			{
				$query->where($db->quoteName('guid') . ' IN ("' . implode('","', $admin_view_guids) . '")');
			}
			else
			{
				return null;
			}
		}

		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			return $db->loadAssocList('guid', 'system_name');
		}

		return null;
	}

	/**
	 * get any area linked GUIDs
	 *
	 * @param string   $guid      The target GUID
	 * @param string   $method    The target method
	 *
	 * @return array|null   The result guids
	 * @since  3.2.0
	 * @since  5.0.4 (changed to guid)
	 **/
	public static function linked($guid, string $method): ?array
	{
		// check if method exist
		if (method_exists(__CLASS__, $method))
		{
			return self::{$method}($guid);
		}

		return null;
	}

	/**
	 * get the substrings of the namespace until the last "\" or "."
	 *
	 * @return array|null   The result substrings
	 * @since 3.2.0
	 **/
	public static function namespaces(): ?array
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select(
				'DISTINCT REPLACE(SUBSTRING('
				. $db->quoteName('namespace')
				. ', 1, LENGTH('
				. $db->quoteName('namespace')
				. ') - LEAST('
				. 'IF(LOCATE('
				. $db->quote('\\')
				. ', ' . $db->quoteName('namespace')
				. ') > 0, LOCATE('
				. $db->quote('\\')
				. ', REVERSE('
				. $db->quoteName('namespace')
				. ')), 0), '
				. 'IF(LOCATE('
				. $db->quote('.')
				. ', ' . $db->quoteName('namespace')
				. ') > 0, LOCATE('
				. $db->quote('.')
				. ', REVERSE('
				. $db->quoteName('namespace')
				. ')), 0))), ".", "\\\") AS trimmed_namespace'
			)
			->from($db->quoteName('#__componentbuilder_power'))
			->where($db->quoteName('published') . ' = 1')
			->order('LENGTH(trimmed_namespace) ASC, trimmed_namespace ASC');
		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			return $db->loadAssocList('trimmed_namespace', 'trimmed_namespace');
		}

		return null;
	}

	/**
	 * get get IDs of powers matching namespaces
	 *
	 * @param string   $namespace    The target namespace
	 *
	 * @return array|null   The result ids
	 * @since 3.2.0
	 **/
	public static function namegroup(string $namespace): ?array
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id')))
			->from($db->quoteName('#__componentbuilder_power'))
			->where($db->quoteName('published') . ' = 1');

		// we get only those that match the owner and repo (smaller set)
		$paths = explode('\\', $namespace);
		foreach ($paths as $path)
		{
			$query->where($db->quoteName('namespace') . ' REGEXP ' . $db->quote($path));
		}

		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			return $db->loadColumn();
		}

		return null;
	}

	/**
	 * get translation extension guids
	 *
	 * @param string   $extension    The target GUID
	 * @param string   $type         The target method
	 *
	 * @return array|null   The result ids
	 * @since 3.2.0
	 **/
	public static function translation(string $extension, string $type): ?array
	{
		// only allow these columns (extension types)
		$columns = array(
			'joomla_component' => 'components',
			'joomla_module' => 'modules',
			'joomla_plugin' => 'plugins'
		);

		// check if the column name is correct
		if (isset($columns[$type]))
		{
			$column = $columns[$type];
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName(['id', $column]))
				->from($db->quoteName('#__componentbuilder_language_translation'))
				->where($db->quoteName($column) . ' != ' . $db->quote(''));

			$db->setQuery($query);
			$db->execute();

			if ($db->getNumRows())
			{
				$results = $db->loadAssocList();
				$matches = [];
				foreach ($results as $k => $v)
				{
					$values = json_decode((string) $v[$column], true);
					if (is_array($values) && in_array($extension, $values, true))
					{
						$matches[(int) $v['id']] = (int) $v['id'];
					}
				}

				// Checks that we found matches
				if (ArrayHelper::check($matches))
				{
					return array_values($matches);
				}
			}
		}

		return null;
	}

	/**
	 * Get identifiers whose stored translations match the requested language.
	 *
	 * @param   string  $language    Exact language tag, all published languages, or none.
	 * @param   bool    $translated  Select translated or untranslated strings.
	 *
	 * @return  array|null  Matching identifiers, or null when there are no matches.
	 *
	 * @since   3.2.0
	 * @since   6.2.1 Match nonblank translation entries instead of serialized substrings.
	 */
	public static function translations(string $language, bool $translated = true): ?array
	{
		if ($language === '' || ($translated && $language === 'none')
			|| (!$translated && $language === 'all'))
		{
			return null;
		}

		$required = [];

		if ($language === 'all')
		{
			$required = self::languages() ?? [];

			// A stale "all" selection must not match every row when no languages exist.
			if ($required === [])
			{
				return null;
			}
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(['id', 'translation']))
			->from($db->quoteName('#__componentbuilder_language_translation'));
		$db->setQuery($query);
		$matches = [];

		// Decode one row at a time; do not retain the table's full JSON payloads.
		foreach ($db->getIterator() as $row)
		{
			$languages = self::translationLanguages($row->translation);

			if ($language === 'all')
			{
				$matchesLanguage = array_diff_key($required, $languages) === [];
			}
			elseif ($language === 'none')
			{
				$matchesLanguage = $languages === [];
			}
			else
			{
				$matchesLanguage = isset($languages[$language]) === $translated;
			}

			if ($matchesLanguage)
			{
				$matches[(int) $row->id] = (int) $row->id;
			}
		}

		return $matches === [] ? null : array_values($matches);
	}

	/**
	 * get available languages
	 *
	 * @return array|null   The result ids
	 * @since 3.2.0
	 **/
	public static function languages(): ?array
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('langtag', 'name')))
			->from($db->quoteName('#__componentbuilder_language'))
			->where($db->quoteName('published') . ' = 1')
			->order($db->quoteName('name') . ' desc');
		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			return $db->loadAssocList('langtag', 'name');
		}

		return null;
	}

	/**
	 * get get IDs of powers link to this path
	 *
	 * @param string   $path    The target PATH
	 *
	 * @return array|null   The result ids
	 * @since 3.2.0
	 **/
	public static function paths(string $path): ?array
	{
		// get all this power ids
		$ids = [];

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id', 'approved_paths')))
			->from($db->quoteName('#__componentbuilder_power'))
			->where($db->quoteName('published') . ' = 1');

		// we get only those that match the owner and repo (smaller set)
		if (($pos = strpos($path, '/')) !== false)
		{
			$owner = substr($path,  0, $pos);
			$repo = substr($path, $pos + 1);
			$query
				->where($db->quoteName('approved_paths') . ' REGEXP ' . $db->quote($owner))
				->where($db->quoteName('approved_paths') . ' REGEXP ' . $db->quote($repo));
		}

		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			$result = $db->loadAssocList('id', 'approved_paths');
			foreach ($result as $id => $paths)
			{
				if (JsonHelper::check($paths))
				{
					$paths = json_decode($paths, true);
					if (ArrayHelper::check($paths) && in_array($path, $paths, true))
					{
						$ids[$id] = $id;
					}
				}
			}

			if (ArrayHelper::check($ids))
			{
				return $ids;
			}
		}

		return null;
	}

	/**
	 * get available repositories of target area
	 *
	 * @param int   $target    The target area
	 *
	 * @return array|null   The result ids
	 * @since 3.2.0
	 **/
	public static function repositories(int $target): ?array
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('repository', 'organisation')))
			->from($db->quoteName('#__componentbuilder_repository'))
			->where($db->quoteName('published') . ' >= 1')
			->where($db->quoteName('target') . ' = ' . $target)
			->order($db->quoteName('ordering') . ' desc');
		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			$items = $db->loadObjectList();
			$options = [];
			foreach($items as $item)
			{
				$path = $item->organisation . '/' . $item->repository;
				$options[$path] = $path;
			}
			return $options;
		}

		return null;
	}

	/**
	 * Identify languages with a usable value in a stored translation subform.
	 *
	 * @param   ?string  $value  JSON subform data, including legacy empty values.
	 *
	 * @return  array<string, true>  Exact language tags with nonblank translations.
	 *
	 * @since   6.2.1
	 */
	private static function translationLanguages(?string $value): array
	{
		$entries = json_decode($value ?? '', true);
		$languages = [];

		if (!is_array($entries))
		{
			return $languages;
		}

		foreach ($entries as $entry)
		{
			if (is_array($entry) && isset($entry['language'], $entry['translation'])
				&& is_string($entry['language']) && trim($entry['language']) !== ''
				&& is_string($entry['translation']) && trim($entry['translation']) !== '')
			{
				$languages[$entry['language']] = true;
			}
		}

		return $languages;
	}

	/**
	 * Get a component admin views GUIDs
	 *
	 * @param string  $guid  The target GUID
	 *
	 * @return array|null  The result guids
	 * @since  3.2.0
	 * @since  5.0.4 (changed to guid)
	 */
	private static function joomla_component_admin_views(string $guid): ?array
	{
		// get all this components views
		$admin_view_guids = [];

		// get the views of this component
		if ($add_views = GetHelper::var('component_admin_views', $guid, 'joomla_component', 'addadmin_views'))
		{
			if (JsonHelper::check($add_views))
			{
				$add_views = json_decode($add_views, true);
				if (ArrayHelper::check($add_views))
				{
					foreach($add_views as $add_view)
					{
						if (isset($add_view['adminview']))
						{
							$admin_view_guids[$add_view['adminview']] = $add_view['adminview'];
						}
					}
				}
			}
		}

		// check that we have fields
		if (ArrayHelper::check($admin_view_guids))
		{
			return array_values($admin_view_guids);
		}

		return null;
	}

	/**
	 * get a component custom admin views GUIDs
	 *
	 * @param string  $guid  The target GUID
	 *
	 * @return array|null  The result guids
	 * @since  3.2.0
	 * @since  5.0.4 (changed to guid)
	 */
	private static function joomla_component_custom_admin_views(string $guid): ?array
	{
		// get all this components views
		$admin_view_guids = [];

		// get the views of this component
		if ($add_views = GetHelper::var('component_custom_admin_views', $guid, 'joomla_component', 'addcustom_admin_views'))
		{
			if (JsonHelper::check($add_views))
			{
				$add_views = json_decode($add_views, true);
				if (ArrayHelper::check($add_views))
				{
					foreach($add_views as $add_view)
					{
						if (isset($add_view['customadminview']))
						{
							$admin_view_guids[$add_view['customadminview']] = $add_view['customadminview'];
						}
					}
				}
			}
		}

		// check that we have fields
		if (ArrayHelper::check($admin_view_guids))
		{
			return array_values($admin_view_guids);
		}

		return null;
	}

	/**
	 * get a component site views GUIDs
	 *
	 * @param string  $guid  The target GUID
	 *
	 * @return array|null  The result guids
	 * @since  3.2.0
	 * @since  5.0.4 (changed to guid)
	 */
	private static function joomla_component_site_views(string $guid): ?array
	{
		// get all this components views
		$admin_view_guids = [];

		// get the views of this component
		if ($add_views = GetHelper::var('component_site_views', $guid, 'joomla_component', 'addsite_views'))
		{
			if (JsonHelper::check($add_views))
			{
				$add_views = json_decode($add_views, true);
				if (ArrayHelper::check($add_views))
				{
					foreach($add_views as $add_view)
					{
						if (isset($add_view['siteview']))
						{
							$admin_view_guids[$add_view['siteview']] = $add_view['siteview'];
						}
					}
				}
			}
		}

		// check that we have fields
		if (ArrayHelper::check($admin_view_guids))
		{
			return array_values($admin_view_guids);
		}

		return null;
	}

	/**
	 * get a component fields GUIDs
	 *
	 * @param string  $guid  The target GUID
	 *
	 * @return array|null  The result guids
	 * @since  3.2.0
	 * @since  5.0.4 (changed to guid)
	 */
	private static function joomla_component(string $guid): ?array
	{
		// we start the field array
		$field_guids = [];

		// first get all views
		$admin_view_guids = [];

		// get the views of this component
		if ($add_views = GetHelper::var('component_admin_views', $guid, 'joomla_component', 'addadmin_views'))
		{
			if (JsonHelper::check($add_views))
			{
				$add_views = json_decode($add_views, true);
				if (ArrayHelper::check($add_views))
				{
					foreach($add_views as $add_view)
					{
						if (isset($add_view['adminview']))
						{
							$admin_view_guids[$add_view['adminview']] = $add_view['adminview'];
						}
					}
				}
			}
		}

		// check that we have views
		if (ArrayHelper::check($admin_view_guids))
		{
			foreach ($admin_view_guids as $admin_view)
			{
				// get all the fields linked to the admin view
				if ($add_fields = GetHelper::var('admin_fields', $admin_view, 'admin_view', 'addfields'))
				{
					if (JsonHelper::check($add_fields))
					{
						$add_fields = json_decode($add_fields, true);
						if (ArrayHelper::check($add_fields))
						{
							foreach($add_fields as $add_field)
							{
								if (isset($add_field['field']))
								{
									$field_guids[$add_field['field']] = $add_field['field'];
								}
							}
						}
					}
				}
			}
		}

		// get config values
		if ($add_config = GetHelper::var('component_config', $guid, 'joomla_component', 'addconfig'))
		{
			if (JsonHelper::check($add_config))
			{
				$add_config = json_decode($add_config, true);
				if (ArrayHelper::check($add_config))
				{
					foreach($add_config as $add_conf)
					{
						if (isset($add_conf['field']))
						{
							$field_guids[$add_conf['field']] = $add_conf['field'];
						}
					}
				}
			}
		}

		// check that we have fields
		if (ArrayHelper::check($field_guids))
		{
			return array_values($field_guids);
		}

		return null;
	}

	/**
	 * get a module fields GUIDs
	 *
	 * @param string  $guid  The target GUID
	 *
	 * @return array|null  The result guids
	 * @since  3.2.0
	 * @since  5.0.4 (changed to guid)
	 */
	private static function joomla_module(string $guid): ?array
	{
		// we start the field array
		$field_guids = [];

		if ($fields = GetHelper::var('joomla_module', $guid, 'guid', 'fields'))
		{
			if (JsonHelper::check($fields))
			{
				$fields = json_decode($fields, true);
				if (ArrayHelper::check($fields))
				{
					foreach($fields as $form)
					{
						if (isset($form['fields']) && ArrayHelper::check($form['fields']))
						{
							foreach ($form['fields'] as $field)
							{
								if (isset($field['field']))
								{
									$field_guids[$field['field']] = $field['field'];
								}
							}
						}
					}
				}
			}
		}

		// check that we have fields
		if (ArrayHelper::check($field_guids))
		{
			return array_values($field_guids);
		}

		return null;
	}

	/**
	 * get a plugin fields GUIDs
	 *
	 * @param string  $guid  The target GUID
	 *
	 * @return array|null  The result guids
	 * @since  3.2.0
	 * @since  5.0.4 (changed to guid)
	 */
	private static function joomla_plugin(string $guid): ?array
	{
		// we start the field array
		$field_guids = [];

		if ($fields = GetHelper::var('joomla_plugin', $guid, 'guid', 'fields'))
		{
			if (JsonHelper::check($fields))
			{
				$fields = json_decode($fields, true);
				if (ArrayHelper::check($fields))
				{
					foreach($fields as $form)
					{
						if (isset($form['fields']) && ArrayHelper::check($form['fields']))
						{
							foreach ($form['fields'] as $field)
							{
								if (isset($field['field']))
								{
									$field_guids[$field['field']] = $field['field'];
								}
							}
						}
					}
				}
			}
		}

		// check that we have fields
		if (ArrayHelper::check($field_guids))
		{
			return array_values($field_guids);
		}

		return null;
	}

	/**
	 * get an admin view fields GUIDs
	 *
	 * @param string  $guid  The target GUID
	 *
	 * @return array|null  The result guids
	 * @since  3.2.0
	 * @since  5.0.4 (changed to guid)
	 */
	private static function admin_view(string $guid): ?array
	{
		// we start the field array
		$field_guids = [];

		// get all the fields linked to the admin view
		if ($add_fields = GetHelper::var('admin_fields', $guid, 'admin_view', 'addfields'))
		{
			if (JsonHelper::check($add_fields))
			{
				$add_fields = json_decode($add_fields, true);
				if (ArrayHelper::check($add_fields))
				{
					foreach($add_fields as $add_field)
					{
						if (isset($add_field['field']))
						{
							$field_guids[$add_field['field']] = $add_field['field'];
						}
					}
				}
			}
		}

		// check that we have fields
		if (ArrayHelper::check($field_guids))
		{
			return array_values($field_guids);
		}

		return null;
	}
}

