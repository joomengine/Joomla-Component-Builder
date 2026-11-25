<?php
/**
 * @package	Joomla.Component.Builder
 *
 * @created	4th September 2022
 * @author	 Llewellyn van der Merwe <https://dev.vdm.io>
 * @git		Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###

use Joomla\CMS\Language\Text;

// No direct access to this file
defined('_JEXEC') or die;

?>
<div class="com-###component###-dashboard-details">
	<div class="com-###component###-dashboard-details__image mb-4">
		<img
			class="img-fluid w-100"
			alt="<?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###'); ?>"
			src="components/com_###component###/assets/images/vdm-component.###COMP_IMAGE_TYPE###"
			loading="lazy"
			decoding="async"
		>
	</div>
	<ul class="list-group list-group-flush mb-4">
		<li class="list-group-item d-flex flex-wrap justify-content-between gap-2">
			<span>
				<strong><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_VERSION'); ?>:</strong>
				<?php echo $this->manifest->version; ?>
			</span>
			<span class="update-notice" id="component-update-notice"></span>
		</li>
		<li class="list-group-item">
			<strong><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_DATE'); ?>:</strong>
			<?php echo $this->manifest->creationDate; ?>
		</li>
		<li class="list-group-item">
			<strong><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_AUTHOR'); ?>:</strong>
			<a href="mailto:<?php echo $this->manifest->authorEmail; ?>">
				<?php echo $this->manifest->author; ?>
			</a>
		</li>
		<li class="list-group-item">
			<strong><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_WEBSITE'); ?>:</strong>
			<a
				href="<?php echo $this->manifest->authorUrl; ?>"
				target="_blank"
				rel="noopener noreferrer"
			>
				<?php echo $this->manifest->authorUrl; ?>
			</a>
		</li>
		<li class="list-group-item">
			<strong><?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_LICENSE'); ?>:</strong>
			<?php echo $this->manifest->license; ?>
		</li>
		<li class="list-group-item">
			<strong><?php echo $this->manifest->copyright; ?></strong>
		</li>
	</ul>
	<?php if (Super___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check($this->contributors)) : ?>
		<div class="com-###component###-dashboard-details__contributors mt-4">
			<h3 class="h5 mb-3">
				<?php if (count($this->contributors) > 1) : ?>
					<?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_CONTRIBUTORS'); ?>
				<?php else : ?>
					<?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_CONTRIBUTOR'); ?>
				<?php endif; ?>
			</h3>
			<ul class="list-group list-group-flush">
				<?php foreach ($this->contributors as $contributor) : ?>
					<li class="list-group-item">
						<strong><?php echo $contributor['title']; ?>:</strong>
						<?php echo $contributor['name']; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>