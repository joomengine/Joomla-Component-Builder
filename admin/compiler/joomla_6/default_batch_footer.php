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

use Joomla\CMS\Language\Text;

// No direct access to this file
defined('_JEXEC') or die;###LICENSE_LOCKED_DEFINED###

?>
<!-- clear the batch values if cancel -->
<button class="btn" type="button" onclick="###BATCH_ONCLICK_CANCEL_SCRIPT###" data-dismiss="modal">
	<?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JCANCEL'); ?>
</button>
<!-- post the batch values if process -->
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('###view###.batch');">
	<?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>