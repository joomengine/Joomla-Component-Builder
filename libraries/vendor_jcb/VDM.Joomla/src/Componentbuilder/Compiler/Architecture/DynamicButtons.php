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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Builder\DynamicButtons as Builder;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * Dynamic Buttons Class
 * 
 * @since 5.1.4
 */
class DynamicButtons
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 5.1.4
	 */
	protected Config $config;

	/**
	 * The DynamicButtons Class.
	 *
	 * @var   Builder
	 * @since 5.1.4
	 */
	protected Builder $builder;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 5.1.4
	 */
	protected Language $language;

	/**
	 * Constructor.
	 *
	 * @param Config     $config     The Config Class.
	 * @param Builder    $builder    The DynamicButtons Class.
	 * @param Language   $language   The Language Class.
	 *
	 * @since 5.1.4
	 */
	public function __construct(Config $config, Builder $builder, Language $language)
	{
		$this->config = $config;
		$this->builder = $builder;
		$this->language = $language;
	}

	/**
	 * Generate the PHP code for adding custom toolbar buttons based on a list code.
	 *
	 * This method iterates over custom button configurations retrieved from the builder,
	 * loads each button's language string, and composes the PHP source lines required
	 * to render the buttons conditionally based on access permissions.
	 *
	 * @param  string  $nameListCode  The list code key used to fetch custom buttons.
	 *
	 * @return string  The generated PHP code lines for toolbar buttons or an empty string.
	 * @since  5.1.4
	 */
	public function get(string $nameListCode): string
	{
		// Validate input and ensure we have button definitions.
		if (!$this->builder->isArray($nameListCode))
		{
			return '';
		}

		$buttons = [];

		foreach ($this->builder->get($nameListCode) as $customButton)
		{
			// Ensure the button configuration has the required keys.
			if (empty($customButton['NAME']) || empty($customButton['name']) || empty($customButton['link']) || empty($customButton['icon']))
			{
				continue;
			}

			// Prepare the language key and register the translation.
			$keyLang = $this->config->lang_prefix . '_' . $customButton['NAME'];
			$this->language->set(
				$this->config->lang_target,
				$keyLang,
				StringHelper::safe($customButton['name'], 'Ww')
			);

			// Build the code lines for the button.
			$linkSafe = StringHelper::safe($customButton['link'], 'F');
			$nameEscaped = StringHelper::safe($customButton['name'], 'w');

			$buttons[] = Indent::_(2) . "if (\$this->canDo->get('{$customButton['link']}.access'))";
			$buttons[] = Indent::_(2) . '{';
			$buttons[] = Indent::_(3) . "// " . Line::_(__LINE__, __CLASS__) . " add {$nameEscaped} button.";
			$buttons[] = Indent::_(3)
				. $this->button(
					$nameListCode . '.redirectTo' . $linkSafe, $customButton['icon'], $keyLang
				);
			$buttons[] = Indent::_(2) . '}';
		}

		// Return the generated button code block.
		return ArrayHelper::check($buttons) ? implode(PHP_EOL, $buttons) : '';
	}
	/**
	 * Render the selection-dependent redirect button.
	 *
	 * @param   string  $task   Controller task.
	 * @param   string  $icon   Button icon.
	 * @param   string  $label  Translation key.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function button(string $task, string $icon, string $label): string
	{
		return "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::custom('"
			. $task . "', '" . $icon . "', '', '" . $label . "', true);";
	}
}

