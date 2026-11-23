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
var updatePowerConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updatePowerConditions()
{
	if (updatePowerConditionsBaseline === null)
	{
		updatePowerConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_head-lbl"];
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
					updatePowerConditionsBaseline.containers.push({element: element, display: element.style.display});
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
				updatePowerConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updatePowerConditionsBaseline.containers.length; index++)
	{
		var baseline = updatePowerConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updatePowerConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var add_head_vvvvvxe = jQuery("#jform_add_head input[type='radio']:checked").val();
	vvvvvxe(add_head_vvvvvxe);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updatePowerConditions);

// the vvvvvxe function
function vvvvvxe(add_head_vvvvvxe)
{
	// set the function logic
	if (add_head_vvvvvxe == Number('1'))
	{
		jQuery('#jform_head-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_head-lbl').closest('.control-group').hide();
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


document.addEventListener('DOMContentLoaded', function() {
	// get the linked details
	getLinked();
	// load the active array values
	buildSelectionMemory('property');
	buildSelectionMemory('method');
	// load the active selection array values
	getClassCodeIds('property', 'jform_id', false);
	getClassCodeIds('method', 'jform_id', false);
	// check and load all the customcode edit buttons
	setTimeout(getEditCustomCodeButtons, 300);
	// trigger the row watcher
	rowWatcher();
});

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

// set selection the options
selectionMemory = {'property':{},'method':{}};
selectionActiveArray = {'property':{},'method':{}};
selectedIdRemoved = {'property':'not','method':'not'};

function buildSelectionMemory(type) {
	var i;
	for (i = 0; i < 70; i++) {
		// build ID
		var id_check = 'jform_'+type+'_selection'+'__'+type+'_selection'+i+'__'+type;
		// set memory
		if (jQuery("#"+id_check).length) {
			selectionMemory[type][id_check] = jQuery("#"+id_check+" option:selected").val();
		}
	}
}

function getClassHeaderCode(){
	// now get the values
	var value = jQuery("#jform_class_extends  option:selected").val();
	var add_value = jQuery("#jform_add_head input[type='radio']:checked").val();
	if (add_value == 1 && value > 0){
		// we first check local memory
		var _result = jQuery.jStorage.get('extends_header_'+value, null);
		if (_result) {
				// now set the code
				addCodeToEditor(_result, "jform_head", false, null);
		} else {
			// now get the code
			getCodeFrom_server(value, 'extends', 'type', 'getClassHeaderCode').then(function(result) {
				if(result){
					// now set the code
					addCodeToEditor(result, "jform_head", false, null);
					// add result to local memory
					jQuery.jStorage.set('extends_header_'+value, result, {TTL: expire});
				}
			});
		}
	}
}

function getClassCodeIds(type, target_field, reset_all){
	// now get the value
	var value = jQuery('#'+target_field).val();
	// now get the code
	getCodeFrom_server(value, type, 'type', 'getClassCodeIds').then(function(result) {
		if(result){
			// reset the selection
			selectionActiveArray[type] = {};
			// update the active array
			jQuery.each(result, function(i, prop) {
				selectionActiveArray[type][prop] = selectionArray[type][prop];
			});
			// update the active field selection
			updateActiveFieldSelection(type, reset_all);
		}
	});
}

function updateActiveFieldSelection(type, reset_all){
	// update the selection options
	if ('joomla_plugin_group' === type) {
		// get value if not going to reset all
		if (!reset_all){
			// get the active values
			var activeValue =  jQuery("#jform_"+type+" option:selected").val();
			var activeText =  jQuery("#jform_"+type+" option:selected").text();
			// clear the options out
			jQuery("#jform_"+type).find('option').remove().end();
			// add the active selection back (must be what is available)
			jQuery("#jform_"+type).append('<option value="'+activeValue+'">'+activeText+'</option>');
			// now add the lists back
			jQuery.each( selectionActiveArray[type], function(aValue, aText ) {
				if (activeValue !== aValue) {
					jQuery("#jform_"+type).append('<option value="'+aValue+'">'+aText+'</option>');
				}
			});
			jQuery("#jform_"+type).val(activeValue);			
		} else {
			// clear the options out
			jQuery("#jform_"+type).find('option').remove().end();
			// now add the lists back
			jQuery.each( selectionActiveArray[type], function(aValue, aText ) {
				jQuery("#jform_"+type).append('<option value="'+aValue+'">'+aText+'</option>');
			});
			jQuery("#jform_"+type).val('');
		}
		jQuery("#jform_"+type).trigger('liszt:updated');
		// reset all when global update is made
		if (reset_all) {
			resetAll('method');
			resetAll('property');
		}
	} else {
		selectionDynamicUpdate(type);
		// reset all when global update is made
		if (reset_all) {
			resetAll(type);
		}
	}
}

function resetAll(type) {
	var i;
	for (i = 0; i < 10; i++) {
		// build ID
		var id_check = 'jform_'+type+'_selection'+'__'+type+'_selection'+i+'__'+type;
		// first check if Id is on page as that not the same as the one currently calling
		if (jQuery("#"+id_check).length) {
			if (i == 0) {
				jQuery('#'+id_check).val('');
				jQuery('#'+id_check).trigger('liszt:updated');
			} else {
				// remove the row
				jQuery('#'+id_check).closest('tr').remove();
			}
		}
	}
	Joomla.editors.instances['jform_main_class_code'].setValue('');
	selectionMemory = {'property':{},'method':{}};
}

function getClassCode(field, type){
	// get the ID
	var id = jQuery(field).attr('id');
	// now get the value
	var value = jQuery('#' + id).val();
	// check if we have a memory for this field, if true remove code of old selection and clear memory
	if (selectionMemory[type].hasOwnProperty(id) && selectionMemory[type][id] > 0) {
		// the old id to remove
		var old_value = selectionMemory[type][id];
		// remove the code // we first check local memory
		var _result = jQuery.jStorage.get('code_4_'+type+'_'+old_value, null);
		if (_result) {
			// now remove the code
			if (removeCodeFromEditor(_result, 'jform_main_class_code')) {
				selectionMemory[type][id] = 0;
			}
		} else {
			// now get the code
			getCodeFrom_server(old_value, type, 'type', 'getClassCode').then(function(result) {
				if(result){
					// now remove the code
					if (removeCodeFromEditor(result, 'jform_main_class_code')) {
						selectionMemory[type][id] = 0;
					}
					// add result to local memory
					jQuery.jStorage.set('code_4_'+type+'_'+old_value, result, {TTL: expire});
				}
			});
		}
	}
	if (propertyIsSet(value, id, type)) {
		// reset the selection
		jQuery('#'+id).val('');
		jQuery('#'+id).trigger("liszt:updated");
		// give out a notice
		jQuery.UIkit.notify({message: Joomla.Text._('COM_COMPONENTBUILDER_ALREADY_SELECTED_TRY_ANOTHER'), timeout: 5000, status: 'warning', pos: 'top-center'});
	} else {
		// set the active removed value
		selectedIdRemoved[type] = id;
		// do a dynamic update (to remove what was already used)
		selectionDynamicUpdate(type);
		// set the add action
		if (type === 'property') {
			var _action_add = 'prepend';
		} else {
			var _action_add = 'append';
		}
		// we first check local memory
		var _result = jQuery.jStorage.get('code_4_'+type+'_'+value, null);
		if (_result) {
			// now set the code
			if (addCodeToEditor(_result, "jform_main_class_code", true, _action_add)) {
				selectionMemory[type][id] = value;
			}
		} else {
			// now get the code
			getCodeFrom_server(value, type, 'type', 'getClassCode').then(function(result) {
				if(result){
					// now set the code
					if (addCodeToEditor(result, "jform_main_class_code", true, _action_add)) {
						selectionMemory[type][id] = value;
					}
					// add result to local memory
					jQuery.jStorage.set('code_4_'+type+'_'+value, result, {TTL: expire});
				}
			});
		}
	}
}

function selectionDynamicUpdate(type) {
	selectionAvailable = {};
	selectionSelectedArray = {};
	selectionTrackerArray = {};
	var i;
	for (i = 0; i < 70; i++) { // for now this is the number of field we should check
		// build ID
		var id_check = 'jform_'+type+'_selection'+'__'+type+'_selection'+i+'__'+type;
		// first check if Id is on page as that not the same as the one currently calling
		if (jQuery("#"+id_check).length && selectedIdRemoved[type] !== id_check) {
			// build the selected array
			var key =  jQuery("#"+id_check+" option:selected").val();
			var text =  jQuery("#"+id_check+" option:selected").text();
			selectionSelectedArray[key] = text;
			// keep track of the value set
			selectionTrackerArray[id_check] = key;
			// clear the options out
			jQuery("#"+id_check).find('option').remove().end();
		}
	}
	// now build the list to keep
	jQuery.each( selectionActiveArray[type], function( prop, name ) {
		if (!selectionSelectedArray.hasOwnProperty(prop)) {
			selectionAvailable[prop] = name;
		}
	});
	// now add the lists back
	jQuery.each( selectionTrackerArray, function( tId, tKey ) {
		if (jQuery('#'+tId).length) {
			jQuery('#'+tId).append('<option value="'+tKey+'">'+selectionSelectedArray[tKey]+'</option>');
			jQuery.each( selectionAvailable, function( aKey, aValue ) {
				jQuery('#'+tId).append('<option value="'+aKey+'">'+aValue+'</option>');
			});
			jQuery('#'+tId).val(tKey);
			jQuery('#'+tId).trigger('liszt:updated');
		}
	});
}

function rowWatcher() {
	jQuery(document).on('subform-row-remove', function(event, row){
		// we first chck if this is a method call
       		var valid_call = jQuery(row.innerHTML).find('.method_selection_list').attr('id');
		var type_call = 'method';
		if (!isSet(valid_call)){
			// now lets see if this is a property call
			var valid_call = jQuery(row.innerHTML).find('.property_selection_list').attr('id');
			var type_call = 'property';
		}
		// so lets update selection if call valid
		if (isSet(valid_call)){
			selectedIdRemoved[type_call] = valid_call;
			selectionDynamicUpdate(type_call);
			// also remove from code
			var valid_value = jQuery(row.innerHTML).find('#' + valid_call + ' option:selected').val();
			if (valid_value === '') {
				valid_value = selectionMemory[type_call][valid_call];
			}
			// remove the code // we first check local memory
			var _result = jQuery.jStorage.get('code_4_'+type_call+'_'+valid_value, null);
			if (_result) {
				// now remove the code
				if (removeCodeFromEditor(_result, 'jform_main_class_code')) {
					selectionMemory[type_call][valid_call] = 0;
				}
			} else {
				// now get the code
				getCodeFrom_server(valid_value, type_call, 'type', 'getClassCode').then(function(result) {
					if(result){
						if (removeCodeFromEditor(result, 'jform_main_class_code')) {
							selectionMemory[type_call][valid_call] = 0;;
						}
						// add result to local memory
						jQuery.jStorage.set('code_4_'+type_call+'_'+valid_value, result, {TTL: expire});
					}
				});
			}
		}
	});
	jQuery(document).on('subform-row-add', function(event, row){
		// we first chck if this is a method call
       		var valid_call = jQuery(row.innerHTML).find('.method_selection_list').attr('id');
		var type_call = 'method';
		if (!isSet(valid_call)){
			// now lets see if this is a property call
			var valid_call = jQuery(row.innerHTML).find('.property_selection_list').attr('id');
			var type_call = 'property';
		}
		// so lets update selection if call valid
		if (isSet(valid_call)){
			selectedIdRemoved[type_call] = 'not';
			selectionDynamicUpdate(type_call);
		}
	});
}

function propertyIsSet(prop, id, type) {
	var i;
	for (i = 0; i < 70; i++) { // for now this is the number of field we should check
		// build ID
		var id_check = 'jform_'+type+'_selection'+'__'+type+'_selection'+i+'__'+type;
		// first check if Id is on page as that not the same as the one currently calling
		if (jQuery("#"+id_check).length && id_check != id) {
			// get the property value
			var tmp = jQuery("#"+id_check+" option:selected").val();
			// now validate
			if (tmp === prop) {
				return true;
			}
		}
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
