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
var updateAdminViewConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateAdminViewConditions()
{
	if (updateAdminViewConditionsBaseline === null)
	{
		updateAdminViewConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_css_view-lbl","#jform_css_views-lbl","#jform_javascript_view_file-lbl","#jform_javascript_views_file-lbl","#jform_javascript_view_footer-lbl","#jform_javascript_views_footer-lbl","#jform_ajax_input-lbl","#jform_php_ajaxmethod-lbl","#jform_php_getitem-lbl","#jform_php_getitems-lbl","#jform_php_getitems_after_all-lbl","#jform_php_getlistquery-lbl","#jform_php_getform-lbl","#jform_php_before_save-lbl","#jform_php_save-lbl","#jform_php_postsavehook-lbl","#jform_php_allowadd-lbl","#jform_php_allowedit-lbl","#jform_php_before_cancel-lbl","#jform_php_after_cancel-lbl","#jform_php_batchcopy-lbl","#jform_php_batchmove-lbl","#jform_php_before_publish-lbl","#jform_php_after_publish-lbl","#jform_php_before_delete-lbl","#jform_php_after_delete-lbl","#jform_php_document-lbl","#jform_source","#jform_sql","#jform_addtables-lbl","#jform_custom_button-lbl","#jform_php_controller-lbl","#jform_php_controller_list-lbl","#jform_php_model-lbl","#jform_php_model_list-lbl"];
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
					updateAdminViewConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = ["source"];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateAdminViewConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateAdminViewConditionsBaseline.containers.length; index++)
	{
		var baseline = updateAdminViewConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateAdminViewConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var add_css_view_vvvvvxf = jQuery("#jform_add_css_view input[type='radio']:checked").val();
	vvvvvxf(add_css_view_vvvvvxf);

	var add_css_views_vvvvvxg = jQuery("#jform_add_css_views input[type='radio']:checked").val();
	vvvvvxg(add_css_views_vvvvvxg);

	var add_javascript_view_file_vvvvvxh = jQuery("#jform_add_javascript_view_file input[type='radio']:checked").val();
	vvvvvxh(add_javascript_view_file_vvvvvxh);

	var add_javascript_views_file_vvvvvxi = jQuery("#jform_add_javascript_views_file input[type='radio']:checked").val();
	vvvvvxi(add_javascript_views_file_vvvvvxi);

	var add_javascript_view_footer_vvvvvxj = jQuery("#jform_add_javascript_view_footer input[type='radio']:checked").val();
	vvvvvxj(add_javascript_view_footer_vvvvvxj);

	var add_javascript_views_footer_vvvvvxk = jQuery("#jform_add_javascript_views_footer input[type='radio']:checked").val();
	vvvvvxk(add_javascript_views_footer_vvvvvxk);

	var add_php_ajax_vvvvvxl = jQuery("#jform_add_php_ajax input[type='radio']:checked").val();
	vvvvvxl(add_php_ajax_vvvvvxl);

	var add_php_getitem_vvvvvxm = jQuery("#jform_add_php_getitem input[type='radio']:checked").val();
	vvvvvxm(add_php_getitem_vvvvvxm);

	var add_php_getitems_vvvvvxn = jQuery("#jform_add_php_getitems input[type='radio']:checked").val();
	vvvvvxn(add_php_getitems_vvvvvxn);

	var add_php_getitems_after_all_vvvvvxo = jQuery("#jform_add_php_getitems_after_all input[type='radio']:checked").val();
	vvvvvxo(add_php_getitems_after_all_vvvvvxo);

	var add_php_getlistquery_vvvvvxp = jQuery("#jform_add_php_getlistquery input[type='radio']:checked").val();
	vvvvvxp(add_php_getlistquery_vvvvvxp);

	var add_php_getform_vvvvvxq = jQuery("#jform_add_php_getform input[type='radio']:checked").val();
	vvvvvxq(add_php_getform_vvvvvxq);

	var add_php_before_save_vvvvvxr = jQuery("#jform_add_php_before_save input[type='radio']:checked").val();
	vvvvvxr(add_php_before_save_vvvvvxr);

	var add_php_save_vvvvvxs = jQuery("#jform_add_php_save input[type='radio']:checked").val();
	vvvvvxs(add_php_save_vvvvvxs);

	var add_php_postsavehook_vvvvvxt = jQuery("#jform_add_php_postsavehook input[type='radio']:checked").val();
	vvvvvxt(add_php_postsavehook_vvvvvxt);

	var add_php_allowadd_vvvvvxu = jQuery("#jform_add_php_allowadd input[type='radio']:checked").val();
	vvvvvxu(add_php_allowadd_vvvvvxu);

	var add_php_allowedit_vvvvvxv = jQuery("#jform_add_php_allowedit input[type='radio']:checked").val();
	vvvvvxv(add_php_allowedit_vvvvvxv);

	var add_php_before_cancel_vvvvvxw = jQuery("#jform_add_php_before_cancel input[type='radio']:checked").val();
	vvvvvxw(add_php_before_cancel_vvvvvxw);

	var add_php_after_cancel_vvvvvxx = jQuery("#jform_add_php_after_cancel input[type='radio']:checked").val();
	vvvvvxx(add_php_after_cancel_vvvvvxx);

	var add_php_batchcopy_vvvvvxy = jQuery("#jform_add_php_batchcopy input[type='radio']:checked").val();
	vvvvvxy(add_php_batchcopy_vvvvvxy);

	var add_php_batchmove_vvvvvxz = jQuery("#jform_add_php_batchmove input[type='radio']:checked").val();
	vvvvvxz(add_php_batchmove_vvvvvxz);

	var add_php_before_publish_vvvvvya = jQuery("#jform_add_php_before_publish input[type='radio']:checked").val();
	vvvvvya(add_php_before_publish_vvvvvya);

	var add_php_after_publish_vvvvvyb = jQuery("#jform_add_php_after_publish input[type='radio']:checked").val();
	vvvvvyb(add_php_after_publish_vvvvvyb);

	var add_php_before_delete_vvvvvyc = jQuery("#jform_add_php_before_delete input[type='radio']:checked").val();
	vvvvvyc(add_php_before_delete_vvvvvyc);

	var add_php_after_delete_vvvvvyd = jQuery("#jform_add_php_after_delete input[type='radio']:checked").val();
	vvvvvyd(add_php_after_delete_vvvvvyd);

	var add_php_document_vvvvvye = jQuery("#jform_add_php_document input[type='radio']:checked").val();
	vvvvvye(add_php_document_vvvvvye);

	var add_sql_vvvvvyf = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvyf(add_sql_vvvvvyf);

	var source_vvvvvyg = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvyg = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvyg(source_vvvvvyg,add_sql_vvvvvyg);

	var source_vvvvvyi = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvyi = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvyi(source_vvvvvyi,add_sql_vvvvvyi);

	var add_custom_button_vvvvvyk = jQuery("#jform_add_custom_button input[type='radio']:checked").val();
	vvvvvyk(add_custom_button_vvvvvyk);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateAdminViewConditions);

// the vvvvvxf function
function vvvvvxf(add_css_view_vvvvvxf)
{
	// set the function logic
	if (add_css_view_vvvvvxf == Number('1'))
	{
		jQuery('#jform_css_view-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_css_view-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxg function
function vvvvvxg(add_css_views_vvvvvxg)
{
	// set the function logic
	if (add_css_views_vvvvvxg == Number('1'))
	{
		jQuery('#jform_css_views-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_css_views-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxh function
function vvvvvxh(add_javascript_view_file_vvvvvxh)
{
	// set the function logic
	if (add_javascript_view_file_vvvvvxh == Number('1'))
	{
		jQuery('#jform_javascript_view_file-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_javascript_view_file-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxi function
function vvvvvxi(add_javascript_views_file_vvvvvxi)
{
	// set the function logic
	if (add_javascript_views_file_vvvvvxi == Number('1'))
	{
		jQuery('#jform_javascript_views_file-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_javascript_views_file-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxj function
function vvvvvxj(add_javascript_view_footer_vvvvvxj)
{
	// set the function logic
	if (add_javascript_view_footer_vvvvvxj == Number('1'))
	{
		jQuery('#jform_javascript_view_footer-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_javascript_view_footer-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxk function
function vvvvvxk(add_javascript_views_footer_vvvvvxk)
{
	// set the function logic
	if (add_javascript_views_footer_vvvvvxk == Number('1'))
	{
		jQuery('#jform_javascript_views_footer-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_javascript_views_footer-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxl function
function vvvvvxl(add_php_ajax_vvvvvxl)
{
	// set the function logic
	if (add_php_ajax_vvvvvxl == Number('1'))
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

// the vvvvvxm function
function vvvvvxm(add_php_getitem_vvvvvxm)
{
	// set the function logic
	if (add_php_getitem_vvvvvxm == Number('1'))
	{
		jQuery('#jform_php_getitem-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_getitem-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxn function
function vvvvvxn(add_php_getitems_vvvvvxn)
{
	// set the function logic
	if (add_php_getitems_vvvvvxn == Number('1'))
	{
		jQuery('#jform_php_getitems-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_getitems-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxo function
function vvvvvxo(add_php_getitems_after_all_vvvvvxo)
{
	// set the function logic
	if (add_php_getitems_after_all_vvvvvxo == Number('1'))
	{
		jQuery('#jform_php_getitems_after_all-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_getitems_after_all-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxp function
function vvvvvxp(add_php_getlistquery_vvvvvxp)
{
	// set the function logic
	if (add_php_getlistquery_vvvvvxp == Number('1'))
	{
		jQuery('#jform_php_getlistquery-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_getlistquery-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxq function
function vvvvvxq(add_php_getform_vvvvvxq)
{
	// set the function logic
	if (add_php_getform_vvvvvxq == Number('1'))
	{
		jQuery('#jform_php_getform-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_getform-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxr function
function vvvvvxr(add_php_before_save_vvvvvxr)
{
	// set the function logic
	if (add_php_before_save_vvvvvxr == Number('1'))
	{
		jQuery('#jform_php_before_save-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_before_save-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxs function
function vvvvvxs(add_php_save_vvvvvxs)
{
	// set the function logic
	if (add_php_save_vvvvvxs == Number('1'))
	{
		jQuery('#jform_php_save-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_save-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxt function
function vvvvvxt(add_php_postsavehook_vvvvvxt)
{
	// set the function logic
	if (add_php_postsavehook_vvvvvxt == Number('1'))
	{
		jQuery('#jform_php_postsavehook-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_postsavehook-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxu function
function vvvvvxu(add_php_allowadd_vvvvvxu)
{
	// set the function logic
	if (add_php_allowadd_vvvvvxu == Number('1'))
	{
		jQuery('#jform_php_allowadd-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_allowadd-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxv function
function vvvvvxv(add_php_allowedit_vvvvvxv)
{
	// set the function logic
	if (add_php_allowedit_vvvvvxv == Number('1'))
	{
		jQuery('#jform_php_allowedit-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_allowedit-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxw function
function vvvvvxw(add_php_before_cancel_vvvvvxw)
{
	// set the function logic
	if (add_php_before_cancel_vvvvvxw == Number('1'))
	{
		jQuery('#jform_php_before_cancel-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_before_cancel-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxx function
function vvvvvxx(add_php_after_cancel_vvvvvxx)
{
	// set the function logic
	if (add_php_after_cancel_vvvvvxx == Number('1'))
	{
		jQuery('#jform_php_after_cancel-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_after_cancel-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxy function
function vvvvvxy(add_php_batchcopy_vvvvvxy)
{
	// set the function logic
	if (add_php_batchcopy_vvvvvxy == Number('1'))
	{
		jQuery('#jform_php_batchcopy-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_batchcopy-lbl').closest('.control-group').hide();
	}
}

// the vvvvvxz function
function vvvvvxz(add_php_batchmove_vvvvvxz)
{
	// set the function logic
	if (add_php_batchmove_vvvvvxz == Number('1'))
	{
		jQuery('#jform_php_batchmove-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_batchmove-lbl').closest('.control-group').hide();
	}
}

// the vvvvvya function
function vvvvvya(add_php_before_publish_vvvvvya)
{
	// set the function logic
	if (add_php_before_publish_vvvvvya == Number('1'))
	{
		jQuery('#jform_php_before_publish-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_before_publish-lbl').closest('.control-group').hide();
	}
}

// the vvvvvyb function
function vvvvvyb(add_php_after_publish_vvvvvyb)
{
	// set the function logic
	if (add_php_after_publish_vvvvvyb == Number('1'))
	{
		jQuery('#jform_php_after_publish-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_after_publish-lbl').closest('.control-group').hide();
	}
}

// the vvvvvyc function
function vvvvvyc(add_php_before_delete_vvvvvyc)
{
	// set the function logic
	if (add_php_before_delete_vvvvvyc == Number('1'))
	{
		jQuery('#jform_php_before_delete-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_before_delete-lbl').closest('.control-group').hide();
	}
}

// the vvvvvyd function
function vvvvvyd(add_php_after_delete_vvvvvyd)
{
	// set the function logic
	if (add_php_after_delete_vvvvvyd == Number('1'))
	{
		jQuery('#jform_php_after_delete-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_after_delete-lbl').closest('.control-group').hide();
	}
}

// the vvvvvye function
function vvvvvye(add_php_document_vvvvvye)
{
	// set the function logic
	if (add_php_document_vvvvvye == Number('1'))
	{
		jQuery('#jform_php_document-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_document-lbl').closest('.control-group').hide();
	}
}

// the vvvvvyf function
function vvvvvyf(add_sql_vvvvvyf)
{
	// set the function logic
	if (add_sql_vvvvvyf == Number('1'))
	{
		jQuery('#jform_source').closest('.control-group').show();
		// Add required attributes to source.
		jQuery('#jform_source').prop('required', true);
		jQuery('#jform_source').attr('aria-required', true);
		jQuery('#jform_source').addClass('required');
	}
	else
	{
		jQuery('#jform_source').closest('.control-group').hide();
		// Remove required attributes from source.
		jQuery('#jform_source').removeAttr('required');
		jQuery('#jform_source').removeAttr('aria-required');
		jQuery('#jform_source').removeClass('required');
	}
}

// the vvvvvyg function
function vvvvvyg(source_vvvvvyg,add_sql_vvvvvyg)
{
	// set the function logic
	if ((source_vvvvvyg == Number('2')) && (add_sql_vvvvvyg == Number('1')))
	{
		jQuery('#jform_sql').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_sql').closest('.control-group').hide();
	}
}

// the vvvvvyi function
function vvvvvyi(source_vvvvvyi,add_sql_vvvvvyi)
{
	// set the function logic
	if ((source_vvvvvyi == Number('1')) && (add_sql_vvvvvyi == Number('1')))
	{
		jQuery('#jform_addtables-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_addtables-lbl').closest('.control-group').hide();
	}
}

// the vvvvvyk function
function vvvvvyk(add_custom_button_vvvvvyk)
{
	// set the function logic
	if (add_custom_button_vvvvvyk == Number('1'))
	{
		jQuery('#jform_custom_button-lbl').closest('.control-group').show();
		jQuery('#jform_php_controller-lbl').closest('.control-group').show();
		jQuery('#jform_php_controller_list-lbl').closest('.control-group').show();
		jQuery('#jform_php_model-lbl').closest('.control-group').show();
		jQuery('#jform_php_model_list-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_custom_button-lbl').closest('.control-group').hide();
		jQuery('#jform_php_controller-lbl').closest('.control-group').hide();
		jQuery('#jform_php_controller_list-lbl').closest('.control-group').hide();
		jQuery('#jform_php_model-lbl').closest('.control-group').hide();
		jQuery('#jform_php_model_list-lbl').closest('.control-group').hide();
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



document.addEventListener('DOMContentLoaded', () => {
	// check if this view has alias field
	checkAliasField();

	// check if this view has category field
	checkCategoryField();

	// get the linked details
	getLinked();

	// set button
	addButtonID('admin_fields', 'create_edit_buttons', 1); // <-- first

	const checkedRadio = document.querySelector("#jform_add_custom_import input[type='radio']:checked");
	const valueSwitch = checkedRadio ? checkedRadio.value : null;

	// now load the fields
	getAjaxDisplay('admin_fields');
	getAjaxDisplay('admin_fields_conditions');
	getAjaxDisplay('admin_fields_relations');

	// set button
	addButtonID('admin_fields_conditions', 'create_edit_buttons', 1); // <-- second

	// set button to create more fields
	addButton('field', 'create_edit_buttons'); // <-- third

	// set button
	addButtonID('admin_fields_relations', 'create_edit_buttons', 1); // <-- forth

	// set button
	addButtonID('admin_custom_tabs', 'addtabs-lbl', 1); // <-- fifth

	// check and load all the customcode edit buttons
	getEditCustomCodeButtons();
});

/**
 * Remove all matched elements from the DOM.
 *
 * @param {string} selector
 * @returns {void}
 */
function removeElements(selector) {
	document.querySelectorAll(selector).forEach((element) => {
		element.remove();
	});
}

/**
 * Remove the closest parent matching the selector for all matched elements.
 *
 * @param {string} selector
 * @param {string} closestSelector
 * @returns {void}
 */
function removeClosest(selector, closestSelector) {
	document.querySelectorAll(selector).forEach((element) => {
		const parent = element.closest(closestSelector);

		if (parent) {
			parent.remove();
		}
	});
}

/**
 * Set HTML content on an element if it exists.
 *
 * @param {string} selector
 * @param {string} html
 * @returns {void}
 */
function setHTML(selector, html) {
	const element = document.querySelector(selector);

	if (element) {
		element.innerHTML = html;
	}
}

/**
 * Set value on an element if it exists.
 *
 * @param {string} selector
 * @param {string} value
 * @returns {void}
 */
function setValue(selector, value) {
	const element = document.querySelector(selector);

	if (element) {
		element.value = value;
	}
}

/**
 * Get value from an element if it exists.
 *
 * @param {string} selector
 * @returns {string}
 */
function getValue(selector) {
	const element = document.querySelector(selector);

	return element ? element.value : '';
}

/**
 * Check whether alias field support exists.
 *
 * @returns {void}
 */
function checkAliasField() {
	getCodeFrom_server(1, 'type', 'type', 'checkAliasField')
		.then((result) => {
			if (result) {
				// remove the notice
				removeElements('.note_create_edit_notice_p');
			} else {
				// hide everything about alias management
				removeClosest('#jform_alias_builder_type', '.control-group');
				removeClosest('#jform_alias_builder', '.control-group');
				removeClosest('.note_alias_builder_default', '.control-group');
				removeClosest('.note_alias_builder_custom', '.control-group');
			}
		})
		.catch((error) => {
			console.error('checkAliasField failed:', error);
		});
}

/**
 * Check whether category field support exists.
 *
 * @returns {void}
 */
function checkCategoryField() {
	getCodeFrom_server(1, 'type', 'type', 'checkCategoryField')
		.then((result) => {
			if (result) {
				// remove the notice
				removeElements('.note_create_edit_notice_p');
			} else {
				// hide everything about category management
				removeClosest('#jform_add_category_submenu', '.control-group');
				removeClosest('.note_category_menu_switch', '.control-group');
			}
		})
		.catch((error) => {
			console.error('checkCategoryField failed:', error);
		});
}

/**
 * Load AJAX display content for the given type.
 *
 * @param {string} type
 * @returns {void}
 */
function getAjaxDisplay(type) {
	getCodeFrom_server(1, type, 'type', 'getAjaxDisplay')
		.then((result) => {
			if (result) {
				setHTML(`#display_${type}`, result);
			}

			// set button
			addButtonID(type, `header_${type}_buttons`, 2); // <-- little edit button
		})
		.catch((error) => {
			console.error(`getAjaxDisplay failed for type "${type}":`, error);
		});
}

/**
 * Get table columns and set them into the sourcemap textarea.
 *
 * @param {string} fieldKey
 * @param {string|number} table_
 * @param {string|number} nr_
 * @returns {void}
 */
function getTableColumns(fieldKey, table_, nr_) {
	const tableSelector = `#jform_addtables_${table_}addtables${fieldKey}${nr_}_table`;
	const sourcemapSelector = `textarea#jform_addtables_${table_}addtables${fieldKey}${nr_}_sourcemap`;
	const tableElement = document.querySelector(tableSelector);

	// first check if the field is set
	if (tableElement) {
		const tableName = tableElement.value || '';

		getCodeFrom_server(1, tableName, 'table', 'tableColumns')
			.then((result) => {
				setValue(sourcemapSelector, result || '');
			})
			.catch((error) => {
				console.error(`getTableColumns failed for table "${tableName}":`, error);
				setValue(sourcemapSelector, '');
			});
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
 * Request a button ID from the server.
 *
 * @param  {string} type        The button type identifier.
 * @param  {number} size        The button size (must be > 0).
 * @global  {string} token       The Joomla CSRF token name.
 * @global  {string} vastDevMod  Optional developer mode flag.
 *
 * @return {Promise<object|null>}  JSON response or null on failure.
 * @since  3.1.3
 */
async function addButtonID_server(type, size) {
	try {
		// --- Validate parameters ---
		if (typeof type !== 'string' || !type.trim()) {
			console.error('[addButtonID_server] Invalid type provided:', type);
			return null;
		}
		if (typeof size !== 'number' || size <= 0) {
			console.error('[addButtonID_server] Invalid size provided:', size);
			return null;
		}
		if (typeof token !== 'string' || !token.trim()) {
			console.error('[addButtonID_server] Missing CSRF token.');
			return null;
		}

		// --- Build request URL ---
		const baseUrl = 'index.php';
		const params = new URLSearchParams({
			option: 'com_componentbuilder',
			task: 'ajax.getButtonID',
			format: 'json',
			raw: 'true',
			[token]: '1',
			type: type,
			size: size
		});
		if (vastDevMod) params.append('vdm', vastDevMod);
		const fullUrl = JRouter(`${baseUrl}?${params.toString()}`);

		// --- Perform fetch ---
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
			console.error(`[addButtonID_server] Server responded with ${response.status}: ${response.statusText}`);
			return null;
		}

		// --- Parse JSON result ---
		const data = await response.json();
		return data ?? null;

	} catch (error) {
		console.error('[addButtonID_server] Fetch operation failed:', error);
		return null;
	}
}

/**
 * Insert a button ID result into the DOM.
 *
 * @param  {string} type        The button type identifier.
 * @param  {string} where       The DOM element ID or selector where to inject the result.
 * @param  {number} size        The button size (default: 1).
 * @global  {string} token       The Joomla CSRF token name.
 * @global  {string} vastDevMod  Optional developer mode flag.
 *
 * @return {Promise<void>}
 * @since  3.1.3
 */
async function addButtonID(type, where, size) {
	try {
		const result = await addButtonID_server(type, size);

		if (!result) {
			console.warn('[addButtonID] No data returned.');
			return;
		}

		// Find target element
		const target =
			document.querySelector(`#${where}`) ||
			document.querySelector(`#jform_${where}`);

		if (!target) {
			console.error(`[addButtonID] Target element "${where}" not found.`);
			return;
		}

		// Insert the content
		if (size === 2) {
			target.innerHTML = result;
		} else {
			addData(result, `#jform_${where}`);
		}

	} catch (error) {
		console.error('[addButtonID] Error while inserting button ID:', error);
	}
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

function getLinked() {
	getCodeFrom_server(1, 'type', 'type', 'getLinked').then(function(result) {
		if (result.error) {
			console.error(result.error);
		} else if (result) {
			document.getElementById('display_linked_to').innerHTML = result;
		}
	});
}
