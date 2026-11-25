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
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=custom_admin_view&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('custom_admin_view.details_above', $this); ?>
<div class="main-card">
<div class="row">
	<div class="col-md-9">

	<?php echo Html::_('uitab.startTabSet', 'custom_admin_viewTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'custom_admin_viewTab', 'details', Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('custom_admin_view.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('custom_admin_view.details_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('custom_admin_view.details_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'custom_admin_viewTab', 'toolbar', Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW_TOOLBAR', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('custom_admin_view.toolbar_left', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('custom_admin_view.toolbar_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'custom_admin_viewTab', 'javascript_css', Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW_JAVASCRIPT_CSS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('custom_admin_view.javascript_css_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'custom_admin_viewTab', 'php', Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW_PHP', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('custom_admin_view.php_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'custom_admin_viewTab', 'linked_components', Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW_LINKED_COMPONENTS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('custom_admin_view.linked_components_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'custom_admin_viewTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('core.edit.created_by') || $this->canDo->get('core.edit.created') || $this->canDo->get('core.edit.state') || ($this->canDo->get('core.delete') && $this->canDo->get('core.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'custom_admin_viewTab', 'publishing', Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('custom_admin_view.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('custom_admin_view.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'custom_admin_viewTab', 'permissions', Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_COMPONENTBUILDER_CUSTOM_ADMIN_VIEW_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="custom_admin_view.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
	</div>
	<div class="col-md-3"><div class="m-md-3">
		<?php echo LayoutHelper::render('custom_admin_view.details_rightside', $this); ?>
	</div></div>
</div>
</div>

<div class="clearfix"></div>
<?php echo LayoutHelper::render('custom_admin_view.details_under', $this); ?>
</form>
</div>

<script type="text/javascript">
jQuery(function () {

jQuery('#jform_add_php_view').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_view', updateCustomAdminViewConditions);

jQuery('#jform_add_php_jview_display').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_jview_display', updateCustomAdminViewConditions);

jQuery('#jform_add_php_jview').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_jview', updateCustomAdminViewConditions);

jQuery('#jform_add_php_document').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_document', updateCustomAdminViewConditions);

jQuery('#jform_add_css_document').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_css_document', updateCustomAdminViewConditions);

jQuery('#jform_add_javascript_file').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_javascript_file', updateCustomAdminViewConditions);

jQuery('#jform_add_js_document').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_js_document', updateCustomAdminViewConditions);

jQuery('#jform_add_custom_button').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_custom_button', updateCustomAdminViewConditions);

jQuery('#jform_add_css').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_css', updateCustomAdminViewConditions);

jQuery('#jform_add_php_ajax').on('keyup', updateCustomAdminViewConditions);
jQuery('#adminForm').on('change', '#jform_add_php_ajax', updateCustomAdminViewConditions);

});


document.addEventListener("DOMContentLoaded", function() {
    document.querySelector('#open-libraries').innerHTML = '<a href="index.php?option=com_componentbuilder&view=libraries"><?php echo Text::_('COM_COMPONENTBUILDER_LIBRARIES'); ?></a>';
});

jQuery('#jform_snippet').closest('.input-append').addClass('jform_snippet_input_width');
jQuery('#jform_main_get').closest('.input-append').addClass('jform_main_get_input_width');
jQuery('#jform_dynamic_get').closest('.input-append').addClass('jform_dynamic_get_input_width');
jQuery(function() {
	// make sure the code bocks are active
	document.querySelectorAll("code").forEach(function(codeBlock) {
		codeBlock.addEventListener("click", function() {
			codeBlock.selText(); // Call the custom selText function
			codeBlock.classList.add("selected"); // Add the "selected" class
		});
	});
});
document.addEventListener("DOMContentLoaded", function () {
	// Attach change event listeners to form elements
	document.querySelector("#adminForm")?.addEventListener("change", function (event) {
		const target = event.target;

		if (target.matches("#jform_libraries")) {
			event.preventDefault();
			getSnippets();
		} else if (target.matches("#jform_snippet")) {
			event.preventDefault();
			const snippetId = target.value;
			if (snippetId) {
				getSnippetDetails(snippetId);
			}
		} else if (target.matches("#jform_dynamic_get")) {
			event.preventDefault();
			const dynamicId = target.value;
			if (dynamicId) {
				getDynamicValues(dynamicId);
			}
		}
	});

	const dynamicElement = document.querySelector("#jform_dynamic_get");
	const dynamicElementValue = dynamicElement.value;
	if (dynamicElement && dynamicElementValue) {
		getDynamicValues(dynamicElementValue);
	}

	getLayoutDetails(9999);
	getTemplateDetails(9999);
});
// some lang strings
var select_a_snippet = '<?php echo Text::_('COM_COMPONENTBUILDER_SELECT_A_SNIPPET'); ?>';
var create_a_snippet = '<?php echo Text::_('COM_COMPONENTBUILDER_CREATE_A_SNIPPET'); ?>';


/**
 * Select all text content within an HTMLElement.
 *
 * Adds a convenient `selText()` method to all HTMLElements.
 * Works across modern browsers and gracefully handles errors.
 *
 * @return {HTMLElement}  Returns the element itself for chaining.
 * @since  5.1.3
 */
HTMLElement.prototype.selText = function () {
	try {
		const selection = window.getSelection();
		if (!selection) {
			console.warn('selText: window.getSelection() not supported in this environment.');
			return this;
		}

		const range = document.createRange();
		range.selectNodeContents(this);

		selection.removeAllRanges(); // clear any prior selections
		selection.addRange(range);   // select the element's text content

		// Optionally bring the element into view if it's outside viewport
		if (typeof this.scrollIntoView === 'function') {
			this.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	} catch (error) {
		console.error('selText failed:', error);
	}

	return this;
};

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
