/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */




function getFieldSelectOptions_server(fieldId){
	var getUrl = JRouter("index.php?option=com_componentbuilder&task=ajax.fieldSelectOptions&format=json");
	if(token.length > 0 && fieldId > 0){
		var request = token+'=1&id='+fieldId;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
	});
}
function getFieldSelectOptions(fieldKey){
	// first check if the field is set
	if(jQuery("#jform_addconditions__addconditions"+fieldKey+"__match_field").length) {
		var fieldId = jQuery("#jform_addconditions__addconditions"+fieldKey+"__match_field option:selected").val();
		getFieldSelectOptions_server(fieldId).done(function(result) {
			if(result){
				jQuery('textarea#jform_addconditions__addconditions'+fieldKey+'__match_options').val(result);
			}
			else
			{
				jQuery('textarea#jform_addconditions__addconditions'+fieldKey+'__match_options').val('');
			}
		});
	}
}


