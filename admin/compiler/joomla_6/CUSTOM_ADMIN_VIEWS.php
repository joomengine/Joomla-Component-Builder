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

###CUSTOM_ADMIN_VIEWS_HEADER###

// No direct access to this file
defined('_JEXEC') or die;###LICENSE_LOCKED_DEFINED######CUSTOM_ADMIN_CODE_BODY###

?>
<?php if ($this->canDo->get('###sview###.access')): ?>###CUSTOM_ADMIN_SUBMITBUTTON_SCRIPT###
###CUSTOM_ADMIN_TOP_FORM######CUSTOM_ADMIN_BODY######CUSTOM_ADMIN_BOTTOM_FORM###
<?php else: ?>
		<h1><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_NO_ACCESS_GRANTED'); ?></h1>
<?php endif; ?>
