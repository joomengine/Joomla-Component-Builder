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
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=field&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('field.set_properties_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'fieldTab', ['active' => 'set_properties', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'fieldTab', 'set_properties', Text::_('COM_COMPONENTBUILDER_FIELD_SET_PROPERTIES', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('field.set_properties_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'fieldTab', 'database', Text::_('COM_COMPONENTBUILDER_FIELD_DATABASE', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('field.database_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('field.database_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('field.database_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'fieldTab', 'scripts', Text::_('COM_COMPONENTBUILDER_FIELD_SCRIPTS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('field.scripts_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('field.scripts_right', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'fieldTab', 'type_info', Text::_('COM_COMPONENTBUILDER_FIELD_TYPE_INFO', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('field.type_info_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'fieldTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('core.edit.created_by') || $this->canDo->get('core.edit.created') || $this->canDo->get('field.edit.state') || ($this->canDo->get('field.delete') && $this->canDo->get('field.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'fieldTab', 'publishing', Text::_('COM_COMPONENTBUILDER_FIELD_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('field.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('field.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'fieldTab', 'permissions', Text::_('COM_COMPONENTBUILDER_FIELD_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_COMPONENTBUILDER_FIELD_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="field.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>

<div class="clearfix"></div>
<?php echo LayoutHelper::render('field.set_properties_under', $this); ?>
</form>
</div>

<script type="text/javascript">
jQuery(function () {

jQuery('#jform_datalenght').on('keyup', updateFieldConditions);
jQuery('#adminForm').on('change', '#jform_datalenght', updateFieldConditions);

jQuery('#jform_datadefault').on('keyup', updateFieldConditions);
jQuery('#adminForm').on('change', '#jform_datadefault', updateFieldConditions);

jQuery('#jform_datatype').on('keyup', updateFieldConditions);
jQuery('#adminForm').on('change', '#jform_datatype', updateFieldConditions);

jQuery('#jform_store').on('keyup', updateFieldConditions);
jQuery('#adminForm').on('change', '#jform_store', updateFieldConditions);

jQuery('#jform_add_css_view').on('keyup', updateFieldConditions);
jQuery('#adminForm').on('change', '#jform_add_css_view', updateFieldConditions);

jQuery('#jform_add_css_views').on('keyup', updateFieldConditions);
jQuery('#adminForm').on('change', '#jform_add_css_views', updateFieldConditions);

jQuery('#jform_add_javascript_view_footer').on('keyup', updateFieldConditions);
jQuery('#adminForm').on('change', '#jform_add_javascript_view_footer', updateFieldConditions);

jQuery('#jform_add_javascript_views_footer').on('keyup', updateFieldConditions);
jQuery('#adminForm').on('change', '#jform_add_javascript_views_footer', updateFieldConditions);

jQuery('#jform_fieldtype').on('keyup', updateFieldConditions);
jQuery('#adminForm').on('change', '#jform_fieldtype', updateFieldConditions);

});



/**
 * Initialize form listeners and code block click handlers.
 *
 * Fully replaces the original jQuery logic in pure JavaScript.
 *
 * @since 5.1.3
 */
document.addEventListener('DOMContentLoaded', function () {
	setTimeout(function () {
		document.querySelectorAll('code').forEach(function (codeBlock) {
			codeBlock.addEventListener('click', function () {
				codeBlock.selText();
				codeBlock.classList.add('selected');
			});
		});
	}, 2000);

	const adminForm = document.getElementById('adminForm');
	if (adminForm) {
		adminForm.addEventListener('change', function (e) {
			if (e.target && e.target.id === 'jform_fieldtype') {
				e.preventDefault();

				// Get selected option
				const select = document.getElementById('jform_fieldtype');
				const selected = select.options[select.selectedIndex];
				if (!selected) return;

				const fieldId = selected.value;
				const fieldText = selected.textContent.trim().toLowerCase();

				// Run your existing functions (kept identical)
				getFieldTypeProperties(fieldId, true);
				dbChecker(fieldText);
			}
		});
	}
});

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
