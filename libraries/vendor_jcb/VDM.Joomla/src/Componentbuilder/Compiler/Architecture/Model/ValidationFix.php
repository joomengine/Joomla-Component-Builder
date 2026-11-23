<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    19th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Model;


use VDM\Joomla\Componentbuilder\Compiler\Builder\ValidationFix as ValidationFixRegistry;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Registry as CompilerRegistry;


/**
 * Generate model declarations for native conditional form validation.
 * 
 * The model owns its definitions and stored-record access. The shared generated
 * rule owns evaluation and temporary form state at Joomla's validation boundary.
 * 
 * @since  6.1.7
 * @since  6.2.1  Separate model declarations from the reusable rule implementation.
 */
final class ValidationFix
{
	/**
	 * Normalized conditional requirements, shared with the browser generator.
	 *
	 * @var    ValidationFixRegistry
	 * @since  6.1.7
	 */
	protected ValidationFixRegistry $validationfix;

	/**
	 * The registry of generated validation rules.
	 *
	 * @var    CompilerRegistry
	 * @since  6.2.0
	 */
	protected CompilerRegistry $registry;

	/**
	 * The shared native conditional rule emitter.
	 *
	 * @var    ConditionalRule
	 * @since  6.2.0
	 */
	protected ConditionalRule $conditionalrule;

	/**
	 * Construct the model emitter from its shared compilation state.
	 *
	 * @param   ValidationFixRegistry  $validationfix    Normalized condition definitions.
	 * @param   CompilerRegistry       $registry         Generated rule registry.
	 * @param   ConditionalRule        $conditionalrule  Native rule emitter.
	 *
	 * @since   6.2.0
	 */
	public function __construct(
		ValidationFixRegistry $validationfix,
		CompilerRegistry $registry,
		ConditionalRule $conditionalrule
	)
	{
		$this->validationfix = $validationfix;
		$this->registry = $registry;
		$this->conditionalrule = $conditionalrule;
	}

	/**
	 * Build the view's condition property in the model's property section.
	 *
	 * Export definition values independently of static template substitutions so
	 * that multiline strings and values resembling placeholders remain exact.
	 *
	 * @param   string  $view  The single view name.
	 *
	 * @return  string  The declaration, or an empty string for an unaffected view.
	 *
	 * @since   6.2.1
	 */
	public function getProperties(string $view): string
	{
		$groups = $this->validationfix->getConditions($view);
		if ($groups === [])
		{
			return '';
		}

		$lines = [
			'/**',
			' * Conditional requirements generated from this view\'s field conditions.',
			' *',
			' * Matches within a group are combined with AND. Groups retain their',
			' * authored order; the shared rule evaluates filtered values before',
			' * Joomla checks required fields. Browser exemption lists are ignored.',
			' *',
			' * @var    array',
			' * @since  6.2.1',
			' */',
		];

		return PHP_EOL . PHP_EOL . Indent::_(1)
			. implode(PHP_EOL . Indent::_(1), $lines)
			. PHP_EOL . Indent::_(1) . 'protected $conditionalRequirements = '
			. $this->export($groups, 1) . ';';
	}

	/**
	 * Build the native validation wrapper and named model data resolver.
	 *
	 * Only normalized server conditions enable the generated rule. A historical
	 * browser-only field list cannot create an unused override or waive a field.
	 *
	 * @param   string  $view       The single view name.
	 * @param   string  $Component  The component name retained for caller compatibility.
	 *
	 * @return  string  The methods, or an empty string for an unaffected view.
	 *
	 * @since   6.1.7
	 * @since   6.2.1  Emit named model methods and register the shared evaluator.
	 */
	public function get($view, $Component): string
	{
		if ($this->validationfix->getConditions($view) === [])
		{
			return '';
		}

		$this->registry->set(
			'validation.rules.conditionalrequired',
			$this->conditionalrule->get()
		);

		return PHP_EOL . PHP_EOL
			. $this->indentTemplate($this->joomlaPowers($this->modelMethods()));
	}

	/**
	 * Render the model's orchestration and deferred stored-record access.
	 *
	 * Model plugins and filters run inside the one parent validation call. The
	 * named resolver is invoked by the native rule only after that filtering.
	 *
	 * @return  string  Static generated PHP, without definition values.
	 *
	 * @since   6.2.1
	 */
	private function modelMethods(): string
	{
		return <<<'PHP'
	/**
	 * Validate conditional requirements through Joomla's native form lifecycle.
	 *
	 * @param   Form    $form   The original form to validate.
	 * @param   array   $data   Submitted data, before native filtering.
	 * @param   string  $group  The optional field group to validate.
	 *
	 * @return  array|false  Filtered valid data, or false when validation fails.
	 *
	 * @since   6.2.1
	 */
	public function validate($form, $data, $group = null)
	{
		$rule = __VALIDATION_FORM_HELPER__::loadRuleType('conditionalrequired');
		if (!$rule)
		{
			$this->setError('The conditional validation rule could not be loaded.');
			return false;
		}

		try
		{
			$prepared = $rule->prepare(
				$form,
				$this->conditionalRequirements,
				\Closure::fromCallable([$this, 'resolveConditionalData']),
				$group
			);
			if (!$prepared)
			{
				$this->setError($rule->getError());
				return false;
			}

			$result = $rule->complete(
				parent::validate($form, $rule->filterInternalInput($data), $group)
			);
			if ($rule->getError() !== null)
			{
				$this->setError($rule->getError());
			}

			return $result;
		}
		finally
		{
			$rule->release();
		}
	}

	/**
	 * Load permitted stored selector values after native input filtering.
	 *
	 * PATCH omission and protected selectors can require the stored record.
	 * Keeping this lookup here preserves model-specific getItem() behavior.
	 *
	 * @param   array   $data   Filtered input received by the native rule.
	 * @param   Form    $form   The form after native validation plugins have run.
	 * @param   string  $group  The optional field group being validated.
	 *
	 * @return  array|false  Stored values and PATCH mode, or false for a missing record.
	 *
	 * @since   6.2.1
	 */
	protected function resolveConditionalData(array $data, $form, $group = null)
	{
		$application = __VALIDATION_FACTORY__::getApplication();
		$context = [
			'stored' => [],
			'patch' => $application->isClient('api')
				&& $application->getInput()->getMethod() === 'PATCH',
		];
		$recordId = (int) ($data['id'] ?? $this->getState($this->getName() . '.id', 0));
		if ($recordId > 0)
		{
			$stored = $this->getItem($recordId);
			if ($stored === false || $stored === null)
			{
				return false;
			}

			$stored = new __VALIDATION_REGISTRY__($stored);
			$context['stored'] = $group ? (array) $stored->get($group, []) : $stored->toArray();
		}

		return $context;
	}
PHP;
	}

	/**
	 * Insert deferred Joomla Power keys into static generated templates.
	 *
	 * Split keys prevent JCB self-compilation from resolving references belonging
	 * to the later generated model. Definition values never pass through here.
	 *
	 * @param   string  $code  The static generated-code template.
	 *
	 * @return  string  The template containing deferred Joomla Power keys.
	 *
	 * @since   6.2.0
	 */
	protected function joomlaPowers(string $code): string
	{
		return strtr($code, [
			'__VALIDATION_FORM_HELPER__' => 'Joomla__' . '_571422c4_0340_49f8_b846_5729c7af6ed7___Power',
			'__VALIDATION_REGISTRY__' => 'Joomla__' . '_a87c432d_b5b4_428e_b7ff_14b51664c624___Power',
			'__VALIDATION_FACTORY__' => 'Joomla__' . '_39403062_84fb_46e0_bac4_0023f766e827___Power',
		]);
	}

	/**
	 * Apply the configured indentation to a static model template.
	 *
	 * Definition values are exported separately and never pass through this
	 * formatter, so their literal whitespace cannot be changed here.
	 *
	 * @param   string  $code  Static PHP with tab-indented source lines.
	 *
	 * @return  string  The model methods using the configured indentation.
	 *
	 * @since   6.2.1
	 */
	private function indentTemplate(string $code): string
	{
		$lines = explode(PHP_EOL, $code);
		foreach ($lines as &$line)
		{
			$level = strspn($line, "\t");
			if ($level > 0)
			{
				$line = Indent::_($level) . substr($line, $level);
			}
		}
		unset($line);

		return implode(PHP_EOL, $lines);
	}

	/**
	 * Export a multiline PHP array while preserving every scalar value exactly.
	 *
	 * Indentation is added only around array syntax. Reindenting a completed
	 * literal would change strings containing actual line breaks and tabs.
	 *
	 * @param   mixed  $value  A normalized scalar or array.
	 * @param   int    $level  Indentation of the containing declaration.
	 *
	 * @return  string  An executable PHP literal.
	 *
	 * @since   6.2.0
	 * @since   6.2.1  Format arrays structurally without rewriting scalar literals.
	 */
	protected function export($value, int $level = 0): string
	{
		if (!is_array($value))
		{
			return var_export($value, true);
		}
		if ($value === [])
		{
			return '[]';
		}

		$list = array_is_list($value);
		$items = [];
		foreach ($value as $key => $item)
		{
			$items[] = Indent::_($level + 1)
				. ($list ? '' : var_export($key, true) . ' => ')
				. $this->export($item, $level + 1) . ',';
		}

		return '[' . PHP_EOL . implode(PHP_EOL, $items)
			. PHP_EOL . Indent::_($level) . ']';
	}
}

