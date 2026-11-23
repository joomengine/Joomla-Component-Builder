<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace VDM\Component\Componentbuilder\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Help Base Controller
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
		$app        = Factory::getApplication();
		$user       = $app->getIdentity();
		$jinput     = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		// Check Token!
		$token         = Session::getFormToken();
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
		$db       = Factory::getContainer()->get(DatabaseInterface::class);
		$query    = $db->getQuery(true);
		$query->select(array('a.title','a.content'));
		$query->from('#__componentbuilder_help_document AS a');
		$query->where('a.id = '.(int) $id);
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		if($db->getNumRows())
		{
			$text = [];
			$document = $db->loadObject();
			// fix image issue
			$images['src="images'] = 'src="'.Uri::root().'images';
			$images["src='images"] = "src='".Uri::root()."images";
			$images['src="/images'] = 'src="'.Uri::root().'images';
			$images["src='/images"] = "src='".Uri::root()."images";
			// set document template
			$text[] = "<!doctype html>";
			$text[] = '<html>';
			$text[] = "<head>";
			$text[] = '<meta charset="utf-8">';
			$text[] = "<title>".$document->title."</title>";
			$text[] = '<link type="text/css" href="'.Uri::root().'media/com_componentbuilder/uikit/css/uikit.gradient.min.css" rel="stylesheet"></link>';
			$text[] = '<script type="text/javascript" src="'.Uri::root().'media/com_componentbuilder/uikit/js/uikit.min.js"></script>';
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
