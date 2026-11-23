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
use Joomla\CMS\Session\Session;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Uri\Uri;

// No direct access to this file
defined('_JEXEC') or die;

$this->app->getInput()->set('hidemainmenu', false);
$selectNotice = '<h3>' . Text::_('COM_COMPONENTBUILDER_HI') . ' ' . $this->user->name . '</h3>';
$selectNotice .= '<p>' . Text::_('COM_COMPONENTBUILDER_PLEASE_SELECT_A_COMPONENT_THAT_YOU_WOULD_LIKE_TO_COMPILE') . '</p>';

// set the noticeboard options
$noticeboardOptions = ['vdm', 'pro'];

?>
<?php if ($this->canDo->get('compiler.access')): ?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'compiler.back') {
			parent.history.back();
			return false;
		} else {
			var form = document.getElementById('adminForm');
			form.task.value = task;
			form.submit();
		}
	}
</script>

<script type="text/javascript">
/**
 * Enhanced Joomla submit button handler for the compiler interface.
 *
 * Handles form submission tasks safely with DOM validation,
 * smooth UI transitions, and contextual loading states.
 *
 * @param {string} task  The Joomla task to perform.
 * @param {string} [key] Optional key for install-related tasks.
 *
 * @return {boolean} Returns true if the form submission proceeds, false otherwise.
 * @since 5.1.3
 */
Joomla.submitbutton = function (task, key = '') {
	// Abort early if task is not provided
	if (!task) {
		console.warn('Joomla.submitbutton: No task provided.');
		return false;
	}

	const form = document.getElementById('adminForm');
	if (!form) {
		console.error('Joomla.submitbutton: #adminForm not found.');
		return false;
	}

	const componentField = document.getElementById('component_id');
	const component = componentField ? componentField.value.trim() : '';
	const isCompilerTask = task === 'compiler.compiler';
	let isValid = true;

	// Validate component selection for compilation
	if (!component && isCompilerTask) {
		isValid = false;
	}

	if (!isValid) {
		document.querySelectorAll('.notice').forEach(elem => {
			elem.style.display = 'block';
		});
		console.warn('Joomla.submitbutton: Component not selected.');
		return false;
	}

	// Hide form while processing
	const formContainer = document.getElementById('form');
	if (formContainer) {
		formContainer.style.display = 'none';
	}

	// Handle install tasks with key assignment
	if (task === 'compiler.installCompiledModule' || task === 'compiler.installCompiledPlugin') {
		if (form.install_item_id) {
			form.install_item_id.value = key;
		} else {
			console.warn('Joomla.submitbutton: install_item_id field not found.');
		}
	}

	// Set the task and safely submit
	form.task.value = task;
	setTimeout(() => form.submit(), 100);

	// UI update helper
	const show = (id, delay = 0) => {
		setTimeout(() => {
			const el = document.getElementById(id);
			if (el) el.style.display = 'block';
		}, delay);
	};

	// Manage contextual UI loading animations
	switch (task) {
		case 'compiler.compiler': {
			const selectedOption = document.querySelector('#component_id option:checked');
			const componentName = selectedOption ? selectedOption.textContent.trim() : '';

			document.querySelectorAll('.component-name').forEach(elem => {
				elem.textContent = componentName;
			});

			show('compiler', 100);
			show('compiling', 100);
			show('compiler-spinner', 200);
			show('compiler-notice', 200);
			break;
		}

		case 'compiler.clearTmp':
			show('clear');
			show('loading');
			break;

		case 'compiler.getCompilerAnimations':
			show('get-compiler-animations');
			show('loading');
			break;

		default:
			show('loading');
			break;
	}

	return true;
};
// Add spindle-wheel for importations:
document.addEventListener('DOMContentLoaded', function() {

	// get page body
	var outerBodyDiv = document.body || document.querySelector('body');

	// start loading spinner
	var loadingDiv = document.createElement('div');
	loadingDiv.id = 'loading';

	// Set CSS properties (robust, no calculations)
	loadingDiv.style.position = 'fixed';
	loadingDiv.style.top = '0';
	loadingDiv.style.left = '0';
	loadingDiv.style.right = '0';
	loadingDiv.style.bottom = '0';
	loadingDiv.style.width = '100%';
	loadingDiv.style.height = '100%';

	loadingDiv.style.background = "rgba(255, 255, 255, .8) url('components/com_componentbuilder/assets/images/ajax.gif') 50% 35% no-repeat";
	loadingDiv.style.opacity = '0.8';
	loadingDiv.style.zIndex = '9999';
	loadingDiv.style.display = 'none';

	// legacy IE fallback (safe to keep)
	loadingDiv.style.msFilter = "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
	loadingDiv.style.filter = "alpha(opacity=80)";

	// add to page body
	outerBodyDiv.appendChild(loadingDiv);

	// waiting compiler overlay
	var compilingDiv = document.createElement('div');
	compilingDiv.id = 'compiling';

	// Set CSS properties individually
	compilingDiv.style.background = "rgba(16, 164, 230, .4)";
	compilingDiv.style.top = (outerBodyDiv.getBoundingClientRect().top + window.pageYOffset) + "px";
	compilingDiv.style.left = (outerBodyDiv.getBoundingClientRect().left + window.pageXOffset) + "px";
	compilingDiv.style.width = outerBodyDiv.offsetWidth + "px";
	compilingDiv.style.height = outerBodyDiv.offsetHeight + "px";
	compilingDiv.style.position = 'fixed';
	compilingDiv.style.opacity = '0.40';
	compilingDiv.style.msFilter = "progid:DXImageTransform.Microsoft.Alpha(Opacity=40)";
	compilingDiv.style.filter = "alpha(opacity=40)";
	compilingDiv.style.display = 'none';

	// add to page
	outerBodyDiv.appendChild(compilingDiv);
});
</script>
<?php if (empty($this->Components)): ?>
	<?php echo $this->loadTemplate('nocomponentstocompile'); ?>
	<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=compiler'); ?>"
		method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
		<input type="hidden" name="task" value="" />
		<?php echo Html::_('form.token'); ?>
	</form>
<?php else: ?>
<div class="main-card p-md-3">
	<?php if (StringHelper::check($this->SuccessMessage)): ?>
		<div class="alert alert-success">
		<button type="button" class="close" data-dismiss="alert">×</button>
			<?php echo $this->SuccessMessage; ?>
		</div>
	<?php endif; ?>
	<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=compiler'); ?>"
		method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
		<div id="form" class="row">
			<div class="col-md-4 p-md-3">
				<h3><?php echo Text::_('COM_COMPONENTBUILDER_READY_TO_COMPILE_A_COMPONENT'); ?></h3>
				<div id="compilerForm">
					<div class="row">
						<span class="notice" style="display:none; color:red;"><?php echo Text::_('COM_COMPONENTBUILDER_YOU_MUST_SELECT_A_COMPONENT'); ?></span><br />
						<?php if ($this->form): ?>
							<?php echo $this->form->renderFieldset('builder'); ?>
						<?php endif; ?>
					</div>
					<button type="button" class="btn btn-primary btn-lg px-4 me-sm-3" style="width: 100%;" onclick="Joomla.submitbutton('compiler.compiler')"><span class="icon-cog icon-white"></span>
						<?php echo Text::_('COM_COMPONENTBUILDER_COMPILE_COMPONENT'); ?>
					</button>
					<input type="hidden" name="install_item_id" value="0"> 
					<input type="hidden" name="version" value="3" />
				</div>
			</div>
			<div class="col-md-8 p-md-3">
				<div id="advance-details"><?php echo $this->form->renderFieldset('advanced'); ?></div>
				<div id="component-details"><?php echo $selectNotice; ?></div>
				<?php echo LayoutHelper::render('jcbnoticeboard', ['dankie' => $this->dankie]); ?>
			</div>
		</div>
		<div id="get-compiler-animations" style="display:none;" class="row p-md-3">
			<h1><?php echo Text::_('COM_COMPONENTBUILDER_PLEASE_WAIT'); ?></h1>
			<h4><?php echo Text::_('COM_COMPONENTBUILDER_WHILE_WE_DOWNLOAD_ALL_TWENTY_SIX_COMPILER_GIF_ANIMATIONS_RANDOMLY_USED_IN_THE_COMPILER_GUI_DURING_COMPILATION'); ?> <span class="loading-dots">.</span></h4>
		</div>
		<div id="clear" style="display:none;" class="row p-md-3">
			<h1><?php echo Text::_('COM_COMPONENTBUILDER_PLEASE_WAIT'); ?></h1>
			<h4><?php echo Text::_('COM_COMPONENTBUILDER_REMOVING_ALL_ZIP_PACKAGES_FROM_THE_TEMPORARY_FOLDER_OF_THE_JOOMLA_INSTALL'); ?> <span class="loading-dots">.</span></h4>
		</div>
		<div id="compiler" style="display:none;">
			<div class="row">
				<div id="compiler-spinner" class="col-md-4 p-md-3" style="display:none;">
					<h3><?php echo Text::sprintf('COM_COMPONENTBUILDER_S_PLEASE_WAIT', $this->user->name); ?></h3>
					<p style="font-size: smaller;"><?php echo Text::_('COM_COMPONENTBUILDER_THIS_MAY_TAKE_A_WHILE_DEPENDING_ON_THE_SIZE_OF_YOUR_PROJECT'); ?></p>
					<p><b><span class="component-name"><?php echo Text::_('COM_COMPONENTBUILDER_THE_COMPONENT'); ?></span></b> <?php echo Text::_('COM_COMPONENTBUILDER_IS_BEING_COMPILED'); ?> <span class="loading-dots">.</span></p>
					<p style="font-size: smaller;"><?php echo Text::_('COM_COMPONENTBUILDER_DURING_THE_INITIAL_COMPILATION_OF_ANY_COMPONENT_THE_PROCESS_MAY_TAKE_ADDITIONAL_TIME_AS_WE_RETRIEVE_AND_CONFIGURE_THE_ASSOCIATED_SUPERPOWERS_'); ?></p>
					<div style="text-align: center;"><?php echo ComponentbuilderHelper::getDynamicContent('builder-gif', $this->builder_gif_size); ?></div>
				</div>
				<div id="compiler-notice" class="col-md-8 p-md-3" style="display:none;">
					<?php echo LayoutHelper::render('jcbnoticeboard',
						['id' => 'mastodon-feed-2', 'button_id' => 'refresh-feed-2', 'posts' => 7, 'dankie' => $this->dankie]); ?>
				</div>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo Html::_('form.token'); ?>
	</form>
</div>
<script type="text/javascript">
// token
var token = '<?php echo Session::getFormToken(); ?>';
var all_is_good = '<?php echo Text::_('COM_COMPONENTBUILDER_ALL_IS_GOOD_THERE_IS_NO_NOTICE_AT_THIS_TIME'); ?>';
document.addEventListener('DOMContentLoaded', function () {
    // Attaching the change event listener to the element with id 'component_id'
    var componentSelect = document.getElementById('component_id');
    if (componentSelect) {
        componentSelect.addEventListener('change', function (e) {
            var component = this.value; // 'this' refers to componentSelect element
            if (component === "") {
                // Setting the innerHTML of the 'component-details' element
                document.getElementById('component-details').innerHTML = "<?php echo $selectNotice; ?>";
                // Displaying the noticeboard
                document.getElementById("noticeboard").style.display = 'block';
                // Looping through all elements with class 'notice' to display them
                document.querySelectorAll('.notice').forEach(function (elem) {
                    elem.style.display = 'block';
                });
            } else {
                // If a component is selected, call getComponentDetails with the selected value
                getComponentDetails(component);
                // Hiding the noticeboard
                document.getElementById("noticeboard").style.display = 'none';
                // Looping through all elements with class 'notice' to hide them
                document.querySelectorAll('.notice').forEach(function (elem) {
                    elem.style.display = 'none';
                });
            }
        });
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
<?php endif; ?>
<?php else: ?>
		<h1><?php echo Text::_('COM_COMPONENTBUILDER_NO_ACCESS_GRANTED'); ?></h1>
<?php endif; ?>
