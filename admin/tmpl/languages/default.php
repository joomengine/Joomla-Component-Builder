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

// No direct access to this file
defined('_JEXEC') or die;

if ($this->saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_componentbuilder&task=languages.saveOrderAjax&tmpl=component';
	Html::_('sortablelist.sortable', 'languageList', 'adminForm', strtolower($this->listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=languages'); ?>" method="post" name="adminForm" id="adminForm">
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
	<table class="table table-striped" id="languageList">
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
// languages footer script

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
// when the build button is clicked
jQuery('#toolbar').on('click',"button.button-joomla", function(e){
	loadingDiv.style.display = 'block';
});

</script>
