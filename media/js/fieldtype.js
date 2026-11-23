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
var updateFieldtypeConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateFieldtypeConditions()
{
	if (updateFieldtypeConditionsBaseline === null)
	{
		updateFieldtypeConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_datalenght_other","#jform_datadefault_other","#jform_datalenght","#jform_datadefault","#jform_indexes","#jform_store",".note_whmcs_encryption","#jform_datatype","#jform_null_switch"];
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
					updateFieldtypeConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = ["datalenght_other","datadefault_other","datalenght","indexes","store","datatype","null_switch"];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateFieldtypeConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateFieldtypeConditionsBaseline.containers.length; index++)
	{
		var baseline = updateFieldtypeConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateFieldtypeConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var datalenght_vvvvwbw = jQuery("#jform_datalenght").val();
	var has_defaults_vvvvwbw = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	vvvvwbw(datalenght_vvvvwbw,has_defaults_vvvvwbw);

	var datadefault_vvvvwby = jQuery("#jform_datadefault").val();
	var has_defaults_vvvvwby = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	vvvvwby(datadefault_vvvvwby,has_defaults_vvvvwby);

	var datatype_vvvvwca = jQuery("#jform_datatype").val();
	var has_defaults_vvvvwca = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	vvvvwca(datatype_vvvvwca,has_defaults_vvvvwca);

	var datatype_vvvvwcc = jQuery("#jform_datatype").val();
	var has_defaults_vvvvwcc = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	vvvvwcc(datatype_vvvvwcc,has_defaults_vvvvwcc);

	var has_defaults_vvvvwcd = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	var datatype_vvvvwcd = jQuery("#jform_datatype").val();
	vvvvwcd(has_defaults_vvvvwcd,datatype_vvvvwcd);

	var datatype_vvvvwce = jQuery("#jform_datatype").val();
	var has_defaults_vvvvwce = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	vvvvwce(datatype_vvvvwce,has_defaults_vvvvwce);

	var store_vvvvwcg = jQuery("#jform_store").val();
	var datatype_vvvvwcg = jQuery("#jform_datatype").val();
	var has_defaults_vvvvwcg = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	vvvvwcg(store_vvvvwcg,datatype_vvvvwcg,has_defaults_vvvvwcg);

	var datatype_vvvvwch = jQuery("#jform_datatype").val();
	var store_vvvvwch = jQuery("#jform_store").val();
	var has_defaults_vvvvwch = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	vvvvwch(datatype_vvvvwch,store_vvvvwch,has_defaults_vvvvwch);

	var has_defaults_vvvvwci = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	var store_vvvvwci = jQuery("#jform_store").val();
	var datatype_vvvvwci = jQuery("#jform_datatype").val();
	vvvvwci(has_defaults_vvvvwci,store_vvvvwci,datatype_vvvvwci);

	var has_defaults_vvvvwcj = jQuery("#jform_has_defaults input[type='radio']:checked").val();
	vvvvwcj(has_defaults_vvvvwcj);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateFieldtypeConditions);

// the vvvvwbw function
function vvvvwbw(datalenght_vvvvwbw,has_defaults_vvvvwbw)
{
	if (isSet(datalenght_vvvvwbw) && datalenght_vvvvwbw.constructor !== Array)
	{
		var temp_vvvvwbw = datalenght_vvvvwbw;
		var datalenght_vvvvwbw = [];
		datalenght_vvvvwbw.push(temp_vvvvwbw);
	}
	else if (!isSet(datalenght_vvvvwbw))
	{
		var datalenght_vvvvwbw = [];
	}
	var datalenght = datalenght_vvvvwbw.some(datalenght_vvvvwbw_SomeFunc);

	if (isSet(has_defaults_vvvvwbw) && has_defaults_vvvvwbw.constructor !== Array)
	{
		var temp_vvvvwbw = has_defaults_vvvvwbw;
		var has_defaults_vvvvwbw = [];
		has_defaults_vvvvwbw.push(temp_vvvvwbw);
	}
	else if (!isSet(has_defaults_vvvvwbw))
	{
		var has_defaults_vvvvwbw = [];
	}
	var has_defaults = has_defaults_vvvvwbw.some(has_defaults_vvvvwbw_SomeFunc);


	// set this function logic
	if (datalenght && has_defaults)
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

// the vvvvwbw Some function
function datalenght_vvvvwbw_SomeFunc(datalenght_vvvvwbw)
{
	// set the function logic
	if (datalenght_vvvvwbw == 'Other')
	{
		return true;
	}
	return false;
}

// the vvvvwbw Some function
function has_defaults_vvvvwbw_SomeFunc(has_defaults_vvvvwbw)
{
	// set the function logic
	if (has_defaults_vvvvwbw == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwby function
function vvvvwby(datadefault_vvvvwby,has_defaults_vvvvwby)
{
	if (isSet(datadefault_vvvvwby) && datadefault_vvvvwby.constructor !== Array)
	{
		var temp_vvvvwby = datadefault_vvvvwby;
		var datadefault_vvvvwby = [];
		datadefault_vvvvwby.push(temp_vvvvwby);
	}
	else if (!isSet(datadefault_vvvvwby))
	{
		var datadefault_vvvvwby = [];
	}
	var datadefault = datadefault_vvvvwby.some(datadefault_vvvvwby_SomeFunc);

	if (isSet(has_defaults_vvvvwby) && has_defaults_vvvvwby.constructor !== Array)
	{
		var temp_vvvvwby = has_defaults_vvvvwby;
		var has_defaults_vvvvwby = [];
		has_defaults_vvvvwby.push(temp_vvvvwby);
	}
	else if (!isSet(has_defaults_vvvvwby))
	{
		var has_defaults_vvvvwby = [];
	}
	var has_defaults = has_defaults_vvvvwby.some(has_defaults_vvvvwby_SomeFunc);


	// set this function logic
	if (datadefault && has_defaults)
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

// the vvvvwby Some function
function datadefault_vvvvwby_SomeFunc(datadefault_vvvvwby)
{
	// set the function logic
	if (datadefault_vvvvwby == 'Other')
	{
		return true;
	}
	return false;
}

// the vvvvwby Some function
function has_defaults_vvvvwby_SomeFunc(has_defaults_vvvvwby)
{
	// set the function logic
	if (has_defaults_vvvvwby == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwca function
function vvvvwca(datatype_vvvvwca,has_defaults_vvvvwca)
{
	if (isSet(datatype_vvvvwca) && datatype_vvvvwca.constructor !== Array)
	{
		var temp_vvvvwca = datatype_vvvvwca;
		var datatype_vvvvwca = [];
		datatype_vvvvwca.push(temp_vvvvwca);
	}
	else if (!isSet(datatype_vvvvwca))
	{
		var datatype_vvvvwca = [];
	}
	var datatype = datatype_vvvvwca.some(datatype_vvvvwca_SomeFunc);

	if (isSet(has_defaults_vvvvwca) && has_defaults_vvvvwca.constructor !== Array)
	{
		var temp_vvvvwca = has_defaults_vvvvwca;
		var has_defaults_vvvvwca = [];
		has_defaults_vvvvwca.push(temp_vvvvwca);
	}
	else if (!isSet(has_defaults_vvvvwca))
	{
		var has_defaults_vvvvwca = [];
	}
	var has_defaults = has_defaults_vvvvwca.some(has_defaults_vvvvwca_SomeFunc);


	// set this function logic
	if (datatype && has_defaults)
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

// the vvvvwca Some function
function datatype_vvvvwca_SomeFunc(datatype_vvvvwca)
{
	// set the function logic
	if (datatype_vvvvwca == 'CHAR' || datatype_vvvvwca == 'VARCHAR' || datatype_vvvvwca == 'INT' || datatype_vvvvwca == 'TINYINT' || datatype_vvvvwca == 'BIGINT' || datatype_vvvvwca == 'FLOAT' || datatype_vvvvwca == 'DECIMAL' || datatype_vvvvwca == 'DOUBLE')
	{
		return true;
	}
	return false;
}

// the vvvvwca Some function
function has_defaults_vvvvwca_SomeFunc(has_defaults_vvvvwca)
{
	// set the function logic
	if (has_defaults_vvvvwca == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwcc function
function vvvvwcc(datatype_vvvvwcc,has_defaults_vvvvwcc)
{
	if (isSet(datatype_vvvvwcc) && datatype_vvvvwcc.constructor !== Array)
	{
		var temp_vvvvwcc = datatype_vvvvwcc;
		var datatype_vvvvwcc = [];
		datatype_vvvvwcc.push(temp_vvvvwcc);
	}
	else if (!isSet(datatype_vvvvwcc))
	{
		var datatype_vvvvwcc = [];
	}
	var datatype = datatype_vvvvwcc.some(datatype_vvvvwcc_SomeFunc);

	if (isSet(has_defaults_vvvvwcc) && has_defaults_vvvvwcc.constructor !== Array)
	{
		var temp_vvvvwcc = has_defaults_vvvvwcc;
		var has_defaults_vvvvwcc = [];
		has_defaults_vvvvwcc.push(temp_vvvvwcc);
	}
	else if (!isSet(has_defaults_vvvvwcc))
	{
		var has_defaults_vvvvwcc = [];
	}
	var has_defaults = has_defaults_vvvvwcc.some(has_defaults_vvvvwcc_SomeFunc);


	// set this function logic
	if (datatype && has_defaults)
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

// the vvvvwcc Some function
function datatype_vvvvwcc_SomeFunc(datatype_vvvvwcc)
{
	// set the function logic
	if (datatype_vvvvwcc == 'CHAR' || datatype_vvvvwcc == 'VARCHAR' || datatype_vvvvwcc == 'DATETIME' || datatype_vvvvwcc == 'DATE' || datatype_vvvvwcc == 'TIME' || datatype_vvvvwcc == 'INT' || datatype_vvvvwcc == 'TINYINT' || datatype_vvvvwcc == 'BIGINT' || datatype_vvvvwcc == 'FLOAT' || datatype_vvvvwcc == 'DECIMAL' || datatype_vvvvwcc == 'DOUBLE')
	{
		return true;
	}
	return false;
}

// the vvvvwcc Some function
function has_defaults_vvvvwcc_SomeFunc(has_defaults_vvvvwcc)
{
	// set the function logic
	if (has_defaults_vvvvwcc == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwcd function
function vvvvwcd(has_defaults_vvvvwcd,datatype_vvvvwcd)
{
	if (isSet(has_defaults_vvvvwcd) && has_defaults_vvvvwcd.constructor !== Array)
	{
		var temp_vvvvwcd = has_defaults_vvvvwcd;
		var has_defaults_vvvvwcd = [];
		has_defaults_vvvvwcd.push(temp_vvvvwcd);
	}
	else if (!isSet(has_defaults_vvvvwcd))
	{
		var has_defaults_vvvvwcd = [];
	}
	var has_defaults = has_defaults_vvvvwcd.some(has_defaults_vvvvwcd_SomeFunc);

	if (isSet(datatype_vvvvwcd) && datatype_vvvvwcd.constructor !== Array)
	{
		var temp_vvvvwcd = datatype_vvvvwcd;
		var datatype_vvvvwcd = [];
		datatype_vvvvwcd.push(temp_vvvvwcd);
	}
	else if (!isSet(datatype_vvvvwcd))
	{
		var datatype_vvvvwcd = [];
	}
	var datatype = datatype_vvvvwcd.some(datatype_vvvvwcd_SomeFunc);


	// set this function logic
	if (has_defaults && datatype)
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

// the vvvvwcd Some function
function has_defaults_vvvvwcd_SomeFunc(has_defaults_vvvvwcd)
{
	// set the function logic
	if (has_defaults_vvvvwcd == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwcd Some function
function datatype_vvvvwcd_SomeFunc(datatype_vvvvwcd)
{
	// set the function logic
	if (datatype_vvvvwcd == 'CHAR' || datatype_vvvvwcd == 'VARCHAR' || datatype_vvvvwcd == 'DATETIME' || datatype_vvvvwcd == 'DATE' || datatype_vvvvwcd == 'TIME' || datatype_vvvvwcd == 'INT' || datatype_vvvvwcd == 'TINYINT' || datatype_vvvvwcd == 'BIGINT' || datatype_vvvvwcd == 'FLOAT' || datatype_vvvvwcd == 'DECIMAL' || datatype_vvvvwcd == 'DOUBLE')
	{
		return true;
	}
	return false;
}

// the vvvvwce function
function vvvvwce(datatype_vvvvwce,has_defaults_vvvvwce)
{
	if (isSet(datatype_vvvvwce) && datatype_vvvvwce.constructor !== Array)
	{
		var temp_vvvvwce = datatype_vvvvwce;
		var datatype_vvvvwce = [];
		datatype_vvvvwce.push(temp_vvvvwce);
	}
	else if (!isSet(datatype_vvvvwce))
	{
		var datatype_vvvvwce = [];
	}
	var datatype = datatype_vvvvwce.some(datatype_vvvvwce_SomeFunc);

	if (isSet(has_defaults_vvvvwce) && has_defaults_vvvvwce.constructor !== Array)
	{
		var temp_vvvvwce = has_defaults_vvvvwce;
		var has_defaults_vvvvwce = [];
		has_defaults_vvvvwce.push(temp_vvvvwce);
	}
	else if (!isSet(has_defaults_vvvvwce))
	{
		var has_defaults_vvvvwce = [];
	}
	var has_defaults = has_defaults_vvvvwce.some(has_defaults_vvvvwce_SomeFunc);


	// set this function logic
	if (datatype && has_defaults)
	{
		jQuery('#jform_store').closest('.control-group').show();
		// Add required attributes to store.
		jQuery('#jform_store').prop('required', true);
		jQuery('#jform_store').attr('aria-required', true);
		jQuery('#jform_store').addClass('required');
	}
	else
	{
		jQuery('#jform_store').closest('.control-group').hide();
		// Remove required attributes from store.
		jQuery('#jform_store').removeAttr('required');
		jQuery('#jform_store').removeAttr('aria-required');
		jQuery('#jform_store').removeClass('required');
	}
}

// the vvvvwce Some function
function datatype_vvvvwce_SomeFunc(datatype_vvvvwce)
{
	// set the function logic
	if (datatype_vvvvwce == 'CHAR' || datatype_vvvvwce == 'VARCHAR' || datatype_vvvvwce == 'TEXT' || datatype_vvvvwce == 'MEDIUMTEXT' || datatype_vvvvwce == 'LONGTEXT' || datatype_vvvvwce == 'BLOB' || datatype_vvvvwce == 'TINYBLOB' || datatype_vvvvwce == 'MEDIUMBLOB' || datatype_vvvvwce == 'LONGBLOB')
	{
		return true;
	}
	return false;
}

// the vvvvwce Some function
function has_defaults_vvvvwce_SomeFunc(has_defaults_vvvvwce)
{
	// set the function logic
	if (has_defaults_vvvvwce == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwcg function
function vvvvwcg(store_vvvvwcg,datatype_vvvvwcg,has_defaults_vvvvwcg)
{
	if (isSet(store_vvvvwcg) && store_vvvvwcg.constructor !== Array)
	{
		var temp_vvvvwcg = store_vvvvwcg;
		var store_vvvvwcg = [];
		store_vvvvwcg.push(temp_vvvvwcg);
	}
	else if (!isSet(store_vvvvwcg))
	{
		var store_vvvvwcg = [];
	}
	var store = store_vvvvwcg.some(store_vvvvwcg_SomeFunc);

	if (isSet(datatype_vvvvwcg) && datatype_vvvvwcg.constructor !== Array)
	{
		var temp_vvvvwcg = datatype_vvvvwcg;
		var datatype_vvvvwcg = [];
		datatype_vvvvwcg.push(temp_vvvvwcg);
	}
	else if (!isSet(datatype_vvvvwcg))
	{
		var datatype_vvvvwcg = [];
	}
	var datatype = datatype_vvvvwcg.some(datatype_vvvvwcg_SomeFunc);

	if (isSet(has_defaults_vvvvwcg) && has_defaults_vvvvwcg.constructor !== Array)
	{
		var temp_vvvvwcg = has_defaults_vvvvwcg;
		var has_defaults_vvvvwcg = [];
		has_defaults_vvvvwcg.push(temp_vvvvwcg);
	}
	else if (!isSet(has_defaults_vvvvwcg))
	{
		var has_defaults_vvvvwcg = [];
	}
	var has_defaults = has_defaults_vvvvwcg.some(has_defaults_vvvvwcg_SomeFunc);


	// set this function logic
	if (store && datatype && has_defaults)
	{
		jQuery('.note_whmcs_encryption').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_whmcs_encryption').closest('.control-group').hide();
	}
}

// the vvvvwcg Some function
function store_vvvvwcg_SomeFunc(store_vvvvwcg)
{
	// set the function logic
	if (store_vvvvwcg == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvwcg Some function
function datatype_vvvvwcg_SomeFunc(datatype_vvvvwcg)
{
	// set the function logic
	if (datatype_vvvvwcg == 'CHAR' || datatype_vvvvwcg == 'VARCHAR' || datatype_vvvvwcg == 'TEXT' || datatype_vvvvwcg == 'MEDIUMTEXT' || datatype_vvvvwcg == 'LONGTEXT' || datatype_vvvvwcg == 'BLOB' || datatype_vvvvwcg == 'TINYBLOB' || datatype_vvvvwcg == 'MEDIUMBLOB' || datatype_vvvvwcg == 'LONGBLOB')
	{
		return true;
	}
	return false;
}

// the vvvvwcg Some function
function has_defaults_vvvvwcg_SomeFunc(has_defaults_vvvvwcg)
{
	// set the function logic
	if (has_defaults_vvvvwcg == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwch function
function vvvvwch(datatype_vvvvwch,store_vvvvwch,has_defaults_vvvvwch)
{
	if (isSet(datatype_vvvvwch) && datatype_vvvvwch.constructor !== Array)
	{
		var temp_vvvvwch = datatype_vvvvwch;
		var datatype_vvvvwch = [];
		datatype_vvvvwch.push(temp_vvvvwch);
	}
	else if (!isSet(datatype_vvvvwch))
	{
		var datatype_vvvvwch = [];
	}
	var datatype = datatype_vvvvwch.some(datatype_vvvvwch_SomeFunc);

	if (isSet(store_vvvvwch) && store_vvvvwch.constructor !== Array)
	{
		var temp_vvvvwch = store_vvvvwch;
		var store_vvvvwch = [];
		store_vvvvwch.push(temp_vvvvwch);
	}
	else if (!isSet(store_vvvvwch))
	{
		var store_vvvvwch = [];
	}
	var store = store_vvvvwch.some(store_vvvvwch_SomeFunc);

	if (isSet(has_defaults_vvvvwch) && has_defaults_vvvvwch.constructor !== Array)
	{
		var temp_vvvvwch = has_defaults_vvvvwch;
		var has_defaults_vvvvwch = [];
		has_defaults_vvvvwch.push(temp_vvvvwch);
	}
	else if (!isSet(has_defaults_vvvvwch))
	{
		var has_defaults_vvvvwch = [];
	}
	var has_defaults = has_defaults_vvvvwch.some(has_defaults_vvvvwch_SomeFunc);


	// set this function logic
	if (datatype && store && has_defaults)
	{
		jQuery('.note_whmcs_encryption').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_whmcs_encryption').closest('.control-group').hide();
	}
}

// the vvvvwch Some function
function datatype_vvvvwch_SomeFunc(datatype_vvvvwch)
{
	// set the function logic
	if (datatype_vvvvwch == 'CHAR' || datatype_vvvvwch == 'VARCHAR' || datatype_vvvvwch == 'TEXT' || datatype_vvvvwch == 'MEDIUMTEXT' || datatype_vvvvwch == 'LONGTEXT' || datatype_vvvvwch == 'BLOB' || datatype_vvvvwch == 'TINYBLOB' || datatype_vvvvwch == 'MEDIUMBLOB' || datatype_vvvvwch == 'LONGBLOB')
	{
		return true;
	}
	return false;
}

// the vvvvwch Some function
function store_vvvvwch_SomeFunc(store_vvvvwch)
{
	// set the function logic
	if (store_vvvvwch == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvwch Some function
function has_defaults_vvvvwch_SomeFunc(has_defaults_vvvvwch)
{
	// set the function logic
	if (has_defaults_vvvvwch == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwci function
function vvvvwci(has_defaults_vvvvwci,store_vvvvwci,datatype_vvvvwci)
{
	if (isSet(has_defaults_vvvvwci) && has_defaults_vvvvwci.constructor !== Array)
	{
		var temp_vvvvwci = has_defaults_vvvvwci;
		var has_defaults_vvvvwci = [];
		has_defaults_vvvvwci.push(temp_vvvvwci);
	}
	else if (!isSet(has_defaults_vvvvwci))
	{
		var has_defaults_vvvvwci = [];
	}
	var has_defaults = has_defaults_vvvvwci.some(has_defaults_vvvvwci_SomeFunc);

	if (isSet(store_vvvvwci) && store_vvvvwci.constructor !== Array)
	{
		var temp_vvvvwci = store_vvvvwci;
		var store_vvvvwci = [];
		store_vvvvwci.push(temp_vvvvwci);
	}
	else if (!isSet(store_vvvvwci))
	{
		var store_vvvvwci = [];
	}
	var store = store_vvvvwci.some(store_vvvvwci_SomeFunc);

	if (isSet(datatype_vvvvwci) && datatype_vvvvwci.constructor !== Array)
	{
		var temp_vvvvwci = datatype_vvvvwci;
		var datatype_vvvvwci = [];
		datatype_vvvvwci.push(temp_vvvvwci);
	}
	else if (!isSet(datatype_vvvvwci))
	{
		var datatype_vvvvwci = [];
	}
	var datatype = datatype_vvvvwci.some(datatype_vvvvwci_SomeFunc);


	// set this function logic
	if (has_defaults && store && datatype)
	{
		jQuery('.note_whmcs_encryption').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_whmcs_encryption').closest('.control-group').hide();
	}
}

// the vvvvwci Some function
function has_defaults_vvvvwci_SomeFunc(has_defaults_vvvvwci)
{
	// set the function logic
	if (has_defaults_vvvvwci == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwci Some function
function store_vvvvwci_SomeFunc(store_vvvvwci)
{
	// set the function logic
	if (store_vvvvwci == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvwci Some function
function datatype_vvvvwci_SomeFunc(datatype_vvvvwci)
{
	// set the function logic
	if (datatype_vvvvwci == 'CHAR' || datatype_vvvvwci == 'VARCHAR' || datatype_vvvvwci == 'TEXT' || datatype_vvvvwci == 'MEDIUMTEXT' || datatype_vvvvwci == 'LONGTEXT' || datatype_vvvvwci == 'BLOB' || datatype_vvvvwci == 'TINYBLOB' || datatype_vvvvwci == 'MEDIUMBLOB' || datatype_vvvvwci == 'LONGBLOB')
	{
		return true;
	}
	return false;
}

// the vvvvwcj function
function vvvvwcj(has_defaults_vvvvwcj)
{
	// set the function logic
	if (has_defaults_vvvvwcj == Number('1'))
	{
		jQuery('#jform_datatype').closest('.control-group').show();
		// Add required attributes to datatype.
		jQuery('#jform_datatype').prop('required', true);
		jQuery('#jform_datatype').attr('aria-required', true);
		jQuery('#jform_datatype').addClass('required');
		jQuery('#jform_null_switch').closest('.control-group').show();
		// Add required attributes to null_switch.
		jQuery('#jform_null_switch').prop('required', true);
		jQuery('#jform_null_switch').attr('aria-required', true);
		jQuery('#jform_null_switch').addClass('required');
	}
	else
	{
		jQuery('#jform_datatype').closest('.control-group').hide();
		// Remove required attributes from datatype.
		jQuery('#jform_datatype').removeAttr('required');
		jQuery('#jform_datatype').removeAttr('aria-required');
		jQuery('#jform_datatype').removeClass('required');
		jQuery('#jform_null_switch').closest('.control-group').hide();
		// Remove required attributes from null_switch.
		jQuery('#jform_null_switch').removeAttr('required');
		jQuery('#jform_null_switch').removeAttr('aria-required');
		jQuery('#jform_null_switch').removeClass('required');
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


jQuery(document).ready(function($)
{
	// check and load all the custom code edit buttons
	getEditCustomCodeButtons();
});

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
