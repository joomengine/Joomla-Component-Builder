<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###

use Joomla\CMS\Router\Route;

// No direct access to this file
defined('_JEXEC') or die;###LICENSE_LOCKED_DEFINED###

/** @var \###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Site\View\###View###\HtmlView $this */

$icon = 'icon-check';
$title_key = $this->item->###SQL_TITLE_KEY### ?? '';
$title_column = $this->item->###SQL_TITLE_COLUMN### ?? '';
$data = [
	'contentType' => 'com_###component###.###view###',
	'id' => $title_key,
	'title' => $title_column,
	'uri' => Route::_('index.php?option=com_###component###&layout=modal&tmpl=component&id='. (int) ($this->item->id ?? 0))
];

// Add Content select script
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('modal-content-select');

// The data for Content select script
$this->getDocument()->addScriptOptions('content-select-on-load', $data, false);

?>

<div class="px-4 py-5 my-5 text-center">
    <span class="fa-8x mb-4 <?php echo $icon; ?>" aria-hidden="true"></span>
    <h1 class="display-5 fw-bold"><?php echo $this->sanitize($title_column, false); ?></h1>
    <div class="col-lg-6 mx-auto">
        <p class="lead mb-4">
            <?php echo $this->sanitize($title_column, false); ?>
        </p>
    </div>
</div>