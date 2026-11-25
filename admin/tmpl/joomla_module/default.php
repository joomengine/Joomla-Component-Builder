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
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=joomla_module&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('joomla_module.html_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'joomla_moduleTab', ['active' => 'html', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'html', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_HTML', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_module.html_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_module.html_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_module.html_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'code', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_CODE', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_module.code_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_module.code_right', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'helper', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_HELPER', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_module.helper_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_module.helper_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_module.helper_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'forms_fields', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_FORMS_FIELDS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_module.forms_fields_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'script_file', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_SCRIPT_FILE', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_module.script_file_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'mysql', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_MYSQL', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_module.mysql_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'readme', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_README', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_module.readme_left', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'dynamic_integration', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_DYNAMIC_INTEGRATION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('joomla_module.dynamic_integration_left', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'joomla_moduleTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('joomla_module.edit.created_by') || $this->canDo->get('joomla_module.edit.created') || $this->canDo->get('joomla_module.edit.state') || ($this->canDo->get('joomla_module.delete') && $this->canDo->get('joomla_module.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'publishing', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_module.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla_module.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'joomla_moduleTab', 'permissions', Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_COMPONENTBUILDER_JOOMLA_MODULE_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="joomla_module.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>
</form>
</div>

<script type="text/javascript">
jQuery(function () {

jQuery('#jform_add_class_helper').on('keyup', updateJoomlaModuleConditions);
jQuery('#adminForm').on('change', '#jform_add_class_helper', updateJoomlaModuleConditions);

jQuery('#jform_add_class_helper_header').on('keyup', updateJoomlaModuleConditions);
jQuery('#adminForm').on('change', '#jform_add_class_helper_header', updateJoomlaModuleConditions);

jQuery('#jform_update_server_target').on('keyup', updateJoomlaModuleConditions);
jQuery('#adminForm').on('change', '#jform_update_server_target', updateJoomlaModuleConditions);

jQuery('#jform_add_update_server').on('keyup', updateJoomlaModuleConditions);
jQuery('#adminForm').on('change', '#jform_add_update_server', updateJoomlaModuleConditions);

});


jQuery('#jform_snippet').closest('.input-append').addClass('jform_snippet_input_width');
jQuery(function() {
	// make sure the code bocks are active
	document.querySelectorAll("code").forEach(function(codeBlock) {
		codeBlock.addEventListener("click", function() {
			codeBlock.selText(); // Call the custom selText function
			codeBlock.classList.add("selected"); // Add the "selected" class
		});
	});
});
jQuery('#adminForm').on('change', '#jform_custom_get',function (e) {
	e.preventDefault();
	// load the dynamic get include with the needed initiation
	setModuleCode();
});
jQuery('#adminForm').on('change', '#jform_add_class_helper',function (e) {
	e.preventDefault();
	// load the dynamic helper include with the needed initiation
	setModuleCode();
});
jQuery('#adminForm').on('change', '#jform_libraries',function (e) {
	e.preventDefault();
	// get the snippets of the selected libraries
	getSnippets();
	// load the dynamic media placeholders if needed
	setModuleCode();
});

jQuery('#adminForm').on('change', '#jform_snippet',function (e) {
	e.preventDefault();
	// get type value
	var snippetId = jQuery("#jform_snippet option:selected").val();
	getSnippetDetails(snippetId);
});

jQuery(document).ready(function() {
	// get type value
	var snippetId = jQuery("#jform_snippet option:selected").val();
	getSnippetDetails(snippetId);
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
</script>
