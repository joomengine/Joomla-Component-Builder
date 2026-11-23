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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Controller\AllowDelete as ApiAllowDelete;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Controller\AllowView as ApiAllowView;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Controller\GetModel as ApiGetModel;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Controller\RecordId as ApiRecordId;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\View\FieldPermissions as ApiFieldPermissions;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\View\Fields as ApiFields;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\View\PrepareItem as ApiPrepareItem;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\View\Relationships as ApiRelationships;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Serializer\Relations as ApiRelations;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\FadeInEffect;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\TabLayoutFields;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\ViewScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\LicenseLock;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Field\SetAccessControl;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Menu\AdminView as MenuAdminView;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\CheckboxSave;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\GetItemMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\ValidationFix;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Table\Constructor as TableConstructor;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\Jquery;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Creator\Fieldsetinterface;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminView\AddModalToolBarInterface as AdminViewAddModalToolBarInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminView\AddToolBarInterface as AdminViewAddToolBarInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminView\EditBodyInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ItemSaveInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\AjaxTokenInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface as Event;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\HeaderInterface;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Admin Views Edit View Class.
 * 
 * Builds everything the edit view of one admin view is made of: the form it
 * shows, the toolbar above it, the table and model behind it, and the scripts
 * it runs. A view the component gave no single name is not one you can edit,
 * and gets none of it.
 * 
 * The order the pieces are asked for is the order the compiler has always
 * asked for them in, and the events fired between them are the same events.
 * 
 * @since 6.1.7
 */
final class EditView
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Event Class.
	 *
	 * @var   Event
	 * @since 6.1.7
	 */
	protected Event $event;

	/**
	 * The Header Class.
	 *
	 * @var   HeaderInterface
	 * @since 6.1.7
	 */
	protected HeaderInterface $header;

	/**
	 * The Customcode Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 6.1.7
	 */
	protected Dispenser $dispenser;

	/**
	 * The Content One Builder Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * The Content Multi Builder Class.
	 *
	 * @var   ContentMulti
	 * @since 6.1.7
	 */
	protected ContentMulti $contentmulti;

	/**
	 * The Fieldset Creator Class.
	 *
	 * @var   Fieldsetinterface
	 * @since 6.1.7
	 */
	protected Fieldsetinterface $fieldset;

	/**
	 * The AdminView AddToolBar Class.
	 *
	 * @var   AdminViewAddToolBarInterface
	 * @since 6.1.7
	 */
	protected AdminViewAddToolBarInterface $addtoolbar;

	/**
	 * The AdminView AddModalToolBar Class.
	 *
	 * @var   AdminViewAddModalToolBarInterface
	 * @since 6.1.7
	 */
	protected AdminViewAddModalToolBarInterface $addmodaltoolbar;

	/**
	 * The AdminView ViewScript Class.
	 *
	 * @var   ViewScript
	 * @since 6.1.7
	 */
	protected ViewScript $viewscript;

	/**
	 * The AdminView TabLayoutFields Class.
	 *
	 * @var   TabLayoutFields
	 * @since 6.1.7
	 */
	protected TabLayoutFields $tablayoutfields;

	/**
	 * The AdminView EditBody Class.
	 *
	 * @var   EditBodyInterface
	 * @since 6.1.7
	 */
	protected EditBodyInterface $editbody;

	/**
	 * The AdminView FadeInEffect Class.
	 *
	 * @var   FadeInEffect
	 * @since 6.1.7
	 */
	protected FadeInEffect $fadeineffect;

	/**
	 * The Menu AdminView Class.
	 *
	 * @var   MenuAdminView
	 * @since 6.1.7
	 */
	protected MenuAdminView $menuadminview;

	/**
	 * The View AjaxToken Class.
	 *
	 * @var   AjaxTokenInterface
	 * @since 6.1.7
	 */
	protected AjaxTokenInterface $ajaxtoken;

	/**
	 * The View Jquery Class.
	 *
	 * @var   Jquery
	 * @since 6.1.7
	 */
	protected Jquery $jquery;

	/**
	 * The Model CheckboxSave Class.
	 *
	 * @var   CheckboxSave
	 * @since 6.1.7
	 */
	protected CheckboxSave $checkboxsave;

	/**
	 * The Model GetItemMethod Class.
	 *
	 * @var   GetItemMethod
	 * @since 6.1.7
	 */
	protected GetItemMethod $getitemmethod;

	/**
	 * The Model ItemSave Class.
	 *
	 * @var   ItemSaveInterface
	 * @since 6.1.7
	 */
	protected ItemSaveInterface $itemsave;

	/**
	 * The Model ValidationFix Class.
	 *
	 * @var   ValidationFix
	 * @since 6.1.7
	 */
	protected ValidationFix $validationfix;

	/**
	 * The Field SetAccessControl Class.
	 *
	 * @var   SetAccessControl
	 * @since 6.1.7
	 */
	protected SetAccessControl $setaccesscontrol;

	/**
	 * The Table Constructor Class.
	 *
	 * @var   TableConstructor
	 * @since 6.1.7
	 */
	protected TableConstructor $tableconstructor;

	/**
	 * The Component LicenseLock Class.
	 *
	 * @var   LicenseLock
	 * @since 6.1.7
	 */
	protected LicenseLock $licenselock;

	/**
	 * The Api Controller GetModel Class.
	 *
	 * @var   ApiGetModel
	 * @since 6.1.7
	 */
	protected ApiGetModel $apigetmodel;

	/**
	 * The Api Controller RecordId Class.
	 *
	 * @var   ApiRecordId
	 * @since 6.1.7
	 */
	protected ApiRecordId $apirecordid;

	/**
	 * The Api Controller AllowView Class.
	 *
	 * @var   ApiAllowView
	 * @since 6.1.7
	 */
	protected ApiAllowView $apiallowview;

	/**
	 * The Api Controller AllowDelete Class.
	 *
	 * @var   ApiAllowDelete
	 * @since 6.1.7
	 */
	protected ApiAllowDelete $apiallowdelete;

	/**
	 * The Api View Fields Class.
	 *
	 * @var   ApiFields
	 * @since 6.1.7
	 */
	protected ApiFields $apifields;

	/**
	 * The Api View FieldPermissions Class.
	 *
	 * @var   ApiFieldPermissions
	 * @since 6.1.7
	 */
	protected ApiFieldPermissions $apifieldpermissions;

	/**
	 * The Api View PrepareItem Class.
	 *
	 * @var   ApiPrepareItem
	 * @since 6.1.7
	 */
	protected ApiPrepareItem $apiprepareitem;

	/**
	 * The Api View Relationships Class.
	 *
	 * @var   ApiRelationships
	 * @since 6.1.7
	 */
	protected ApiRelationships $apirelationships;

	/**
	 * The Api Serializer Relations Class.
	 *
	 * @var   ApiRelations
	 * @since 6.1.7
	 */
	protected ApiRelations $apirelations;

	/**
	 * Constructor.
	 *
	 * @param Config                            $config                                The Config Class.
	 * @param Event                             $event                                 The Event Class.
	 * @param HeaderInterface                   $header                                The Header Class.
	 * @param Dispenser                         $dispenser                             The Customcode Dispenser Class.
	 * @param ContentOne                        $contentone                            The Content One Builder Class.
	 * @param ContentMulti                      $contentmulti                          The Content Multi Builder Class.
	 * @param Fieldsetinterface                          $fieldset                              The Fieldset Creator Class.
	 * @param AdminViewAddToolBarInterface      $addtoolbar                            The AdminView AddToolBar Class.
	 * @param AdminViewAddModalToolBarInterface $addmodaltoolbar                       The AdminView AddModalToolBar Class.
	 * @param ViewScript                        $viewscript                            The AdminView ViewScript Class.
	 * @param TabLayoutFields                   $tablayoutfields                       The AdminView TabLayoutFields Class.
	 * @param EditBodyInterface                 $editbody                              The AdminView EditBody Class.
	 * @param FadeInEffect                      $fadeineffect                          The AdminView FadeInEffect Class.
	 * @param MenuAdminView                     $menuadminview                         The Menu AdminView Class.
	 * @param AjaxTokenInterface                $ajaxtoken                             The View AjaxToken Class.
	 * @param Jquery                            $jquery                                The View Jquery Class.
	 * @param CheckboxSave                      $checkboxsave                          The Model CheckboxSave Class.
	 * @param GetItemMethod                     $getitemmethod                         The Model GetItemMethod Class.
	 * @param ItemSaveInterface                 $itemsave                              The Model ItemSave Class.
	 * @param ValidationFix                     $validationfix                         The Model ValidationFix Class.
	 * @param SetAccessControl                  $setaccesscontrol                      The Field SetAccessControl Class.
	 * @param TableConstructor                  $tableconstructor                      The Table Constructor Class.
	 * @param LicenseLock                       $licenselock                           The Component LicenseLock Class.
	 * @param ApiGetModel   $apigetmodel   The Api Controller GetModel Class.
	 * @param ApiRecordId   $apirecordid   The Api Controller RecordId Class.
	 * @param ApiAllowView   $apiallowview   The Api Controller AllowView Class.
	 * @param ApiAllowDelete   $apiallowdelete   The Api Controller AllowDelete Class.
	 * @param ApiFields   $apifields   The Api View Fields Class.
	 * @param ApiFieldPermissions   $apifieldpermissions   The Api View FieldPermissions Class.
	 * @param ApiPrepareItem   $apiprepareitem   The Api View PrepareItem Class.
	 * @param ApiRelationships   $apirelationships   The Api View Relationships Class.
	 * @param ApiRelations   $apirelations   The Api Serializer Relations Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Event $event,
		HeaderInterface $header,
		Dispenser $dispenser,
		ContentOne $contentone,
		ContentMulti $contentmulti,
		Fieldsetinterface $fieldset,
		AdminViewAddToolBarInterface $addtoolbar,
		AdminViewAddModalToolBarInterface $addmodaltoolbar,
		ViewScript $viewscript,
		TabLayoutFields $tablayoutfields,
		EditBodyInterface $editbody,
		FadeInEffect $fadeineffect,
		MenuAdminView $menuadminview,
		AjaxTokenInterface $ajaxtoken,
		Jquery $jquery,
		CheckboxSave $checkboxsave,
		GetItemMethod $getitemmethod,
		ItemSaveInterface $itemsave,
		ValidationFix $validationfix,
		SetAccessControl $setaccesscontrol,
		TableConstructor $tableconstructor,
		LicenseLock $licenselock,
		ApiGetModel $apigetmodel,
		ApiRecordId $apirecordid,
		ApiAllowView $apiallowview,
		ApiAllowDelete $apiallowdelete,
		ApiFields $apifields,
		ApiFieldPermissions $apifieldpermissions,
		ApiPrepareItem $apiprepareitem,
		ApiRelationships $apirelationships,
		ApiRelations $apirelations)
	{
		$this->config = $config;
		$this->event = $event;
		$this->header = $header;
		$this->dispenser = $dispenser;
		$this->contentone = $contentone;
		$this->contentmulti = $contentmulti;
		$this->fieldset = $fieldset;
		$this->addtoolbar = $addtoolbar;
		$this->addmodaltoolbar = $addmodaltoolbar;
		$this->viewscript = $viewscript;
		$this->tablayoutfields = $tablayoutfields;
		$this->editbody = $editbody;
		$this->fadeineffect = $fadeineffect;
		$this->menuadminview = $menuadminview;
		$this->ajaxtoken = $ajaxtoken;
		$this->jquery = $jquery;
		$this->checkboxsave = $checkboxsave;
		$this->getitemmethod = $getitemmethod;
		$this->itemsave = $itemsave;
		$this->validationfix = $validationfix;
		$this->setaccesscontrol = $setaccesscontrol;
		$this->tableconstructor = $tableconstructor;
		$this->licenselock = $licenselock;
		$this->apigetmodel = $apigetmodel;
		$this->apirecordid = $apirecordid;
		$this->apiallowview = $apiallowview;
		$this->apiallowdelete = $apiallowdelete;
		$this->apifields = $apifields;
		$this->apifieldpermissions = $apifieldpermissions;
		$this->apiprepareitem = $apiprepareitem;
		$this->apirelationships = $apirelationships;
		$this->apirelations = $apirelations;
	}

	/**
	 * Build the edit view of one admin view.
	 *
	 * @param   array   $view            The view being built.
	 * @param   string  $nameSingleCode  The single view name.
	 * @param   string  $nameListCode    The list view name.
	 *
	 * @return  void
	 *
	 * @since   6.1.7
	 */
	public function build(&$view, &$nameSingleCode, &$nameListCode): void
	{
		// set the view names
		if (isset($view['settings']->name_single)
			&& $view['settings']->name_single != 'null')
		{
			// set license per view if needed
			$this->licenselock->setView(
				$nameSingleCode
			);
			$this->licenselock->setView(
				$nameListCode
			);

			// Trigger Event: jcb_ce_onBeforeBuildAdminEditViewContent
			$this->event->trigger(
				'jcb_ce_onBeforeBuildAdminEditViewContent', [&$view, &$nameSingleCode, &$nameListCode]
			);

			// Here we set defaults
			// The real values are set in ModalSelect(4fc020dc-3137-478d-8d42-0571a75b77b5)

			// add the Title Key for the Modal
			$this->contentmulti->set($nameSingleCode . '|SQL_TITLE_KEY', 'id');

			// add the Title Column for the Modal
			$this->contentmulti->set($nameSingleCode . '|SQL_TITLE_COLUMN', 'name');

			// FIELDSETS <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|FIELDSETS',
				$this->fieldset->get(
					$view,
					$this->config->component_code_name,
					$nameSingleCode,
					$nameListCode
				)
			);

			// ACCESSCONTROL <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|ACCESSCONTROL',
				$this->setaccesscontrol->get(
					$nameSingleCode
				)
			);

			// LINKEDVIEWITEMS <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|LINKEDVIEWITEMS', '');

			// ADDTOOLBAR <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|ADDTOOLBAR',
				$this->addtoolbar->get($view)
			);

			// INITTOOLBAR <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|INITTOOLBAR',
				$this->addtoolbar->initSite()
			);

			// ADDMODALTOOLBAR <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|ADDMODALTOOLBAR',
				$this->addmodaltoolbar->get($view)
			);

			// set the script for this view
			$this->viewscript->get($view);

			// VIEW_SCRIPT
			$this->contentmulti->set($nameSingleCode . '|VIEW_SCRIPT',
				$this->viewscript->script(
					$nameSingleCode, 'fileScript'
				)
			);

			// EDITBODYSCRIPT
			$this->contentmulti->set($nameSingleCode . '|EDITBODYSCRIPT',
				$this->viewscript->script(
					$nameSingleCode, 'footerScript'
				)
			);

			// AJAXTOKE <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|AJAXTOKE',
				$this->ajaxtoken->get(
					$nameSingleCode
				)
			);

			// DOCUMENT_CUSTOM_PHP <<<DYNAMIC>>>
			if ($phpDocument = $this->dispenser->get(
				'php_document', $nameSingleCode,
				PHP_EOL, null, true,
				false
			))
			{
				$this->contentmulti->set($nameSingleCode . '|DOCUMENT_CUSTOM_PHP',
					str_replace(
						'$document->', '$this->getDocument()->', (string) $phpDocument
					)
				);
				// clear some memory
				unset($phpDocument);
			}
			else
			{
				$this->contentmulti->set($nameSingleCode . '|DOCUMENT_CUSTOM_PHP', '');
			}
			// LINKEDVIEWTABLESCRIPTS <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|LINKEDVIEWTABLESCRIPTS', '');

			// VALIDATION_PROPERTIES <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|VALIDATION_PROPERTIES',
				$this->validationfix->getProperties($nameSingleCode)
			);

			// VALIDATEFIX <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|VALIDATIONFIX',
				$this->validationfix->get(
					$nameSingleCode,
					$this->contentone->get('Component')
				)
			);

			// EDITBODY <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|EDITBODY',
				$this->editbody->get($view)
			);

			// EDITBODYFADEIN <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|EDITBODYFADEIN',
				$this->fadeineffect->get($view)
			);

			// JTABLECONSTRUCTOR <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|JTABLECONSTRUCTOR',
				$this->tableconstructor->get(
					$nameSingleCode
				)
			);

			// JTABLEALIASCATEGORY <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|JTABLEALIASCATEGORY',
				$this->tableconstructor->aliasCategory(
					$nameSingleCode
				)
			);

			// METHOD_GET_ITEM <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|METHOD_GET_ITEM',
				$this->getitemmethod->get(
					$nameSingleCode
				)
			);

			// LINKEDVIEWGLOBAL <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|LINKEDVIEWGLOBAL', '');

			// LINKEDVIEWMETHODS <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|LINKEDVIEWMETHODS', '');

			// JMODELADMIN_BEFORE_DELETE <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|JMODELADMIN_BEFORE_DELETE',
				$this->dispenser->get(
					'php_before_delete',
					$nameSingleCode, PHP_EOL
				)
			);

			// JMODELADMIN_AFTER_DELETE <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|JMODELADMIN_AFTER_DELETE',
				$this->dispenser->get(
					'php_after_delete', $nameSingleCode,
					PHP_EOL . PHP_EOL
				)
			);

			// JMODELADMIN_BEFORE_DELETE <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|JMODELADMIN_BEFORE_PUBLISH',
				$this->dispenser->get(
					'php_before_publish',
					$nameSingleCode, PHP_EOL
				)
			);

			// JMODELADMIN_AFTER_DELETE <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|JMODELADMIN_AFTER_PUBLISH',
				$this->dispenser->get(
					'php_after_publish',
					$nameSingleCode, PHP_EOL . PHP_EOL
				)
			);

			// CHECKBOX_SAVE <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|CHECKBOX_SAVE',
				$this->checkboxsave->get(
					$nameSingleCode
				)
			);

			// METHOD_ITEM_SAVE <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|METHOD_ITEM_SAVE',
				$this->itemsave->get(
					$nameSingleCode
				)
			);

			// POSTSAVEHOOK <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|POSTSAVEHOOK',
				$this->dispenser->get(
					'php_postsavehook', $nameSingleCode,
					PHP_EOL, null,
					true, PHP_EOL . Indent::_(2) . "return;",
					PHP_EOL . PHP_EOL . Indent::_(2) . "return;"
				)
			);

			// VIEWCSS <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|VIEWCSS',
				$this->dispenser->get(
					'css_view', $nameSingleCode, '',
					null, true
				)
			);

			// AJAXTOKE <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|AJAXTOKE',
				$this->ajaxtoken->get(
					$nameSingleCode
				)
			);

			// add css to front end
			if (isset($view['edit_create_site_view'])
				&& is_numeric($view['edit_create_site_view'])
				&& $view['edit_create_site_view'] > 0)
			{
				$this->contentmulti->set($nameSingleCode . '|SITE_VIEWCSS',
					$this->contentmulti->get($nameSingleCode . '|VIEWCSS', '')
				);
				// check if we should add a create menu
				if ($view['edit_create_site_view'] == 2)
				{
					// SITE_MENU_XML <<<DYNAMIC>>>
					$this->contentmulti->set($nameSingleCode . '|SITE_MENU_XML',
						$this->menuadminview->get(
							$nameSingleCode, $view
						)
					);
				}
				// SITE_ADMIN_VIEW_CONTROLLER_HEADER <<<DYNAMIC>>> add the header details for the controller
				$this->contentmulti->set($nameSingleCode . '|SITE_ADMIN_VIEW_CONTROLLER_HEADER',
					$this->header->get(
						'site.admin.view.controller',
						$nameSingleCode
					)
				);
				// SITE_ADMIN_VIEW_MODEL_HEADER <<<DYNAMIC>>> add the header details for the model
				$this->contentmulti->set($nameSingleCode . '|SITE_ADMIN_VIEW_MODEL_HEADER',
					$this->header->get(
						'site.admin.view.model',
						$nameSingleCode
					)
				);
				// SITE_ADMIN_VIEW_HTML_HEADER <<<DYNAMIC>>> add the header details for the view
				$this->contentmulti->set($nameSingleCode . '|SITE_ADMIN_VIEW_HTML_HEADER',
					$this->header->get(
						'site.admin.view.html',
						$nameSingleCode
					)
				);
				// SITE_ADMIN_VIEW_HEADER <<<DYNAMIC>>> add the header details for the view
				$this->contentmulti->set($nameSingleCode . '|SITE_ADMIN_VIEW_HEADER',
					$this->header->get(
						'site.admin.view',
						$nameSingleCode
					)
				);
			}

			// TABLAYOUTFIELDSARRAY <<<DYNAMIC>>> add the tab layout fields array to the model
			$this->contentmulti->set($nameSingleCode . '|TABLAYOUTFIELDSARRAY',
				$this->tablayoutfields->get(
					$nameSingleCode
				)
			);

			// ADMIN_VIEW_CONTROLLER_HEADER <<<DYNAMIC>>> add the header details for the controller
			$this->contentmulti->set($nameSingleCode . '|ADMIN_VIEW_CONTROLLER_HEADER',
				$this->header->get(
					'admin.view.controller',
					$nameSingleCode
				)
			);

			// ADMIN_VIEW_MODEL_HEADER <<<DYNAMIC>>> add the header details for the model
			$this->contentmulti->set($nameSingleCode . '|ADMIN_VIEW_MODEL_HEADER',
				$this->header->get(
					'admin.view.model', $nameSingleCode
				)
			);

			// Always populate the shared model template, including views without API routes.
			$this->contentmulti->set($nameSingleCode . '|ADMIN_VIEW_MODEL_ITEM_ACCESS',
				$this->apiallowview->getItemGuard($nameSingleCode, !empty($view['add_api']))
			);
			// ADMIN_VIEW_HTML_HEADER <<<DYNAMIC>>> add the header details for the view
			$this->contentmulti->set($nameSingleCode . '|ADMIN_VIEW_HTML_HEADER',
				$this->header->get(
					'admin.view.html', $nameSingleCode
				)
			);
			// ADMIN_VIEW_HEADER <<<DYNAMIC>>> add the header details for the view
			$this->contentmulti->set($nameSingleCode . '|ADMIN_VIEW_HEADER',
				$this->header->get(
					'admin.view', $nameSingleCode
				)
			);

			// API_VIEW_CONTROLLER_HEADER <<<DYNAMIC>>> add the header details for the controller
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_CONTROLLER_HEADER',
				$this->header->get(
					'api.view.controller',
					$nameSingleCode
				)
			);

			// API_VIEW_JSON_HEADER <<<DYNAMIC>>> add the header details for the controller
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_JSON_HEADER',
				$this->header->get(
					'api.view.json',
					$nameSingleCode
				)
			);

			// API_VIEW_CONTROLLER_GETMODEL <<<DYNAMIC>>> add the explicit model mapping to the api controller
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_CONTROLLER_GETMODEL',
				$this->apigetmodel->get(
					$nameSingleCode, $nameListCode, false
				)
			);

			// API_VIEW_CONTROLLER_RECORDID <<<DYNAMIC>>> add the record id resolution to the api controller
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_CONTROLLER_RECORDID',
				$this->apirecordid->get(
					$nameSingleCode
				)
			);

			// API_VIEW_CONTROLLER_ALLOWVIEW <<<DYNAMIC>>> add the allow view permission to the api controller
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_CONTROLLER_ALLOWVIEW',
				$this->apiallowview->get(
					$nameSingleCode
				)
			);

			// API_VIEW_CONTROLLER_ALLOWDELETE <<<DYNAMIC>>> add the allow delete permission to the api controller
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_CONTROLLER_ALLOWDELETE',
				$this->apiallowdelete->get(
					$nameSingleCode
				)
			);

			// API_VIEW_JSON_FIELDS <<<DYNAMIC>>> add the fields to render to the api json view
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_JSON_FIELDS',
				$this->apifields->get(
					$nameSingleCode
				)
			);

			// API_VIEW_JSON_PERMISSIONS <<<DYNAMIC>>> add the field permission guards to the api json view
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_JSON_PERMISSIONS',
				$this->apifieldpermissions->get(
					$nameSingleCode, true
				)
			);

			// API_VIEW_JSON_PREPAREITEM <<<DYNAMIC>>> add the prepare item code to the api json view
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_JSON_PREPAREITEM',
				$this->apiprepareitem->get(
					$nameSingleCode, false
				)
			);

			// API_VIEW_JSON_RELATIONSHIP <<<DYNAMIC>>> add the relationships to the api json view
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_JSON_RELATIONSHIP',
				$this->apirelationships->get(
					$nameSingleCode, $nameListCode, true
				)
			);

			// API_VIEW_SERIALIZER_HEADER <<<DYNAMIC>>> add the header details for the serializer
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_SERIALIZER_HEADER',
				$this->header->get(
					'api.view.serializer',
					$nameSingleCode
				)
			);

			// API_VIEW_SERIALIZER_RELATIONS <<<DYNAMIC>>> add the relationship methods to the api serializer
			$this->contentmulti->set($nameSingleCode . '|API_VIEW_SERIALIZER_RELATIONS',
				$this->apirelations->get(
					$nameSingleCode, $nameListCode
				)
			);

			// JQUERY <<<DYNAMIC>>>
			$this->contentmulti->set($nameSingleCode . '|JQUERY',
				$this->jquery->get(
					$nameSingleCode
				)
			);

			// Trigger Event: jcb_ce_onAfterBuildAdminEditViewContent
			$this->event->trigger(
				'jcb_ce_onAfterBuildAdminEditViewContent',[&$view, &$nameSingleCode, &$nameListCode]
			);
		}
	}
}

