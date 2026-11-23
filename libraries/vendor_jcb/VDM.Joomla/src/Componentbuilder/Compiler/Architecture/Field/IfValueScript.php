<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    18th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Field;


use VDM\Joomla\Componentbuilder\Compiler\Field\Groups as FieldGroups;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ScriptUserSwitch;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;


/**
 * Field If Value Script Class.
 * 
 * Builds the javascript test a form condition runs against the field it
 * watches: what counts as a match for the behaviour the condition declares,
 * over the options the field offers.
 * 
 * @since  6.1.7
 */
final class IfValueScript
{
	/**
	 * The Field Groups Class.
	 *
	 * @var   FieldGroups
	 * @since 6.1.7
	 */
	protected FieldGroups $fieldgroups;

	/**
	 * The Script User Switch Class.
	 *
	 * @var   ScriptUserSwitch
	 * @since 6.1.7
	 */
	protected ScriptUserSwitch $scriptuserswitch;

	/**
	 * Constructor.
	 *
	 * @param FieldGroups       $fieldgroups       The Field Groups Class.
	 * @param ScriptUserSwitch  $scriptuserswitch  The Script User Switch Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(FieldGroups $fieldgroups,
		ScriptUserSwitch $scriptuserswitch)
	{
		$this->fieldgroups = $fieldgroups;
		$this->scriptuserswitch = $scriptuserswitch;
	}

	/**
	 * Build the javascript test one form condition runs.
	 *
	 * @param   string  $value     The javascript variable holding the watched value.
	 * @param   mixed   $behavior  The match behaviour the condition declares.
	 * @param   mixed   $type      The type of the field being watched.
	 * @param   mixed   $options   The options the field offers, which the caller does not guarantee to be an array.
	 *
	 * @return  string|int  The test, or the integer 0 when the condition tests nothing.
	 *
	 * @since   6.1.7
	 * @since   6.2.1  Escape text and parse numeric options without legacy octal syntax.
	 */
	public function get(string $value, $behavior, $type, $options): string|int
	{
		// reset string
		$string = '';
		switch ($behavior)
		{
			case 1: // Is
				// only 4 list/radio/checkboxes
				if ($this->fieldgroups->check($type, 'list')
					|| $this->fieldgroups->check($type, 'dynamic')
					|| !$this->fieldgroups->check($type))
				{
					if (ArrayHelper::check($options))
					{
						foreach ($options as $option)
						{
							if (!is_numeric($option))
							{
								if ($option != 'true' && $option != 'false')
								{
									$option = "'" . $this->escapeString($option, "'") . "'";
								}
							}
							else
							{
								// Avoid legacy octal parsing and invalid leading-zero decimals.
								$option = "Number('" . $this->escapeString((string) $option, "'") . "')";
							}
							if (StringHelper::check($string))
							{
								$string .= ' || ' . $value . ' == ' . $option;
							}
							else
							{
								$string .= $value . ' == ' . $option;
							}
						}
					}
					else
					{
						$string .= 'isSet(' . $value . ')';
					}
				}
				break;
			case 2: // Is Not
				// only 4 list/radio/checkboxes
				if ($this->fieldgroups->check($type, 'list')
					|| $this->fieldgroups->check($type, 'dynamic')
					|| !$this->fieldgroups->check($type))
				{
					if (ArrayHelper::check($options))
					{
						foreach ($options as $option)
						{
							if (!is_numeric($option))
							{
								if ($option != 'true' && $option != 'false')
								{
									$option = "'" . $this->escapeString($option, "'") . "'";
								}
							}
							else
							{
								// Avoid legacy octal parsing and invalid leading-zero decimals.
								$option = "Number('" . $this->escapeString((string) $option, "'") . "')";
							}
							if (StringHelper::check($string))
							{
								$string .= ' || ' . $value . ' != ' . $option;
							}
							else
							{
								$string .= $value . ' != ' . $option;
							}
						}
					}
					else
					{
						$string .= '!isSet(' . $value . ')';
					}
				}
				break;
			case 3: // Any Selection
				// only 4 list/radio/checkboxes/dynamic_list
				if ($this->fieldgroups->check($type, 'list')
					|| $this->fieldgroups->check($type, 'dynamic')
					|| !$this->fieldgroups->check($type))
				{
					if (ArrayHelper::check($options))
					{
						foreach ($options as $option)
						{
							if (!is_numeric($option))
							{
								if ($option != 'true' && $option != 'false')
								{
									$option = "'" . $this->escapeString($option, "'") . "'";
								}
							}
							else
							{
								// Avoid legacy octal parsing and invalid leading-zero decimals.
								$option = "Number('" . $this->escapeString((string) $option, "'") . "')";
							}
							if (StringHelper::check($string))
							{
								$string .= ' || ' . $value . ' == ' . $option;
							}
							else
							{
								$string .= $value . ' == ' . $option;
							}
						}
					}
					else
					{
						$userFix = '';
						if ($this->scriptuserswitch->inArray($type))
						{
							// TODO this needs a closer look, a bit buggy
							$userFix = " && " . $value . " != 0";
						}
						$string .= 'isSet(' . $value . ')' . $userFix;
					}
				}
				break;
			case 4: // Active (not empty)
				// only 4 text_field
				if ($this->fieldgroups->check($type, 'text'))
				{
					$string .= 'isSet(' . $value . ')';
				}
				break;
			case 5: // Unactive (empty)
				// only 4 text_field
				if ($this->fieldgroups->check($type, 'text'))
				{
					$string .= '!isSet(' . $value . ')';
				}
				break;
			case 6: // Key Word All (case-sensitive)
				// only 4 text_field
				if ($this->fieldgroups->check($type, 'text'))
				{
					if (ArrayHelper::check(
						$options['keywords'] ?? []
					))
					{
						foreach ($options['keywords'] as $keyword)
						{
							$keyword = $this->escapeString($keyword, '"');
							if (StringHelper::check($string))
							{
								$string .= ' && ' . $value . '.indexOf("'
									. $keyword . '") >= 0';
							}
							else
							{
								$string .= $value . '.indexOf("' . $keyword
									. '") >= 0';
							}
						}
					}
					if (!StringHelper::check($string))
					{
						$string .= $value . ' == "error"';
					}
				}
				break;
			case 7: // Key Word Any (case-sensitive)
				// only 4 text_field
				if ($this->fieldgroups->check($type, 'text'))
				{
					if (ArrayHelper::check(
						$options['keywords'] ?? []
					))
					{
						foreach ($options['keywords'] as $keyword)
						{
							$keyword = $this->escapeString($keyword, '"');
							if (StringHelper::check($string))
							{
								$string .= ' || ' . $value . '.indexOf("'
									. $keyword . '") >= 0';
							}
							else
							{
								$string .= $value . '.indexOf("' . $keyword
									. '") >= 0';
							}
						}
					}
					if (!StringHelper::check($string))
					{
						$string .= $value . ' == "error"';
					}
				}
				break;
			case 8: // Key Word All (case-insensitive)
				// only 4 text_field
				if ($this->fieldgroups->check($type, 'text'))
				{
					if (ArrayHelper::check(
						$options['keywords'] ?? []
					))
					{
						foreach ($options['keywords'] as $keyword)
						{
							$keyword = StringHelper::safe(
								$keyword, 'w'
							);
							$keyword = $this->escapeString($keyword, '"');
							if (StringHelper::check($string))
							{
								$string .= ' && ' . $value
									. '.toLowerCase().indexOf("' . $keyword
									. '") >= 0';
							}
							else
							{
								$string .= $value . '.toLowerCase().indexOf("'
									. $keyword . '") >= 0';
							}
						}
					}
					if (!StringHelper::check($string))
					{
						$string .= $value . ' == "error"';
					}
				}
				break;
			case 9: // Key Word Any (case-insensitive)
				// only 4 text_field
				if ($this->fieldgroups->check($type, 'text'))
				{
					if (ArrayHelper::check(
						$options['keywords'] ?? []
					))
					{
						foreach ($options['keywords'] as $keyword)
						{
							$keyword = StringHelper::safe(
								$keyword, 'w'
							);
							$keyword = $this->escapeString($keyword, '"');
							if (StringHelper::check($string))
							{
								$string .= ' || ' . $value
									. '.toLowerCase().indexOf("' . $keyword
									. '") >= 0';
							}
							else
							{
								$string .= $value . '.toLowerCase().indexOf("'
									. $keyword . '") >= 0';
							}
						}
					}
					if (!StringHelper::check($string))
					{
						$string .= $value . ' == "error"';
					}
				}
				break;
			case 10: // Min Length
				// only 4 text_field
				if ($this->fieldgroups->check($type, 'text'))
				{
					if (ArrayHelper::check($options))
					{
						if ($options['length'] ?? false)
						{
							$string .= $value . '.length >= '
								. (int) $options['length'];
						}
					}
					if (!StringHelper::check($string))
					{
						$string .= $value . '.length >= 5';
					}
				}
				break;
			case 11: // Max Length
				// only 4 text_field
				if ($this->fieldgroups->check($type, 'text'))
				{
					if (ArrayHelper::check($options))
					{
						if ($options['length'] ?? false)
						{
							$string .= $value . '.length <= '
								. (int) $options['length'];
						}
					}
					if (!StringHelper::check($string))
					{
						$string .= $value . '.length <= 5';
					}
				}
				break;
			case 12: // Exact Length
				// only 4 text_field
				if ($this->fieldgroups->check($type, 'text'))
				{
					if (ArrayHelper::check($options))
					{
						if ($options['length'] ?? false)
						{
							$string .= $value . '.length == '
								. (int) $options['length'];
						}
					}
					if (!StringHelper::check($string))
					{
						$string .= $value . '.length == 5';
					}
				}
				break;
		}
		if (!StringHelper::check($string))
		{
			$string = 0;
		}

		return $string;
	}

	/**
	 * Escape literal text without changing numeric or boolean option coercion.
	 *
	 * @param   string  $value  The authored option or keyword.
	 * @param   string  $quote  The delimiter used by the generated string.
	 *
	 * @return  string  Text safe inside the chosen JavaScript string delimiter.
	 *
	 * @since   6.2.1
	 */
	private function escapeString(string $value, string $quote): string
	{
		return strtr($value, [
			"\\" => "\\\\",
			$quote => "\\" . $quote,
			"\r" => '\\r',
			"\n" => '\\n',
			"\u{2028}" => '\\u2028',
			"\u{2029}" => '\\u2029',
		]);
	}
}

