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
var updateHelpDocumentConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateHelpDocumentConditions()
{
	if (updateHelpDocumentConditionsBaseline === null)
	{
		updateHelpDocumentConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_admin_view","#jform_site_view","#jform_url","#jform_article","#jform_content-lbl","#jform_groups"];
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
					updateHelpDocumentConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = ["admin_view","site_view","url","article","groups"];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateHelpDocumentConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateHelpDocumentConditionsBaseline.containers.length; index++)
	{
		var baseline = updateHelpDocumentConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateHelpDocumentConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var location_vvvvwcq = jQuery("#jform_location input[type='radio']:checked").val();
	vvvvwcq(location_vvvvwcq);

	var location_vvvvwcr = jQuery("#jform_location input[type='radio']:checked").val();
	vvvvwcr(location_vvvvwcr);

	var type_vvvvwcs = jQuery("#jform_type").val();
	vvvvwcs(type_vvvvwcs);

	var type_vvvvwct = jQuery("#jform_type").val();
	vvvvwct(type_vvvvwct);

	var type_vvvvwcu = jQuery("#jform_type").val();
	vvvvwcu(type_vvvvwcu);

	var target_vvvvwcv = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwcv(target_vvvvwcv);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateHelpDocumentConditions);

// the vvvvwcq function
function vvvvwcq(location_vvvvwcq)
{
	// set the function logic
	if (location_vvvvwcq == Number('1'))
	{
		jQuery('#jform_admin_view').closest('.control-group').show();
		// Add required attributes to admin_view.
		jQuery('#jform_admin_view').prop('required', true);
		jQuery('#jform_admin_view').attr('aria-required', true);
		jQuery('#jform_admin_view').addClass('required');
	}
	else
	{
		jQuery('#jform_admin_view').closest('.control-group').hide();
		// Remove required attributes from admin_view.
		jQuery('#jform_admin_view').removeAttr('required');
		jQuery('#jform_admin_view').removeAttr('aria-required');
		jQuery('#jform_admin_view').removeClass('required');
	}
}

// the vvvvwcr function
function vvvvwcr(location_vvvvwcr)
{
	// set the function logic
	if (location_vvvvwcr == Number('2'))
	{
		jQuery('#jform_site_view').closest('.control-group').show();
		// Add required attributes to site_view.
		jQuery('#jform_site_view').prop('required', true);
		jQuery('#jform_site_view').attr('aria-required', true);
		jQuery('#jform_site_view').addClass('required');
	}
	else
	{
		jQuery('#jform_site_view').closest('.control-group').hide();
		// Remove required attributes from site_view.
		jQuery('#jform_site_view').removeAttr('required');
		jQuery('#jform_site_view').removeAttr('aria-required');
		jQuery('#jform_site_view').removeClass('required');
	}
}

// the vvvvwcs function
function vvvvwcs(type_vvvvwcs)
{
	if (isSet(type_vvvvwcs) && type_vvvvwcs.constructor !== Array)
	{
		var temp_vvvvwcs = type_vvvvwcs;
		var type_vvvvwcs = [];
		type_vvvvwcs.push(temp_vvvvwcs);
	}
	else if (!isSet(type_vvvvwcs))
	{
		var type_vvvvwcs = [];
	}
	var type = type_vvvvwcs.some(type_vvvvwcs_SomeFunc);


	// set this function logic
	if (type)
	{
		jQuery('#jform_url').closest('.control-group').show();
		// Add required attributes to url.
		jQuery('#jform_url').prop('required', true);
		jQuery('#jform_url').attr('aria-required', true);
		jQuery('#jform_url').addClass('required');
	}
	else
	{
		jQuery('#jform_url').closest('.control-group').hide();
		// Remove required attributes from url.
		jQuery('#jform_url').removeAttr('required');
		jQuery('#jform_url').removeAttr('aria-required');
		jQuery('#jform_url').removeClass('required');
	}
}

// the vvvvwcs Some function
function type_vvvvwcs_SomeFunc(type_vvvvwcs)
{
	// set the function logic
	if (type_vvvvwcs == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvwct function
function vvvvwct(type_vvvvwct)
{
	if (isSet(type_vvvvwct) && type_vvvvwct.constructor !== Array)
	{
		var temp_vvvvwct = type_vvvvwct;
		var type_vvvvwct = [];
		type_vvvvwct.push(temp_vvvvwct);
	}
	else if (!isSet(type_vvvvwct))
	{
		var type_vvvvwct = [];
	}
	var type = type_vvvvwct.some(type_vvvvwct_SomeFunc);


	// set this function logic
	if (type)
	{
		jQuery('#jform_article').closest('.control-group').show();
		// Add required attributes to article.
		jQuery('#jform_article').prop('required', true);
		jQuery('#jform_article').attr('aria-required', true);
		jQuery('#jform_article').addClass('required');
	}
	else
	{
		jQuery('#jform_article').closest('.control-group').hide();
		// Remove required attributes from article.
		jQuery('#jform_article').removeAttr('required');
		jQuery('#jform_article').removeAttr('aria-required');
		jQuery('#jform_article').removeClass('required');
	}
}

// the vvvvwct Some function
function type_vvvvwct_SomeFunc(type_vvvvwct)
{
	// set the function logic
	if (type_vvvvwct == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwcu function
function vvvvwcu(type_vvvvwcu)
{
	if (isSet(type_vvvvwcu) && type_vvvvwcu.constructor !== Array)
	{
		var temp_vvvvwcu = type_vvvvwcu;
		var type_vvvvwcu = [];
		type_vvvvwcu.push(temp_vvvvwcu);
	}
	else if (!isSet(type_vvvvwcu))
	{
		var type_vvvvwcu = [];
	}
	var type = type_vvvvwcu.some(type_vvvvwcu_SomeFunc);


	// set this function logic
	if (type)
	{
		jQuery('#jform_content-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_content-lbl').closest('.control-group').hide();
	}
}

// the vvvvwcu Some function
function type_vvvvwcu_SomeFunc(type_vvvvwcu)
{
	// set the function logic
	if (type_vvvvwcu == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwcv function
function vvvvwcv(target_vvvvwcv)
{
	// set the function logic
	if (target_vvvvwcv == Number('1'))
	{
		jQuery('#jform_groups').closest('.control-group').show();
		// Add required attributes to groups.
		jQuery('#jform_groups').prop('required', true);
		jQuery('#jform_groups').attr('aria-required', true);
		jQuery('#jform_groups').addClass('required');
	}
	else
	{
		jQuery('#jform_groups').closest('.control-group').hide();
		// Remove required attributes from groups.
		jQuery('#jform_groups').removeAttr('required');
		jQuery('#jform_groups').removeAttr('aria-required');
		jQuery('#jform_groups').removeClass('required');
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
