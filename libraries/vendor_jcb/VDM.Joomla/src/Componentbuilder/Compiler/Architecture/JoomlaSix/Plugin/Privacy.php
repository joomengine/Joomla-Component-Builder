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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Plugin;


use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * Render the reviewed bundled privacy definitions as native Joomla 6 events.
 * 
 * Only selected store definitions with their exact executable default contract
 * are migrated. Edited handlers remain subject to the normal plugin diagnostic.
 * No stored source or earlier-target definition is changed.
 * 
 * @since 6.2.0
 */
final class Privacy
{
	/**
	 * Reviewed method identities and their native event/result contracts.
	 *
	 * @var array
	 * @since 6.2.0
	 */
	private const METHODS = [
		'onPrivacyCanRemoveData' => ['ddc9d897-8902-4ac3-bf38-f37e6af1a46a', 'CanRemoveDataEvent', '$status', 'new Joomla__' . '_5c599ecf_8f58_44b4_bbaf_a47eb5d302e5___Power()'],
		'onPrivacyExportRequest' => ['3692862c-6688-4afe-809e-c6677b318fea', 'ExportRequestEvent', '$domains', 'array()'],
		'onPrivacyRemoveData' => ['1e9e7ad9-ea31-43a2-a288-ffaa4bec5101', 'RemoveDataEvent', '', ''],
	];

	/**
	 * Replace reviewed default methods while preserving all surrounding source.
	 *
	 * @param object $plugin The plugin definition, including selected store methods.
	 * @param string $code   The resolved class body, before Power injection.
	 *
	 * @return string
	 * @since 6.2.0
	 */
	public function get(object $plugin, string $code): string
	{
		$selection = $plugin->method_selection ?? [];
		$selection = is_string($selection) ? json_decode($selection, true) : $selection;
		$selected = [];

		foreach ((array) $selection as $item)
		{
			$selected[] = ((array) $item)['method'] ?? '';
		}

		if (array_intersect($selected, array_column(self::METHODS, 0)) === [])
		{
			return $code;
		}

		foreach (array_reverse($this->methods($code)) as $method)
		{
			$contract = self::METHODS[$method['name']] ?? null;

			if ($contract === null || !in_array($contract[0], $selected, true))
			{
				continue;
			}

			$source = substr($code, $method['start'], $method['length']);

			if ($this->tokens($source) === $this->tokens($this->original($method['name'], $contract)))
			{
				$line = strrpos(substr($code, 0, $method['start']), "\n");
				$line = $line === false ? 0 : $line + 1;
				$indent = substr($code, $line, $method['start'] - $line);

				// Replace only the reviewed method's indentation with the configured value.
				if (trim($indent, " \t") === '')
				{
					$method['length'] += strlen($indent);
					$method['start'] = $line;
				}

				$code = substr_replace($code, $this->native($method['name'], $contract), $method['start'], $method['length']);
			}
		}

		return $code;
	}

	/**
	 * Locate complete class-level methods without matching text inside strings.
	 *
	 * @param string $code The class body.
	 *
	 * @return array
	 * @since 6.2.0
	 */
	private function methods(string $code): array
	{
		$methods = [];
		$offset = -6;
		$depth = 0;
		$start = null;
		$doc = null;
		$name = null;
		$function = false;

		foreach (token_get_all('<?php ' . $code) as $token)
		{
			$text = is_array($token) ? $token[1] : $token;
			$id = is_array($token) ? $token[0] : null;

			if ($id === T_OPEN_TAG)
			{
				$offset += strlen($text);
				continue;
			}

			if ($depth === 0)
			{
				if ($start === null && $id === T_DOC_COMMENT)
				{
					$doc = $offset;
				}
				elseif (!in_array($id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true))
				{
					$start = $start ?? $doc ?? $offset;
				}

				if ($function && $name === null && $id === T_STRING)
				{
					$name = $text;
				}

				$function = $function || $id === T_FUNCTION;
			}

			if ($token === '{' || in_array($id, [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))
			{
				$depth++;
			}
			elseif ($token === '}')
			{
				$depth--;
			}

			$offset += strlen($text);

			if ($depth === 0 && ($token === ';' || $token === '}'))
			{
				if ($name !== null)
				{
					$methods[] = ['name' => $name, 'start' => $start, 'length' => $offset - $start];
				}

				$start = $doc = $name = null;
				$function = false;
			}
		}

		return $methods;
	}

	/**
	 * Compare executable PHP tokens, tolerating only comments and formatting.
	 *
	 * @param string $code The method source.
	 *
	 * @return array
	 * @since 6.2.0
	 */
	private function tokens(string $code): array
	{
		$tokens = [];

		foreach (token_get_all('<?php ' . $code) as $token)
		{
			if (!is_array($token))
			{
				$tokens[] = $token;
			}
			elseif (!in_array($token[0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true))
			{
				$tokens[] = [$token[0], $token[1]];
			}
		}

		return $tokens;
	}

	/**
	 * Describe the exact shipped executable contract, including its Power IDs.
	 *
	 * @param string $name     The store method name.
	 * @param array  $contract The reviewed event/result contract.
	 *
	 * @return string
	 * @since 6.2.0
	 */
	private function original(string $name, array $contract): string
	{
		$result = $contract[2];
		$arguments = $result === '' ? '$this, $request, $user' : '$this, ' . $result . ', $request, $user';

		return 'public function ' . $name . '(Joomla__' . '_afbb897f_f5b8_465d_9213_dae5ccf3df3d___Power $request, Joomla__' . '_effdaf6d_2275_425d_9f52_d4952e564d34___Power $user = null) {'
			. ($result === '' ? '' : $result . ' = ' . $contract[3] . ';')
			. 'if (!$user) { return ' . $result . '; }'
			. "if (method_exists(Joomla__" . "_aebfeb9f_f8a3_42be_a21d_5db56ae30c1c___Power::class, '" . $name . "')) {"
			. 'Joomla__' . '_aebfeb9f_f8a3_42be_a21d_5db56ae30c1c___Power::' . $name . '(' . $arguments . '); }'
			. ($result === '' ? '' : 'return ' . $result . ';') . '}';
	}

	/**
	 * Render native event getters and results around the original helper calls.
	 *
	 * @param string $name     The store method name.
	 * @param array  $contract The reviewed event/result contract.
	 *
	 * @return string
	 * @since 6.2.0
	 */
	private function native(string $name, array $contract): string
	{
		$event = '\\Joomla\\CMS\\Event\\Privacy\\' . $contract[1];
		$result = $contract[2];
		$arguments = $result === '' ? '$this, $request, $user' : '$this, ' . $result . ', $request, $user';
		$script = Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . ' *' . Line::_(__LINE__, __CLASS__)
			. ' Process the privacy request through the component helper.';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1) . ' * @param ' . $event . ' $event The native privacy event.';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1) . ' * @return void';
		$script .= PHP_EOL . Indent::_(1) . ' * @since 6.2.0';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'public function ' . $name . '(' . $event . ' $event): void';
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= PHP_EOL . Indent::_(2) . '$request = $event->getRequest();';
		$script .= PHP_EOL . Indent::_(2) . '$user = $event->getUser();';

		if ($result !== '')
		{
			$script .= PHP_EOL . Indent::_(2) . $result . ' = ' . $contract[3] . ';';
		}

		$script .= PHP_EOL . PHP_EOL . Indent::_(2)
			. "if (\$user && method_exists(Joomla__" . "_aebfeb9f_f8a3_42be_a21d_5db56ae30c1c___Power::class, '" . $name . "'))";
		$script .= PHP_EOL . Indent::_(2) . '{';
		$script .= PHP_EOL . Indent::_(3) . 'Joomla__' . '_aebfeb9f_f8a3_42be_a21d_5db56ae30c1c___Power::' . $name . '(' . $arguments . ');';
		$script .= PHP_EOL . Indent::_(2) . '}';

		if ($result !== '')
		{
			$script .= PHP_EOL . PHP_EOL . Indent::_(2) . '$event->addResult(' . $result . ');';
		}

		$script .= PHP_EOL . Indent::_(1) . '}';

		return $script;
	}
}

