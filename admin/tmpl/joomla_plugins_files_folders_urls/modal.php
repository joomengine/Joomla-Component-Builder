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

/** @var \VDM\Component\Componentbuilder\Administrator\View\Joomla_plugins_files_folders_urls\HtmlView $this */

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
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=joomla_plugins_files_folders_urls&layout=modal&tmpl=component&titleKey=' . $this->getModalTitleKey()); ?>" method="post" name="adminForm" id="adminForm">
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
	<table class="table table-striped" id="joomla_plugin_files_folders_urlsList">
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
