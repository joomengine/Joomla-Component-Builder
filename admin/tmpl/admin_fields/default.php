<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate');
Html::_('bootstrap.tooltip');

// No direct access to this file
defined('_JEXEC') or die;

$layout  = $this->isModal ? 'modal' : 'edit';
$tmpl    = $this->input->get('tmpl');
$tmpl    = $tmpl ? '&tmpl=' . $tmpl : '';
?>
<script type="text/javascript">
	(function() {
		// create loading overlay
		var loadingDiv = document.createElement('div');
		loadingDiv.id = 'loading';
		loadingDiv.style.position = 'fixed';
		loadingDiv.style.top = '0';
		loadingDiv.style.left = '0';
		loadingDiv.style.right = '0';
		loadingDiv.style.bottom = '0';
		loadingDiv.style.width = '100%';
		loadingDiv.style.height = '100%';
		loadingDiv.style.background = "rgba(255,255,255,0.8) url('components/com_componentbuilder/assets/images/ajax.gif') 50% 35% no-repeat";
		loadingDiv.style.opacity = '0.8';
		loadingDiv.style.zIndex = '9999';
		loadingDiv.style.display = 'block';
		loadingDiv.style.msFilter = "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
		loadingDiv.style.filter = "alpha(opacity=80)";
		document.body.appendChild(loadingDiv);
		// remove overlay when page fully loaded
		window.addEventListener('load', function() {
			var componentLoader = document.getElementById('componentbuilder_loader');
			if (componentLoader) componentLoader.style.display = 'block';
			loadingDiv.style.display = 'none';
		});
	})();
</script>
<div id="componentbuilder_loader" style="display: none;">
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=admin_fields&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('admin_fields.fields_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'admin_fieldsTab', ['active' => 'fields', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'admin_fieldsTab', 'fields', Text::_('COM_COMPONENTBUILDER_ADMIN_FIELDS_FIELDS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_fields.fields_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'admin_fieldsTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('admin_fields.edit.created_by') || $this->canDo->get('admin_fields.edit.created') || $this->canDo->get('admin_fields.edit.state') || ($this->canDo->get('admin_fields.delete') && $this->canDo->get('admin_fields.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'admin_fieldsTab', 'publishing', Text::_('COM_COMPONENTBUILDER_ADMIN_FIELDS_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_fields.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_fields.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'admin_fieldsTab', 'permissions', Text::_('COM_COMPONENTBUILDER_ADMIN_FIELDS_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_COMPONENTBUILDER_ADMIN_FIELDS_PERMISSION'); ?></legend>
					<div>
						<?php echo $this->form->getInput('rules'); ?>
					</div>
				</fieldset>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php echo Html::_('uitab.endTabSet'); ?>

	<div>
		<input type="hidden" name="task" value="admin_fields.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>
</form>
</div>

<script type="text/javascript">



// little script to check value and give notice
function checkAdminBehaviour(field) {
	// get the ID
	var id = jQuery(field).attr('id');
	var target = id.split('__');
	//set the subID
	var subID = target[0]+'__'+target[1];
	// get value
	var value = jQuery('#'+subID+'__list').val();
	// set notice and do house cleaning
	if (2 == value) {
		// no database
		if (target[2] == 'list') {
			jQuery.UIkit.notify({message: Joomla.Text._('COM_COMPONENTBUILDER_ONLY_USE_THE_BNONE_DBB_OPTION_IF_YOU_ARE_PLANNING_ON_TARGETING_THIS_FIELD_WITH_JAVASCRIPTCUSTOM_PHP_TO_MOVE_ITS_VALUE_INTO_ANOTHER_FIELD_THAT_DOES_GET_SAVED_TO_THE_DATABASE'), timeout: 10000, status: 'warning', pos: 'top-right'});
			jQuery.UIkit.notify({message: Joomla.Text._('COM_COMPONENTBUILDER_THE_BNONE_DBB_OPTION_WILL_REMOVE_THIS_FIELD_FROM_BEING_SAVED_IN_THE_DATABASE'), timeout: 5000, status: 'primary', pos: 'top-right'});
		} else {
			jQuery.UIkit.notify({message: Joomla.Text._('COM_COMPONENTBUILDER_THESE_OPTIONS_ARE_NOT_AVAILABLE_TO_THE_FIELD_IF_BNONE_DBB_OPTION_IS_SELECTED'), timeout: 7000, status: 'warning', pos: 'top-right'});
		}
		// do some house cleaning
		jQuery('#'+subID+'__order_list').val(0).trigger('liszt:updated');
		jQuery('#'+subID+'__filter').val('').trigger('liszt:updated');
		jQuery('#'+subID+'__title').prop('checked', false).trigger('change');
		jQuery('#'+subID+'__alias').prop('checked', false).trigger('change');
		jQuery('#'+subID+'__sort').prop('checked', false).trigger('change');
		jQuery('#'+subID+'__search').prop('checked', false).trigger('change');
		jQuery('#'+subID+'__link').prop('checked', false).trigger('change');
	} else if (1 == value || 3 == value  || 4 == value) {
		// get number of items
		var numItems = jQuery('.count-the-items1235').length + 10;
		// show in list view
		if (target[2] == 'list') {
			if (1 == value) {
				jQuery.UIkit.notify({message: Joomla.Text._('COM_COMPONENTBUILDER_THE_BSHOW_IN_ALL_LIST_VIEWSB_OPTION_WILL_ADD_THIS_FIELD_TO_ALL_LIST_VIEWS_ADMIN_AMP_LINKED'), timeout: 5000, status: 'primary', pos: 'top-right'});
			} else if (3 == value) {
				jQuery.UIkit.notify({message: Joomla.Text._('COM_COMPONENTBUILDER_THE_BONLY_IN_ADMIN_LIST_VIEWB_OPTION_WILL_ONLY_ADD_THIS_FIELD_TO_THE_ADMIN_LIST_VIEW_NOT_TO_ANY_LINKED_VIEWS'), timeout: 5000, status: 'primary', pos: 'top-right'});
			} else if (4 == value) {
				jQuery.UIkit.notify({message: Joomla.Text._('COM_COMPONENTBUILDER_THE_BONLY_IN_LINKED_LIST_VIEWSB_OPTION_WILL_ONLY_ADD_THIS_FIELD_TO_THE_LINKED_LIST_VIEW_IF_THIS_VIEW_GETS_LINKED_TO_OTHER_VIEW_NOT_TO_THIS_ADMIN_LIST_VIEW'), timeout: 5000, status: 'primary', pos: 'top-right'});
			}
		}
		// check if the order list already has a value
		var orderList = jQuery('#'+subID+'__order_list').val();
		if (orderList == 0) {
			// count the already set and get the next number available
			var listviewNumber = fanAsgfdSffsNumber(subID.replace(/\d+/g, ''), numItems);
			// update the position
			jQuery('#'+subID+'__order_list').val(listviewNumber).trigger('liszt:updated');
		}
	} else {
		// do some house cleaning
		jQuery('#'+subID+'__order_list').val(0).trigger('liszt:updated');
		jQuery('#'+subID+'__filter').val('').trigger('liszt:updated');
		jQuery('#'+subID+'__sort').prop('checked', false).trigger('change');
		jQuery('#'+subID+'__link').prop('checked', false).trigger('change');
	}
}

// count the already set and get the next number available
function fanAsgfdSffsNumber(targetForm, numItems) {
	var i;
	// no check all the order values already set so to fill in the caps
	var numbers = [];
	for (i = 0; i < numItems; i++) { // for now this is the number of field we should check
		// build ID
		var id_check = targetForm+i+'__order_list';
		// first check if Id is on page
		if (jQuery("#"+id_check).length) {
			// get the property value
			var tmp = jQuery("#"+id_check+" option:selected").val();
			// now validate
			if (tmp >= 1) {
				numbers.push(parseInt(tmp));
			}
		}
	}
	// check that there are actually some set
	if (numbers.length) {
		// sort the array
		numbers.sort(fanAsgfdSffsSort);
		// get the absent values
		var absent = fanAsgfdSffsAbsent(numbers);
		// check if an absent value was found
		if (absent.length) {
			// sort the array (just to be safe)
			absent.sort(fanAsgfdSffsSort);
			// return lowest found value
			return absent[0];
		}
	}
	// since no absent value was found add to next available option
	var total = 0;
	for (i = 0; i < numItems; i++) { // for now this is the number of field we should check
		// build ID
		var id_check = targetForm+i+'__list';
		// first check if Id is on page
		if (jQuery("#"+id_check).length) {
			// get the property value
			var tmp = jQuery("#"+id_check+" option:selected").val();
			// now validate
			if (tmp >= 1) {
				total++;
			}
		}
	}
	return total;
}

// simple sort function
function fanAsgfdSffsSort(a,b) {
    return a - b;
}

// simple absent function
function fanAsgfdSffsAbsent(arr){
    var absentArray = [], min= 1, max = arr[arr.length-1];
    while(min < max){
        if(jQuery.inArray(min, arr) == -1) {
			absentArray.push(min);
		}
		min++;
    }
    return absentArray;
}
// little script to check that only one title is selected
function checkTitle(field) {
	// get the ID
	var id = jQuery(field).attr('id');
	var target = id.split('__');
	//set the subID
	var subID = target[0]+'__'+target[1];
	var subID = subID.replace(/\d+/g, '');
	// set notice and do house cleaning
	if (jQuery('#'+id).prop('checked')) {
		// get number of items
		var numItems = jQuery('.count-the-items1235').length + 10;
		for (i = 0; i < numItems; i++) { // for now this is the number of field we should check
			// build ID
			var id_check = subID+i+'__title';
			// first check if Id is on page
			if (jQuery("#"+id_check).length && id_check !== id) {
				// uncheck it
				jQuery("#"+id_check).prop('checked', false).trigger('change');
			}
		}
	}
}
// little script to check that only one title is selected
function checkAlias(field) {
	// get the ID
	var id = jQuery(field).attr('id');
	var target = id.split('__');
	//set the subID
	var subID = target[0]+'__'+target[1];
	var subID = subID.replace(/\d+/g, '');
	// set notice and do house cleaning
	if (jQuery('#'+id).prop('checked')) {
		// get number of items
		var numItems = jQuery('.count-the-items1235').length + 10;
		for (i = 0; i < numItems; i++) { // for now this is the number of field we should check
			// build ID
			var id_check = subID+i+'__alias';
			// first check if Id is on page
			if (jQuery("#"+id_check).length && id_check !== id) {
				// uncheck it
				jQuery("#"+id_check).prop('checked', false).trigger('change');
			}
		}
	}
}
// little script to check value and give notice
function explainFilterBehaviour(field) {
	// get the ID
	let id = jQuery(field).attr('id');
	// get value
	let value = jQuery('#'+id).val();
	// set notice and do house cleaning
	if (2 == value) {
		// means multi option can be selected in the filter, this is just available with the new filters
		jQuery.UIkit.notify({message: Joomla.JText._('COM_COMPONENTBUILDER_THE_BMULTI_FILTERB_SELECTION_OPTION_ALLOWS_THE_USER_TO_SELECT_MORE_THEN_ONE_VALUE_IN_THIS_FILTERFIELD_PLEASE_NOTE_THAT_THIS_OPTION_BONLY_WORKSB_WITH_THE_BNEWB_FILTERS_THAT_LOAD_ABOVE_THE_ADMIN_LIST_VIEW_YOU_CAN_SELECT_THE_NEW_FILTER_OPTION_WHENWHERE_YOU_ADD_THE_VIEW_TO_THE_COMPONENT'), timeout: 10000, status: 'primary', pos: 'top-right'});
	} else if (1 == value) {
		// means single option can be selected in the filter
		jQuery.UIkit.notify({message: Joomla.JText._('COM_COMPONENTBUILDER_THE_BSINGLE_FILTERB_SELECTION_OPTION_ALLOWS_THE_USER_TO_SELECT_JUST_ONE_VALUE_IN_THIS_FILTERFIELD'), timeout: 5000, status: 'primary', pos: 'top-right'});
	}
}
</script>
