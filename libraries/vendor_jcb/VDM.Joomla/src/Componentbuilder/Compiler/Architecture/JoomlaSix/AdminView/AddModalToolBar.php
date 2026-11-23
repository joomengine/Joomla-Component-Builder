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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\AdminView;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Permission;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomButtons;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminView\AddModalToolBarInterface;


/**
 * Admin View Add Modal ToolBar Class for Joomla 6
 * 
 * @since 5.1.4
 */
final class AddModalToolBar implements AddModalToolBarInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 5.1.4
	 */
	protected Config $config;

	/**
	 * The Permission Class.
	 *
	 * @var   Permission
	 * @since 5.1.4
	 */
	protected Permission $permission;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 5.1.4
	 */
	protected Language $language;

	/**
	 * The CustomButtons Class.
	 *
	 * @var   CustomButtons
	 * @since 5.1.4
	 */
	protected CustomButtons $custombuttons;

	/**
	 * Constructor.
	 *
	 * @param Config          $config          The Config Class.
	 * @param Permission      $permission      The Permission Class.
	 * @param Language        $language        The Language Class.
	 * @param CustomButtons   $custombuttons   The CustomButtons Class.
	 *
	 * @since 5.1.4
	 */
	public function __construct(Config $config, Permission $permission, Language $language,
		CustomButtons $custombuttons)
	{
		$this->config = $config;
		$this->permission = $permission;
		$this->language = $language;
		$this->custombuttons = $custombuttons;
	}

	/**
	 * Build the modal toolbar configuration for the given view.
	 *
	 * @param  array  $view  The view configuration array, including settings and description.
	 *
	 * @return string  The generated PHP toolbar code.
	 * @since  5.1.4
	 */
	public function get(array $view): string
	{
		/** @var object $settings */
		$settings = (object) ($view['settings'] ?? []);

		$nameSingleCode = $settings->name_single_code ?? null;
		$type = (int) ($settings->type ?? 0);

		if (empty($nameSingleCode))
		{
			return '';
		}

		$this->addEmptyStateLanguageStrings($view);

		// Readonly or editable modal type
		if ($type === 2)
		{
			return $this->buildModalReadonlyToolbar($view, $nameSingleCode);
		}

		return $this->buildModalEditableToolbar($view, $nameSingleCode);
	}

	/**
	 * Set the empty state language strings
	 *
	 * @param  array  $view  The view settings array.
	 *
	 * @return void
	 * @since  5.1.4
	 */
	protected function addEmptyStateLanguageStrings(array $view): void
	{
		$langViews = $this->config->lang_prefix . '_'
			. StringHelper::safe($view['settings']->name_list_code, 'U');

		$name_list   = strtolower($view['settings']->name_list);
		$name_single = strtolower($view['settings']->name_single);

		// add empty title
		$this->language->set(
			$this->config->lang_target,
			$langViews . '_EMPTYSTATE_TITLE',
			'No ' . $name_list . ' have been created yet.'
		);
		// add empty content
		$this->language->set(
			$this->config->lang_target,
			$langViews . '_EMPTYSTATE_CONTENT',
			$view['settings']->description ?? ''
		);
		// add empty button add
		$this->language->set(
			$this->config->lang_target,
			$langViews . '_EMPTYSTATE_BUTTON_ADD',
			'Add your first ' . $name_single
		);
	}

	/**
	 * Build the toolbar for readonly modal views.
	 *
	 * @param  array   $view            The view array containing settings.
	 * @param  string  $nameSingleCode  The single item code name.
	 *
	 * @return string  The toolbar code.
	 * @since  5.1.4
	 */
	protected function buildModalReadonlyToolbar(array $view, string $nameSingleCode): string
	{
		$viewNameLang_readonly = $this->config->lang_prefix . '_'
			. StringHelper::safe($view['settings']->name_single . ' readonly', 'U');

		$this->language->set(
			$this->config->lang_target,
			$viewNameLang_readonly,
			$view['settings']->name_single . ' :: Readonly'
		);

		$toolBar = "\$this->input->set('hidemainmenu', true);";

		$toolBar .= PHP_EOL . Indent::_(2)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::title(Text::_('COM_COMPONENTBUILDER__VIEWNAMELANG_READONLY_'), '" . $nameSingleCode . "');";
		$toolBar .= PHP_EOL . Indent::_(2)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::cancel('"
			. $nameSingleCode . ".cancel', 'JTOOLBAR_CLOSE');";

		return $toolBar;
	}

	/**
	 * Build the toolbar for editable modal views.
	 *
	 * @param  array   $view            The view array containing settings.
	 * @param  string  $nameSingleCode  The single item code name.
	 *
	 * @return string  The toolbar code.
	 * @since  5.1.4
	 */
	protected function buildModalEditableToolbar(array $view, string $nameSingleCode): string
	{
		// Step 1: Set up language strings
		[$viewNameLang_new, $viewNameLang_edit] = $this->setEditableLanguageStrings($view);

		// Step 2: Initialize toolbar and user
		$toolBar = $this->initializeToolbarUser();

		// Step 3: Add modal title
		$toolBar .= $this->addToolbarTitle($viewNameLang_new, $viewNameLang_edit);

		// Step 4: Referral-based permissions
		$toolBar .= $this->buildModalReferralSection($view, $nameSingleCode);

		// Step 5: Permission-based sections for modal
		$toolBar .= $this->buildModalPermissionSections($view, $nameSingleCode);

		return $toolBar;
	}

	/**
	 * Define new/edit language strings and load them into the language system.
	 *
	 * @param  array  $view  The view data containing settings.
	 *
	 * @return array<string>  The [newLangKey, editLangKey].
	 * @since  5.1.4
	 */
	protected function setEditableLanguageStrings(array $view): array
	{
		$viewNameLang_new  = $this->config->lang_prefix . '_'
			. StringHelper::safe($view['settings']->name_single . ' New', 'U');
		$viewNameLang_edit = $this->config->lang_prefix . '_'
			. StringHelper::safe($view['settings']->name_single . ' Edit', 'U');

		$this->language->set(
			$this->config->lang_target,
			$viewNameLang_new,
			'A New ' . $view['settings']->name_single
		);
		$this->language->set(
			$this->config->lang_target,
			$viewNameLang_edit,
			'Editing the ' . $view['settings']->name_single
		);

		return [$viewNameLang_new, $viewNameLang_edit];
	}

	/**
	 * Initialize the toolbar, user, and basic view setup.
	 *
	 * @return string  Partial toolbar string.
	 * @since  5.1.4
	 */
	protected function initializeToolbarUser(): string
	{
		$toolBar = "\$this->input->set('hidemainmenu', true);";
		$toolBar .= PHP_EOL . Indent::_(2)
			. "\$user = \$this->getCurrentUser();";

		$toolBar .= PHP_EOL . Indent::_(2) . "\$userId = \$user->id;";
		$toolBar .= PHP_EOL . Indent::_(2)
			. "\$isNew = \$this->item->id == 0;";

		return $toolBar;
	}

	/**
	 * Add the toolbar title and its logic line comment.
	 *
	 * @param  string  $viewNameLang_new   The new item language key.
	 * @param  string  $viewNameLang_edit  The edit item language key.
	 *
	 * @return string  Partial toolbar string.
	 * @since  5.1.4
	 */
	protected function addToolbarTitle(string $viewNameLang_new, string $viewNameLang_edit): string
	{
		$toolBar  = PHP_EOL . PHP_EOL . Indent::_(2)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::title( Joomla__"
			. "_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_(\$isNew ? '"
			. $viewNameLang_new . "' : '" . $viewNameLang_edit
			. "'), 'pencil-2 article-add');";

		$toolBar .= PHP_EOL . Indent::_(2) . "//"
			. Line::_(__LINE__, __CLASS__) . " Built the actions for new and existing records.";

		return $toolBar;
	}

	/**
	 * Build the referral section for editable modal toolbars.
	 *
	 * @param  array   $view            The view array.
	 * @param  string  $nameSingleCode  The name single code.
	 *
	 * @return string  Partial toolbar string.
	 * @since  5.1.4
	 */
	protected function buildModalReferralSection(array $view, string $nameSingleCode): string
	{
		$toolBar  = PHP_EOL . Indent::_(2)
			. "if (Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$this->referral))";
		$toolBar .= PHP_EOL . Indent::_(2) . "{";
		$toolBar .= PHP_EOL . Indent::_(3)
			. "if (\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.create') . "') && \$isNew)";
		$toolBar .= PHP_EOL . Indent::_(3) . "{";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "//" . Line::_(__LINE__, __CLASS__) . " We can create the record.";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::save('"
			. $nameSingleCode . ".save', 'JTOOLBAR_SAVE');";
		$toolBar .= PHP_EOL . Indent::_(3) . "}";
		$toolBar .= PHP_EOL . Indent::_(3)
			. "elseif (\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.edit') . "'))";
		$toolBar .= PHP_EOL . Indent::_(3) . "{";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "//" . Line::_(__LINE__, __CLASS__) . " We can save the record.";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::save('"
			. $nameSingleCode . ".save', 'JTOOLBAR_SAVE');";
		$toolBar .= PHP_EOL . Indent::_(3) . "}";
		$toolBar .= PHP_EOL . Indent::_(3)
			. "if (\$isNew)";
		$toolBar .= PHP_EOL . Indent::_(3) . "{";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "//" . Line::_(__LINE__, __CLASS__) . " Do not create but cancel.";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::cancel('"
			. $nameSingleCode . ".cancel', 'JTOOLBAR_CANCEL');";
		$toolBar .= PHP_EOL . Indent::_(3) . "}";
		$toolBar .= PHP_EOL . Indent::_(3)
			. "else";
		$toolBar .= PHP_EOL . Indent::_(3) . "{";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "//" . Line::_(__LINE__, __CLASS__) . " We can close it.";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::cancel('"
			. $nameSingleCode . ".cancel', 'JTOOLBAR_CLOSE');";
		$toolBar .= PHP_EOL . Indent::_(3) . "}";
		$toolBar .= PHP_EOL . Indent::_(2) . "}";

		return $toolBar;
	}

	/**
	 * Build the permission-based sections for editable modal toolbars.
	 *
	 * @param  array   $view            The view data.
	 * @param  string  $nameSingleCode  The single item code name.
	 *
	 * @return string  Partial toolbar string.
	 * @since  5.1.4
	 */
	protected function buildModalPermissionSections(array $view, string $nameSingleCode): string
	{
		$toolBar  = PHP_EOL . Indent::_(2) . "else";
		$toolBar .= PHP_EOL . Indent::_(2) . "{";
		$toolBar .= PHP_EOL . Indent::_(3) . "if (\$isNew)";
		$toolBar .= PHP_EOL . Indent::_(3) . "{";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "//" . Line::_(__LINE__, __CLASS__) . " For new records, check the create permission.";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "if (\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.create') . "'))";
		$toolBar .= PHP_EOL . Indent::_(4) . "{";
		$toolBar .= PHP_EOL . Indent::_(5)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::apply('"
			. $nameSingleCode . ".apply', 'JTOOLBAR_APPLY');";
		$toolBar .= PHP_EOL . Indent::_(5)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::save('"
			. $nameSingleCode . ".save', 'JTOOLBAR_SAVE');";
		$toolBar .= PHP_EOL . Indent::_(5)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::custom('"
			. $nameSingleCode
			. ".save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);";
		$toolBar .= PHP_EOL . Indent::_(4) . "};";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::cancel('"
			. $nameSingleCode . ".cancel', 'JTOOLBAR_CANCEL');";
		$toolBar .= PHP_EOL . Indent::_(3) . "}";
		$toolBar .= PHP_EOL . Indent::_(3) . "else";
		$toolBar .= PHP_EOL . Indent::_(3) . "{";
		$toolBar .= PHP_EOL . Indent::_(4)
			. "if (\$this->canDo->get('"
			. $this->permission->getGlobal($nameSingleCode, 'core.edit') . "'))";
		$toolBar .= PHP_EOL . Indent::_(4) . "{";
		$toolBar .= PHP_EOL . Indent::_(5)
			. "//" . Line::_(__LINE__, __CLASS__) . " We can save the new record";
		$toolBar .= PHP_EOL . Indent::_(5)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::apply('"
			. $nameSingleCode . ".apply', 'JTOOLBAR_APPLY');";
		$toolBar .= PHP_EOL . Indent::_(5)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::save('"
			. $nameSingleCode . ".save', 'JTOOLBAR_SAVE');";
		$toolBar .= PHP_EOL . Indent::_(4) . "}";

		$toolBar .= $this->custombuttons->get($view, 2, Indent::_(2));

		$toolBar .= PHP_EOL . Indent::_(4)
			. "Joomla__" . "_0c1a176a_304f_433a_8233_37d01ff87815___Power::cancel('"
			. $nameSingleCode . ".cancel', 'JTOOLBAR_CLOSE');";
		$toolBar .= PHP_EOL . Indent::_(3) . "}";
		$toolBar .= PHP_EOL . Indent::_(2) . "}";

		return $toolBar;
	}
}

