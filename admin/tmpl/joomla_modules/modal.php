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
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

// No direct access to this file
defined('_JEXEC') or die;

/** @var \VDM\Component\Componentbuilder\Administrator\View\Joomla_modules\HtmlView $this */

$app = Factory::getApplication();

if ($app->isClient('site'))
{
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

// dynamic selection of title key (link in modal)
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$this->modalTitleKey = $input->get('titleKey', 'id', 'word');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('multiselect')
    ->useScript('modal-content-select');
?>
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=joomla_modules&layout=modal&tmpl=component&titleKey=' . $this->getModalTitleKey()); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
<?php
	// Add the trash helper layout
	echo LayoutHelper::render('trashhelper', $this);
	// Add the searchtools
	echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
?>
<?php if (empty($this->items)): ?>
	<div class="alert alert-no-items">
		<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
<?php else : ?>
	<table class="table table-striped" id="joomla_moduleList">
		<thead><?php echo $this->loadTemplate('head');?></thead>
		<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
		<tbody><?php echo $this->loadTemplate('body');?></tbody>
	</table>
	<input type="hidden" name="boxchecked" value="0" />
	</div>
<?php endif; ?>
	<input type="hidden" name="task" value="" />
	<?php echo Html::_('form.token'); ?>
</form>
<script type="text/javascript">
// joomla_modules footer script

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

    document.addEventListener('DOMContentLoaded', function () {
        const pushButton = document.getElementById('toolbar-share-custom-button-pushpowers');
        const resetButton = document.getElementById('toolbar-joomla-custom-button-resetpowers');
        // Set confirmation messages
        if (pushButton) {
            const pushText = Joomla.Text._('COM_COMPONENTBUILDER_HTHREEKEEP_THIS_WIDOW_OPENHTHREEPTHIS_IS_A_BVERY_LARGE_TASKB_AND_MAY_TAKE_A_BLONG_TIMEB_TO_COMPLETEBRBRIT_WILL_BPUSH_ALLB_ENTITIES_LINKED_TO_EACH_SELECTED_BJOOMLA_MODULEB_DOWN_TO_THE_FIELD_LEVELBRBRDO_YOU_WANT_TO_PROCEEDP');
            pushButton.setAttribute('confirm-message', pushText);
        }
        if (resetButton) {
            const resetText = Joomla.Text._('COM_COMPONENTBUILDER_HTHREEKEEP_THIS_WIDOW_OPENHTHREEPTHIS_IS_A_BVERY_LARGE_TASKB_AND_MAY_TAKE_A_BLONG_TIMEB_TO_COMPLETEBRBRIT_WILL_BRESET_ALLB_ENTITIES_LINKED_TO_EACH_SELECTED_BJOOMLA_MODULEB_DOWN_TO_THE_FIELD_LEVELBRBRDO_YOU_WANT_TO_CONTINUEP');
            resetButton.setAttribute('confirm-message', resetText);
        }
        const form = document.adminForm;
        // Hook into the form's submit event
        if (form && loadingDiv) {
            form.addEventListener('submit', function () {
                loadingDiv.style.display = 'block';
            });
        }
    });

<?php
		// some language strings for JS in this area
		Text::script('COM_COMPONENTBUILDER_HTHREEKEEP_THIS_WIDOW_OPENHTHREEPTHIS_IS_A_BVERY_LARGE_TASKB_AND_MAY_TAKE_A_BLONG_TIMEB_TO_COMPLETEBRBRIT_WILL_BPUSH_ALLB_ENTITIES_LINKED_TO_EACH_SELECTED_BJOOMLA_MODULEB_DOWN_TO_THE_FIELD_LEVELBRBRDO_YOU_WANT_TO_PROCEEDP');
		Text::script('COM_COMPONENTBUILDER_HTHREEKEEP_THIS_WIDOW_OPENHTHREEPTHIS_IS_A_BVERY_LARGE_TASKB_AND_MAY_TAKE_A_BLONG_TIMEB_TO_COMPLETEBRBRIT_WILL_BRESET_ALLB_ENTITIES_LINKED_TO_EACH_SELECTED_BJOOMLA_MODULEB_DOWN_TO_THE_FIELD_LEVELBRBRDO_YOU_WANT_TO_CONTINUEP');
?>
</script>
