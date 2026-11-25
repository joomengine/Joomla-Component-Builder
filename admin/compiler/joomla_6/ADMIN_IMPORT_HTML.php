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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\View\Import;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Helper\###Component###Helper;

// No direct access to this file
\defined('_JEXEC') or die;###LICENSE_LOCKED_DEFINED###

/**
 * ###Component### Import Html View
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	protected $headerList;
	protected $hasPackage = false;
	protected $headers;
	protected $hasHeader = 0;
	protected $dataType;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		$paths = new \StdClass;
		$paths->first = '';
		$model = $this->getModel();
		$model->setUseExceptions(true);
		$state = $model->getState();

		$this->paths = &$paths;
		$this->state = &$state;
		// get global action permissions
		$this->canDo = ###Component###Helper::getActions('import');

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

		// get the session object
		$session = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getSession();
		// check if it has package
		$this->hasPackage     = $session->get('hasPackage', false);
		$this->dataType     = $session->get('dataType', false);
		if($this->hasPackage && $this->dataType)
		{
			$this->headerList     = json_decode($session->get($this->dataType.'_VDM_IMPORTHEADERS', false),true);
			$this->headers         = ###Component###Helper::getFileHeaders($this->dataType);
			// clear the data type
			$session->remove('dataType');
		}


		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		Joomla___0c1a176a_304f_433a_8233_37d01ff87815___Power::title(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('COM_###COMPONENT###_IMPORT_TITLE'), 'upload');

		if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			Joomla___0c1a176a_304f_433a_8233_37d01ff87815___Power::preferences('com_###component###');
		}

		// set help url for this view if found
		$this->help_url = ###Component###Helper::getHelpUrl('import');
		if (Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($this->help_url))
		{
			Joomla___0c1a176a_304f_433a_8233_37d01ff87815___Power::help('COM_###COMPONENT###_HELP_MANAGER', false, $this->help_url);
		}
	}
}
