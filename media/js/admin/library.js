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
var updateLibraryConditionsBaseline = null;

/**
 * Recompute this view's conditions in definition order from the original baseline.
 * Call after authored code assigns watched values, including AJAX defaults.
 * @return {void}
 * @since 6.2.1
 */
function updateLibraryConditions()
{
	if (updateLibraryConditionsBaseline === null)
	{
		updateLibraryConditionsBaseline = {containers: [], required: []};
		var selectors = ["#jform_addconditions-lbl","#jform_php_setdocument",".note_display_library_config",".note_display_library_files_folders_urls",".note_no_behaviour_one",".note_no_behaviour_three",".note_no_behaviour_two",".note_yes_behaviour_one",".note_yes_behaviour_two",".note_build_in_behaviour_one",".note_build_in_behaviour_three",".note_build_in_behaviour_two","#jform_libraries","#jform_how","#jform_type",".note_yes_behaviour_library"];
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
					updateLibraryConditionsBaseline.containers.push({element: element, display: element.style.display});
				}
			}
		}
		var requiredNames = ["php_setdocument","how","type"];
		for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)
		{
			var controls = jQuery('#jform_' + requiredNames[requiredIndex]).get();
			for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)
			{
				var element = controls[controlIndex];
				var control = jQuery(element);
				updateLibraryConditionsBaseline.required.push({
					element: element,
					required: !control.is(':disabled')
						&& (Boolean(element.required) || element.hasAttribute('required') || control.hasClass('required'))
				});
			}
		}
	}

	for (var index = 0; index < updateLibraryConditionsBaseline.containers.length; index++)
	{
		var baseline = updateLibraryConditionsBaseline.containers[index];
		baseline.element.style.display = baseline.display;
	}
	var required = updateLibraryConditionsBaseline.required;
	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).prop('required', true)
				.attr('aria-required', true).addClass('required');
		}
	}

	var how_vvvvwao = jQuery("#jform_how").val();
	var target_vvvvwao = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwao(how_vvvvwao,target_vvvvwao);

	var how_vvvvwaq = jQuery("#jform_how").val();
	var target_vvvvwaq = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwaq(how_vvvvwaq,target_vvvvwaq);

	var how_vvvvwas = jQuery("#jform_how").val();
	var target_vvvvwas = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwas(how_vvvvwas,target_vvvvwas);

	var how_vvvvwau = jQuery("#jform_how").val();
	var target_vvvvwau = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwau(how_vvvvwau,target_vvvvwau);

	var how_vvvvwaw = jQuery("#jform_how").val();
	var target_vvvvwaw = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwaw(how_vvvvwaw,target_vvvvwaw);

	var target_vvvvwax = jQuery("#jform_target input[type='radio']:checked").val();
	var how_vvvvwax = jQuery("#jform_how").val();
	vvvvwax(target_vvvvwax,how_vvvvwax);

	var how_vvvvway = jQuery("#jform_how").val();
	var target_vvvvway = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvway(how_vvvvway,target_vvvvway);

	var target_vvvvwaz = jQuery("#jform_target input[type='radio']:checked").val();
	var how_vvvvwaz = jQuery("#jform_how").val();
	vvvvwaz(target_vvvvwaz,how_vvvvwaz);

	var how_vvvvwba = jQuery("#jform_how").val();
	var target_vvvvwba = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwba(how_vvvvwba,target_vvvvwba);

	var target_vvvvwbb = jQuery("#jform_target input[type='radio']:checked").val();
	var how_vvvvwbb = jQuery("#jform_how").val();
	vvvvwbb(target_vvvvwbb,how_vvvvwbb);

	var target_vvvvwbc = jQuery("#jform_target input[type='radio']:checked").val();
	var type_vvvvwbc = jQuery("#jform_type input[type='radio']:checked").val();
	vvvvwbc(target_vvvvwbc,type_vvvvwbc);

	var target_vvvvwbe = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwbe(target_vvvvwbe);

	var target_vvvvwbf = jQuery("#jform_target input[type='radio']:checked").val();
	vvvvwbf(target_vvvvwbf);

	for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)
	{
		if (!required[requiredIndex].required)
		{
			jQuery(required[requiredIndex].element).removeAttr('required')
				.removeAttr('aria-required').removeClass('required');
		}
	}
}

document.addEventListener('DOMContentLoaded', updateLibraryConditions);

// the vvvvwao function
function vvvvwao(how_vvvvwao,target_vvvvwao)
{
	if (isSet(how_vvvvwao) && how_vvvvwao.constructor !== Array)
	{
		var temp_vvvvwao = how_vvvvwao;
		var how_vvvvwao = [];
		how_vvvvwao.push(temp_vvvvwao);
	}
	else if (!isSet(how_vvvvwao))
	{
		var how_vvvvwao = [];
	}
	var how = how_vvvvwao.some(how_vvvvwao_SomeFunc);

	if (isSet(target_vvvvwao) && target_vvvvwao.constructor !== Array)
	{
		var temp_vvvvwao = target_vvvvwao;
		var target_vvvvwao = [];
		target_vvvvwao.push(temp_vvvvwao);
	}
	else if (!isSet(target_vvvvwao))
	{
		var target_vvvvwao = [];
	}
	var target = target_vvvvwao.some(target_vvvvwao_SomeFunc);


	// set this function logic
	if (how && target)
	{
		jQuery('#jform_addconditions-lbl').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_addconditions-lbl').closest('.control-group').hide();
	}
}

// the vvvvwao Some function
function how_vvvvwao_SomeFunc(how_vvvvwao)
{
	// set the function logic
	if (how_vvvvwao == Number('2'))
	{
		return true;
	}
	return false;
}

// the vvvvwao Some function
function target_vvvvwao_SomeFunc(target_vvvvwao)
{
	// set the function logic
	if (target_vvvvwao == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwaq function
function vvvvwaq(how_vvvvwaq,target_vvvvwaq)
{
	if (isSet(how_vvvvwaq) && how_vvvvwaq.constructor !== Array)
	{
		var temp_vvvvwaq = how_vvvvwaq;
		var how_vvvvwaq = [];
		how_vvvvwaq.push(temp_vvvvwaq);
	}
	else if (!isSet(how_vvvvwaq))
	{
		var how_vvvvwaq = [];
	}
	var how = how_vvvvwaq.some(how_vvvvwaq_SomeFunc);

	if (isSet(target_vvvvwaq) && target_vvvvwaq.constructor !== Array)
	{
		var temp_vvvvwaq = target_vvvvwaq;
		var target_vvvvwaq = [];
		target_vvvvwaq.push(temp_vvvvwaq);
	}
	else if (!isSet(target_vvvvwaq))
	{
		var target_vvvvwaq = [];
	}
	var target = target_vvvvwaq.some(target_vvvvwaq_SomeFunc);


	// set this function logic
	if (how && target)
	{
		jQuery('#jform_php_setdocument').closest('.control-group').show();
		// Add required attributes to php_setdocument.
		jQuery('#jform_php_setdocument').prop('required', true);
		jQuery('#jform_php_setdocument').attr('aria-required', true);
		jQuery('#jform_php_setdocument').addClass('required');
	}
	else
	{
		jQuery('#jform_php_setdocument').closest('.control-group').hide();
		// Remove required attributes from php_setdocument.
		jQuery('#jform_php_setdocument').removeAttr('required');
		jQuery('#jform_php_setdocument').removeAttr('aria-required');
		jQuery('#jform_php_setdocument').removeClass('required');
	}
}

// the vvvvwaq Some function
function how_vvvvwaq_SomeFunc(how_vvvvwaq)
{
	// set the function logic
	if (how_vvvvwaq == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvwaq Some function
function target_vvvvwaq_SomeFunc(target_vvvvwaq)
{
	// set the function logic
	if (target_vvvvwaq == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwas function
function vvvvwas(how_vvvvwas,target_vvvvwas)
{
	if (isSet(how_vvvvwas) && how_vvvvwas.constructor !== Array)
	{
		var temp_vvvvwas = how_vvvvwas;
		var how_vvvvwas = [];
		how_vvvvwas.push(temp_vvvvwas);
	}
	else if (!isSet(how_vvvvwas))
	{
		var how_vvvvwas = [];
	}
	var how = how_vvvvwas.some(how_vvvvwas_SomeFunc);

	if (isSet(target_vvvvwas) && target_vvvvwas.constructor !== Array)
	{
		var temp_vvvvwas = target_vvvvwas;
		var target_vvvvwas = [];
		target_vvvvwas.push(temp_vvvvwas);
	}
	else if (!isSet(target_vvvvwas))
	{
		var target_vvvvwas = [];
	}
	var target = target_vvvvwas.some(target_vvvvwas_SomeFunc);


	// set this function logic
	if (how && target)
	{
		jQuery('.note_display_library_config').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_display_library_config').closest('.control-group').hide();
	}
}

// the vvvvwas Some function
function how_vvvvwas_SomeFunc(how_vvvvwas)
{
	// set the function logic
	if (how_vvvvwas == Number('2') || how_vvvvwas == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvwas Some function
function target_vvvvwas_SomeFunc(target_vvvvwas)
{
	// set the function logic
	if (target_vvvvwas == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwau function
function vvvvwau(how_vvvvwau,target_vvvvwau)
{
	if (isSet(how_vvvvwau) && how_vvvvwau.constructor !== Array)
	{
		var temp_vvvvwau = how_vvvvwau;
		var how_vvvvwau = [];
		how_vvvvwau.push(temp_vvvvwau);
	}
	else if (!isSet(how_vvvvwau))
	{
		var how_vvvvwau = [];
	}
	var how = how_vvvvwau.some(how_vvvvwau_SomeFunc);

	if (isSet(target_vvvvwau) && target_vvvvwau.constructor !== Array)
	{
		var temp_vvvvwau = target_vvvvwau;
		var target_vvvvwau = [];
		target_vvvvwau.push(temp_vvvvwau);
	}
	else if (!isSet(target_vvvvwau))
	{
		var target_vvvvwau = [];
	}
	var target = target_vvvvwau.some(target_vvvvwau_SomeFunc);


	// set this function logic
	if (how && target)
	{
		jQuery('.note_display_library_files_folders_urls').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_display_library_files_folders_urls').closest('.control-group').hide();
	}
}

// the vvvvwau Some function
function how_vvvvwau_SomeFunc(how_vvvvwau)
{
	// set the function logic
	if (how_vvvvwau == Number('1') || how_vvvvwau == Number('2') || how_vvvvwau == Number('3'))
	{
		return true;
	}
	return false;
}

// the vvvvwau Some function
function target_vvvvwau_SomeFunc(target_vvvvwau)
{
	// set the function logic
	if (target_vvvvwau == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwaw function
function vvvvwaw(how_vvvvwaw,target_vvvvwaw)
{
	if (isSet(how_vvvvwaw) && how_vvvvwaw.constructor !== Array)
	{
		var temp_vvvvwaw = how_vvvvwaw;
		var how_vvvvwaw = [];
		how_vvvvwaw.push(temp_vvvvwaw);
	}
	else if (!isSet(how_vvvvwaw))
	{
		var how_vvvvwaw = [];
	}
	var how = how_vvvvwaw.some(how_vvvvwaw_SomeFunc);

	if (isSet(target_vvvvwaw) && target_vvvvwaw.constructor !== Array)
	{
		var temp_vvvvwaw = target_vvvvwaw;
		var target_vvvvwaw = [];
		target_vvvvwaw.push(temp_vvvvwaw);
	}
	else if (!isSet(target_vvvvwaw))
	{
		var target_vvvvwaw = [];
	}
	var target = target_vvvvwaw.some(target_vvvvwaw_SomeFunc);


	// set this function logic
	if (how && target)
	{
		jQuery('.note_no_behaviour_one').closest('.control-group').show();
		jQuery('.note_no_behaviour_three').closest('.control-group').show();
		jQuery('.note_no_behaviour_two').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_no_behaviour_one').closest('.control-group').hide();
		jQuery('.note_no_behaviour_three').closest('.control-group').hide();
		jQuery('.note_no_behaviour_two').closest('.control-group').hide();
	}
}

// the vvvvwaw Some function
function how_vvvvwaw_SomeFunc(how_vvvvwaw)
{
	// set the function logic
	if (how_vvvvwaw == Number('0'))
	{
		return true;
	}
	return false;
}

// the vvvvwaw Some function
function target_vvvvwaw_SomeFunc(target_vvvvwaw)
{
	// set the function logic
	if (target_vvvvwaw == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwax function
function vvvvwax(target_vvvvwax,how_vvvvwax)
{
	if (isSet(target_vvvvwax) && target_vvvvwax.constructor !== Array)
	{
		var temp_vvvvwax = target_vvvvwax;
		var target_vvvvwax = [];
		target_vvvvwax.push(temp_vvvvwax);
	}
	else if (!isSet(target_vvvvwax))
	{
		var target_vvvvwax = [];
	}
	var target = target_vvvvwax.some(target_vvvvwax_SomeFunc);

	if (isSet(how_vvvvwax) && how_vvvvwax.constructor !== Array)
	{
		var temp_vvvvwax = how_vvvvwax;
		var how_vvvvwax = [];
		how_vvvvwax.push(temp_vvvvwax);
	}
	else if (!isSet(how_vvvvwax))
	{
		var how_vvvvwax = [];
	}
	var how = how_vvvvwax.some(how_vvvvwax_SomeFunc);


	// set this function logic
	if (target && how)
	{
		jQuery('.note_no_behaviour_one').closest('.control-group').show();
		jQuery('.note_no_behaviour_three').closest('.control-group').show();
		jQuery('.note_no_behaviour_two').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_no_behaviour_one').closest('.control-group').hide();
		jQuery('.note_no_behaviour_three').closest('.control-group').hide();
		jQuery('.note_no_behaviour_two').closest('.control-group').hide();
	}
}

// the vvvvwax Some function
function target_vvvvwax_SomeFunc(target_vvvvwax)
{
	// set the function logic
	if (target_vvvvwax == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwax Some function
function how_vvvvwax_SomeFunc(how_vvvvwax)
{
	// set the function logic
	if (how_vvvvwax == Number('0'))
	{
		return true;
	}
	return false;
}

// the vvvvway function
function vvvvway(how_vvvvway,target_vvvvway)
{
	if (isSet(how_vvvvway) && how_vvvvway.constructor !== Array)
	{
		var temp_vvvvway = how_vvvvway;
		var how_vvvvway = [];
		how_vvvvway.push(temp_vvvvway);
	}
	else if (!isSet(how_vvvvway))
	{
		var how_vvvvway = [];
	}
	var how = how_vvvvway.some(how_vvvvway_SomeFunc);

	if (isSet(target_vvvvway) && target_vvvvway.constructor !== Array)
	{
		var temp_vvvvway = target_vvvvway;
		var target_vvvvway = [];
		target_vvvvway.push(temp_vvvvway);
	}
	else if (!isSet(target_vvvvway))
	{
		var target_vvvvway = [];
	}
	var target = target_vvvvway.some(target_vvvvway_SomeFunc);


	// set this function logic
	if (how && target)
	{
		jQuery('.note_yes_behaviour_one').closest('.control-group').show();
		jQuery('.note_yes_behaviour_two').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_yes_behaviour_one').closest('.control-group').hide();
		jQuery('.note_yes_behaviour_two').closest('.control-group').hide();
	}
}

// the vvvvway Some function
function how_vvvvway_SomeFunc(how_vvvvway)
{
	// set the function logic
	if (how_vvvvway == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvway Some function
function target_vvvvway_SomeFunc(target_vvvvway)
{
	// set the function logic
	if (target_vvvvway == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwaz function
function vvvvwaz(target_vvvvwaz,how_vvvvwaz)
{
	if (isSet(target_vvvvwaz) && target_vvvvwaz.constructor !== Array)
	{
		var temp_vvvvwaz = target_vvvvwaz;
		var target_vvvvwaz = [];
		target_vvvvwaz.push(temp_vvvvwaz);
	}
	else if (!isSet(target_vvvvwaz))
	{
		var target_vvvvwaz = [];
	}
	var target = target_vvvvwaz.some(target_vvvvwaz_SomeFunc);

	if (isSet(how_vvvvwaz) && how_vvvvwaz.constructor !== Array)
	{
		var temp_vvvvwaz = how_vvvvwaz;
		var how_vvvvwaz = [];
		how_vvvvwaz.push(temp_vvvvwaz);
	}
	else if (!isSet(how_vvvvwaz))
	{
		var how_vvvvwaz = [];
	}
	var how = how_vvvvwaz.some(how_vvvvwaz_SomeFunc);


	// set this function logic
	if (target && how)
	{
		jQuery('.note_yes_behaviour_one').closest('.control-group').show();
		jQuery('.note_yes_behaviour_two').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_yes_behaviour_one').closest('.control-group').hide();
		jQuery('.note_yes_behaviour_two').closest('.control-group').hide();
	}
}

// the vvvvwaz Some function
function target_vvvvwaz_SomeFunc(target_vvvvwaz)
{
	// set the function logic
	if (target_vvvvwaz == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwaz Some function
function how_vvvvwaz_SomeFunc(how_vvvvwaz)
{
	// set the function logic
	if (how_vvvvwaz == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwba function
function vvvvwba(how_vvvvwba,target_vvvvwba)
{
	if (isSet(how_vvvvwba) && how_vvvvwba.constructor !== Array)
	{
		var temp_vvvvwba = how_vvvvwba;
		var how_vvvvwba = [];
		how_vvvvwba.push(temp_vvvvwba);
	}
	else if (!isSet(how_vvvvwba))
	{
		var how_vvvvwba = [];
	}
	var how = how_vvvvwba.some(how_vvvvwba_SomeFunc);

	if (isSet(target_vvvvwba) && target_vvvvwba.constructor !== Array)
	{
		var temp_vvvvwba = target_vvvvwba;
		var target_vvvvwba = [];
		target_vvvvwba.push(temp_vvvvwba);
	}
	else if (!isSet(target_vvvvwba))
	{
		var target_vvvvwba = [];
	}
	var target = target_vvvvwba.some(target_vvvvwba_SomeFunc);


	// set this function logic
	if (how && target)
	{
		jQuery('.note_build_in_behaviour_one').closest('.control-group').show();
		jQuery('.note_build_in_behaviour_three').closest('.control-group').show();
		jQuery('.note_build_in_behaviour_two').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_build_in_behaviour_one').closest('.control-group').hide();
		jQuery('.note_build_in_behaviour_three').closest('.control-group').hide();
		jQuery('.note_build_in_behaviour_two').closest('.control-group').hide();
	}
}

// the vvvvwba Some function
function how_vvvvwba_SomeFunc(how_vvvvwba)
{
	// set the function logic
	if (how_vvvvwba == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvwba Some function
function target_vvvvwba_SomeFunc(target_vvvvwba)
{
	// set the function logic
	if (target_vvvvwba == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwbb function
function vvvvwbb(target_vvvvwbb,how_vvvvwbb)
{
	if (isSet(target_vvvvwbb) && target_vvvvwbb.constructor !== Array)
	{
		var temp_vvvvwbb = target_vvvvwbb;
		var target_vvvvwbb = [];
		target_vvvvwbb.push(temp_vvvvwbb);
	}
	else if (!isSet(target_vvvvwbb))
	{
		var target_vvvvwbb = [];
	}
	var target = target_vvvvwbb.some(target_vvvvwbb_SomeFunc);

	if (isSet(how_vvvvwbb) && how_vvvvwbb.constructor !== Array)
	{
		var temp_vvvvwbb = how_vvvvwbb;
		var how_vvvvwbb = [];
		how_vvvvwbb.push(temp_vvvvwbb);
	}
	else if (!isSet(how_vvvvwbb))
	{
		var how_vvvvwbb = [];
	}
	var how = how_vvvvwbb.some(how_vvvvwbb_SomeFunc);


	// set this function logic
	if (target && how)
	{
		jQuery('.note_build_in_behaviour_one').closest('.control-group').show();
		jQuery('.note_build_in_behaviour_three').closest('.control-group').show();
		jQuery('.note_build_in_behaviour_two').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_build_in_behaviour_one').closest('.control-group').hide();
		jQuery('.note_build_in_behaviour_three').closest('.control-group').hide();
		jQuery('.note_build_in_behaviour_two').closest('.control-group').hide();
	}
}

// the vvvvwbb Some function
function target_vvvvwbb_SomeFunc(target_vvvvwbb)
{
	// set the function logic
	if (target_vvvvwbb == Number('1'))
	{
		return true;
	}
	return false;
}

// the vvvvwbb Some function
function how_vvvvwbb_SomeFunc(how_vvvvwbb)
{
	// set the function logic
	if (how_vvvvwbb == Number('4'))
	{
		return true;
	}
	return false;
}

// the vvvvwbc function
function vvvvwbc(target_vvvvwbc,type_vvvvwbc)
{
	// set the function logic
	if ((target_vvvvwbc == Number('1')) && (type_vvvvwbc == Number('2')))
	{
		jQuery('#jform_libraries').closest('.control-group').show();
	}
	else
	{
		jQuery('#jform_libraries').closest('.control-group').hide();
	}
}

// the vvvvwbe function
function vvvvwbe(target_vvvvwbe)
{
	// set the function logic
	if (target_vvvvwbe == Number('1'))
	{
		jQuery('#jform_how').closest('.control-group').show();
		// Add required attributes to how.
		jQuery('#jform_how').prop('required', true);
		jQuery('#jform_how').attr('aria-required', true);
		jQuery('#jform_how').addClass('required');
		jQuery('#jform_type').closest('.control-group').show();
		// Add required attributes to type.
		jQuery('#jform_type').prop('required', true);
		jQuery('#jform_type').attr('aria-required', true);
		jQuery('#jform_type').addClass('required');
	}
	else
	{
		jQuery('#jform_how').closest('.control-group').hide();
		// Remove required attributes from how.
		jQuery('#jform_how').removeAttr('required');
		jQuery('#jform_how').removeAttr('aria-required');
		jQuery('#jform_how').removeClass('required');
		jQuery('#jform_type').closest('.control-group').hide();
		// Remove required attributes from type.
		jQuery('#jform_type').removeAttr('required');
		jQuery('#jform_type').removeAttr('aria-required');
		jQuery('#jform_type').removeClass('required');
	}
}

// the vvvvwbf function
function vvvvwbf(target_vvvvwbf)
{
	// set the function logic
	if (target_vvvvwbf == Number('2'))
	{
		jQuery('.note_yes_behaviour_library').closest('.control-group').show();
	}
	else
	{
		jQuery('.note_yes_behaviour_library').closest('.control-group').hide();
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


jQuery(document).ready(function()
{
	// get the linked details
	getLinked();
	// now load the displays
	getAjaxDisplay('library_config');
	getAjaxDisplay('library_files_folders_urls');

	// check and load all the customcode edit buttons
	setTimeout(getEditCustomCodeButtons, 300);
});

function addData(result,where){
	jQuery(result).insertAfter(jQuery(where).closest('.control-group'));
}

function getAjaxDisplay(type){
	getCodeFrom_server(1, type, 'type', 'getAjaxDisplay').then(function(result) {
		if (result) {
			jQuery('#display_'+type).html(result);
		}
		// set button
		addButtonID(type,'header_'+type+'_buttons', 2); // <-- little edit button
	});
}

function getFieldSelectOptions(fieldKey){
	// first check if the field is set
	if(jQuery("#jform_addconditions__addconditions"+fieldKey+"__option_field").length) {
		var fieldId = jQuery("#jform_addconditions__addconditions"+fieldKey+"__option_field option:selected").val();
		getCodeFrom_server(fieldId, 'type', 'type', 'fieldSelectOptions').then(function(result) {
			if(result) {
				jQuery('textarea#jform_addconditions__addconditions'+fieldKey+'__field_options').val(result);
			} else {
				jQuery('textarea#jform_addconditions__addconditions'+fieldKey+'__field_options').val('');
			}
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

function getLinked() {
	getCodeFrom_server(1, 'type', 'type', 'getLinked').then(function(result) {
		if (result.error) {
			console.error(result.error);
		} else if (result) {
			document.getElementById('display_linked_to').innerHTML = result;
		}
	});
}
