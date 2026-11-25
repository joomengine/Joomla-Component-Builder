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
use Joomla\CMS\HTML\HTMLHelper as Html;

// No direct access to this file
defined('_JEXEC') or die;###LICENSE_LOCKED_DEFINED###

?>
<div id="filter-bar" class="btn-toolbar">
	<div class="filter-search btn-group pull-left">
		<label for="filter_search" class="element-invisible"><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('Search');?></label>
		<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo Joomla___34690c75_1090_47eb_8c06_7228dc7eedd6___Power::tooltipText('Search ###Views###'); ?>" />
	</div>
	<div class="btn-group pull-left">
		<button type="submit" class="btn hasTooltip" title="<?php echo Joomla___34690c75_1090_47eb_8c06_7228dc7eedd6___Power::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
		<button type="button" class="btn hasTooltip" title="<?php echo Joomla___34690c75_1090_47eb_8c06_7228dc7eedd6___Power::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
	</div>
	<div class="btn-group pull-right hidden-phone">
		<label for="limit" class="element-invisible"><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
	<div class="btn-group pull-right hidden-phone">
		<label for="directionTable" class="element-invisible"><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JFIELD_ORDERING_DESC');?></label>
		<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
			<option value=""><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JFIELD_ORDERING_DESC');?></option>
			<option value="asc" <?php if ($this->listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JGLOBAL_ORDER_ASCENDING');?></option>
			<option value="desc" <?php if ($this->listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JGLOBAL_ORDER_DESCENDING');?></option>
		</select>
	</div>
	<div class="btn-group pull-right">
		<label for="sortTable" class="element-invisible"><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JGLOBAL_SORT_BY');?></label>
		<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
			<option value=""><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JGLOBAL_SORT_BY');?></option>
			<?php echo Joomla___34690c75_1090_47eb_8c06_7228dc7eedd6___Power::_('select.options', $this->getSortFields(), 'value', 'text', $this->listOrder);?>
		</select>
	</div>
</div>
<div class="clearfix"> </div>