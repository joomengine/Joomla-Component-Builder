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

// No direct access to this file
defined('_JEXEC') or die;

// get the form
$form = $displayData->getForm();

// get the layout fields override method name (from layout path/ID)
$layout_path_array = explode('.', $this->getLayoutId());
// Since we cannot pass the layout and tab names as parameters to the model method
// this name combination of tab and layout in the method name is the only work around
// seeing that JCB uses those two values (tab_name & layout_name) as the layout file name.
// example of layout name: details_left.php
// example of method name: getFields_details_left()
$fields_tab_layout = 'fields_' . $layout_path_array[1];

// get the fields
$fields = $displayData->get($fields_tab_layout) ?: array(
	###LAYOUTITEMS###
);

$hiddenFields = $displayData->get('hidden_fields') ?: [];

?>
<?php if ($fields && count((array) $fields)) :?>
<?php foreach($fields as $field): ?>
	<?php if (in_array($field, $hiddenFields)) : ?>
		<?php $form->setFieldAttribute($field, 'type', 'hidden'); ?>
	<?php endif; ?>
	<?php echo $form->renderField($field, null, null, array('class' => 'control-wrapper-' . $field)); ?>
<?php endforeach; ?>
<?php endif; ?>
