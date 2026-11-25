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
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=custom_code&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('custom_code.details_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'custom_codeTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'custom_codeTab', 'details', Text::_('COM_COMPONENTBUILDER_CUSTOM_CODE_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('custom_code.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('custom_code.details_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('custom_code.details_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'custom_codeTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('custom_code.edit.created_by') || $this->canDo->get('custom_code.edit.created') || $this->canDo->get('custom_code.edit.state') || ($this->canDo->get('custom_code.delete') && $this->canDo->get('custom_code.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'custom_codeTab', 'publishing', Text::_('COM_COMPONENTBUILDER_CUSTOM_CODE_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('custom_code.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('custom_code.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'custom_codeTab', 'permissions', Text::_('COM_COMPONENTBUILDER_CUSTOM_CODE_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_COMPONENTBUILDER_CUSTOM_CODE_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="custom_code.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>

<div class="clearfix"></div>
<?php echo LayoutHelper::render('custom_code.details_under', $this); ?>
</form>
</div>

<script type="text/javascript">
jQuery(function () {

jQuery('#jform_target').on('keyup', updateCustomCodeConditions);
jQuery('#adminForm').on('change', '#jform_target', updateCustomCodeConditions);

jQuery('#jform_type').on('keyup', updateCustomCodeConditions);
jQuery('#adminForm').on('change', '#jform_type', updateCustomCodeConditions);

});


document.getElementById('adminForm').addEventListener('change', function (e) {
	var el = e.target;

	if (el.closest('#jform_function_name')) {
		var target = document.querySelector('#jform_target input[type="radio"]:checked');
		if (target && target.value == 2) {
			document.getElementById('usedin').style.display = '';
			var functioName = document.getElementById('jform_function_name').value;
			// check if this function name is taken
			checkFunctionName(functioName);
		} else {
			document.getElementById('usedin').style.display = 'none';
		}
	}

	if (el.closest('#jform_target')) {
		var target = document.querySelector('#jform_target input[type="radio"]:checked');
		if (target && target.value == 2) {
			document.getElementById('usedin').style.display = '';
			var functioName = document.getElementById('jform_function_name').value;
			// check if this function name is taken
			checkFunctionName(functioName);
		} else {
			document.getElementById('usedin').style.display = 'none';
		}
	}

	if (el.closest('#jform_comment_type')) {
		var type = document.querySelector('#jform_comment_type input[type="radio"]:checked');
		if (type && type.value == 2) {
			document.getElementById('html-comment-info').style.display = '';
			document.getElementById('phpjs-comment-info').style.display = 'none';
		} else {
			document.getElementById('html-comment-info').style.display = 'none';
			document.getElementById('phpjs-comment-info').style.display = '';
		}
	}
});

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
</script>
