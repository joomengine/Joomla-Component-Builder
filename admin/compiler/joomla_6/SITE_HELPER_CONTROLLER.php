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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### Help Base Controller
 *
 * @since  1.6
 */
class HelpController extends BaseController
{
	public function __construct($config)
	{
		parent::__construct($config);
		// load the tasks
		$this->registerTask('getText', 'help');
	}

	public function help()
	{
		$app        = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$user       = $app->getIdentity();
		$jinput     = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		// Check Token!
		$token         = Joomla___5ba38513_5c4f_4b0d_935e_49e986a6bce8___Power::getFormToken();
		$call_token    = $jinput->get('token', 0, 'ALNUM');
		if($user->id != 0 && ($jinput->get($token, 0, 'ALNUM') || $token === $call_token))
		{
			$task = $this->getTask();
			switch($task){
				case 'getText':
					try
					{
						$idValue = $jinput->get('id', 0, 'INT');
						if($idValue)
						{
							$result = $this->getHelpDocumentText($idValue);
						}
						else
						{
							$result = '';
						}
						echo $result;
						// stop execution gracefully
						jexit();
					}
					catch(Exception $e)
					{
						// stop execution gracefully
						jexit();
					}
				break;
			}
		}
		else
		{
			// stop execution gracefully
			jexit();
		}
	}

	protected function getHelpDocumentText($id)
	{
		$db     = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->get(DatabaseInterface::class);
		$query  = $db->createQuery();
		$query->select(array('a.title','a.content'));
		$query->from('#__###component###_help_document AS a');
		$query->where('a.id = '.(int) $id);
		$query->where('a.published = 1');
		$query->where('a.location = 2');
		$db->setQuery($query);
		$db->execute();
		if($db->getNumRows())
		{
			$text = [];
			$document = $db->loadObject();
			// fix image issue
			$images['src="images'] = 'src="'.Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root().'images';
			$images["src='images"] = "src='".Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root()."images";
			$images['src="/images'] = 'src="'.Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root().'images';
			$images["src='/images"] = "src='".Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root()."images";
			// set document template
			$text[] = "<!doctype html>";
			$text[] = '<html>';
			$text[] = "<head>";
			$text[] = '<meta charset="utf-8">';
			$text[] = "<title>".$document->title."</title>";
			$text[] = '<link type="text/css" href="'.Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root().'media/com_###component###/uikit/css/uikit.gradient.min.css" rel="stylesheet"></link>';
			$text[] = '<script type="text/javascript" src="'.Joomla___eecc143e_b5cf_4c33_ba4d_97da1df61422___Power::root().'media/com_###component###/uikit/js/uikit.min.js"></script>';
			$text[] = "</head>";
			$text[] = '<body><br />';
			$text[] = '<div class="uk-container uk-container-center uk-grid-collapse">';
			$text[] = '<div class="uk-panel uk-width-1-1 uk-panel-box uk-panel-box-primary">';
			// build the help text
			$text[] = '<h1 class="uk-panel-title">'.$document->title."</h1>";
			$text[] = str_replace(array_keys($images),array_values($images),$document->content);
			// end template
			$text[] = '</div><br /><br />';
			$text[] = '</div>';
			$text[] = "</body>";
			$text[] = "</html>";

			return implode("\n",$text);
		}
		return false;
	}
}
