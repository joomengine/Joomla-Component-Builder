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
var updateCustomCodeConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateCustomCodeConditions()
{
	if (updateCustomCodeConditionsBaseline === null)
	{
		updateCustomCodeConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_function_name",".note_jcb_placeholder","#jform_system_name","#jform_component","#jform_path","#jform_from_line","#jform_hashtarget","#jform_to_line","#jform_type","#jform_hashendtarget"];
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
					updateCustomCodeConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = ["function_name","component","path","type"];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateCustomCodeConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateCustomCodeConditionsBaseline.containers.length; index++)
	{
		var baseline = updateCustomCodeConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateCustomCodeConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var target_vvvvwaj = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwaj(target_vvvvwaj);

	var target_vvvvwak = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwak(target_vvvvwak);

	var target_vvvvwal = jQuery("#jform_target input[type='radio']:checked").val();
	var type_vvvvwal = jQuery("#jform_type input[type='radio']:checked").val();
	vvvvwal(target_vvvvwal,type_vvvvwal);

	var type_vvvvwam = jQuery("#jform_type input[type='radio']:checked").val();
	var target_vvvvwam = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwam(type_vvvvwam,target_vvvvwam);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateCustomCodeConditions);

// the vvvvwaj function
function vvvvwaj(target_vvvvwaj)
{
	// set the function logic
	if (target_vvvvwaj == Number('2'))
	{
		jQuery('#jform_function_name').closest('.control-group').show();
		// Add required attributes to function_name.
		jQuery('#jform_function_name').prop('required', true);
		jQuery('#jform_function_name').attr('aria-required', true);
		jQuery('#jform_function_name').addClass('required');
		jQuery('.note_jcb_placeholder').closest('.control-group').show();
		jQuery('#jform_system_name').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_function_name').closest('.control-group').hide();
		// Remove required attributes from function_name.
		jQuery('#jform_function_name').removeAttr('required');
		jQuery('#jform_function_name').removeAttr('aria-required');
		jQuery('#jform_function_name').removeClass('required');
		jQuery('.note_jcb_placeholder').closest('.control-group').hide();
		jQuery('#jform_system_name').closest('.control-group').hide();
	}
}

// the vvvvwak function
function vvvvwak(target_vvvvwak)
{
	// set the function logic
	if (target_vvvvwak == Number('1'))
	{
		jQuery('#jform_component').closest('.control-group').show();
		// Add required attributes to component.
		jQuery('#jform_component').prop('required', true);
		jQuery('#jform_component').attr('aria-required', true);
		jQuery('#jform_component').addClass('required');
		jQuery('#jform_path').closest('.control-group').show();
		// Add required attributes to path.
		jQuery('#jform_path').prop('required', true);
		jQuery('#jform_path').attr('aria-required', true);
		jQuery('#jform_path').addClass('required');
		jQuery('#jform_from_line').closest('.control-group').show();
		jQuery('#jform_hashtarget').closest('.control-group').show();
		jQuery('#jform_to_line').closest('.control-group').show();
		jQuery('#jform_type').closest('.control-group').show();
		// Add required attributes to type.
		jQuery('#jform_type').prop('required', true);
		jQuery('#jform_type').attr('aria-required', true);
		jQuery('#jform_type').addClass('required');
	}
	else
	{
		jQuery('#jform_component').closest('.control-group').hide();
		// Remove required attributes from component.
		jQuery('#jform_component').removeAttr('required');
		jQuery('#jform_component').removeAttr('aria-required');
		jQuery('#jform_component').removeClass('required');
		jQuery('#jform_path').closest('.control-group').hide();
		// Remove required attributes from path.
		jQuery('#jform_path').removeAttr('required');
		jQuery('#jform_path').removeAttr('aria-required');
		jQuery('#jform_path').removeClass('required');
		jQuery('#jform_from_line').closest('.control-group').hide();
		jQuery('#jform_hashtarget').closest('.control-group').hide();
		jQuery('#jform_to_line').closest('.control-group').hide();
		jQuery('#jform_type').closest('.control-group').hide();
		// Remove required attributes from type.
		jQuery('#jform_type').removeAttr('required');
		jQuery('#jform_type').removeAttr('aria-required');
		jQuery('#jform_type').removeClass('required');
	}
}

// the vvvvwal function
function vvvvwal(target_vvvvwal,type_vvvvwal)
{
	// set the function logic
	if ((target_vvvvwal == Number('1')) && (type_vvvvwal == Number('1')))
	{
		jQuery('#jform_hashendtarget').closest('.control-group').show();
		jQuery('#jform_to_line').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_hashendtarget').closest('.control-group').hide();
		jQuery('#jform_to_line').closest('.control-group').hide();
	}
}

// the vvvvwam function
function vvvvwam(type_vvvvwam,target_vvvvwam)
{
	// set the function logic
	if ((type_vvvvwam == Number('1')) && (target_vvvvwam == Number('1')))
	{
		jQuery('#jform_hashendtarget').closest('.control-group').show();
		jQuery('#jform_to_line').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_hashendtarget').closest('.control-group').hide();
		jQuery('#jform_to_line').closest('.control-group').hide();
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


document.addEventListener('DOMContentLoaded', function () {
	var target = document.querySelector('#jform_target input[type="radio"]:checked');
	if (target && target.value == 2) {
		document.getElementById('usedin').style.display = '';
		var functioName = document.getElementById('jform_function_name').value;
		// check if this function name is taken
		checkFunctionName(functioName);
	}
	var type = document.querySelector('#jform_comment_type input[type="radio"]:checked');
	if (type && type.value == 2) {
		document.getElementById('html-comment-info').style.display = '';
		document.getElementById('phpjs-comment-info').style.display = 'none';
	} else {
		document.getElementById('html-comment-info').style.display = 'none';
		document.getElementById('phpjs-comment-info').style.display = '';
	}
	// check and load all the custom code edit buttons
	getEditCustomCodeButtons();
});

function setCustomCodePlaceholder() {
	var ide = document.getElementById('jform_id').value;
	var functioName = document.getElementById('jform_function_name').value;
	if (ide > 0 && functioName.length > 2) {
		document.getElementById('jcb-placeholder').innerHTML = '<code>[CUSTO'+'MCODE='+functioName+']</code>';
		document.getElementById('jcb-placeholder-arg').innerHTML = '<code>[CUSTO'+'MCODE='+functioName+'&#43;value1,value2]</code>';
	} else if (ide > 0) {
		document.getElementById('jcb-placeholder').innerHTML = '<code>[not ready]</code>';
		document.getElementById('jcb-placeholder-arg').innerHTML = '<code>[not ready]</code>';
	} else if (functioName.length > 2) {
		document.getElementById('jcb-placeholder').innerHTML = '<code>[CUSTO'+'MCODE='+functioName+']</code>';
		document.getElementById('jcb-placeholder-arg').innerHTML = '<code>[CUSTO'+'MCODE='+functioName+'&#43;value1,value2]</code>';
	} else {
		document.getElementById('jcb-placeholder').innerHTML = '<code>[save to see]</code>';
		document.getElementById('jcb-placeholder-arg').innerHTML = '<code>[save to see]</code>';
	}
	// update the notes
	if (ide > 0) {
		document.querySelectorAll('.placeholder-key-id').forEach(function (el) { el.textContent = ide; });
	}
}

function checkFunctionName(functioName) {
	if (functioName.length > 2) {
		var ide = document.getElementById('jform_id').value;
		if (ide == 0) {
			ide = -1;
		}
		checkFunctionName_server(functioName, ide).then(function(result) {
			if(result.name && result.message){
				// show notice that functioName is okay
				showNotice(result.message, result.status);
				document.getElementById('jform_function_name').value = result.name;
				// now start search for where the function is used
				usedin(result.name, ide);
			} else if(result.message){
				// show notice that functioName is not okay
				showNotice(result.message, result.status);
				document.getElementById('jform_function_name').value = '';
			} else {
				// set an error that message was not send
				showNotice(Joomla.Text._('COM_COMPONENTBUILDER_FUNCTION_NAME_ALREADY_TAKEN_PLEASE_TRY_AGAIN'), 'danger');
				document.getElementById('jform_function_name').value = '';
			}
			// set custom code placeholder
			setCustomCodePlaceholder();
		});
	} else {
		// set an error that message was not send
		showNotice(Joomla.Text._('COM_COMPONENTBUILDER_YOU_MUST_ADD_AN_UNIQUE_FUNCTION_NAME'), 'danger');
		document.getElementById('jform_function_name').value = '';
		// set custom code placeholder
		setCustomCodePlaceholder();
	}
}
// check Function name
function checkFunctionName_server(functioName, ide){
	var getUrl = 'index.php?option=com_componentbuilder&task=ajax.checkFunctionName&raw=true&format=json';
	if(token.length > 0){
		var request = 'token='+token+'&functioName='+functioName+'&id='+ide;
	}
	return fetch(getUrl, {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
		body: request
	}).then(function(r) { return r.json(); });
}
//show notices
function showNotice(message, status) {
	var bsClass = status === 'danger' ? 'danger' : status === 'warning' ? 'warning' : 'success';
	var container = document.getElementById('jcb-toast-container');
	if (!container) {
		container = document.createElement('div');
		container.id = 'jcb-toast-container';
		container.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:1090;max-width:350px;';
		document.body.appendChild(container);
	}
	var alert = document.createElement('div');
	alert.className = 'alert alert-' + bsClass + ' alert-dismissible fade show shadow';
	alert.setAttribute('role', 'alert');
	alert.innerHTML = message +
		'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
	container.appendChild(alert);
	setTimeout(function () {
		alert.classList.remove('show');
		setTimeout(function () { alert.remove(); }, 300);
	}, 5000);
}

/**
 * Checks where a given function is used by iterating through a list of numeric targets (0–29).
 * For each target, it calls usedin_server() concurrently and updates the UI based on the responses.
 *
 * @param {string} functioName - The functioName parameter to send to the server.
 * @param {string|number} ide - The identifier to send.
 */
function usedin(functioName, ide) {
	let found = false;

	// Helper functions to show/hide elements by ID.
	const hideElement = (id) => {
		const el = document.getElementById(id);
		if (el) {
			el.style.display = 'none';
		}
	};

	const showElement = (id) => {
		const el = document.getElementById(id);
		if (el) {
			el.style.display = 'block';
		}
	};

	// Hide initial UI elements.
	hideElement('before-usedin');
	hideElement('note-usedin-not');
	hideElement('note-usedin-found');
	showElement('loading-usedin');

	// Create a targets array of 30 integers (0 to 29).
	const targets = Array.from({ length: 30 }, (_, i) => i);

	// Map each target to a promise that makes an AJAX call.
	const promises = targets.map((target) => {
		return usedin_server(functioName, ide, target)
			.then((used) => {
				if (used && used.in) {
					// Check if the element with id "usedin-{used.id}" exists.
					let funcElement = document.getElementById('usedin-' + used.id);
					if (!funcElement) {
						// Create the main container div.
						funcElement = document.createElement('div');
						funcElement.id = 'usedin-' + used.id;

						// Create the header element with the area name.
						const header = document.createElement('h2');
						header.textContent = used.area_name;

						// Create the inner div element that will contain the result.
						const innerDiv = document.createElement('div');
						innerDiv.id = 'area-' + used.id;

						// Append the header and inner div to the main element.
						funcElement.appendChild(header);
						funcElement.appendChild(innerDiv);

						// Append this element to the container with id "usedin-targets".
						const container = document.getElementById('usedin-targets');
						if (container) {
							container.appendChild(funcElement);
						} else {
							console.error(
								"Container with id 'usedin-targets' not found. Appending to document.body instead."
							);
						}
					}

					// Ensure the element is visible.
					showElement('usedin-' + used.id);

					// Update the inner div's content with the response.
					const areaEl = document.getElementById('area-' + used.id);
					if (areaEl) {
						areaEl.innerHTML = used.in;
					}

					// Notify the user.
					showNotice(used.in, 'success');
					found = true;
				} else {
					// If no valid response, hide the element with id "usedin-{target}".
					hideElement('usedin-' + target);
				}
			})
			.catch((error) => {
				console.error('Error in usedin_server for target ' + target + ':', error);
			});
	});

	// Once all Ajax calls are completed, update the UI accordingly.
	Promise.all(promises).then(() => {
		hideElement('loading-usedin');
		if (found) {
			showElement('note-usedin-found');
		} else {
			showElement('note-usedin-not');
		}
	});
}

/**
 * Sends an AJAX GET request to the server with the specified parameters.
 * The function builds a URL with query parameters and returns a promise
 * that resolves with the JSON response.
 *
 * @param {string} functioName - The functioName to send with the request.
 * @param {string|number} ide - The identifier to send.
 * @param {string|number} target - The target functioName to send.
 *
 * @returns {Promise<Object>} - A promise that resolves to the JSON response.
 */
function usedin_server(functioName, ide, target) {
	// Check if the global variable 'token' exists and has a non-empty functioName.
	// 'token', 'functioName', and 'return_here' are assumed to be defined elsewhere in your code.
	if (token && token.length > 0) {
		var request =
			token +
			'=1&functioName=' +
			functioName +
			'&id=' +
			ide +
			'&target=' +
			target +
			'&raw=true&return_here=' +
			return_here;
	} else {
		console.error(
			'There was a issue with the functioNames passed to the [usedin_server] method and we could not make the Ajax call.'
		);
		return Promise.reject(new Error('Invalid token or parameters.'));
	}

	// Base URL for the AJAX request.
	const baseUrl = `index.php?option=com_componentbuilder&task=ajax.usedin&format=json&${request}`;

	// Use the Fetch API to perform a GET request.
	return fetch(baseUrl, {
		method: "GET",
		headers: {
			"Accept": "application/json"
		}
	}).then(response => {
		if (!response.ok) {
			throw new Error(`HTTP error! Status: ${response.status}`);
		}
		return response.json();
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
