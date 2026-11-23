<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate');
Html::_('bootstrap.tooltip');
use Joomla\CMS\Session\Session;

// No direct access to this file
defined('_JEXEC') or die;

//keep main menu visible
$this->app->getInput()->set('hidemainmenu', false);

// the ajax gateway every extrusion call travels through
$urlAjax = 'index.php?option=com_componentbuilder&format=json&raw=true&' . Session::getFormToken() . '=1&task=ajax.';

?>
<?php if ($this->canDo->get('extrusion.access')): ?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'extrusion.back') {
			parent.history.back();
			return false;
		} else {
			var form = document.getElementById('adminForm');
			form.task.value = task;
			form.submit();
		}
	}
</script>

<div class="main-card p-md-3" id="extrusion-page">

	<ul class="nav nav-tabs" id="extrusion-tabs">
		<li class="nav-item">
			<button type="button" class="nav-link active" id="extrusion-tab-setup" data-extrusion-tab="setup">
				<span class="icon-cog" aria-hidden="true"></span>
				<?php echo Text::_('COM_COMPONENTBUILDER_SETUP'); ?>
			</button>
		</li>
		<li class="nav-item">
			<button type="button" class="nav-link" id="extrusion-tab-pairing" data-extrusion-tab="pairing" disabled>
				<span class="icon-shuffle" aria-hidden="true"></span>
				<?php echo Text::_('COM_COMPONENTBUILDER_PAIRING'); ?>
			</button>
		</li>
		<li class="nav-item">
			<button type="button" class="nav-link" id="extrusion-tab-results" data-extrusion-tab="results" disabled>
				<span class="icon-list" aria-hidden="true"></span>
				<?php echo Text::_('COM_COMPONENTBUILDER_RESULTS'); ?>
			</button>
		</li>
	</ul>

	<div id="extrusion-pane-setup" class="extrusion-pane" data-extrusion-pane="setup">
		<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=extrusion'); ?>"
			method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
			<div class="row">
				<div class="col-md-5 p-md-3">
					<h3><?php echo Text::_('COM_COMPONENTBUILDER_PULL_AN_EXISTING_EXTENSION_INTO_JCB'); ?></h3>
					<p><?php echo Text::_('COM_COMPONENTBUILDER_SELECT_THE_FOLDERS_OF_A_JOOMLA_COMPONENT_OR_ANY_LIBRARY_OF_PHP_CLASSES_STRAIGHT_FROM_THIS_SITE_THE_TOOL_DISCOVERS_EVERYTHING_INSIDE_THEM_ON_ITS_OWN_INCLUDING_THE_INSTALL_SQL_SHOWS_YOU_EXACTLY_WHAT_IT_FOUND_AND_LETS_YOU_DECIDE_ITEM_BY_ITEM_WHAT_BECOMES_NEW_WHAT_UPDATES_SOMETHING_YOU_ALREADY_HAVE_AND_WHAT_STAYS_OUT'); ?></p>
					<?php if ($this->form): ?>
						<?php echo $this->form->renderFieldset('source'); ?>
					<?php endif; ?>
					<button type="button" class="btn btn-primary btn-lg px-4" style="width: 100%;" id="extrusion-harvest-button">
						<span class="icon-search icon-white" aria-hidden="true"></span>
						<?php echo Text::_('COM_COMPONENTBUILDER_HARVEST_THE_SOURCE'); ?>
					</button>
					<div id="extrusion-setup-notice" class="alert alert-danger mt-2" style="display:none;"></div>
				</div>
				<div class="col-md-7 p-md-3">
					<div class="accordion" id="extrusion-switches">
						<div class="accordion-item">
							<h2 class="accordion-header">
								<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#extrusion-switches-body">
									<?php echo Text::_('COM_COMPONENTBUILDER_WHAT_SHOULD_BE_HARVESTED'); ?>
								</button>
							</h2>
							<div id="extrusion-switches-body" class="accordion-collapse collapse show" data-bs-parent="#extrusion-switches">
								<div class="accordion-body">
									<?php if ($this->form): ?>
										<?php echo $this->form->renderFieldset('switches'); ?>
										<?php echo $this->form->renderFieldset('advanced'); ?>
									<?php endif; ?>
									<div id="extrusion-namespace-repair-options" hidden>
										<button type="button" class="btn btn-outline-primary" id="extrusion-repair-namespaces-button"
											aria-describedby="extrusion-namespace-repair-description">
											<span class="icon-refresh" aria-hidden="true"></span>
											<?php echo Text::_('COM_COMPONENTBUILDER_REPAIR_EXISTING_POWER_NAMESPACES'); ?>
										</button>
										<p id="extrusion-namespace-repair-description" class="mt-2">
											<?php echo Text::_('COM_COMPONENTBUILDER_SELECT_AN_EXISTING_TARGET_COMPONENT_IN_UPDATE_MODE_AND_ITS_LIBRARY_SOURCE_FOLDERS_REVIEW_AND_REPAIR_NAMESPACE_PLACEHOLDERS_FOR_MATCHED_POWERS_ONLY_THEIR_GUIDS_CODE_SETTINGS_AND_LINKS_ARE_RETAINED'); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="p-md-3"><?php if ($this->dankie == 2): ?>
					<?php echo LayoutHelper::render('jcbsupportmessage', []); ?><?php else: ?>
					<?php echo ComponentbuilderHelper::getDynamicContent('banner', '728-90'); ?><?php endif; ?>
				</div>
			</div>
			<input type="hidden" name="task" value="" />
			<?php echo Html::_('form.token'); ?>
		</form>
	</div>

	<div id="extrusion-pane-running" class="extrusion-pane" data-extrusion-pane="running" style="display:none;">
		<div class="row">
			<div class="col-md-4 p-md-3">
				<h3><?php echo $this->escape($this->user->name); ?>, <?php echo Text::_('COM_COMPONENTBUILDER_PLEASE_WAIT'); ?></h3>
				<p><b><span id="extrusion-running-title"><?php echo Text::_('COM_COMPONENTBUILDER_THE_SOURCE'); ?></span></b>
					<span id="extrusion-running-verb"><?php echo Text::_('COM_COMPONENTBUILDER_IS_BEING_HARVESTED'); ?></span>
					<span class="loading-dots">.</span></p>
				<p style="font-size: smaller;"><?php echo Text::_('COM_COMPONENTBUILDER_A_LARGE_SOURCE_CAN_CARRY_HUNDREDS_OF_CLASSES_AND_VIEWS_SO_THIS_MAY_TAKE_A_MOMENT'); ?></p>
			</div>
		</div>
		<div class="col-md-8 p-md-3">
			<div class="p-md-3"><?php if ($this->dankie == 2): ?>
				<?php echo LayoutHelper::render('jcbsupportmessage', []); ?><?php else: ?>
				<?php echo ComponentbuilderHelper::getDynamicContent('banner', '728-90'); ?><?php endif; ?>
			</div>
		</div>
	</div>

	<div id="extrusion-pane-pairing" class="extrusion-pane" data-extrusion-pane="pairing" style="display:none;">
		<div class="row p-md-3">
			<div class="col-md-8">
				<h3 id="extrusion-pairing-title"><?php echo Text::_('COM_COMPONENTBUILDER_PAIR_THE_HARVEST_WITH_WHAT_YOU_ALREADY_HAVE'); ?></h3>
				<p><?php echo Text::_('COM_COMPONENTBUILDER_EVERYTHING_BELOW_WAS_FOUND_IN_THE_SOURCE_PROPOSALS_IDENTIFY_THE_ACTUAL_TARGET_RECORDS_AMBIGUOUS_OR_CONFLICTING_POWERS_MUST_BE_RESOLVED_BEFORE_IMPORT_CHANGE_ANY_DECISION_NOTHING_IS_WRITTEN_UNTIL_YOU_APPROVE_THE_CURRENT_PLAN'); ?></p>
				<p id="extrusion-namespace-repair-notice" class="alert alert-info" role="status" hidden>
					<?php echo Text::_('COM_COMPONENTBUILDER_NAMESPACE_REPAIR_ONLY_THE_NAMESPACES_OF_EXISTING_MATCHED_POWERS_CAN_CHANGE_REVIEW_THE_PROPOSED_NAMESPACE_DIFFERENCES_UNMATCHED_CLASSES_ARE_SKIPPED'); ?>
				</p>
			</div>
			<div class="col-md-4" style="text-align: right;">
				<label for="extrusion-component-select" style="display:block;"><?php echo Text::_('COM_COMPONENTBUILDER_TARGET_COMPONENT'); ?></label>
				<select id="extrusion-component-select" class="form-select" style="display:inline-block; max-width: 100%;"></select>
			</div>
		</div>
		<div id="extrusion-bulk-bar" class="p-md-2">
			<div class="extrusion-filters">
				<div class="extrusion-filter-field">
					<label for="extrusion-filter-type"><?php echo Text::_('COM_COMPONENTBUILDER_ENTITY_TYPE'); ?></label>
					<select id="extrusion-filter-type" class="form-select form-select-sm">
						<option value=""><?php echo Text::_('COM_COMPONENTBUILDER_ALL_TYPES'); ?></option>
						<option value="power"><?php echo Text::_('COM_COMPONENTBUILDER_POWERS'); ?></option>
						<option value="admin_view"><?php echo Text::_('COM_COMPONENTBUILDER_ADMIN_VIEWS'); ?></option>
						<option value="field"><?php echo Text::_('COM_COMPONENTBUILDER_FIELDS'); ?></option>
						<option value="site_view"><?php echo Text::_('COM_COMPONENTBUILDER_SITE_VIEWS'); ?></option>
						<option value="custom_admin_view"><?php echo Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEWS'); ?></option>
						<option value="layout"><?php echo Text::_('COM_COMPONENTBUILDER_LAYOUTS'); ?></option>
						<option value="template"><?php echo Text::_('COM_COMPONENTBUILDER_TEMPLATES'); ?></option>
					</select>
				</div>
				<div class="extrusion-filter-field">
					<label for="extrusion-filter-status"><?php echo Text::_('COM_COMPONENTBUILDER_MATCHING_STATUS'); ?></label>
					<select id="extrusion-filter-status" class="form-select form-select-sm">
						<option value=""><?php echo Text::_('COM_COMPONENTBUILDER_ALL_STATUSES'); ?></option>
						<option value="matched"><?php echo Text::_('COM_COMPONENTBUILDER_MATCHED'); ?></option>
						<option value="ambiguous"><?php echo Text::_('COM_COMPONENTBUILDER_AMBIGUOUS'); ?></option>
						<option value="conflict"><?php echo Text::_('COM_COMPONENTBUILDER_CONFLICT'); ?></option>
						<option value="unresolved"><?php echo Text::_('COM_COMPONENTBUILDER_UNRESOLVED'); ?></option>
						<option value="unmatched"><?php echo Text::_('COM_COMPONENTBUILDER_UNMATCHED'); ?></option>
						<option value="new"><?php echo Text::_('COM_COMPONENTBUILDER_NEW'); ?></option>
						<option value="similar"><?php echo Text::_('COM_COMPONENTBUILDER_SIMILAR'); ?></option>
						<option value="shared"><?php echo Text::_('COM_COMPONENTBUILDER_SHARED'); ?></option>
						<option value="ignored"><?php echo Text::_('COM_COMPONENTBUILDER_IGNORED'); ?></option>
						<option value="filtered"><?php echo Text::_('COM_COMPONENTBUILDER_FILTERED'); ?></option>
					</select>
				</div>
				<div class="extrusion-filter-field">
					<label for="extrusion-filter-change"><?php echo Text::_('COM_COMPONENTBUILDER_PLANNED_CHANGE'); ?></label>
					<select id="extrusion-filter-change" class="form-select form-select-sm">
						<option value=""><?php echo Text::_('COM_COMPONENTBUILDER_ALL_CHANGES'); ?></option>
						<option value="create"><?php echo Text::_('COM_COMPONENTBUILDER_CREATE_NEW'); ?></option>
						<option value="update"><?php echo Text::_('COM_COMPONENTBUILDER_UPDATE'); ?></option>
						<option value="nochange"><?php echo Text::_('COM_COMPONENTBUILDER_NO_CHANGE'); ?></option>
						<option value="ignore"><?php echo Text::_('COM_COMPONENTBUILDER_IGNORE'); ?></option>
						<option value="blocked"><?php echo Text::_('COM_COMPONENTBUILDER_BLOCKED'); ?></option>
						<option value="pending"><?php echo Text::_('COM_COMPONENTBUILDER_PENDING_REVIEW'); ?></option>
					</select>
				</div>
				<div class="extrusion-filter-field">
					<label for="extrusion-filter"><?php echo Text::_('COM_COMPONENTBUILDER_SEARCH_THE_TREE'); ?></label>
					<input type="text" id="extrusion-filter" class="form-control form-control-sm"
						placeholder="<?php echo Text::_('COM_COMPONENTBUILDER_FILTER_THE_TREE'); ?>" />
				</div>
			</div>
			<div class="extrusion-bulk-actions">
			<span class="extrusion-filter-count"><span id="extrusion-visible-count">0</span> / <span id="extrusion-total-count">0</span> <?php echo Text::_('COM_COMPONENTBUILDER_ITEMS_SHOWN'); ?></span>
			<span><span id="extrusion-selected-count">0</span> <?php echo Text::_('COM_COMPONENTBUILDER_SELECTED_IN_THIS_VIEW'); ?>:</span>
			<button type="button" class="btn btn-sm btn-outline-primary" data-extrusion-bulk="create"><?php echo Text::_('COM_COMPONENTBUILDER_CREATE_NEW'); ?></button>
			<button type="button" class="btn btn-sm btn-outline-secondary" data-extrusion-bulk="ignore"><?php echo Text::_('COM_COMPONENTBUILDER_IGNORE'); ?></button>
			<button type="button" class="btn btn-sm btn-outline-secondary" data-extrusion-bulk="reset"><?php echo Text::_('COM_COMPONENTBUILDER_BACK_TO_PROPOSED'); ?></button>
			<small><?php echo Text::_('COM_COMPONENTBUILDER_GROUP_SELECTION_INCLUDES_NESTED_ITEMS_MATCHING_THESE_FILTERS'); ?></small>
			</div>
		</div>
		<div id="extrusion-ambiguity-notice" class="alert alert-warning" hidden>
			<?php echo Text::_('COM_COMPONENTBUILDER_SOME_ITEMS_HAVE_MORE_THAN_ONE_POSSIBLE_TARGET_FILTER_THEM_AND_CHOOSE_THE_CORRECT_RECORD'); ?>
			<button type="button" class="btn btn-sm btn-outline-secondary" id="extrusion-show-ambiguous"><?php echo Text::_('COM_COMPONENTBUILDER_SHOW_AMBIGUOUS_ITEMS'); ?></button>
		</div>
		<div id="extrusion-board" class="p-md-2"></div>
		<p id="extrusion-filter-empty" class="p-md-2" role="status" hidden><?php echo Text::_('COM_COMPONENTBUILDER_NO_ITEMS_MATCH_THESE_FILTERS'); ?></p>
		<div class="p-md-2">
			<div id="extrusion-review-notice" role="status" aria-live="polite"></div>
		</div>
		<div class="p-md-3">
			<button type="button" class="btn btn-success btn-lg px-4" id="extrusion-import-button" disabled>
				<span class="icon-download icon-white" aria-hidden="true"></span>
				<span id="extrusion-import-label"><?php echo Text::_('COM_COMPONENTBUILDER_IMPORT_INTO_JCB'); ?></span>
			</button>
			<button type="button" class="btn btn-outline-secondary btn-lg px-4" id="extrusion-back-button">
				<?php echo Text::_('COM_COMPONENTBUILDER_BACK_TO_SETUP'); ?>
			</button>
		</div>
	</div>

	<div id="extrusion-pane-results" class="extrusion-pane" data-extrusion-pane="results" style="display:none;">
		<div class="row p-md-3">
			<div class="col-md-12">
				<h3 id="extrusion-results-title"><?php echo Text::_('COM_COMPONENTBUILDER_THE_IMPORT_REPORT'); ?></h3>
				<div id="extrusion-results"></div>
			</div>
		</div>
	</div>

	<div id="extrusion-folder-modal" class="extrusion-modal" style="display:none;">
		<div class="extrusion-modal-card">
			<h4><?php echo Text::_('COM_COMPONENTBUILDER_SELECT_A_FOLDER'); ?></h4>
			<div id="extrusion-folder-path" class="extrusion-folder-path"></div>
			<div id="extrusion-folder-list" class="extrusion-modal-list"></div>
			<div>
				<button type="button" class="btn btn-success" id="extrusion-folder-choose"><?php echo Text::_('COM_COMPONENTBUILDER_CHOOSE_THIS_FOLDER'); ?></button>
				<button type="button" class="btn btn-outline-secondary" id="extrusion-folder-close"><?php echo Text::_('COM_COMPONENTBUILDER_CANCEL'); ?></button>
			</div>
		</div>
	</div>

	<div id="extrusion-modal" class="extrusion-modal" style="display:none;">
		<div class="extrusion-modal-card">
			<h4 id="extrusion-modal-title"><?php echo Text::_('COM_COMPONENTBUILDER_CHOOSE_THE_TARGET'); ?></h4>
			<input type="text" id="extrusion-modal-search" class="form-control"
				placeholder="<?php echo Text::_('COM_COMPONENTBUILDER_TYPE_TO_SEARCH'); ?>" autocomplete="off" aria-describedby="extrusion-power-search-hint" />
			<p id="extrusion-power-search-hint" hidden><?php echo Text::_('COM_COMPONENTBUILDER_LINKED_POWERS_ARE_SHOWN_FIRST_TO_FIND_ANOTHER_POWER_ENTER_ITS_EXACT_NAME_SYSTEM_NAME_NAMESPACE_OR_GUID'); ?></p>
			<div id="extrusion-modal-list" class="extrusion-modal-list"></div>
			<button type="button" class="btn btn-outline-secondary" id="extrusion-modal-close"><?php echo Text::_('COM_COMPONENTBUILDER_CANCEL'); ?></button>
		</div>
	</div>
	<div id="extrusion-confirm-modal" class="extrusion-modal" role="dialog" aria-modal="true"
		aria-labelledby="extrusion-confirm-title" aria-describedby="extrusion-confirm-description" style="display:none;">
		<div class="extrusion-modal-card">
			<h4 id="extrusion-confirm-title"><?php echo Text::_('COM_COMPONENTBUILDER_CONFIRM_IMPORT'); ?></h4>
			<p id="extrusion-confirm-description"><?php echo Text::_('COM_COMPONENTBUILDER_I_ACKNOWLEDGE_THAT_THESE_CHANGES_CAN_AFFECT_THE_SYSTEM'); ?></p>
			<p id="extrusion-confirm-dry-run" hidden><?php echo Text::_('COM_COMPONENTBUILDER_THIS_IS_A_DRY_RUN_NO_RECORDS_WILL_BE_WRITTEN'); ?></p>
			<div class="extrusion-confirm-actions">
				<button type="button" class="btn btn-success" id="extrusion-confirm-import"><?php echo Text::_('COM_COMPONENTBUILDER_ACKNOWLEDGE_AND_IMPORT'); ?></button>
				<button type="button" class="btn btn-outline-secondary" id="extrusion-confirm-cancel"><?php echo Text::_('COM_COMPONENTBUILDER_CANCEL'); ?></button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
// the extrusion page bootstrap
window.JCBExtrusion = {
	url: '<?php echo $urlAjax; ?>',
	canImport: true,
	text: {
		pairingTitle: '<?php echo Text::_('COM_COMPONENTBUILDER_PAIR_THE_HARVEST_WITH_WHAT_YOU_ALREADY_HAVE', true); ?>',
		repairPairingTitle: '<?php echo Text::_('COM_COMPONENTBUILDER_REVIEW_EXISTING_POWER_NAMESPACE_REPAIRS', true); ?>',
		repairNeedTarget: '<?php echo Text::_('COM_COMPONENTBUILDER_NAMESPACE_REPAIR_REQUIRES_UPDATE_MODE_AND_AN_EXPLICITLY_SELECTED_EXISTING_TARGET_COMPONENT', true); ?>',
		repairNeedLibraries: '<?php echo Text::_('COM_COMPONENTBUILDER_SELECT_AT_LEAST_ONE_LIBRARY_SOURCE_FOLDER_CONTAINING_THE_CLASSES_WHOSE_POWER_NAMESPACES_SHOULD_BE_REPAIRED', true); ?>',
		repairProposal: '<?php echo Text::_('COM_COMPONENTBUILDER_PROPOSED_NAMESPACE_REPAIR', true); ?>',
		importLabel: '<?php echo Text::_('COM_COMPONENTBUILDER_IMPORT_INTO_JCB', true); ?>',
		repairLabel: '<?php echo Text::_('COM_COMPONENTBUILDER_APPLY_NAMESPACE_REPAIRS', true); ?>',
		confirmTitle: '<?php echo Text::_('COM_COMPONENTBUILDER_CONFIRM_IMPORT', true); ?>',
		repairConfirmTitle: '<?php echo Text::_('COM_COMPONENTBUILDER_CONFIRM_NAMESPACE_REPAIR', true); ?>',
		confirmDescription: '<?php echo Text::_('COM_COMPONENTBUILDER_I_ACKNOWLEDGE_THAT_THESE_CHANGES_CAN_AFFECT_THE_SYSTEM', true); ?>',
		repairConfirmDescription: '<?php echo Text::_('COM_COMPONENTBUILDER_I_ACKNOWLEDGE_THAT_CHANGING_THESE_EXISTING_POWER_NAMESPACES_CAN_AFFECT_THEIR_CONSUMERS_ONLY_THE_REVIEWED_NAMESPACE_CHANGES_WILL_BE_APPLIED', true); ?>',
		confirmLabel: '<?php echo Text::_('COM_COMPONENTBUILDER_ACKNOWLEDGE_AND_IMPORT', true); ?>',
		repairConfirmLabel: '<?php echo Text::_('COM_COMPONENTBUILDER_ACKNOWLEDGE_AND_REPAIR', true); ?>',
		reportTitle: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_IMPORT_REPORT', true); ?>',
		repairReportTitle: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_NAMESPACE_REPAIR_REPORT', true); ?>',
		repairing: '<?php echo Text::_('COM_COMPONENTBUILDER_IS_HAVING_ITS_POWER_NAMESPACES_REPAIRED', true); ?>',
		reviewPending: '<?php echo Text::_('COM_COMPONENTBUILDER_RESOLVING_THE_CURRENT_TARGETS_AND_WRITE_PLAN', true); ?>',
		reviewBlocked: '<?php echo Text::_('COM_COMPONENTBUILDER_RESOLVE_THE_BLOCKED_ITEMS_BEFORE_IMPORTING', true); ?>',
		blockerDetails: '<?php echo Text::_('COM_COMPONENTBUILDER_REVIEW_CONFLICT_DETAILS', true); ?>',
		ambiguousHint: '<?php echo Text::_('COM_COMPONENTBUILDER_CHOOSE_THE_CORRECT_TARGET_FROM_THE_CANDIDATES', true); ?>',
		selectGroup: '<?php echo Text::_('COM_COMPONENTBUILDER_SELECT_GROUP', true); ?>',
		selectItem: '<?php echo Text::_('COM_COMPONENTBUILDER_SELECT_ITEM', true); ?>',
		reviewReady: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_CURRENT_TARGETS_AND_EFFECTIVE_CHANGES_HAVE_BEEN_VALIDATED', true); ?>',
		actualTarget: '<?php echo Text::_('COM_COMPONENTBUILDER_ACTUAL_TARGET', true); ?>',
		newIdentity: '<?php echo Text::_('COM_COMPONENTBUILDER_NEW_POWER_IDENTITY', true); ?>',
		otherCandidates: '<?php echo Text::_('COM_COMPONENTBUILDER_OTHER_CANDIDATES', true); ?>',
		relocation: '<?php echo Text::_('COM_COMPONENTBUILDER_VALIDATED_NAMESPACE_RELOCATION', true); ?>',
		skippedExisting: '<?php echo Text::_('COM_COMPONENTBUILDER_SKIPPED_EXISTING_AVAILABLE_TO_DEPENDENCIES', true); ?>',
		unresolved: '<?php echo Text::_('COM_COMPONENTBUILDER_UNRESOLVED', true); ?>',
		status_matched: '<?php echo Text::_('COM_COMPONENTBUILDER_MATCHED', true); ?>',
		status_new: '<?php echo Text::_('COM_COMPONENTBUILDER_NEW', true); ?>',
		status_ambiguous: '<?php echo Text::_('COM_COMPONENTBUILDER_AMBIGUOUS', true); ?>',
		status_conflict: '<?php echo Text::_('COM_COMPONENTBUILDER_CONFLICT', true); ?>',
		status_unresolved: '<?php echo Text::_('COM_COMPONENTBUILDER_UNRESOLVED', true); ?>',
		status_ignored: '<?php echo Text::_('COM_COMPONENTBUILDER_IGNORED', true); ?>',
		status_filtered: '<?php echo Text::_('COM_COMPONENTBUILDER_FILTERED', true); ?>',
		status_unmatched: '<?php echo Text::_('COM_COMPONENTBUILDER_UNMATCHED', true); ?>',
		status_similar: '<?php echo Text::_('COM_COMPONENTBUILDER_SIMILAR', true); ?>',
		status_shared: '<?php echo Text::_('COM_COMPONENTBUILDER_SHARED', true); ?>',
		harvesting: '<?php echo Text::_('COM_COMPONENTBUILDER_IS_BEING_HARVESTED', true); ?>',
		importing: '<?php echo Text::_('COM_COMPONENTBUILDER_IS_BEING_IMPORTED', true); ?>',
		theSource: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_SOURCE', true); ?>',
		harvestFailed: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_HARVEST_FAILED', true); ?>',
		importFailed: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_IMPORT_FAILED', true); ?>',
		requestFailed: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_REQUEST_FAILED_REVIEW_THE_CURRENT_STATE_BEFORE_TRYING_AGAIN', true); ?>',
		networkFailed: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_CONNECTION_FAILED_BEFORE_A_RESPONSE_WAS_RECEIVED_CHECK_THE_CONNECTION_AND_REVIEW_THE_CURRENT_STATE_BEFORE_TRYING_AGAIN', true); ?>',
		httpFailed: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_SERVER_RETURNED_AN_UNSUCCESSFUL_RESPONSE', true); ?>',
		invalidResponse: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_SERVER_RESPONSE_WAS_NOT_VALID_JSON_FOR_THIS_OPERATION', true); ?>',
		operationFailed: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_SERVER_COULD_NOT_COMPLETE_THIS_OPERATION', true); ?>',
		failureReference: '<?php echo Text::_('COM_COMPONENTBUILDER_FAILURE_REFERENCE', true); ?>',
		powerSearchTruncated: '<?php echo Text::_('COM_COMPONENTBUILDER_SHOWING_THE_FIRST_ONE_HUNDRED_MATCHES_REFINE_THE_SEARCH_WITH_AN_EXACT_NAMESPACE_OR_GUID', true); ?>',
		needSource: '<?php echo Text::_('COM_COMPONENTBUILDER_SELECT_AT_LEAST_AN_ADMIN_FOLDER_A_SITE_FOLDER_OR_A_LIBRARY_FOLDER_TO_HARVEST', true); ?>',
		createNew: '<?php echo Text::_('COM_COMPONENTBUILDER_CREATE_NEW', true); ?>',
		update: '<?php echo Text::_('COM_COMPONENTBUILDER_UPDATE', true); ?>',
		ignore: '<?php echo Text::_('COM_COMPONENTBUILDER_IGNORE', true); ?>',
		proposed: '<?php echo Text::_('COM_COMPONENTBUILDER_PROPOSED', true); ?>',
		detected: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_SOURCE_WAS_RECOGNISED_AS', true); ?>',
		noTarget: '<?php echo Text::_('COM_COMPONENTBUILDER_NO_TARGET_COMPONENT', true); ?>',
		chooseTarget: '<?php echo Text::_('COM_COMPONENTBUILDER_CHOOSE_THE_TARGET', true); ?>',
		noMatches: '<?php echo Text::_('COM_COMPONENTBUILDER_NOTHING_MATCHES_YOUR_SEARCH', true); ?>',
		adminViews: '<?php echo Text::_('COM_COMPONENTBUILDER_ADMIN_VIEWS', true); ?>',
		fields: '<?php echo Text::_('COM_COMPONENTBUILDER_FIELDS', true); ?>',
		siteViews: '<?php echo Text::_('COM_COMPONENTBUILDER_SITE_VIEWS', true); ?>',
		customAdminViews: '<?php echo Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEWS', true); ?>',
		layouts: '<?php echo Text::_('COM_COMPONENTBUILDER_LAYOUTS', true); ?>',
		templates: '<?php echo Text::_('COM_COMPONENTBUILDER_TEMPLATES', true); ?>',
		powers: '<?php echo Text::_('COM_COMPONENTBUILDER_POWERS', true); ?>',
		matched: '<?php echo Text::_('COM_COMPONENTBUILDER_MATCHED', true); ?>',
		similar: '<?php echo Text::_('COM_COMPONENTBUILDER_SIMILAR', true); ?>',
		newItem: '<?php echo Text::_('COM_COMPONENTBUILDER_NEW', true); ?>',
		items: '<?php echo Text::_('COM_COMPONENTBUILDER_ITEMS', true); ?>',
		written: '<?php echo Text::_('COM_COMPONENTBUILDER_WRITTEN', true); ?>',
		skipped: '<?php echo Text::_('COM_COMPONENTBUILDER_SKIPPED', true); ?>',
		failed: '<?php echo Text::_('COM_COMPONENTBUILDER_FAILED', true); ?>',
		dryRun: '<?php echo Text::_('COM_COMPONENTBUILDER_THIS_WAS_A_DRY_RUN_NOTHING_WAS_WRITTEN', true); ?>',
		importDone: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_IMPORT_HAS_RUN', true); ?>',
		harvestAgain: '<?php echo Text::_('COM_COMPONENTBUILDER_HARVEST_AGAIN', true); ?>',
		messages: '<?php echo Text::_('COM_COMPONENTBUILDER_MESSAGES', true); ?>',
		report: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_FULL_REPORT', true); ?>',
		selectFolder: '<?php echo Text::_('COM_COMPONENTBUILDER_SELECT', true); ?>',
		addLibrary: '<?php echo Text::_('COM_COMPONENTBUILDER_ADD_A_LIBRARY_FOLDER', true); ?>',
		siteRoot: '<?php echo Text::_('COM_COMPONENTBUILDER_SITE_ROOT', true); ?>',
		upOneFolder: '<?php echo Text::_('COM_COMPONENTBUILDER_UP_ONE_FOLDER', true); ?>',
		emptyFolder: '<?php echo Text::_('COM_COMPONENTBUILDER_THIS_FOLDER_HOLDS_NO_FOLDERS', true); ?>',
		folderFailed: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_FOLDER_LIST_COULD_NOT_BE_LOADED', true); ?>',
		catalogueFailed: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_EXISTING_DEFINITIONS_COULD_NOT_BE_LOADED_SO_NOTHING_COULD_BE_MATCHED_AGAINST_THIS_COMPONENT', true); ?>',
		shared: '<?php echo Text::_('COM_COMPONENTBUILDER_SHARED', true); ?>',
		sharedWith: '<?php echo Text::_('COM_COMPONENTBUILDER_ONE_FIELD_OWNED_BY', true); ?>',
		detach: '<?php echo Text::_('COM_COMPONENTBUILDER_DETACH', true); ?>',
		detachHint: '<?php echo Text::_('COM_COMPONENTBUILDER_DECIDE_THIS_VIEW_ON_ITS_OWN_INSTEAD_OF_SHARING_THE_FIELD', true); ?>',
		detached: '<?php echo Text::_('COM_COMPONENTBUILDER_DETACHED', true); ?>',
		oneField: '<?php echo Text::_('COM_COMPONENTBUILDER_ONE_FIELD_LINKED_BY', true); ?>',
		views: '<?php echo Text::_('COM_COMPONENTBUILDER_VIEWS', true); ?>',
		sharedSection: '<?php echo Text::_('COM_COMPONENTBUILDER_SHARED', true); ?>',
		adopted: '<?php echo Text::_('COM_COMPONENTBUILDER_ADOPTED', true); ?>',
		consolidated: '<?php echo Text::_('COM_COMPONENTBUILDER_CONSOLIDATED', true); ?>',
		reused: '<?php echo Text::_('COM_COMPONENTBUILDER_REUSED', true); ?>',
		kept: '<?php echo Text::_('COM_COMPONENTBUILDER_KEPT', true); ?>',
		noChange: '<?php echo Text::_('COM_COMPONENTBUILDER_NO_CHANGE', true); ?>',
		noChangeHint: '<?php echo Text::_('COM_COMPONENTBUILDER_THIS_RECORD_ALREADY_SAYS_WHAT_THE_SOURCE_SAYS_SO_THE_IMPORT_LEAVES_IT_ALONE_ITS_VIEW_IS_STILL_WIRED_UP_AS_IT_SHOULD_BE', true); ?>',
		diffHint: '<?php echo Text::_('COM_COMPONENTBUILDER_SEE_EXACTLY_WHAT_THIS_IMPORT_WOULD_CHANGE_HERE', true); ?>',
		diffLoading: '<?php echo Text::_('COM_COMPONENTBUILDER_READING_WHAT_WOULD_CHANGE', true); ?>',
		weighing: '<?php echo Text::_('COM_COMPONENTBUILDER_WEIGHING', true); ?>',
		weighingHint: '<?php echo Text::_('COM_COMPONENTBUILDER_THIS_ROW_WAS_DECIDED_A_MOMENT_AGO_SO_WHAT_IT_WOULD_CHANGE_IS_BEING_READ_AGAIN_UNDER_THE_PAIRING_IT_HAS_NOW', true); ?>',
		weighingFailed: '<?php echo Text::_('COM_COMPONENTBUILDER_THE_BOARD_COULD_NOT_BE_WEIGHED_AGAIN_UNDER_ITS_DECISIONS_OPEN_A_ROW_TO_READ_WHAT_IT_WOULD_CHANGE_NOW', true); ?>',
		diffCreates: '<?php echo Text::_('COM_COMPONENTBUILDER_WOULD_BE_CREATED', true); ?>',
		diffUpdates: '<?php echo Text::_('COM_COMPONENTBUILDER_WOULD_BE_UPDATED', true); ?>'
	}
};
</script>
<?php else: ?>
		<h1><?php echo Text::_('COM_COMPONENTBUILDER_NO_ACCESS_GRANTED'); ?></h1>
<?php endif; ?>
