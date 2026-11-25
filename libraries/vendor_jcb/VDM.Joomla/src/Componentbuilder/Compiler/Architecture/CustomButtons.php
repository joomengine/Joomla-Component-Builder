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
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Builder\CustomForm;
use VDM\Joomla\Componentbuilder\Compiler\Builder\OnlyFunctionButtons;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Structure;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Registry;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * Custom Buttons Class
 * 
 * @since 5.1.4
 */
class CustomButtons
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 5.1.4
	 */
	protected Config $config;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 5.1.4
	 */
	protected ContentOne $contentone;

	/**
	 * The ContentMulti Class.
	 *
	 * @var   ContentMulti
	 * @since 5.1.4
	 */
	protected ContentMulti $contentmulti;

	/**
	 * The CustomForm Class.
	 *
	 * @var   CustomForm
	 * @since 5.1.4
	 */
	protected CustomForm $customform;

	/**
	 * The OnlyFunctionButtons Class.
	 *
	 * @var   OnlyFunctionButtons
	 * @since 5.1.4
	 */
	protected OnlyFunctionButtons $onlyfunctionbuttons;

	/**
	 * The Structure Class.
	 *
	 * @var   Structure
	 * @since 5.1.4
	 */
	protected Structure $structure;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 5.1.4
	 */
	protected Language $language;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 5.1.4
	 */
	protected Placeholder $placeholder;

	/**
	 * The Registry Class.
	 *
	 * @var   Registry
	 * @since 5.1.4
	 */
	protected Registry $registry;

	/**
	 * The the current view settings.
	 *
	 * @var   object
	 * @since 5.1.4
	 */
	protected object $settings;

	/**
	 * Constructor.
	 *
	 * @param Config                $config                The Config Class.
	 * @param ContentOne            $contentone            The ContentOne Class.
	 * @param ContentMulti          $contentmulti          The ContentMulti Class.
	 * @param CustomForm            $customform            The CustomForm Class.
	 * @param OnlyFunctionButtons   $onlyfunctionbuttons   The OnlyFunctionButtons Class.
	 * @param Structure             $structure             The Structure Class.
	 * @param Language              $language              The Language Class.
	 * @param Placeholder           $placeholder           The Placeholder Class.
	 * @param Registry              $registry              The Registry Class.
	 *
	 * @since 5.1.4
	 */
	public function __construct(Config $config, ContentOne $contentone,
		ContentMulti $contentmulti, CustomForm $customform,
		OnlyFunctionButtons $onlyfunctionbuttons,
		Structure $structure, Language $language,
		Placeholder $placeholder, Registry $registry)
	{
		$this->config = $config;
		$this->contentone = $contentone;
		$this->contentmulti = $contentmulti;
		$this->customform = $customform;
		$this->onlyfunctionbuttons = $onlyfunctionbuttons;
		$this->structure = $structure;
		$this->language = $language;
		$this->placeholder = $placeholder;
		$this->registry = $registry;
	}

	/**
	 * Build and inject custom toolbar buttons for a view.
	 *
	 * @param  array   $view  The view context array. Must contain a 'settings' object with properties used here.
	 * @param  integer $type  View type: 1 (admin item), 2 (admin single), 3 (admin list). Defaults to 1.
	 * @param  string  $tab   Indentation prefix for generated code strings.
	 *
	 * @return string  Buttons code (with line breaks) or empty string if no buttons were produced.
	 * @since  5.1.2
	 */
	public function get(array $view, int $type = 1, string $tab = ''): string
	{
		$validateSelection = 'false';
		$TARGET = StringHelper::safe($this->config->build_target, 'U');

		$this->settings = (object) ($view['settings'] ?? []);

		[$viewCodeName, $viewsCodeName] = $this->determineViewNames((int) $type);

		if (empty($viewCodeName))
		{
			return '';
		}

		if (3 === $type)
		{
			// Initialize list placeholders if needed and set validate selection
			$this->ensureListButtonStores($viewsCodeName, $TARGET);
			$validateSelection = 'true';
		}

		// Ensure single view placeholders exist
		$this->ensureSingleButtonStores($viewCodeName, $TARGET);

		$buttons = [];

		// Site-only toolbar placement handling
		if ($this->config->build_target === 'site')
		{
			$this->applySiteToolbarPlacement($viewCodeName);
		}
		// Admin item view special dashboard button
		elseif (1 === $type)
		{
			$this->maybeAddDashboardButton($viewCodeName, $buttons, $tab);
		}

		// Custom buttons handling (admin+site)
		if ($this->shouldAddCustomButtons())
		{
			$this->addCustomButtons($type, $viewCodeName, $viewsCodeName, $validateSelection, $tab, $buttons);
			$this->addControllerAndModelStrings($type, $viewCodeName, $viewsCodeName, $TARGET);
		}

		// Finalization: add submit JS if needed, ensure form exists, return buttons
		if (ArrayHelper::check($buttons))
		{
			$this->ensureSubmitButtonJs($viewCodeName, $TARGET);
			$this->ensureFormExists($viewCodeName);

			return PHP_EOL . implode(PHP_EOL, $buttons);
		}

		return '';
	}

	/**
	 * Add the required JavaScript for custom toolbar buttons.
	 *
	 * @return string  The script block lines joined by newlines.
	 * @since  5.1.2
	 */
	public function javascript(): string
	{
		$javascript = 'Joomla.submitbutton = function(task) {';
		$javascript .= PHP_EOL . Indent::_(1) . "if (task == '')";
		$javascript .= PHP_EOL . Indent::_(1) . '{';
		$javascript .= PHP_EOL . Indent::_(2) . 'return false;';
		$javascript .= PHP_EOL . Indent::_(1) . '}';
		$javascript .= PHP_EOL . Indent::_(1) . 'else';
		$javascript .= PHP_EOL . Indent::_(1) . '{';
		$javascript .= PHP_EOL . Indent::_(2) . 'Joomla.submitform(task);';
		$javascript .= PHP_EOL . Indent::_(2) . 'return true;';
		$javascript .= PHP_EOL . Indent::_(1) . '}';
		$javascript .= PHP_EOL . Indent::_(0) . '};';
		$lines = explode(PHP_EOL, $javascript);
		$script = PHP_EOL . Indent::_(2) . '//' . Line::_(__LINE__, __CLASS__) . ' Add the needed Javascript to insure that the buttons work.';
		$script .= PHP_EOL . Indent::_(2) . "Html::_('behavior.framework', true);";
		$script .= PHP_EOL . Indent::_(2) . '$jcbToolbarScript = ' . var_export(array_shift($lines), true);

		foreach ($lines as $line)
		{
			$script .= PHP_EOL . Indent::_(3) . '. PHP_EOL . ' . var_export($line, true);
		}

		$script .= ';';

		if ($this->config->get('joomla_version', 3) == 3)
		{
			$script .= PHP_EOL . Indent::_(2) . '$this->getDocument()->addScriptDeclaration($jcbToolbarScript);';
		}
		else
		{
			$script .= PHP_EOL . Indent::_(2) . '$this->getDocument()->getWebAssetManager()->addInlineScript($jcbToolbarScript);';
		}

		return $script;
	}

	/**
	 * Determine the single and list code names for the view.
	 *
	 * @param  integer $type  View type (1,2,3).
	 *
	 * @return array{0:string,1:?string}
	 * @since  5.1.2
	 */
	private function determineViewNames(int $type): array
	{
		$viewCodeName  = '';
		$viewsCodeName = '';

		if ($type === 1)
		{
			$viewCodeName = $this->settings->code ?? '';
			$viewsCodeName = $viewCodeName;
		}
		elseif ($type === 2)
		{
			$viewCodeName = $this->settings->name_single_code ?? '';
			$viewsCodeName = $this->settings->name_list_code ?? $viewCodeName;
		}
		elseif ($type === 3)
		{
			$viewCodeName  = $this->settings->name_single_code ?? '';
			$viewsCodeName = $this->settings->name_list_code ?? $viewCodeName;
		}

		return [$viewCodeName, $viewsCodeName];
	}

	/**
	 * Ensure list custom button controller/method stores exist.
	 *
	 * @param  string $viewsCodeName  The list view code name.
	 * @param  string $TARGET         Upper-cased build target.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function ensureListButtonStores(string $viewsCodeName, string $TARGET): void
	{
		if (!$this->contentmulti->exists($viewsCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_METHOD_LIST'))
		{
			$this->contentmulti->set($viewsCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_CONTROLLER_LIST', '');
			$this->contentmulti->set($viewsCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_METHOD_LIST', '');
		}
	}

	/**
	 * Ensure single view custom button controller/method stores exist.
	 *
	 * @param  string $viewCodeName  The single view code name.
	 * @param  string $TARGET        Upper-cased build target.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function ensureSingleButtonStores(string $viewCodeName, string $TARGET): void
	{
		if (!$this->contentmulti->exists($viewCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_METHOD'))
		{
			$this->contentmulti->set($viewCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_CONTROLLER', '');
			$this->contentmulti->set($viewCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_METHOD', '');
		}
	}

	/**
	 * Apply site toolbar placement for site target builds.
	 *
	 * @param  string $viewCodeName  The single view code name.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function applySiteToolbarPlacement(string $viewCodeName): void
	{
		$this->contentmulti->set($viewCodeName . '|SITE_TOP_BUTTON', '');
		$this->contentmulti->set($viewCodeName . '|SITE_BOTTOM_BUTTON', '');

		$position = (int) ($this->settings->button_position ?? 5);

		switch ($position)
		{
			case 1:
				$this->contentmulti->set(
					$viewCodeName . '|SITE_TOP_BUTTON',
					'<div class="uk-clearfix"><div class="uk-float-right"><?php echo $this->toolbar->render(); ?></div></div>'
				);
				break;

			case 2:
				$this->contentmulti->set(
					$viewCodeName . '|SITE_TOP_BUTTON',
					'<?php echo $this->toolbar->render(); ?>'
				);
				break;

			case 3:
				$this->contentmulti->set(
					$viewCodeName . '|SITE_BOTTOM_BUTTON',
					'<div class="uk-clearfix"><div class="uk-float-right"><?php echo $this->toolbar->render(); ?></div></div>'
				);
				break;

			case 4:
				$this->contentmulti->set(
					$viewCodeName . '|SITE_BOTTOM_BUTTON',
					'<?php echo $this->toolbar->render(); ?>'
				);
				break;

			case 5:
				$this->placeholder->set_('SITE_TOOLBAR', '<?php echo $this->toolbar->render(); ?>');
				break;
		}
	}

	/**
	 * Add the "dashboard" button to admin item views when applicable.
	 *
	 * @param  string $viewCodeName  The single view code name.
	 * @param  array  $buttons       Reference to buttons array being built.
	 * @param  string $tab           Indentation prefix.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function maybeAddDashboardButton(string $viewCodeName, array &$buttons, string $tab): void
	{
		$dynamic_dashboard      = $this->registry->get('build.dashboard', '');
		$dynamic_dashboard_type = $this->registry->get('build.dashboard.type', '');

		$notSameCustomAdmin = ($dynamic_dashboard_type !== 'custom_admin_views')
			|| ($dynamic_dashboard_type === 'custom_admin_views' && $dynamic_dashboard !== $viewCodeName);

		if ($notSameCustomAdmin)
		{
			$buttons[] = $tab . Indent::_(2) . "//" . Line::_(__Line__, __Class__) . " add cpanel button";
			$buttons[] = $tab . Indent::_(2)
				. $this->button(
					$viewCodeName . '.dashboard', 'grid-2',
					'COM_' . $this->contentone->get('COMPONENT') . '_DASH', false
				);
		}
	}

	/**
	 * Determine if custom buttons should be added for the view.
	 *
	 * @return boolean
	 * @since  5.1.2
	 */
	private function shouldAddCustomButtons(): bool
	{
		return (isset($this->settings->add_custom_button) && (int) $this->settings->add_custom_button === 1);
	}

	/**
	 * Add custom buttons for item/single or list contexts.
	 *
	 * @param  integer     $type               View type (1/2/3).
	 * @param  string      $viewCodeName       Single view code name.
	 * @param  string|null $viewsCodeName      List view code name (only for type 3).
	 * @param  string      $validateSelection  "true"/"false" string used for list button submit.
	 * @param  string      $tab                Indentation prefix.
	 * @param  array       $buttons            Reference to buttons array being built.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function addCustomButtons(int $type, string $viewCodeName,
		?string $viewsCodeName, string $validateSelection, string $tab, array &$buttons): void
	{
		$custom_buttons = (array) ($this->settings->custom_buttons ?? []);
		if (!ArrayHelper::check($custom_buttons))
		{
			return;
		}

		// always reset these
		$this->onlyfunctionbuttons->remove($viewsCodeName);

		foreach ($custom_buttons as $custom_button)
		{
			$keyLang = $this->registerCustomButtonLanguage((string) $custom_button['name']);
			$keyCode = StringHelper::safe($custom_button['name']);

			// ITEM / SINGLE (type != 3) or SITE (target 2) handling
			if ($type !== 3 && ($custom_button['target'] != 2 || $this->config->build_target === 'site'))
			{
				$this->appendItemButton($viewCodeName, $keyCode, $custom_button, $keyLang, $tab, $buttons);
				continue;
			}

			// LIST (type == 3) handling
			if ($type === 3 && $custom_button['target'] != 1 && $viewsCodeName !== null)
			{
				if (isset($custom_button['type']) && (int) $custom_button['type'] === 2)
				{
					$this->appendListOnlyFunctionButton($viewsCodeName, $viewCodeName, $keyCode, $custom_button, $keyLang, $tab);
				}
				else
				{
					$this->appendListButton($viewsCodeName, $viewCodeName, $keyCode, $custom_button, $keyLang, $validateSelection, $tab, $buttons);
				}
			}
		}
	}

	/**
	 * Register a language key for a custom button and return the lang key.
	 *
	 * @param  string $name  Display label of the custom button.
	 *
	 * @return string  The language key used.
	 * @since  5.1.2
	 */
	private function registerCustomButtonLanguage(string $name): string
	{
		$keyLang = $this->config->lang_prefix . '_' . StringHelper::safe($name, 'U');
		$this->language->set($this->config->lang_target, $keyLang, $name);

		return $keyLang;
	}

	/**
	 * Append a custom button for item/single contexts.
	 *
	 * Preserves permission checks exactly as the original:
	 * - Site or target=2 uses $this->user->authorise(...)
	 * - Otherwise uses $this->canDo->get(...)
	 *
	 * @param  string $viewCodeName  Single view code name.
	 * @param  string $keyCode       Safe name code for permission key.
	 * @param  array  $customButton  The custom button definition.
	 * @param  string $keyLang       Language key of the label.
	 * @param  string $tab           Indentation prefix.
	 * @param  array  $buttons       Reference to buttons array.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function appendItemButton(string $viewCodeName, string $keyCode,
		array $customButton, string $keyLang, string $tab, array &$buttons): void
	{
		$indent1 = Indent::_(1) . $tab . Indent::_(1);

		if ($customButton['target'] == 2 || $this->config->build_target === 'site')
		{
			$buttons[] = $indent1 . "if (\$this->user->authorise('{$viewCodeName}.{$keyCode}', 'com_{$this->config->component_code_name}'))";
		}
		else
		{
			$buttons[] = $indent1 . "if (\$this->canDo->get('{$viewCodeName}.{$keyCode}'))";
		}

		$buttons[] = $indent1 . "{";
		$buttons[] = $indent1 . Indent::_(1) . "//" . Line::_(__Line__, __Class__) . " add {$customButton['name']} button.";
		$buttons[] = $indent1 . Indent::_(1)
			. $this->button(
				$viewCodeName . '.' . $customButton['method'],
				$customButton['icomoon'] . ' custom-button-' . strtolower((string) $customButton['method']),
				$keyLang, false
			);
		$buttons[] = $indent1 . "}";
	}

	/**
	 * Append a "function only" custom button to the list context via $this->onlyfunctionbuttons.
	 *
	 * @param  string $viewsCodeName  List view code name.
	 * @param  string $viewCodeName   Single view code name (for permission).
	 * @param  string $keyCode        Safe name code for permission key.
	 * @param  array  $customButton   The custom button definition.
	 * @param  string $keyLang        Language key of the label.
	 * @param  string $tab            Indentation prefix.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function appendListOnlyFunctionButton(string $viewsCodeName, string $viewCodeName,
		string $keyCode, array $customButton, string $keyLang, string $tab): void
	{
		$this->onlyfunctionbuttons->add(
			$viewsCodeName,
			PHP_EOL . Indent::_(1) . $tab
			. "if (\$this->user->authorise('{$viewCodeName}.{$keyCode}', 'com_{$this->config->component_code_name}'))"
		);
		$this->onlyfunctionbuttons->add($viewsCodeName, PHP_EOL . Indent::_(1) . $tab . "{");
		$this->onlyfunctionbuttons->add(
			$viewsCodeName,
			PHP_EOL . Indent::_(1) . $tab . Indent::_(1) . "//" . Line::_(__LINE__, __CLASS__) . " add {$customButton['name']} button."
		);
		$this->onlyfunctionbuttons->add(
			$viewsCodeName,
			PHP_EOL . Indent::_(1) . $tab . Indent::_(1)
			. $this->button(
				$viewsCodeName . '.' . $customButton['method'],
				$customButton['icomoon'] . ' custom-button-' . strtolower((string) $customButton['method']),
				$keyLang, false
			)
		);
		$this->onlyfunctionbuttons->add($viewsCodeName, PHP_EOL . Indent::_(1) . $tab . "}");
	}

	/**
	 * Append a standard custom button for list context to the $buttons array.
	 *
	 * @param  string $viewsCodeName      List view code name.
	 * @param  string $viewCodeName       Single view code name (for permission).
	 * @param  string $keyCode            Safe name code for permission key.
	 * @param  array  $customButton       The custom button definition.
	 * @param  string $keyLang            Language key of the label.
	 * @param  string $validateSelection  "true"/"false" selection string.
	 * @param  string $tab                Indentation prefix.
	 * @param  array  $buttons            Reference to buttons array.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function appendListButton(string $viewsCodeName, string $viewCodeName, string $keyCode,
		array $customButton, string $keyLang, string $validateSelection, string $tab, array &$buttons): void
	{
		$indent1 = Indent::_(1) . $tab . Indent::_(1);

		$buttons[] = $indent1 . "if (\$this->user->authorise('{$viewCodeName}.{$keyCode}', 'com_{$this->config->component_code_name}'))";
		$buttons[] = $indent1 . "{";
		$buttons[] = $indent1 . Indent::_(1) . "//" . Line::_(__Line__, __Class__) . " add {$customButton['name']} button.";
		$buttons[] = $indent1 . Indent::_(1)
			. $this->button(
				$viewsCodeName . '.' . $customButton['method'],
				$customButton['icomoon'] . ' custom-button-' . strtolower((string) $customButton['method']),
				$keyLang, $validateSelection === 'true', true
			);
		$buttons[] = $indent1 . "}";
	}

	/**
	 * Add controller and model code strings into content stores, based on type and target.
	 *
	 * @param  integer     $type           View type (1/2/3).
	 * @param  string      $viewCodeName   Single view code name.
	 * @param  string|null $viewsCodeName  List view code name.
	 * @param  string      $TARGET         Upper-cased build target.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function addControllerAndModelStrings(int $type, string $viewCodeName, ?string $viewsCodeName, string $TARGET): void
	{
		if ($type === 3)
		{
			if (isset($this->settings->php_controller_list) && StringHelper::check($this->settings->php_controller_list) && $this->settings->php_controller_list != '//')
			{
				$this->contentmulti->set(
					$viewsCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_CONTROLLER_LIST',
					PHP_EOL . PHP_EOL . $this->placeholder->update_($this->settings->php_controller_list)
				);
			}

			if (isset($this->settings->php_model_list) && StringHelper::check($this->settings->php_model_list) && $this->settings->php_model_list != '//')
			{
				$this->contentmulti->set(
					$viewsCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_METHOD_LIST',
					PHP_EOL . PHP_EOL . $this->placeholder->update_($this->settings->php_model_list)
				);
			}
		}
		else
		{
			if (StringHelper::check($this->settings->php_controller) && $this->settings->php_controller != '//')
			{
				$this->contentmulti->set(
					$viewCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_CONTROLLER',
					PHP_EOL . PHP_EOL . $this->placeholder->update_($this->settings->php_controller)
				);

				if ('site' === $this->config->build_target)
				{
					$target = [$this->config->build_target => $viewCodeName];
					$this->structure->build($target, 'custom_form'); // GET_FORM_CUSTOM
				}
			}

			if (StringHelper::check($this->settings->php_model) && $this->settings->php_model != '//')
			{
				$this->contentmulti->set(
					$viewCodeName . '|' . $TARGET . '_CUSTOM_BUTTONS_METHOD',
					PHP_EOL . PHP_EOL . $this->placeholder->update_($this->settings->php_model)
				);
			}
		}
	}

	/**
	 * Ensure the submit button JavaScript is injected only when the submit script is absent.
	 *
	 * @param  string $viewCodeName  Single view code name.
	 * @param  string $TARGET        Upper-cased build target.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function ensureSubmitButtonJs(string $viewCodeName, string $TARGET): void
	{
		$missingSubmitScript =
			(!isset($this->settings->php_document))
			|| (ArrayHelper::check($this->settings->php_document) && strpos(implode(' ', $this->settings->php_document), '/submitbutton.js') === false)
			|| (StringHelper::check($this->settings->php_document) && strpos((string) $this->settings->php_document, '/submitbutton.js') === false);

		if ($missingSubmitScript)
		{
			$this->contentmulti->set(
				$viewCodeName . '|' . $TARGET . '_JAVASCRIPT_FOR_BUTTONS', $this->javascript()
			);
		}
	}

	/**
	 * Ensure a form exists in the default markup; if not, add a custom form scaffold.
	 *
	 * @param  string $viewCodeName  Single view code name.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	private function ensureFormExists(string $viewCodeName): void
	{
		if (isset($this->settings->default) && strpos((string) $this->settings->default, '<form') === false)
		{
			$this->customform->set("{$this->config->build_target}.{$viewCodeName}", true);
		}
	}
	/**
	 * Render one toolbar button for the selected target.
	 *
	 * @param   string  $task            Controller task.
	 * @param   string  $icon            Button icon and additional classes.
	 * @param   string  $label           Translation key.
	 * @param   bool    $selection       Whether a list selection is required.
	 * @param   bool    $quoteSelection  Preserve the earlier-target source shape.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function button(
		string $task, string $icon, string $label, bool $selection, bool $quoteSelection = false
	): string
	{
		$selected = $selection ? 'true' : 'false';

		if ($quoteSelection)
		{
			$selected = "'" . $selected . "'";
		}

		return "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::custom('"
			. $task . "', '" . $icon . "', '', '" . $label . "', " . $selected . ");";
	}
}

