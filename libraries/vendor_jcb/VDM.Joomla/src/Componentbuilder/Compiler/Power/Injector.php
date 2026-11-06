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

namespace VDM\Joomla\Componentbuilder\Compiler\Power;


use VDM\Joomla\Componentbuilder\Compiler\Interfaces\PowerInterface as Power;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Power\ExtractorInterface as Extractor;
use VDM\Joomla\Componentbuilder\Power\Parser;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Power\InjectorInterface;


/**
 * Compiler Power Injector
 * @since 3.2.0
 */
class Injector implements InjectorInterface
{
	/**
	 * Power Objects
	 *
	 * @var    Power
	 * @since 3.2.0
	 **/
	protected Power $power;

	/**
	 * Compiler Powers Extractor
	 *
	 * @var    Extractor
	 * @since 3.2.0
	 **/
	protected Extractor $extractor;

	/**
	 * Compiler Powers Parser
	 *
	 * @var    Parser
	 * @since 3.2.0
	 **/
	protected Parser $parser;

	/**
	 * Compiler Placeholder
	 *
	 * @var    Placeholder
	 * @since 3.2.0
	 */
	protected Placeholder $placeholder;

	/**
	 * Super Power Update Map
	 *
	 * @var    array
	 * @since 3.2.0
	 */
	protected array $map = [];

	/**
	 * Insert Use Statements
	 *
	 * @var    array
	 * @since 3.2.0
	 */
	protected array $useStatements = [];

	/**
	 * Insert Trait Statements
	 *
	 * @var    array
	 * @since 3.2.0
	 */
	protected array $traits = [];

	/**
	 * Other Statements
	 *
	 * @var    array
	 * @since 3.2.0
	 */
	protected array $other = [];

	/**
	 * Duplicate Statements
	 *
	 * @var    array
	 * @since 3.2.0
	 */
	protected array $duplicate = [];

	/**
	 * Constructor.
	 *
	 * @param Power         $power        The power object.
	 * @param Extractor     $extractor    The powers extractor object.
	 * @param Parser        $parser       The powers parser object.
	 * @param Placeholder   $placeholder  The compiler placeholder object.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Power $power, Extractor $extractor,
		Parser $parser, Placeholder $placeholder)
	{
		$this->power = $power;
		$this->extractor = $extractor;
		$this->parser = $parser;
		$this->placeholder = $placeholder;
	}

	/**
	 * Inject the powers found in the code
	 *
	 * @param string   $code The class code
	 *
	 * @return string   The updated code
	 * @since 3.2.0
	 */
	public function power(string $code): string
	{
		if (($guids = $this->extractor->get($code)) !== null)
		{
			return $this->update($guids, $code);
		}

		return $code;
	}

	/**
	 * Update the code
	 *
	 * @param array   $guids The Power guids found
	 * @param string   $code The class code
	 *
	 * @return string   The updated code
	 * @since 3.2.0
	 */
	protected function update(array $guids, string $code): string
	{
		$use_statements = $this->parser->getUseStatements($code);
		$traits = $this->parser->getTraits(
			$this->parser->getClassCode($code) ?? ''
		);

		// reset with each file
		$this->map = [];
		$this->useStatements = [];
		$this->traits = [];
		$this->other = [];
		$this->duplicate = [];

		foreach ($guids as $key => $guid)
		{
			if  (($power = $this->power->get($guid)) !== null)
			{
				if (($name = $this->inspect($power, $use_statements, $traits)) !== null)
				{
					$this->map[$key] = $name;
				}
			}
		}

		// update
		if ($this->map !== [])
		{
			if ($this->useStatements !== [])
			{
				$code = $this->addUseStatements($code, $use_statements);
			}

			return $this->placeholder->update($code, $this->map);
		}

		return $code;
	}

	/**
	 * Inspect the super power to determine the necessary class name based on use statements and traits.
	 * It checks if the given power (class, trait, etc.) already has a corresponding use statement
	 * and handles the naming accordingly to avoid conflicts.
	 *
	 * @param object      $power           The power object containing type, namespace, and class name.
	 * @param array|null  $useStatements   Array of existing use statements in the code.
	 * @param array|null  $traits          Array of existing traits used in the code.
	 *
	 * @return string|null The determined class name, or null if the type is not valid.
	 * @since 3.2.0
	 */
	protected function inspect(object $power, ?array $useStatements, ?array $traits): ?string
	{
		$namespaceStatement = $this->buildNamespaceStatment($power);

		$use_extracted = $this->extractUseStatements($namespaceStatement, $power->class_name, $useStatements);

		$name = $use_extracted['found'] ?? $power->class_name;

		$name = $this->getUniqueName($name, $power);

		$this->handleTraitLogic($name, $power, $traits);

		if (!$use_extracted['hasStatement'])
		{
			$this->addUseStatement($name, $power->class_name, $namespaceStatement);
		}

		return $name;
	}

	/**
	 * Builds the namespace statement from the power object's namespace and class name.
	 *
	 * @param object $power  The power object.
	 *
	 * @return string The constructed use statement.
	 * @since 3.2.0
	 */
	protected function buildNamespaceStatment(object $power): string
	{
		return $power->_namespace . '\\' . $power->class_name;
	}

	/**
	 * Extracts and processes use statements to find if the current class name is already used.
	 * It identifies any potential naming conflicts.
	 *
	 * @param string      $useStatement     The search statement of the current class.
	 * @param string      $className         The class name of the power object.
	 * @param array|null  $useStatements     The existing use statements.
	 *
	 * @return array  An array with keys 'found' and 'hasStatement'.
	 * @since 3.2.0
	 */
	protected function extractUseStatements(string $useStatement, string $className, ?array $useStatements): array
	{
		$results = ['found' => null, 'hasStatement' => false];

		if ($useStatements !== null)
		{
			foreach ($useStatements as $use_statement)
			{
				$class_name = $this->extractClassNameOrAlias($use_statement);

				if ($this->isUseStatementEqual($use_statement, $useStatement))
				{
					if ($results['found'] === null)
					{
						$results['found'] = $class_name;
						$results['hasStatement'] = true;
					}
					else
					{
						// TODO we need to backport fix these
						$this->duplicate[$use_statement] = $class_name;
					}
				}
				elseif ($className === $class_name)
				{
					$this->other[$className] = $class_name;
				}
			}
		}

		return $results;
	}

	/**
	 * Checks if the namespace statement is already declared in the current use statements.
	 *
	 * This method uses a regular expression to check for an exact match of the full statement,
	 * taking into account the possibility of an alias being used.
	 *
	 * @param string  $useStatement     The existing use statement to check against.
	 * @param string  $namespaceStatement  The search statement to search for (without the trailing semicolon, or use prefix).
	 *
	 * @return bool True if the full statement is found, false otherwise.
	 */
	protected function isUseStatementEqual(string $useStatement, string $namespaceStatement): bool
	{
		// Create a regular expression pattern to match the full statement
		// The pattern checks for the start of the statement, optional whitespace,
		// and an optional alias after the full statement.
		$pattern = '/^use\s+' . preg_quote($namespaceStatement, '/') . '(?:\s+as\s+\w+)?;$/';

		// Perform the regex match to check if the use statement is equal to the search statment
		return (bool) preg_match($pattern, $useStatement);
	}

	/**
	 * Extracts the class name or alias from a use statement.
	 *
	 * This method parses a PHP 'use' statement and extracts either the class name or its alias.
	 * If the statement doesn't match the expected format, or if no class name or alias is found,
	 * the method returns null.
	 *
	 * Example: 
	 * - 'use Namespace\ClassName;' -> returns 'ClassName'
	 * - 'use Namespace\ClassName as Alias;' -> returns 'Alias'
	 *
	 * @param string  $useStatement  The use statement from which to extract the class name or alias.
	 *
	 * @return string|null The class name or alias if found, null otherwise.
	 * @since 3.2.0
	 */
	protected function extractClassNameOrAlias(string $useStatement): ?string
	{
		// If the input doesn't start with 'use ', assume it's just the namespace without a use statement
		if (strpos($useStatement, 'use ') !== 0)
		{
			return $this->extractLastNameFromNamespace($useStatement);
		}

		// Regular expression to extract the class name and alias from the use statement
		$pattern = '/use\s+(?P<namespace>[\w\\\\]+?)(?:\s+as\s+(?P<alias>\w+))?;/';

		if (preg_match($pattern, $useStatement, $matches))
		{
			// Return the alias if it exists; otherwise, return the last part of the namespace (class name)
			return $matches['alias'] ?? $this->extractLastNameFromNamespace($matches['namespace']);
		}

		// Return null if no match is found
		return null;
	}

	/**
	 * Ensures the name for the use statement is unique, avoiding conflicts with other classes.
	 *
	 * @param string  $name       The current name
	 * @param object  $power      The power object containing type, namespace, and class name.
	 *
	 * @return string  The unique name
	 * @since 3.2.0
	 */
	protected function getUniqueName(string $name, object $power): string
	{
		// set search namespace
		$namespace = ($name !== $power->class_name) ? $this->buildNamespaceStatment($power) : $power->_namespace;

		// check if we need to update the name
		if (isset($this->other[$name]))
		{
			// if the name is already used
			while (isset($this->other[$name]))
			{
				if (($tmp = $this->extractClassNameOrAlias($namespace)) !== null)
				{
					$name = ucfirst($tmp) . $name;
					$namespace = $this->removeLastNameFromNamespace($namespace);
				}
				else
				{
					$name = 'Unique' . $name;
				}
			}
		}

		// also loop new found use statements
		if (isset($this->useStatements[$name]))
		{
			// if the name is already used
			while (isset($this->useStatements[$name]))
			{
				if (($tmp = $this->extractClassNameOrAlias($namespace)) !== null)
				{
					$name = ucfirst($tmp) . $name;
					$namespace = $this->removeLastNameFromNamespace($namespace);
				}
				else
				{
					$name = 'Unique' . $name;
				}
			}
		}

		return $name;
	}

	/**
	 * Extracts the last part of a namespace string, which is typically the class name.
	 *
	 * @param string $namespace  The namespace string to extract from.
	 *
	 * @return string|null The extracted class name.
	 * @since 3.2.0
	 */
	protected function extractLastNameFromNamespace(string $namespace): ?string
	{
		$parts = explode('\\', $namespace);
		$result = end($parts);

		// Remove '\\' from the beginning and end of the resulting string
		$result = trim($result, '\\');

		// If the resulting string is empty, return null
		return empty($result) ? null : $result;
	}

	/**
	 * Removes the last name from the namespace.
	 *
	 * @param string $namespace The namespace
	 *
	 * @return string The namespace shortened
	 * @since 3.2.0
	 */
	protected function removeLastNameFromNamespace(string $namespace): string
	{
		// Remove '\\' from the beginning and end of the resulting string
		$namespace = trim($namespace, '\\');

		$parts = explode('\\', $namespace);

		// Remove the last part (the class name)
		array_pop($parts);

		// Reassemble the namespace without the class name
		return implode('\\', $parts);
	}

	/**
	 * Determines whether a trait statement should be added.
	 *
	 * @param object $power The power object.
	 *
	 * @return bool True if a trait statement should be added, false otherwise.
	 * @since 3.2.0
	 */
	protected function shouldAddTraitStatement(object $power): bool
	{
		return $power->type === 'trait';
	}

	/**
	 * Handles specific logic for traits, such as checking if the trait is already used.
	 *
	 * @param string      $name    The current name.
	 * @param object      $power   The power object containing type, namespace, and class name.
	 * @param array|null  $traits  The traits used in the code.
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function handleTraitLogic(string $name, object $power, ?array $traits): void
	{
		if ($this->shouldAddTraitStatement($power) && $traits !== null)
		{
			foreach ($traits as $trait)
			{
				if ($trait === $name)
				{
					return;
				}
			}
		}

		// add the trait
		$this->traits[$name] = 'use ' . $name . ';';
	}

	/**
	 * Adds a use statement to the class if it's not already present.
	 *
	 * @param string  $name                The name to use.
	 * @param string  $className           The class name of the power object.
	 * @param string  $namespaceStatement  The search statement to search for (without the trailing semicolon, or use prefix).
	 *
	 * @since 3.2.0
	 */
	protected function addUseStatement(string &$name, string $className, string $namespaceStatement): void
	{
		// we don't add use statements with just one part
		if ($this->countPartsInString($namespaceStatement) <= 1)
		{
			return; // we just update the code
		}

		if ($name !== $className)
		{
			$statement = 'use ' . $namespaceStatement . ' as ' . $name . ';';
		}
		else
		{
			$statement = 'use ' . $namespaceStatement . ';';
		}

		$this->useStatements[$name] = $statement;
	}

	/**
	 * Counts the number of parts in a string separated by backslashes.
	 *
	 * @param string $string The input string to be evaluated.
	 *
	 * @return int    The number of parts separated by backslashes.
	 * @since 5.0.2
	 */
	protected function countPartsInString(string $string): int
	{
		// Split the string by the backslash
		$parts = explode('\\', $string);

		// Count the number of parts and return the result
		if (($number = ArrayHelper::check($parts, true)) !== false)
		{
			return $number;
		}
		return 0;
	}

	/**
	 * Insert a line before the class declaration in the given class code.
	 *
	 * @param string       $code             The class code
	 * @param array|null   $useStatements    The existing use statements
	 *
	 * @return string The modified file content
	 * @since 3.2.0
	 */
	protected function addUseStatements(string $code, ?array $useStatements): string
	{
		if ($useStatements !== null)
		{
			// we add the use statements using existing use statements
			$key = end($useStatements);

			array_unshift($this->useStatements, $key);

			return $this->placeholder->update($code, [$key => implode(PHP_EOL, array_values($this->useStatements))]);
		}

		return $this->addLines($code, implode(PHP_EOL, array_values($this->useStatements)));
	}

	/**
	 * Insert a line before the class declaration in the given class code.
	 *
	 * @param string   $code     The class code
	 * @param string   $lines    The new lines to insert
	 *
	 * @return string The modified file content
	 * @since 3.2.0
	 */
	protected function addLines(string $code, string $lines): string
	{
		// Token offsets preserve comments, attributes, and the source line endings.
		$prefix = strpos($code, '<?') === false ? '<?php ' : '';
		$tokens = token_get_all($prefix . $code);
		$offset = -strlen($prefix);
		$declaration = null;
		$attribute_depth = 0;

		foreach ($tokens as $index => $token)
		{
			$id = is_array($token) ? $token[0] : null;
			$text = is_array($token) ? $token[1] : $token;

			if ($id === T_ATTRIBUTE)
			{
				$declaration ??= $offset;
				$attribute_depth++;
			}
			elseif ($attribute_depth > 0)
			{
				if ($token === '[')
				{
					$attribute_depth++;
				}
				elseif ($token === ']')
				{
					$attribute_depth--;
				}
			}
			elseif (in_array($id, [T_CLASS, T_INTERFACE, T_TRAIT], true))
			{
				// A named declaration follows with T_STRING; anonymous classes and
				// Foo::class references must not become import insertion points.
				$next = $index + 1;
				while (isset($tokens[$next]) && is_array($tokens[$next])
					&& in_array($tokens[$next][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true))
				{
					$next++;
				}

				if (isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0] === T_STRING)
				{
					return substr_replace($code, $lines . PHP_EOL, $declaration ?? $offset, 0);
				}

				$declaration = null;
			}
			elseif (in_array($id, [T_COMMENT, T_DOC_COMMENT, T_FINAL, T_ABSTRACT, T_READONLY], true))
			{
				$declaration ??= $offset;
			}
			elseif ($id !== T_WHITESPACE)
			{
				$declaration = null;
			}

			$offset += strlen($text);
		}

		// Non-class files retain the Joomla direct-access guard fallback.
		return $this->addLinesAfterDefinedLine($code, $lines);
	}

	/**
	 * Inserts a new line after the defined('_JEXEC') line.
	 *
	 * @param string   $code     The class code
	 * @param string   $lines    The new lines to insert
	 *
	 * @return string The modified file content
	 * @since 3.2.0
	 */
	protected function addLinesAfterDefinedLine(string $code, string $lines): string
	{
		// Patterns to match the defined('_JEXEC') and defined('JPATH_BASE') lines
		$patterns = [
			"/defined\('_JEXEC'\)(.*?)\s*;/",
			"/\\defined\('_JEXEC'\)(.*?)\s*;/",
			"/defined\('JPATH_BASE'\)(.*?)\s*;/",
			"/\\defined\('JPATH_BASE'\)(.*?)\s*;/",
		];

		$insert_pos = null;

		// Iterate through the patterns and try to find a match
		foreach ($patterns as $pattern)
		{
			preg_match($pattern, $code, $matches, PREG_OFFSET_CAPTURE);
			$defined_line_pos = $matches[0][1] ?? null;

			if ($defined_line_pos !== null)
			{
				// Find the position of the newline character after the defined() line
				$next_lines_pos = strpos($code, PHP_EOL, (int) $defined_line_pos + strlen($matches[0][0]));

				// Insert the new line at the found position
				if ($next_lines_pos !== false)
				{
					$insert_pos = $next_lines_pos;
					break;
				}
			}
		}

		// Insert the new line at the found position
		if ($insert_pos !== null)
		{
			$code = substr_replace($code, PHP_EOL . $lines, $insert_pos, 0);
		}

		return $code;
	}
}

