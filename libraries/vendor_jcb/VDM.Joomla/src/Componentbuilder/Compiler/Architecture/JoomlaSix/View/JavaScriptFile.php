<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    23rd October, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\View;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\JavaScriptFile as SharedJavaScriptFile;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * Place Joomla 6 view scripts inside the component media package.
 * 
 * @since  6.2.0
 */
final class JavaScriptFile extends SharedJavaScriptFile
{
	/**
	 * Render one entry in the template-owned view script list.
	 *
	 * @param   string  $path  The installed asset path.
	 *
	 * @return  string  The escaped PHP array entry.
	 * @since   6.2.0
	 */
	protected function render(string $path): string
	{
		return PHP_EOL . Indent::_(3) . "//" . Line::_(__LINE__, __CLASS__)
			. " Add View JavaScript File" . PHP_EOL
			. Indent::_(3) . var_export(ltrim($path, '/'), true) . ',';
	}

	/**
	 * Resolve the public script path for this view.
	 *
	 * @param   string  $name  The view code name.
	 *
	 * @return  string  The installed asset path.
	 * @since   6.2.0
	 */
	protected function getPath(string $name): string
	{
		$client = $this->config->build_target === 'site' ? 'site' : 'admin';

		return '/media/com_' . $this->config->component_code_name
			. '/js/' . $client . '/' . $name . '.js';
	}

	/**
	 * Build and replace every optional script slot for the current view.
	 *
	 * Optional view_script_paths and view_script_dependencies are string lists.
	 * Missing lists are empty; entries remain local to this view.
	 *
	 * @param   array   $view    The current view definition.
	 * @param   string  $TARGET  The upper-case build target.
	 *
	 * @return  void
	 * @throws  \InvalidArgumentException  When a supplied input is not a string list.
	 * @since   6.2.1
	 */
	public function set(array &$view, string $TARGET): void
	{
		$paths = $this->entries($view, 'view_script_paths');
		$dependencies = $this->entries($view, 'view_script_dependencies');
		$prefix = $view['settings']->code . '|' . $TARGET;
		$migrated = $this->documentScripts(
			$this->contentmulti->get($prefix . '_DOCUMENT_CUSTOM_PHP', '')
		);

		$this->contentmulti->set($prefix . '_VIEW_SCRIPT_ENTRY',
			$this->get($view, $TARGET)
		);
		if ($migrated !== null)
		{
			$paths = $migrated . $paths;
			$this->contentmulti->set($prefix . '_DOCUMENT_CUSTOM_PHP', '');
		}
		$this->contentmulti->set($prefix . '_VIEW_SCRIPT_PATHS', $paths === '' ? '' : $paths . PHP_EOL);
		$this->contentmulti->set($prefix . '_VIEW_SCRIPT_DEPENDENCIES', $dependencies);
	}

	/**
	 * Expand and escape an explicitly supplied ordered list.
	 *
	 * @param   array   $view      The current view definition.
	 * @param   string  $property  The scoped settings property to read.
	 *
	 * @return  string  PHP array entries, or an empty string for absent input.
	 * @throws  \InvalidArgumentException  When the input contains invalid values.
	 * @since   6.2.1
	 */
	private function entries(array $view, string $property): string
	{
		$values = $view['settings']->{$property} ?? [];

		if (!is_array($values) || !array_is_list($values))
		{
			throw new \InvalidArgumentException($property . ' must be an ordered list of strings.');
		}

		$entries = '';

		foreach ($values as $value)
		{
			if (!is_string($value) || trim($value) === '')
			{
				throw new \InvalidArgumentException($property . ' must contain non-empty strings.');
			}

			$value = $this->placeholder->update_($value);

			if (trim($value) === '')
			{
				throw new \InvalidArgumentException($property . ' expanded to an empty value.');
			}

			$entries .= $property === 'view_script_dependencies'
				? ', ' . var_export($value, true)
				: PHP_EOL . Indent::_(3) . var_export($value, true) . ',';
		}

		return $entries;
	}

	/**
	 * Migrate an asset-only PHP Document block without evaluating authored code.
	 *
	 * Every statement must be a literal Html script include with automatic
	 * versioning. Mixed, conditional or dynamic PHP remains completely intact.
	 * Comments follow their paths into the ordered list; string tokens retain
	 * their original PHP escaping without decoding or executing them.
	 *
	 * @param   string  $php  The already-rendered PHP Document contribution.
	 *
	 * @return  string|null  Array entries, or null when the block is not eligible.
	 * @since   6.2.1
	 */
	private function documentScripts(string $php): ?string
	{
		$entries = '';
		$statement = [];
		$found = false;

		foreach (token_get_all('<?php ' . $php) as $token)
		{
			if (is_array($token) && in_array($token[0], [T_OPEN_TAG, T_WHITESPACE], true))
			{
				continue;
			}
			if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true))
			{
				$comment = trim($token[1]);
				$comment = str_starts_with($comment, '// add ') ? '// Add ' . substr($comment, 7) : $comment;
				$entries .= PHP_EOL . Indent::_(3)
					. str_replace("\n", "\n" . Indent::_(3), $comment);
				continue;
			}

			$statement[] = $token;
			if ($token !== ';')
			{
				continue;
			}
			if (count($statement) !== 15)
			{
				return null;
			}

			$parts = array_map(static fn($part) => is_array($part) ? $part[1] : $part, $statement);
			if ($parts !== [$parts[0], '::', '_', '(', $parts[4], ',', $parts[6], ',',
				'[', $parts[9], '=>', $parts[11], ']', ')', ';']
				|| !in_array($parts[0], ['Html', 'Joomla__' . '_34690c75_1090_47eb_8c06_7228dc7eedd6___Power'], true)
				|| !in_array($parts[4], ["'script'", '"script"'], true)
				|| !is_array($statement[6]) || $statement[6][0] !== T_CONSTANT_ENCAPSED_STRING
				|| in_array($parts[6], ["''", '""'], true)
				|| !in_array($parts[9], ["'version'", '"version"'], true)
				|| !in_array($parts[11], ["'auto'", '"auto"'], true))
			{
				return null;
			}

			$entries .= PHP_EOL . Indent::_(3) . $parts[6] . ',';
			$statement = [];
			$found = true;
		}

		return $found && $statement === [] ? $entries : null;
	}
}

