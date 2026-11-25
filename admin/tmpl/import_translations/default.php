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

// No direct access to this file
defined('_JEXEC') or die;

$this->app->getInput()->set('hidemainmenu', false);

?>
<?php if ($this->canDo->get('import_translations.access')): ?>
<?php
$jcbSubmitScript = '	Joomla.submitbutton = function(task) {'
	. PHP_EOL . '		if (task === \'import_translations.back\') {'
	. PHP_EOL . '			parent.history.back();'
	. PHP_EOL . '			return false;'
	. PHP_EOL . '		} else {'
	. PHP_EOL . '			var form = document.getElementById(\'adminForm\');'
	. PHP_EOL . '			form.task.value = task;'
	. PHP_EOL . '			form.submit();'
	. PHP_EOL . '		}'
	. PHP_EOL . '	}';
$this->getDocument()->getWebAssetManager()->addInlineScript(
	$jcbSubmitScript, ['name' => 'com_componentbuilder.import_translations.submit'], [], ['core']
);
?>
<?php $urlId = (isset($this->item->id)) ? '&id='. (int) $this->item->id : ''; ?>
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&view=import_translations' . $urlId); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<div class="main-card">
	<div class="row">
		<div class="col-md-12">
			<?php echo $this->loadTemplate('translationimporterareajsix'); ?>
		</div>
	</div>
</div>
<input type="hidden" name="task" value="" />
<?php echo Html::_('form.token'); ?>
</form>
<?php else: ?>
		<h1><?php echo Text::_('COM_COMPONENTBUILDER_NO_ACCESS_GRANTED'); ?></h1>
<?php endif; ?>
