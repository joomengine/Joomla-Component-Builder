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

namespace VDM\Joomla\Componentbuilder\Compiler\Creator;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Field;
use VDM\Joomla\Componentbuilder\Compiler\Field\Groups;
use VDM\Joomla\Componentbuilder\Compiler\Field\Name;
use VDM\Joomla\Componentbuilder\Compiler\Field\TypeName;
use VDM\Joomla\Componentbuilder\Compiler\Field\Attributes;
use VDM\Joomla\Componentbuilder\Compiler\Field\ModalSelect;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Xml;
use VDM\Joomla\Componentbuilder\Compiler\Creator\CustomFieldTypeFile;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Counter;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ComponentFields;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\String\FieldHelper;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\GuidHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Creator\Fieldtypeinterface;


/**
 * Field Simple XML Creator Class
 * 
 * @since 3.2.0
 */
final class FieldXML implements Fieldtypeinterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 3.2.0
	 */
	protected Config $config;

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
	 * The Groups Class.
	 *
	 * @var   Groups
	 * @since 3.2.0
	 */
	protected Groups $groups;

	/**
	 * The Name Class.
	 *
	 * @var   Name
	 * @since 3.2.0
	 */
	protected Name $name;

	/**
	 * The TypeName Class.
	 *
	 * @var   TypeName
	 * @since 3.2.0
	 */
	protected TypeName $typename;

	/**
	 * The Attributes Class.
	 *
	 * @var   Attributes
	 * @since 3.2.0
	 */
	protected Attributes $attributes;

	/**
	 * The ModalSelect Class.
	 *
	 * @var   ModalSelect
	 * @since 5.1.1
	 */
	protected ModalSelect $modalselect;

	/**
	 * The Xml Class.
	 *
	 * @var   Xml
	 * @since 3.2.0
	 */
	protected Xml $xml;

	/**
	 * The CustomFieldTypeFile Class.
	 *
	 * @var   CustomFieldTypeFile
	 * @since 3.2.0
	 */
	protected CustomFieldTypeFile $customfieldtypefile;

	/**
	 * The Counter Class.
	 *
	 * @var   Counter
	 * @since 3.2.0
	 */
	protected Counter $counter;

	/**
	 * The ComponentFields Class.
	 *
	 * @var   ComponentFields
	 * @since 5.1.1
	 */
	protected ComponentFields $componentfields;

	/**
	 * Constructor.
	 *
	 * @param Config                $config                The Config Class.
	 * @param Language              $language              The Language Class.
	 * @param Field                 $field                 The Field Class.
	 * @param Groups                $groups                The Groups Class.
	 * @param Name                  $name                  The Name Class.
	 * @param TypeName              $typename              The TypeName Class.
	 * @param Attributes            $attributes            The Attributes Class.
	 * @param ModalSelect           $modalselect           The ModalSelect Class.
	 * @param Xml                   $xml                   The Xml Class.
	 * @param CustomFieldTypeFile   $customfieldtypefile   The CustomFieldTypeFile Class.
	 * @param Counter               $counter               The Counter Class.
	 * @param ComponentFields       $componentfields       The Component Fields Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Config $config, Language $language, Field $field,
		Groups $groups, Name $name, TypeName $typename, Attributes $attributes, ModalSelect $modalselect,
		Xml $xml, CustomFieldTypeFile $customfieldtypefile, Counter $counter, ComponentFields $componentfields)
	{
		$this->config = $config;
		$this->language = $language;
		$this->field = $field;
		$this->groups = $groups;
		$this->name = $name;
		$this->typename = $typename;
		$this->attributes = $attributes;
		$this->modalselect = $modalselect;
		$this->xml = $xml;
		$this->customfieldtypefile = $customfieldtypefile;
		$this->counter = $counter;
		$this->componentfields = $componentfields;
	}

	/**
	 * Create a field with simpleXMLElement class
	 *
	 * @param   string      $setType          The set of fields type
	 * @param   array       $fieldAttributes  The field values
	 * @param   string      $name             The field name
	 * @param   string      $typeName         The field type
	 * @param   string      $langView         The language string of the view
	 * @param   string      $nameSingleCode   The single view name
	 * @param   string      $nameListCode     The list view name
	 * @param   array       $placeholders     The place holder and replace values
	 * @param   array|null  $optionArray      The option bucket array used to set the field options if needed.
	 * @param   array|null  $custom           Used when field is from config
	 * @param   string      $taber            The tabs to add in layout
	 *
	 * @return  \stdClass   The field in xml object
	 * @since   3.2.0
	 */
	public function get(string $setType, array &$fieldAttributes, string $name,
		string $typeName, string $langView, string $nameSingleCode, string $nameListCode,
		array $placeholders, ?array &$optionArray, ?array $custom = null, string $taber = ''): \stdClass
	{
		$this->counter->field++;

		switch ($setType)
		{
			case 'option':
				return $this->buildOptionField($fieldAttributes, $name, $typeName, $langView,
					$nameSingleCode, $nameListCode, $placeholders, $optionArray);

			case 'plain':
				return $this->buildPlainField($fieldAttributes, $name, $typeName);

			case 'spacer':
				return $this->buildSpacerField($fieldAttributes, $name, $typeName);

			case 'special':
				$subform = []; // new subform starts here
				return $this->buildSpecialField($fieldAttributes, $name, $subform, $typeName, $langView,
					$nameSingleCode, $nameListCode, $placeholders);

			case 'custom':
				return $this->buildCustomField($fieldAttributes, $name, $typeName, $langView,
					$nameSingleCode, $nameListCode, $placeholders, $optionArray, $custom);

			default:
				return new \stdClass();
		}
	}

	/**
	 * Build an option field with grouped or plain options.
	 *
	 * @param   array       $fieldAttributes  The field values
	 * @param   string      $name             The field name
	 * @param   string      $typeName         The field type
	 * @param   string      $langView         The language string of the view
	 * @param   string      $nameSingleCode   The single view name
	 * @param   string      $nameListCode     The list view name
	 * @param   array       $placeholders     The place holder and replace values
	 * @param   array|null  $optionArray      The option bucket array used to set the field options if needed.
	 *
	 * @return  \stdClass   The field in xml object
	 * @since   5.1.1
	 */
	private function buildOptionField(array &$fieldAttributes, string $name, string $typeName,
		string $langView, string $nameSingleCode, string $nameListCode,
		array $placeholders, ?array &$optionArray): \stdClass
	{
		$field = new \stdClass();
		$field->fieldXML = new \SimpleXMLElement('<field/>');
		$field->comment = Line::_(__LINE__, __CLASS__) . " " . ucfirst($name)
			. " Field. Type: " . StringHelper::safe($typeName, 'F') . ". (joomla)";

		foreach ($fieldAttributes as $property => $value)
		{
			if ($property !== 'option')
			{
				$field->fieldXML->addAttribute($property, $value);
			}
			elseif ($property === 'option')
			{
				$this->xml->comment(
					$field->fieldXML,
					Line::_(__LINE__, __CLASS__) . " Option Set."
				);

				if (
					strtolower($typeName) === 'groupedlist'
					&& strpos((string) $value, ',') !== false
					&& strpos((string) $value, '@@') !== false
				)
				{
					$this->buildGroupedOptionSet($field->fieldXML, (string) $value, $langView, $typeName, $optionArray);
				}
				elseif (strpos((string) $value, ',') !== false)
				{
					$this->buildMultipleOptions($field->fieldXML, (string) $value, $langView, $optionArray);
				}
				else
				{
					$this->buildSingleOption($field->fieldXML, (string) $value, $langView, $optionArray);
				}
			}
		}

		if (!$field->fieldXML->count() && $this->groups->check($typeName, 'list'))
		{
			$this->xml->comment(
				$field->fieldXML,
				Line::_(__LINE__, __CLASS__) . " No Manual Options Were Added In Field Settings."
			);
		}

		return $field;
	}

	/**
	 * Build a plain field (standard Joomla field without options).
	 *
	 * @param   array   $fieldAttributes  The field attributes
	 * @param   string  $name             The field name
	 * @param   string  $typeName         The field type
	 *
	 * @return  \stdClass   The field in xml object
	 * @since   5.1.1
	 */
	private function buildPlainField(array &$fieldAttributes, string $name, string $typeName): \stdClass
	{
		$field = new \stdClass();
		$field->fieldXML = new \SimpleXMLElement('<field/>');
		$field->comment = Line::_(__LINE__, __CLASS__) . ' ' . ucfirst($name)
			. ' Field. Type: ' . StringHelper::safe($typeName, 'F') . '. (joomla)';

		$this->appendFieldAttributes($field->fieldXML, $fieldAttributes, 'option');

		return $field;
	}

	/**
	 * Build a spacer field (non-database field used for display or layout).
	 *
	 * @param   array   $fieldAttributes  The field attributes
	 * @param   string  $name             The field name
	 * @param   string  $typeName         The field type
	 *
	 * @return  \stdClass   The field in xml object
	 * @since   5.1.1
	 */
	private function buildSpacerField(array &$fieldAttributes, string $name, string $typeName): \stdClass
	{
		$field = new \stdClass();
		$field->fieldXML = new \SimpleXMLElement('<field/>');
		$field->comment = Line::_(__LINE__, __CLASS__) . ' ' . ucfirst($name)
			. ' Field. Type: ' . StringHelper::safe($typeName, 'F') . '. A None Database Field. (joomla)';

		$this->appendFieldAttributes($field->fieldXML, $fieldAttributes, 'option');

		return $field;
	}

	/**
	 * Build a special field (repeatable or subform type).
	 *
	 * @param   array       $fieldAttributes
	 * @param   string      $name
	 * @param   array       $subform
	 * @param   string      $typeName
	 * @param   string      $langView
	 * @param   string      $nameSingleCode
	 * @param   string      $nameListCode
	 * @param   array       $placeholders
	 *
	 * @return  \stdClass
	 * @since   5.1.1
	 */
	private function buildSpecialField(array &$fieldAttributes, string $name, array &$subform, string $typeName,
		string $langView, string $nameSingleCode, string $nameListCode,
		array $placeholders): \stdClass
	{

		if ($typeName === 'subform')
		{
			return $this->buildSubformField($fieldAttributes, $name, $subform, $typeName, $langView, $nameSingleCode, $nameListCode, $placeholders);
		}

		if ($typeName === 'repeatable') // Just for Joomla 3 (will be removed when we drop support for J3, if we remember to look here :)
		{
			return $this->buildRepeatableField($fieldAttributes, $name, $typeName, $langView, $nameSingleCode, $nameListCode, $placeholders);
		}

		return new \stdClass();
	}

	/**
	 * Build a custom field (JCB custom configuration or plugin/module field).
	 *
	 * @param   array       $fieldAttributes
	 * @param   string      $name
	 * @param   string      $typeName
	 * @param   string      $langView
	 * @param   string      $nameSingleCode
	 * @param   string      $nameListCode
	 * @param   array       $placeholders
	 * @param   array|null  $optionArray
	 * @param   array|null  $custom
	 *
	 * @return  \stdClass
	 * @since   5.1.1
	 */
	private function buildCustomField(array &$fieldAttributes, string &$name, string &$typeName,
		string &$langView, string &$nameSingleCode, string &$nameListCode,
		array $placeholders, ?array &$optionArray, ?array $custom = null): \stdClass
	{
		$field = new \stdClass();
		$field->fieldXML = new \SimpleXMLElement('<field/>');
		$field->comment = Line::_(__LINE__, __CLASS__) . ' ' . ucfirst($name)
			. ' Field. Type: ' . StringHelper::safe($typeName, 'F') . '. (custom)';

		foreach ($fieldAttributes as $property => $value)
		{
			if ($property !== 'option')
			{
				$field->fieldXML->addAttribute($property, $value);
			}
			elseif ($property === 'option')
			{
				$this->xml->comment(
					$field->fieldXML,
					Line::_(__LINE__, __CLASS__) . " Option Set."
				);

				if (
					strtolower($typeName) === 'groupedlist'
					&& strpos((string) $value, ',') !== false
					&& strpos((string) $value, '@@') !== false
				)
				{
					$this->buildGroupedOptionSet($field->fieldXML, (string) $value, $langView, $typeName, $optionArray);
				}
				elseif (strpos((string) $value, ',') !== false)
				{
					$this->buildMultipleOptions($field->fieldXML, (string) $value, $langView, $optionArray);
				}
				else
				{
					$this->buildSingleOption($field->fieldXML, (string) $value, $langView, $optionArray);
				}
			}
		}

		if (
			($nameSingleCode === 'config' && $nameListCode === 'configs')
			|| strpos($nameSingleCode, 'pLuG!n') !== false
			|| strpos($nameSingleCode, 'M0dUl3') !== false
		)
		{
			$listLangName = $langView . '_' . StringHelper::safe($name, 'U');

			$this->customfieldtypefile->set([
				'type'   => $typeName,
				'code'   => $name,
				'lang'   => $listLangName,
				'custom' => $custom
			], $nameListCode, $nameSingleCode);
		}

		return $field;
	}

	/**
	 * Build a subform field (nested repeatable field container).
	 *
	 * @param   array       $fieldAttributes
	 * @param   string      $name
	 * @param   array       $subform
	 * @param   string      $typeName
	 * @param   string      $langView
	 * @param   string      $nameSingleCode
	 * @param   string      $nameListCode
	 * @param   array       $placeholders
	 *
	 * @return  \stdClass
	 * @since   5.1.1
	 */
	private function buildSubformField(array &$fieldAttributes, string $name, array &$subform, string $typeName,
		string $langView, string $nameSingleCode, string $nameListCode,
		array $placeholders): \stdClass
	{
		$field = new \stdClass();
		$field->fieldXML = new \SimpleXMLElement('<field/>');
		$field->comment = Line::_(__LINE__, __CLASS__) . ' ' . ucfirst($name)
			. ' Field. Type: ' . StringHelper::safe($typeName, 'F') . '. (joomla)';

		foreach ($fieldAttributes as $property => $value)
		{
			if ($property !== 'fields' && $property !== 'formsource')
			{
				$field->fieldXML->addAttribute($property, $value);
			}
		}

		if (!empty($fieldAttributes['formsource']) && StringHelper::check($fieldAttributes['formsource']))
		{
			$field->fieldXML->addAttribute('formsource', $fieldAttributes['formsource']);
			return $field;
		}

		$fieldsSets = $this->normalizeFieldsSet($fieldAttributes['fields']);

		if (!ArrayHelper::check($fieldsSets))
		{
			return $field;
		}

		$form = $field->fieldXML->addChild('form');
		$this->xml->attributes($form, [
			'hidden' => 'true',
			'name'   => 'list_' . $fieldAttributes['name'] . '_modal',
			'repeat' => 'true'
		]);

		$_resolverKey = $fieldAttributes['name'];
		$fieldsSets = $this->resolveFieldSetData($fieldsSets, $nameSingleCode, $nameListCode, $_resolverKey);

		// get the base subform details
		$new = true;
		if (isset($fieldAttributes['nested_depth']))
		{
			$new = false;
		}

		foreach ($fieldsSets as $fieldData)
		{
			if (!ObjectHelper::check($fieldData['settings']))
			{
				continue;
			}

			$r_name = $this->name->get($fieldData, $nameListCode, $_resolverKey);
			$r_typeName = $this->typename->get($fieldData);
			$r_langLabel = '';
			$r_multiple = false;
			$r_optionArray = [];
			$viewType = 0;

			$r_fieldValues = $this->attributes->set(
				$fieldData, $viewType, $r_name, $r_typeName, $r_multiple, $r_langLabel,
				$langView, $nameListCode, $nameSingleCode, $placeholders, true
			);

			if (!ArrayHelper::check($r_fieldValues))
			{
				continue;
			}

			if ($r_typeName === 'ModalSelect')
			{
				$r_fieldValues['custom'] = $this->modalselect->extract($r_fieldValues);
			}

			$subform[$r_name] = [
				'name' => $r_name,
				'type' => $r_typeName,
				'link' => $this->setLinkerRelations($r_fieldValues['custom'] ?? [])
			];

			if ($this->groups->check($r_typeName, 'option'))
			{
				$this->xml->append($form, $this->getSubformField(
					'option', $r_fieldValues, $r_name, $subform, $r_typeName,
					$langView, $nameSingleCode, $nameListCode,
					$placeholders, $r_optionArray
				));
			}
			elseif ($r_typeName === 'subform')
			{
				$r_fieldValues['nested_depth'] = isset($fieldAttributes['nested_depth'])
					? ++$fieldAttributes['nested_depth'] : 1;

				if ($r_fieldValues['nested_depth'] <= 20)
				{
					$subform[$r_name]['fields'] = [];
					$this->xml->append($form, $this->getSubformField(
						'special', $r_fieldValues, $r_name, $subform[$r_name]['fields'], $r_typeName,
						$langView, $nameSingleCode, $nameListCode,
						$placeholders, $r_optionArray
					));
				}
			}
			elseif (isset($r_fieldValues['custom']) && ArrayHelper::check($r_fieldValues['custom']))
			{
				$custom = $r_fieldValues['custom'];
				unset($r_fieldValues['custom']);

				$this->xml->append($form, $this->getSubformField(
					'custom', $r_fieldValues, $r_name, $subform, $r_typeName,
					$langView, $nameSingleCode, $nameListCode,
					$placeholders, $r_optionArray, null
				));

				$listLangName = StringHelper::check($r_langLabel) ? $r_langLabel :
					$langView . '_' . FieldHelper::safe($r_name, true);

				$this->language->set($this->config->lang_target, $listLangName, StringHelper::safe($r_name, 'W'));

				$this->customfieldtypefile->set([
					'type'   => $r_typeName,
					'code'   => $r_name,
					'lang'   => $listLangName,
					'custom' => $custom
				], $nameListCode, $nameSingleCode);
			}
			else
			{
				$this->xml->append($form, $this->getSubformField(
					'plain', $r_fieldValues, $r_name, $subform, $r_typeName,
					$langView, $nameSingleCode, $nameListCode,
					$placeholders, $r_optionArray
				));
			}
		}

		// set the base subform details
		if ($new && $subform !== [])
		{
			$com_field = $this->componentfields->get("{$nameSingleCode}.{$name}", null);
			if ($com_field !== null)
			{
				$com_field['fields'] = $subform;
				$this->componentfields->set("{$nameSingleCode}.{$name}", $com_field);
			}
		}

		return $field;
	}

	/**
	 * Create a field with simpleXMLElement class
	 *
	 * @param   string      $setType          The set of fields type
	 * @param   array       $fieldAttributes  The field values
	 * @param   string      $name             The field name
	 * @param   array       $subform          The current subform
	 * @param   string      $typeName         The field type
	 * @param   string      $langView         The language string of the view
	 * @param   string      $nameSingleCode   The single view name
	 * @param   string      $nameListCode     The list view name
	 * @param   array       $placeholders     The place holder and replace values
	 * @param   array|null  $optionArray      The option bucket array used to set the field options if needed.
	 * @param   array|null  $custom           Used when field is from config
	 *
	 * @return  \stdClass   The field in xml object
	 * @since   5.1.1
	 */
	private function getSubformField(string $setType, array &$fieldAttributes, string $name, array &$subform,
		string $typeName, string $langView, string $nameSingleCode, string $nameListCode,
		array $placeholders, ?array &$optionArray, ?array $custom = null): \stdClass
	{
		$this->counter->field++;

		switch ($setType)
		{
			case 'option':
				return $this->buildOptionField($fieldAttributes, $name, $typeName, $langView,
					$nameSingleCode, $nameListCode, $placeholders, $optionArray);

			case 'plain':
				return $this->buildPlainField($fieldAttributes, $name, $typeName);

			case 'spacer':
				return $this->buildSpacerField($fieldAttributes, $name, $typeName);

			case 'special':
				return $this->buildSpecialField($fieldAttributes, $name, $subform, $typeName, $langView,
					$nameSingleCode, $nameListCode, $placeholders);

			case 'custom':
				return $this->buildCustomField($fieldAttributes, $name, $typeName, $langView,
					$nameSingleCode, $nameListCode, $placeholders, $optionArray, $custom);

			default:
				return new \stdClass();
		}
	}

	/**
	 * Build a repeatable field (deprecated Joomla repeatable format).
	 *
	 * @param   array       $fieldAttributes
	 * @param   string      $name
	 * @param   string      $typeName
	 * @param   string      $langView
	 * @param   string      $nameSingleCode
	 * @param   string      $nameListCode
	 * @param   array       $placeholders
	 *
	 * @return  \stdClass
	 * @since   5.1.1
	 */
	private function buildRepeatableField(array &$fieldAttributes, string $name, string $typeName,
		string $langView, string $nameSingleCode, string $nameListCode,
		array $placeholders): \stdClass
	{
		$field = new \stdClass();
		$field->fieldXML = new \SimpleXMLElement('<field/>');
		$field->comment = Line::_(__LINE__, __CLASS__) . ' ' . ucfirst($name)
			. ' Field. Type: ' . StringHelper::safe($typeName, 'F') . '. (depreciated)';

		foreach ($fieldAttributes as $property => $value)
		{
			if ($property !== 'fields')
			{
				$field->fieldXML->addAttribute($property, $value);
			}
		}

		$fieldsSets = $this->normalizeFieldsSet($fieldAttributes['fields']);

		if (!ArrayHelper::check($fieldsSets))
		{
			return $field;
		}

		$fieldsXML = $field->fieldXML->addChild('fields');
		$fieldsXML->addAttribute('name', $fieldAttributes['name'] . '_fields');
		$fieldsXML->addAttribute('label', '');
		$fieldSetXML = $fieldsXML->addChild('fieldset');
		$fieldSetXML->addAttribute('hidden', 'true');
		$fieldSetXML->addAttribute('name', $fieldAttributes['name'] . '_modal');
		$fieldSetXML->addAttribute('repeat', 'true');

		$_resolverKey = $fieldAttributes['name'];
		$fieldsSets = $this->resolveFieldSetData($fieldsSets, $nameSingleCode, $nameListCode, $_resolverKey);

		foreach ($fieldsSets as $fieldData)
		{
			if (!ObjectHelper::check($fieldData['settings']))
			{
				continue;
			}

			$r_name = $this->name->get($fieldData, $nameListCode, $_resolverKey);
			$r_typeName = $this->typename->get($fieldData);
			$r_multiple = false;
			$viewType = 0;
			$r_langLabel = '';
			$r_optionArray = [];

			$r_fieldValues = $this->attributes->set(
				$fieldData, $viewType, $r_name, $r_typeName, $r_multiple,
				$r_langLabel, $langView, $nameListCode, $nameSingleCode,
				$placeholders, true
			);

			if (!ArrayHelper::check($r_fieldValues))
			{
				continue;
			}

			if ($this->groups->check($r_typeName, 'option'))
			{
				$this->xml->append($fieldSetXML, $this->get(
					'option', $r_fieldValues, $r_name, $subform, $r_typeName,
					$langView, $nameSingleCode, $nameListCode,
					$placeholders, $r_optionArray
				));
			}
			elseif (isset($r_fieldValues['custom']) && ArrayHelper::check($r_fieldValues['custom']))
			{
				$custom = $r_fieldValues['custom'];
				unset($r_fieldValues['custom']);

				$this->xml->append($fieldSetXML, $this->get(
					'custom', $r_fieldValues, $r_name, $subform, $r_typeName,
					$langView, $nameSingleCode, $nameListCode,
					$placeholders, $r_optionArray, $custom
				));

				$listLangName = StringHelper::check($r_langLabel)
					? $r_langLabel
					: $langView . '_' . FieldHelper::safe($r_name, true);

				$this->language->set($this->config->lang_target, $listLangName, StringHelper::safe($r_name, 'W'));

				$this->customfieldtypefile->set([
					'type'   => $r_typeName,
					'code'   => $r_name,
					'lang'   => $listLangName,
					'custom' => $custom
				], $nameListCode, $nameSingleCode);
			}
			else
			{
				$this->xml->append($fieldSetXML, $this->get(
					'plain', $r_fieldValues, $r_name, $r_typeName,
					$langView, $nameSingleCode, $nameListCode,
					$placeholders, $r_optionArray
				));
			}
		}

		return $field;
	}

	/**
	 * Normalize the field definition string into an array.
	 *
	 * @param   mixed  $fields  Comma-separated, integer, or GUID
	 *
	 * @return  array
	 * @since   5.1.1
	 */
	private function normalizeFieldsSet($fields): array
	{
		if (strpos((string) $fields, ',') !== false)
		{
			return explode(',', (string) $fields);
		}
		elseif (is_numeric($fields))
		{
			return [(int) $fields];
		}
		elseif (GuidHelper::valid($fields))
		{
			return [(string) $fields];
		}

		return [];
	}

	/**
	 * Resolve the field data from a set of IDs using the internal field service.
	 *
	 * @param   array   $fields           Field identifiers
	 * @param   string  $nameSingleCode   Single code
	 * @param   string  $nameListCode     List code
	 * @param   string  $_resolverKey     Resolver key
	 *
	 * @return  array
	 * @since   5.1.1
	 */
	private function resolveFieldSetData(array $fields, string $nameSingleCode, string $nameListCode, string $_resolverKey): array
	{
		return array_map(
			function ($id) use ($nameSingleCode, $nameListCode, $_resolverKey) {
				$field = ['field' => $id];
				$this->field->set($field, $nameSingleCode, $nameListCode, $_resolverKey);
				return $field;
			}, array_values($fields)
		);
	}

	/**
	 * Build a single option XML element.
	 *
	 * @param   \SimpleXMLElement  $fieldXML
	 * @param   string             $value
	 * @param   string             $langView
	 * @param   array|null         $optionArray
	 *
	 * @return  void
	 * @since   5.1.1
	 */
	private function buildSingleOption(\SimpleXMLElement $fieldXML, string $value, string $langView, ?array &$optionArray): void
	{
		$optionXML = $fieldXML->addChild('option');

		if (strpos($value, '|') !== false)
		{
			list($v, $t) = explode('|', $value);
			$langValue = $langView . '_' . FieldHelper::safe($t, true);
			$this->language->set($this->config->lang_target, $langValue, $t);
			$optionXML->addAttribute('value', $v);
			$optionArray[$v] = $langValue;
		}
		else
		{
			$langValue = $langView . '_' . FieldHelper::safe($value, true);
			$this->language->set($this->config->lang_target, $langValue, $value);
			$optionXML->addAttribute('value', $value);
			$optionArray[$value] = $langValue;
		}

		$optionXML[0] = $langValue;
	}

	/**
	 * Build multiple option XML elements.
	 *
	 * @param   \SimpleXMLElement  $fieldXML
	 * @param   string             $value
	 * @param   string             $langView
	 * @param   array|null         $optionArray
	 *
	 * @return  void
	 * @since   5.1.1
	 */
	private function buildMultipleOptions(\SimpleXMLElement $fieldXML, string $value, string $langView, ?array &$optionArray): void
	{
		$options = explode(',', $value);

		foreach ($options as $option)
		{
			$this->buildSingleOption($fieldXML, $option, $langView, $optionArray);
		}
	}

	/**
	 * Build grouped option set for groupedlist fields.
	 *
	 * @param   \SimpleXMLElement  $fieldXML
	 * @param   string             $value
	 * @param   string             $langView
	 * @param   string             $typeName
	 * @param   array|null         $optionArray
	 *
	 * @return  void
	 * @since   5.1.1
	 */
	private function buildGroupedOptionSet(\SimpleXMLElement $fieldXML, string $value, string $langView, string $typeName, ?array &$optionArray): void
	{
		$groups = [];
		$grouped = ['group' => [], 'option' => []];
		$order = [];

		$options = explode(',', $value);

		foreach ($options as $option)
		{
			if (strpos($option, '@@') !== false)
			{
				$this->parseGroupLabel($option, $langView, $groups, $order);
			}
			elseif (strpos($option, '|') !== false)
			{
				$this->parseGroupedOption($option, $langView, $grouped, $order, $optionArray);
			}
			else
			{
				$this->parsePlainGroupedOption($option, $langView, $grouped, $order, $optionArray);
			}
		}

		$this->appendGroupedOptions($fieldXML, $groups, $grouped, $order);
	}

	/**
	 * Parse a group label from groupedlist syntax.
	 *
	 * @param   string  $option
	 * @param   string  $langView
	 * @param   array   $groups
	 * @param   array   $order
	 *
	 * @return  void
	 * @since   5.1.1
	 */
	private function parseGroupLabel(string $option, string $langView, array &$groups, array &$order): void
	{
		[$label, $groupId] = explode('@@', $option);

		$langValue = $langView . '_' . FieldHelper::safe($label, true);
		$this->language->set($this->config->lang_target, $langValue, $label);

		$groups[$groupId] = $langValue;
		$order['group' . $groupId] = $groupId;
	}

	/**
	 * Parse a grouped option with value|text|groupId syntax.
	 *
	 * @param   string       $option
	 * @param   string       $langView
	 * @param   array        $grouped
	 * @param   array        $order
	 * @param   array|null   $optionArray
	 *
	 * @return  void
	 * @since   5.1.1
	 */
	private function parseGroupedOption(string $option, string $langView, array &$grouped, array &$order, ?array &$optionArray): void
	{
		$parts = explode('|', $option);

		if (count($parts) === 3)
		{
			[$value, $text, $groupId] = $parts;
			$langValue = $langView . '_' . FieldHelper::safe($text, true);
			$this->language->set($this->config->lang_target, $langValue, $text);

			$grouped['group'][$groupId][] = ['value' => $value, 'text' => $langValue];
			$optionArray[$value] = $langValue;
			$order['group' . $groupId] = $groupId;
		}
		else
		{
			[$value, $text] = $parts;
			$langValue = $langView . '_' . FieldHelper::safe($text, true);
			$this->language->set($this->config->lang_target, $langValue, $text);

			$grouped['option'][$value] = ['value' => $value, 'text' => $langValue];
			$optionArray[$value] = $langValue;
			$order['option' . $value] = $value;
		}
	}

	/**
	 * Parse a plain grouped option where value equals text.
	 *
	 * @param   string       $option
	 * @param   string       $langView
	 * @param   array        $grouped
	 * @param   array        $order
	 * @param   array|null   $optionArray
	 *
	 * @return  void
	 * @since   5.1.1
	 */
	private function parsePlainGroupedOption(string $option, string $langView, array &$grouped, array &$order, ?array &$optionArray): void
	{
		$langValue = $langView . '_' . FieldHelper::safe($option, true);
		$this->language->set($this->config->lang_target, $langValue, $option);

		$grouped['option'][$option] = ['value' => $option, 'text' => $langValue];
		$optionArray[$option] = $langValue;
		$order['option' . $option] = $option;
	}

	/**
	 * Append parsed grouped and ungrouped options to field XML.
	 *
	 * @param   \SimpleXMLElement  $fieldXML
	 * @param   array              $groups
	 * @param   array              $grouped
	 * @param   array              $order
	 *
	 * @return  void
	 * @since   5.1.1
	 */
	private function appendGroupedOptions(\SimpleXMLElement $fieldXML, array &$groups, array &$grouped, array &$order): void
	{
		foreach ($order as $key => $id)
		{
			if (strpos($key, 'group') === 0 && isset($groups[$id]) && !empty($grouped['group'][$id]))
			{
				$groupXML = $fieldXML->addChild('group');
				$groupXML->addAttribute('label', $groups[$id]);

				foreach ($grouped['group'][$id] as $opt)
				{
					$optionXML = $groupXML->addChild('option');
					$optionXML->addAttribute('value', $opt['value']);
					$optionXML[0] = $opt['text'];
				}
			}
			elseif (isset($grouped['option'][$id]))
			{
				$opt = $grouped['option'][$id];
				$optionXML = $fieldXML->addChild('option');
				$optionXML->addAttribute('value', $opt['value']);
				$optionXML[0] = $opt['text'];
			}
		}
	}

	/**
	 * Append all attributes to field, excluding a specific one
	 *
	 * @param  \SimpleXMLElement  $fieldXML
	 * @param  array              $fieldAttributes
	 * @param  string|null        $exclude
	 *
	 * @return void
	 * @since  5.1.1
	 */
	private function appendFieldAttributes(\SimpleXMLElement $fieldXML, array $fieldAttributes, ?string $exclude = null): void
	{
		foreach ($fieldAttributes as $property => $value)
		{
			if ($property !== $exclude)
			{
				$fieldXML->addAttribute($property, $value);
			}
		}
	}

	/**
	 * Sets the linker relations for a field based on the provided link data.
	 *
	 * The method determines the type of link relation based on the presence of a table.
	 * If no table is provided, it assigns a type 2 with a null table, otherwise it assigns type 1.
	 * It also extracts additional values from the input array, such as component, entity, value, and key.
	 *
	 * @param array  $link  The link data which may contain 'table', 'component', 'view', 'text', and 'id'.
	 *
	 * @return array|null The structured linker relation array, or null if input is an empty array.
	 * @since  5.1.1
	 */
	private function setLinkerRelations(array $link): ?array
	{
		if ($link === [])
		{
			return null;
		}

		$linker = [
			'type' => empty($link['table']) ? 2 : 1,
			'table' => $link['table'] ?? null,
			'component' => $link['component'] ?? null,
			'entity' => $link['view'] ?? null,
			'value' => $link['text'] ?? null,
			'key' => $link['id'] ?? null
		];

		return $linker;
	}
}

