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
var updateServerConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateServerConditions()
{
	if (updateServerConditionsBaseline === null)
	{
		updateServerConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_authentication","#jform_host","#jform_port","#jform_path",".note_ssh_security","#jform_username",".note_ftp_signature","#jform_signature","#jform_password","#jform_private","#jform_private_key","#jform_secret"];
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
					updateServerConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = ["authentication","host","port","path","username","signature","password","private","private_key"];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateServerConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateServerConditionsBaseline.containers.length; index++)
	{
		var baseline = updateServerConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateServerConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var protocol_vvvvwck = jQuery("#jform_protocol").val();
	vvvvwck(protocol_vvvvwck);

	var protocol_vvvvwcl = jQuery("#jform_protocol").val();
	vvvvwcl(protocol_vvvvwcl);

	var protocol_vvvvwcm = jQuery("#jform_protocol").val();
	var authentication_vvvvwcm = jQuery("#jform_authentication").val();
	vvvvwcm(protocol_vvvvwcm,authentication_vvvvwcm);

	var protocol_vvvvwco = jQuery("#jform_protocol").val();
	var authentication_vvvvwco = jQuery("#jform_authentication").val();
	vvvvwco(protocol_vvvvwco,authentication_vvvvwco);

	var protocol_vvvvwcq = jQuery("#jform_protocol").val();
	var authentication_vvvvwcq = jQuery("#jform_authentication").val();
	vvvvwcq(protocol_vvvvwcq,authentication_vvvvwcq);

	var protocol_vvvvwcs = jQuery("#jform_protocol").val();
	var authentication_vvvvwcs = jQuery("#jform_authentication").val();
	vvvvwcs(protocol_vvvvwcs,authentication_vvvvwcs);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateServerConditions);

// the vvvvwck function
function vvvvwck(protocol_vvvvwck)
{
	if (isSet(protocol_vvvvwck) && protocol_vvvvwck.constructor !== Array)
	{
		var temp_vvvvwck = protocol_vvvvwck;
		var protocol_vvvvwck = [];
		protocol_vvvvwck.push(temp_vvvvwck);
	}
	else if (!isSet(protocol_vvvvwck))
	{
		var protocol_vvvvwck = [];
	}
	var protocol = protocol_vvvvwck.some(protocol_vvvvwck_SomeFunc);


	// set this function logic
	if (protocol)
	{
		jQuery('#jform_authentication').closest('.control-group').show();
		// Add required attributes to authentication.
		jQuery('#jform_authentication').prop('required', true);
		jQuery('#jform_authentication').attr('aria-required', true);
		jQuery('#jform_authentication').addClass('required');
		jQuery('#jform_host').closest('.control-group').show();
		// Add required attributes to host.
		jQuery('#jform_host').prop('required', true);
		jQuery('#jform_host').attr('aria-required', true);
		jQuery('#jform_host').addClass('required');
		jQuery('#jform_port').closest('.control-group').show();
		// Add required attributes to port.
		jQuery('#jform_port').prop('required', true);
		jQuery('#jform_port').attr('aria-required', true);
		jQuery('#jform_port').addClass('required');
		jQuery('#jform_path').closest('.control-group').show();
		// Add required attributes to path.
		jQuery('#jform_path').prop('required', true);
		jQuery('#jform_path').attr('aria-required', true);
		jQuery('#jform_path').addClass('required');
		jQuery('.note_ssh_security').closest('.control-group').show();
		jQuery('#jform_username').closest('.control-group').show();
		// Add required attributes to username.
		jQuery('#jform_username').prop('required', true);
		jQuery('#jform_username').attr('aria-required', true);
		jQuery('#jform_username').addClass('required');
	}
	else
	{
		jQuery('#jform_authentication').closest('.control-group').hide();
		// Remove required attributes from authentication.
		jQuery('#jform_authentication').removeAttr('required');
		jQuery('#jform_authentication').removeAttr('aria-required');
		jQuery('#jform_authentication').removeClass('required');
		jQuery('#jform_host').closest('.control-group').hide();
		// Remove required attributes from host.
		jQuery('#jform_host').removeAttr('required');
		jQuery('#jform_host').removeAttr('aria-required');
		jQuery('#jform_host').removeClass('required');
		jQuery('#jform_port').closest('.control-group').hide();
		// Remove required attributes from port.
		jQuery('#jform_port').removeAttr('required');
		jQuery('#jform_port').removeAttr('aria-required');
		jQuery('#jform_port').removeClass('required');
		jQuery('#jform_path').closest('.control-group').hide();
		// Remove required attributes from path.
		jQuery('#jform_path').removeAttr('required');
		jQuery('#jform_path').removeAttr('aria-required');
		jQuery('#jform_path').removeClass('required');
		jQuery('.note_ssh_security').closest('.control-group').hide();
		jQuery('#jform_username').closest('.control-group').hide();
		// Remove required attributes from username.
		jQuery('#jform_username').removeAttr('required');
		jQuery('#jform_username').removeAttr('aria-required');
		jQuery('#jform_username').removeClass('required');
	}
}

// the vvvvwck Some function
function protocol_vvvvwck_SomeFunc(protocol_vvvvwck)
{
	// set the function logic
	if (protocol_vvvvwck == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwcl function
function vvvvwcl(protocol_vvvvwcl)
{
	if (isSet(protocol_vvvvwcl) && protocol_vvvvwcl.constructor !== Array)
	{
		var temp_vvvvwcl = protocol_vvvvwcl;
		var protocol_vvvvwcl = [];
		protocol_vvvvwcl.push(temp_vvvvwcl);
	}
	else if (!isSet(protocol_vvvvwcl))
	{
		var protocol_vvvvwcl = [];
	}
	var protocol = protocol_vvvvwcl.some(protocol_vvvvwcl_SomeFunc);


	// set this function logic
	if (protocol)
	{
		jQuery('.note_ftp_signature').closest('.control-group').show();
		jQuery('#jform_signature').closest('.control-group').show();
		// Add required attributes to signature.
		jQuery('#jform_signature').prop('required', true);
		jQuery('#jform_signature').attr('aria-required', true);
		jQuery('#jform_signature').addClass('required');
	}
	else
	{
		jQuery('.note_ftp_signature').closest('.control-group').hide();
		jQuery('#jform_signature').closest('.control-group').hide();
		// Remove required attributes from signature.
		jQuery('#jform_signature').removeAttr('required');
		jQuery('#jform_signature').removeAttr('aria-required');
		jQuery('#jform_signature').removeClass('required');
	}
}

// the vvvvwcl Some function
function protocol_vvvvwcl_SomeFunc(protocol_vvvvwcl)
{
	// set the function logic
	if (protocol_vvvvwcl == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwcm function
function vvvvwcm(protocol_vvvvwcm,authentication_vvvvwcm)
{
	if (isSet(protocol_vvvvwcm) && protocol_vvvvwcm.constructor !== Array)
	{
		var temp_vvvvwcm = protocol_vvvvwcm;
		var protocol_vvvvwcm = [];
		protocol_vvvvwcm.push(temp_vvvvwcm);
	}
	else if (!isSet(protocol_vvvvwcm))
	{
		var protocol_vvvvwcm = [];
	}
	var protocol = protocol_vvvvwcm.some(protocol_vvvvwcm_SomeFunc);

	if (isSet(authentication_vvvvwcm) && authentication_vvvvwcm.constructor !== Array)
	{
		var temp_vvvvwcm = authentication_vvvvwcm;
		var authentication_vvvvwcm = [];
		authentication_vvvvwcm.push(temp_vvvvwcm);
	}
	else if (!isSet(authentication_vvvvwcm))
	{
		var authentication_vvvvwcm = [];
	}
	var authentication = authentication_vvvvwcm.some(authentication_vvvvwcm_SomeFunc);


	// set this function logic
	if (protocol && authentication)
	{
		jQuery('#jform_password').closest('.control-group').show();
		// Add required attributes to password.
		jQuery('#jform_password').prop('required', true);
		jQuery('#jform_password').attr('aria-required', true);
		jQuery('#jform_password').addClass('required');
	}
	else
	{
		jQuery('#jform_password').closest('.control-group').hide();
		// Remove required attributes from password.
		jQuery('#jform_password').removeAttr('required');
		jQuery('#jform_password').removeAttr('aria-required');
		jQuery('#jform_password').removeClass('required');
	}
}

// the vvvvwcm Some function
function protocol_vvvvwcm_SomeFunc(protocol_vvvvwcm)
{
	// set the function logic
	if (protocol_vvvvwcm == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwcm Some function
function authentication_vvvvwcm_SomeFunc(authentication_vvvvwcm)
{
	// set the function logic
	if (authentication_vvvvwcm == Number('1') || authentication_vvvvwcm == Number('3') || authentication_vvvvwcm == Number('5'))
	{
		return true;
	}
	return false;
}

// the vvvvwco function
function vvvvwco(protocol_vvvvwco,authentication_vvvvwco)
{
	if (isSet(protocol_vvvvwco) && protocol_vvvvwco.constructor !== Array)
	{
		var temp_vvvvwco = protocol_vvvvwco;
		var protocol_vvvvwco = [];
		protocol_vvvvwco.push(temp_vvvvwco);
	}
	else if (!isSet(protocol_vvvvwco))
	{
		var protocol_vvvvwco = [];
	}
	var protocol = protocol_vvvvwco.some(protocol_vvvvwco_SomeFunc);

	if (isSet(authentication_vvvvwco) && authentication_vvvvwco.constructor !== Array)
	{
		var temp_vvvvwco = authentication_vvvvwco;
		var authentication_vvvvwco = [];
		authentication_vvvvwco.push(temp_vvvvwco);
	}
	else if (!isSet(authentication_vvvvwco))
	{
		var authentication_vvvvwco = [];
	}
	var authentication = authentication_vvvvwco.some(authentication_vvvvwco_SomeFunc);


	// set this function logic
	if (protocol && authentication)
	{
		jQuery('#jform_private').closest('.control-group').show();
		// Add required attributes to private.
		jQuery('#jform_private').prop('required', true);
		jQuery('#jform_private').attr('aria-required', true);
		jQuery('#jform_private').addClass('required');
	}
	else
	{
		jQuery('#jform_private').closest('.control-group').hide();
		// Remove required attributes from private.
		jQuery('#jform_private').removeAttr('required');
		jQuery('#jform_private').removeAttr('aria-required');
		jQuery('#jform_private').removeClass('required');
	}
}

// the vvvvwco Some function
function protocol_vvvvwco_SomeFunc(protocol_vvvvwco)
{
	// set the function logic
	if (protocol_vvvvwco == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwco Some function
function authentication_vvvvwco_SomeFunc(authentication_vvvvwco)
{
	// set the function logic
	if (authentication_vvvvwco == Number('2') || authentication_vvvvwco == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvwcq function
function vvvvwcq(protocol_vvvvwcq,authentication_vvvvwcq)
{
	if (isSet(protocol_vvvvwcq) && protocol_vvvvwcq.constructor !== Array)
	{
		var temp_vvvvwcq = protocol_vvvvwcq;
		var protocol_vvvvwcq = [];
		protocol_vvvvwcq.push(temp_vvvvwcq);
	}
	else if (!isSet(protocol_vvvvwcq))
	{
		var protocol_vvvvwcq = [];
	}
	var protocol = protocol_vvvvwcq.some(protocol_vvvvwcq_SomeFunc);

	if (isSet(authentication_vvvvwcq) && authentication_vvvvwcq.constructor !== Array)
	{
		var temp_vvvvwcq = authentication_vvvvwcq;
		var authentication_vvvvwcq = [];
		authentication_vvvvwcq.push(temp_vvvvwcq);
	}
	else if (!isSet(authentication_vvvvwcq))
	{
		var authentication_vvvvwcq = [];
	}
	var authentication = authentication_vvvvwcq.some(authentication_vvvvwcq_SomeFunc);


	// set this function logic
	if (protocol && authentication)
	{
		jQuery('#jform_private_key').closest('.control-group').show();
		// Add required attributes to private_key.
		jQuery('#jform_private_key').prop('required', true);
		jQuery('#jform_private_key').attr('aria-required', true);
		jQuery('#jform_private_key').addClass('required');
	}
	else
	{
		jQuery('#jform_private_key').closest('.control-group').hide();
		// Remove required attributes from private_key.
		jQuery('#jform_private_key').removeAttr('required');
		jQuery('#jform_private_key').removeAttr('aria-required');
		jQuery('#jform_private_key').removeClass('required');
	}
}

// the vvvvwcq Some function
function protocol_vvvvwcq_SomeFunc(protocol_vvvvwcq)
{
	// set the function logic
	if (protocol_vvvvwcq == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwcq Some function
function authentication_vvvvwcq_SomeFunc(authentication_vvvvwcq)
{
	// set the function logic
	if (authentication_vvvvwcq == Number('4') || authentication_vvvvwcq == Number('5'))
	{
		return true;
	}
	return false;
}

// the vvvvwcs function
function vvvvwcs(protocol_vvvvwcs,authentication_vvvvwcs)
{
	if (isSet(protocol_vvvvwcs) && protocol_vvvvwcs.constructor !== Array)
	{
		var temp_vvvvwcs = protocol_vvvvwcs;
		var protocol_vvvvwcs = [];
		protocol_vvvvwcs.push(temp_vvvvwcs);
	}
	else if (!isSet(protocol_vvvvwcs))
	{
		var protocol_vvvvwcs = [];
	}
	var protocol = protocol_vvvvwcs.some(protocol_vvvvwcs_SomeFunc);

	if (isSet(authentication_vvvvwcs) && authentication_vvvvwcs.constructor !== Array)
	{
		var temp_vvvvwcs = authentication_vvvvwcs;
		var authentication_vvvvwcs = [];
		authentication_vvvvwcs.push(temp_vvvvwcs);
	}
	else if (!isSet(authentication_vvvvwcs))
	{
		var authentication_vvvvwcs = [];
	}
	var authentication = authentication_vvvvwcs.some(authentication_vvvvwcs_SomeFunc);


	// set this function logic
	if (protocol && authentication)
	{
		jQuery('#jform_secret').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_secret').closest('.control-group').hide();
	}
}

// the vvvvwcs Some function
function protocol_vvvvwcs_SomeFunc(protocol_vvvvwcs)
{
	// set the function logic
	if (protocol_vvvvwcs == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwcs Some function
function authentication_vvvvwcs_SomeFunc(authentication_vvvvwcs)
{
	// set the function logic
	if (authentication_vvvvwcs == Number('2') || authentication_vvvvwcs == Number('3') || authentication_vvvvwcs == Number('4') || authentication_vvvvwcs == Number('5'))
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
