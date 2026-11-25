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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Module;


use Joomla\Filesystem\Folder;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Language\Set;
use VDM\Joomla\Componentbuilder\Compiler\Language\Purge;
use VDM\Joomla\Componentbuilder\Compiler\Language\Translation;
use VDM\Joomla\Componentbuilder\Compiler\Language\Multilingual;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface as Event;
use VDM\Joomla\Componentbuilder\Compiler\Creator\FieldsetExtension;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Languages;
use VDM\Joomla\Componentbuilder\Compiler\Builder\Multilingual as BuilderMultilingual;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Counter;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\File;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Interfaces\Architecture\Module\MainXMLInterface;


/**
 * Joomla 6 Module Main XML Class
 * 
 * @since 5.1.2
 */
final class MainXML implements MainXMLInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 5.1.2
	 */
	protected Config $config;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 5.1.2
	 */
	protected Language $language;

	/**
	 * The Set Class.
	 *
	 * @var   Set
	 * @since 5.1.2
	 */
	protected Set $set;

	/**
	 * The Purge Class.
	 *
	 * @var   Purge
	 * @since 5.1.2
	 */
	protected Purge $purge;

	/**
	 * The Translation Class.
	 *
	 * @var   Translation
	 * @since 5.1.2
	 */
	protected Translation $translation;

	/**
	 * The Multilingual Class.
	 *
	 * @var   Multilingual
	 * @since 5.1.2
	 */
	protected Multilingual $multilingual;

	/**
	 * The EventInterface Class.
	 *
	 * @var   Event
	 * @since 5.1.2
	 */
	protected Event $event;

	/**
	 * The FieldsetExtension Class.
	 *
	 * @var   FieldsetExtension
	 * @since 5.1.2
	 */
	protected FieldsetExtension $fieldsetextension;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 5.1.2
	 */
	protected ContentOne $contentone;

	/**
	 * The Languages Class.
	 *
	 * @var   Languages
	 * @since 5.1.2
	 */
	protected Languages $languages;

	/**
	 * The Multilingual Class.
	 *
	 * @var   BuilderMultilingual
	 * @since 5.1.2
	 */
	protected BuilderMultilingual $buildermultilingual;

	/**
	 * The Counter Class.
	 *
	 * @var   Counter
	 * @since 5.1.2
	 */
	protected Counter $counter;

	/**
	 * The File Class.
	 *
	 * @var   File
	 * @since 5.1.2
	 */
	protected File $file;

	/**
	 * Constructor.
	 *
	 * @param Config                $config                The Config Class.
	 * @param Language              $language              The Language Class.
	 * @param Set                   $set                   The Set Class.
	 * @param Purge                 $purge                 The Purge Class.
	 * @param Translation           $translation           The Translation Class.
	 * @param Multilingual          $multilingual          The Multilingual Class.
	 * @param Event                 $event                 The EventInterface Class.
	 * @param FieldsetExtension     $fieldsetextension     The FieldsetExtension Class.
	 * @param ContentOne            $contentone            The ContentOne Class.
	 * @param Languages             $languages             The Languages Class.
	 * @param BuilderMultilingual   $buildermultilingual   The Multilingual Class.
	 * @param Counter               $counter               The Counter Class.
	 * @param File                  $file                  The File Class.
	 *
	 * @since 5.1.2
	 */
	public function __construct(Config $config, Language $language, Set $set, Purge $purge,
		Translation $translation, Multilingual $multilingual,
		Event $event, FieldsetExtension $fieldsetextension,
		ContentOne $contentone, Languages $languages,
		BuilderMultilingual $buildermultilingual,
		Counter $counter, File $file)
	{
		$this->config = $config;
		$this->language = $language;
		$this->set = $set;
		$this->purge = $purge;
		$this->translation = $translation;
		$this->multilingual = $multilingual;
		$this->event = $event;
		$this->fieldsetextension = $fieldsetextension;
		$this->contentone = $contentone;
		$this->languages = $languages;
		$this->buildermultilingual = $buildermultilingual;
		$this->counter = $counter;
		$this->file = $file;
	}

	/**
	 * Generates the main XML for the module.
	 *
	 * @param object $module The module object.
	 *
	 * @return string The generated XML.
	 * @since  5.1.2
	 */
	public function get(object $module): string
	{
		$config_fields = $this->buildConfigFields($module);
		$add_component_path = $this->shouldAddComponentPath($module);
		$language_files = $this->generateLanguageFiles($module);

		$xml = $this->generateScriptAndSqlXml($module);
		$xml .= $this->generateLanguageXml($module, $language_files);
		$xml .= $this->generateFileXml($module, $language_files);
		$xml .= $this->generateConfigXml($module, $config_fields, $add_component_path);
		$xml .= $this->generateUpdateServerXml($module);

		return $xml;
	}

	/**
	 * Build configuration fields XML.
	 *
	 * @param object $module The module object.
	 *
	 * @return array The configuration fields.
	 * @since  5.1.2
	 */
	protected function buildConfigFields(object $module): array
	{
		$configFields = [];
		if (!isset($module->config_fields) || !ArrayHelper::check($module->config_fields))
		{
			return $configFields;
		}

		$dbKey = 'yyy';
		$addScriptsField = true;
		$add_scripts_field = $module->add_scripts_field ?? null;

		foreach ($module->config_fields as $fieldName => $fieldsets)
		{
			foreach ($fieldsets as $fieldset => $fields)
			{
				$xmlFields = $this->fieldsetextension->get($module, $fields, $dbKey);

				if ($addScriptsField && $add_scripts_field)
				{
					$namespace = $this->config->namespace_prefix . '\\Module\\' . $module->namespace
						. '\\' . $module->target_client_namespace . '\\Field';
					$xmlFields .= PHP_EOL . Indent::_(2) . '<field name="jcbassets" type="'
						. $module->class_name . 'engine" addfieldprefix="' . $namespace . '" />';
					$addScriptsField = false;
				}

				if (isset($xmlFields) && StringHelper::check($xmlFields))
				{
					$configFields["{$fieldName}{$fieldset}"] = $xmlFields;
				}
				$dbKey++;
			}
		}

		return $configFields;
	}

	/**
	 * Determine if the component path should be added.
	 *
	 * @param object $module The module object.
	 *
	 * @return bool True if the component path should be added, false otherwise.
	 * @since  5.1.2
	 */
	protected function shouldAddComponentPath(object $module): bool
	{
		if (!isset($module->config_fields) || !ArrayHelper::check($module->config_fields) ||
			!isset($module->fieldsets_paths) || !ArrayHelper::check($module->fieldsets_paths))
		{
			return false;
		}

		foreach ($module->config_fields as $fieldName => $fieldsets)
		{
			foreach ($fieldsets as $fieldset => $fields)
			{
				if (isset($module->fieldsets_paths["{$fieldName}{$fieldset}"]) &&
					$module->fieldsets_paths["{$fieldName}{$fieldset}"] == 1)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Generate XML for script and SQL files.
	 *
	 * @param object $module The module object.
	 *
	 * @return string The XML for script and SQL files.
	 * @since  5.1.2
	 */
	protected function generateScriptAndSqlXml(object $module): string
	{
		$xml = '';

		if ($module->add_install_script)
		{
			$xml .= PHP_EOL . PHP_EOL . Indent::_(1) . '<!--' . Line::_(
				__LINE__,__CLASS__
			) . ' Scripts to run on installation -->';
			$xml .= PHP_EOL . Indent::_(1) . '<scriptfile>script.php</scriptfile>';
		}

		if ($module->add_sql)
		{
			$xml .= PHP_EOL . PHP_EOL . Indent::_(1) . '<!--' . Line::_(
				__LINE__,__CLASS__
			) . ' Runs on install -->';
			$xml .= PHP_EOL . Indent::_(1) . '<install>';
			$xml .= PHP_EOL . Indent::_(2) . '<sql>';
			$xml .= PHP_EOL . Indent::_(3) . '<file driver="mysql" charset="utf8">sql/mysql/install.sql</file>';
			$xml .= PHP_EOL . Indent::_(2) . '</sql>';
			$xml .= PHP_EOL . Indent::_(1) . '</install>';
		}

		if ($module->add_sql_uninstall)
		{
			$xml .= PHP_EOL . PHP_EOL . Indent::_(1) . '<!--' . Line::_(
				__LINE__,__CLASS__
			) . ' Runs on uninstall -->';
			$xml .= PHP_EOL . Indent::_(1) . '<uninstall>';
			$xml .= PHP_EOL . Indent::_(2) . '<sql>';
			$xml .= PHP_EOL . Indent::_(3) . '<file driver="mysql" charset="utf8">sql/mysql/uninstall.sql</file>';
			$xml .= PHP_EOL . Indent::_(2) . '</sql>';
			$xml .= PHP_EOL . Indent::_(1) . '</uninstall>';
		}

		return $xml;
	}

	/**
	 * Generate XML for language files.
	 *
	 * @param object $module           The module object.
	 * @param array  $languageFiles    The language files.
	 *
	 * @return string The XML for language files.
	 * @since  5.1.2
	 */
	protected function generateLanguageXml(object $module, array $languageFiles): string
	{
		$xml = '';

		if (ArrayHelper::check($languageFiles))
		{
			$xml .= PHP_EOL . PHP_EOL . Indent::_(1) . '<!--' . Line::_(
				__LINE__, __CLASS__
			) . ' Language files -->';
			$xml .= PHP_EOL . Indent::_(1) . '<languages folder="language">';

			foreach ($languageFiles as $tag)
			{
				$xml .= PHP_EOL . Indent::_(2) . "<language tag=\"{$tag}\">{$tag}/{$module->file_name}.ini</language>";
				$xml .= PHP_EOL . Indent::_(2) . "<language tag=\"{$tag}\">{$tag}/{$module->file_name}.sys.ini</language>";
			}

			$xml .= PHP_EOL . Indent::_(1) . '</languages>';
		}

		return $xml;
	}

	/**
	 * Generate the XML for the files.
	 *
	 * @param object $module           The module object.
	 * @param array  $languageFiles    The language files.
	 *
	 * @return string The XML for the files.
	 * @since  5.1.2
	 */
	protected function generateFileXml(object $module, array $languageFiles): string
	{
		$files = Folder::files($module->folder_path);
		$folders = Folder::folders($module->folder_path);
		$ignore = ['media', 'services', 'sql', 'language', 'script.php', "{$module->file_name}.xml", "{$module->file_name}.php"];

		$xml = PHP_EOL . PHP_EOL . Indent::_(1) . '<!--' . Line::_(
			__LINE__, __CLASS__
		) . ' Module files -->';

		$xml .= PHP_EOL . Indent::_(1) . '<files>';
		$xml .= PHP_EOL . Indent::_(2) . "<folder module=\"{$module->file_name}\">services</folder>";

		foreach ($files as $file)
		{
			if (!in_array($file, $ignore))
			{
				$xml .= PHP_EOL . Indent::_(2) . "<filename>{$file}</filename>";
			}
		}

		if (!empty($languageFiles))
		{
			$xml .= PHP_EOL . Indent::_(2) . '<folder>language</folder>';
		}

		if ($module->add_sql || $module->add_sql_uninstall)
		{
			$xml .= PHP_EOL . Indent::_(2) . '<folder>sql</folder>';
		}

		foreach ($folders as $folder)
		{
			if (!in_array($folder, $ignore))
			{
				$xml .= PHP_EOL . Indent::_(2) . "<folder>{$folder}</folder>";
			}
		}

		$xml .= PHP_EOL . Indent::_(1) . '</files>';

		if (is_dir($module->folder_path . '/media'))
		{
			$xml .= PHP_EOL . Indent::_(1) . '<media destination="' . $module->folder_name . '" folder="media">';

			foreach (Folder::folders($module->folder_path . '/media') as $folder)
			{
				$xml .= PHP_EOL . Indent::_(2) . '<folder>' . $folder . '</folder>';
			}

			foreach (Folder::files($module->folder_path . '/media') as $file)
			{
				$xml .= PHP_EOL . Indent::_(2) . '<filename>' . $file . '</filename>';
			}

			$xml .= PHP_EOL . Indent::_(1) . '</media>';
		}

		return $xml;
	}

	/**
	 * Generate XML for configuration fields.
	 *
	 * @param object $module           The module object.
	 * @param array  $configFields     The configuration fields.
	 * @param bool   $addComponentPath Whether to add the component path.
	 *
	 * @return string The XML for configuration fields.
	 * @since  5.1.2
	 */
	protected function generateConfigXml(object $module, array $configFields, bool $addComponentPath): string
	{
		$xml = PHP_EOL . PHP_EOL . Indent::_(1) . '<!--' . Line::_(
			__LINE__, __CLASS__
		) . ' Config parameters -->';
		$xml .= $addComponentPath ? PHP_EOL . Indent::_(1) . '<config' : PHP_EOL . Indent::_(1) . '<config>';

		if (!isset($module->config_fields) || !ArrayHelper::check($configFields))
		{
			$xml .= PHP_EOL . Indent::_(1) . '<fields name="params">';
			$xml .= $this->setAdvanceConfigXml($module);
			$xml .= PHP_EOL . Indent::_(1) . '</fields>';
			$xml .= PHP_EOL . Indent::_(1) . '</config>';

			return $xml;
		}

		if ($addComponentPath)
		{
			$namespace = $this->config->namespace_prefix . '\\Component\\' . $this->contentone->get('ComponentNamespace') . '\\Administrator';
			$xml .= PHP_EOL . Indent::_(3) . "addruleprefix=\"{$namespace}\\Rule\"";
			$xml .= PHP_EOL . Indent::_(3) . "addfieldprefix=\"{$namespace}\\Field\">";
			$xml .= PHP_EOL . Indent::_(1) . '>';
		}

		$advance = false;
		foreach ($module->config_fields as $fieldName => $fieldsets)
		{
			$xml .= PHP_EOL . Indent::_(1) . "<fields name=\"{$fieldName}\">";

			foreach ($fieldsets as $fieldset => $fields)
			{
				if ($fieldset === 'advance' && $fieldName === 'params')
				{
					$advance = true;
				}

				$label = $module->fieldsets_label["{$fieldName}{$fieldset}"] ?? $fieldset;

				$xml .= PHP_EOL . Indent::_(1) . "<fieldset name=\"{$fieldset}\" label=\"{$label}\">";

				if (isset($configFields["{$fieldName}{$fieldset}"]))
				{
					$xml .= $configFields["{$fieldName}{$fieldset}"];
				}

				$xml .= PHP_EOL . Indent::_(1) . '</fieldset>';
			}

			if ($fieldName === 'params' && !$advance)
			{
				$advance = true;
				$xml .= $this->setAdvanceConfigXml($module);
			}

			$xml .= PHP_EOL . Indent::_(1) . '</fields>';
		}

		if (!$advance)
		{
			$xml .= PHP_EOL . Indent::_(1) . '<fields name="params">';
			$xml .= $this->setAdvanceConfigXml($module);
			$xml .= PHP_EOL . Indent::_(1) . '</fields>';
		}

		$xml .= PHP_EOL . Indent::_(1) . '</config>';

		return $xml;
	}

	/**
	 * Build the advance field set for the config area of a module
	 *
	 * @param object $module     The module object.
	 *
	 * @return string The XML for advance configuration fields.
	 * @since  5.1.2
	 */
	protected function setAdvanceConfigXml(object $module): string
	{
		$fieldset = PHP_EOL . Indent::_(2) . '<fieldset name="advanced">';
		$fieldset .= PHP_EOL . Indent::_(3) . '<field';
		$fieldset .= PHP_EOL . Indent::_(4) . 'name="layout"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'type="modulelayout"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'label="JFIELD_ALT_LAYOUT_LABEL"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'class="form-select"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'validate="moduleLayout"';
		$fieldset .= PHP_EOL . Indent::_(3) . '/>';

		$fieldset .= PHP_EOL . Indent::_(3) . '<field';
		$fieldset .= PHP_EOL . Indent::_(4) . 'name="moduleclass_sfx"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'type="textarea"';
		$fieldset .= PHP_EOL . Indent::_(4) . "label=\"{$module->moduleclass_sfx_label}\"";
		$fieldset .= PHP_EOL . Indent::_(4) . 'rows="3"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'validate="CssIdentifier"';
		$fieldset .= PHP_EOL . Indent::_(3) . '/>';

		$fieldset .= PHP_EOL . Indent::_(3) . '<field';
		$fieldset .= PHP_EOL . Indent::_(4) . 'name="owncache"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'type="list"';
		$fieldset .= PHP_EOL . Indent::_(4) . "label=\"{$module->caching_label}\"";
		$fieldset .= PHP_EOL . Indent::_(4) . 'default="1"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'filter="integer"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'validate="options"';
		$fieldset .= PHP_EOL . Indent::_(4) . '>';
		$fieldset .= PHP_EOL . Indent::_(4) . '<option value="1">JGLOBAL_USE_GLOBAL</option>';
		$fieldset .= PHP_EOL . Indent::_(4) . "<option value=\"0\">{$module->value_nocaching}</option>";
		$fieldset .= PHP_EOL . Indent::_(3) . '</field>';

		$fieldset .= PHP_EOL . Indent::_(3) . '<field';
		$fieldset .= PHP_EOL . Indent::_(4) . 'name="cache_time"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'type="number"';
		$fieldset .= PHP_EOL . Indent::_(4) . "label=\"{$module->cache_time_label}\"";
		$fieldset .= PHP_EOL . Indent::_(4) . 'default="900"';
		$fieldset .= PHP_EOL . Indent::_(4) . 'filter="integer"';
		$fieldset .= PHP_EOL . Indent::_(3) . '/>';
		$fieldset .= PHP_EOL . Indent::_(2) . '</fieldset>';

		return $fieldset;
	}

	/**
	 * Generate XML for update servers.
	 *
	 * @param object $module The module object.
	 *
	 * @return string The XML for update servers.
	 * @since  5.1.2
	 */
	protected function generateUpdateServerXml(object $module): string
	{
		$xml = '';

		if ($module->add_update_server)
		{
			$xml .= PHP_EOL . PHP_EOL . Indent::_(1) . '<!--' . Line::_(
				__LINE__, __CLASS__
			) . ' Update servers -->';
			$xml .= PHP_EOL . Indent::_(1) . '<updateservers>';
			$xml .= PHP_EOL . Indent::_(2) . "<server type=\"extension\" priority=\"1\" name=\"{$module->official_name}\">{$module->update_server_url}</server>";
			$xml .= PHP_EOL . Indent::_(1) . '</updateservers>';
		}

		return $xml;
	}

	/**
	 * Generate language files.
	 *
	 * @param object $module The module object.
	 *
	 * @return array The language files.
	 * @since  5.1.2
	 */
	protected function generateLanguageFiles(object $module): array
	{
		$languageFiles = [];

		if (!$this->language->exist($module->key))
		{
			return $languageFiles;
		}

		$langContent = $this->language->getTarget($module->key);
		$this->event->trigger('jcb_ce_onBeforeBuildModuleLang', [&$module, &$langContent]);

		$values = array_unique($langContent);
		$this->buildermultilingual->set('modules', $this->multilingual->get($values));

		$langTag = $this->config->get('lang_tag', 'en-GB');
		$this->languages->set("modules.{$langTag}.all", $langContent);
		$this->language->setTarget($module->key, null);

		$this->set->execute($values, $module->guid, 'modules');
		$this->purge->execute($values, $module->guid, 'modules');

		$this->event->trigger('jcb_ce_onBeforeBuildModuleLangFiles', [&$module]);

		if ($this->languages->IsArray('modules'))
		{
			foreach ($this->languages->get('modules') as $tag => $areas)
			{
				$tag = trim($tag);
				foreach ($areas as $area => $languageStrings)
				{
					$fileName = "{$module->file_name}.ini";
					$fileSysName = "{$module->file_name}.sys.ini";
					$total = count($values);
					if ($this->translation->check($tag, $languageStrings, $total, $fileName))
					{
						$lang = array_map(
							fn($langString, $placeholder) => "{$placeholder}=\"{$langString}\"",
							array_values($languageStrings),
							array_keys($languageStrings)
						);

						$path = "{$module->folder_path}/language/{$tag}/";

						if (!is_dir($path))
						{
							Folder::create($path);
							$this->counter->folder++;
						}

						$this->file->write($path . $fileName, implode(PHP_EOL, $lang));
						$this->file->write($path . $fileSysName, implode(PHP_EOL, $lang));

						$this->counter->line += count($lang);
						unset($lang);

						$languageFiles[$tag] = $tag;
					}
				}
			}
		}

		return $languageFiles;
	}
}

