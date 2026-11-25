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
defined('_JEXEC') or die;

?>
<?php if (isset($this->icons['main']) && is_array($this->icons['main']) && !empty($this->icons['main'])) : ?>
    <div class="dashboard-icons" role="list">
		<?php foreach ($this->icons['main'] as $icon) : ?>
            <div class="dashboard-icon-item" role="listitem">
                <a class="dashboard-icon-link" href="<?php echo $icon->url; ?>">
					<span class="dashboard-icon-image">
						<img
                            alt="<?php echo $icon->alt; ?>"
                            src="components/com_###component###/assets/images/icons/<?php echo $icon->image; ?>"
                            loading="lazy"
                            decoding="async"
                        >
					</span>
                    <span class="dashboard-icon-title">
						<?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_($icon->name); ?>
					</span>
                </a>
            </div>
		<?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="alert alert-danger">
        <h4 class="alert-heading">
			<?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_("Permission denied, or not correctly set"); ?>
        </h4>
        <div>
			<?php echo Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_("Please notify your System Administrator if result is unexpected."); ?>
        </div>
    </div>
<?php endif; ?>