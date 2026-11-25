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
<?php
$jcbLoadingOverlay = '(function () {'
	. PHP_EOL . '	const initialise = function () {'
	. PHP_EOL . '		if (document.readyState === \'complete\') {'
	. PHP_EOL . '			const container = document.getElementById("componentbuilder_loader");'
	. PHP_EOL . '			if (container) {'
	. PHP_EOL . '				container.style.display = \'block\';'
	. PHP_EOL . '			}'
	. PHP_EOL . '			return;'
	. PHP_EOL . '		}'
	. PHP_EOL . '		(function() {'
	. PHP_EOL . '			// create loading overlay'
	. PHP_EOL . '			var loadingDiv = document.createElement(\'div\');'
	. PHP_EOL . '			loadingDiv.id = \'loading\';'
	. PHP_EOL . '			loadingDiv.style.position = \'fixed\';'
	. PHP_EOL . '			loadingDiv.style.top = \'0\';'
	. PHP_EOL . '			loadingDiv.style.left = \'0\';'
	. PHP_EOL . '			loadingDiv.style.right = \'0\';'
	. PHP_EOL . '			loadingDiv.style.bottom = \'0\';'
	. PHP_EOL . '			loadingDiv.style.width = \'100%\';'
	. PHP_EOL . '			loadingDiv.style.height = \'100%\';'
	. PHP_EOL . '			loadingDiv.style.background = "rgba(255,255,255,0.8) url(\'components/com_componentbuilder/assets/images/ajax.gif\') 50% 35% no-repeat";'
	. PHP_EOL . '			loadingDiv.style.opacity = \'0.8\';'
	. PHP_EOL . '			loadingDiv.style.zIndex = \'9999\';'
	. PHP_EOL . '			loadingDiv.style.display = \'block\';'
	. PHP_EOL . '			loadingDiv.style.msFilter = "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";'
	. PHP_EOL . '			loadingDiv.style.filter = "alpha(opacity=80)";'
	. PHP_EOL . '			document.body.appendChild(loadingDiv);'
	. PHP_EOL . '			// remove overlay when page fully loaded'
	. PHP_EOL . '			window.addEventListener(\'load\', function() {'
	. PHP_EOL . '				var componentLoader = document.getElementById(\'componentbuilder_loader\');'
	. PHP_EOL . '				if (componentLoader)'
	. PHP_EOL . '				{'
	. PHP_EOL . '					componentLoader.style.display = \'block\';'
	. PHP_EOL . '				}'
	. PHP_EOL . '				loadingDiv.style.display = \'none\';'
	. PHP_EOL . '			});'
	. PHP_EOL . '		})();'
	. PHP_EOL . '	};'
	. PHP_EOL . '	if (document.readyState === \'loading\') {'
	. PHP_EOL . '		document.addEventListener(\'DOMContentLoaded\', initialise, {once: true});'
	. PHP_EOL . '	} else {'
	. PHP_EOL . '		initialise();'
	. PHP_EOL . '	}'
	. PHP_EOL . '})();';
$this->getDocument()->getWebAssetManager()->addInlineScript(
	$jcbLoadingOverlay, ['name' => 'com_componentbuilder.loading-overlay'], [], ['core']
);
?>
<div id="componentbuilder_loader" style="display: none;">
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=admin_view&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('admin_view.details_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'admin_viewTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'details', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.details_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.details_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'settings', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_SETTINGS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.settings_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'fields', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_FIELDS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.fields_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.fields_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.fields_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'css', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_CSS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.css_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'javascript', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_JAVASCRIPT', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.javascript_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'toolbar', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_TOOLBAR', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.toolbar_left', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.toolbar_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'php', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_PHP', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.php_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'mysql', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_MYSQL', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.mysql_left', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.mysql_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'admin_viewTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('admin_view.edit.created_by') || $this->canDo->get('admin_view.edit.created') || $this->canDo->get('admin_view.edit.state') || ($this->canDo->get('admin_view.delete') && $this->canDo->get('admin_view.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'publishing', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'permissions', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="admin_view.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>

<div class="clearfix"></div>
<?php echo LayoutHelper::render('admin_view.details_under', $this); ?>
</form>
</div>

<script type="text/javascript">
jQuery(function () {

jQuery('#jform_add_css_view').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_css_view', updateAdminViewConditions);

jQuery('#jform_add_css_views').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_css_views', updateAdminViewConditions);

jQuery('#jform_add_javascript_view_file').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_javascript_view_file', updateAdminViewConditions);

jQuery('#jform_add_javascript_views_file').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_javascript_views_file', updateAdminViewConditions);

jQuery('#jform_add_javascript_view_footer').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_javascript_view_footer', updateAdminViewConditions);

jQuery('#jform_add_javascript_views_footer').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_javascript_views_footer', updateAdminViewConditions);

jQuery('#jform_add_php_ajax').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_ajax', updateAdminViewConditions);

jQuery('#jform_add_php_getitem').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_getitem', updateAdminViewConditions);

jQuery('#jform_add_php_getitems').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_getitems', updateAdminViewConditions);

jQuery('#jform_add_php_getitems_after_all').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_getitems_after_all', updateAdminViewConditions);

jQuery('#jform_add_php_getlistquery').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_getlistquery', updateAdminViewConditions);

jQuery('#jform_add_php_getform').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_getform', updateAdminViewConditions);

jQuery('#jform_add_php_before_save').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_before_save', updateAdminViewConditions);

jQuery('#jform_add_php_save').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_save', updateAdminViewConditions);

jQuery('#jform_add_php_postsavehook').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_postsavehook', updateAdminViewConditions);

jQuery('#jform_add_php_allowadd').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_allowadd', updateAdminViewConditions);

jQuery('#jform_add_php_allowedit').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_allowedit', updateAdminViewConditions);

jQuery('#jform_add_php_before_cancel').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_before_cancel', updateAdminViewConditions);

jQuery('#jform_add_php_after_cancel').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_after_cancel', updateAdminViewConditions);

jQuery('#jform_add_php_batchcopy').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_batchcopy', updateAdminViewConditions);

jQuery('#jform_add_php_batchmove').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_batchmove', updateAdminViewConditions);

jQuery('#jform_add_php_before_publish').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_before_publish', updateAdminViewConditions);

jQuery('#jform_add_php_after_publish').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_after_publish', updateAdminViewConditions);

jQuery('#jform_add_php_before_delete').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_before_delete', updateAdminViewConditions);

jQuery('#jform_add_php_after_delete').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_after_delete', updateAdminViewConditions);

jQuery('#jform_add_php_document').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_document', updateAdminViewConditions);

jQuery('#jform_add_sql').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_sql', updateAdminViewConditions);

jQuery('#jform_source').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_source', updateAdminViewConditions);

jQuery('#jform_add_custom_button').on('keyup', updateAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_custom_button', updateAdminViewConditions);

});



document.addEventListener('DOMContentLoaded', () => {
	const adminForm = document.getElementById('adminForm');

	if (!adminForm) {
		return;
	}

	adminForm.addEventListener('change', (event) => {
		const target = event.target;

		if (!(target instanceof Element)) {
			return;
		}

		const id = target.id || '';

		// Existing preloaded fields:
		// jform_addtables__addtables0__table
		let match = id.match(/^jform_addtables__addtables(\d+)__table$/);

		if (match) {
			getTableColumns(match[1], '_', '_');
			return;
		}

		// Dynamically added subform rows:
		// jform_addtables_addtables3_table
		match = id.match(/^jform_addtables_addtables(\d+)_table$/);

		if (match) {
			getTableColumns(match[1], '', '');
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
jQuery(document).ready(function(){
	jQuery(document).on('subform-row-add', function(event, row){
		getIconImage(jQuery(row).find('.icomoon342'));
	});
});

function getIconImage(field) {
	// get the ID
	var id = jQuery(field).attr('id');
	// remove old one 
	jQuery('#image_'+id).remove();
	// get value
	var value = jQuery('#'+id).val();
	// build new one
	var span = '<span id="image_'+id+'" class="icon-'+value+'" style="position: absolute; top: 8px; right: -20px;"></span>';
	// add the icon
	jQuery('#'+id+'_chzn').append(span);
}
</script>
