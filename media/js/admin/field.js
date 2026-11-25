/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


/**
 * Compatibility for custom scripts using the retired required-field helper.
 *
 * @param  {string}  name    The legacy field name, ignored.
 * @param  {number}  status  The legacy required state, ignored.
 *
 * @return {void}
 * @deprecated 6.2.1 Required fields are decided by server-side conditions.
 */
function updateFieldRequired(name, status)
{
}
// Original display and required state are captured before conditions run.
var updateFieldConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateFieldConditions()
{
	if (updateFieldConditionsBaseline === null)
	{
		updateFieldConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_datalenght_other","#jform_datadefault_other","#jform_datadefault","#jform_indexes","#jform_datalenght","#jform_initiator_on_get_model","#jform_initiator_on_save_model",".note_expert_field_save_mode","#jform_on_get_model_field","#jform_on_save_model_field","#jform_css_view-lbl","#jform_css_views-lbl","#jform_javascript_view_footer-lbl","#jform_javascript_views_footer-lbl","#jform_datatype","#jform_store","#jform_null_switch"];
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
					updateFieldConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = ["datalenght_other","datadefault_other","indexes","datalenght","on_get_model_field","on_save_model_field","datatype","store","null_switch"];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateFieldConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateFieldConditionsBaseline.containers.length; index++)
	{
		var baseline = updateFieldConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateFieldConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var datalenght_vvvvwbg = jQuery("#jform_datalenght").val();
	vvvvwbg(datalenght_vvvvwbg);

	var datadefault_vvvvwbh = jQuery("#jform_datadefault").val();
	vvvvwbh(datadefault_vvvvwbh);

	var datatype_vvvvwbi = jQuery("#jform_datatype").val();
	vvvvwbi(datatype_vvvvwbi);

	var datatype_vvvvwbj = jQuery("#jform_datatype").val();
	vvvvwbj(datatype_vvvvwbj);

	var store_vvvvwbm = jQuery("#jform_store").val();
	vvvvwbm(store_vvvvwbm);

	var add_css_view_vvvvwbn = jQuery("#jform_add_css_view input[type='radio']:checked").val();
	vvvvwbn(add_css_view_vvvvwbn);

	var add_css_views_vvvvwbo = jQuery("#jform_add_css_views input[type='radio']:checked").val();
	vvvvwbo(add_css_views_vvvvwbo);

	var add_javascript_view_footer_vvvvwbp = jQuery("#jform_add_javascript_view_footer input[type='radio']:checked").val();
	vvvvwbp(add_javascript_view_footer_vvvvwbp);

	var add_javascript_views_footer_vvvvwbq = jQuery("#jform_add_javascript_views_footer input[type='radio']:checked").val();
	vvvvwbq(add_javascript_views_footer_vvvvwbq);

	var fieldtype_vvvvwbr = jQuery("#jform_fieldtype").val();
	vvvvwbr(fieldtype_vvvvwbr);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateFieldConditions);

// the vvvvwbg function
function vvvvwbg(datalenght_vvvvwbg)
{
	if (isSet(datalenght_vvvvwbg) && datalenght_vvvvwbg.constructor !== Array)
	{
		var temp_vvvvwbg = datalenght_vvvvwbg;
		var datalenght_vvvvwbg = [];
		datalenght_vvvvwbg.push(temp_vvvvwbg);
	}
	else if (!isSet(datalenght_vvvvwbg))
	{
		var datalenght_vvvvwbg = [];
	}
	var datalenght = datalenght_vvvvwbg.some(datalenght_vvvvwbg_SomeFunc);


	// set this function logic
	if (datalenght)
	{
		jQuery('#jform_datalenght_other').closest('.control-group').show();
		// Add required attributes to datalenght_other.
		jQuery('#jform_datalenght_other').prop('required', true);
		jQuery('#jform_datalenght_other').attr('aria-required', true);
		jQuery('#jform_datalenght_other').addClass('required');
	}
	else
	{
		jQuery('#jform_datalenght_other').closest('.control-group').hide();
		// Remove required attributes from datalenght_other.
		jQuery('#jform_datalenght_other').removeAttr('required');
		jQuery('#jform_datalenght_other').removeAttr('aria-required');
		jQuery('#jform_datalenght_other').removeClass('required');
	}
}

// the vvvvwbg Some function
function datalenght_vvvvwbg_SomeFunc(datalenght_vvvvwbg)
{
	// set the function logic
	if (datalenght_vvvvwbg == 'Other')
	{
		return true;
	}
	return false;
}

// the vvvvwbh function
function vvvvwbh(datadefault_vvvvwbh)
{
	if (isSet(datadefault_vvvvwbh) && datadefault_vvvvwbh.constructor !== Array)
	{
		var temp_vvvvwbh = datadefault_vvvvwbh;
		var datadefault_vvvvwbh = [];
		datadefault_vvvvwbh.push(temp_vvvvwbh);
	}
	else if (!isSet(datadefault_vvvvwbh))
	{
		var datadefault_vvvvwbh = [];
	}
	var datadefault = datadefault_vvvvwbh.some(datadefault_vvvvwbh_SomeFunc);


	// set this function logic
	if (datadefault)
	{
		jQuery('#jform_datadefault_other').closest('.control-group').show();
		// Add required attributes to datadefault_other.
		jQuery('#jform_datadefault_other').prop('required', true);
		jQuery('#jform_datadefault_other').attr('aria-required', true);
		jQuery('#jform_datadefault_other').addClass('required');
	}
	else
	{
		jQuery('#jform_datadefault_other').closest('.control-group').hide();
		// Remove required attributes from datadefault_other.
		jQuery('#jform_datadefault_other').removeAttr('required');
		jQuery('#jform_datadefault_other').removeAttr('aria-required');
		jQuery('#jform_datadefault_other').removeClass('required');
	}
}

// the vvvvwbh Some function
function datadefault_vvvvwbh_SomeFunc(datadefault_vvvvwbh)
{
	// set the function logic
	if (datadefault_vvvvwbh == 'Other')
	{
		return true;
	}
	return false;
}

// the vvvvwbi function
function vvvvwbi(datatype_vvvvwbi)
{
	if (isSet(datatype_vvvvwbi) && datatype_vvvvwbi.constructor !== Array)
	{
		var temp_vvvvwbi = datatype_vvvvwbi;
		var datatype_vvvvwbi = [];
		datatype_vvvvwbi.push(temp_vvvvwbi);
	}
	else if (!isSet(datatype_vvvvwbi))
	{
		var datatype_vvvvwbi = [];
	}
	var datatype = datatype_vvvvwbi.some(datatype_vvvvwbi_SomeFunc);


	// set this function logic
	if (datatype)
	{
		jQuery('#jform_datadefault').closest('.control-group').show();
		jQuery('#jform_indexes').closest('.control-group').show();
		// Add required attributes to indexes.
		jQuery('#jform_indexes').prop('required', true);
		jQuery('#jform_indexes').attr('aria-required', true);
		jQuery('#jform_indexes').addClass('required');
	}
	else
	{
		jQuery('#jform_datadefault').closest('.control-group').hide();
		jQuery('#jform_indexes').closest('.control-group').hide();
		// Remove required attributes from indexes.
		jQuery('#jform_indexes').removeAttr('required');
		jQuery('#jform_indexes').removeAttr('aria-required');
		jQuery('#jform_indexes').removeClass('required');
	}
}

// the vvvvwbi Some function
function datatype_vvvvwbi_SomeFunc(datatype_vvvvwbi)
{
	// set the function logic
	if (datatype_vvvvwbi == 'CHAR' || datatype_vvvvwbi == 'VARCHAR' || datatype_vvvvwbi == 'DATETIME' || datatype_vvvvwbi == 'DATE' || datatype_vvvvwbi == 'TIME' || datatype_vvvvwbi == 'INT' || datatype_vvvvwbi == 'TINYINT' || datatype_vvvvwbi == 'BIGINT' || datatype_vvvvwbi == 'FLOAT' || datatype_vvvvwbi == 'DECIMAL' || datatype_vvvvwbi == 'DOUBLE')
	{
		return true;
	}
	return false;
}

// the vvvvwbj function
function vvvvwbj(datatype_vvvvwbj)
{
	if (isSet(datatype_vvvvwbj) && datatype_vvvvwbj.constructor !== Array)
	{
		var temp_vvvvwbj = datatype_vvvvwbj;
		var datatype_vvvvwbj = [];
		datatype_vvvvwbj.push(temp_vvvvwbj);
	}
	else if (!isSet(datatype_vvvvwbj))
	{
		var datatype_vvvvwbj = [];
	}
	var datatype = datatype_vvvvwbj.some(datatype_vvvvwbj_SomeFunc);


	// set this function logic
	if (datatype)
	{
		jQuery('#jform_datalenght').closest('.control-group').show();
		// Add required attributes to datalenght.
		jQuery('#jform_datalenght').prop('required', true);
		jQuery('#jform_datalenght').attr('aria-required', true);
		jQuery('#jform_datalenght').addClass('required');
	}
	else
	{
		jQuery('#jform_datalenght').closest('.control-group').hide();
		// Remove required attributes from datalenght.
		jQuery('#jform_datalenght').removeAttr('required');
		jQuery('#jform_datalenght').removeAttr('aria-required');
		jQuery('#jform_datalenght').removeClass('required');
	}
}

// the vvvvwbj Some function
function datatype_vvvvwbj_SomeFunc(datatype_vvvvwbj)
{
	// set the function logic
	if (datatype_vvvvwbj == 'CHAR' || datatype_vvvvwbj == 'VARCHAR' || datatype_vvvvwbj == 'INT' || datatype_vvvvwbj == 'TINYINT' || datatype_vvvvwbj == 'BIGINT' || datatype_vvvvwbj == 'FLOAT' || datatype_vvvvwbj == 'DECIMAL' || datatype_vvvvwbj == 'DOUBLE')
	{
		return true;
	}
	return false;
}

// the vvvvwbm function
function vvvvwbm(store_vvvvwbm)
{
	if (isSet(store_vvvvwbm) && store_vvvvwbm.constructor !== Array)
	{
		var temp_vvvvwbm = store_vvvvwbm;
		var store_vvvvwbm = [];
		store_vvvvwbm.push(temp_vvvvwbm);
	}
	else if (!isSet(store_vvvvwbm))
	{
		var store_vvvvwbm = [];
	}
	var store = store_vvvvwbm.some(store_vvvvwbm_SomeFunc);


	// set this function logic
	if (store)
	{
		jQuery('#jform_initiator_on_get_model').closest('.control-group').show();
		jQuery('#jform_initiator_on_save_model').closest('.control-group').show();
		jQuery('.note_expert_field_save_mode').closest('.control-group').show();
		jQuery('#jform_on_get_model_field').closest('.control-group').show();
		// Add required attributes to on_get_model_field.
		jQuery('#jform_on_get_model_field').prop('required', true);
		jQuery('#jform_on_get_model_field').attr('aria-required', true);
		jQuery('#jform_on_get_model_field').addClass('required');
		jQuery('#jform_on_save_model_field').closest('.control-group').show();
		// Add required attributes to on_save_model_field.
		jQuery('#jform_on_save_model_field').prop('required', true);
		jQuery('#jform_on_save_model_field').attr('aria-required', true);
		jQuery('#jform_on_save_model_field').addClass('required');
	}
	else
	{
		jQuery('#jform_initiator_on_get_model').closest('.control-group').hide();
		jQuery('#jform_initiator_on_save_model').closest('.control-group').hide();
		jQuery('.note_expert_field_save_mode').closest('.control-group').hide();
		jQuery('#jform_on_get_model_field').closest('.control-group').hide();
		// Remove required attributes from on_get_model_field.
		jQuery('#jform_on_get_model_field').removeAttr('required');
		jQuery('#jform_on_get_model_field').removeAttr('aria-required');
		jQuery('#jform_on_get_model_field').removeClass('required');
		jQuery('#jform_on_save_model_field').closest('.control-group').hide();
		// Remove required attributes from on_save_model_field.
		jQuery('#jform_on_save_model_field').removeAttr('required');
		jQuery('#jform_on_save_model_field').removeAttr('aria-required');
		jQuery('#jform_on_save_model_field').removeClass('required');
	}
}

// the vvvvwbm Some function
function store_vvvvwbm_SomeFunc(store_vvvvwbm)
{
	// set the function logic
	if (store_vvvvwbm == Number('6'))
	{
		return true;
	}
	return false;
}

// the vvvvwbn function
function vvvvwbn(add_css_view_vvvvwbn)
{
	// set the function logic
	if (add_css_view_vvvvwbn == Number('1'))
	{
		jQuery('#jform_css_view-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_css_view-lbl').closest('.control-group').hide();
	}
}

// the vvvvwbo function
function vvvvwbo(add_css_views_vvvvwbo)
{
	// set the function logic
	if (add_css_views_vvvvwbo == Number('1'))
	{
		jQuery('#jform_css_views-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_css_views-lbl').closest('.control-group').hide();
	}
}

// the vvvvwbp function
function vvvvwbp(add_javascript_view_footer_vvvvwbp)
{
	// set the function logic
	if (add_javascript_view_footer_vvvvwbp == Number('1'))
	{
		jQuery('#jform_javascript_view_footer-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_javascript_view_footer-lbl').closest('.control-group').hide();
	}
}

// the vvvvwbq function
function vvvvwbq(add_javascript_views_footer_vvvvwbq)
{
	// set the function logic
	if (add_javascript_views_footer_vvvvwbq == Number('1'))
	{
		jQuery('#jform_javascript_views_footer-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_javascript_views_footer-lbl').closest('.control-group').hide();
	}
}

// the vvvvwbr function
function vvvvwbr(fieldtype_vvvvwbr)
{
	if (isSet(fieldtype_vvvvwbr) && fieldtype_vvvvwbr.constructor !== Array)
	{
		var temp_vvvvwbr = fieldtype_vvvvwbr;
		var fieldtype_vvvvwbr = [];
		fieldtype_vvvvwbr.push(temp_vvvvwbr);
	}
	else if (!isSet(fieldtype_vvvvwbr))
	{
		var fieldtype_vvvvwbr = [];
	}
	var fieldtype = fieldtype_vvvvwbr.some(fieldtype_vvvvwbr_SomeFunc);


	// set this function logic
	if (fieldtype)
	{
		jQuery('#jform_datadefault').closest('.control-group').hide();
		jQuery('#jform_datalenght').closest('.control-group').hide();
		// Remove required attributes from datalenght.
		jQuery('#jform_datalenght').removeAttr('required');
		jQuery('#jform_datalenght').removeAttr('aria-required');
		jQuery('#jform_datalenght').removeClass('required');
		jQuery('#jform_datatype').closest('.control-group').hide();
		// Remove required attributes from datatype.
		jQuery('#jform_datatype').removeAttr('required');
		jQuery('#jform_datatype').removeAttr('aria-required');
		jQuery('#jform_datatype').removeClass('required');
		jQuery('#jform_store').closest('.control-group').hide();
		// Remove required attributes from store.
		jQuery('#jform_store').removeAttr('required');
		jQuery('#jform_store').removeAttr('aria-required');
		jQuery('#jform_store').removeClass('required');
		jQuery('#jform_indexes').closest('.control-group').hide();
		// Remove required attributes from indexes.
		jQuery('#jform_indexes').removeAttr('required');
		jQuery('#jform_indexes').removeAttr('aria-required');
		jQuery('#jform_indexes').removeClass('required');
		jQuery('#jform_initiator_on_get_model').closest('.control-group').hide();
		jQuery('#jform_initiator_on_save_model').closest('.control-group').hide();
		jQuery('.note_expert_field_save_mode').closest('.control-group').hide();
		jQuery('#jform_null_switch').closest('.control-group').hide();
		// Remove required attributes from null_switch.
		jQuery('#jform_null_switch').removeAttr('required');
		jQuery('#jform_null_switch').removeAttr('aria-required');
		jQuery('#jform_null_switch').removeClass('required');
		jQuery('#jform_on_get_model_field').closest('.control-group').hide();
		// Remove required attributes from on_get_model_field.
		jQuery('#jform_on_get_model_field').removeAttr('required');
		jQuery('#jform_on_get_model_field').removeAttr('aria-required');
		jQuery('#jform_on_get_model_field').removeClass('required');
		jQuery('#jform_on_save_model_field').closest('.control-group').hide();
		// Remove required attributes from on_save_model_field.
		jQuery('#jform_on_save_model_field').removeAttr('required');
		jQuery('#jform_on_save_model_field').removeAttr('aria-required');
		jQuery('#jform_on_save_model_field').removeClass('required');
		jQuery('#jform_datadefault_other').closest('.control-group').hide();
		// Remove required attributes from datadefault_other.
		jQuery('#jform_datadefault_other').removeAttr('required');
		jQuery('#jform_datadefault_other').removeAttr('aria-required');
		jQuery('#jform_datadefault_other').removeClass('required');
		jQuery('#jform_datalenght_other').closest('.control-group').hide();
		// Remove required attributes from datalenght_other.
		jQuery('#jform_datalenght_other').removeAttr('required');
		jQuery('#jform_datalenght_other').removeAttr('aria-required');
		jQuery('#jform_datalenght_other').removeClass('required');
	}
}

// the vvvvwbr Some function
function fieldtype_vvvvwbr_SomeFunc(fieldtype_vvvvwbr)
{
	// set the function logic
	if (fieldtype_vvvvwbr == 'f9ecacd0-8481-4157-8c71-d7aaefc2b7c3' || fieldtype_vvvvwbr == '626d0cba-a908-4a4f-a447-96d781aeaa0b')
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



// set properties the options
var propertiesArray = {};
var propertyIdRemoved;

// the options row id key
var rowIdKey = 'properties';

/**
 * Initialize field property and validation UI logic after DOM is ready.
 *
 * This listener replaces jQuery(document).ready() with a pure JavaScript equivalent.
 * It initializes field-type properties, linked fields, validation rules,
 * and dynamic UI behaviors immediately after Joomla's form is rendered.
 *
 * @return {void}
 * @since  3.1.3
 */
document.addEventListener('DOMContentLoaded', () => {
	try {
		// --- Get the current field type value and text ---
		const fieldTypeSelect = document.querySelector('#jform_fieldtype');
		const fieldtype = fieldTypeSelect?.value ?? '';
		const fieldText = fieldTypeSelect?.options[fieldTypeSelect.selectedIndex]?.text?.toLowerCase() ?? '';

		// --- Load field type properties dynamically ---
		if (typeof getFieldTypeProperties === 'function') {
			getFieldTypeProperties(fieldtype, false);
		}

		// --- Load linked details if available ---
		if (typeof getLinked === 'function') {
			getLinked();
		}

		// --- Load and render validation rules table ---
		if (typeof getValidationRulesTable === 'function') {
			getValidationRulesTable();
		}

		// --- Initialize add button for validation rules ---
		if (typeof addButton === 'function') {
			addButton('validation_rule', 'validation_rules_header', 2);
		}

		// --- Check if database input fields should be shown or hidden ---
		if (typeof dbChecker === 'function') {
			dbChecker(fieldText);
		}

		// --- Load and prepare edit custom code buttons ---
		if (typeof getEditCustomCodeButtons === 'function') {
			getEditCustomCodeButtons();
		}

		console.debug('[Init] Field property/validation logic initialized successfully.');
	} catch (error) {
		console.error('[Init] Initialization error:', error);
	}
});

/**
 * Handle field property selection and dynamically update related description/value fields.
 *
 * This function checks for duplicate property selections, resets invalid ones,
 * updates dependent fields, and fetches the property description and value
 * from the server via getFieldPropertyDesc_server().
 *
 * @param  {HTMLElement} field       The select field element triggering the change.
 * @param  {string}      targetForm  The form context ("properties" or another form name).
 *
 * @return {Promise<void>}           Resolves when updates and server fetch are complete.
 * @since  3.1.3
 */
async function getFieldPropertyDesc(field, targetForm) {
	if (!field) {
		console.debug('[getFieldPropertyDesc] Missing field element.');
		return;
	}

	// Get the ID and property value
	const id = field.id;
	const property = field.value ?? '';

	// Split the ID into parts (e.g. field__desc)
	const target = id.split('__');

	// Check for duplicate properties
	if (typeof propertyIsSet === 'function' && propertyIsSet(property, id, targetForm)) {
		// Reset the selection
		// removeCurrentSubformRow(field); TODO: the option to remove the row
		unselectChoicesFieldValue(`#${id}`);

		// Show Joomla dialog warning and auto-close
		const message = Joomla?.Text?._('Property already selected, try another.')
			|| 'Property already selected, try another.';

		// Ensure Joomla renderMessages API exists
		if (typeof Joomla !== 'undefined' && typeof Joomla.renderMessages === 'function') {
			Joomla.renderMessages({
				warning: [message]
			});
			// Auto-scroll to top so message is visible
			window.scrollTo({ top: 0, behavior: 'smooth' });
		}
		// Final fallback to console
		else {
			console.warn(message);
		}

		// Reset dependent description/value fields
		const descField = document.querySelector(`#${target[0]}__desc`);
		const valueField = document.querySelector(`#${target[0]}__value`);
		if (descField) descField.value = '';
		if (valueField) valueField.value = '';

		return;
	}

	// Trigger property refresh logic
	if (typeof propertyDynamicSet === 'function') {
		propertyDynamicSet();
	}

	// Determine field type (context: 'properties' or 'extra')
	let fieldtype = 'extra';
	if (targetForm === 'properties') {
		const fieldTypeSelect = document.querySelector('#jform_fieldtype');
		if (fieldTypeSelect) {
			fieldtype = fieldTypeSelect.value ?? 'extra';
		}
	}

	// Fetch property description and value from the server
	const result = await getFieldPropertyDesc_server(fieldtype, property);

	// Update dependent fields based on the result
	const descField = document.querySelector(`#${target[0]}__desc`);
	const valueField = document.querySelector(`#${target[0]}__value`);

	if (result && (result.desc || result.value)) {
		if (descField) descField.value = result.desc ?? '';
		if (valueField) valueField.value = result.value ?? '';
	} else {
		if (descField) descField.value = Joomla.Text._('COM_COMPONENTBUILDER_SELECT_A_PROPERTY');
		if (valueField) valueField.value = '';
	}
}

/**
 * Remove the current subform row (where this field lives)
 * using Joomla's native SubformRepeatable instance.
 *
 * @param {HTMLElement} field  The field element inside the subform row.
 *
 * @return {void}
 * @since  5.1.3
 */
function removeCurrentSubformRow(field) {
	if (!(field instanceof HTMLElement)) {
		console.warn('Invalid field element provided.');
		return;
	}

	const row = field.closest('.subform-repeatable-group');
	if (!row) {
		console.warn('No subform row found for field.');
		return;
	}

	row.remove();
}

/**
 * Check whether a given property has already been selected in another field.
 *
 * This function iterates through all property select fields (up to 70 by default)
 * within the given target form, comparing their current values against the provided
 * property value. If a duplicate is found, it returns true; otherwise false.
 *
 * @param  {string}  prop        The property value to check for duplicates.
 * @param  {string}  id          The current field's ID to exclude from the check.
 * @param  {string}  targetForm  The name prefix of the form group (e.g., "properties").
 *
 * @return {boolean}             True if the property is already selected elsewhere, otherwise false.
 * @since  3.1.3
 */
function propertyIsSet(prop, id, targetForm) {
	// Validate input
	if (!prop || !id || !targetForm) {
		console.debug('[propertyIsSet] Missing required parameters:', { prop, id, targetForm });
		return false;
	}

	// Loop through possible fields (max 70 rows by convention)
	for (let i = 0; i < 70; i++) {
		// Construct the expected field ID pattern
		const id_check = `${targetForm}_${targetForm}${i}__name`;
		const field = document.getElementById(id_check);

		// Skip if not found or if this is the same field being checked
		if (!field || id_check === id) {
			continue;
		}

		// Get the currently selected value
		const selectedOption = field.options[field.selectedIndex];
		const tmp = selectedOption ? selectedOption.value : '';

		// If the same property is already selected, return true
		if (tmp === prop) {
			return true;
		}
	}

	// No duplicates found
	return false;
}

/**
 * Load and initialize the field type properties dynamically.
 *
 * This method retrieves subform and database property information for a given
 * field type, updates the Joomla form dynamically, and initializes new form elements.
 * It reproduces the full behavior of the legacy jQuery implementation using pure JavaScript.
 *
 * @param  {string|number}  fieldtype  The field type identifier or ID.
 * @param  {boolean}        db         Whether to load database defaults if present.
 *
 * @return {void}
 * @since  3.1.3
 */
function getFieldTypeProperties(fieldtype, db) {
	getCodeFrom_server(fieldtype, 'type', 'type', 'fieldTypeProperties')
		.then(function (result) {
			// Ensure a valid response with subform data
			if (!result || !result.subform) {
				return;
			}

			// Store the property options list globally
			propertiesArray = result.nameListOptions;

			// --- Remove any previous dynamically added property forms ---
			document.querySelectorAll('.prop_removal').forEach(el => el.remove());

			// --- Remove "select field type" notices if they exist ---
			document.querySelectorAll('.note_select_field_type').forEach(el => {
				const group = el.closest('.control-group');
				if (group) group.remove();
			});

			// --- Locate the control group for filter information ---
			const noteInfo = document.querySelector('.note_filter_information');
			const targetGroup = noteInfo ? noteInfo.closest('.control-group') : null;

			// --- Prepend the new content in the correct order ---
			if (targetGroup) {
				// Prepend the "extra" HTML block
				if (result.extra) {
					targetGroup.insertAdjacentHTML('afterbegin', result.extra);
				}

				// Prepend any textarea fields returned by the AJAX response
				if (result.textarea && Array.isArray(result.textarea)) {
					for (const tField of result.textarea) {
						targetGroup.insertAdjacentHTML('afterbegin', tField);
					}
				}

				// Finally, prepend the subform structure itself
				targetGroup.insertAdjacentHTML('afterbegin', result.subform);
			}

			// --- Initialize row watcher if available ---
			if (typeof rowWatcher === 'function') {
				rowWatcher();
			}

			// --- Trigger "update" events on all subform-repeatable elements ---
			document.querySelectorAll('div.subform-repeatable').forEach(el => {
				el.dispatchEvent(new Event('update', { bubbles: true }));
			});

			// --- Reapply property dynamic list restrictions if available ---
			if (typeof propertyDynamicSet === 'function') {
				propertyDynamicSet();
			}

			// --- Remove any old help block and re-render updated help info ---
			document.querySelectorAll('#help').forEach(el => el.remove());

			const helpNote = document.querySelector('.helpNote');
			if (helpNote) {
				const helpDiv = document.createElement('div');
				helpDiv.id = 'help';
				helpDiv.innerHTML = `${result.description}<br />${result.values_description}`;
				helpNote.appendChild(helpDiv);
			}

			// --- If requested, load database-related default values ---
			if (db && result.database) {
				const dbData = result.database;

				// Update datatype
				if (notEmpty(dbData?.datatype)) {
					setChoicesFieldValue('#jform_datatype', dbData.datatype);
					updateFieldRequired('datatype', 0);
				} else {
					unselectChoicesFieldValue('#jform_datatype');
				}

				// Update datalenght
				if (notEmpty(dbData?.datalenght)) {
					setChoicesFieldValue('#jform_datalenght', dbData.datalenght);
					updateFieldRequired('datalenght', 0);
					if (dbData.datalenght === 'Other' && notEmpty(dbData?.datalenght_other)) {
						setChoicesFieldValue('#jform_datalenght_other', dbData.datalenght_other);
						updateFieldRequired('datalenght_other', 0);
					}
				} else {
					unselectChoicesFieldValue('#jform_datalenght');
					unselectChoicesFieldValue('#jform_datalenght_other');
					updateFieldRequired('datalenght', 1);
					updateFieldRequired('datalenght_other', 1);
				}

				// Update datadefault
				if (notEmpty(dbData?.datadefault)) {
					setChoicesFieldValue('#jform_datadefault', dbData.datadefault);
					updateFieldRequired('datadefault', 0);
					if (dbData.datadefault === 'Other') {
						setChoicesFieldValue('#jform_datadefault_other', dbData.datadefault_other);
						updateFieldRequired('datadefault_other', 0);
					} else {
						updateFieldRequired('datadefault_other', 1);
					}
				} else {
					unselectChoicesFieldValue('#jform_datadefault');
					unselectChoicesFieldValue('#jform_datadefault_other');
					updateFieldRequired('datadefault', 1);
					updateFieldRequired('datadefault_other', 1);
				}

				// Update indexes
				if (notEmpty(dbData?.indexes)) {
					setChoicesFieldValue('#jform_indexes', dbData.indexes);
					// updateFieldRequired('indexes', 0);
				} else {
					// unselectChoicesFieldValue('#jform_indexes');
				}

				// Update store
				if (notEmpty(dbData?.store)) {
					setChoicesFieldValue('#jform_store', dbData.store);
					// updateFieldRequired('store', 0);
				}  else {
					// unselectChoicesFieldValue('#jform_store');
				}
			} else if (db) {
				// Reset datatype
				// unselectChoicesFieldValue('#jform_datatype');

				// Reset datalenght
				// unselectChoicesFieldValue('#jform_datalenght');
				// updateFieldRequired('datalenght', 1);
				// unselectChoicesFieldValue('#jform_datalenght_other');
				// updateFieldRequired('datalenght_other', 1);

				// Reset datadefault
				// unselectChoicesFieldValue('#jform_datadefault');
				// updateFieldRequired('datadefault', 1);
				// unselectChoicesFieldValue('#jform_datadefault_other');
				// updateFieldRequired('datadefault_other', 1);

				// Reset indexes
				// unselectChoicesFieldValue('#jform_indexes');
				// updateFieldRequired('indexes', 1);

				// Reset store
				// unselectChoicesFieldValue('#jform_store');
				// updateFieldRequired('store', 1);
			}
		})
		.catch(error => {
			console.error('[getFieldTypeProperties] Error:', error);
		});
}

/**
 * Determine whether a given value should be considered "non-empty" or "present".
 *
 * This function returns true for:
 * - Positive numbers (greater than 0)
 * - Non-empty strings
 * - Non-empty arrays
 * - Any object with a numeric `.length` property greater than 0
 *
 * It safely ignores and returns false for:
 * - 0, null, undefined, NaN, false
 * - Empty strings ('')
 * - Empty arrays ([])
 * - Objects without a `length` property
 *
 * @param  {*} value  The value to evaluate.
 *
 * @return {boolean}  True if the value is considered non-empty, false otherwise.
 * @since  5.1.3
 */
function notEmpty(value) {
	// Handle numeric values explicitly
	if (typeof value === 'number') {
		return value > 0 && Number.isFinite(value);
	}

	// Handle objects with a length property (string, array, NodeList, etc.)
	if (typeof value?.length === 'number') {
		return value.length > 0;
	}

	// Everything else (null, undefined, {}, false, etc.) is considered empty
	return false;
}

/**
 * Watch for Joomla subform row additions and removals.
 *
 * This listener keeps property data consistent by re-running
 * propertyDynamicSet() whenever a subform row is added or removed.
 *
 * On row removal, the removed field's ID is also extracted
 * to update the dynamic property list accordingly.
 *
 * @return {void}
 * @since  3.1.3
 */
function rowWatcher() {
	// Listen for subform row removals
	document.addEventListener('subform-row-remove', function (event) {
		try {
			// Joomla 5 provides the removed row in event.detail.row
			const row = event.detail?.row || null;

			if (row) {
				// Find the .field_list_name_options element and get its ID
				const field = row.querySelector('.field_list_name_options');
				if (field && field.id) {
					propertyIdRemoved = field.id;
				}
			}

			// Recalculate property options
			if (typeof propertyDynamicSet === 'function') {
				propertyDynamicSet();
			}
		} catch (error) {
			console.error('[rowWatcher] subform-row-remove error:', error);
		}
	});

	// Listen for subform row additions
	document.addEventListener('subform-row-add', function (event) {
		try {
			// Recalculate property options after a new row is added
			if (typeof propertyDynamicSet === 'function') {
				propertyDynamicSet();
			}
		} catch (error) {
			console.error('[rowWatcher] subform-row-add error:', error);
		}
	});
}

/**
 * Dynamically rebuild the available property selection lists.
 *
 * This function synchronizes all property select fields on the page
 * to prevent duplicate selections. It rebuilds the available options list
 * for each field based on what is already selected elsewhere.
 *
 * @return {void}
 * @since  3.1.3
 */
function propertyDynamicSet() {
	// Reset global trackers
	propertiesAvailable = {};
	propertiesSelectedArray = {};
	propertiesTrackerArray = {};

	// Check up to 70 potential property rows (same as legacy logic)
	for (let i = 0; i < 70; i++) {
		// Build field ID (example: properties_properties0__name)
		const id_check = `${rowIdKey}_${rowIdKey}${i}__name`;
		const field = document.getElementById(id_check);

		// Ensure field exists and isn't the one just removed
		if (field && propertyIdRemoved !== id_check) {
			// Get selected option key and label
			const selectedOption = field.options[field.selectedIndex];
			if (selectedOption) {
				const key = selectedOption.value;
				const text = selectedOption.text;

				// Track selected and used properties
				propertiesSelectedArray[key] = text;
				propertiesTrackerArray[id_check] = key;

				// Clear all existing options
				while (field.options.length > 0) {
					field.remove(0);
				}
			}
		}
	}

	// Build available property options (those not yet selected)
	for (const prop in propertiesArray) {
		if (Object.prototype.hasOwnProperty.call(propertiesArray, prop) && !propertiesSelectedArray.hasOwnProperty(prop)) {
			propertiesAvailable[prop] = propertiesArray[prop];
		}
	}

	// Rebuild the options list for each tracked select field
	for (const tId in propertiesTrackerArray) {
		if (Object.prototype.hasOwnProperty.call(propertiesTrackerArray, tId)) {
			const tKey = propertiesTrackerArray[tId];
			const selectEl = document.getElementById(tId);

			if (selectEl) {
				// Reinsert the previously selected option
				const selectedOption = document.createElement('option');
				selectedOption.value = tKey;
				selectedOption.textContent = propertiesSelectedArray[tKey] ?? '';
				selectEl.appendChild(selectedOption);

				// Add all available properties
				for (const aKey in propertiesAvailable) {
					if (Object.prototype.hasOwnProperty.call(propertiesAvailable, aKey)) {
						const option = document.createElement('option');
						option.value = aKey;
						option.textContent = propertiesAvailable[aKey];
						selectEl.appendChild(option);
					}
				}

				// Restore selected value and refresh Choices UI
				setChoicesFieldValue(`#${tId}`, tKey, false);
			}
		}
	}
}

/**
 * Fetch the field property description from the server.
 *
 * This method replaces the legacy jQuery.ajax() call with a modern Fetch API request.
 * It validates inputs, builds a secure request URL using Joomla's JRouter,
 * and returns the JSON response from the server. If an error occurs, it logs
 * a descriptive message and returns null to ensure safe promise resolution.
 *
 * @param  {string|number} fieldtype  The field type ID or name to request the description for.
 * @param  {string}        property   The property name to request.
 *
 * @return {Promise<object|null>}     A Promise resolving to the JSON response object or null on error.
 * @since  3.1.3
 */
function getFieldPropertyDesc_server(fieldtype, property) {
	// Validate token
	if (typeof token !== 'string' || token.length === 0) {
		console.warn('[getFieldPropertyDesc_server] Missing or invalid token.');
		return Promise.resolve(null);
	}

	// Validate fieldtype
	let validFieldtype = false;
	if (typeof fieldtype === 'number' && fieldtype > 0) {
		validFieldtype = true;
	} else if (typeof fieldtype === 'string' && fieldtype.trim().length > 0) {
		validFieldtype = true;
	}

	if (!validFieldtype) {
		console.debug('[getFieldPropertyDesc_server] Empty fieldtype provided:', fieldtype);
		return Promise.resolve(null);
	}

	// Validate property
	if (typeof property !== 'string' || property.trim().length === 0) {
		console.debug('[getFieldPropertyDesc_server] Empty property provided.');
		return Promise.resolve(null);
	}

	// Build URL and query
	const baseUrl = 'index.php';
	const params = new URLSearchParams({
		option: 'com_componentbuilder',
		task: 'ajax.getFieldPropertyDesc',
		format: 'json',
		raw: 'true',
		[token]: '1',
		fieldtype: encodeURIComponent(fieldtype),
		property: encodeURIComponent(property)
	});
	if (vastDevMod) params.append('vdm', vastDevMod);

	// Final request URL
	const requestUrl = JRouter(`${baseUrl}?${params.toString()}`);

	// Perform GET request via Fetch
	return fetch(requestUrl, {
		method: 'GET',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then((response) => {
		if (!response.ok) {
			throw new Error(`[getFieldPropertyDesc_server] Network response was not ok: ${response.status}`);
		}
		return response.json();
	})
	.then((data) => data)
	.catch((error) => {
			console.error('[getFieldPropertyDesc_server] Fetch operation failed:', error);
		return null;
	});
}

/**
 * Manage database field visibility and validation based on the field type.
 *
 * When the field type is "note" or "spacer", all database settings are hidden
 * and cleared. Otherwise, the relevant database fields are shown and required.
 *
 * @param  {string} type  The field type to evaluate.
 *
 * @return {void}
 * @since  3.1.3
 */
function dbChecker(type) {
	if (type === 'note' || type === 'spacer') {
		// Reset database-related select fields
		unselectChoicesFieldValue('#jform_datatype');
		unselectChoicesFieldValue('#jform_datalenght');
		unselectChoicesFieldValue('#jform_datadefault');
		unselectChoicesFieldValue('#jform_indexes');
		unselectChoicesFieldValue('#jform_store');

		// Datatype field group
		const datatypeLbl = document.querySelector('#jform_datatype-lbl');
		const datatype = document.querySelector('#jform_datatype');
		if (datatypeLbl) datatypeLbl.closest('.control-group').style.display = 'none';
		if (datatype) datatype.closest('.control-group').style.display = 'none';
		updateFieldRequired('datatype', 1);
		if (datatype) {
			datatype.removeAttribute('required');
			datatype.removeAttribute('aria-required');
			datatype.classList.remove('required');
		}

		// Null switch field group
		const nullLbl = document.querySelector('#jform_null_switch-lbl');
		const nullSwitch = document.querySelector('#jform_null_switch');
		if (nullLbl) nullLbl.closest('.control-group').style.display = 'none';
		if (nullSwitch) nullSwitch.closest('.control-group').style.display = 'none';
		updateFieldRequired('null_switch', 1);
		if (nullSwitch) {
			nullSwitch.removeAttribute('required');
			nullSwitch.removeAttribute('aria-required');
			nullSwitch.classList.remove('required');
		}

		// Store field group
		const storeLbl = document.querySelector('#jform_store-lbl');
		const store = document.querySelector('#jform_store');
		if (storeLbl) storeLbl.closest('.control-group').style.display = 'none';
		if (store) store.closest('.control-group').style.display = 'none';
		updateFieldRequired('store', 1);
		if (store) {
			store.removeAttribute('required');
			store.removeAttribute('aria-required');
			store.classList.remove('required');
		}

		// Show/hide notices
		document.querySelectorAll('.note_no_database_settings_needed').forEach(el => {
			const group = el.closest('.control-group');
			if (group) group.style.display = '';
		});
		document.querySelectorAll('.note_database_settings_needed').forEach(el => {
			const group = el.closest('.control-group');
			if (group) group.style.display = 'none';
		});

	} else {
		// Datatype field group
		const datatypeLbl = document.querySelector('#jform_datatype-lbl');
		const datatype = document.querySelector('#jform_datatype');
		if (datatypeLbl) datatypeLbl.closest('.control-group').style.display = '';
		if (datatype) datatype.closest('.control-group').style.display = '';
		updateFieldRequired('datatype', 0);
		if (datatype) {
			datatype.setAttribute('required', 'required');
			datatype.setAttribute('aria-required', 'true');
			datatype.classList.add('required');
		}

		// Null switch field group
		// const nullLbl = document.querySelector('#jform_null_switch-lbl');
		// const nullSwitch = document.querySelector('#jform_null_switch');
		// if (nullLbl) nullLbl.closest('.control-group').style.display = '';
		// if (nullSwitch) nullSwitch.closest('.control-group').style.display = '';
		// updateFieldRequired('null_switch', 0);
		// if (nullSwitch) {
		//	nullSwitch.setAttribute('required', 'required');
		//	nullSwitch.setAttribute('aria-required', 'true');
		//	nullSwitch.classList.add('required');
		// }

		// Store field group
		// const storeLbl = document.querySelector('#jform_store-lbl');
		// const store = document.querySelector('#jform_store');
		// if (storeLbl) storeLbl.closest('.control-group').style.display = '';
		// if (store) store.closest('.control-group').style.display = '';
		// updateFieldRequired('store', 0);
		// if (store) {
		//	store.setAttribute('required', 'required');
		//	store.setAttribute('aria-required', 'true');
		//	store.classList.add('required');
		// }

		// Update database-related selects to defaults (0 or empty) if needed
		// unselectChoicesFieldValue('#jform_indexes');
		// unselectChoicesFieldValue('#jform_store');

		// Show/hide notices
		document.querySelectorAll('.note_no_database_settings_needed').forEach(el => {
			const group = el.closest('.control-group');
			if (group) group.style.display = 'none';
		});
		document.querySelectorAll('.note_database_settings_needed').forEach(el => {
			const group = el.closest('.control-group');
			if (group) group.style.display = '';
		});
	}
}

/**
 * Fetch and display the validation rules table from the server.
 *
 * This method requests the validation rules HTML from the server using
 * getCodeFrom_server(), then injects it into the #display_validation_rules
 * container. It is functionally identical to the original jQuery version
 * but rewritten in pure, modern JavaScript.
 *
 * @return {Promise<void>}  Resolves when the validation rules are loaded and rendered.
 * @since  3.1.3
 */
async function getValidationRulesTable() {
	try {
		// Request validation rules HTML from the server
		const result = await getCodeFrom_server(1, 'type', 'type', 'getValidationRulesTable');

		// Inject result into target container if present
		if (result) {
			const target = document.getElementById('display_validation_rules');
			if (target) {
				target.innerHTML = result;
			} else {
				console.warn('[getValidationRulesTable] Target element #display_validation_rules not found.');
			}
		}
	} catch (error) {
		console.error('[getValidationRulesTable] Failed to fetch validation rules table:', error);
	}
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

/**
 * Insert a new HTML element or markup right after
 * the closest `.control-group` ancestor of the target element.
 *
 * Equivalent to:
 *   jQuery(result).insertAfter(jQuery(where).closest('.control-group'));
 *
 * @param  {string|HTMLElement} result  The HTML markup or DOM element to insert.
 * @param  {string}             where   A CSS selector or HTMLElement used to locate the `.control-group` ancestor.
 *
 * @return {void}
 * @since  3.1.3
 */
function addData(result, where) {
	try {
		// Resolve the base element (can be selector or element)
		const baseElement = typeof where === 'string'
			? document.querySelector(where)
			: where;

		if (!baseElement) {
			console.error('[addData] Target element not found:', where);
			return;
		}

		// Find the closest .control-group ancestor
		const controlGroup = baseElement.closest('.control-group');
		if (!controlGroup || !controlGroup.parentNode) {
			console.error('[addData] No .control-group ancestor found for:', where);
			return;
		}

		// Create or reuse element to insert
		let nodeToInsert;
		if (typeof result === 'string') {
			// Create a DOM node directly from HTML
			const wrapper = document.createElement('div');
			wrapper.innerHTML = result.trim();
			nodeToInsert = wrapper.firstElementChild;
		} else if (result instanceof HTMLElement) {
			nodeToInsert = result;
		} else {
			console.error('[addData] Invalid result type:', result);
			return;
		}

		// Insert the node after the .control-group element
		controlGroup.parentNode.insertBefore(nodeToInsert, controlGroup.nextSibling);
	} catch (error) {
		console.error('[addData] Error inserting element:', error);
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
 * Set a field value and keep the visible UI (Choices/Joomla Fancy Select) in sync.
 *
 * - Skips Choices/Joomla fancy select logic for plain text/number/email/url inputs.
 * - Only updates the value if it actually changed.
 * - Avoids duplicate change events that can cause recursion.
 *
 * @param  {string} selector  CSS selector for the field element.
 * @param  {any}    value     The value to assign to the field.
 * @param  {bool}   forceBubbles
 *
 * @return {void}
 * @since  5.1.3
 */
function setChoicesFieldValue(selector, value, forceBubbles = true) {
	const field = document.querySelector(selector);
	if (!field) {
		return;
	}

	// Compute normalized new value and current value
	const newVal = value == null ? '' : String(value);
	const curVal = field.value == null ? '' : String(field.value);

	// Skip work if nothing changed (prevents unnecessary events/loops)
	const valueChanged = newVal !== curVal;
	isPlainInput = isPlainInputField(field);

	// For plain inputs: set only if changed, then emit a single change
	if (isPlainInput) {
		if (valueChanged) {
			field.value = newVal;
			// Fire a single change to notify observers; no need for input here
			field.dispatchEvent(new Event('change', { bubbles: true }));
		}
		return;
	}

	// For selects (or other widgets) ensure Choices/Joomla Fancy Select is ready
	const choicesInstance = ensureChoicesInitialization(field);

	// Update only if value actually changed
	if (valueChanged) {
		// Update the native value first
		field.value = newVal;

		// If we have a Choices/Joomla instance, update its UI.
		// These often emit their own change event; no need to manually dispatch another.
		if (choicesInstance) {
			try {
				// Preferred API
				choicesInstance.setChoiceByValue(newVal);
			} catch (err) {
				// Fallback for older APIs
				if (typeof choicesInstance.setValue === 'function') {
					choicesInstance.setValue(newVal);
				}
			}
			if (!forceBubbles) {
				return;
			}
		}

		// No widget present: dispatch a single change event.
		field.dispatchEvent(new Event('change', { bubbles: true }));
	}
}

/**
 * Unselect the current value from a Joomla Fancy Select / Choices.js field
 * without removing or clearing its available options.
 *
 * - Keeps all existing options intact.
 * - Ensures an empty option ("Select an option") exists.
 * - Visually and logically deselects the current choice.
 * - Works with both Joomla.FieldChoices and plain Choices.js.
 *
 * @param  {string} selector  CSS selector for the field element.
 *
 * @return {void}
 * @since  5.1.5
 */
function unselectChoicesFieldValue(selector) {
	const field = document.querySelector(selector);
	if (!field) {
		console.warn('unselectChoicesFieldValue: field not found ->', selector);
		return;
	}

	// Compute normalized new value and current value
	const newVal = '';
	const curVal = field.value == null ? '' : String(field.value);

	// Skip work if nothing changed (prevents unnecessary events/loops)
	const valueChanged = newVal !== curVal;
	isPlainInput = isPlainInputField(field);

	// Update only if value actually changed
	if (!valueChanged) {
		return;
	}

	// For plain inputs: set only if changed, then emit a single change
	if (isPlainInput) {
		if (valueChanged) {
			field.value = newVal;
			// Fire a single change to notify observers; no need for input here
			field.dispatchEvent(new Event('change', { bubbles: true }));
		}
		return;
	}

	// Ensure an empty option exists
	let emptyOption = field.querySelector('option[value=""]');
	if (!emptyOption) {
		emptyOption = document.createElement('option');
		emptyOption.value = '';
		emptyOption.textContent = 'Select an option';
		field.insertBefore(emptyOption, field.firstChild);
	}

	// Initialize or fetch existing Choices/Fancy Select instance
	const choicesInstance = ensureChoicesInitialization(field);

	// Set the field to the empty option (unselect)
	field.value = emptyOption.value;

	// Update Choices/Fancy Select visually without clearing the available choices
	if (choicesInstance) {
		try {
			// Try the standard method first
			if (typeof choicesInstance.setChoiceByValue === 'function') {
				choicesInstance.setChoiceByValue(emptyOption.value);
			}
			// Fallback for older APIs
			else if (typeof choicesInstance.setValue === 'function') {
				choicesInstance.setValue([emptyOption.value]);
			}
			// Some Joomla Fancy Selects may use a selectElement property
			else if (choicesInstance.passedElement?.element) {
				choicesInstance.passedElement.element.value = emptyOption.value;
			}
		} catch (e) {
			console.warn('unselectChoicesFieldValue: failed to update UI', e);
		}
	}

	// Fire a proper change event to keep observers in sync
	field.dispatchEvent(new Event('change', { bubbles: true }));
}

/**
 * Ensure a Joomla Fancy Select or Choices.js instance is initialized for a given field.
 *
 * This function checks whether the element already has a Choices/Fancy Select instance.
 * If not, it attempts to initialize one using Joomla.FieldChoices or a Choices.js fallback.
 *
 * @param  {HTMLElement} element  The field element to initialize.
 *
 * @return {object|null}          The initialized Choices/Fancy Select instance or null on failure.
 * @since  5.1.3
 */
function ensureChoicesInitialization(element) {
	if (!element) {
		return null;
	}

	// Detect existing wrapper or instance
	const fancyWrapper = element.closest('joomla-field-fancy-select');
	const existingChoices =
		element.choices ||
		(fancyWrapper && fancyWrapper.choicesInstance) ||
		(window.Joomla?.FieldChoices?.instances && Joomla.FieldChoices.instances[element.id]) ||
		null;

	// Return if already initialized
	if (existingChoices) {
		return existingChoices;
	}

	// Attempt Joomla FieldChoices initialization
	if (window.Joomla?.FieldChoices?.init) {
		try {
			const instance = Joomla.FieldChoices.init(element);
			return instance || null;
		} catch (e) {
			console.warn('ensureChoicesInitialization: Joomla FieldChoices.init failed', e);
		}
	}

	// Fallback: manually initialize Choices.js
	if (typeof Choices !== 'undefined') {
		try {
			const instance = new Choices(element, { shouldSort: false, searchEnabled: true });
			element.choices = instance;
			return instance;
		} catch (e) {
			console.warn('ensureChoicesInitialization: Choices fallback failed', e);
		}
	}

	return null;
}

/**
 * Detects whether an element is a plain input (not a select/Choices).
 *
 * @param  {HTMLElement} field
 * @return {boolean}
 * @since  5.1.3
 */
function isPlainInputField(field) {
	const tagName = field.tagName.toLowerCase();
	const type = (field.getAttribute('type') || '').toLowerCase();
	return tagName === 'input' && ['text', 'number', 'email', 'url'].includes(type);
}
