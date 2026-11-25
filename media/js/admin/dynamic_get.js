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
var updateDynamicGetConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateDynamicGetConditions()
{
	if (updateDynamicGetConditionsBaseline === null)
	{
		updateDynamicGetConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_getcustom","#jform_select_all","#jform_view_table_main","#jform_view_selection","#jform_db_table_main","#jform_db_selection","#jform_php_calculation-lbl",".note_calculation_item",".note_calculation_items","#jform_php_custom_get-lbl","#jform_filter-lbl","#jform_global-lbl","#jform_group-lbl","#jform_order-lbl","#jform_where-lbl","#jform_join_db_table-lbl","#jform_join_view_table-lbl","#jform_php_before_getitem-lbl","#jform_php_after_getitem-lbl","#jform_add_php_after_getitem","#jform_add_php_before_getitem","#jform_php_getlistquery-lbl","#jform_php_before_getitems-lbl","#jform_php_after_getitems-lbl","#jform_add_php_after_getitems","#jform_add_php_before_getitems","#jform_add_php_getlistquery","#jform_pagination","#jform_add_php_router_parse","#jform_php_router_parse","#jform_plugin_events"];
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
					updateDynamicGetConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = ["getcustom","view_selection","db_table_main","db_selection","add_php_after_getitem","add_php_before_getitem","add_php_after_getitems","add_php_before_getitems","add_php_getlistquery","pagination","add_php_router_parse","php_router_parse"];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateDynamicGetConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateDynamicGetConditionsBaseline.containers.length; index++)
	{
		var baseline = updateDynamicGetConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateDynamicGetConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var gettype_vvvvvzi = jQuery("#jform_gettype").val();
	vvvvvzi(gettype_vvvvvzi);

	var main_source_vvvvvzj = jQuery("#jform_main_source").val();
	vvvvvzj(main_source_vvvvvzj);

	var main_source_vvvvvzk = jQuery("#jform_main_source").val();
	vvvvvzk(main_source_vvvvvzk);

	var main_source_vvvvvzl = jQuery("#jform_main_source").val();
	vvvvvzl(main_source_vvvvvzl);

	var main_source_vvvvvzm = jQuery("#jform_main_source").val();
	vvvvvzm(main_source_vvvvvzm);

	var main_source_vvvvvzn = jQuery("#jform_main_source").val();
	vvvvvzn(main_source_vvvvvzn);

	var addcalculation_vvvvvzo = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	vvvvvzo(addcalculation_vvvvvzo);

	var addcalculation_vvvvvzp = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzp = jQuery("#jform_gettype").val();
	vvvvvzp(addcalculation_vvvvvzp,gettype_vvvvvzp);

	var addcalculation_vvvvvzq = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzq = jQuery("#jform_gettype").val();
	vvvvvzq(addcalculation_vvvvvzq,gettype_vvvvvzq);

	var main_source_vvvvvzt = jQuery("#jform_main_source").val();
	vvvvvzt(main_source_vvvvvzt);

	var main_source_vvvvvzu = jQuery("#jform_main_source").val();
	vvvvvzu(main_source_vvvvvzu);

	var add_php_before_getitem_vvvvvzv = jQuery("#jform_add_php_before_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzv = jQuery("#jform_gettype").val();
	vvvvvzv(add_php_before_getitem_vvvvvzv,gettype_vvvvvzv);

	var add_php_after_getitem_vvvvvzw = jQuery("#jform_add_php_after_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzw = jQuery("#jform_gettype").val();
	vvvvvzw(add_php_after_getitem_vvvvvzw,gettype_vvvvvzw);

	var gettype_vvvvvzy = jQuery("#jform_gettype").val();
	vvvvvzy(gettype_vvvvvzy);

	var add_php_getlistquery_vvvvvzz = jQuery("#jform_add_php_getlistquery input[type='radio']:checked").val();
	var gettype_vvvvvzz = jQuery("#jform_gettype").val();
	vvvvvzz(add_php_getlistquery_vvvvvzz,gettype_vvvvvzz);

	var add_php_before_getitems_vvvvwaa = jQuery("#jform_add_php_before_getitems input[type='radio']:checked").val();
	var gettype_vvvvwaa = jQuery("#jform_gettype").val();
	vvvvwaa(add_php_before_getitems_vvvvwaa,gettype_vvvvwaa);

	var add_php_after_getitems_vvvvwab = jQuery("#jform_add_php_after_getitems input[type='radio']:checked").val();
	var gettype_vvvvwab = jQuery("#jform_gettype").val();
	vvvvwab(add_php_after_getitems_vvvvwab,gettype_vvvvwab);

	var gettype_vvvvwad = jQuery("#jform_gettype").val();
	vvvvwad(gettype_vvvvwad);

	var gettype_vvvvwae = jQuery("#jform_gettype").val();
	vvvvwae(gettype_vvvvwae);

	var gettype_vvvvwaf = jQuery("#jform_gettype").val();
	vvvvwaf(gettype_vvvvwaf);

	var gettype_vvvvwag = jQuery("#jform_gettype").val();
	var add_php_router_parse_vvvvwag = jQuery("#jform_add_php_router_parse input[type='radio']:checked").val();
	vvvvwag(gettype_vvvvwag,add_php_router_parse_vvvvwag);

	var gettype_vvvvwai = jQuery("#jform_gettype").val();
	vvvvwai(gettype_vvvvwai);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateDynamicGetConditions);

// the vvvvvzi function
function vvvvvzi(gettype_vvvvvzi)
{
	if (isSet(gettype_vvvvvzi) && gettype_vvvvvzi.constructor !== Array)
	{
		var temp_vvvvvzi = gettype_vvvvvzi;
		var gettype_vvvvvzi = [];
		gettype_vvvvvzi.push(temp_vvvvvzi);
	}
	else if (!isSet(gettype_vvvvvzi))
	{
		var gettype_vvvvvzi = [];
	}
	var gettype = gettype_vvvvvzi.some(gettype_vvvvvzi_SomeFunc);


	// set this function logic
	if (gettype)
	{
		jQuery('#jform_getcustom').closest('.control-group').show();
		// Add required attributes to getcustom.
		jQuery('#jform_getcustom').prop('required', true);
		jQuery('#jform_getcustom').attr('aria-required', true);
		jQuery('#jform_getcustom').addClass('required');
	}
	else
	{
		jQuery('#jform_getcustom').closest('.control-group').hide();
		// Remove required attributes from getcustom.
		jQuery('#jform_getcustom').removeAttr('required');
		jQuery('#jform_getcustom').removeAttr('aria-required');
		jQuery('#jform_getcustom').removeClass('required');
	}
}

// the vvvvvzi Some function
function gettype_vvvvvzi_SomeFunc(gettype_vvvvvzi)
{
	// set the function logic
	if (gettype_vvvvvzi == Number('3') || gettype_vvvvvzi == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvvzj function
function vvvvvzj(main_source_vvvvvzj)
{
	if (isSet(main_source_vvvvvzj) && main_source_vvvvvzj.constructor !== Array)
	{
		var temp_vvvvvzj = main_source_vvvvvzj;
		var main_source_vvvvvzj = [];
		main_source_vvvvvzj.push(temp_vvvvvzj);
	}
	else if (!isSet(main_source_vvvvvzj))
	{
		var main_source_vvvvvzj = [];
	}
	var main_source = main_source_vvvvvzj.some(main_source_vvvvvzj_SomeFunc);


	// set this function logic
	if (main_source)
	{
		jQuery('#jform_select_all').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_select_all').closest('.control-group').hide();
	}
}

// the vvvvvzj Some function
function main_source_vvvvvzj_SomeFunc(main_source_vvvvvzj)
{
	// set the function logic
	if (main_source_vvvvvzj == Number('1') || main_source_vvvvvzj == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvvzk function
function vvvvvzk(main_source_vvvvvzk)
{
	if (isSet(main_source_vvvvvzk) && main_source_vvvvvzk.constructor !== Array)
	{
		var temp_vvvvvzk = main_source_vvvvvzk;
		var main_source_vvvvvzk = [];
		main_source_vvvvvzk.push(temp_vvvvvzk);
	}
	else if (!isSet(main_source_vvvvvzk))
	{
		var main_source_vvvvvzk = [];
	}
	var main_source = main_source_vvvvvzk.some(main_source_vvvvvzk_SomeFunc);


	// set this function logic
	if (main_source)
	{
		jQuery('#jform_view_table_main').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_view_table_main').closest('.control-group').hide();
	}
}

// the vvvvvzk Some function
function main_source_vvvvvzk_SomeFunc(main_source_vvvvvzk)
{
	// set the function logic
	if (main_source_vvvvvzk == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvvzl function
function vvvvvzl(main_source_vvvvvzl)
{
	if (isSet(main_source_vvvvvzl) && main_source_vvvvvzl.constructor !== Array)
	{
		var temp_vvvvvzl = main_source_vvvvvzl;
		var main_source_vvvvvzl = [];
		main_source_vvvvvzl.push(temp_vvvvvzl);
	}
	else if (!isSet(main_source_vvvvvzl))
	{
		var main_source_vvvvvzl = [];
	}
	var main_source = main_source_vvvvvzl.some(main_source_vvvvvzl_SomeFunc);


	// set this function logic
	if (main_source)
	{
		jQuery('#jform_view_selection').closest('.control-group').show();
		// Add required attributes to view_selection.
		jQuery('#jform_view_selection').prop('required', true);
		jQuery('#jform_view_selection').attr('aria-required', true);
		jQuery('#jform_view_selection').addClass('required');
	}
	else
	{
		jQuery('#jform_view_selection').closest('.control-group').hide();
		// Remove required attributes from view_selection.
		jQuery('#jform_view_selection').removeAttr('required');
		jQuery('#jform_view_selection').removeAttr('aria-required');
		jQuery('#jform_view_selection').removeClass('required');
	}
}

// the vvvvvzl Some function
function main_source_vvvvvzl_SomeFunc(main_source_vvvvvzl)
{
	// set the function logic
	if (main_source_vvvvvzl == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvvzm function
function vvvvvzm(main_source_vvvvvzm)
{
	if (isSet(main_source_vvvvvzm) && main_source_vvvvvzm.constructor !== Array)
	{
		var temp_vvvvvzm = main_source_vvvvvzm;
		var main_source_vvvvvzm = [];
		main_source_vvvvvzm.push(temp_vvvvvzm);
	}
	else if (!isSet(main_source_vvvvvzm))
	{
		var main_source_vvvvvzm = [];
	}
	var main_source = main_source_vvvvvzm.some(main_source_vvvvvzm_SomeFunc);


	// set this function logic
	if (main_source)
	{
		jQuery('#jform_db_table_main').closest('.control-group').show();
		// Add required attributes to db_table_main.
		jQuery('#jform_db_table_main').prop('required', true);
		jQuery('#jform_db_table_main').attr('aria-required', true);
		jQuery('#jform_db_table_main').addClass('required');
	}
	else
	{
		jQuery('#jform_db_table_main').closest('.control-group').hide();
		// Remove required attributes from db_table_main.
		jQuery('#jform_db_table_main').removeAttr('required');
		jQuery('#jform_db_table_main').removeAttr('aria-required');
		jQuery('#jform_db_table_main').removeClass('required');
	}
}

// the vvvvvzm Some function
function main_source_vvvvvzm_SomeFunc(main_source_vvvvvzm)
{
	// set the function logic
	if (main_source_vvvvvzm == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvvzn function
function vvvvvzn(main_source_vvvvvzn)
{
	if (isSet(main_source_vvvvvzn) && main_source_vvvvvzn.constructor !== Array)
	{
		var temp_vvvvvzn = main_source_vvvvvzn;
		var main_source_vvvvvzn = [];
		main_source_vvvvvzn.push(temp_vvvvvzn);
	}
	else if (!isSet(main_source_vvvvvzn))
	{
		var main_source_vvvvvzn = [];
	}
	var main_source = main_source_vvvvvzn.some(main_source_vvvvvzn_SomeFunc);


	// set this function logic
	if (main_source)
	{
		jQuery('#jform_db_selection').closest('.control-group').show();
		// Add required attributes to db_selection.
		jQuery('#jform_db_selection').prop('required', true);
		jQuery('#jform_db_selection').attr('aria-required', true);
		jQuery('#jform_db_selection').addClass('required');
	}
	else
	{
		jQuery('#jform_db_selection').closest('.control-group').hide();
		// Remove required attributes from db_selection.
		jQuery('#jform_db_selection').removeAttr('required');
		jQuery('#jform_db_selection').removeAttr('aria-required');
		jQuery('#jform_db_selection').removeClass('required');
	}
}

// the vvvvvzn Some function
function main_source_vvvvvzn_SomeFunc(main_source_vvvvvzn)
{
	// set the function logic
	if (main_source_vvvvvzn == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvvzo function
function vvvvvzo(addcalculation_vvvvvzo)
{
	// set the function logic
	if (addcalculation_vvvvvzo == Number('1'))
	{
		jQuery('#jform_php_calculation-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_calculation-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzp function
function vvvvvzp(addcalculation_vvvvvzp,gettype_vvvvvzp)
{
	if (isSet(addcalculation_vvvvvzp) && addcalculation_vvvvvzp.constructor !== Array)
	{
		var temp_vvvvvzp = addcalculation_vvvvvzp;
		var addcalculation_vvvvvzp = [];
		addcalculation_vvvvvzp.push(temp_vvvvvzp);
	}
	else if (!isSet(addcalculation_vvvvvzp))
	{
		var addcalculation_vvvvvzp = [];
	}
	var addcalculation = addcalculation_vvvvvzp.some(addcalculation_vvvvvzp_SomeFunc);

	if (isSet(gettype_vvvvvzp) && gettype_vvvvvzp.constructor !== Array)
	{
		var temp_vvvvvzp = gettype_vvvvvzp;
		var gettype_vvvvvzp = [];
		gettype_vvvvvzp.push(temp_vvvvvzp);
	}
	else if (!isSet(gettype_vvvvvzp))
	{
		var gettype_vvvvvzp = [];
	}
	var gettype = gettype_vvvvvzp.some(gettype_vvvvvzp_SomeFunc);


	// set this function logic
	if (addcalculation && gettype)
	{
		jQuery('.note_calculation_item').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_calculation_item').closest('.control-group').hide();
	}
}

// the vvvvvzp Some function
function addcalculation_vvvvvzp_SomeFunc(addcalculation_vvvvvzp)
{
	// set the function logic
	if (addcalculation_vvvvvzp == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvvzp Some function
function gettype_vvvvvzp_SomeFunc(gettype_vvvvvzp)
{
	// set the function logic
	if (gettype_vvvvvzp == Number('1') || gettype_vvvvvzp == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvvzq function
function vvvvvzq(addcalculation_vvvvvzq,gettype_vvvvvzq)
{
	if (isSet(addcalculation_vvvvvzq) && addcalculation_vvvvvzq.constructor !== Array)
	{
		var temp_vvvvvzq = addcalculation_vvvvvzq;
		var addcalculation_vvvvvzq = [];
		addcalculation_vvvvvzq.push(temp_vvvvvzq);
	}
	else if (!isSet(addcalculation_vvvvvzq))
	{
		var addcalculation_vvvvvzq = [];
	}
	var addcalculation = addcalculation_vvvvvzq.some(addcalculation_vvvvvzq_SomeFunc);

	if (isSet(gettype_vvvvvzq) && gettype_vvvvvzq.constructor !== Array)
	{
		var temp_vvvvvzq = gettype_vvvvvzq;
		var gettype_vvvvvzq = [];
		gettype_vvvvvzq.push(temp_vvvvvzq);
	}
	else if (!isSet(gettype_vvvvvzq))
	{
		var gettype_vvvvvzq = [];
	}
	var gettype = gettype_vvvvvzq.some(gettype_vvvvvzq_SomeFunc);


	// set this function logic
	if (addcalculation && gettype)
	{
		jQuery('.note_calculation_items').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_calculation_items').closest('.control-group').hide();
	}
}

// the vvvvvzq Some function
function addcalculation_vvvvvzq_SomeFunc(addcalculation_vvvvvzq)
{
	// set the function logic
	if (addcalculation_vvvvvzq == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvvzq Some function
function gettype_vvvvvzq_SomeFunc(gettype_vvvvvzq)
{
	// set the function logic
	if (gettype_vvvvvzq == Number('2') || gettype_vvvvvzq == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvvzt function
function vvvvvzt(main_source_vvvvvzt)
{
	if (isSet(main_source_vvvvvzt) && main_source_vvvvvzt.constructor !== Array)
	{
		var temp_vvvvvzt = main_source_vvvvvzt;
		var main_source_vvvvvzt = [];
		main_source_vvvvvzt.push(temp_vvvvvzt);
	}
	else if (!isSet(main_source_vvvvvzt))
	{
		var main_source_vvvvvzt = [];
	}
	var main_source = main_source_vvvvvzt.some(main_source_vvvvvzt_SomeFunc);


	// set this function logic
	if (main_source)
	{
		jQuery('#jform_php_custom_get-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_custom_get-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzt Some function
function main_source_vvvvvzt_SomeFunc(main_source_vvvvvzt)
{
	// set the function logic
	if (main_source_vvvvvzt == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvvzu function
function vvvvvzu(main_source_vvvvvzu)
{
	if (isSet(main_source_vvvvvzu) && main_source_vvvvvzu.constructor !== Array)
	{
		var temp_vvvvvzu = main_source_vvvvvzu;
		var main_source_vvvvvzu = [];
		main_source_vvvvvzu.push(temp_vvvvvzu);
	}
	else if (!isSet(main_source_vvvvvzu))
	{
		var main_source_vvvvvzu = [];
	}
	var main_source = main_source_vvvvvzu.some(main_source_vvvvvzu_SomeFunc);


	// set this function logic
	if (main_source)
	{
		jQuery('#jform_filter-lbl').closest('.control-group').show();
		jQuery('#jform_global-lbl').closest('.control-group').show();
		jQuery('#jform_group-lbl').closest('.control-group').show();
		jQuery('#jform_order-lbl').closest('.control-group').show();
		jQuery('#jform_where-lbl').closest('.control-group').show();
		jQuery('#jform_join_db_table-lbl').closest('.control-group').show();
		jQuery('#jform_join_view_table-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_filter-lbl').closest('.control-group').hide();
		jQuery('#jform_global-lbl').closest('.control-group').hide();
		jQuery('#jform_group-lbl').closest('.control-group').hide();
		jQuery('#jform_order-lbl').closest('.control-group').hide();
		jQuery('#jform_where-lbl').closest('.control-group').hide();
		jQuery('#jform_join_db_table-lbl').closest('.control-group').hide();
		jQuery('#jform_join_view_table-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzu Some function
function main_source_vvvvvzu_SomeFunc(main_source_vvvvvzu)
{
	// set the function logic
	if (main_source_vvvvvzu == Number('1') || main_source_vvvvvzu == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvvzv function
function vvvvvzv(add_php_before_getitem_vvvvvzv,gettype_vvvvvzv)
{
	if (isSet(add_php_before_getitem_vvvvvzv) && add_php_before_getitem_vvvvvzv.constructor !== Array)
	{
		var temp_vvvvvzv = add_php_before_getitem_vvvvvzv;
		var add_php_before_getitem_vvvvvzv = [];
		add_php_before_getitem_vvvvvzv.push(temp_vvvvvzv);
	}
	else if (!isSet(add_php_before_getitem_vvvvvzv))
	{
		var add_php_before_getitem_vvvvvzv = [];
	}
	var add_php_before_getitem = add_php_before_getitem_vvvvvzv.some(add_php_before_getitem_vvvvvzv_SomeFunc);

	if (isSet(gettype_vvvvvzv) && gettype_vvvvvzv.constructor !== Array)
	{
		var temp_vvvvvzv = gettype_vvvvvzv;
		var gettype_vvvvvzv = [];
		gettype_vvvvvzv.push(temp_vvvvvzv);
	}
	else if (!isSet(gettype_vvvvvzv))
	{
		var gettype_vvvvvzv = [];
	}
	var gettype = gettype_vvvvvzv.some(gettype_vvvvvzv_SomeFunc);


	// set this function logic
	if (add_php_before_getitem && gettype)
	{
		jQuery('#jform_php_before_getitem-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_before_getitem-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzv Some function
function add_php_before_getitem_vvvvvzv_SomeFunc(add_php_before_getitem_vvvvvzv)
{
	// set the function logic
	if (add_php_before_getitem_vvvvvzv == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvvzv Some function
function gettype_vvvvvzv_SomeFunc(gettype_vvvvvzv)
{
	// set the function logic
	if (gettype_vvvvvzv == Number('1') || gettype_vvvvvzv == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvvzw function
function vvvvvzw(add_php_after_getitem_vvvvvzw,gettype_vvvvvzw)
{
	if (isSet(add_php_after_getitem_vvvvvzw) && add_php_after_getitem_vvvvvzw.constructor !== Array)
	{
		var temp_vvvvvzw = add_php_after_getitem_vvvvvzw;
		var add_php_after_getitem_vvvvvzw = [];
		add_php_after_getitem_vvvvvzw.push(temp_vvvvvzw);
	}
	else if (!isSet(add_php_after_getitem_vvvvvzw))
	{
		var add_php_after_getitem_vvvvvzw = [];
	}
	var add_php_after_getitem = add_php_after_getitem_vvvvvzw.some(add_php_after_getitem_vvvvvzw_SomeFunc);

	if (isSet(gettype_vvvvvzw) && gettype_vvvvvzw.constructor !== Array)
	{
		var temp_vvvvvzw = gettype_vvvvvzw;
		var gettype_vvvvvzw = [];
		gettype_vvvvvzw.push(temp_vvvvvzw);
	}
	else if (!isSet(gettype_vvvvvzw))
	{
		var gettype_vvvvvzw = [];
	}
	var gettype = gettype_vvvvvzw.some(gettype_vvvvvzw_SomeFunc);


	// set this function logic
	if (add_php_after_getitem && gettype)
	{
		jQuery('#jform_php_after_getitem-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_after_getitem-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzw Some function
function add_php_after_getitem_vvvvvzw_SomeFunc(add_php_after_getitem_vvvvvzw)
{
	// set the function logic
	if (add_php_after_getitem_vvvvvzw == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvvzw Some function
function gettype_vvvvvzw_SomeFunc(gettype_vvvvvzw)
{
	// set the function logic
	if (gettype_vvvvvzw == Number('1') || gettype_vvvvvzw == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvvzy function
function vvvvvzy(gettype_vvvvvzy)
{
	if (isSet(gettype_vvvvvzy) && gettype_vvvvvzy.constructor !== Array)
	{
		var temp_vvvvvzy = gettype_vvvvvzy;
		var gettype_vvvvvzy = [];
		gettype_vvvvvzy.push(temp_vvvvvzy);
	}
	else if (!isSet(gettype_vvvvvzy))
	{
		var gettype_vvvvvzy = [];
	}
	var gettype = gettype_vvvvvzy.some(gettype_vvvvvzy_SomeFunc);


	// set this function logic
	if (gettype)
	{
		jQuery('#jform_add_php_after_getitem').closest('.control-group').show();
		// Add required attributes to add_php_after_getitem.
		jQuery('#jform_add_php_after_getitem').prop('required', true);
		jQuery('#jform_add_php_after_getitem').attr('aria-required', true);
		jQuery('#jform_add_php_after_getitem').addClass('required');
		jQuery('#jform_add_php_before_getitem').closest('.control-group').show();
		// Add required attributes to add_php_before_getitem.
		jQuery('#jform_add_php_before_getitem').prop('required', true);
		jQuery('#jform_add_php_before_getitem').attr('aria-required', true);
		jQuery('#jform_add_php_before_getitem').addClass('required');
	}
	else
	{
		jQuery('#jform_add_php_after_getitem').closest('.control-group').hide();
		// Remove required attributes from add_php_after_getitem.
		jQuery('#jform_add_php_after_getitem').removeAttr('required');
		jQuery('#jform_add_php_after_getitem').removeAttr('aria-required');
		jQuery('#jform_add_php_after_getitem').removeClass('required');
		jQuery('#jform_add_php_before_getitem').closest('.control-group').hide();
		// Remove required attributes from add_php_before_getitem.
		jQuery('#jform_add_php_before_getitem').removeAttr('required');
		jQuery('#jform_add_php_before_getitem').removeAttr('aria-required');
		jQuery('#jform_add_php_before_getitem').removeClass('required');
	}
}

// the vvvvvzy Some function
function gettype_vvvvvzy_SomeFunc(gettype_vvvvvzy)
{
	// set the function logic
	if (gettype_vvvvvzy == Number('1') || gettype_vvvvvzy == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvvzz function
function vvvvvzz(add_php_getlistquery_vvvvvzz,gettype_vvvvvzz)
{
	if (isSet(add_php_getlistquery_vvvvvzz) && add_php_getlistquery_vvvvvzz.constructor !== Array)
	{
		var temp_vvvvvzz = add_php_getlistquery_vvvvvzz;
		var add_php_getlistquery_vvvvvzz = [];
		add_php_getlistquery_vvvvvzz.push(temp_vvvvvzz);
	}
	else if (!isSet(add_php_getlistquery_vvvvvzz))
	{
		var add_php_getlistquery_vvvvvzz = [];
	}
	var add_php_getlistquery = add_php_getlistquery_vvvvvzz.some(add_php_getlistquery_vvvvvzz_SomeFunc);

	if (isSet(gettype_vvvvvzz) && gettype_vvvvvzz.constructor !== Array)
	{
		var temp_vvvvvzz = gettype_vvvvvzz;
		var gettype_vvvvvzz = [];
		gettype_vvvvvzz.push(temp_vvvvvzz);
	}
	else if (!isSet(gettype_vvvvvzz))
	{
		var gettype_vvvvvzz = [];
	}
	var gettype = gettype_vvvvvzz.some(gettype_vvvvvzz_SomeFunc);


	// set this function logic
	if (add_php_getlistquery && gettype)
	{
		jQuery('#jform_php_getlistquery-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_getlistquery-lbl').closest('.control-group').hide();
	}
}

// the vvvvvzz Some function
function add_php_getlistquery_vvvvvzz_SomeFunc(add_php_getlistquery_vvvvvzz)
{
	// set the function logic
	if (add_php_getlistquery_vvvvvzz == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvvzz Some function
function gettype_vvvvvzz_SomeFunc(gettype_vvvvvzz)
{
	// set the function logic
	if (gettype_vvvvvzz == Number('2') || gettype_vvvvvzz == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvwaa function
function vvvvwaa(add_php_before_getitems_vvvvwaa,gettype_vvvvwaa)
{
	if (isSet(add_php_before_getitems_vvvvwaa) && add_php_before_getitems_vvvvwaa.constructor !== Array)
	{
		var temp_vvvvwaa = add_php_before_getitems_vvvvwaa;
		var add_php_before_getitems_vvvvwaa = [];
		add_php_before_getitems_vvvvwaa.push(temp_vvvvwaa);
	}
	else if (!isSet(add_php_before_getitems_vvvvwaa))
	{
		var add_php_before_getitems_vvvvwaa = [];
	}
	var add_php_before_getitems = add_php_before_getitems_vvvvwaa.some(add_php_before_getitems_vvvvwaa_SomeFunc);

	if (isSet(gettype_vvvvwaa) && gettype_vvvvwaa.constructor !== Array)
	{
		var temp_vvvvwaa = gettype_vvvvwaa;
		var gettype_vvvvwaa = [];
		gettype_vvvvwaa.push(temp_vvvvwaa);
	}
	else if (!isSet(gettype_vvvvwaa))
	{
		var gettype_vvvvwaa = [];
	}
	var gettype = gettype_vvvvwaa.some(gettype_vvvvwaa_SomeFunc);


	// set this function logic
	if (add_php_before_getitems && gettype)
	{
		jQuery('#jform_php_before_getitems-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_before_getitems-lbl').closest('.control-group').hide();
	}
}

// the vvvvwaa Some function
function add_php_before_getitems_vvvvwaa_SomeFunc(add_php_before_getitems_vvvvwaa)
{
	// set the function logic
	if (add_php_before_getitems_vvvvwaa == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwaa Some function
function gettype_vvvvwaa_SomeFunc(gettype_vvvvwaa)
{
	// set the function logic
	if (gettype_vvvvwaa == Number('2') || gettype_vvvvwaa == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvwab function
function vvvvwab(add_php_after_getitems_vvvvwab,gettype_vvvvwab)
{
	if (isSet(add_php_after_getitems_vvvvwab) && add_php_after_getitems_vvvvwab.constructor !== Array)
	{
		var temp_vvvvwab = add_php_after_getitems_vvvvwab;
		var add_php_after_getitems_vvvvwab = [];
		add_php_after_getitems_vvvvwab.push(temp_vvvvwab);
	}
	else if (!isSet(add_php_after_getitems_vvvvwab))
	{
		var add_php_after_getitems_vvvvwab = [];
	}
	var add_php_after_getitems = add_php_after_getitems_vvvvwab.some(add_php_after_getitems_vvvvwab_SomeFunc);

	if (isSet(gettype_vvvvwab) && gettype_vvvvwab.constructor !== Array)
	{
		var temp_vvvvwab = gettype_vvvvwab;
		var gettype_vvvvwab = [];
		gettype_vvvvwab.push(temp_vvvvwab);
	}
	else if (!isSet(gettype_vvvvwab))
	{
		var gettype_vvvvwab = [];
	}
	var gettype = gettype_vvvvwab.some(gettype_vvvvwab_SomeFunc);


	// set this function logic
	if (add_php_after_getitems && gettype)
	{
		jQuery('#jform_php_after_getitems-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_php_after_getitems-lbl').closest('.control-group').hide();
	}
}

// the vvvvwab Some function
function add_php_after_getitems_vvvvwab_SomeFunc(add_php_after_getitems_vvvvwab)
{
	// set the function logic
	if (add_php_after_getitems_vvvvwab == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwab Some function
function gettype_vvvvwab_SomeFunc(gettype_vvvvwab)
{
	// set the function logic
	if (gettype_vvvvwab == Number('2') || gettype_vvvvwab == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvwad function
function vvvvwad(gettype_vvvvwad)
{
	if (isSet(gettype_vvvvwad) && gettype_vvvvwad.constructor !== Array)
	{
		var temp_vvvvwad = gettype_vvvvwad;
		var gettype_vvvvwad = [];
		gettype_vvvvwad.push(temp_vvvvwad);
	}
	else if (!isSet(gettype_vvvvwad))
	{
		var gettype_vvvvwad = [];
	}
	var gettype = gettype_vvvvwad.some(gettype_vvvvwad_SomeFunc);


	// set this function logic
	if (gettype)
	{
		jQuery('#jform_add_php_after_getitems').closest('.control-group').show();
		// Add required attributes to add_php_after_getitems.
		jQuery('#jform_add_php_after_getitems').prop('required', true);
		jQuery('#jform_add_php_after_getitems').attr('aria-required', true);
		jQuery('#jform_add_php_after_getitems').addClass('required');
		jQuery('#jform_add_php_before_getitems').closest('.control-group').show();
		// Add required attributes to add_php_before_getitems.
		jQuery('#jform_add_php_before_getitems').prop('required', true);
		jQuery('#jform_add_php_before_getitems').attr('aria-required', true);
		jQuery('#jform_add_php_before_getitems').addClass('required');
		jQuery('#jform_add_php_getlistquery').closest('.control-group').show();
		// Add required attributes to add_php_getlistquery.
		jQuery('#jform_add_php_getlistquery').prop('required', true);
		jQuery('#jform_add_php_getlistquery').attr('aria-required', true);
		jQuery('#jform_add_php_getlistquery').addClass('required');
	}
	else
	{
		jQuery('#jform_add_php_after_getitems').closest('.control-group').hide();
		// Remove required attributes from add_php_after_getitems.
		jQuery('#jform_add_php_after_getitems').removeAttr('required');
		jQuery('#jform_add_php_after_getitems').removeAttr('aria-required');
		jQuery('#jform_add_php_after_getitems').removeClass('required');
		jQuery('#jform_add_php_before_getitems').closest('.control-group').hide();
		// Remove required attributes from add_php_before_getitems.
		jQuery('#jform_add_php_before_getitems').removeAttr('required');
		jQuery('#jform_add_php_before_getitems').removeAttr('aria-required');
		jQuery('#jform_add_php_before_getitems').removeClass('required');
		jQuery('#jform_add_php_getlistquery').closest('.control-group').hide();
		// Remove required attributes from add_php_getlistquery.
		jQuery('#jform_add_php_getlistquery').removeAttr('required');
		jQuery('#jform_add_php_getlistquery').removeAttr('aria-required');
		jQuery('#jform_add_php_getlistquery').removeClass('required');
	}
}

// the vvvvwad Some function
function gettype_vvvvwad_SomeFunc(gettype_vvvvwad)
{
	// set the function logic
	if (gettype_vvvvwad == Number('2') || gettype_vvvvwad == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvwae function
function vvvvwae(gettype_vvvvwae)
{
	if (isSet(gettype_vvvvwae) && gettype_vvvvwae.constructor !== Array)
	{
		var temp_vvvvwae = gettype_vvvvwae;
		var gettype_vvvvwae = [];
		gettype_vvvvwae.push(temp_vvvvwae);
	}
	else if (!isSet(gettype_vvvvwae))
	{
		var gettype_vvvvwae = [];
	}
	var gettype = gettype_vvvvwae.some(gettype_vvvvwae_SomeFunc);


	// set this function logic
	if (gettype)
	{
		jQuery('#jform_pagination').closest('.control-group').show();
		// Add required attributes to pagination.
		jQuery('#jform_pagination').prop('required', true);
		jQuery('#jform_pagination').attr('aria-required', true);
		jQuery('#jform_pagination').addClass('required');
	}
	else
	{
		jQuery('#jform_pagination').closest('.control-group').hide();
		// Remove required attributes from pagination.
		jQuery('#jform_pagination').removeAttr('required');
		jQuery('#jform_pagination').removeAttr('aria-required');
		jQuery('#jform_pagination').removeClass('required');
	}
}

// the vvvvwae Some function
function gettype_vvvvwae_SomeFunc(gettype_vvvvwae)
{
	// set the function logic
	if (gettype_vvvvwae == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwaf function
function vvvvwaf(gettype_vvvvwaf)
{
	if (isSet(gettype_vvvvwaf) && gettype_vvvvwaf.constructor !== Array)
	{
		var temp_vvvvwaf = gettype_vvvvwaf;
		var gettype_vvvvwaf = [];
		gettype_vvvvwaf.push(temp_vvvvwaf);
	}
	else if (!isSet(gettype_vvvvwaf))
	{
		var gettype_vvvvwaf = [];
	}
	var gettype = gettype_vvvvwaf.some(gettype_vvvvwaf_SomeFunc);


	// set this function logic
	if (gettype)
	{
		jQuery('#jform_add_php_router_parse').closest('.control-group').show();
		// Add required attributes to add_php_router_parse.
		jQuery('#jform_add_php_router_parse').prop('required', true);
		jQuery('#jform_add_php_router_parse').attr('aria-required', true);
		jQuery('#jform_add_php_router_parse').addClass('required');
	}
	else
	{
		jQuery('#jform_add_php_router_parse').closest('.control-group').hide();
		// Remove required attributes from add_php_router_parse.
		jQuery('#jform_add_php_router_parse').removeAttr('required');
		jQuery('#jform_add_php_router_parse').removeAttr('aria-required');
		jQuery('#jform_add_php_router_parse').removeClass('required');
	}
}

// the vvvvwaf Some function
function gettype_vvvvwaf_SomeFunc(gettype_vvvvwaf)
{
	// set the function logic
	if (gettype_vvvvwaf == Number('1') || gettype_vvvvwaf == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwag function
function vvvvwag(gettype_vvvvwag,add_php_router_parse_vvvvwag)
{
	if (isSet(gettype_vvvvwag) && gettype_vvvvwag.constructor !== Array)
	{
		var temp_vvvvwag = gettype_vvvvwag;
		var gettype_vvvvwag = [];
		gettype_vvvvwag.push(temp_vvvvwag);
	}
	else if (!isSet(gettype_vvvvwag))
	{
		var gettype_vvvvwag = [];
	}
	var gettype = gettype_vvvvwag.some(gettype_vvvvwag_SomeFunc);

	if (isSet(add_php_router_parse_vvvvwag) && add_php_router_parse_vvvvwag.constructor !== Array)
	{
		var temp_vvvvwag = add_php_router_parse_vvvvwag;
		var add_php_router_parse_vvvvwag = [];
		add_php_router_parse_vvvvwag.push(temp_vvvvwag);
	}
	else if (!isSet(add_php_router_parse_vvvvwag))
	{
		var add_php_router_parse_vvvvwag = [];
	}
	var add_php_router_parse = add_php_router_parse_vvvvwag.some(add_php_router_parse_vvvvwag_SomeFunc);


	// set this function logic
	if (gettype && add_php_router_parse)
	{
		jQuery('#jform_php_router_parse').closest('.control-group').show();
		// Add required attributes to php_router_parse.
		jQuery('#jform_php_router_parse').prop('required', true);
		jQuery('#jform_php_router_parse').attr('aria-required', true);
		jQuery('#jform_php_router_parse').addClass('required');
	}
	else
	{
		jQuery('#jform_php_router_parse').closest('.control-group').hide();
		// Remove required attributes from php_router_parse.
		jQuery('#jform_php_router_parse').removeAttr('required');
		jQuery('#jform_php_router_parse').removeAttr('aria-required');
		jQuery('#jform_php_router_parse').removeClass('required');
	}
}

// the vvvvwag Some function
function gettype_vvvvwag_SomeFunc(gettype_vvvvwag)
{
	// set the function logic
	if (gettype_vvvvwag == Number('1') || gettype_vvvvwag == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwag Some function
function add_php_router_parse_vvvvwag_SomeFunc(add_php_router_parse_vvvvwag)
{
	// set the function logic
	if (add_php_router_parse_vvvvwag == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwai function
function vvvvwai(gettype_vvvvwai)
{
	if (isSet(gettype_vvvvwai) && gettype_vvvvwai.constructor !== Array)
	{
		var temp_vvvvwai = gettype_vvvvwai;
		var gettype_vvvvwai = [];
		gettype_vvvvwai.push(temp_vvvvwai);
	}
	else if (!isSet(gettype_vvvvwai))
	{
		var gettype_vvvvwai = [];
	}
	var gettype = gettype_vvvvwai.some(gettype_vvvvwai_SomeFunc);


	// set this function logic
	if (gettype)
	{
		jQuery('#jform_plugin_events').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_plugin_events').closest('.control-group').hide();
	}
}

// the vvvvwai Some function
function gettype_vvvvwai_SomeFunc(gettype_vvvvwai)
{
	// set the function logic
	if (gettype_vvvvwai == Number('1'))
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


/* ======================================================================== *\
   ComponentBuilder – Sub‑form helpers
   Fully production‑ready, ESLint‑clean, duplicate‑ID safe
\* ======================================================================== */
(function () {
	'use strict';

	/* --------------------------------------------------------------------- *
	 |  Configuration helpers                                                |
	 * --------------------------------------------------------------------- */

	/**
	 * Return the Joomla‑router URL for the given AJAX task.
	 * Falls back to a raw path if JRouter() is not defined.
	 *
	 * @param  {string} task e.g. "ajax.viewTableColumns"
	 * @return {string}
	 */
	function route(task) {
		const url = `index.php?option=com_componentbuilder&task=${task}&format=json&raw=true`;
		return typeof window.JRouter === 'function' ? window.JRouter(url) : url;
	}

	/**
	 * CSRF token (expects a global `token` variable).
	 */
	const csrf = window.token ?? Joomla.getOptions('csrf.token');

	/* --------------------------------------------------------------------- *
	 |  Generic server fetcher                                               |
	 * --------------------------------------------------------------------- */

	/**
	 * Fetch column data for either a *view* or a *db* table.
	 *
	 * @param {"view"|"db"} type
	 * @param {string} idOrName   View → GUID, DB → table name
	 * @param {string} asKey      Alias key (usually "a")
	 * @param {number} rowType    1‑based row‑type index
	 * @return {Promise<any>}     JSON payload (resolved) or thrown Error
	 */
	function fetchColumns(type, idOrName, asKey, rowType) {
		if (!csrf || !idOrName || !asKey) {
			return Promise.reject(
				new Error('[fetchColumns] Missing CSRF token, alias or identifier')
			);
		}

		const task      = type === 'view' ? 'ajax.viewTableColumns' : 'ajax.dbTableColumns';
		const paramName = type === 'view' ? 'id' : 'name';
		const url       = `${route(task)}&${csrf}=1&as=${asKey}&type=${rowType}&${paramName}=${encodeURIComponent(idOrName)}`;

		return fetch(url, { method: 'GET' })
			.then(r => r.json());
	}

	/* --------------------------------------------------------------------- *
	 |  UI utilities                                                         |
	 * --------------------------------------------------------------------- */

	/**
	 * Safely fetch the radio value for “select all / custom”.
	 * @return {number} 1 → select all, 0 → custom
	 */
	function currentSelectAll() {
		const radio = /** @type {HTMLInputElement|null} */ (
			document.querySelector('#jform_select_all input[type="radio"]:checked')
		);
		return radio ? Number(radio.value) : 0;
	}

	/**
	 * Update the selection `<textarea>` (main or sub‑row) with new data.
	 *
	 * @param {string|false} data
	 * @param {"view"|"db"}  type
	 * @param {string|number} key      Field index inside the row
	 * @param {boolean}      main      TRUE → main selection textarea
	 * @param {number|string} table_   Join‑table index (sub‑rows)
	 * @param {number|string} nr_      Clone suffix (sub‑rows)
	 */
	function loadSelectionData(data, type, key, main, table_, nr_) {
		let selector;
		if (main) {
			selector = `textarea#jform_${key}_selection`;
		} else {
			selector = `textarea#jform_join_${type}_table${table_}_join_${type}_table${key}${nr_}_selection`;
		}

		const textarea = /** @type {HTMLTextAreaElement|null} */ (document.querySelector(selector));
		if (!textarea) {
			console.warn('[loadSelectionData] Textarea not found:', selector);
			return;
		}
		textarea.value = data || '';
	}

	/* --------------------------------------------------------------------- *
	 |  Public helpers (exposed via window.*)                                |
	 * --------------------------------------------------------------------- */

	/**
	 * Handle the “Select all / Custom select” radio buttons.
	 *
	 * @param {number} selectAll 1 → select all, 0 → custom
	 */
	function setSelectAll(selectAll) {
		const mainSource = Number(document.getElementById('jform_main_source')?.value ?? 0);
		const key        = mainSource === 1 ? 'view' : mainSource === 2 ? 'db' : null;
		if (!key) return;

		const textarea  = document.getElementById(`jform_${key}_selection`);
		if (!textarea) return;

		if (selectAll === 1) {
			textarea.value    = 'a.*';
			textarea.readOnly = true;
		} else {
			textarea.readOnly = false;

			/* Trigger a fresh column fetch so the user sees all fields. */
			if (key === 'view') {
				const guid = /** @type {HTMLInputElement} */ (
					document.getElementById('jform_view_table_main_id')
				)?.value;
				if (guid) {
					getViewTableColumns(guid, 'a', key, 3, true, '', '');
				}
			} else {
				const name = /** @type {HTMLSelectElement} */ (
					document.getElementById('jform_db_table_main')
				)?.value;
				if (name) {
					getDbTableColumns(name, 'a', key, 3, true, '', '');
				}
			}
		}
	}

	/**
	 * Wrapper around `fetchColumns("view", …)` that keeps the original
	 * call‑signature (`id, asKey, key, rowType, main, table_, nr_`).
	 */
	function getViewTableColumns(id, asKey, key, rowType, main, table_, nr_) {
		if (main && currentSelectAll() === 1) {
			setSelectAll(1);
			return;
		}
		fetchColumns('view', id, asKey, rowType)
			.then(res => {
				if (res?.error) {
					console.error(res.error);
					loadSelectionData(false, 'view', key, main, table_, nr_);
				} else {
					loadSelectionData(res, 'view', key, main, table_, nr_);
				}
			})
			.catch(err => {
				console.error(err);
				loadSelectionData(false, 'view', key, main, table_, nr_);
			});
	}

	/**
	 * Wrapper around `fetchColumns("db", …)` that keeps the original
	 * call‑signature (`name, asKey, key, rowType, main, table_, nr_`).
	 */
	function getDbTableColumns(name, asKey, key, rowType, main, table_, nr_) {
		if (main && currentSelectAll() === 1) {
			setSelectAll(1);
			return;
		}
		fetchColumns('db', name, asKey, rowType)
			.then(res => {
				if (res?.error) {
					console.error(res.error);
					loadSelectionData(false, 'db', key, main, table_, nr_);
				} else {
					loadSelectionData(res, 'db', key, main, table_, nr_);
				}
			})
			.catch(err => {
				console.error(err);
				loadSelectionData(false, 'db', key, main, table_, nr_);
			});
	}

	/* --------------------------------------------------------------------- *
	 |  updateSubItems – duplicate‑ID safe handler                           |
	 * --------------------------------------------------------------------- */

	/**
	 * Attach duplicate‑ID‑safe, delegated change handling to a sub‑form row.
	 *
	 * @param {string} fieldName "view" | "db"
	 * @param {number} fieldNr   Row‑field index
	 * @param {number} table_    Join‑table index
	 * @param {number} nr_       Clone suffix
	 */
	function updateSubItems(fieldName, fieldNr, table_, nr_) {

		/* Build selectors (works for hidden input, text input, select). */
		const base = `jform_join_${fieldName}_table${table_}_join_${fieldName}_table${fieldNr}${nr_}`;
		const sel  = {
			tableId : `#${base}_${fieldName}_table_id`, // hidden <input>
			table   : `#${base}_${fieldName}_table`,    // <select> OR dup. inputs
			alias   : `#${base}_as`,
			rowType : `#${base}_row_type`,
		};

		const adminForm = document.getElementById('adminForm');
		if (!adminForm) {
			console.error('[updateSubItems] #adminForm not found.');
			return;
		}

		/* Guard: avoid rebinding the same row. */
		if (adminForm.dataset[`boundFor${base}`]) return;
		adminForm.dataset[`boundFor${base}`] = 'true';

		const tableSelectors = fieldName === 'view'
			? [sel.tableId, sel.table]
			: [sel.table];

		const delegateSelectors = [
			...tableSelectors, sel.alias, sel.rowType,
		].join(', ');

		adminForm.addEventListener('change', handleChange);

		/* --- Delegated change handler ----------------------------------- */
		function handleChange(e) {
			if (!e.target.matches(delegateSelectors)) return;
			e.preventDefault();

			const tableEl   = pickElement(tableSelectors);
			const aliasEl   = pickElement(sel.alias);
			const rowTypeEl = pickElement(sel.rowType);

			if (!tableEl || !aliasEl || !rowTypeEl) return;

			const tableVal   = getElementValue(tableEl);
			const aliasVal   = getElementValue(aliasEl);
			const rowTypeVal = getElementValue(rowTypeEl);

			if (fieldName === 'view') {
				getViewTableColumns(
					tableVal, aliasVal, fieldNr, rowTypeVal, false, table_, nr_
				);
			} else {
				getDbTableColumns(
					tableVal, aliasVal, fieldNr, rowTypeVal, false, table_, nr_
				);
			}
		}

		/* --- Helper: choose the correct node among duplicated IDs -------- */
		function pickElement(selectors) {
			const nodes = [...[].concat(selectors).flatMap(
				sel => [...document.querySelectorAll(sel)]
			)];
			if (!nodes.length) return null;
			if (nodes.length === 1) return nodes[0];

			/* 1️⃣ Prefer hidden input with GUID length 38. */
			for (const n of nodes) {
				if (isHidden(n) && getElementValue(n).length === 38) return n;
			}
			/* 2️⃣ Any hidden input with non‑empty value. */
			for (const n of nodes) {
				if (isHidden(n) && getElementValue(n)) return n;
			}
			/* 3️⃣ Fallback: newest element (last in DOM order). */
			return nodes[nodes.length - 1];
		}

		function isHidden(el) {
			return el.tagName === 'INPUT' && el.type === 'hidden';
		}

		function getElementValue(el) {
			if (isHidden(el)) return el.value;
			if (el.tagName === 'SELECT') {
				const s = /** @type {HTMLSelectElement} */ (el);
				return s.selectedIndex >= 0 ? s.options[s.selectedIndex].value : '';
			}
			return '';
		}
	}

	/* --------------------------------------------------------------------- *
	 |  Expose public helpers                                                |
	 * --------------------------------------------------------------------- */
	window.setSelectAll          = setSelectAll;
	window.getViewTableColumns   = getViewTableColumns;
	window.getDbTableColumns     = getDbTableColumns;
	window.loadSelectionData     = loadSelectionData;
	window.updateSubItems        = updateSubItems;
})();

document.addEventListener('DOMContentLoaded', function() {
	// get the linked details
	getLinked();
	let valueSwitch = document.querySelector("#jform_add_php_router_parse input[type='radio']:checked").value;
	getDynamicScripts(valueSwitch);
	// check and load all the custom code edit buttons
	getEditCustomCodeButtons();
});

function getDynamicScripts(id) {
	if (id == 1) {
		// get the current values
		let current_router_parse = document.querySelector('textarea#jform_php_router_parse').value;
		// set the router parse method script
		if (current_router_parse.length == 0) {
			getCodeFrom_server(1, 'routerparse', 'type', 'getDynamicScripts').then(function(result) {
				if (result.error) {
					console.error(result.error);
				} else if (result) {
					document.querySelector('textarea#jform_php_router_parse').value = result;
				}
			});
		}
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

function getLinked() {
	getCodeFrom_server(1, 'type', 'type', 'getLinked').then(function(result) {
		if (result.error) {
			console.error(result.error);
		} else if (result) {
			document.getElementById('display_linked_to').innerHTML = result;
		}
	});
}
