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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Plugin;


use InvalidArgumentException;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne as Builder;
use VDM\Joomla\Componentbuilder\Power\Parser;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\PowerInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Plugin\ExtensionInterface;


/**
 * Native Joomla 6 plugin subscribers.
 * 
 * Stored explicit subscription maps remain authoritative. Implicit on* handlers
 * use native events; jcb_ce_* hooks retain the compiler's positional arguments.
 * Reviewed bundled privacy defaults are emitted natively; other positional
 * legacy definitions require explicit migration.
 * 
 * @since 5.1.2
 */
final class Extension implements ExtensionInterface
{
	/**
	 * Placeholder service.
	 *
	 * @var Placeholder
	 * @since 5.0.2
	 */
	protected Placeholder $placeholder;

	/**
	 * Global content builder.
	 *
	 * @var Builder
	 * @since 5.0.2
	 */
	protected Builder $builder;

	/**
	 * PHP structure parser.
	 *
	 * @var Parser
	 * @since 5.0.2
	 */
	protected Parser $parser;

	/**
	 * Target Joomla Power definitions.
	 *
	 * @var PowerInterface
	 * @since 6.2.0
	 */
	protected PowerInterface $power;

	/**
	 * Reviewed bundled privacy definitions.
	 *
	 * @var Privacy
	 * @since 6.2.0
	 */
	protected Privacy $privacy;

	/**
	 * Constructor.
	 *
	 * @param Placeholder    $placeholder The placeholder service.
	 * @param Builder        $builder     The global content builder.
	 * @param Parser         $parser      The PHP structure parser.
	 * @param PowerInterface $power       The target Joomla Power definitions.
	 * @param Privacy        $privacy     The bundled privacy definition renderer.
	 *
	 * @since 6.2.0
	 */
	public function __construct(Placeholder $placeholder, Builder $builder, Parser $parser, PowerInterface $power, Privacy $privacy)
	{
		$this->placeholder = $placeholder;
		$this->builder = $builder;
		$this->parser = $parser;
		$this->power = $power;
		$this->privacy = $privacy;
	}

	/**
	 * Render without changing the stored definition or retaining per-plugin state.
	 *
	 * @param object $plugin The plugin definition.
	 *
	 * @return string
	 * @since 5.0.2
	 */
	public function get(object $plugin): string
	{
		// API_ROUTES_METHOD and other generated methods must exist before inspection.
		$code = $this->placeholder->update_(
			$this->placeholder->update($plugin->main_class_code, $this->builder->allActive())
		);
		$code = $this->privacy->get($plugin, $code);
		$methods = $this->methods($code);
		$explicit = false;

		foreach ($methods as $method)
		{
			$name = strtolower($method['name']);

			if ($name === 'registerlisteners')
			{
				$this->migrationRequired($plugin, $method, 'replace registerListeners() with getSubscribedEvents(); overriding it selects Joomla\'s deprecated listener registration');
			}

			if ($name === '__construct')
			{
				$this->validateConstructor($plugin, $method);
			}

			if ($name === 'getsubscribedevents')
			{
				$signature = array_values(array_filter(token_get_all('<?php ' . $method['declaration']), static function ($token): bool
				{
					return !is_array($token) || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);
				}));
				$returnType = array_pop($signature);
				$returnsArray = is_array($returnType) && $returnType[0] === T_ARRAY && array_pop($signature) === ':';

				if ($method['access'] !== 'public' || !$method['static'] || $method['abstract']
					|| $this->requiredArguments($method) > 0
					|| !$returnsArray)
				{
					$this->migrationRequired($plugin, $method, 'declare public static function getSubscribedEvents(): array with no required arguments');
				}

				$explicit = true;
			}
		}

		if (!$explicit)
		{
			$events = [];

			foreach ($methods as $method)
			{
				if ($method['access'] !== 'public' || $method['static'] || $method['abstract'])
				{
					continue;
				}

				if (str_starts_with($method['name'], 'jcb_ce_'))
				{
					// The compiler invokes these native subscribers with positional references.
					$events[$method['name']] = $method['name'];
				}
				elseif (str_starts_with($method['name'], 'on'))
				{
					$this->validateHandler($plugin, $method);
					$events[$method['name']] = $method['name'];
				}
			}

			$code .= $this->subscriptionMethod($events);
		}

		$extension = $plugin->comment . PHP_EOL . 'final class ' . $plugin->class_name
			. ' extends ' . $plugin->extends
			. ' implements Joomla__' . '_c06c5116_6b9d_487c_9b09_5094ec4506a3___Power'
			. PHP_EOL . '{' . PHP_EOL . $code . PHP_EOL . '}' . PHP_EOL;

		return $this->placeholder->update_($this->placeholder->update($extension, $this->builder->allActive()));
	}

	/**
	 * Select class-level methods; nested declarations belong to their own scope.
	 *
	 * @param string $code The stored class body.
	 *
	 * @return array
	 * @since 6.2.0
	 */
	protected function methods(string $code): array
	{
		$methods = [];
		$member = '';
		$depth = 0;
		$function = false;

		foreach (token_get_all('<?php ' . $code) as $token)
		{
			if (is_array($token) && $token[0] === T_OPEN_TAG)
			{
				continue;
			}

			if ($depth === 0 && is_array($token) && in_array($token[0],
				[T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_FINAL, T_ABSTRACT, T_FUNCTION], true))
			{
				// Inspect legal inline/attributed declarations without rewriting stored source.
				$member .= PHP_EOL;
				$function = $function || $token[0] === T_FUNCTION;
			}

			$member .= is_array($token) ? $token[1] : $token;

			if ($token === '{' || (is_array($token)
				&& in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true)))
			{
				$depth++;
			}
			elseif ($token === '}')
			{
				$depth--;
			}

			if ($depth === 0 && ($token === ';' || $token === '}'))
			{
				if ($function)
				{
					$parsed = $this->parser->code($member)['methods'] ?? [];

					if ($parsed !== [])
					{
						$methods[] = $parsed[0];
					}
				}

				$member = '';
				$function = false;
			}
		}

		return $methods;
	}

	/**
	 * Require the config-only constructor used by the Joomla 6 provider.
	 *
	 * @param object $plugin The plugin definition.
	 * @param array  $method The parsed constructor.
	 *
	 * @return void
	 * @since 6.2.0
	 */
	protected function validateConstructor(object $plugin, array $method): void
	{
		$arguments = $method['arguments'] ?? [];
		$first = reset($arguments);
		$name = strtolower((string) key($arguments));
		$type = strtolower(preg_replace('/\s+/', '', $first['type'] ?? ''));

		if ($method['access'] !== 'public' || $this->requiredArguments($method) > 1
			|| ($first && $type !== '' && array_intersect(explode('|', ltrim($type, '?')), ['array', 'mixed']) === [])
			|| ($type === '' && in_array($name, ['$dispatcher', '$subject'], true))
			|| $this->legacyParentConstructor($method['body'] ?? ''))
		{
			$this->migrationRequired($plugin, $method, 'use the config-only constructor __construct(array $config = []), calling parent::__construct($config); the provider no longer supplies a dispatcher');
		}
	}

	/**
	 * Identify the old parent::__construct($dispatcher, $config) call structurally.
	 *
	 * @param string $body The constructor body.
	 *
	 * @return bool
	 * @since 6.2.0
	 */
	protected function legacyParentConstructor(string $body): bool
	{
		$tokens = array_values(array_filter(token_get_all('<?php ' . $body), static function ($token): bool
		{
			return !is_array($token) || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);
		}));
		$count = count($tokens);

		for ($i = 0; $i < $count - 3; $i++)
		{
			if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_STRING || strtolower($tokens[$i][1]) !== 'parent'
				|| !is_array($tokens[$i + 1]) || $tokens[$i + 1][0] !== T_DOUBLE_COLON
				|| !is_array($tokens[$i + 2]) || strtolower($tokens[$i + 2][1]) !== '__construct'
				|| $tokens[$i + 3] !== '(')
			{
				continue;
			}

			$depth = 1;

			for ($j = $i + 4; $j < $count && $depth > 0; $j++)
			{
				$token = $tokens[$j];

				if ($depth === 1 && $token === ',' && ($tokens[$j + 1] ?? null) !== ')')
				{
					return true;
				}

				if (in_array($token, ['(', '[', '{'], true))
				{
					$depth++;
				}
				elseif (in_array($token, [')', ']', '}'], true))
				{
					$depth--;
				}
			}
		}

		return false;
	}

	/**
	 * An inferred subscription must consume an event and must not return legacy results.
	 *
	 * @param object $plugin The plugin definition.
	 * @param array  $method The parsed handler.
	 *
	 * @return void
	 * @since 6.2.0
	 */
	protected function validateHandler(object $plugin, array $method): void
	{
		$arguments = $method['arguments'] ?? [];
		$first = reset($arguments);

		if ($this->requiredArguments($method) > 1
			|| ($first && !$this->eventType($first['type'] ?? '', $plugin->header ?? ''))
			|| $this->hasValueReturn($method['body'] ?? ''))
		{
			$this->migrationRequired($plugin, $method, 'migrate this handler to one typed Joomla event (additional parameters may be optional), and write results to that event instead of returning legacy results. Intentional custom event signatures require an explicit native getSubscribedEvents() map after migration');
		}
	}

	/**
	 * Find the minimum accepted argument count, including optional and variadic parameters.
	 *
	 * @param array $method The parsed method.
	 *
	 * @return int
	 * @since 6.2.0
	 */
	protected function requiredArguments(array $method): int
	{
		$count = 0;
		$variadic = false;
		$variadics = [];

		foreach (token_get_all('<?php ' . $method['declaration']) as $token)
		{
			if (is_array($token) && $token[0] === T_ELLIPSIS)
			{
				$variadic = true;
			}
			elseif ($variadic && is_array($token) && $token[0] === T_VARIABLE)
			{
				$variadics[$token[1]] = true;
				$variadic = false;
			}
		}

		$position = 0;

		foreach ($method['arguments'] ?? [] as $name => $argument)
		{
			$position++;
			if ($argument['default'] === null && !isset($variadics[$name]))
			{
				$count = $position;
			}
		}

		return $count;
	}

	/**
	 * Resolve event imports and Joomla Powers for inspection without replacing tokens.
	 *
	 * @param string $type   The argument type.
	 * @param string $header The stored plugin imports.
	 *
	 * @return bool
	 * @since 6.2.0
	 */
	protected function eventType(string $type, string $header): bool
	{
		$imports = [];

		foreach ($this->parser->getUseStatements($header) ?? [] as $statement)
		{
			$statement = trim(substr(trim($statement), 3), " \t\n\r;");
			$prefix = '';

			if (str_contains($statement, '{'))
			{
				[$prefix, $statement] = explode('{', $statement, 2);
				$statement = rtrim($statement, " }\t\n\r");
			}

			foreach (explode(',', $statement) as $import)
			{
				$parts = preg_split('/\s+as\s+/i', trim($import));
				$class = trim($prefix . $parts[0], " \\\t\r\n");
				$alias = $parts[1] ?? substr($class, strrpos($class, '\\') + 1);
				$imports[strtolower($alias)] = $class;
			}
		}

		foreach (explode('|', ltrim($type, '?')) as $candidate)
		{
			$candidate = trim($candidate, " \\\t\n\r");

			if (preg_match('/^Joomla___([a-f0-9_]{36})___Power$/', $candidate, $match))
			{
				$power = $this->power->get(str_replace('_', '-', $match[1]), 0);
				$candidate = $power ? $power->_namespace . '\\' . $power->class_name : '';
			}
			else
			{
				$parts = explode('\\', $candidate, 2);
				$candidate = isset($imports[strtolower($parts[0])])
					? $imports[strtolower($parts[0])] . (isset($parts[1]) ? '\\' . $parts[1] : '')
					: $candidate;
			}

			$candidate = strtolower($candidate);

			if (in_array($candidate, ['joomla\\event\\eventinterface', 'joomla\\event\\event', 'joomla\\event\\abstractevent'], true)
				|| str_starts_with($candidate, 'joomla\\cms\\event\\'))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Detect value returns, excluding comments, strings and nested function/class bodies.
	 *
	 * @param string $body The parsed method body.
	 *
	 * @return bool
	 * @since 6.2.0
	 */
	protected function hasValueReturn(string $body): bool
	{
		$tokens = token_get_all('<?php ' . $body);
		$nested = false;
		$depth = 0;
		$return = false;
		$previous = null;

		foreach ($tokens as $token)
		{
			if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true))
			{
				continue;
			}

			if ($return)
			{
				if (is_array($token) && $token[0] === T_STRING && strtolower($token[1]) === 'null')
				{
					continue;
				}

				if ($token !== ';')
				{
					return true;
				}

				$return = false;
			}

			if (is_array($token) && ($token[0] === T_FUNCTION
				|| ($token[0] === T_CLASS && $previous !== T_DOUBLE_COLON)))
			{
				$nested = true;
			}

			$previous = is_array($token) ? $token[0] : $token;

			if ($nested)
			{
				if ($token === '{' || (is_array($token)
					&& in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true)))
				{
					$depth++;
				}
				elseif ($token === '}' && --$depth === 0)
				{
					$nested = false;
				}

				continue;
			}

			$return = is_array($token) && $token[0] === T_RETURN;
		}

		return false;
	}

	/**
	 * Generate a map and retain subscriptions inherited from a native subscriber base.
	 *
	 * @param array $events Event names and their handler names.
	 *
	 * @return string
	 * @since 6.2.0
	 */
	protected function subscriptionMethod(array $events): string
	{
		$script = PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . ' *' . Line::_(__LINE__, __CLASS__)
			. ' Native event subscriptions.';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1) . ' * @return array';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'public static function getSubscribedEvents(): array';
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= PHP_EOL . Indent::_(2) . '$events = is_subclass_of(parent::class, Joomla__' . '_c06c5116_6b9d_487c_9b09_5094ec4506a3___Power::class)';
		$script .= PHP_EOL . Indent::_(3) . '? parent::getSubscribedEvents() : [];';
		$script .= PHP_EOL;

		foreach ($events as $name => $handler)
		{
			$script .= PHP_EOL . Indent::_(2) . '$events[' . var_export($name, true) . '] = ' . var_export($handler, true) . ';';
		}

		$script .= PHP_EOL . Indent::_(2) . 'return $events;';
		$script .= PHP_EOL . Indent::_(1) . '}';

		return $script;
	}

	/**
	 * Report the exact definition that requires an intentional native migration.
	 *
	 * @param object $plugin The plugin definition.
	 * @param array  $method The parsed method.
	 * @param string $reason The migration guidance.
	 *
	 * @return never
	 * @since 6.2.0
	 */
	protected function migrationRequired(object $plugin, array $method, string $reason): never
	{
		throw new InvalidArgumentException('Joomla 6 plugin ' . $plugin->class_name . '::'
			. $method['name'] . '(): ' . $reason . '. Joomla 3–5 definitions are unchanged.');
	}
}

