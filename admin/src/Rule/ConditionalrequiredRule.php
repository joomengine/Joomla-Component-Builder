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
namespace VDM\Component\Componentbuilder\Administrator\Rule;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Component\ComponentHelper;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Form Rule (Conditionalrequired) class for the Joomla Platform.
 *
 * @since  3.5
 */
class ConditionalrequiredRule extends FormRule
{
	// Evaluate trusted conditions inside the native form validation lifecycle.

	/**
	 * Internal field used to evaluate requirements after native filtering.
	 *
	 * @var    string
	 * @since  6.2.1
	 */
	private const FIELD = '__conditional_required';

	/**
	 * Active validations, isolated by the exact original Form instance.
	 *
	 * @var    \SplObjectStorage|null
	 * @since  6.2.1
	 */
	private static $validations;

	/**
	 * The form owned by this validation, or null after release.
	 *
	 * @var    Form|null
	 * @since  6.2.1
	 */
	private $form;

	/**
	 * Selected field group.
	 *
	 * @var    string|null
	 * @since  6.2.1
	 */
	private $group;

	/**
	 * Trusted condition definitions emitted by the compiler.
	 *
	 * @var    array
	 * @since  6.2.1
	 */
	private $conditions = [];

	/**
	 * Named model method resolving stored values and request context.
	 *
	 * @var    \Closure|null
	 * @since  6.2.1
	 */
	private $resolver;

	/**
	 * Temporary XML node, retained even when plugins move or remove it.
	 *
	 * @var    \DOMElement|null
	 * @since  6.2.1
	 */
	private $node;

	/**
	 * Required attributes captured after native validation plugins run.
	 *
	 * @var    array
	 * @since  6.2.1
	 */
	private $attributes = [];

	/**
	 * Whether this validation installed the temporary field.
	 *
	 * @var    bool
	 * @since  6.2.1
	 */
	private $installed = false;

	/**
	 * Whether requirements have been evaluated in the native lifecycle.
	 *
	 * @var    bool
	 * @since  6.2.1
	 */
	private $evaluated = false;

	/**
	 * An integration error, separate from Joomla's ordinary validation errors.
	 *
	 * @var    string|null
	 * @since  6.2.1
	 */
	private $error;

	/**
	 * Prepare one validation without filtering data or invoking model plugins.
	 *
	 * @param   Form      $form        The original validation form.
	 * @param   array     $conditions  Trusted compiler condition definitions.
	 * @param   \Closure  $resolver    Named model method accepting data, form and group.
	 * @param   ?string   $group       Selected field group.
	 *
	 * @return  bool
	 * @throws  \Throwable  When native form preparation throws.
	 * @since   6.2.1
	 */
	public function prepare(Form $form, array $conditions, \Closure $resolver, ?string $group = null): bool
	{
		$this->error = null;

		if ($this->form !== null || (self::$validations !== null && self::$validations->contains($form)))
		{
			return $this->fail('Conditional validation is already active for this form.');
		}

		if ($form->getFieldXml(self::FIELD, $group) !== false)
		{
			return $this->fail('The form already contains the reserved conditional validation field.');
		}

		if (self::$validations === null)
		{
			self::$validations = new \SplObjectStorage();
		}

		$this->form = $form;
		$this->group = $group;
		$this->conditions = $conditions;
		$this->resolver = $resolver;
		$this->evaluated = false;
		self::$validations[$form] = $this;

		try
		{
			if ($this->installField())
			{
				return true;
			}
		}
		catch (\Throwable $error)
		{
			$this->release();
			throw $error;
		}

		$this->release();
		return false;
	}

	/**
	 * Remove the internal field from submitted or validated data.
	 *
	 * @param   array  $data  Values before or after the native lifecycle.
	 *
	 * @return  array
	 * @since   6.2.1
	 */
	public function filterInternalInput(array $data): array
	{
		$input = new Registry($data);
		$input->remove($this->group ? $this->group . '.' . self::FIELD : self::FIELD);

		return $input->toArray();
	}

	/**
	 * Accept native output only after the conditional rule has run successfully.
	 *
	 * @param   array|false  $result  The parent model's validation result.
	 *
	 * @return  array|false
	 * @since   6.2.1
	 */
	public function complete($result)
	{
		if ($result === false || $this->error !== null)
		{
			return false;
		}

		if (!$this->evaluated)
		{
			return $this->fail('The conditional validation rule did not run before the form was accepted.');
		}

		return $this->filterInternalInput($result);
	}

	/**
	 * Restore only temporary changes and release this form's validation state.
	 *
	 * @return  void
	 * @since   6.2.1
	 */
	public function release(): void
	{
		if ($this->node !== null && $this->node->parentNode !== null)
		{
			$this->node->parentNode->removeChild($this->node);
		}

		foreach ($this->attributes as [$element, $required])
		{
			if ($required === null)
			{
				unset($element['required']);
			}
			else
			{
				$element['required'] = $required;
			}
		}

		if ($this->form !== null)
		{
			if ($this->installed)
			{
				$this->form->removeField(self::FIELD, $this->group);
			}

			if (self::$validations !== null && self::$validations->contains($this->form)
				&& self::$validations[$this->form] === $this)
			{
				self::$validations->detach($this->form);
			}
		}

		$this->form = null;
		$this->group = null;
		$this->conditions = [];
		$this->resolver = null;
		$this->node = null;
		$this->attributes = [];
		$this->installed = false;
		$this->evaluated = false;
	}

	/**
	 * Return an integration failure without replacing native validation errors.
	 *
	 * @return  string|null
	 * @since   6.2.1
	 */
	public function getError(): ?string
	{
		return $this->error;
	}

	/**
	 * Bridge Joomla's fresh rule instance to the validation owning this form.
	 *
	 * @param   \SimpleXMLElement  $element  The internal field definition.
	 * @param   mixed              $value    Ignored; browser input cannot define requirements.
	 * @param   ?string            $group    The field group being validated.
	 * @param   ?Registry          $input    Values after native filtering.
	 * @param   ?Form              $form     The original validation form.
	 *
	 * @return  bool
	 * @since   6.2.0
	 * @since   6.2.1  Delegate to the validation instance's named evaluator.
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
	{
		if ($form === null || $input === null || self::$validations === null || !self::$validations->contains($form))
		{
			return false;
		}

		$validation = self::$validations[$form];

		if ((string) $element['name'] !== self::FIELD || (string) $group !== (string) $validation->group)
		{
			return $validation->fail('The conditional validation rule ran for an unexpected field or group.');
		}

		if (!$validation->isFirstField($element))
		{
			return $validation->fail('The conditional validation rule must run before the selected form fields.');
		}

		return $validation->evaluate($input->toArray());
	}

	/**
	 * Install the bridge before all fields in the selected validation scope.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function installField(): bool
	{
		$element = new \SimpleXMLElement('<field name="__conditional_required" type="hidden" filter="unset" validate="conditionalrequired" />');

		if (!$this->form->setField($element, $this->group))
		{
			return $this->fail('Unable to install the conditional validation rule in this form.');
		}

		$this->installed = true;
		$element = $this->form->getFieldXml(self::FIELD, $this->group);

		if ($element === false)
		{
			return $this->fail('Unable to find the installed conditional validation field.');
		}

		$this->node = dom_import_simplexml($element);
		$container = $this->node->parentNode;

		while ($container->nodeName !== 'form' && $container->nodeName !== 'fields')
		{
			$container = $container->parentNode;
		}

		$container->insertBefore($this->node, $container->firstChild);
		return true;
	}

	/**
	 * Detect plugin changes that move the bridge after a field in this scope.
	 *
	 * @param   \SimpleXMLElement  $element  The internal field definition.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function isFirstField(\SimpleXMLElement $element): bool
	{
		foreach ($element->xpath('preceding::field[not(ancestor::field)]') ?: [] as $previous)
		{
			$groups = $previous->xpath('ancestor::fields[@name]/@name');
			$previousGroup = implode('.', array_map('strval', $groups ?: []));

			if ($this->group === null || $this->group === '' || $previousGroup === $this->group
				|| strpos($previousGroup, $this->group . '.') === 0)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Resolve trusted context and apply requirements to the already filtered data.
	 *
	 * @param   array  $data  All filtered values from the native Form.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function evaluate(array $data): bool
	{
		if ($this->evaluated)
		{
			return $this->fail('Conditional requirements were evaluated more than once for this validation.');
		}

		$this->evaluated = true;
		$context = ($this->resolver)($data, $this->form, $this->group);

		if (!is_array($context) || !isset($context['stored'], $context['patch'])
			|| !is_array($context['stored']) || !is_bool($context['patch']))
		{
			return $this->fail('Unable to resolve trusted data for conditional validation.');
		}

		$input = new Registry($data);
		$values = $this->group ? (array) $input->get($this->group, []) : $data;
		$requirements = $this->requirements($values, $context['stored'], $context['patch']);

		if ($this->error !== null)
		{
			return false;
		}

		$this->applyRequirements($requirements);

		return true;
	}

	/**
	 * Evaluate condition groups in their declared browser order.
	 *
	 * @param   array  $data    Filtered values within the selected group.
	 * @param   array  $stored  Decoded stored values within the selected group.
	 * @param   bool   $patch   Whether omitted input retains stored API PATCH state.
	 *
	 * @return  array
	 * @since   6.2.1
	 */
	private function requirements(array $data, array $stored, bool $patch): array
	{
		$requirements = [];

		foreach ($this->conditions as $condition)
		{
			foreach ($condition['targets'] as $target)
			{
				$requirements[$target] = true;
			}
		}

		foreach ($this->conditions as $condition)
		{
			$matched = $this->matchesGroup($condition['matches'], $data, $stored, $patch);

			if ($matched !== null && ($matched || $condition['toggle']))
			{
				$required = $matched ? $condition['show'] : !$condition['show'];

				foreach ($condition['targets'] as $target)
				{
					$requirements[$target] = $required;
				}
			}
		}

		return $requirements;
	}

	/**
	 * Combine related match rules using AND, keeping unsupported groups strict.
	 *
	 * @param   array  $rules   The related selector definitions.
	 * @param   array  $data    Filtered submitted values.
	 * @param   array  $stored  Decoded stored values.
	 * @param   bool   $patch   Whether this request is an API PATCH.
	 *
	 * @return  bool|null  Null when the group has an unsupported selector.
	 * @since   6.2.1
	 */
	private function matchesGroup(array $rules, array $data, array $stored, bool $patch): ?bool
	{
		$matched = true;

		foreach ($rules as $rule)
		{
			if (!$rule['supported'])
			{
				return null;
			}

			$value = $this->resolveValue($rule, $data, $stored, $patch);
			$matches = $this->matchesRule($value, $rule);
			$matched = $matched && $matches;
		}

		return $matched;
	}

	/**
	 * Resolve selectors according to native filtering, ACL and PATCH semantics.
	 *
	 * @param   array  $rule    One normalized selector rule.
	 * @param   array  $data    Filtered submitted values.
	 * @param   array  $stored  Decoded stored values.
	 * @param   bool   $patch   Whether this request is an API PATCH.
	 *
	 * @return  mixed
	 * @since   6.2.1
	 */
	private function resolveValue(array $rule, array $data, array $stored, bool $patch)
	{
		$name = $rule['name'];
		$disabled = strtolower((string) $this->form->getFieldAttribute($name, 'disabled', '', $this->group));
		$filter = strtolower((string) $this->form->getFieldAttribute($name, 'filter', '', $this->group));
		$available = $this->form->getFieldAttribute($name, 'name', null, $this->group) !== null;
		$protected = !$available || in_array($disabled, ['true', '1', 'disabled'], true) || $filter === 'unset';
		$values = $protected ? $stored : $data;

		if (array_key_exists($name, $values))
		{
			$value = $values[$name];
		}
		elseif (!$protected && $patch && array_key_exists($name, $stored))
		{
			$value = $stored[$name];
		}
		elseif (!$protected && $rule['checkbox'])
		{
			$value = false;
		}
		else
		{
			$value = $this->form->getFieldAttribute($name, 'default', null, $this->group);
		}

		if (!$this->hasValidShape($name, $value))
		{
			return null;
		}

		return $rule['checkbox'] ? (bool) $value : $value;
	}

	/**
	 * Reject selector collections that the live native field cannot represent.
	 *
	 * @param   string  $name   The selector name within the selected group.
	 * @param   mixed   $value  The effective filtered or stored selector value.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function hasValidShape(string $name, $value): bool
	{
		if ($value === null || is_scalar($value))
		{
			return true;
		}

		$field = $this->form->getField($name, $this->group);

		if (!is_array($value) || ($field && !$field->multiple))
		{
			return $this->fail('Invalid value shape for conditional selector: ' . $name . '.');
		}

		foreach ($value as $entry)
		{
			if ($entry !== null && !is_scalar($entry))
			{
				return $this->fail('Invalid value shape for conditional selector: ' . $name . '.');
			}
		}

		return true;
	}

	/**
	 * Apply the browser's some semantics to selectors that accept arrays.
	 *
	 * @param   mixed  $value  The effective selector value.
	 * @param   array  $rule   One normalized selector rule.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function matchesRule($value, array $rule): bool
	{
		if (!$rule['array'])
		{
			return $this->matches($value, $rule);
		}

		$values = $this->isPresent($value) ? (array) $value : [];

		foreach ($values as $entry)
		{
			if ($this->matches($entry, $rule))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Dispatch the normalized selector behavior to its named matcher.
	 *
	 * @param   mixed  $value  One effective selector value.
	 * @param   array  $rule   One normalized selector rule.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function matches($value, array $rule): bool
	{
		$behavior = $rule['behavior'];

		if ($behavior >= 1 && $behavior <= 3)
		{
			return $this->matchesSelection($value, $rule);
		}

		if ($behavior === 4 || $behavior === 5)
		{
			return $behavior === 4 ? $this->isPresent($value) : !$this->isPresent($value);
		}

		if (!is_scalar($value) && $value !== null)
		{
			return false;
		}

		if ($behavior >= 6 && $behavior <= 9)
		{
			return $this->matchesKeywords((string) $value, $rule);
		}

		return $this->matchesLength((string) $value, $rule);
	}

	/**
	 * Match selection options and the existing user-field presence behavior.
	 *
	 * @param   mixed  $value  One effective selection value.
	 * @param   array  $rule   One normalized selection rule.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function matchesSelection($value, array $rule): bool
	{
		$behavior = $rule['behavior'];

		if ($rule['options'] !== [])
		{
			foreach ($rule['options'] as $option)
			{
				$equal = $this->equals($value, $option);

				if ($behavior === 2 ? !$equal : $equal)
				{
					return true;
				}
			}

			return false;
		}

		$present = $this->isPresent($value);

		if ($behavior === 2)
		{
			return !$present;
		}

		return $present && !($behavior === 3 && $rule['user'] && $this->equals($value, '0'));
	}

	/**
	 * Match all or any configured keywords with the selected case behavior.
	 *
	 * @param   string  $value  One effective text value.
	 * @param   array   $rule   One normalized keyword rule.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function matchesKeywords(string $value, array $rule): bool
	{
		$keywords = $rule['options']['keywords'] ?? [];

		if ($keywords === [])
		{
			return $value === 'error';
		}

		$all = $rule['behavior'] === 6 || $rule['behavior'] === 8;

		if ($rule['behavior'] === 8 || $rule['behavior'] === 9)
		{
			$value = \Joomla\String\StringHelper::strtolower($value);
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

	/**
	 * Compare text length using JavaScript's UTF-16 code-unit count.
	 *
	 * @param   string  $value  One effective text value.
	 * @param   array   $rule   One normalized length rule.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function matchesLength(string $value, array $rule): bool
	{
		$length = \Joomla\String\StringHelper::strlen($value) + preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $value);
		$expected = (int) (($rule['options']['length'] ?? 0) ?: 5);

		switch ($rule['behavior'])
		{
			case 10:
				return $length >= $expected;
			case 11:
				return $length <= $expected;
			case 12:
				return $length === $expected;
		}

		return false;
	}

	/**
	 * Determine presence without treating numeric zero as an absent value.
	 *
	 * @param   mixed  $value  One selector value.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function isPresent($value): bool
	{
		return $value !== null && $value !== '' && $value !== [];
	}

	/**
	 * Compare DOM-style selection values with numeric and boolean options.
	 *
	 * @param   mixed  $value   One effective selector value.
	 * @param   mixed  $option  A trusted configured option.
	 *
	 * @return  bool
	 * @since   6.2.1
	 */
	private function equals($value, $option): bool
	{
		if (is_numeric($option) || $option === 'true' || $option === 'false')
		{
			$number = $option === 'true' ? 1 : ($option === 'false' ? 0 : (float) $option);
			$candidate = $this->numericValue($value);
			return $candidate !== null && $candidate === (float) $number;
		}

		return is_scalar($value) && (string) $value === (string) $option;
	}

	/**
	 * Convert a scalar selection as JavaScript does for a numeric comparison.
	 *
	 * Preserve decimal leading zeros, non-decimal prefixes and Infinity without
	 * accepting trailing junk, signed prefixes or numeric separators. The trim
	 * set follows ECMAScript WhiteSpace and LineTerminator, not PHP's trim().
	 * Null is kept nonmatching: JavaScript null is not loosely equal to a number.
	 *
	 * @param   mixed  $value  A filtered scalar selection value.
	 *
	 * @return  float|null  Its numeric value, or null for a nonnumeric selection.
	 * @since   6.2.1
	 */
	private function numericValue($value): ?float
	{
		if (is_bool($value) || is_int($value) || is_float($value))
		{
			return (float) $value;
		}
		if (!is_string($value))
		{
			return null;
		}

		$space = '[\p{Zs}\x{FEFF}\x09-\x0D\x{2028}\x{2029}]';
		$value = preg_replace('/\A' . $space . '+|' . $space . '+\z/u', '', $value);
		if ($value === '' || is_numeric($value))
		{
			return (float) $value;
		}
		if ($value === 'Infinity' || $value === '+Infinity' || $value === '-Infinity')
		{
			return $value === '-Infinity' ? -INF : INF;
		}
		if ($value === null)
		{
			return null;
		}
		if (preg_match('/\A0[xX]([0-9a-fA-F]+)\z/', $value, $match))
		{
			return (float) hexdec($match[1]);
		}
		if (preg_match('/\A0[bB]([01]+)\z/', $value, $match))
		{
			return (float) bindec($match[1]);
		}
		if (preg_match('/\A0[oO]([0-7]+)\z/', $value, $match))
		{
			return (float) octdec($match[1]);
		}

		return null;
	}

	/**
	 * Change only required attributes while retaining native value validation.
	 *
	 * @param   array  $requirements  Required state keyed by target field name.
	 *
	 * @return  void
	 * @since   6.2.1
	 */
	private function applyRequirements(array $requirements): void
	{
		foreach ($requirements as $field => $required)
		{
			$element = $this->form->getFieldXml($field, $this->group);

			if ($element !== false)
			{
				$this->attributes[] = [$element, isset($element['required']) ? (string) $element['required'] : null];
				// Conditions cannot restore a requirement removed by the live form.
				$required = $required
					&& in_array((string) $element['required'], ['true', 'required'], true)
					&& strtolower((string) $element['filter']) !== 'unset'
					&& !in_array((string) $element['disabled'], ['true', '1', 'disabled'], true);
				$this->form->setFieldAttribute($field, 'required', $required ? 'true' : 'false', $this->group);
			}
		}
	}

	/**
	 * Record the first integration error for the owning model.
	 *
	 * @param   string  $message  The integration failure description.
	 *
	 * @return  bool  Always false.
	 * @since   6.2.1
	 */
	private function fail(string $message): bool
	{
		if ($this->error === null)
		{
			$this->error = $message;
		}

		return false;
	}
}
