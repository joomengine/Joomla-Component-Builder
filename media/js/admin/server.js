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

	var protocol_vvvvwcg = jQuery("#jform_protocol").val();
	vvvvwcg(protocol_vvvvwcg);

	var protocol_vvvvwch = jQuery("#jform_protocol").val();
	vvvvwch(protocol_vvvvwch);

	var protocol_vvvvwci = jQuery("#jform_protocol").val();
	var authentication_vvvvwci = jQuery("#jform_authentication").val();
	vvvvwci(protocol_vvvvwci,authentication_vvvvwci);

	var protocol_vvvvwck = jQuery("#jform_protocol").val();
	var authentication_vvvvwck = jQuery("#jform_authentication").val();
	vvvvwck(protocol_vvvvwck,authentication_vvvvwck);

	var protocol_vvvvwcm = jQuery("#jform_protocol").val();
	var authentication_vvvvwcm = jQuery("#jform_authentication").val();
	vvvvwcm(protocol_vvvvwcm,authentication_vvvvwcm);

	var protocol_vvvvwco = jQuery("#jform_protocol").val();
	var authentication_vvvvwco = jQuery("#jform_authentication").val();
	vvvvwco(protocol_vvvvwco,authentication_vvvvwco);

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

// the vvvvwcg function
function vvvvwcg(protocol_vvvvwcg)
{
	if (isSet(protocol_vvvvwcg) && protocol_vvvvwcg.constructor !== Array)
	{
		var temp_vvvvwcg = protocol_vvvvwcg;
		var protocol_vvvvwcg = [];
		protocol_vvvvwcg.push(temp_vvvvwcg);
	}
	else if (!isSet(protocol_vvvvwcg))
	{
		var protocol_vvvvwcg = [];
	}
	var protocol = protocol_vvvvwcg.some(protocol_vvvvwcg_SomeFunc);


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

// the vvvvwcg Some function
function protocol_vvvvwcg_SomeFunc(protocol_vvvvwcg)
{
	// set the function logic
	if (protocol_vvvvwcg == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwch function
function vvvvwch(protocol_vvvvwch)
{
	if (isSet(protocol_vvvvwch) && protocol_vvvvwch.constructor !== Array)
	{
		var temp_vvvvwch = protocol_vvvvwch;
		var protocol_vvvvwch = [];
		protocol_vvvvwch.push(temp_vvvvwch);
	}
	else if (!isSet(protocol_vvvvwch))
	{
		var protocol_vvvvwch = [];
	}
	var protocol = protocol_vvvvwch.some(protocol_vvvvwch_SomeFunc);


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

// the vvvvwch Some function
function protocol_vvvvwch_SomeFunc(protocol_vvvvwch)
{
	// set the function logic
	if (protocol_vvvvwch == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwci function
function vvvvwci(protocol_vvvvwci,authentication_vvvvwci)
{
	if (isSet(protocol_vvvvwci) && protocol_vvvvwci.constructor !== Array)
	{
		var temp_vvvvwci = protocol_vvvvwci;
		var protocol_vvvvwci = [];
		protocol_vvvvwci.push(temp_vvvvwci);
	}
	else if (!isSet(protocol_vvvvwci))
	{
		var protocol_vvvvwci = [];
	}
	var protocol = protocol_vvvvwci.some(protocol_vvvvwci_SomeFunc);

	if (isSet(authentication_vvvvwci) && authentication_vvvvwci.constructor !== Array)
	{
		var temp_vvvvwci = authentication_vvvvwci;
		var authentication_vvvvwci = [];
		authentication_vvvvwci.push(temp_vvvvwci);
	}
	else if (!isSet(authentication_vvvvwci))
	{
		var authentication_vvvvwci = [];
	}
	var authentication = authentication_vvvvwci.some(authentication_vvvvwci_SomeFunc);


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

// the vvvvwci Some function
function protocol_vvvvwci_SomeFunc(protocol_vvvvwci)
{
	// set the function logic
	if (protocol_vvvvwci == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwci Some function
function authentication_vvvvwci_SomeFunc(authentication_vvvvwci)
{
	// set the function logic
	if (authentication_vvvvwci == Number('1') || authentication_vvvvwci == Number('3') || authentication_vvvvwci == Number('5'))
	{
		return true;
	}
	return false;
}

// the vvvvwck function
function vvvvwck(protocol_vvvvwck,authentication_vvvvwck)
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

	if (isSet(authentication_vvvvwck) && authentication_vvvvwck.constructor !== Array)
	{
		var temp_vvvvwck = authentication_vvvvwck;
		var authentication_vvvvwck = [];
		authentication_vvvvwck.push(temp_vvvvwck);
	}
	else if (!isSet(authentication_vvvvwck))
	{
		var authentication_vvvvwck = [];
	}
	var authentication = authentication_vvvvwck.some(authentication_vvvvwck_SomeFunc);


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

// the vvvvwck Some function
function authentication_vvvvwck_SomeFunc(authentication_vvvvwck)
{
	// set the function logic
	if (authentication_vvvvwck == Number('2') || authentication_vvvvwck == Number('3'))
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
	if (authentication_vvvvwcm == Number('4') || authentication_vvvvwcm == Number('5'))
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
		jQuery('#jform_secret').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_secret').closest('.control-group').hide();
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
	if (authentication_vvvvwco == Number('2') || authentication_vvvvwco == Number('3') || authentication_vvvvwco == Number('4') || authentication_vvvvwco == Number('5'))
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
