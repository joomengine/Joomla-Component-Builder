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



/**
 * Initialize core view functionality.
 *
 * Loads linked item details and custom code edit buttons
 * once the DOM is ready.
 *
 * @returns {void}
 * @since   6.1.6
 */
(function() {
	'use strict';

	function initCoreView() {
		if (typeof getLinked === 'function') {
			getLinked();
		}

		if (typeof getEditCustomCodeButtons === 'function') {
			getEditCustomCodeButtons();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCoreView, { once: true });
	} else {
		initCoreView();
	}
})();

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
 * Validate if the given ID is acceptable.
 *
 * Accepts positive integers and any non-empty string (including GUIDs).
 *
 * @param   {number|string} id  The ID value to validate.
 *
 * @return  {boolean}  True if valid, false otherwise.
 * @since   3.1.2
 */
function getCodeFrom_isValidId(id) {
	if (typeof id === 'number') {
		return Number.isInteger(id) && id > 0;
	}
	if (typeof id === 'string') {
		return id.trim().length > 0;
	}
	return false;
}

/**
 * Fetch data from the server with validated parameters.
 *
 * @param   {number|string} id          The record ID or GUID.
 * @param   {string}        type        The type value to send.
 * @param   {string}        typeName    The type parameter name (e.g. "type" or "libraries").
 * @param   {string}        callingName The AJAX task name (e.g. "getLinked").
 * @global  {string}        token       The CSRF token name or key.
 * @global  {string}        vastDevMod  The developer key or mode flag (optional).
 *
 * @return  {Promise<*>}  Returns parsed JSON data or null on failure.
 * @since   3.1.2
 */
async function getCodeFrom_server(id, type, typeName, callingName) {
	try {
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

		var params = new URLSearchParams({
			option: 'com_componentbuilder',
			task: 'ajax.' + callingName,
			format: 'json',
			raw: 'true',
			[token]: '1',
			[typeName]: type,
			id: id
		});

		if (typeof vastDevMod !== 'undefined' && vastDevMod) {
			params.append('vdm', vastDevMod);
		}

		var fullUrl = JRouter('index.php?' + params.toString());

		var response = await fetch(fullUrl, {
			method: 'GET',
			headers: {
				'Accept': 'application/json'
			},
			cache: 'no-store',
			credentials: 'same-origin'
		});

		if (!response.ok) {
			console.error(
				'[getCodeFrom_server] Server responded with status '
				+ response.status + ': ' + response.statusText
			);
			return null;
		}

		var data = await response.json();

		return data ?? null;
	} catch (error) {
		console.error('[getCodeFrom_server] Fetch operation failed:', error);
		return null;
	}
}

/**
 * Get the Choices.js instance for a select element.
 *
 * Joomla 4/5 wraps enhanced selects in a <joomla-field-fancy-select>
 * custom element that stores the Choices.js instance.
 *
 * @param   {HTMLSelectElement} selectElement  The select element.
 *
 * @returns {object|null}  The Choices.js instance, or null.
 * @since   5.1.1
 */
function getChoicesInstance(selectElement) {
	if (!selectElement) {
		return null;
	}

	var wrapper = selectElement.closest('joomla-field-fancy-select');

	if (wrapper && wrapper.choicesInstance) {
		return wrapper.choicesInstance;
	}

	return null;
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

/**
 * Load and display snippet details for the selected snippet.
 *
 * Fetches snippet data from the server and renders the snippet code
 * and usage blocks into their respective containers.
 *
 * @param   {string|number} id  The snippet GUID or ID.
 *
 * @returns {void}
 * @since   3.1.2
 */
function getSnippetDetails(id) {
	if (!id) {
		return;
	}

	getCodeFrom_server(id, '_type', '_type', 'snippetDetails').then(function(result) {
		if (!result) {
			return;
		}

		if (result.snippet) {
			var library = '';
			if (result.library && result.library.length > 0) {
				library = ' <b>(' + result.library + ')</b>';
			}

			var html = '<div id="snippet-code">'
				+ '<b>' + result.name + ' (' + result.type + ')</b> '
				+ '<a href="' + result.url + '" target="_blank">'
				+ 'see more details' + library + '</a><br />'
				+ '<em>' + result.heading + '</em><br />'
				+ '<textarea id="snippet" class="form-control w-100" rows="11">'
				+ result.snippet
				+ '</textarea>'
				+ '</div>';

			var existingCode = document.getElementById('snippet-code');
			if (existingCode) {
				existingCode.remove();
			}

			var codeContainer = document.querySelector('.snippet-code');
			if (codeContainer) {
				codeContainer.insertAdjacentHTML('beforeend', html);

				var textarea = document.getElementById('snippet');
				if (textarea) {
					textarea.addEventListener('focus', function() {
						this.select();
					});
				}
			}
		}

		if (result.usage) {
			var existingUsage = document.getElementById('snippet-usage');
			if (existingUsage) {
				existingUsage.remove();
			}

			var usageContainer = document.querySelector('.snippet-usage');
			if (usageContainer) {
				usageContainer.insertAdjacentHTML(
					'beforeend',
					'<div id="snippet-usage"><p>' + result.usage + '</p></div>'
				);
			}
		}
	}).catch(function(error) {
		console.error('[getSnippetDetails] Failed:', error);
	});
}

/**
 * Cached snippet option GUIDs from the original field.
 *
 * @type {Array<string>}
 */
var snippetIds = [];

/**
 * Cached snippet option labels keyed by GUID.
 *
 * @type {Object<string, string>}
 */
var snippets = {};

/**
 * The currently selected snippet GUID.
 *
 * @type {string}
 */
var snippet = '';

/**
 * Load snippets based on the selected libraries.
 *
 * Reads the current library selection through the Choices.js API
 * when available, sends the selected GUIDs to the server, and
 * rebuilds the snippet select with the filtered results.
 *
 * @returns {void}
 * @since   3.1.2
 */
function getSnippets() {
	var loading = document.getElementById('loading');
	if (loading) {
		loading.style.display = '';
	}

	var select = document.getElementById('jform_snippet');
	if (!select) {
		if (loading) {
			loading.style.display = 'none';
		}
		return;
	}

	var librariesSelect = document.getElementById('jform_libraries');
	var libraries = null;

	if (librariesSelect) {
		var libChoices = getChoicesInstance(librariesSelect);

		if (libChoices && typeof libChoices.getValue === 'function') {
			var selected = libChoices.getValue(true);

			if (Array.isArray(selected) && selected.length > 0) {
				libraries = selected;
			} else if (typeof selected === 'string' && selected.trim().length > 0) {
				libraries = [selected];
			}
		} else if (librariesSelect.multiple) {
			libraries = Array.from(librariesSelect.selectedOptions).map(function(opt) {
				return opt.value;
			});

			if (libraries.length === 0) {
				libraries = null;
			}
		} else if (librariesSelect.value) {
			libraries = [librariesSelect.value];
		}
	}

	if (libraries && libraries.length > 0) {
		getCodeFrom_server(1, JSON.stringify(libraries), 'libraries', 'getSnippets').then(function(result) {
			setSnippets(result);

			if (loading) {
				loading.style.display = 'none';
			}

			if (typeof snippetButton !== 'undefined') {
				var currentVal = '';
				var choicesInst = getChoicesInstance(select);

				if (choicesInst && typeof choicesInst.getValue === 'function') {
					var val = choicesInst.getValue(true);
					currentVal = (typeof val === 'string') ? val : '';
				} else {
					currentVal = select.value || '';
				}

				snippetButton(currentVal);
			}
		}).catch(function(error) {
			console.error('[getSnippets] Failed:', error);
			if (loading) {
				loading.style.display = 'none';
			}
		});
	} else {
		setSnippets(snippetIds);
		if (loading) {
			loading.style.display = 'none';
		}
	}
}

/**
 * Rebuild the snippet select options.
 *
 * Uses the Choices.js API when available to keep the enhanced
 * select widget in sync. Falls back to native DOM manipulation
 * for views without Choices.js.
 *
 * @param   {Array|null} array  The list of snippet GUIDs to display.
 *
 * @returns {void}
 * @since   3.1.2
 */
function setSnippets(array) {
	var select = document.getElementById('jform_snippet');
	if (!select) {
		return;
	}

	var choicesInstance = getChoicesInstance(select);
	var choicesArray = [];

	if (Array.isArray(array) && array.length > 0) {
		array.forEach(function(guid) {
			var key = String(guid);

			if (Object.prototype.hasOwnProperty.call(snippets, key)) {
				choicesArray.push({
					value: key,
					label: snippets[key],
					selected: key === snippet
				});
			}
		});
	} else {
		choicesArray.push({
			value: '',
			label: create_a_snippet,
			selected: true
		});
	}

	if (choicesInstance && typeof choicesInstance.clearStore === 'function' && typeof choicesInstance.setChoices === 'function') {
		choicesInstance.clearStore();
		choicesInstance.setChoices(choicesArray, 'value', 'label', true);

		if (snippet) {
			choicesInstance.setChoiceByValue(snippet);
		}
	} else {
		select.innerHTML = '';

		choicesArray.forEach(function(opt) {
			var option = document.createElement('option');
			option.value = opt.value;
			option.textContent = opt.label;

			if (opt.selected) {
				option.selected = true;
			}

			select.appendChild(option);
		});
	}
}

/**
 * Initialize the snippet system.
 *
 * Caches the original field options, binds the Choices.js addItem
 * event for snippet selection, binds library change events to
 * reload snippets, and triggers the initial snippet load.
 *
 * @returns {void}
 * @since   5.1.1
 */
function initSnippetSystem() {
	var select = document.getElementById('jform_snippet');
	if (!select) {
		return;
	}

	if (select.dataset.snippetSystemInit === '1') {
		return;
	}
	select.dataset.snippetSystemInit = '1';

	// Cache the original options rendered by the PHP field
	Array.from(select.options).forEach(function(option) {
		var key = String(option.value || '');
		var text = String(option.text || '');
		snippets[key] = text;
		snippetIds.push(key);
	});
	snippet = String(select.value || '');

	/**
	 * Bind the Choices.js addItem event to trigger snippet detail loading.
	 *
	 * @returns {boolean}  True if binding succeeded, false if Choices.js is not ready.
	 */
	function bindChoicesEvents() {
		var choicesInstance = getChoicesInstance(select);

		if (
			choicesInstance
			&& choicesInstance.passedElement
			&& choicesInstance.passedElement.element
		) {
			choicesInstance.passedElement.element.addEventListener('addItem', function(e) {
				var value = e.detail ? e.detail.value : '';

				if (value) {
					snippet = value;
					getSnippetDetails(value);
				}

				if (typeof snippetButton !== 'undefined') {
					snippetButton(value || '');
				}
			});

			return true;
		}

		return false;
	}

	// Native change listener as fallback for views without Choices.js
	select.addEventListener('change', function() {
		if (this.value) {
			snippet = this.value;
			getSnippetDetails(this.value);
		}

		if (typeof snippetButton !== 'undefined') {
			snippetButton(this.value || '');
		}
	});

	// Bind Choices.js events now; retry if the instance is not ready yet
	if (!bindChoicesEvents()) {
		setTimeout(function() {
			if (!bindChoicesEvents()) {
				setTimeout(bindChoicesEvents, 3000);
			}
		}, 1000);
	}

	// Bind the libraries field to reload snippets on selection change
	var librariesSelect = document.getElementById('jform_libraries');

	if (librariesSelect) {
		var libChoices = getChoicesInstance(librariesSelect);

		if (
			libChoices
			&& libChoices.passedElement
			&& libChoices.passedElement.element
		) {
			libChoices.passedElement.element.addEventListener('addItem', function() {
				getSnippets();
			});

			libChoices.passedElement.element.addEventListener('removeItem', function() {
				getSnippets();
			});
		} else {
			librariesSelect.addEventListener('change', function() {
				getSnippets();
			});
		}
	}

	// Load initial snippets
	getSnippets();
}

// Initialize when the DOM is ready
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initSnippetSystem, { once: true });
} else {
	initSnippetSystem();
}
