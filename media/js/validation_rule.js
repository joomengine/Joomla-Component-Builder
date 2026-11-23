/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */




document.addEventListener('DOMContentLoaded', () => {

	// get rule name
	const nameField = document.querySelector('#jform_name');
	if (nameField) {
		const ruleName = nameField.value || '';
		checkRuleName(ruleName);
	}

	// get inherit value
	const inheritField = document.querySelector('#jform_inherit');
	const phpField = document.querySelector('#jform_php');

	if (inheritField && !phpField) {
		const rulefilename = inheritField.value || '';
		getExistingValidationRuleCode(rulefilename);
	}

	// delayed load
	setTimeout(getEditCustomCodeButtons, 300);
});


// ----------------------------
// AJAX HELPERS (FETCH)
// ----------------------------

function buildRequestParams(params) {
	const search = new URLSearchParams();

	if (typeof token !== 'undefined' && token.length > 0) {
		search.append(token, '1');
	}

	Object.entries(params).forEach(([key, value]) => {
		search.append(key, value);
	});

	return search.toString();
}

function fetchJSON(url, params = {}) {
	const query = buildRequestParams(params);
	return fetch(url + '&' + query, {
		method: 'GET',
		headers: {
			'Accept': 'application/json'
		}
	}).then(response => {
		if (!response.ok) {
			throw new Error('Network response was not ok');
		}
		return response.json();
	});
}


// ----------------------------
// SERVER CALLS
// ----------------------------

function getExistingValidationRuleCode_server(rulefilename) {
	const url = JRouter("index.php?option=com_componentbuilder&task=ajax.getExistingValidationRuleCode&format=json&raw=true");

	if (!rulefilename) return Promise.resolve({});

	return fetchJSON(url, { name: rulefilename });
}

function checkRuleName_server(ruleName, ide) {
	const url = JRouter("index.php?option=com_componentbuilder&task=ajax.checkRuleName&format=json&raw=true");

	return fetchJSON(url, {
		name: ruleName,
		id: ide
	});
}


// ----------------------------
// UI HELPERS (JOOMLA DIALOG)
// ----------------------------

function getDialog() {
	return window.customElements?.get('joomla-dialog') || null;
}

function showAlert(message) {
	const Dialog = getDialog();

	if (Dialog) {
		Dialog.alert(message);
	} else {
		alert(message); // fallback only
	}
}

function showConfirm(message) {
	const Dialog = getDialog();

	if (Dialog) {
		return Dialog.confirm(message);
	}

	return Promise.resolve(confirm(message)); // fallback
}


// ----------------------------
// CORE LOGIC
// ----------------------------

function getExistingValidationRuleCode(rulefilename) {
	getExistingValidationRuleCode_server(rulefilename)
		.then(result => {
			if (result.values) {
				const textarea = document.querySelector('textarea#jform_php');
				if (textarea) {
					textarea.value = result.values;
				}
			}
		})
		.catch(console.error);
}

function checkRuleName(ruleName) {

	const nameField = document.querySelector('#jform_name');

	if (!ruleName || ruleName.length <= 2) {
		showAlert(Joomla.Text._('COM_COMPONENTBUILDER_YOU_MUST_ADD_AN_UNIQUE_VALIDATION_RULE_NAME'));
		if (nameField) nameField.value = '';
		return;
	}

	let ide = document.querySelector('#jform_id')?.value || 0;
	if (ide == 0) ide = -1;

	checkRuleName_server(ruleName, ide)
		.then(result => {

			if (result.name && result.message) {

				showAlert(result.message);

				if (nameField) nameField.value = result.name;

				// continue flow
				usedin(result.name, ide);

			} else if (result.message) {

				showAlert(result.message);

				if (nameField) nameField.value = '';

			} else {

				showAlert(Joomla.Text._('COM_COMPONENTBUILDER_VALIDATION_RULE_NAME_ALREADY_TAKEN_PLEASE_TRY_AGAIN'));

				if (nameField) nameField.value = '';
			}
		})
		.catch(() => {
			showAlert(Joomla.Text._('COM_COMPONENTBUILDER_VALIDATION_RULE_NAME_ALREADY_TAKEN_PLEASE_TRY_AGAIN'));
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
