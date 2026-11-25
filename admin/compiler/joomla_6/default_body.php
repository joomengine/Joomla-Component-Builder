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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Helper\###Component###Helper;

// No direct access to this file
defined('_JEXEC') or die;###LICENSE_LOCKED_DEFINED###

$edit = "index.php?option=com_###component###&view=###views###&task=###view###.edit";

?>
###LISTBODY###