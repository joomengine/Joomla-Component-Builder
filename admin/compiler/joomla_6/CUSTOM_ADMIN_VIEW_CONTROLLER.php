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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Controller;

###CUSTOM_ADMIN_VIEW_CONTROLLER_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### ###SView### Base Controller
 *
 * @since  1.6
 */
class ###SView###Controller extends BaseController
{
	/**
	 * The context for storing internal data, e.g. record.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = '###sview###';

	/**
	 * The URL view item variable.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $view_item = '###sview###';

	/**
	 * Adds option to redirect back to the dashboard.
	 *
	 * @return  void
	 * @since   3.0
	 */
	public function dashboard(): void
	{
		$this->setRedirect(Joomla___d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_###component###', false));
	}###CUSTOM_ADMIN_CUSTOM_BUTTONS_CONTROLLER###
}
