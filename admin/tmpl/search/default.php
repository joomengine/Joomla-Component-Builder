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
use VDM\Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Uri\Uri;

// No direct access to this file
defined('_JEXEC') or die;

// allow main menu selection
$this->app->getInput()->set('hidemainmenu', false);

// set the basu URL
$url_base = Uri::base() . 'index.php?option=com_componentbuilder';
$url_search = $url_base . '&view=search';

// get main search input field
$search_value = $this->form->getField('search_value');

?>
<?php if ($this->canDo->get('search.access')): ?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'search.back') {
			parent.history.back();
			return false;
		} else {
			var form = document.getElementById('adminForm');
			form.task.value = task;
			form.submit();
		}
	}
</script>
<?php $urlId = (isset($this->item->id)) ? '&id='. (int) $this->item->id : ''; ?>

<div class="main-card p-md-3">
	<?php if ($this->form): ?>
	<form action="<?php echo Route::_($url_search); ?>" method="post"
		name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
		<div id="search-notification-area" class="search-notification-area" aria-live="polite" aria-atomic="true"></div>
		<div class="row">
			<div class="col-12">
				<div class="row mb-3" id="search_progress_block" style="display: none;">
					<div class="col-12">
						<div class="progress search-progress-wrapper">
							<div
								id="search_progress_bar"
								class="progress-bar progress-bar-striped progress-bar-animated bg-success"
								role="progressbar"
								style="width: 0%;"
								aria-valuemin="0"
								aria-valuemax="100"
								aria-valuenow="0"
							>0%</div>
						</div>
					</div>
				</div>
				<div class="row mb-3" id="replace_progress_block" style="display: none;">
					<div class="col-12">
						<div class="progress search-progress-wrapper progress-small">
							<div
								id="replace_progress_bar"
								class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
								role="progressbar"
								style="width: 0%;"
								aria-valuemin="0"
								aria-valuemax="100"
								aria-valuenow="0"
							></div>
						</div>
					</div>
				</div>
				<div class="row mb-3" id="search_details_block" style="display: none;">
					<div class="col-12 d-flex flex-wrap justify-content-between align-items-start gap-2">
						<div>
							<span id="search_details">
								<span class="search_details_title"><?php echo Text::_('COM_COMPONENTBUILDER_SEARCHED_FOR'); ?></span>:
								&nbsp;[<span id="searched" class="found_code">....</span>]&nbsp;&nbsp;&nbsp;&nbsp;
							</span>
							<span id="replace_details" style="display: none;">
								<span class="search_details_title"><?php echo Text::_('COM_COMPONENTBUILDER_REPLACED_WITH'); ?></span>:
								&nbsp;[<span id="replaced" class="found_code">....</span>]
							</span>
						</div>
						<div class="btn-group" role="group" aria-label="<?php echo Text::_('COM_COMPONENTBUILDER_SEARCH_ACTIONS'); ?>">
							<button
								style="display: none;"
								type="button"
								onclick="replaceAllCheck();"
								class="update_all_block hasTooltip btn btn-danger"
								title="<?php echo Text::_('COM_COMPONENTBUILDER_UPDATE_ALL_ITEMS_FOUND_WITH_THIS_DATABASE_SEARCH_WITH_THE_REPLACE_VALUE'); ?>"
							>
								<span class="icon-database" aria-hidden="true"></span>
								<?php echo Text::_('COM_COMPONENTBUILDER_UPDATE_ALL'); ?>
							</button>
							<button
								type="button"
								onclick="showSearch();"
								class="btn btn-success"
							>
								<span class="icon-search" aria-hidden="true"></span>
								<?php echo Text::_('COM_COMPONENTBUILDER_SEARCH_DATABASE_AGAIN'); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="row" id="search_settings_block">
					<div class="col-md-7">
						<div class="my-md-2">
							<?php echo $this->form->renderField('type_search'); ?>
							<div class="btn-wrapper input-group">
								<?php echo $search_value->input; ?>
								<button
									id="start_search_button"
									onclick="startSearch(this, true);"
									type="button"
									class="btn btn-outline-success button-select hasTooltip"
									title="<?php echo Html::_('tooltipText', 'COM_COMPONENTBUILDER_START_A_SEARCH'); ?>"
									aria-label="<?php echo Text::_('COM_COMPONENTBUILDER_START_A_SEARCH'); ?>"
								>
									<span class="icon-search" aria-hidden="true"></span>
								</button>
								<button
									id="stop_search_button"
									onclick="stopSearch();"
									type="button"
									class="btn btn-danger button-select hasTooltip"
									style="display: none;"
									title="<?php echo Html::_('tooltipText', 'COM_COMPONENTBUILDER_STOP_A_SEARCH'); ?>"
									aria-label="<?php echo Text::_('COM_COMPONENTBUILDER_STOP_A_SEARCH'); ?>"
								>
									<span class="icon-stop" aria-hidden="true"></span>
								</button>
							</div>
						</div>
						<div class="my-md-2">
							<?php echo $this->form->renderField('replace_value'); ?>
						</div>
						<div class="update_all_block" style="display: none;">
							<button
								type="button"
								onclick="replaceAllCheck();"
								class="hasTooltip btn btn-danger w-100"
								title="<?php echo Text::_('COM_COMPONENTBUILDER_UPDATE_ALL_ITEMS_FOUND_WITH_THIS_DATABASE_SEARCH_WITH_THE_REPLACE_VALUE'); ?>"
							>
								<span class="icon-database" aria-hidden="true"></span>
								<?php echo Text::_('COM_COMPONENTBUILDER_UPDATE_ALL'); ?>
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<?php echo $this->form->renderFieldset('settings'); ?>
					</div>
				</div>
				<div class="row" id="search_results_block">
					<div class="col-12">
						<hr>
						<div id="search_results_table_block">
							<?php echo LayoutHelper::render('table', [
								'id' => 'search_results_table',
								'headers' => $this->table_headers,
								'items' => 7,
								'init' => false,
								'table_class' => "table table-striped",
								'table_container_class' => "overflow-x-auto"
							]); ?>
						</div>
					</div>
				</div>
				<div class="row" id="item_view_block">
					<div class="col-12">
						<div id="item_notice_block" style="display: none;">
							<hr>
							<span id="item_edit_button"></span>&nbsp;
							<?php echo Text::_('COM_COMPONENTBUILDER_TABLE'); ?>:&nbsp;<b><span id="item_table_name"></span></b>
							(<?php echo Text::_('COM_COMPONENTBUILDER_ID'); ?>:<b><span id="item_row_id"></span></b>)&nbsp;|&nbsp;
							<?php echo Text::_('COM_COMPONENTBUILDER_FIELD'); ?>:&nbsp;<b><span id="item_field_name"></span></b>
							(<?php echo Text::_('COM_COMPONENTBUILDER_LINE'); ?>:<b><span id="item_line_number"></span></b>)&nbsp;&nbsp;&nbsp;
							<button
								type="button"
								id="item_button_update"
								onclick=""
								class="hasTooltip btn btn-success"
								title="<?php echo Text::_('COM_COMPONENTBUILDER_SAVE_ALL_CHANGES_MADE_TO_THE_SELECTED_ITEM'); ?>"
							>
								<?php echo Text::_('COM_COMPONENTBUILDER_SAVE_ITEM'); ?>
							</button>
						</div>
						<hr>
						<?php echo $this->form->getInput('item_code'); ?>
					</div>
				</div>

			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo Html::_('form.token'); ?>
	</form>
	<?php endif; ?>
</div>
<?php if (isset($this->item['tables']) && ArrayHelper::check($this->item['tables'])) : ?>
<script>

// get search table values
const searchTables = <?php echo json_encode($this->item['tables']); ?>;

// the search Ajax URLs
const UrlAjax = '<?php echo $url_base; ?>&format=json&raw=true&<?php echo Session::getFormToken(); ?>=1&task=ajax.';

// the search URL
const UrlSearch = '<?php echo $url_search; ?>';

// make sure our controller is set
let controller = null;
let controller_replace = null;

// some counters
var fieldCount = 0;
var lineCount = 0;

// start search time keepers
var startSearchTime, endSearchTime;

// active edit button of row selected
var editButtonSelected;

// get search progress area
const searchProgressObject = document.getElementById("search_progress_block");
const searchProgressBarObject = document.getElementById("search_progress_bar");
const replaceProgressObject = document.getElementById("replace_progress_block");
const replaceProgressBarObject = document.getElementById("replace_progress_bar");

// get search settings area
const searchSettingsObject = document.getElementById("search_settings_block");
const searchDetailsObject = document.getElementById("search_details_block");
const searchedObject = document.getElementById("searched");

// get replace settings area
const replaceDetailsObject = document.getElementById("replace_details");
const replacedObject = document.getElementById("replaced");

// set the search mode objects
const modeObject = document.getElementById("type_search");
const typeSearchObject = document.getElementById("type_search0");
const typeReplaceObject = document.getElementById("type_search1");
const typeSearchLabelObject = document.querySelector('[for=type_search0]');
const typeReplaceLabelObject = document.querySelector('[for=type_search1]');

// search buttons
const startSearchButton = document.getElementById("start_search_button");
const stopSearchButton = document.getElementById("stop_search_button");

// set the search settings objects
const searchObject = document.getElementById("search_value");
const replaceObject = document.getElementById("replace_value");
const matchObject = document.getElementById("search_behaviour0");
const wholeObject = document.getElementById("search_behaviour1");
const regexObject = document.getElementById("search_behaviour2");
const tableObject = document.getElementById("table_name");

// Do the search on key up of search or replace input elements
searchObject.onkeyup = startSearch;

// when the made changes and there is replace value do search
modeObject.onchange = startSearch;
replaceObject.onkeyup = startSearch;

// Do the search on key up of search input elements
matchObject.onchange = startSearch;
wholeObject.onchange = startSearch;
regexObject.onchange = startSearch;
tableObject.onchange = startSearch;

// set the item notice area
const itemNoticeObject = document.getElementById("item_notice_block");
const itemEditButtonObject = document.getElementById("item_edit_button");
const itemTableNameObject = document.getElementById("item_table_name");
const itemRowIdObject = document.getElementById("item_row_id");
const itemFieldNameObject = document.getElementById("item_field_name");
const itemLineNumberObject = document.getElementById("item_line_number");

// set the update buttons
const buttonUpdateItemObject = document.getElementById("item_button_update");
const buttonUpdateAllObject = document.querySelectorAll(".update_all_block");

// Define editorObject globally
var editorObject = null;
var editorPromise = null;

/**
 * Internal function that attempts to obtain the editor instance,
 * retrying until it is found or a timeout is reached.
 *
 * @param {number} retry
 * @returns {Promise<object|null>}
 */
async function SetEditor(retry = 0) {

	// Already loaded?
	if (editorObject) {
		return editorObject;
	}

	// --- NEW API: JoomlaEditor.get('item_code') ----------------------------
	try {
		const instance = JoomlaEditor.get('item_code');
		if (instance) {
			editorObject = instance;
			return instance;
		}
	} catch (e) {
		// JoomlaEditor not ready; expected
	}

	// NEW API fallback: getActive()
	try {
		const active = JoomlaEditor.getActive();
		if (active && active.getId && active.getId() === 'item_code') {
			editorObject = active;
			return active;
		}
	} catch (e2) {
		// JoomlaEditor not ready; expected
	}

	// --- SILENT LEGACY API (NO WARNINGS) ----------------------------------
	try {
		if (
			typeof Joomla !== "undefined" &&
			Joomla.editors &&
			Joomla.editors.instances
		) {
			// Bypass the proxy "get" trap — SILENT MODE
			const raw = Reflect.get(
				Joomla.editors.instances,
				'item_code',
				Joomla.editors.instances
			);

			if (raw) {
				editorObject = raw;
				return raw;
			}
		}
	} catch (e3) {
		// JoomlaEditor not ready; un-expected
	}

	// --- RETRY -------------------------------------------------------------
	if (retry < 20) {
		await new Promise(r => setTimeout(r, 250)); // wait for Joomla to load CodeMirror
		return SetEditor(retry + 1);
	}

	console.warn('Editor instance not found after retries.');
	return null;
}

/**
 * Retrieve the editor instance. Waits if needed.
 *
 * @returns {Promise<object|null>}
 */
async function GetEditor() {

	if (editorObject) {
		return editorObject;
	}

	if (editorPromise) {
		return editorPromise;
	}

	editorPromise = SetEditor(0);

	const result = await editorPromise;

	editorPromise = null;

	return result;
}

// configurations of the table
const tableConfigObject = {
	responsive: true,
	order: [[ 2, "asc" ]],
	select:  true,
	paging: true,
	deferRender: true,
	lengthMenu: [5, 10, 20 ,50, 80, 100, 150, 200, 500, 1000, 1500, 2000],
	pageLength: 80,
//	pagingType: "scrolling", // NOT YET
	scrollY: 170,
	columnDefs: [
		{ 'targets': [ 0 ], 'visible': false, 'searchable': false },
		{ 'targets': [ 0, 1 ], type: 'html' },
		{ responsivePriority: 1, targets: 1 },
		{ responsivePriority: 2, targets: 2 },
		{ responsivePriority: 3, targets: 3 }
	],
	columns: [
		{
			data: 'edit'
		},
		{
			data: 'code'
		},
		{
			data: 'table'
		},
		{
			data: 'field'
		},
		{
			data: 'id',
			width: "15px",
			className:  "small_column"
		},
		{
			data: 'line',
			width: "15px",
			className:  "small_column"
		}
	]
};

// set some table object
var tableSearchObject;
var tableLengthObject;
var tableActiveObject;

// The Result Table Code
document.addEventListener('DOMContentLoaded', function () {

	// init the table
	let searchResultsTable = new DataTable('#search_results_table', tableConfigObject);

	searchResultsTable.on( 'select', function ( e, dt, type, indexes ) {
		if ( type === 'row' ) {
			// get the data from the row
			let data = searchResultsTable.rows( indexes ).data();

			// get the item data
			let item_id = data[0].id;
			let item_table = data[0].table;
			let item_field = data[0].field;
			let item_line = data[0].line;

			// set the active edit button
			editButtonSelected = data[0].edit; 
			// set active row
			tableActiveObject = searchResultsTable.row( indexes );

			// get selected item
			getSelectedItem(item_table, item_id, item_field, item_line);

			// hide the search settings
			hideSearch();
		}
	});

	searchResultsTable.on( 'deselect', function ( e, dt, type, indexes ) {
		if ( type === 'row' ) {
			clearSelectedItem(false);
		}
	});

	// set the table search object
	tableSearchObject = document.getElementById("search_results_table_filter");
	tableLengthObject = document.getElementById("search_results_table_length");

	showSearch();
<?php if (strlen($this->urlvalues['search_value'] ?? '') > 0): ?>
	startSearch(null, true);
<?php endif; ?>
});
</script>
<?php endif; ?>
<?php else: ?>
		<h1><?php echo Text::_('COM_COMPONENTBUILDER_NO_ACCESS_GRANTED'); ?></h1>
<?php endif; ?>
