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
use Joomla\CMS\Uri\Uri;

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
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=admin_fields_conditions&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('admin_fields_conditions.conditions_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'admin_fields_conditionsTab', ['active' => 'conditions', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'admin_fields_conditionsTab', 'conditions', Text::_('COM_COMPONENTBUILDER_ADMIN_FIELDS_CONDITIONS_CONDITIONS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_fields_conditions.conditions_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'admin_fields_conditionsTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('admin_fields_conditions.edit.created_by') || $this->canDo->get('admin_fields_conditions.edit.created') || $this->canDo->get('admin_fields_conditions.edit.state') || ($this->canDo->get('admin_fields_conditions.delete') && $this->canDo->get('admin_fields_conditions.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'admin_fields_conditionsTab', 'publishing', Text::_('COM_COMPONENTBUILDER_ADMIN_FIELDS_CONDITIONS_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_fields_conditions.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_fields_conditions.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'admin_fields_conditionsTab', 'permissions', Text::_('COM_COMPONENTBUILDER_ADMIN_FIELDS_CONDITIONS_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_COMPONENTBUILDER_ADMIN_FIELDS_CONDITIONS_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="admin_fields_conditions.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>
</form>
</div>

<script type="text/javascript">



<?php $numberAddconditions = range(0, count( (array) $this->item->addconditions) + 3, 1);?>

// for the values already set
jQuery(document).ready(function(){
<?php foreach($numberAddconditions as $fieldNr): ?>
	jQuery('#adminForm').on('change', '#jform_addconditions__addconditions<?php echo $fieldNr ?>__match_field',function (e) {
		e.preventDefault();
		getFieldSelectOptions(<?php echo $fieldNr ?>);
	});
<?php endforeach; ?>
	jQuery(document).on('subform-row-add', function(event, row){
		var groupName = jQuery(row).data('group');
		var fieldName = groupName.replace(/([0-9])/g, '');
		var fieldNr = groupName.replace(/([A-z_])/g, '');
		if ('addconditions' === fieldName) {
			jQuery('#adminForm').on('change', '#jform_addconditions__addconditions'+fieldNr+'__match_field',function (e) {
				e.preventDefault();
				getFieldSelectOptions(fieldNr);
			});
		}
	});
});

<?php
	$app = Factory::getApplication();
?>
function JRouter(link) {
<?php
	if ($app->isClient('site'))
	{
		echo 'var url = "'. Uri::root() . '";';
	}
	else
	{
		echo 'var url = "";';
	}
?>
	return url+link;
}
</script>
