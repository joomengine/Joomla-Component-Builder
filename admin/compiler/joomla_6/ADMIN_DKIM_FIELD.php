<?php
/**
 * @package    Joomla.Component.Builder
 * @created    23rd October, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Field;

use Joomla\CMS\Form\Field\NoteField;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * DKIM configuration note and its DNS preview script.
 *
 * @since  6.2.0
 */
class DkimField extends NoteField
{
	/**
	 * The field type resolved by Joomla's form loader.
	 *
	 * @var    string
	 * @since  6.2.0
	 */
	protected $type = 'Dkim';

	/**
	 * Activate the DNS preview before rendering Joomla's native note markup.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getLabel()
	{
		Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getDocument()->getWebAssetManager()
			->registerAndUseScript(
				'com_###component###.dkim',
				Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root() . 'media/com_###component###/js/admin/dkim.js',
				['version' => 'auto'],
				['defer' => true]
			);

		return parent::getLabel();
	}
}
