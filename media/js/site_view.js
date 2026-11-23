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
var updateSiteViewConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateSiteViewConditions()
{
	if (updateSiteViewConditionsBaseline === null)
	{
		updateSiteViewConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_php_view-lbl","#jform_php_jview_display-lbl","#jform_php_jview-lbl","#jform_php_document-lbl","#jform_css_document-lbl","#jform_javascript_file-lbl","#jform_js_document-lbl","#jform_css-lbl","#jform_ajax_input-lbl","#jform_php_ajaxmethod-lbl","#jform_custom_button-lbl","#jform_php_controller-lbl","#jform_php_model-lbl",".note_custom_toolbar_placeholder"];
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
					updateSiteViewConditionsBaseline.containers.push({element: element, display: element.style.display});
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
				updateSiteViewConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateSiteViewConditionsBaseline.containers.length; index++)
	{
		var baseline = updateSiteViewConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateSiteViewConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var add_php_view_vvvvvyv = jQuery("#jform_add_php_view input[type='radio']:checked").val();
	vvvvvyv(add_php_view_vvvvvyv);

	var add_php_jview_display_vvvvvyw = jQuery("#jform_add_php_jview_display input[type='radio']:checked").val();
	vvvvvyw(add_php_jview_display_vvvvvyw);

	var add_php_jview_vvvvvyx = jQuery("#jform_add_php_jview input[type='radio']:checked").val();
	vvvvvyx(add_php_jview_vvvvvyx);

	var add_php_document_vvvvvyy = jQuery("#jform_add_php_document input[type='radio']:checked").val();
	vvvvvyy(add_php_document_vvvvvyy);

	var add_css_document_vvvvvyz = jQuery("#jform_add_css_document input[type='radio']:checked").val();
	vvvvvyz(add_css_document_vvvvvyz);

	var add_javascript_file_vvvvvza = jQuery("#jform_add_javascript_file input[type='radio']:checked").val();
	vvvvvza(add_javascript_file_vvvvvza);

	var add_js_document_vvvvvzb = jQuery("#jform_add_js_document input[type='radio']:checked").val();
	vvvvvzb(add_js_document_vvvvvzb);

	var add_css_vvvvvzc = jQuery("#jform_add_css input[type='radio']:checked").val();
	vvvvvzc(add_css_vvvvvzc);

	var add_php_ajax_vvvvvzd = jQuery("#jform_add_php_ajax input[type='radio']:checked").val();
	vvvvvzd(add_php_ajax_vvvvvzd);

	var add_custom_button_vvvvvze = jQuery("#jform_add_custom_button input[type='radio']:checked").val();
	vvvvvze(add_custom_button_vvvvvze);

	var button_position_vvvvvzf = jQuery("#jform_button_position").val();
	vvvvvzf(button_position_vvvvvzf);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateSiteViewConditions);

// the vvvvvyv function
function vvvvvyv(add_php_view_vvvvvyv)
{
	// set the function logic
	if (add_php_view_vvvvvyv == Number('1'))
	{
		jQuery('#jform_php_view-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_view-lbl').closest('.control-group').hide();
	}
}

// the vvvvvyw function
function vvvvvyw(add_php_jview_display_vvvvvyw)
{
	// set the function logic
	if (add_php_jview_display_vvvvvyw == Number('1'))
	{
		jQuery('#jform_php_jview_display-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_jview_display-lbl').closest('.control-group').hide();
	}
}

// the vvvvvyx function
function vvvvvyx(add_php_jview_vvvvvyx)
{
	// set the function logic
	if (add_php_jview_vvvvvyx == Number('1'))
	{
		jQuery('#jform_php_jview-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_jview-lbl').closest('.control-group').hide();
	}
}

// the vvvvvyy function
function vvvvvyy(add_php_document_vvvvvyy)
{
	// set the function logic
	if (add_php_document_vvvvvyy == Number('1'))
	{
		jQuery('#jform_php_document-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_document-lbl').closest('.control-group').hide();
	}
}

// the vvvvvyz function
function vvvvvyz(add_css_document_vvvvvyz)
{
	// set the function logic
	if (add_css_document_vvvvvyz == Number('1'))
	{
		jQuery('#jform_css_document-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_css_document-lbl').closest('.control-group').hide();
	}
}

// the vvvvvza function
function vvvvvza(add_javascript_file_vvvvvza)
{
	// set the function logic
	if (add_javascript_file_vvvvvza == Number('1'))
	{
		jQuery('#jform_javascript_file-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_javascript_file-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzb function
function vvvvvzb(add_js_document_vvvvvzb)
{
	// set the function logic
	if (add_js_document_vvvvvzb == Number('1'))
	{
		jQuery('#jform_js_document-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_js_document-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzc function
function vvvvvzc(add_css_vvvvvzc)
{
	// set the function logic
	if (add_css_vvvvvzc == Number('1'))
	{
		jQuery('#jform_css-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_css-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzd function
function vvvvvzd(add_php_ajax_vvvvvzd)
{
	// set the function logic
	if (add_php_ajax_vvvvvzd == Number('1'))
	{
		jQuery('#jform_ajax_input-lbl').closest('.control-group').show();
		jQuery('#jform_php_ajaxmethod-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_ajax_input-lbl').closest('.control-group').hide();
		jQuery('#jform_php_ajaxmethod-lbl').closest('.control-group').hide();
	}
}

// the vvvvvze function
function vvvvvze(add_custom_button_vvvvvze)
{
	// set the function logic
	if (add_custom_button_vvvvvze == Number('1'))
	{
		jQuery('#jform_custom_button-lbl').closest('.control-group').show();
		jQuery('#jform_php_controller-lbl').closest('.control-group').show();
		jQuery('#jform_php_model-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_custom_button-lbl').closest('.control-group').hide();
		jQuery('#jform_php_controller-lbl').closest('.control-group').hide();
		jQuery('#jform_php_model-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzf function
function vvvvvzf(button_position_vvvvvzf)
{
	if (isSet(button_position_vvvvvzf) && button_position_vvvvvzf.constructor !== Array)
	{
		var temp_vvvvvzf = button_position_vvvvvzf;
		var button_position_vvvvvzf = [];
		button_position_vvvvvzf.push(temp_vvvvvzf);
	}
	else if (!isSet(button_position_vvvvvzf))
	{
		var button_position_vvvvvzf = [];
	}
	var button_position = button_position_vvvvvzf.some(button_position_vvvvvzf_SomeFunc);


	// set this function logic
	if (button_position)
	{
		jQuery('.note_custom_toolbar_placeholder').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_custom_toolbar_placeholder').closest('.control-group').hide();
	}
}

// the vvvvvzf Some function
function button_position_vvvvvzf_SomeFunc(button_position_vvvvvzf)
{
	// set the function logic
	if (button_position_vvvvvzf == Number('5'))
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
	// get the linked details
	getLinked();
	// check and load all the custom code edit buttons
	getEditCustomCodeButtons();
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

function getLinked() {
	getCodeFrom_server(1, 'type', 'type', 'getLinked').then(function(result) {
		if (result.error) {
			console.error(result.error);
		} else if (result) {
			document.getElementById('display_linked_to').innerHTML = result;
		}
	});
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

function getDynamicValuesServer(dynamicId) {
    var getUrl = 'index.php?option=com_componentbuilder&task=ajax.getDynamicValues&raw=true&format=json';
    if (token.length > 0 && (dynamicId > 0 || dynamicId.length > 0)) {
        var request = token + '=1&view=site_view&id=' + dynamicId;
    }

    return fetch(getUrl + '&' + request, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json());
}

function getDynamicValues(id) {
    getDynamicValuesServer(id).then(function(result) {
        if (result) {
            var dynamicValuesElement = document.getElementById('dynamic_values');
            if (dynamicValuesElement) {
                dynamicValuesElement.remove();
            }
            document.querySelector('.dynamic_values').insertAdjacentHTML('beforeend', '<div id="dynamic_values">' + result + '</div>');

            // Event listener for code blocks
            document.querySelectorAll("code").forEach(function(codeBlock) {
                codeBlock.addEventListener("click", function() {
                    codeBlock.selText(); // Call the custom selText function
                    codeBlock.classList.add("selected");  // Add the "selected" class
                });
            });
        }
    }).catch(function(error) {
        console.error('Error fetching dynamic values:', error);
    });
}

function getLayoutDetails_server(id) {
    var getUrl = JRouter("index.php?option=com_componentbuilder&task=ajax.getLayoutDetails&format=json&raw=true&vdm=" + vastDevMod);
    var request = '';

    // Ensure token and id are present
    if (token.length > 0 && id > 0) {
        request = token + '=1&id=' + id;
    }

    // Return a fetch promise (fetch does not support JSONP, so I assume the server can return JSON)
    return fetch(getUrl + '&' + request, {
        method: 'GET'
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error("Network response was not ok");
        }
        return response.json();  // Assuming the server returns JSON
    });
}

function getLayoutDetails(id) {
    getLayoutDetails_server(id)
        .then(function(result) {
            if (result) {
                document.querySelector('#details').insertAdjacentHTML('beforeend', result);

                // Re-enable code block text selection functionality
                document.querySelectorAll("code").forEach(function(codeBlock) {
                    codeBlock.addEventListener("click", function() {
                        codeBlock.selText();
                        codeBlock.classList.add("selected");
                    });
                });
            }
        })
        .catch(function(error) {
            console.error('There was a problem with the fetch operation:', error);
        });
}


function getTemplateDetails(id) {
    getCodeFrom_server(id, 'type', 'type', 'templateDetails').then(function(result) {
        if (result) {
            document.querySelector('#details').insertAdjacentHTML('beforeend', result);

            // Re-enable code block text selection functionality
            document.querySelectorAll("code").forEach(function(codeBlock) {
                codeBlock.addEventListener("click", function() {
                    codeBlock.selText();
                    codeBlock.classList.add("selected");
                });
            });
        }
    });
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
