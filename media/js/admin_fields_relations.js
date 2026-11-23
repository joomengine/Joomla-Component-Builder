/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */




jQuery(document).ready(function()
{
	// check and load all the customcode edit buttons
	getEditCustomCodeButtons();
});

/**
 * Track active requests per subform row so stale responses
 * do not overwrite newer values.
 *
 * @type {Object.<string, AbortController>}
 */
var codeGlueOptionsControllers = {};

/**
 * Check whether a value is set.
 *
 * @param {*} value
 * @returns {boolean}
 */
function _isSet(value) {
	return value !== undefined && value !== null && value !== '';
}

/**
 * Get a DOM element by ID.
 *
 * @param {string} id
 * @returns {HTMLElement|null}
 */
function getElement(id) {
	return document.getElementById(id);
}

/**
 * Get the subform row prefix from a field element.
 *
 * Expected field ID format:
 * prefix__rowindex__fieldname
 *
 * @param {HTMLElement} field
 * @returns {string}
 */
function getSubformRowId(field) {
	if (!field || !field.id || typeof field.id !== 'string') {
		return '';
	}

	var parts = field.id.split('__');

	if (parts.length < 3) {
		return '';
	}

	return parts[0] + '__' + parts[1];
}

/**
 * Get a trimmed field value by ID.
 *
 * @param {string} id
 * @returns {string}
 */
function getFieldValue(id) {
	var element = getElement(id);

	if (!element || element.value === undefined || element.value === null) {
		return '';
	}

	return String(element.value).trim();
}

/**
 * Extract selected values from a select element.
 *
 * For a multiple select, all selected values are returned.
 * For a normal select, the selected value is returned as a one-item array.
 *
 * @param {HTMLSelectElement|null} selectElement
 * @returns {string[]}
 */
function getSelectedValues(selectElement) {
	if (!selectElement || !selectElement.selectedOptions) {
		return [];
	}

	return Array.from(selectElement.selectedOptions, function(option) {
		return String(option.value || '').trim();
	}).filter(function(value) {
		return value !== '';
	});
}

/**
 * Convert selected join field values into the exact format
 * expected by the PHP server method:
 *
 * - "guid1,guid2,guid3" when values exist
 * - "none" when no values are selected
 *
 * @param {string[]} values
 * @returns {string}
 */
function buildJoinfieldsString(values) {
	if (!Array.isArray(values) || values.length === 0) {
		return 'none';
	}

	return values.join(',');
}

/**
 * Normalize the AJAX response into a string.
 *
 * Your PHP method returns a string or false.
 * Depending on the Joomla AJAX wrapper, the JSON response may contain
 * that value directly or inside a property like result/data/value.
 *
 * @param {*} result
 * @returns {string}
 */
function normalizeCodeGlueResponse(result) {
	if (result === false || result === null || result === undefined) {
		return '';
	}

	if (typeof result === 'string') {
		return result;
	}

	if (typeof result === 'object') {
		if (_isSet(result.result)) {
			return String(result.result);
		}

		if (_isSet(result.data)) {
			return String(result.data);
		}

		if (_isSet(result.value)) {
			return String(result.value);
		}
	}

	return '';
}

/**
 * Abort an in-flight request for a given subform row.
 *
 * @param {string} subID
 * @returns {void}
 */
function abortCodeGlueOptionsRequest(subID) {
	if (codeGlueOptionsControllers[subID]) {
		codeGlueOptionsControllers[subID].abort();
		delete codeGlueOptionsControllers[subID];
	}
}

/**
 * Triggered on subform field change.
 * Reads sibling field values, fetches CodeGlue options,
 * and writes the result into the set field.
 *
 * @param {HTMLElement} field
 * @returns {void}
 */
function getCodeGlueOptions(field) {
	var subID = getSubformRowId(field);

	if (!subID) {
		console.warn('getCodeGlueOptions: could not determine subform row ID.', field);
		return;
	}

	var setField = getElement(subID + '__set');

	if (!setField) {
		console.warn('getCodeGlueOptions: target "set" field not found for row:', subID);
		return;
	}

	var listfield = getFieldValue(subID + '__listfield');
	var type = getFieldValue(subID + '__join_type');
	var area = getFieldValue(subID + '__area');
	var joinfieldsSelect = getElement(subID + '__joinfields');
	var joinfields = buildJoinfieldsString(getSelectedValues(joinfieldsSelect));

	if (!_isSet(listfield) || !_isSet(type) || !_isSet(area)) {
		abortCodeGlueOptionsRequest(subID);
		setField.value = '';
		return;
	}

	getCodeGlueOptions_server(subID, listfield, joinfields, type, area)
		.then(function(result) {
			setField.value = normalizeCodeGlueResponse(result);
		})
		.catch(function(error) {
			if (error && error.name === 'AbortError') {
				return;
			}

			console.error('getCodeGlueOptions failed:', error);
			setField.value = '';
		});
}

/**
 * Fetch CodeGlue options from the server.
 *
 * The PHP side expects:
 * - joinfields = "guid1,guid2,guid3"
 * - or joinfields = "none"
 *
 * @param {string} subID
 * @param {string} listfield
 * @param {string} joinfields
 * @param {string} type
 * @param {string} area
 * @returns {Promise<*>}
 */
function getCodeGlueOptions_server(subID, listfield, joinfields, type, area) {
	if (
		typeof token === 'undefined' ||
		!_isSet(token) ||
		!_isSet(listfield) ||
		!_isSet(type) ||
		!_isSet(area)
	) {
		return Promise.resolve(null);
	}

	var getUrl = JRouter('index.php?option=com_componentbuilder&task=ajax.getCodeGlueOptions&format=json');
	var params = new URLSearchParams();

	params.append(token, '1');
	params.append('listfield', listfield);
	params.append('joinfields', _isSet(joinfields) ? joinfields : 'none');
	params.append('type', type);
	params.append('area', area);

	abortCodeGlueOptionsRequest(subID);

	var controller = new AbortController();
	codeGlueOptionsControllers[subID] = controller;

	return fetch(getUrl + '&' + params.toString(), {
		method: 'GET',
		headers: {
			'Accept': 'application/json',
			'X-Requested-With': 'XMLHttpRequest'
		},
		credentials: 'same-origin',
		signal: controller.signal
	})
		.then(function(response) {
			if (!response.ok) {
				throw new Error('Server responded with status ' + response.status);
			}

			return response.json();
		})
		.finally(function() {
			if (codeGlueOptionsControllers[subID] === controller) {
				delete codeGlueOptionsControllers[subID];
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
