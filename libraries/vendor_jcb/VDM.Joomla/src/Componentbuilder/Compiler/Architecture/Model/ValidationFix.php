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
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
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

		return PHP_EOL . PHP_EOL . Indent::_(1) . '/**'
			. PHP_EOL . Indent::_(1) . ' * Conditional requirements generated from this view\'s field conditions.'
			. PHP_EOL . Indent::_(1) . ' *'
			. PHP_EOL . Indent::_(1) . ' * Matches within a group are combined with AND. Groups retain their'
			. PHP_EOL . Indent::_(1) . ' * authored order; the shared rule evaluates filtered values before'
			. PHP_EOL . Indent::_(1) . ' * Joomla checks required fields. Browser exemption lists are ignored.'
			. PHP_EOL . Indent::_(1) . ' *'
			. PHP_EOL . Indent::_(1) . ' * @var    array'
			. PHP_EOL . Indent::_(1) . ' * @since  6.2.1'
			. PHP_EOL . Indent::_(1) . ' */'
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

		return PHP_EOL . PHP_EOL . $this->modelMethods();
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
		return Indent::_(1) . '/**'
			. PHP_EOL . Indent::_(1) . ' * Validate conditional requirements through Joomla\'s native form lifecycle.'
			. PHP_EOL . Indent::_(1) . ' *'
			. PHP_EOL . Indent::_(1) . ' * @param   Form    $form   The original form to validate.'
			. PHP_EOL . Indent::_(1) . ' * @param   array   $data   Submitted data, before native filtering.'
			. PHP_EOL . Indent::_(1) . ' * @param   string  $group  The optional field group to validate.'
			. PHP_EOL . Indent::_(1) . ' *'
			. PHP_EOL . Indent::_(1) . ' * @return  array|false  Filtered valid data, or false when validation fails.'
			. PHP_EOL . Indent::_(1) . ' *'
			. PHP_EOL . Indent::_(1) . ' * @since   6.2.1'
			. PHP_EOL . Indent::_(1) . ' */'
			. PHP_EOL . Indent::_(1) . 'public function validate($form, $data, $group = null)'
			. PHP_EOL . Indent::_(1) . '{'
			. PHP_EOL . Indent::_(2) . '//' . Line::_(__LINE__, __CLASS__) . ' Keep conditional requirements inside the native model validation lifecycle.'
			. PHP_EOL . Indent::_(2) . '$rule = Joomla__' . '_571422c4_0340_49f8_b846_5729c7af6ed7___Power::loadRuleType(\'conditionalrequired\');'
			. PHP_EOL . Indent::_(2) . 'if (!$rule)'
			. PHP_EOL . Indent::_(2) . '{'
			. PHP_EOL . Indent::_(3) . '$this->setError(\'The conditional validation rule could not be loaded.\');'
			. PHP_EOL . Indent::_(3) . 'return false;'
			. PHP_EOL . Indent::_(2) . '}'
			. PHP_EOL
			. PHP_EOL . Indent::_(2) . 'try'
			. PHP_EOL . Indent::_(2) . '{'
			. PHP_EOL . Indent::_(3) . '$prepared = $rule->prepare('
			. PHP_EOL . Indent::_(4) . '$form,'
			. PHP_EOL . Indent::_(4) . '$this->conditionalRequirements,'
			. PHP_EOL . Indent::_(4) . '\\Closure::fromCallable([$this, \'resolveConditionalData\']),'
			. PHP_EOL . Indent::_(4) . '$group'
			. PHP_EOL . Indent::_(3) . ');'
			. PHP_EOL . Indent::_(3) . 'if (!$prepared)'
			. PHP_EOL . Indent::_(3) . '{'
			. PHP_EOL . Indent::_(4) . '$this->setError($rule->getError());'
			. PHP_EOL . Indent::_(4) . 'return false;'
			. PHP_EOL . Indent::_(3) . '}'
			. PHP_EOL
			. PHP_EOL . Indent::_(3) . '$result = $rule->complete('
			. PHP_EOL . Indent::_(4) . 'parent::validate($form, $rule->filterInternalInput($data), $group)'
			. PHP_EOL . Indent::_(3) . ');'
			. PHP_EOL . Indent::_(3) . 'if ($rule->getError() !== null)'
			. PHP_EOL . Indent::_(3) . '{'
			. PHP_EOL . Indent::_(4) . '$this->setError($rule->getError());'
			. PHP_EOL . Indent::_(3) . '}'
			. PHP_EOL
			. PHP_EOL . Indent::_(3) . 'return $result;'
			. PHP_EOL . Indent::_(2) . '}'
			. PHP_EOL . Indent::_(2) . 'finally'
			. PHP_EOL . Indent::_(2) . '{'
			. PHP_EOL . Indent::_(3) . '$rule->release();'
			. PHP_EOL . Indent::_(2) . '}'
			. PHP_EOL . Indent::_(1) . '}'
			. PHP_EOL
			. PHP_EOL . Indent::_(1) . '/**'
			. PHP_EOL . Indent::_(1) . ' * Load permitted stored selector values after native input filtering.'
			. PHP_EOL . Indent::_(1) . ' *'
			. PHP_EOL . Indent::_(1) . ' * PATCH omission and protected selectors can require the stored record.'
			. PHP_EOL . Indent::_(1) . ' * Keeping this lookup here preserves model-specific getItem() behavior.'
			. PHP_EOL . Indent::_(1) . ' *'
			. PHP_EOL . Indent::_(1) . ' * @param   array   $data   Filtered input received by the native rule.'
			. PHP_EOL . Indent::_(1) . ' * @param   Form    $form   The form after native validation plugins have run.'
			. PHP_EOL . Indent::_(1) . ' * @param   string  $group  The optional field group being validated.'
			. PHP_EOL . Indent::_(1) . ' *'
			. PHP_EOL . Indent::_(1) . ' * @return  array|false  Stored values and PATCH mode, or false for a missing record.'
			. PHP_EOL . Indent::_(1) . ' *'
			. PHP_EOL . Indent::_(1) . ' * @since   6.2.1'
			. PHP_EOL . Indent::_(1) . ' */'
			. PHP_EOL . Indent::_(1) . 'protected function resolveConditionalData(array $data, $form, $group = null)'
			. PHP_EOL . Indent::_(1) . '{'
			. PHP_EOL . Indent::_(2) . '//' . Line::_(__LINE__, __CLASS__) . ' Resolve stored selectors only after native validation plugins and filtering.'
			. PHP_EOL . Indent::_(2) . '$application = Joomla__' . '_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();'
			. PHP_EOL . Indent::_(2) . '$context = ['
			. PHP_EOL . Indent::_(3) . '\'stored\' => [],'
			. PHP_EOL . Indent::_(3) . '\'patch\' => $application->isClient(\'api\')'
			. PHP_EOL . Indent::_(4) . '&& $application->getInput()->getMethod() === \'PATCH\','
			. PHP_EOL . Indent::_(2) . '];'
			. PHP_EOL . Indent::_(2) . '$recordId = (int) ($data[\'id\'] ?? $this->getState($this->getName() . \'.id\', 0));'
			. PHP_EOL . Indent::_(2) . 'if ($recordId > 0)'
			. PHP_EOL . Indent::_(2) . '{'
			. PHP_EOL . Indent::_(3) . '$stored = $this->getItem($recordId);'
			. PHP_EOL . Indent::_(3) . 'if ($stored === false || $stored === null)'
			. PHP_EOL . Indent::_(3) . '{'
			. PHP_EOL . Indent::_(4) . 'return false;'
			. PHP_EOL . Indent::_(3) . '}'
			. PHP_EOL
			. PHP_EOL . Indent::_(3) . '$stored = new Joomla__' . '_a87c432d_b5b4_428e_b7ff_14b51664c624___Power($stored);'
			. PHP_EOL . Indent::_(3) . '$context[\'stored\'] = $group ? (array) $stored->get($group, []) : $stored->toArray();'
			. PHP_EOL . Indent::_(2) . '}'
			. PHP_EOL
			. PHP_EOL . Indent::_(2) . 'return $context;'
			. PHP_EOL . Indent::_(1) . '}';
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

