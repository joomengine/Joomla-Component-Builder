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
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=fieldtype&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('fieldtype.details_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'fieldtypeTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'fieldtypeTab', 'details', Text::_('COM_COMPONENTBUILDER_FIELDTYPE_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('fieldtype.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('fieldtype.details_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('fieldtype.details_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'fieldtypeTab', 'database_defaults', Text::_('COM_COMPONENTBUILDER_FIELDTYPE_DATABASE_DEFAULTS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('fieldtype.database_defaults_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('fieldtype.database_defaults_right', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'fieldtypeTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('core.edit.created_by') || $this->canDo->get('core.edit.created') || $this->canDo->get('fieldtype.edit.state') || ($this->canDo->get('fieldtype.delete') && $this->canDo->get('fieldtype.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'fieldtypeTab', 'publishing', Text::_('COM_COMPONENTBUILDER_FIELDTYPE_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('fieldtype.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('fieldtype.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'fieldtypeTab', 'permissions', Text::_('COM_COMPONENTBUILDER_FIELDTYPE_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_COMPONENTBUILDER_FIELDTYPE_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="fieldtype.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>
</form>
</div>

<script type="text/javascript">

jQuery('#jform_datalenght').on('keyup', updateFieldtypeConditions);
jQuery('#adminForm').on('change', '#jform_datalenght', updateFieldtypeConditions);

jQuery('#jform_has_defaults').on('keyup', updateFieldtypeConditions);
jQuery('#adminForm').on('change', '#jform_has_defaults', updateFieldtypeConditions);

jQuery('#jform_datadefault').on('keyup', updateFieldtypeConditions);
jQuery('#adminForm').on('change', '#jform_datadefault', updateFieldtypeConditions);

jQuery('#jform_datatype').on('keyup', updateFieldtypeConditions);
jQuery('#adminForm').on('change', '#jform_datatype', updateFieldtypeConditions);

jQuery('#jform_store').on('keyup', updateFieldtypeConditions);
jQuery('#adminForm').on('change', '#jform_store', updateFieldtypeConditions);




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
