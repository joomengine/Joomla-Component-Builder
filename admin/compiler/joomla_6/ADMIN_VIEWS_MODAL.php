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

###ADMIN_VIEWS_MODAL_HEADER###

// No direct access to this file
defined('_JEXEC') or die;###LICENSE_LOCKED_DEFINED###

/** @var \###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\View\###Views###\HtmlView $this */

$app = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();

if ($app->isClient('site'))
{
    Joomla___5ba38513_5c4f_4b0d_935e_49e986a6bce8___Power::checkToken('get') or die(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JINVALID_TOKEN'));
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
###VIEWS_MODAL_BODY######VIEWS_FOOTER_SCRIPT###
