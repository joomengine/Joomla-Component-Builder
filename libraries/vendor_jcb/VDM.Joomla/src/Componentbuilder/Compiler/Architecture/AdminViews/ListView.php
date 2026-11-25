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


use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Controller\DisplayList as ApiDisplayList;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\Controller\GetModel as ApiGetModel;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\View\FieldPermissions as ApiFieldPermissions;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\View\Fields as ApiFields;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\View\PrepareItem as ApiPrepareItem;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Api\View\Relationships as ApiRelationships;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView\ViewScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews\CanDo;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\ImportCustomScripts;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomButtons;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\FilterFields;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\PopulateState;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\SelectionTranslation;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\SelectionTranslationMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\SortFields;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\StoredId;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\View\Jquery;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\EximportView;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ImportCustomScripts as ImportCustomScriptsBuilder;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ListColumnNumber;
use VDM\Joomla\Componentbuilder\Compiler\Builder\OnlyFunctionButtons;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\BatchOptionsInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\DisplayMethodInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\EximportButtonsInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\FilterFieldHelperInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\FilterListSetInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\FilterSetInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\ListBodyInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\ListHeadInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\SidebarFiltersInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\ViewBodyInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Controller\EximportMethodInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\CheckInNowInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ItemsMethodInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ItemsStringFixInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ListQueryInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Router\SiteRouter;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface as Event;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\HeaderInterface;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * Admin Views List View Class.
 * 
 * Builds everything the list view of one admin view is made of: what it shows
 * in each column, what it can be filtered and ordered by, the buttons above
 * it, and the model that fetches the rows. A view the component gave no list
 * name is not one you can list, and gets none of it.
 * 
 * The order the pieces are asked for is the order the compiler has always
 * asked for them in, and the events fired between them are the same events.
 * 
 * @since 6.1.7
 */
class ListView
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
	 * The AdminView ViewScript Class.
	 *
	 * @var   ViewScript
	 * @since 6.1.7
	 */
	protected ViewScript $viewscript;

	/**
	 * The View Jquery Class.
	 *
	 * @var   Jquery
	 * @since 6.1.7
	 */
	protected Jquery $jquery;

	/**
	 * The Custom Buttons Class.
	 *
	 * @var   CustomButtons
	 * @since 6.1.7
	 */
	protected CustomButtons $custombuttons;

	/**
	 * The Model CheckInNow Class.
	 *
	 * @var   CheckInNowInterface
	 * @since 6.1.7
	 */
	protected CheckInNowInterface $checkinnow;

	/**
	 * The Only Function Buttons Builder Class.
	 *
	 * @var   OnlyFunctionButtons
	 * @since 6.1.7
	 */
	protected OnlyFunctionButtons $onlyfunctionbuttons;

	/**
	 * The Eximport View Builder Class.
	 *
	 * @var   EximportView
	 * @since 6.1.7
	 */
	protected EximportView $eximportview;

	/**
	 * The Import Custom Scripts Builder Class.
	 *
	 * @var   ImportCustomScriptsBuilder
	 * @since 6.1.7
	 */
	protected ImportCustomScriptsBuilder $importcustomscriptsbuilder;

	/**
	 * The List Column Number Builder Class.
	 *
	 * @var   ListColumnNumber
	 * @since 6.1.7
	 */
	protected ListColumnNumber $listcolumnnumber;

	/**
	 * The AdminViews DisplayMethod Class.
	 *
	 * @var   DisplayMethodInterface
	 * @since 6.1.7
	 */
	protected DisplayMethodInterface $displaymethod;

	/**
	 * The AdminViews BatchOptions Class.
	 *
	 * @var   BatchOptionsInterface
	 * @since 6.1.7
	 */
	protected BatchOptionsInterface $batchoptions;

	/**
	 * The Controller EximportMethod Class.
	 *
	 * @var   EximportMethodInterface
	 * @since 6.1.7
	 */
	protected EximportMethodInterface $eximportmethod;

	/**
	 * The AdminViews ViewBody Class.
	 *
	 * @var   ViewBodyInterface
	 * @since 6.1.7
	 */
	protected ViewBodyInterface $viewbody;

	/**
	 * The AdminViews EximportButtons Class.
	 *
	 * @var   EximportButtonsInterface
	 * @since 6.1.7
	 */
	protected EximportButtonsInterface $eximportbuttons;

	/**
	 * The AdminViews FilterListSet Class.
	 *
	 * @var   FilterListSetInterface
	 * @since 6.1.7
	 */
	protected FilterListSetInterface $filterlistset;

	/**
	 * The AdminViews FilterSet Class.
	 *
	 * @var   FilterSetInterface
	 * @since 6.1.7
	 */
	protected FilterSetInterface $filterset;

	/**
	 * The AdminViews FilterFieldHelper Class.
	 *
	 * @var   FilterFieldHelperInterface
	 * @since 6.1.7
	 */
	protected FilterFieldHelperInterface $filterfieldhelper;

	/**
	 * The AdminViews SidebarFilters Class.
	 *
	 * @var   SidebarFiltersInterface
	 * @since 6.1.7
	 */
	protected SidebarFiltersInterface $sidebarfilters;

	/**
	 * The Model FilterFields Class.
	 *
	 * @var   FilterFields
	 * @since 6.1.7
	 */
	protected FilterFields $filterfields;

	/**
	 * The Model ItemsStringFix Class.
	 *
	 * @var   ItemsStringFixInterface
	 * @since 6.1.7
	 */
	protected ItemsStringFixInterface $itemsstringfix;

	/**
	 * The Model ItemsMethod Class.
	 *
	 * @var   ItemsMethodInterface
	 * @since 6.1.7
	 */
	protected ItemsMethodInterface $itemsmethod;

	/**
	 * The Component ImportCustomScripts Class.
	 *
	 * @var   ImportCustomScripts
	 * @since 6.1.7
	 */
	protected ImportCustomScripts $importcustomscripts;

	/**
	 * The AdminViews CanDo Class.
	 *
	 * @var   CanDo
	 * @since 6.1.7
	 */
	protected CanDo $cando;

	/**
	 * The AdminViews ListBody Class.
	 *
	 * @var   ListBodyInterface
	 * @since 6.1.7
	 */
	protected ListBodyInterface $listbody;

	/**
	 * The AdminViews ListHead Class.
	 *
	 * @var   ListHeadInterface
	 * @since 6.1.7
	 */
	protected ListHeadInterface $listhead;

	/**
	 * The Model ListQuery Class.
	 *
	 * @var   ListQueryInterface
	 * @since 6.1.7
	 */
	protected ListQueryInterface $listquery;

	/**
	 * The Model PopulateState Class.
	 *
	 * @var   PopulateState
	 * @since 6.1.7
	 */
	protected PopulateState $populatestate;

	/**
	 * The Router SiteRouter Class.
	 *
	 * @var   SiteRouter
	 * @since 6.1.7
	 */
	protected SiteRouter $siterouter;

	/**
	 * The Model SelectionTranslation Class.
	 *
	 * @var   SelectionTranslation
	 * @since 6.1.7
	 */
	protected SelectionTranslation $selectiontranslation;

	/**
	 * The Model SelectionTranslationMethod Class.
	 *
	 * @var   SelectionTranslationMethod
	 * @since 6.1.7
	 */
	protected SelectionTranslationMethod $selectiontranslationmethod;

	/**
	 * The Model SortFields Class.
	 *
	 * @var   SortFields
	 * @since 6.1.7
	 */
	protected SortFields $sortfields;

	/**
	 * The Model StoredId Class.
	 *
	 * @var   StoredId
	 * @since 6.1.7
	 */
	protected StoredId $storedid;

	/**
	 * The Api Controller GetModel Class.
	 *
	 * @var   ApiGetModel
	 * @since 6.1.7
	 */
	protected ApiGetModel $apigetmodel;

	/**
	 * The Api Controller DisplayList Class.
	 *
	 * @var   ApiDisplayList
	 * @since 6.1.7
	 */
	protected ApiDisplayList $apidisplaylist;

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
	 * Constructor.
	 *
	 * @param Config                     $config                         The Config Class.
	 * @param Event                      $event                          The Event Class.
	 * @param HeaderInterface            $header                         The Header Class.
	 * @param Dispenser                  $dispenser                      The Customcode Dispenser Class.
	 * @param ContentOne                 $contentone                     The Content One Builder Class.
	 * @param ContentMulti               $contentmulti                   The Content Multi Builder Class.
	 * @param ViewScript                 $viewscript                     The AdminView ViewScript Class.
	 * @param Jquery                     $jquery                         The View Jquery Class.
	 * @param CustomButtons              $custombuttons                  The Custom Buttons Class.
	 * @param CheckInNowInterface        $checkinnow                     The Model CheckInNow Class.
	 * @param OnlyFunctionButtons        $onlyfunctionbuttons            The Only Function Buttons Builder Class.
	 * @param EximportView               $eximportview                   The Eximport View Builder Class.
	 * @param ImportCustomScriptsBuilder $importcustomscriptsbuilder     The Import Custom Scripts Builder Class.
	 * @param ListColumnNumber           $listcolumnnumber               The List Column Number Builder Class.
	 * @param DisplayMethodInterface     $displaymethod                  The AdminViews DisplayMethod Class.
	 * @param BatchOptionsInterface      $batchoptions                   The AdminViews BatchOptions Class.
	 * @param EximportMethodInterface    $eximportmethod                 The Controller EximportMethod Class.
	 * @param ViewBodyInterface          $viewbody                       The AdminViews ViewBody Class.
	 * @param EximportButtonsInterface   $eximportbuttons                The AdminViews EximportButtons Class.
	 * @param FilterListSetInterface     $filterlistset                  The AdminViews FilterListSet Class.
	 * @param FilterSetInterface         $filterset                      The AdminViews FilterSet Class.
	 * @param FilterFieldHelperInterface $filterfieldhelper              The AdminViews FilterFieldHelper Class.
	 * @param SidebarFiltersInterface    $sidebarfilters                 The AdminViews SidebarFilters Class.
	 * @param FilterFields               $filterfields                   The Model FilterFields Class.
	 * @param ItemsStringFixInterface    $itemsstringfix                 The Model ItemsStringFix Class.
	 * @param ItemsMethodInterface       $itemsmethod                    The Model ItemsMethod Class.
	 * @param ImportCustomScripts        $importcustomscripts            The Component ImportCustomScripts Class.
	 * @param CanDo                      $cando                          The AdminViews CanDo Class.
	 * @param ListBodyInterface          $listbody                       The AdminViews ListBody Class.
	 * @param ListHeadInterface          $listhead                       The AdminViews ListHead Class.
	 * @param ListQueryInterface         $listquery                      The Model ListQuery Class.
	 * @param PopulateState              $populatestate                  The Model PopulateState Class.
	 * @param SiteRouter                  $siterouter                     The Router SiteRouter Class.
	 * @param SelectionTranslation       $selectiontranslation           The Model SelectionTranslation Class.
	 * @param SelectionTranslationMethod $selectiontranslationmethod     The Model SelectionTranslationMethod Class.
	 * @param SortFields                 $sortfields                     The Model SortFields Class.
	 * @param StoredId                   $storedid                       The Model StoredId Class.
	 * @param ApiGetModel   $apigetmodel   The Api Controller GetModel Class.
	 * @param ApiDisplayList   $apidisplaylist   The Api Controller DisplayList Class.
	 * @param ApiFields   $apifields   The Api View Fields Class.
	 * @param ApiFieldPermissions   $apifieldpermissions   The Api View FieldPermissions Class.
	 * @param ApiPrepareItem   $apiprepareitem   The Api View PrepareItem Class.
	 * @param ApiRelationships   $apirelationships   The Api View Relationships Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Event $event,
		HeaderInterface $header,
		Dispenser $dispenser,
		ContentOne $contentone,
		ContentMulti $contentmulti,
		ViewScript $viewscript,
		Jquery $jquery,
		CustomButtons $custombuttons,
		CheckInNowInterface $checkinnow,
		OnlyFunctionButtons $onlyfunctionbuttons,
		EximportView $eximportview,
		ImportCustomScriptsBuilder $importcustomscriptsbuilder,
		ListColumnNumber $listcolumnnumber,
		DisplayMethodInterface $displaymethod,
		BatchOptionsInterface $batchoptions,
		EximportMethodInterface $eximportmethod,
		ViewBodyInterface $viewbody,
		EximportButtonsInterface $eximportbuttons,
		FilterListSetInterface $filterlistset,
		FilterSetInterface $filterset,
		FilterFieldHelperInterface $filterfieldhelper,
		SidebarFiltersInterface $sidebarfilters,
		FilterFields $filterfields,
		ItemsStringFixInterface $itemsstringfix,
		ItemsMethodInterface $itemsmethod,
		ImportCustomScripts $importcustomscripts,
		CanDo $cando,
		ListBodyInterface $listbody,
		ListHeadInterface $listhead,
		ListQueryInterface $listquery,
		PopulateState $populatestate,
		SiteRouter $siterouter,
		SelectionTranslation $selectiontranslation,
		SelectionTranslationMethod $selectiontranslationmethod,
		SortFields $sortfields,
		StoredId $storedid,
		ApiGetModel $apigetmodel,
		ApiDisplayList $apidisplaylist,
		ApiFields $apifields,
		ApiFieldPermissions $apifieldpermissions,
		ApiPrepareItem $apiprepareitem,
		ApiRelationships $apirelationships)
	{
		$this->config = $config;
		$this->event = $event;
		$this->header = $header;
		$this->dispenser = $dispenser;
		$this->contentone = $contentone;
		$this->contentmulti = $contentmulti;
		$this->viewscript = $viewscript;
		$this->jquery = $jquery;
		$this->custombuttons = $custombuttons;
		$this->checkinnow = $checkinnow;
		$this->onlyfunctionbuttons = $onlyfunctionbuttons;
		$this->eximportview = $eximportview;
		$this->importcustomscriptsbuilder = $importcustomscriptsbuilder;
		$this->listcolumnnumber = $listcolumnnumber;
		$this->displaymethod = $displaymethod;
		$this->batchoptions = $batchoptions;
		$this->eximportmethod = $eximportmethod;
		$this->viewbody = $viewbody;
		$this->eximportbuttons = $eximportbuttons;
		$this->filterlistset = $filterlistset;
		$this->filterset = $filterset;
		$this->filterfieldhelper = $filterfieldhelper;
		$this->sidebarfilters = $sidebarfilters;
		$this->filterfields = $filterfields;
		$this->itemsstringfix = $itemsstringfix;
		$this->itemsmethod = $itemsmethod;
		$this->importcustomscripts = $importcustomscripts;
		$this->cando = $cando;
		$this->listbody = $listbody;
		$this->listhead = $listhead;
		$this->listquery = $listquery;
		$this->populatestate = $populatestate;
		$this->siterouter = $siterouter;
		$this->selectiontranslation = $selectiontranslation;
		$this->selectiontranslationmethod = $selectiontranslationmethod;
		$this->sortfields = $sortfields;
		$this->storedid = $storedid;
		$this->apigetmodel = $apigetmodel;
		$this->apidisplaylist = $apidisplaylist;
		$this->apifields = $apifields;
		$this->apifieldpermissions = $apifieldpermissions;
		$this->apiprepareitem = $apiprepareitem;
		$this->apirelationships = $apirelationships;
	}

	/**
	 * Build the list view of one admin view.
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
		// set the views names
		if (isset($view['settings']->name_list)
			&& $view['settings']->name_list != 'null')
		{
			$this->config->lang_target = 'admin';
			// ensure the language strings array also added to the site view
			if (isset($view['edit_create_site_view'])
				&& is_numeric($view['edit_create_site_view'])
				&& $view['edit_create_site_view'] > 0)
			{
				$this->config->lang_target = 'both';
			}

			// ICOMOON <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|ICOMOON', $view['icomoon']);

			// Trigger Event: jcb_ce_onBeforeBuildAdminListViewContent
			$this->event->trigger(
				'jcb_ce_onBeforeBuildAdminListViewContent', [&$view, &$nameSingleCode, &$nameListCode]
			);

			// set the export/import option
			$add_custom_import = (int) ($view['settings']->add_custom_import ?? 0);
			if (isset($view['port']) && $view['port']
				|| 1 === $add_custom_import)
			{
				$this->eximportview->set($nameListCode, true);
				if (1 === $add_custom_import)
				{
					// this view has custom import scripting
					$this->importcustomscriptsbuilder->set(
						$nameListCode, true
					);
					// set all custom scripts
					$this->importcustomscripts->set(
						$nameListCode
					);
				}
			}
			else
			{
				$this->eximportview->set(
					$nameListCode, false
				);
			}

			// set Auto check in function
			if (isset($view['checkin']) && $view['checkin'] == 1)
			{
				// CHECKINCALL <<<DYNAMIC>>>
				$this->contentmulti->set($nameListCode . '|CHECKINCALL',
					$this->checkinnow->getCall()
				);
				// AUTOCHECKIN <<<DYNAMIC>>>
				$this->contentmulti->set($nameListCode . '|AUTOCHECKIN',
					$this->checkinnow->getMethod(
						$nameSingleCode,
						$this->config->component_code_name
					)
				);
			}
			else
			{
				// AUTOCHECKIN <<<DYNAMIC>>>
				$this->contentmulti->set($nameListCode . '|AUTOCHECKIN', '');
				// CHECKINCALL <<<DYNAMIC>>>
				$this->contentmulti->set($nameListCode . '|CHECKINCALL', '');
			}
			// admin list file contnet
			$this->contentmulti->set($nameListCode . '|ADMIN_JAVASCRIPT_FILE',
				$this->viewscript->script(
					$nameListCode, 'list_fileScript'
				)
			);
			// ADMIN_CUSTOM_BUTTONS_LIST
			$this->contentmulti->set($nameListCode . '|ADMIN_CUSTOM_BUTTONS_LIST',
				$this->custombuttons->get($view, 3, Indent::_(1)));
			$this->contentmulti->set($nameListCode . '|ADMIN_CUSTOM_FUNCTION_ONLY_BUTTONS_LIST',
				$this->onlyfunctionbuttons->get(
					$nameListCode, ''
				)
			);

			// GET_ITEMS_METHOD_STRING_FIX <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|GET_ITEMS_METHOD_STRING_FIX',
				$this->itemsstringfix->get(
					$nameSingleCode,
					$nameListCode,
					$this->contentone->get('Component')
				)
			);

			// GET_ITEMS_METHOD_AFTER_ALL <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|GET_ITEMS_METHOD_AFTER_ALL',
				$this->dispenser->get(
					'php_getitems_after_all',
					$nameSingleCode, PHP_EOL
				)
			);

			// SELECTIONTRANSLATIONFIX <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|SELECTIONTRANSLATIONFIX',
				$this->selectiontranslation->get(
					$nameListCode,
					''
				)
			);

			// SELECTIONTRANSLATIONFIXFUNC <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|SELECTIONTRANSLATIONFIXFUNC',
				$this->selectiontranslationmethod->get(
					$nameListCode
				)
			);

			// FILTER_FIELDS <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|FILTER_FIELDS',
				$this->filterfields->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// STOREDID <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|STOREDID',
				$this->storedid->get(
					$nameSingleCode, $nameListCode
				)
			);

			// POPULATESTATE <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|POPULATESTATE',
				$this->populatestate->get(
					$nameSingleCode, $nameListCode
				)
			);

			// SORTFIELDS <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|SORTFIELDS',
				$this->sortfields->get(
					$nameListCode
				)
			);

			// CATEGORY_VIEWS
			$this->contentone->add('ROUTER_CATEGORY_VIEWS',
				$this->siterouter->categoryViews(
				$nameSingleCode,
				$nameListCode
			));

			// FILTERFIELDDISPLAYHELPER <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|FILTERFIELDDISPLAYHELPER',
				$this->sidebarfilters->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// BATCHDISPLAYHELPER <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|BATCHDISPLAYHELPER',
				$this->batchoptions->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// FILTERFUNCTIONS <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|FILTERFUNCTIONS',
				$this->filterfieldhelper->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// FIELDFILTERSETS <<<DYNAMIC>>>
			$this->contentmulti->set('filter_' . $nameListCode . '|FIELDFILTERSETS',
				$this->filterset->get(
				$nameSingleCode,
				$nameListCode
			));

			// FIELDLISTSETS <<<DYNAMIC>>>
			$this->contentmulti->set('filter_' . $nameListCode . '|FIELDLISTSETS',
				$this->filterlistset->get(
				$nameSingleCode,
				$nameListCode
			));

			// LISTQUERY <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|LISTQUERY',
				$this->listquery->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// MODELEXPORTMETHOD <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|MODELEXPORTMETHOD',
				$this->itemsmethod->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// MODELEXIMPORTMETHOD <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|CONTROLLEREXIMPORTMETHOD',
				$this->eximportmethod->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// EXPORTBUTTON <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|EXPORTBUTTON',
				$this->eximportbuttons->export(
					$nameSingleCode,
					$nameListCode
				)
			);

			// IMPORTBUTTON <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|IMPORTBUTTON',
				$this->eximportbuttons->import(
					$nameSingleCode,
					$nameListCode
				)
			);

			// VIEWS_DEFAULT_BODY <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|VIEWS_DEFAULT_BODY',
				$this->viewbody->getDefault(
					$nameSingleCode,
					$nameListCode
				)
			);

			// VIEWS_MODAL_BODY <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|VIEWS_MODAL_BODY',
				$this->viewbody->getModal(
					$nameSingleCode,
					$nameListCode
				)
			);

			// LISTHEAD <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|LISTHEAD',
				$this->listhead->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// LISTBODY <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|LISTBODY',
				$this->listbody->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// LISTCOLNR <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|LISTCOLNR',
				$this->listcolumnnumber->get(
					$nameListCode
				)
			);

			// JVIEWLISTCANDO <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|JVIEWLISTCANDO',
				$this->cando->get(
					$nameSingleCode,
					$nameListCode
				)
			);

			// VIEWSCSS <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|VIEWSCSS',
				$this->dispenser->get(
					'css_views', $nameSingleCode, '',
					null, true
				)
			);

			// ADMIN_DIPLAY_METHOD <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|ADMIN_DIPLAY_METHOD',
				$this->displaymethod->get(
					$nameListCode
				)
			);

			// VIEWS_FOOTER_SCRIPT <<<DYNAMIC>>>
			$scriptNote = PHP_EOL . '//' . Line::_(__Line__, __Class__)
				. ' ' . $nameListCode
				. ' footer script';
			if (($footerScript = $this->dispenser->get(
					'views_footer', $nameSingleCode, '',
					$scriptNote, true,
					false, PHP_EOL
				)) !== false
				&& StringHelper::check($footerScript))
			{
				// only minfy if no php is added to the footer script
				if ($this->config->get('minify', 0)
					&& strpos((string) $footerScript, '<?php') === false)
				{
					// minify the script
					$footerScript = Minify::js($footerScript);
				}
				$this->contentmulti->set($nameListCode . '|VIEWS_FOOTER_SCRIPT',
					$this->getFooterScript($footerScript, $nameListCode));
				// clear some memory
				unset($footerScript);
			}
			else
			{
				$this->contentmulti->set($nameListCode . '|VIEWS_FOOTER_SCRIPT', '');
			}

			// ADMIN_VIEWS_CONTROLLER_HEADER <<<DYNAMIC>>> add the header details for the controller
			$this->contentmulti->set($nameListCode . '|ADMIN_VIEWS_CONTROLLER_HEADER',
				$this->header->get(
					'admin.views.controller',
					$nameListCode
				)
			);
			// ADMIN_VIEWS_MODEL_HEADER <<<DYNAMIC>>> add the header details for the model
			$this->contentmulti->set($nameListCode . '|ADMIN_VIEWS_MODEL_HEADER',
				$this->header->get(
					'admin.views.model', $nameListCode
				)
			);
			// ADMIN_VIEWS_HTML_HEADER <<<DYNAMIC>>> add the header details for the views
			$this->contentmulti->set($nameListCode . '|ADMIN_VIEWS_HTML_HEADER',
				$this->header->get(
					'admin.views.html', $nameListCode
				)
			);
			// ADMIN_VIEWS_HEADER <<<DYNAMIC>>> add the header details for the views
			$this->contentmulti->set($nameListCode . '|ADMIN_VIEWS_HEADER',
				$this->header->get(
					'admin.views', $nameListCode
				)
			);

			// ADMIN_VIEWS_MODAL_HEADER <<<DYNAMIC>>> add the header details for the views
			$this->contentmulti->set($nameListCode . '|ADMIN_VIEWS_MODAL_HEADER',
				$this->header->get(
					'admin.views.modal', $nameListCode
				)
			);

			// API_VIEWS_CONTROLLER_HEADER <<<DYNAMIC>>> add the header details for the controller
			$this->contentmulti->set($nameListCode . '|API_VIEWS_CONTROLLER_HEADER',
				$this->header->get(
					'api.views.controller', $nameListCode
				)
			);

			// API_VIEWS_JSON_HEADER <<<DYNAMIC>>> add the header details for the controller
			$this->contentmulti->set($nameListCode . '|API_VIEWS_JSON_HEADER',
				$this->header->get(
					'api.views.json', $nameListCode
				)
			);

			// API_VIEWS_CONTROLLER_GETMODEL <<<DYNAMIC>>> add the explicit model mapping to the api controller
			$this->contentmulti->set($nameListCode . '|API_VIEWS_CONTROLLER_GETMODEL',
				$this->apigetmodel->get(
					$nameSingleCode, $nameListCode, true
				)
			);

			// API_VIEWS_CONTROLLER_DISPLAYLIST <<<DYNAMIC>>> add the list state mapping to the api controller
			$this->contentmulti->set($nameListCode . '|API_VIEWS_CONTROLLER_DISPLAYLIST',
				$this->apidisplaylist->get(
					$nameSingleCode, $nameListCode
				)
			);

			// API_VIEWS_JSON_FIELDS <<<DYNAMIC>>> add the fields to render to the api json view
			$this->contentmulti->set($nameListCode . '|API_VIEWS_JSON_FIELDS',
				$this->apifields->get(
					$nameSingleCode
				)
			);

			// API_VIEWS_JSON_PERMISSIONS <<<DYNAMIC>>> add the field permission guards to the api json view
			$this->contentmulti->set($nameListCode . '|API_VIEWS_JSON_PERMISSIONS',
				$this->apifieldpermissions->get(
					$nameSingleCode, false
				)
			);

			// API_VIEWS_JSON_PREPAREITEM <<<DYNAMIC>>> add the prepare item code to the api json view
			$this->contentmulti->set($nameListCode . '|API_VIEWS_JSON_PREPAREITEM',
				$this->apiprepareitem->get(
					$nameSingleCode, true
				)
			);

			// API_VIEWS_JSON_RELATIONSHIP <<<DYNAMIC>>> add the relationships to the api json view
			$this->contentmulti->set($nameListCode . '|API_VIEWS_JSON_RELATIONSHIP',
				$this->apirelationships->get(
					$nameSingleCode, $nameListCode, false
				)
			);

			// JQUERY <<<DYNAMIC>>>
			$this->contentmulti->set($nameListCode . '|JQUERY',
				$this->jquery->get(
					$nameSingleCode
				)
			);

			// Trigger Event: jcb_ce_onAfterBuildAdminListViewContent
			$this->event->trigger(
				'jcb_ce_onAfterBuildAdminListViewContent', [&$view, &$nameSingleCode, &$nameListCode]
			);
		}
	}

	/**
	 * Wrap the custom footer script for the generated list view.
	 *
	 * @param   string  $script  The JavaScript body, possibly containing PHP.
	 * @param   string  $name    The list view code name.
	 *
	 * @return  string  The generated template fragment.
	 * @since   6.2.0
	 */
	protected function getFooterScript(string $script, string $name): string
	{
		return PHP_EOL . '<script type="text/javascript">' . $script . '</script>';
	}
}

