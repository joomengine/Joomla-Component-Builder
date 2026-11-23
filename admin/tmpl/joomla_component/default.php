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
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=joomla_component&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('joomla_component.details_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'joomla_componentTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'details', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.details_right', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'settings', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_SETTINGS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.settings_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.settings_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_component.settings_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'admin_views', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_ADMIN_VIEWS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_component.admin_views_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'site_views', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_SITE_VIEWS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_component.site_views_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'custom_admin_views', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_CUSTOM_ADMIN_VIEWS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_component.custom_admin_views_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'libs_helpers', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_LIBS_HELPERS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_component.libs_helpers_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'dash_install', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_DASH_INSTALL', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.dash_install_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.dash_install_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_component.dash_install_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'mysql', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_MYSQL', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_component.mysql_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'readme', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_README', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.readme_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.readme_right', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'dynamic_integration', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_DYNAMIC_INTEGRATION', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.dynamic_integration_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.dynamic_integration_right', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'dynamic_build', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_DYNAMIC_BUILD', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_component.dynamic_build_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'joomla_componentTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('joomla_component.edit.created_by') || $this->canDo->get('joomla_component.edit.created') || $this->canDo->get('joomla_component.edit.state') || ($this->canDo->get('joomla_component.delete') && $this->canDo->get('joomla_component.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'publishing', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_component.metadata', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'joomla_componentTab', 'permissions', Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="joomla_component.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>

<div class="clearfix"></div>
<?php echo LayoutHelper::render('joomla_component.details_under', $this); ?>
</form>
</div>

<script type="text/javascript">

jQuery('#jform_emptycontributors').on('keyup', updateJoomlaComponentConditions);
jQuery('#adminForm').on('change', '#jform_emptycontributors', updateJoomlaComponentConditions);

jQuery('#jform_update_server_target').on('keyup', updateJoomlaComponentConditions);
jQuery('#adminForm').on('change', '#jform_update_server_target', updateJoomlaComponentConditions);

jQuery('#jform_add_update_server').on('keyup', updateJoomlaComponentConditions);
jQuery('#adminForm').on('change', '#jform_add_update_server', updateJoomlaComponentConditions);

jQuery('#jform_changelog_server_target').on('keyup', updateJoomlaComponentConditions);
jQuery('#adminForm').on('change', '#jform_changelog_server_target', updateJoomlaComponentConditions);

jQuery('#jform_add_changelog_server').on('keyup', updateJoomlaComponentConditions);
jQuery('#adminForm').on('change', '#jform_add_changelog_server', updateJoomlaComponentConditions);

jQuery('#jform_buildcomp').on('keyup', updateJoomlaComponentConditions);
jQuery('#adminForm').on('change', '#jform_buildcomp', updateJoomlaComponentConditions);

jQuery('#jform_dashboard_type').on('keyup', updateJoomlaComponentConditions);
jQuery('#adminForm').on('change', '#jform_dashboard_type', updateJoomlaComponentConditions);

jQuery('#jform_translation_tool').on('keyup', updateJoomlaComponentConditions);
jQuery('#adminForm').on('change', '#jform_translation_tool', updateJoomlaComponentConditions);




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

document.addEventListener("DOMContentLoaded", function() {
	document.querySelectorAll(".loading-dots").forEach(function(loading_dots) {
		let x = 0;
		let intervalId = setInterval(function() {
			if (!loading_dots.classList.contains("loading-dots")) {
				clearInterval(intervalId);
				return;
			}
			let dots = ".".repeat(x % 8);
			loading_dots.textContent = dots;
			x++;
		}, 500);
	});
});
// check when dashboard switch changes
jQuery('#adminForm').on('change', '#jform_dashboard_type',function (e)
{
	e.preventDefault();
	var dasboard_type = jQuery("#jform_dashboard_type input[type='radio']:checked").val();
	dasboardSwitch(dasboard_type);
});
</script>
