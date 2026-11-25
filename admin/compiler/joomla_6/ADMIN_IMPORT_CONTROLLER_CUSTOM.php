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

###IMPORT_CUSTOM_CONTROLLER_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### ###View### Base Controller
 *
 * @since  1.6
 */
class ###View###importController extends BaseController
{
	/**
	 * Import an spreadsheet.
	 *
	 * @return  void
	 */
	public function import()
	{
		// Check for request forgeries
		Joomla___5ba38513_5c4f_4b0d_935e_49e986a6bce8___Power::checkToken() or jexit(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JINVALID_TOKEN'));

		$model = $this->getModel('###View###');
		if ($model->import())
		{
			$cache = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()
				->get(\Joomla\CMS\Cache\CacheControllerFactoryInterface::class)
				->createCacheController('callback', ['defaultgroup' => 'mod_menu']);
			$cache->clean();
			// TODO: Reset the users acl here as well to kill off any missing bits
		}

		$app = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$redirect_url = $app->getUserState('com_###component###.redirect_url');
		if (empty($redirect_url))
		{
			$redirect_url = Joomla___d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_###component###&view=###view###', false);
		}
		else
		{
			// wipe out the user state when we're going to redirect
			$app->setUserState('com_###component###.redirect_url', '');
			$app->setUserState('com_###component###.message', '');
			$app->setUserState('com_###component###.extension_message', '');
		}
		$this->setRedirect($redirect_url);
	}
}
