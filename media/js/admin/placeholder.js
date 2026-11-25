/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */




document.addEventListener('DOMContentLoaded', function () {
	document.getElementById('placedin').style.display = '';
	var placeholderName = document.getElementById('jform_target').value;
	// check if this function name is taken
	checkPlaceholderName(placeholderName);
});

function setPlaceholderName() {
	// noting for now (we may add more functionality later)
}

function checkPlaceholderName(placeholderName) {
	if (placeholderName.length > 2) {
		var ide = document.getElementById('jform_id').value;
		if (ide == 0) {
			ide = -1;
		}
		checkPlaceholderName_server(placeholderName, ide).then(function(result) {
			if(result.name && result.message){
				// show notice that placeholderName is okay
				showNotice(result.message, result.status);
				document.getElementById('jform_target').value = result.name;
				// now start search for where the function is used
				placedin(result.name, ide);
			} else if(result.message){
				// show notice that placeholderName is not okay
				showNotice(result.message, result.status);
				document.getElementById('jform_target').value = '';
			} else {
				// set an error that message was not send
				showNotice(Joomla.Text._('COM_COMPONENTBUILDER_PLACEHOLDER_ALREADY_TAKEN_PLEASE_TRY_AGAIN'), 'danger');
				document.getElementById('jform_target').value = '';
			}
			// set custom code placeholder
			setPlaceholderName();
		});
	} else {
		// set an error that message was not send
		showNotice(Joomla.Text._('COM_COMPONENTBUILDER_YOU_MUST_ADD_AN_UNIQUE_PLACEHOLDER'), 'danger');
		document.getElementById('jform_target').value = '';
		// set custom code placeholder
		setPlaceholderName();
	}
}
// check Placeholder
function checkPlaceholderName_server(placeholderName, ide){
	var getUrl = 'index.php?option=com_componentbuilder&task=ajax.checkPlaceholderName&raw=true&format=json';
	if(token.length > 0){
		var request = 'token='+token+'&placeholderName='+placeholderName+'&id='+ide;
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
 * For each target, it calls placedin_server() concurrently and updates the UI based on the responses.
 *
 * @param {string} placeholder - The placeholder parameter to send to the server.
 * @param {string|number} ide - The identifier to send.
 */
function placedin(placeholder, ide) {
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
	hideElement('before-placedin');
	hideElement('note-placedin-not');
	hideElement('note-placedin-found');
	showElement('loading-placedin');

	// Create a targets array of 30 integers (0 to 29).
	const targets = Array.from({ length: 30 }, (_, i) => i);

	// Map each target to a promise that makes an AJAX call.
	const promises = targets.map((target) => {
		return placedin_server(placeholder, ide, target)
			.then((used) => {
				if (used && used.in) {
					// Check if the element with id "placedin-{used.id}" exists.
					let funcElement = document.getElementById('placedin-' + used.id);
					if (!funcElement) {
						// Create the main container div.
						funcElement = document.createElement('div');
						funcElement.id = 'placedin-' + used.id;

						// Create the header element with the area name.
						const header = document.createElement('h2');
						header.textContent = used.area_name;

						// Create the inner div element that will contain the result.
						const innerDiv = document.createElement('div');
						innerDiv.id = 'area-' + used.id;

						// Append the header and inner div to the main element.
						funcElement.appendChild(header);
						funcElement.appendChild(innerDiv);

						// Append this element to the container with id "placedin-targets".
						const container = document.getElementById('placedin-targets');
						if (container) {
							container.appendChild(funcElement);
						} else {
							console.error(
								"Container with id 'placedin-targets' not found. Appending to document.body instead."
							);
						}
					}

					// Ensure the element is visible.
					showElement('placedin-' + used.id);

					// Update the inner div's content with the response.
					const areaEl = document.getElementById('area-' + used.id);
					if (areaEl) {
						areaEl.innerHTML = used.in;
					}

					// Notify the user.
					showNotice(used.in, 'success');
					found = true;
				} else {
					// If no valid response, hide the element with id "placedin-{target}".
					hideElement('placedin-' + target);
				}
			})
			.catch((error) => {
				console.error('Error in placedin_server for target ' + target + ':', error);
			});
	});

	// Once all Ajax calls are completed, update the UI accordingly.
	Promise.all(promises).then(() => {
		hideElement('loading-placedin');
		if (found) {
			showElement('note-placedin-found');
		} else {
			showElement('note-placedin-not');
		}
	});
}

/**
 * Sends an AJAX GET request to the server with the specified parameters.
 * The function builds a URL with query parameters and returns a promise
 * that resolves with the JSON response.
 *
 * @param {string} placeholder - The placeholder to send with the request.
 * @param {string|number} ide - The identifier to send.
 * @param {string|number} target - The target placeholder to send.
 *
 * @returns {Promise<Object>} - A promise that resolves to the JSON response.
 */
function placedin_server(placeholder, ide, target) {
	// Check if the global variable 'token' exists and has a non-empty placeholder.
	// 'token', 'functioName', and 'return_here' are assumed to be defined elsewhere in your code.
	if (token && token.length > 0) {
		var request =
			token +
			'=1&placeholder=' +
			placeholder +
			'&id=' +
			ide +
			'&target=' +
			target +
			'&raw=true&return_here=' +
			return_here;
	} else {
		console.error(
			'There was a issue with the placeholders passed to the [placedin_server] method and we could not make the Ajax call.'
		);
		return Promise.reject(new Error('Invalid token or parameters.'));
	}

	// Base URL for the AJAX request.
	const baseUrl = `index.php?option=com_componentbuilder&task=ajax.placedin&format=json&${request}`;

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
