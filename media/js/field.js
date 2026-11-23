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

	var datalenght_vvvvwbk = jQuery("#jform_datalenght").val();
	vvvvwbk(datalenght_vvvvwbk);

	var datadefault_vvvvwbl = jQuery("#jform_datadefault").val();
	vvvvwbl(datadefault_vvvvwbl);

	var datatype_vvvvwbm = jQuery("#jform_datatype").val();
	vvvvwbm(datatype_vvvvwbm);

	var datatype_vvvvwbn = jQuery("#jform_datatype").val();
	vvvvwbn(datatype_vvvvwbn);

	var store_vvvvwbq = jQuery("#jform_store").val();
	vvvvwbq(store_vvvvwbq);

	var add_css_view_vvvvwbr = jQuery("#jform_add_css_view input[type='radio']:checked").val();
	vvvvwbr(add_css_view_vvvvwbr);

	var add_css_views_vvvvwbs = jQuery("#jform_add_css_views input[type='radio']:checked").val();
	vvvvwbs(add_css_views_vvvvwbs);

	var add_javascript_view_footer_vvvvwbt = jQuery("#jform_add_javascript_view_footer input[type='radio']:checked").val();
	vvvvwbt(add_javascript_view_footer_vvvvwbt);

	var add_javascript_views_footer_vvvvwbu = jQuery("#jform_add_javascript_views_footer input[type='radio']:checked").val();
	vvvvwbu(add_javascript_views_footer_vvvvwbu);

	var fieldtype_vvvvwbv = jQuery("#jform_fieldtype").val();
	vvvvwbv(fieldtype_vvvvwbv);

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

// the vvvvwbk function
function vvvvwbk(datalenght_vvvvwbk)
{
	if (isSet(datalenght_vvvvwbk) && datalenght_vvvvwbk.constructor !== Array)
	{
		var temp_vvvvwbk = datalenght_vvvvwbk;
		var datalenght_vvvvwbk = [];
		datalenght_vvvvwbk.push(temp_vvvvwbk);
	}
	else if (!isSet(datalenght_vvvvwbk))
	{
		var datalenght_vvvvwbk = [];
	}
	var datalenght = datalenght_vvvvwbk.some(datalenght_vvvvwbk_SomeFunc);


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

// the vvvvwbk Some function
function datalenght_vvvvwbk_SomeFunc(datalenght_vvvvwbk)
{
	// set the function logic
	if (datalenght_vvvvwbk == 'Other')
	{
		return true;
	}
	return false;
}

// the vvvvwbl function
function vvvvwbl(datadefault_vvvvwbl)
{
	if (isSet(datadefault_vvvvwbl) && datadefault_vvvvwbl.constructor !== Array)
	{
		var temp_vvvvwbl = datadefault_vvvvwbl;
		var datadefault_vvvvwbl = [];
		datadefault_vvvvwbl.push(temp_vvvvwbl);
	}
	else if (!isSet(datadefault_vvvvwbl))
	{
		var datadefault_vvvvwbl = [];
	}
	var datadefault = datadefault_vvvvwbl.some(datadefault_vvvvwbl_SomeFunc);


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

// the vvvvwbl Some function
function datadefault_vvvvwbl_SomeFunc(datadefault_vvvvwbl)
{
	// set the function logic
	if (datadefault_vvvvwbl == 'Other')
	{
		return true;
	}
	return false;
}

// the vvvvwbm function
function vvvvwbm(datatype_vvvvwbm)
{
	if (isSet(datatype_vvvvwbm) && datatype_vvvvwbm.constructor !== Array)
	{
		var temp_vvvvwbm = datatype_vvvvwbm;
		var datatype_vvvvwbm = [];
		datatype_vvvvwbm.push(temp_vvvvwbm);
	}
	else if (!isSet(datatype_vvvvwbm))
	{
		var datatype_vvvvwbm = [];
	}
	var datatype = datatype_vvvvwbm.some(datatype_vvvvwbm_SomeFunc);


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

// the vvvvwbm Some function
function datatype_vvvvwbm_SomeFunc(datatype_vvvvwbm)
{
	// set the function logic
	if (datatype_vvvvwbm == 'CHAR' || datatype_vvvvwbm == 'VARCHAR' || datatype_vvvvwbm == 'DATETIME' || datatype_vvvvwbm == 'DATE' || datatype_vvvvwbm == 'TIME' || datatype_vvvvwbm == 'INT' || datatype_vvvvwbm == 'TINYINT' || datatype_vvvvwbm == 'BIGINT' || datatype_vvvvwbm == 'FLOAT' || datatype_vvvvwbm == 'DECIMAL' || datatype_vvvvwbm == 'DOUBLE')
	{
		return true;
	}
	return false;
}

// the vvvvwbn function
function vvvvwbn(datatype_vvvvwbn)
{
	if (isSet(datatype_vvvvwbn) && datatype_vvvvwbn.constructor !== Array)
	{
		var temp_vvvvwbn = datatype_vvvvwbn;
		var datatype_vvvvwbn = [];
		datatype_vvvvwbn.push(temp_vvvvwbn);
	}
	else if (!isSet(datatype_vvvvwbn))
	{
		var datatype_vvvvwbn = [];
	}
	var datatype = datatype_vvvvwbn.some(datatype_vvvvwbn_SomeFunc);


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

// the vvvvwbn Some function
function datatype_vvvvwbn_SomeFunc(datatype_vvvvwbn)
{
	// set the function logic
	if (datatype_vvvvwbn == 'CHAR' || datatype_vvvvwbn == 'VARCHAR' || datatype_vvvvwbn == 'INT' || datatype_vvvvwbn == 'TINYINT' || datatype_vvvvwbn == 'BIGINT' || datatype_vvvvwbn == 'FLOAT' || datatype_vvvvwbn == 'DECIMAL' || datatype_vvvvwbn == 'DOUBLE')
	{
		return true;
	}
	return false;
}

// the vvvvwbq function
function vvvvwbq(store_vvvvwbq)
{
	if (isSet(store_vvvvwbq) && store_vvvvwbq.constructor !== Array)
	{
		var temp_vvvvwbq = store_vvvvwbq;
		var store_vvvvwbq = [];
		store_vvvvwbq.push(temp_vvvvwbq);
	}
	else if (!isSet(store_vvvvwbq))
	{
		var store_vvvvwbq = [];
	}
	var store = store_vvvvwbq.some(store_vvvvwbq_SomeFunc);


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

// the vvvvwbq Some function
function store_vvvvwbq_SomeFunc(store_vvvvwbq)
{
	// set the function logic
	if (store_vvvvwbq == Number('6'))
	{
		return true;
	}
	return false;
}

// the vvvvwbr function
function vvvvwbr(add_css_view_vvvvwbr)
{
	// set the function logic
	if (add_css_view_vvvvwbr == Number('1'))
	{
		jQuery('#jform_css_view-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_css_view-lbl').closest('.control-group').hide();
	}
}

// the vvvvwbs function
function vvvvwbs(add_css_views_vvvvwbs)
{
	// set the function logic
	if (add_css_views_vvvvwbs == Number('1'))
	{
		jQuery('#jform_css_views-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_css_views-lbl').closest('.control-group').hide();
	}
}

// the vvvvwbt function
function vvvvwbt(add_javascript_view_footer_vvvvwbt)
{
	// set the function logic
	if (add_javascript_view_footer_vvvvwbt == Number('1'))
	{
		jQuery('#jform_javascript_view_footer-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_javascript_view_footer-lbl').closest('.control-group').hide();
	}
}

// the vvvvwbu function
function vvvvwbu(add_javascript_views_footer_vvvvwbu)
{
	// set the function logic
	if (add_javascript_views_footer_vvvvwbu == Number('1'))
	{
		jQuery('#jform_javascript_views_footer-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_javascript_views_footer-lbl').closest('.control-group').hide();
	}
}

// the vvvvwbv function
function vvvvwbv(fieldtype_vvvvwbv)
{
	if (isSet(fieldtype_vvvvwbv) && fieldtype_vvvvwbv.constructor !== Array)
	{
		var temp_vvvvwbv = fieldtype_vvvvwbv;
		var fieldtype_vvvvwbv = [];
		fieldtype_vvvvwbv.push(temp_vvvvwbv);
	}
	else if (!isSet(fieldtype_vvvvwbv))
	{
		var fieldtype_vvvvwbv = [];
	}
	var fieldtype = fieldtype_vvvvwbv.some(fieldtype_vvvvwbv_SomeFunc);


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

// the vvvvwbv Some function
function fieldtype_vvvvwbv_SomeFunc(fieldtype_vvvvwbv)
{
	// set the function logic
	if (fieldtype_vvvvwbv == 'f9ecacd0-8481-4157-8c71-d7aaefc2b7c3' || fieldtype_vvvvwbv == '626d0cba-a908-4a4f-a447-96d781aeaa0b')
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
	// get type value
	var fieldtype = jQuery("#jform_fieldtype option:selected").val();
	getFieldTypeProperties(fieldtype, false);
	// get the linked details
	getLinked();
	// get the validation rules
	getValidationRulesTable();
	// set button to create more fields
	addButton('validation_rule', 'validation_rules_header', 2);
	// get the field type text
	var fieldText = jQuery("#jform_fieldtype option:selected").text().toLowerCase();
	// now check if database input is needed
	dbChecker(fieldText);
	// check and load all the custom code edit buttons
	getEditCustomCodeButtons();
});

// the options row id key
var rowIdKey = 'properties';

function getFieldTypeProperties(fieldtype, db){
	getCodeFrom_server(fieldtype, 'type', 'type', 'fieldTypeProperties').then(function(result) {
		if(result.subform){
			// load the list of properties
			propertiesArray = result.nameListOptions;
			// remove previous forms of exist
			jQuery('.prop_removal').remove();
			// hide notice
			jQuery('.note_select_field_type').closest('.control-group').remove();
			// append to note_filter_information class
			jQuery('.note_filter_information').closest('.control-group').prepend(result.extra);
			// append to note_filter_information class
			if(result.textarea){
				jQuery.each( result.textarea, function( i, tField ) {
					// append to note_filter_information class
					jQuery('.note_filter_information').closest('.control-group').prepend(tField);
				});
			}
			// append to note_filter_information class
			jQuery('.note_filter_information').closest('.control-group').prepend(result.subform);
			// add the watcher
			rowWatcher();
			// initialize the new form
			jQuery('div.subform-repeatable').trigger('update');
			// jQuery('div.subform-repeatable').subformRepeatable();
			// update all the list fields to only show items not selected already
			propertyDynamicSet();
			// set the field type info
			jQuery('#help').remove();
			jQuery('.helpNote').append('<div id="help">'+result.description+'<br />'+result.values_description+'</div>');
			// load the database properties if not set and defaults were found
			if (db && result.database){
				// update datatype
				jQuery('#jform_datatype').val(result.database.datatype);
				jQuery('#jform_datatype').trigger("liszt:updated");
				jQuery('#jform_datatype').trigger("change");
				// be sure to remove from no required
				updateFieldRequired('datatype', 0);
				// update datalenght
				jQuery('#jform_datalenght').val(result.database.datalenght);
				jQuery('#jform_datalenght').trigger("liszt:updated");
				jQuery('#jform_datalenght').trigger("change");
				// be sure to remove from no required
				updateFieldRequired('datalenght', 0);
				// load the datalenght_other if needed
				if ('Other' === result.database.datalenght){
					jQuery('#jform_datalenght_other').val(result.database.datalenght_other);
					// be sure to remove from no required
					updateFieldRequired('datalenght_other', 0);
				}
				// update datadefault
				jQuery('#jform_datadefault').val(result.database.datadefault);
				jQuery('#jform_datadefault').trigger("liszt:updated");
				jQuery('#jform_datadefault').trigger("change");
				// load the datadefault_other if needed
				if ('Other' === result.database.datadefault){
					jQuery('#jform_datadefault_other').val(result.database.datadefault_other);
					// be sure to remove from no required
					updateFieldRequired('datadefault_other', 0);
				}
				// update indexes
				jQuery('#jform_indexes').val(result.database.indexes);
				jQuery('#jform_indexes').trigger("liszt:updated");
				jQuery('#jform_indexes').trigger("change");
				// be sure to remove from no required
				updateFieldRequired('indexes', 0);
				// update store
				jQuery('#jform_store').val(result.database.store);
				jQuery('#jform_store').trigger("liszt:updated");
				jQuery('#jform_store').trigger("change");
				// be sure to remove from no required
				updateFieldRequired('store', 0);
			}
		}
	})
}

function getFieldPropertyDesc(field, targetForm){
	// get the ID
	var id = jQuery(field).attr('id');
	// build the target array
	var target = id.split('__');
	// get property value
	var property = jQuery(field).val();
	// first check that there isn't any of this property type already set
	if (propertyIsSet(property, id, targetForm)) {
		// reset the selection
		jQuery('#'+id).val('');
		jQuery('#'+id).trigger("liszt:updated");
		// give out a notice
		jQuery.UIkit.notify({message: Joomla.Text._('COM_COMPONENTBUILDER_PROPERTY_ALREADY_SELECTED_TRY_ANOTHER'), timeout: 5000, status: 'warning', pos: 'top-center'});
		// update the values
		jQuery('#'+target[0]+'__desc').val('');
		jQuery('#'+target[0]+'__value').val('');
	} else {
		// do a dynamic update
		propertyDynamicSet();
		// get type value
		if (targetForm === 'properties') {
			var fieldtype = jQuery("#jform_fieldtype option:selected").val();
		} else {
			var fieldtype = 'extra';
		}
		getFieldPropertyDesc_server(fieldtype, property).done(function(result) {
			if(result.desc || result.value){
				// update the values
				jQuery('#'+target[0]+'__desc').val(result.desc);
				jQuery('#'+target[0]+'__value').val(result.value);
			} else {
				// update the values
				jQuery('#'+target[0]+'__desc').val(Joomla.Text._('COM_COMPONENTBUILDER_NO_DESCRIPTION_FOUND'));
				jQuery('#'+target[0]+'__value').val('');
			}
		});
	}
}

// set properties the options
propertiesArray = {};
var propertyIdRemoved;

function propertyDynamicSet() {
	propertiesAvailable = {};
	propertiesSelectedArray = {};
	propertiesTrackerArray = {};
	var i;
	for (i = 0; i < 70; i++) { // for now this is the number of field we should check
		// build ID
		var id_check = rowIdKey+'_'+rowIdKey+i+'__name';
		// first check if Id is on page as that not the same as the one currently calling
		if (jQuery("#"+id_check).length && propertyIdRemoved !== id_check) {
			// build the selected array
			var key =  jQuery("#"+id_check+" option:selected").val();
			var text =  jQuery("#"+id_check+" option:selected").text();
			propertiesSelectedArray[key] = text;
			// keep track of the value set
			propertiesTrackerArray[id_check] = key;
			// clear the options out
			jQuery("#"+id_check).find('option').remove().end();
		}
	}
	// trigger chosen on the list fields
	// jQuery('.field_list_name_options').chosen({"disable_search_threshold":10,"search_contains":true,"allow_single_deselect":true,"placeholder_text_multiple":Joomla.Text._("COM_COMPONENTBUILDER_TYPE_OR_SELECT_SOME_OPTIONS"),"placeholder_text_single":Joomla.Text._("COM_COMPONENTBUILDER_SELECT_A_PROPERTY"),"no_results_text":Joomla.Text._("COM_COMPONENTBUILDER_NO_RESULTS_MATCH")});
	// now build the list to keep
	jQuery.each( propertiesArray, function( prop, name ) {
		if (!propertiesSelectedArray.hasOwnProperty(prop)) {
			propertiesAvailable[prop] = name;
		}
	});
	// now add the lists back
	jQuery.each( propertiesTrackerArray, function( tId, tKey ) {
		if (jQuery('#'+tId).length) {
			jQuery('#'+tId).append('<option value="'+tKey+'">'+propertiesSelectedArray[tKey]+'</option>');
			jQuery.each( propertiesAvailable, function( aKey, aValue ) {
				jQuery('#'+tId).append('<option value="'+aKey+'">'+aValue+'</option>');
			});
			jQuery('#'+tId).val(tKey);
			jQuery('#'+tId).trigger('liszt:updated');
		}
	});
}

function rowWatcher() {
	jQuery(document).on('subform-row-remove', function(event, row){
       		propertyIdRemoved = jQuery(row.innerHTML).find('.field_list_name_options').attr('id');
       		propertyDynamicSet();
	});
	jQuery(document).on('subform-row-add', function(event, row){
       		propertyDynamicSet();
	});
}

function propertyIsSet(prop, id, targetForm) {
	var i;
	for (i = 0; i < 70; i++) { // for now this is the number of field we should check
		// build ID
		var id_check = targetForm+'_'+targetForm+i+'__name';
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

function getFieldPropertyDesc_server(fieldtype, property){
	var getUrl = JRouter("index.php?option=com_componentbuilder&task=ajax.getFieldPropertyDesc&format=json&raw=true&vdm="+vastDevMod);
	if(token.length > 0 && (fieldtype > 0 || fieldtype.length > 0) && property.length > 0){
		var request = token+'=1&fieldtype='+fieldtype+'&property='+property;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'json',
		data: request,
		jsonp: false
	});
}

function getValidationRulesTable(){
	getCodeFrom_server(1,'type','type', 'getValidationRulesTable').then(function(result) {
		if(result){
			jQuery('#display_validation_rules').html(result);
		}
	});
}

function dbChecker(type){
	if ('note' === type || 'spacer' === type) {
		// update the datatype selection
		jQuery('#jform_datatype').val('').trigger('liszt:updated').change();
		jQuery('#jform_datalenght').val('').trigger('liszt:updated').change();
		jQuery('#jform_datadefault').val('').trigger('liszt:updated').change();
		jQuery('#jform_datadefault').val('').trigger('liszt:updated').change();
		jQuery('#jform_indexes').val(0).trigger('liszt:updated').change();
		jQuery('#jform_store').val(0).trigger('liszt:updated').change();
		// remove the datatype
		jQuery('#jform_datatype-lbl').closest('.control-group').hide();
		jQuery('#jform_datatype').closest('.control-group').hide();
		updateFieldRequired('datatype',1);
		jQuery('#jform_datatype').removeAttr('required');
		jQuery('#jform_datatype').removeAttr('aria-required');
		jQuery('#jform_datatype').removeClass('required');
		// remove the null selection
		jQuery('#jform_null_switch-lbl').closest('.control-group').hide();
		jQuery('#jform_null_switch').closest('.control-group').hide();
		updateFieldRequired('null_switch',1);
		jQuery('#jform_null_switch').removeAttr('required');
		jQuery('#jform_null_switch').removeAttr('aria-required');
		jQuery('#jform_null_switch').removeClass('required');
		// remove the store (modeling method)
		jQuery('#jform_store-lbl').closest('.control-group').hide();
		jQuery('#jform_store').closest('.control-group').hide();
		updateFieldRequired('store',1);
		jQuery('#jform_store').removeAttr('required');
		jQuery('#jform_store').removeAttr('aria-required');
		jQuery('#jform_store').removeClass('required');
		// show notice
		jQuery('.note_no_database_settings_needed').closest('.control-group').show();
		jQuery('.note_database_settings_needed').closest('.control-group').hide();
	} else {
		// add the datatype
		jQuery('#jform_datatype-lbl').closest('.control-group').show();
		jQuery('#jform_datatype').closest('.control-group').show();
		updateFieldRequired('datatype',0);
		jQuery('#jform_datatype').prop('required','required');
		jQuery('#jform_datatype').attr('aria-required',true);
		jQuery('#jform_datatype').addClass('required');
		// add the null selection
		jQuery('#jform_null_switch-lbl').closest('.control-group').show();
		jQuery('#jform_null_switch').closest('.control-group').show();
		updateFieldRequired('null_switch',0);
		jQuery('#jform_null_switch').prop('required','required');
		jQuery('#jform_null_switch').attr('aria-required',true);
		jQuery('#jform_null_switch').addClass('required');
		// remove the store (modeling method)
		jQuery('#jform_store-lbl').closest('.control-group').show();
		jQuery('#jform_store').closest('.control-group').show();
		updateFieldRequired('store',0);
		jQuery('#jform_store').prop('required','required');
		jQuery('#jform_store').attr('aria-required',true);
		jQuery('#jform_store').addClass('required');
		// remove notice
		jQuery('.note_no_database_settings_needed').closest('.control-group').hide();
		jQuery('.note_database_settings_needed').closest('.control-group').show();
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
