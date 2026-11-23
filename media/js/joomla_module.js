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
var updateJoomlaModuleConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateJoomlaModuleConditions()
{
	if (updateJoomlaModuleConditionsBaseline === null)
	{
		updateJoomlaModuleConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_add_class_helper_header","#jform_class_helper_code-lbl","#jform_class_helper_header-lbl","#jform_update_server",".note_update_server_note_ftp",".note_update_server_note_zip",".note_update_server_note_other"];
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
					updateJoomlaModuleConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = [];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateJoomlaModuleConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateJoomlaModuleConditionsBaseline.containers.length; index++)
	{
		var baseline = updateJoomlaModuleConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateJoomlaModuleConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var add_class_helper_vvvvvwo = jQuery("#jform_add_class_helper").val();
	vvvvvwo(add_class_helper_vvvvvwo);

	var add_class_helper_header_vvvvvwp = jQuery("#jform_add_class_helper_header input[type='radio']:checked").val();
	var add_class_helper_vvvvvwp = jQuery("#jform_add_class_helper").val();
	vvvvvwp(add_class_helper_header_vvvvvwp,add_class_helper_vvvvvwp);

	var update_server_target_vvvvvwr = jQuery("#jform_update_server_target input[type='radio']:checked").val();
	var add_update_server_vvvvvwr = jQuery("#jform_add_update_server input[type='radio']:checked").val();
	vvvvvwr(update_server_target_vvvvvwr,add_update_server_vvvvvwr);

	var add_update_server_vvvvvws = jQuery("#jform_add_update_server input[type='radio']:checked").val();
	var update_server_target_vvvvvws = jQuery("#jform_update_server_target input[type='radio']:checked").val();
	vvvvvws(add_update_server_vvvvvws,update_server_target_vvvvvws);

	var update_server_target_vvvvvwt = jQuery("#jform_update_server_target input[type='radio']:checked").val();
	var add_update_server_vvvvvwt = jQuery("#jform_add_update_server input[type='radio']:checked").val();
	vvvvvwt(update_server_target_vvvvvwt,add_update_server_vvvvvwt);

	var update_server_target_vvvvvwv = jQuery("#jform_update_server_target input[type='radio']:checked").val();
	var add_update_server_vvvvvwv = jQuery("#jform_add_update_server input[type='radio']:checked").val();
	vvvvvwv(update_server_target_vvvvvwv,add_update_server_vvvvvwv);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateJoomlaModuleConditions);

// the vvvvvwo function
function vvvvvwo(add_class_helper_vvvvvwo)
{
	if (isSet(add_class_helper_vvvvvwo) && add_class_helper_vvvvvwo.constructor !== Array)
	{
		var temp_vvvvvwo = add_class_helper_vvvvvwo;
		var add_class_helper_vvvvvwo = [];
		add_class_helper_vvvvvwo.push(temp_vvvvvwo);
	}
	else if (!isSet(add_class_helper_vvvvvwo))
	{
		var add_class_helper_vvvvvwo = [];
	}
	var add_class_helper = add_class_helper_vvvvvwo.some(add_class_helper_vvvvvwo_SomeFunc);


	// set this function logic
	if (add_class_helper)
	{
		jQuery('#jform_add_class_helper_header').closest('.control-group').show();
		jQuery('#jform_class_helper_code-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_add_class_helper_header').closest('.control-group').hide();
		jQuery('#jform_class_helper_code-lbl').closest('.control-group').hide();
	}
}

// the vvvvvwo Some function
function add_class_helper_vvvvvwo_SomeFunc(add_class_helper_vvvvvwo)
{
	// set the function logic
	if (add_class_helper_vvvvvwo == Number('1') || add_class_helper_vvvvvwo == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvvwp function
function vvvvvwp(add_class_helper_header_vvvvvwp,add_class_helper_vvvvvwp)
{
	if (isSet(add_class_helper_header_vvvvvwp) && add_class_helper_header_vvvvvwp.constructor !== Array)
	{
		var temp_vvvvvwp = add_class_helper_header_vvvvvwp;
		var add_class_helper_header_vvvvvwp = [];
		add_class_helper_header_vvvvvwp.push(temp_vvvvvwp);
	}
	else if (!isSet(add_class_helper_header_vvvvvwp))
	{
		var add_class_helper_header_vvvvvwp = [];
	}
	var add_class_helper_header = add_class_helper_header_vvvvvwp.some(add_class_helper_header_vvvvvwp_SomeFunc);

	if (isSet(add_class_helper_vvvvvwp) && add_class_helper_vvvvvwp.constructor !== Array)
	{
		var temp_vvvvvwp = add_class_helper_vvvvvwp;
		var add_class_helper_vvvvvwp = [];
		add_class_helper_vvvvvwp.push(temp_vvvvvwp);
	}
	else if (!isSet(add_class_helper_vvvvvwp))
	{
		var add_class_helper_vvvvvwp = [];
	}
	var add_class_helper = add_class_helper_vvvvvwp.some(add_class_helper_vvvvvwp_SomeFunc);


	// set this function logic
	if (add_class_helper_header && add_class_helper)
	{
		jQuery('#jform_class_helper_header-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_class_helper_header-lbl').closest('.control-group').hide();
	}
}

// the vvvvvwp Some function
function add_class_helper_header_vvvvvwp_SomeFunc(add_class_helper_header_vvvvvwp)
{
	// set the function logic
	if (add_class_helper_header_vvvvvwp == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvvwp Some function
function add_class_helper_vvvvvwp_SomeFunc(add_class_helper_vvvvvwp)
{
	// set the function logic
	if (add_class_helper_vvvvvwp == Number('1') || add_class_helper_vvvvvwp == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvvwr function
function vvvvvwr(update_server_target_vvvvvwr,add_update_server_vvvvvwr)
{
	// set the function logic
	if ((update_server_target_vvvvvwr == Number('1')) && (add_update_server_vvvvvwr == Number('1')))
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

// the vvvvvws function
function vvvvvws(add_update_server_vvvvvws,update_server_target_vvvvvws)
{
	// set the function logic
	if ((add_update_server_vvvvvws == Number('1')) && (update_server_target_vvvvvws == Number('1')))
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

// the vvvvvwt function
function vvvvvwt(update_server_target_vvvvvwt,add_update_server_vvvvvwt)
{
	// set the function logic
	if ((update_server_target_vvvvvwt == Number('2')) && (add_update_server_vvvvvwt == Number('1')))
	{
		jQuery('.note_update_server_note_zip').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_update_server_note_zip').closest('.control-group').hide();
	}
}

// the vvvvvwv function
function vvvvvwv(update_server_target_vvvvvwv,add_update_server_vvvvvwv)
{
	// set the function logic
	if ((update_server_target_vvvvvwv == Number('3')) && (add_update_server_vvvvvwv == Number('1')))
	{
		jQuery('.note_update_server_note_other').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_update_server_note_other').closest('.control-group').hide();
	}
}

// the isSet function
function isSet(val)
{
	if ((val != undefined) && (val != null) && 0 !== val.length){
		return true;
	}
	return false;
}


jQuery(document).ready(function() {
	// get the linked details
	getLinked();
	// check and load all the customcode edit buttons
	setTimeout(getEditCustomCodeButtons, 300);
});

function setModuleCode() {
	var selected_get =  jQuery("#jform_add_class_helper  option:selected").val();
	var custom_gets =  jQuery("#jform_custom_get").val();
	var libraries =  jQuery("#jform_libraries").val();
	var values = {'class': selected_get, 'get': custom_gets, 'lib': libraries};
	var editor_id = 'jform_mod_code';
	getCodeFrom_server(1, JSON.stringify(values), 'data', 'getModuleCode').then(function(result) {
		if(result.tmpl){
			 addCodeToEditor(result.tmpl.code, editor_id, result.tmpl.merge, result.tmpl.merge_target);
		}
		if(result.css){
			 addCodeToEditor(result.css.code, editor_id, result.css.merge, result.css.merge_target);
		}
		if(result.class){
			 addCodeToEditor(result.class.code, editor_id, result.class.merge, result.class.merge_target);
		}
		if(result.get){
			 addCodeToEditor(result.get.code, editor_id, result.get.merge, result.get.merge_target);
		}
		if(result.lib){
			 addCodeToEditor(result.lib.code, editor_id, result.lib.merge, result.lib.merge_target);
		}
	});
}

function getLinked() {
	getCodeFrom_server(1, 'type', 'type', 'getLinked').then(function(result) {
		if (result.error) {
			console.error(result.error);
		} else if (result) {
			document.getElementById('display_linked_to').innerHTML = result;
		}
	});
}

/**
 * Fetch data from the server with validated parameters.
 *
 * @param  {number|string} id          The record ID (integer > 0 or string > 30 chars)
 * @param  {string}        type        The type value to send
 * @param  {string}        typeName    The type parameter name (e.g. "type" or "context")
 * @param  {string}        callingName The AJAX task name (e.g. "getCode")
 * @global   {string}        token       The CSRF token name or key
 * @global   {string}        vastDevMod  The developer key or mode flag (optional)
 *
 * @return {Promise<object|null>}      Returns parsed JSON data or null on failure
 * @since  3.1.2
 */
async function getCodeFrom_server(id, type, typeName, callingName) {
	try {
		// --- Validation ---
		if (!getCodeFrom_isValidId(id)) {
			console.debug('[getCodeFrom_server] Invalid ID provided:', id);
			return null;
		}
		if (typeof type !== 'string' || !type.trim()) {
			console.debug('[getCodeFrom_server] Invalid type provided:', type);
			return null;
		}
		if (typeof typeName !== 'string' || !typeName.trim()) {
			console.debug('[getCodeFrom_server] Invalid typeName provided:', typeName);
			return null;
		}
		if (typeof callingName !== 'string' || !callingName.trim()) {
			console.debug('[getCodeFrom_server] Invalid callingName provided:', callingName);
			return null;
		}
		if (typeof token !== 'string' || !token.trim()) {
			console.debug('[getCodeFrom_server] Missing security token.');
			return null;
		}

		// --- Construct URL safely ---
		const baseUrl = 'index.php';
		const params = new URLSearchParams({
			option: 'com_componentbuilder',
			task: `ajax.${callingName}`,
			format: 'json',
			raw: 'true',
			[token]: '1',
			[typeName]: type,
			id: id
		});
		if (vastDevMod) params.append('vdm', vastDevMod);

		const fullUrl = JRouter(`${baseUrl}?${params.toString()}`);

		// --- Execute request ---
		const response = await fetch(fullUrl, {
			method: 'GET',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			cache: 'no-store',
			credentials: 'same-origin'
		});

		// --- Validate HTTP response ---
		if (!response.ok) {
			console.error(`[getCodeFromServer] Server responded with status ${response.status}: ${response.statusText}`);
			return null;
		}

		// --- Parse JSON response ---
		const data = await response.json();
		return data ?? null;

	} catch (error) {
		console.error('[getCodeFromServer] Fetch operation failed:', error);
		return null;
	}
}

/**
 * Validate if the given ID is acceptable.
 *
 * @param  {number|string} id  The ID value to validate.
 * @return {boolean}           True if valid, false otherwise.
 * @since  3.1.2
 */
function getCodeFrom_isValidId(id) {
	if (typeof id === 'number') {
		return Number.isInteger(id) && id > 0;
	}
	if (typeof id === 'string') {
		return id.trim().length > 30;
	}
	return false;
}

function addCodeToEditor(code_string, editor_id, merge, merge_target){
	if (Joomla.editors.instances.hasOwnProperty(editor_id)) {
		var old_code_string = Joomla.editors.instances[editor_id].getValue();
		if (merge && old_code_string.length > 0) {
			// make sure not to load the same string twice
			if (old_code_string.indexOf(code_string) == -1)  {
				if ('prepend' === merge_target) {
					var _string = code_string + "\n\n" + old_code_string;
				} else if (merge_target && 'append' !== merge_target) {
					var old_code_array = old_code_string.split(merge_target);
					if (old_code_array.length > 1) {
						var _string = old_code_array.shift() + "\n\n" + code_string + "\n\n" + merge_target + old_code_array.join(merge_target);
					} else {
						var _string = code_string + "\n\n" + merge_target + old_code_array.join('');
					}
				} else {
					var _string = old_code_string + "\n\n" + code_string;
				}
				Joomla.editors.instances[editor_id].setValue(_string.trim());
				return true;
			}
		} else {
			Joomla.editors.instances[editor_id].setValue(code_string.trim());
			return true;
		}
	} else {
		var old_code_string = jQuery('textarea#'+editor_id).val();
		if (merge && old_code_string.length > 0) {
			// make sure not to load the same string twice
			if (old_code_string.indexOf(code_string) == -1) {
				if ('prepend' === merge_target) {
					var _string = code_string + "\n\n" + old_code_string;
				} else if (merge_target && 'append' !== merge_target) {
					var old_code_array = old_code_string.split(merge_target);
					if (old_code_array.length > 1) {
						var _string = old_code_array.shift() + "\n\n" + code_string + "\n\n" + merge_target + old_code_array.join(merge_target);
					} else {
						var _string = code_string + "\n\n" + merge_target + old_code_array.join('');
					}
				} else {
					var _string = old_code_string + "\n\n" + code_string;
				}
				jQuery('textarea#'+editor_id).val(_string.trim());
				return true;
			}
		} else {
			jQuery('textarea#'+editor_id).val(code_string.trim());
			return true;
		}
	}
	return false;
}


function removeCodeFromEditor(code_string, editor_id){
	if (Joomla.editors.instances.hasOwnProperty(editor_id)) {
		var old_code_string = Joomla.editors.instances[editor_id].getValue();
		if (old_code_string.length > 0) {
			// make sure string is found
			if (old_code_string.indexOf(code_string) !== -1) {
				// remove the code
				Joomla.editors.instances[editor_id].setValue(old_code_string.replace(code_string+"\n\n",'').replace("\n\n"+code_string,'').replace(code_string,''));
				return true;
			}
		}
	} else {
		var old_code_string = jQuery('textarea#'+editor_id).val();
		if (old_code_string.length > 0) {
			// make sure string is found
			if (old_code_string.indexOf(code_string) !== -1) {
				// remove the code
				jQuery('textarea#'+editor_id).val(old_code_string.replace(code_string+"\n\n",'').replace("\n\n"+code_string,'').replace(code_string,''));
				return true;
			}
		}
	}
	return false;
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

function getSnippetDetails(id){
	getCodeFrom_server(id, '_type', '_type', 'snippetDetails').then(function(result) {
		if(result.snippet){
			var description = '';
			if (result.description.length > 0) {
				description = '<p>'+result.description+'</p>';
			}
			var library = '';
			if (result.library.length > 0) {
				library = ' <b>('+result.library+')</b>';
			}
			var code = '<div id="snippet-code"><b>'+result.name+' ('+result.type+')</b> <a href="'+result.url+'" target="_blank" >see more details'+library+'</a><br /><em>'+result.heading+'</em><br /><textarea  id="snippet" class="span12" rows="11">'+result.snippet+'</textarea></div>';
			jQuery('#snippet-code').remove();
			jQuery('.snippet-code').append(code);
			// make sure the code block is active
			jQuery("#snippet").focus(function() {
				var jQuerythis = jQuery(this);
				jQuerythis.select();
			
				// Work around Chrome's little problem
				jQuerythis.mouseup(function() {
					// Prevent further mouseup intervention
					jQuerythis.unbind("mouseup");
					return false;
				});
			});
		}
		if(result.usage){
			var usage = '<div id="snippet-usage"><p>'+result.usage+'</p></div>';
			jQuery('#snippet-usage').remove();
			jQuery('.snippet-usage').append(usage);
		}
	})
}

// set snippets that are on the page
var snippetIds = [];
var snippets = {};
var snippet = 0;
jQuery(document).ready(function($)
{
	jQuery("#jform_snippet option").each(function()
	{
		var key =  jQuery(this).val();
		var text =  jQuery(this).text();
		snippets[key] = text;
		snippetIds.push(key);
	});
	snippet = jQuery("#jform_snippet").val();
	getSnippets();
});

function getSnippets(){
	jQuery("#loading").show();
	// clear the selection
	jQuery('#jform_snippet').find('option').remove().end();
	jQuery('#jform_snippet').trigger('liszt:updated');
	// get libraries value if set
	var libraries = jQuery("#jform_libraries").val();
	if (libraries) {
		getCodeFrom_server(1, JSON.stringify(libraries), 'libraries', 'getSnippets').then(function(result) {
			setSnippets(result);
			jQuery("#loading").hide();
			if (typeof snippetButton !== 'undefined') {
				// ensure button is correct
				var snippet = jQuery('#jform_snippet').val();
				snippetButton(snippet);
			}
		});
	}
	else
	{
		// load all snippets in none is selected
		setSnippets(snippetIds);
		jQuery("#loading").hide();
	}
}
function setSnippets(array){
	if (array) {
		jQuery('#jform_snippet').append('<option value="">'+select_a_snippet+'</option>');
		jQuery.each( array, function( i, id ) {
			if (id in snippets) {
				jQuery('#jform_snippet').append('<option value="'+id+'">'+snippets[id]+'</option>');
			}
			if (id == snippet) {
				jQuery('#jform_snippet').val(id);
			}
		});
	} else {
		jQuery('#jform_snippet').append('<option value="">'+create_a_snippet+'</option>');
	}
	jQuery('#jform_snippet').trigger('liszt:updated');
}
