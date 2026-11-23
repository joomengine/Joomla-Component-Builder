/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Original display and required state are captured before conditions run.
var updateJoomlaComponentConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateJoomlaComponentConditions()
{
	if (updateJoomlaComponentConditionsBaseline === null)
	{
		updateJoomlaComponentConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_number","#jform_update_server",".note_update_server_note_ftp",".note_update_server_note_zip",".note_update_server_note_other","#jform_update_server_target","#jform_changelog_server",".note_changelog_server_note_ftp",".note_changelog_server_note_zip",".note_changelog_server_note_other","#jform_changelog_server_target","#jform_buildcompsql","#jform_dashboard",".note_dynamic_dashboard",".note_botton_component_dashboard","#jform_crowdin_account_api_key",".note_crowdin","#jform_crowdin_project_api_key","#jform_crowdin_project_identifier","#jform_crowdin_username"];
		var elements = [];
		for (var selectorIndex = 0; selectorIndex < selectors.length; selectorIndex++)
		{
			var containers = jQuery(selectors[selectorIndex]).closest('.control-group').get();
			for (var containerIndex = 0; containerIndex < containers.length; containerIndex++)
			{
				var element = containers[containerIndex];
				if (element && element.style && elements.indexOf(element) === -1)
				{
					elements.push(element);
					updateJoomlaComponentConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = ["update_server_target","changelog_server_target","buildcompsql"];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateJoomlaComponentConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateJoomlaComponentConditionsBaseline.containers.length; index++)
	{
		var baseline = updateJoomlaComponentConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateJoomlaComponentConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var emptycontributors_vvvvvvv = jQuery("#jform_emptycontributors input[type='radio']:checked").val();
	vvvvvvv(emptycontributors_vvvvvvv);

	var update_server_target_vvvvvvw = jQuery("#jform_update_server_target input[type='radio']:checked").val();
	var add_update_server_vvvvvvw = jQuery("#jform_add_update_server input[type='radio']:checked").val();
	vvvvvvw(update_server_target_vvvvvvw,add_update_server_vvvvvvw);

	var add_update_server_vvvvvvx = jQuery("#jform_add_update_server input[type='radio']:checked").val();
	var update_server_target_vvvvvvx = jQuery("#jform_update_server_target input[type='radio']:checked").val();
	vvvvvvx(add_update_server_vvvvvvx,update_server_target_vvvvvvx);

	var update_server_target_vvvvvvy = jQuery("#jform_update_server_target input[type='radio']:checked").val();
	var add_update_server_vvvvvvy = jQuery("#jform_add_update_server input[type='radio']:checked").val();
	vvvvvvy(update_server_target_vvvvvvy,add_update_server_vvvvvvy);

	var update_server_target_vvvvvwa = jQuery("#jform_update_server_target input[type='radio']:checked").val();
	var add_update_server_vvvvvwa = jQuery("#jform_add_update_server input[type='radio']:checked").val();
	vvvvvwa(update_server_target_vvvvvwa,add_update_server_vvvvvwa);

	var add_update_server_vvvvvwc = jQuery("#jform_add_update_server input[type='radio']:checked").val();
	vvvvvwc(add_update_server_vvvvvwc);

	var changelog_server_target_vvvvvwd = jQuery("#jform_changelog_server_target input[type='radio']:checked").val();
	var add_changelog_server_vvvvvwd = jQuery("#jform_add_changelog_server input[type='radio']:checked").val();
	vvvvvwd(changelog_server_target_vvvvvwd,add_changelog_server_vvvvvwd);

	var add_changelog_server_vvvvvwe = jQuery("#jform_add_changelog_server input[type='radio']:checked").val();
	var changelog_server_target_vvvvvwe = jQuery("#jform_changelog_server_target input[type='radio']:checked").val();
	vvvvvwe(add_changelog_server_vvvvvwe,changelog_server_target_vvvvvwe);

	var changelog_server_target_vvvvvwf = jQuery("#jform_changelog_server_target input[type='radio']:checked").val();
	var add_changelog_server_vvvvvwf = jQuery("#jform_add_changelog_server input[type='radio']:checked").val();
	vvvvvwf(changelog_server_target_vvvvvwf,add_changelog_server_vvvvvwf);

	var changelog_server_target_vvvvvwh = jQuery("#jform_changelog_server_target input[type='radio']:checked").val();
	var add_changelog_server_vvvvvwh = jQuery("#jform_add_changelog_server input[type='radio']:checked").val();
	vvvvvwh(changelog_server_target_vvvvvwh,add_changelog_server_vvvvvwh);

	var add_changelog_server_vvvvvwj = jQuery("#jform_add_changelog_server input[type='radio']:checked").val();
	vvvvvwj(add_changelog_server_vvvvvwj);

	var buildcomp_vvvvvwk = jQuery("#jform_buildcomp input[type='radio']:checked").val();
	vvvvvwk(buildcomp_vvvvvwk);

	var dashboard_type_vvvvvwl = jQuery("#jform_dashboard_type input[type='radio']:checked").val();
	vvvvvwl(dashboard_type_vvvvvwl);

	var dashboard_type_vvvvvwm = jQuery("#jform_dashboard_type input[type='radio']:checked").val();
	vvvvvwm(dashboard_type_vvvvvwm);

	var translation_tool_vvvvvwn = jQuery("#jform_translation_tool").val();
	vvvvvwn(translation_tool_vvvvvwn);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateJoomlaComponentConditions);

// the vvvvvvv function
function vvvvvvv(emptycontributors_vvvvvvv)
{
	// set the function logic
	if (emptycontributors_vvvvvvv == Number('1'))
	{
		jQuery('#jform_number').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_number').closest('.control-group').hide();
	}
}

// the vvvvvvw function
function vvvvvvw(update_server_target_vvvvvvw,add_update_server_vvvvvvw)
{
	// set the function logic
	if ((update_server_target_vvvvvvw == Number('1')) && (add_update_server_vvvvvvw == Number('1')))
	{
		jQuery('#jform_update_server').closest('.control-group').show();
		jQuery('.note_update_server_note_ftp').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_update_server').closest('.control-group').hide();
		jQuery('.note_update_server_note_ftp').closest('.control-group').hide();
	}
}

// the vvvvvvx function
function vvvvvvx(add_update_server_vvvvvvx,update_server_target_vvvvvvx)
{
	// set the function logic
	if ((add_update_server_vvvvvvx == Number('1')) && (update_server_target_vvvvvvx == Number('1')))
	{
		jQuery('#jform_update_server').closest('.control-group').show();
		jQuery('.note_update_server_note_ftp').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_update_server').closest('.control-group').hide();
		jQuery('.note_update_server_note_ftp').closest('.control-group').hide();
	}
}

// the vvvvvvy function
function vvvvvvy(update_server_target_vvvvvvy,add_update_server_vvvvvvy)
{
	// set the function logic
	if ((update_server_target_vvvvvvy == Number('2')) && (add_update_server_vvvvvvy == Number('1')))
	{
		jQuery('.note_update_server_note_zip').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_update_server_note_zip').closest('.control-group').hide();
	}
}

// the vvvvvwa function
function vvvvvwa(update_server_target_vvvvvwa,add_update_server_vvvvvwa)
{
	// set the function logic
	if ((update_server_target_vvvvvwa == Number('3')) && (add_update_server_vvvvvwa == Number('1')))
	{
		jQuery('.note_update_server_note_other').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_update_server_note_other').closest('.control-group').hide();
	}
}

// the vvvvvwc function
function vvvvvwc(add_update_server_vvvvvwc)
{
	// set the function logic
	if (add_update_server_vvvvvwc == Number('1'))
	{
		jQuery('#jform_update_server_target').closest('.control-group').show();
		// Add required attributes to update_server_target.
		jQuery('#jform_update_server_target').prop('required', true);
		jQuery('#jform_update_server_target').attr('aria-required', true);
		jQuery('#jform_update_server_target').addClass('required');
	}
	else
	{
		jQuery('#jform_update_server_target').closest('.control-group').hide();
		// Remove required attributes from update_server_target.
		jQuery('#jform_update_server_target').removeAttr('required');
		jQuery('#jform_update_server_target').removeAttr('aria-required');
		jQuery('#jform_update_server_target').removeClass('required');
	}
}

// the vvvvvwd function
function vvvvvwd(changelog_server_target_vvvvvwd,add_changelog_server_vvvvvwd)
{
	// set the function logic
	if ((changelog_server_target_vvvvvwd == Number('1')) && (add_changelog_server_vvvvvwd == Number('1')))
	{
		jQuery('#jform_changelog_server').closest('.control-group').show();
		jQuery('.note_changelog_server_note_ftp').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_changelog_server').closest('.control-group').hide();
		jQuery('.note_changelog_server_note_ftp').closest('.control-group').hide();
	}
}

// the vvvvvwe function
function vvvvvwe(add_changelog_server_vvvvvwe,changelog_server_target_vvvvvwe)
{
	// set the function logic
	if ((add_changelog_server_vvvvvwe == Number('1')) && (changelog_server_target_vvvvvwe == Number('1')))
	{
		jQuery('#jform_changelog_server').closest('.control-group').show();
		jQuery('.note_changelog_server_note_ftp').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_changelog_server').closest('.control-group').hide();
		jQuery('.note_changelog_server_note_ftp').closest('.control-group').hide();
	}
}

// the vvvvvwf function
function vvvvvwf(changelog_server_target_vvvvvwf,add_changelog_server_vvvvvwf)
{
	// set the function logic
	if ((changelog_server_target_vvvvvwf == Number('2')) && (add_changelog_server_vvvvvwf == Number('1')))
	{
		jQuery('.note_changelog_server_note_zip').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_changelog_server_note_zip').closest('.control-group').hide();
	}
}

// the vvvvvwh function
function vvvvvwh(changelog_server_target_vvvvvwh,add_changelog_server_vvvvvwh)
{
	// set the function logic
	if ((changelog_server_target_vvvvvwh == Number('3')) && (add_changelog_server_vvvvvwh == Number('1')))
	{
		jQuery('.note_changelog_server_note_other').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_changelog_server_note_other').closest('.control-group').hide();
	}
}

// the vvvvvwj function
function vvvvvwj(add_changelog_server_vvvvvwj)
{
	// set the function logic
	if (add_changelog_server_vvvvvwj == Number('1'))
	{
		jQuery('#jform_changelog_server_target').closest('.control-group').show();
		// Add required attributes to changelog_server_target.
		jQuery('#jform_changelog_server_target').prop('required', true);
		jQuery('#jform_changelog_server_target').attr('aria-required', true);
		jQuery('#jform_changelog_server_target').addClass('required');
	}
	else
	{
		jQuery('#jform_changelog_server_target').closest('.control-group').hide();
		// Remove required attributes from changelog_server_target.
		jQuery('#jform_changelog_server_target').removeAttr('required');
		jQuery('#jform_changelog_server_target').removeAttr('aria-required');
		jQuery('#jform_changelog_server_target').removeClass('required');
	}
}

// the vvvvvwk function
function vvvvvwk(buildcomp_vvvvvwk)
{
	// set the function logic
	if (buildcomp_vvvvvwk == Number('1'))
	{
		jQuery('#jform_buildcompsql').closest('.control-group').show();
		// Add required attributes to buildcompsql.
		jQuery('#jform_buildcompsql').prop('required', true);
		jQuery('#jform_buildcompsql').attr('aria-required', true);
		jQuery('#jform_buildcompsql').addClass('required');
	}
	else
	{
		jQuery('#jform_buildcompsql').closest('.control-group').hide();
		// Remove required attributes from buildcompsql.
		jQuery('#jform_buildcompsql').removeAttr('required');
		jQuery('#jform_buildcompsql').removeAttr('aria-required');
		jQuery('#jform_buildcompsql').removeClass('required');
	}
}

// the vvvvvwl function
function vvvvvwl(dashboard_type_vvvvvwl)
{
	// set the function logic
	if (dashboard_type_vvvvvwl == Number('2'))
	{
		jQuery('#jform_dashboard').closest('.control-group').show();
		jQuery('.note_dynamic_dashboard').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_dashboard').closest('.control-group').hide();
		jQuery('.note_dynamic_dashboard').closest('.control-group').hide();
	}
}

// the vvvvvwm function
function vvvvvwm(dashboard_type_vvvvvwm)
{
	// set the function logic
	if (dashboard_type_vvvvvwm == Number('1'))
	{
		jQuery('.note_botton_component_dashboard').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_botton_component_dashboard').closest('.control-group').hide();
	}
}

// the vvvvvwn function
function vvvvvwn(translation_tool_vvvvvwn)
{
	if (isSet(translation_tool_vvvvvwn) && translation_tool_vvvvvwn.constructor !== Array)
	{
		var temp_vvvvvwn = translation_tool_vvvvvwn;
		var translation_tool_vvvvvwn = [];
		translation_tool_vvvvvwn.push(temp_vvvvvwn);
	}
	else if (!isSet(translation_tool_vvvvvwn))
	{
		var translation_tool_vvvvvwn = [];
	}
	var translation_tool = translation_tool_vvvvvwn.some(translation_tool_vvvvvwn_SomeFunc);


	// set this function logic
	if (translation_tool)
	{
		jQuery('#jform_crowdin_account_api_key').closest('.control-group').show();
		jQuery('.note_crowdin').closest('.control-group').show();
		jQuery('#jform_crowdin_project_api_key').closest('.control-group').show();
		jQuery('#jform_crowdin_project_identifier').closest('.control-group').show();
		jQuery('#jform_crowdin_username').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_crowdin_account_api_key').closest('.control-group').hide();
		jQuery('.note_crowdin').closest('.control-group').hide();
		jQuery('#jform_crowdin_project_api_key').closest('.control-group').hide();
		jQuery('#jform_crowdin_project_identifier').closest('.control-group').hide();
		jQuery('#jform_crowdin_username').closest('.control-group').hide();
	}
}

// the vvvvvwn Some function
function translation_tool_vvvvvwn_SomeFunc(translation_tool_vvvvvwn)
{
	// set the function logic
	if (translation_tool_vvvvvwn == Number('1'))
	{
		return true;
	}
	return false;
}

// the isSet function
function isSet(val)
{
	if ((val != undefined) && (val != null) && 0 !== val.length){
		return true;
	}
	return false;
}


jQuery(document).ready(function()
{
	// check what is the dashboard switch
	var dasboard_type = jQuery("#jform_dashboard_type input[type='radio']:checked").val();
	dasboardSwitch(dasboard_type);
	// set buttons
	function setButtons1() {
		addButtonID('component_files_folders','button_component_files_folders', 1);
		addButtonID('component_site_views','button_create_edit_views', 1);
	 }
	function setButtons2() {
		addButtonID('component_updates','component_version', 1);
		addButtonID('component_mysql_tweaks','button_mysql_tweak_options', 1);
		addButtonID('component_custom_admin_views','button_create_edit_views', 1);
	 }
	function setButtons3() {
		addButtonID('component_custom_admin_menus','button_add_custom_menus', 1);
		addButtonID('component_config','button_add_config', 1);
		addButtonID('component_admin_views','button_create_edit_views', 1);
	 }

	 // use setTimeout() to execute
	 setTimeout(setButtons1, 1000);
	 setTimeout(setButtons2, 2000);
	 setTimeout(setButtons3, 3000);
	
	// now load the displays
	function setDisplays1() {
		getAjaxDisplay('component_admin_views');
	}
	function setDisplays2() {
		getAjaxDisplay('component_custom_admin_views');
	}
	function setDisplays3() {
		getAjaxDisplay('component_site_views');
	}

	 // use setTimeout() to execute
	 setTimeout(setDisplays1, 1500);
	 setTimeout(setDisplays2, 2500);
	 setTimeout(setDisplays3, 3500);

	// check and load all the customcode edit buttons
	setTimeout(getEditCustomCodeButtons, 400);

	// get crowdin detail if set
	setTimeout(getTranslationToolDetails, 600);
});

function getTranslationToolDetails(){
	// get the translation tool selection
	var tool = jQuery("#jform_translation_tool").val();
	// trigger Crowdin
	if (tool == 1) {
		// get the identifier
		var identifier = jQuery("#jform_crowdin_project_identifier").val();
		// get the key
		var key = jQuery("#jform_crowdin_project_api_key").val();
		// query server for details
		getCrowdinDetails_server(identifier, key).done(function(result) {
			if (result.error){
				jQuery('#crowdin_information_box').show();
				jQuery('#crowdin_error_box').show();
				jQuery('#crowdin_error_box').html(result.error);
				jQuery('#crowdin_success_box').hide();
			} else if(result.html) {
				jQuery('#crowdin_success_box').show();
				jQuery('#crowdin_success_box').html(result.html);
				jQuery('#crowdin_error_box').hide();
				jQuery('#crowdin_information_box').hide();
			} else {
				jQuery('#crowdin_information_box').show();
				jQuery('#crowdin_success_box').hide();
			}
		});
	}
}

function getCrowdinDetails_server(identifier, key){
	var getUrl = JRouter("index.php?option=com_componentbuilder&task=ajax.getCrowdinDetails&format=json&raw=true&vdm="+vastDevMod);
	if(token.length > 0 && identifier.length > 0 && key.length > 0){
		var request = token+'=1&identifier='+identifier+'&key='+key;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'json',
		data: request,
		jsonp: false
	});
}

function getAjaxDisplay(type){
	getAjaxDisplay_server(type).done(function(result) {
		if(result){
			jQuery('#display_'+type).html(result);
		}
		// set button
		addButtonID(type,'header_'+type+'_buttons', 2); // <-- little edit button
	});
}

function getAjaxDisplay_server(type){
	var getUrl = JRouter("index.php?option=com_componentbuilder&task=ajax.getAjaxDisplay&format=json&raw=true&vdm="+vastDevMod);
	if(token.length > 0 && type.length > 0){
		var request = token+'=1&type=' + type;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'json',
		data: request,
		jsonp: false
	});
}

function addData(result, where){
	jQuery(result).insertAfter(jQuery(where).closest('.control-group'));
}

function dasboardSwitch(value){
	// hide if default
	if (2 == value) {
		jQuery('.control-group-componentdashboard-one').hide();
	} else {
		// default behaviour
		if (jQuery('div.control-group-componentdashboard-one').length) {
			jQuery('.control-group-componentdashboard-one').show();
		} else {
			addButtonID('component_dashboard','button_component_dashboard', 1);
		}
	}
}


/**
 * Retrieve the Edit Custom Code buttons from the server.
 *
 * @param  {number} id  The record ID to load custom code buttons for.
 *
 * @return {Promise<object|null>}  Returns JSON object of buttons or null on failure.
 * @since  3.1.3
 */
async function getEditCustomCodeButtons_server(id) {
	try {
		// --- Validation ---
		if (typeof token !== 'string' || !token.trim()) {
			console.error('[getEditCustomCodeButtons_server] Missing or invalid CSRF token.');
			return null;
		}
		if (typeof id !== 'number' || id <= 0) {
			console.error('[getEditCustomCodeButtons_server] Invalid ID provided:', id);
			return null;
		}
		if (typeof return_here !== 'string' || !return_here.trim()) {
			console.warn('[getEditCustomCodeButtons_server] "return_here" not set; continuing without it.');
		}

		// --- Build URL safely ---
		const baseUrl = 'index.php';
		const params = new URLSearchParams({
			option: 'com_componentbuilder',
			task: 'ajax.getEditCustomCodeButtons',
			format: 'json',
			raw: 'true',
			[token]: '1',
			id: id,
			return_here: return_here || ''
		});
		if (typeof vastDevMod === 'string' && vastDevMod.length > 0) {
			params.append('vdm', vastDevMod);
		}

		const urlWithParams = JRouter(`${baseUrl}?${params.toString()}`);

		// --- Execute request ---
		const response = await fetch(urlWithParams, {
			method: 'GET',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			cache: 'no-store',
			credentials: 'same-origin'
		});

		// --- Handle network errors ---
		if (!response.ok) {
			console.error(`[getEditCustomCodeButtons_server] HTTP ${response.status}: ${response.statusText}`);
			return null;
		}

		// --- Parse JSON result ---
		const data = await response.json();
		return data ?? null;

	} catch (error) {
		console.error('[getEditCustomCodeButtons_server] Fetch failed:', error);
		return null;
	}
}

/**
 * Load and inject Edit Custom Code buttons into the DOM.
 *
 * @return {Promise<void>}
 * @since  3.1.3
 */
async function getEditCustomCodeButtons() {
	try {
		// --- Get record ID from the form ---
		const idField = document.querySelector('#jform_id');
		if (!idField) {
			console.error('[getEditCustomCodeButtons] #jform_id not found.');
			return;
		}

		const idValue = parseInt(idField.value, 10);
		if (isNaN(idValue) || idValue <= 0) {
			console.warn('[getEditCustomCodeButtons] Invalid or empty ID; skipping button load.');
			return;
		}

		// --- Request data from server ---
		const result = await getEditCustomCodeButtons_server(idValue);
		if (!result || typeof result !== 'object') {
			console.warn('[getEditCustomCodeButtons] No result returned or invalid format.');
			return;
		}

		// --- Inject returned button groups ---
		Object.entries(result).forEach(([field, buttons]) => {
			// Create the container div
			const div = document.createElement('div');
			div.className = 'control-group';
			div.innerHTML = `
<div class="control-label">
	<label>Add/Edit Customcode</label>
</div>
<div class="controls control-customcode-buttons-${field}"></div>
			`;

			// Find where to insert (before .control-wrapper-{field})
			const insertBeforeElement = document.querySelector(`.control-wrapper-${field}`);
			if (insertBeforeElement && insertBeforeElement.parentNode) {
				insertBeforeElement.parentNode.insertBefore(div, insertBeforeElement);
			}

			// Append buttons to the new container
			const controlsDiv = div.querySelector(`.control-customcode-buttons-${field}`);
			if (controlsDiv && typeof buttons === 'object') {
				Object.entries(buttons).forEach(([name, buttonHtml]) => {
					if (typeof buttonHtml === 'string') {
						const wrapper = document.createElement('div');
						wrapper.innerHTML = buttonHtml.trim();
						const buttonNode = wrapper.firstElementChild;
						if (buttonNode) {
							controlsDiv.appendChild(buttonNode);
						}
					}
				});
			}
		});
	} catch (error) {
		console.error('[getEditCustomCodeButtons] Error rendering buttons:', error);
	}
}

/**
 * Request a button ID from the server.
 *
 * @param  {string} type        The button type identifier.
 * @param  {number} size        The button size (must be > 0).
 * @global  {string} token       The Joomla CSRF token name.
 * @global  {string} vastDevMod  Optional developer mode flag.
 *
 * @return {Promise<object|null>}  JSON response or null on failure.
 * @since  3.1.3
 */
async function addButtonID_server(type, size) {
	try {
		// --- Validate parameters ---
		if (typeof type !== 'string' || !type.trim()) {
			console.error('[addButtonID_server] Invalid type provided:', type);
			return null;
		}
		if (typeof size !== 'number' || size <= 0) {
			console.error('[addButtonID_server] Invalid size provided:', size);
			return null;
		}
		if (typeof token !== 'string' || !token.trim()) {
			console.error('[addButtonID_server] Missing CSRF token.');
			return null;
		}

		// --- Build request URL ---
		const baseUrl = 'index.php';
		const params = new URLSearchParams({
			option: 'com_componentbuilder',
			task: 'ajax.getButtonID',
			format: 'json',
			raw: 'true',
			[token]: '1',
			type: type,
			size: size
		});
		if (vastDevMod) params.append('vdm', vastDevMod);
		const fullUrl = JRouter(`${baseUrl}?${params.toString()}`);

		// --- Perform fetch ---
		const response = await fetch(fullUrl, {
			method: 'GET',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			cache: 'no-store',
			credentials: 'same-origin'
		});

		if (!response.ok) {
			console.error(`[addButtonID_server] Server responded with ${response.status}: ${response.statusText}`);
			return null;
		}

		// --- Parse JSON result ---
		const data = await response.json();
		return data ?? null;

	} catch (error) {
		console.error('[addButtonID_server] Fetch operation failed:', error);
		return null;
	}
}

/**
 * Insert a button ID result into the DOM.
 *
 * @param  {string} type        The button type identifier.
 * @param  {string} where       The DOM element ID or selector where to inject the result.
 * @param  {number} size        The button size (default: 1).
 * @global  {string} token       The Joomla CSRF token name.
 * @global  {string} vastDevMod  Optional developer mode flag.
 *
 * @return {Promise<void>}
 * @since  3.1.3
 */
async function addButtonID(type, where, size) {
	try {
		const result = await addButtonID_server(type, size);

		if (!result) {
			console.warn('[addButtonID] No data returned.');
			return;
		}

		// Find target element
		const target =
			document.querySelector(`#${where}`) ||
			document.querySelector(`#jform_${where}`);

		if (!target) {
			console.error(`[addButtonID] Target element "${where}" not found.`);
			return;
		}

		// Insert the content
		if (size === 2) {
			target.innerHTML = result;
		} else {
			addData(result, `#jform_${where}`);
		}

	} catch (error) {
		console.error('[addButtonID] Error while inserting button ID:', error);
	}
}

/**
 * Fetches button data from the server.
 *
 * @param  {string} type        The button type identifier.
 * @param  {number} size        The button size indicator (default: 1).
 * @global  {string} token       The CSRF token key.
 * @global  {string} vastDevMod  Developer mode flag (optional).
 *
 * @return {Promise<object|null>} Returns JSON data or null on failure.
 * @since  3.1.3
 */
async function addButton_server(type, size = 1) {
	try {
		// --- Validate input ---
		if (typeof type !== 'string' || !type.trim()) {
			console.error('[addButton_server] Invalid type provided:', type);
			return null;
		}
		if (typeof token !== 'string' || !token.trim()) {
			console.error('[addButton_server] Missing CSRF token.');
			return null;
		}

		// --- Build URL and query ---
		const baseUrl = 'index.php';
		const params = new URLSearchParams({
			option: 'com_componentbuilder',
			task: 'ajax.getButton',
			format: 'json',
			raw: 'true',
			[token]: '1',
			type: type,
			size: size
		});
		if (vastDevMod) params.append('vdm', vastDevMod);

		const fullUrl = JRouter(`${baseUrl}?${params.toString()}`);

		// --- Fetch the data ---
		const response = await fetch(fullUrl, {
			method: 'GET',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			cache: 'no-store',
			credentials: 'same-origin'
		});

		if (!response.ok) {
			console.error(`[addButton_server] Server responded with ${response.status}: ${response.statusText}`);
			return null;
		}

		const data = await response.json();
		return data ?? null;
	} catch (error) {
		console.error('[addButton_server] Fetch failed:', error);
		return null;
	}
}

/**
 * Handles button rendering into the DOM.
 *
 * @param  {string} type   The button type identifier.
 * @param  {string} where  The target element ID or selector.
 * @param  {number} size   Optional button size (default: 1).
 * @global  {string} token  The CSRF token key.
 * @global  {string} vastDevMod  Developer mode flag (optional).
 *
 * @return {Promise<void>}
 * @since  3.1.3
 */
async function addButton(type, where, size) {
	const result = await addButton_server(type, size);

	if (!result) {
		console.warn('[addButton] No button data returned from server.');
		return;
	}

	const target = document.querySelector(size === 2 ? `#${where}` : `#jform_${where}`);
	if (!target) {
		console.error('[addButton] Target element not found:', where);
		return;
	}

	if (size === 2) {
		target.innerHTML = result;
	} else {
		addData(result, target);
	}
}
