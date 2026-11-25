<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    18th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Controller;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\EximportView;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ImportCustomScripts;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Controller\EximportMethodInterface;


/**
 * Controller Eximport Method Class.
 * 
 * Builds the exportData and importData methods of an admin list view
 * controller: the token check, the permission guard, the spreadsheet export,
 * and the session hand-off into the import view.
 * 
 * Target variants supply the current-user and import-session lookups.
 * 
 * @since  6.1.7
 */
class EximportMethod implements EximportMethodInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 6.1.7
	 */
	protected Language $language;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * The Eximport View Class.
	 *
	 * @var   EximportView
	 * @since 6.1.7
	 */
	protected EximportView $eximportview;

	/**
	 * The Import Custom Scripts Class.
	 *
	 * @var   ImportCustomScripts
	 * @since 6.1.7
	 */
	protected ImportCustomScripts $importcustomscripts;

	/**
	 * Constructor.
	 *
	 * @param Config               $config               The Config Class.
	 * @param Language             $language             The Language Class.
	 * @param ContentOne           $contentone           The ContentOne Class.
	 * @param EximportView         $eximportview         The Eximport View Class.
	 * @param ImportCustomScripts  $importcustomscripts  The Import Custom Scripts Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Language $language,
		ContentOne $contentone,
		EximportView $eximportview,
		ImportCustomScripts $importcustomscripts)
	{
		$this->config = $config;
		$this->language = $language;
		$this->contentone = $contentone;
		$this->eximportview = $eximportview;
		$this->importcustomscripts = $importcustomscripts;
	}

	/**
	 * Build the exportData and importData methods of an admin list controller.
	 *
	 * @param   string  $nameSingleCode  The single view code name.
	 * @param   string  $nameListCode    The list view code name.
	 *
	 * @return  string
	 *
	 * @since   6.1.7
	 */
	public function get(string $nameSingleCode, string $nameListCode): string
	{
		if (!$this->eximportview->get($nameListCode))
		{
			return '';
		}

		$method = [];

		// add the export method
		$method[] = PHP_EOL . PHP_EOL . Indent::_(1)
			. "public function exportData()";
		$method[] = Indent::_(1) . "{";
		$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Check for request forgeries";
		$method[] = Indent::_(2) . "Joomla__"."_5ba38513_5c4f_4b0d_935e_49e986a6bce8___Power::checkToken() or die(Text:"
			. ":_('JINVALID_TOKEN'));";
		$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " check if export is allowed for this user.";
		$method[] = $this->getUserObject();
		$method[] = Indent::_(2) . "if (\$user->authorise('"
			. $nameSingleCode . ".export', 'com_"
			. $this->config->component_code_name
			. "') && \$user->authorise('core.export', 'com_"
			. $this->config->component_code_name . "'))";
		$method[] = Indent::_(2) . "{";
		$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Get the input";
		$method[] = $this->getInputObject();
		$method[] = Indent::_(3)
			. "\$pks = \$input->post->get('cid', array(), 'array');";
		$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Sanitize the input";
		$method[] = Indent::_(3) . "\$pks = ArrayHelper::toInteger(\$pks);";
		$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Get the model";
		$method[] = Indent::_(3) . "\$model = \$this->getModel('"
			. StringHelper::safe($nameListCode, 'F')
			. "');";
		$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " get the data to export";
		$method[] = Indent::_(3)
			. "\$data = \$model->getExportData(\$pks);";
		$method[] = Indent::_(3) . "if ("
			. "Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$data))";
		$method[] = Indent::_(3) . "{";
		$method[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
			. " now set the data to the spreadsheet";
		$method[] = Indent::_(4) . "\$date = Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getDate();";
		$method[] = Indent::_(4) . $this->contentone->get('Component') . "Helper::xls(\$data,'"
			. StringHelper::safe($nameListCode, 'F')
			. "_'.\$date->format('jS_F_Y'),'"
			. StringHelper::safe($nameListCode, 'Ww')
			. " exported ('.\$date->format('jS F, Y').')','"
			. StringHelper::safe($nameListCode, 'w')
			. "');";
		$method[] = Indent::_(3) . "}";
		$method[] = Indent::_(2) . "}";
		$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Redirect to the list screen with error.";
		$method[] = Indent::_(2) . "\$message = Joomla__"."_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('"
			. $this->config->lang_prefix . "_EXPORT_FAILED');";
		$method[] = Indent::_(2)
			. "\$this->setRedirect(Joomla__"."_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_"
			. $this->config->component_code_name . "&view=" . $nameListCode
			. "', false), \$message, 'error');";
		$method[] = Indent::_(2) . "return;";
		$method[] = Indent::_(1) . "}";

		// add the import method
		$method[] = PHP_EOL . PHP_EOL . Indent::_(1)
			. "public function importData()";
		$method[] = Indent::_(1) . "{";
		$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Check for request forgeries";
		$method[] = Indent::_(2) . "Joomla__"."_5ba38513_5c4f_4b0d_935e_49e986a6bce8___Power::checkToken() or die(Text:"
			. ":_('JINVALID_TOKEN'));";
		$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " check if import is allowed for this user.";
		$method[] = $this->getUserObject();
		$method[] = Indent::_(2) . "if (\$user->authorise('"
			. $nameSingleCode . ".import', 'com_"
			. $this->config->component_code_name
			. "') && \$user->authorise('core.import', 'com_"
			. $this->config->component_code_name . "'))";
		$method[] = Indent::_(2) . "{";
		$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " Get the import model";
		$method[] = Indent::_(3) . "\$model = \$this->getModel('"
			. StringHelper::safe($nameListCode, 'F')
			. "');";
		$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
			. " get the headers to import";
		$method[] = Indent::_(3)
			. "\$headers = \$model->getExImPortHeaders();";
		$method[] = Indent::_(3) . "if ("
			. "Super_" . "__91004529_94a9_4590_b842_e7c6b624ecf5___Power::check(\$headers))";
		$method[] = Indent::_(3) . "{";
		$method[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
			. " Load headers to session.";
		$method[] = $this->getSessionObject();
		$method[] = Indent::_(4) . "\$headers = json_encode(\$headers);";
		$method[] = Indent::_(4) . "\$session->set('" . $nameSingleCode
			. "_VDM_IMPORTHEADERS', \$headers);";
		$method[] = Indent::_(4) . "\$session->set('backto_VDM_IMPORT', '"
			. $nameListCode . "');";
		$method[] = Indent::_(4)
			. "\$session->set('dataType_VDM_IMPORTINTO', '"
			. $nameSingleCode . "');";
		$method[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
			. " Redirect to import view.";
		// add to lang array
		$selectImportFileNote = $this->config->lang_prefix
			. "_IMPORT_SELECT_FILE_FOR_"
			. StringHelper::safe($nameListCode, 'U');
		$this->language->set(
			$this->config->lang_target, $selectImportFileNote,
			'Select the file to import data to ' . $nameListCode . '.'
		);
		$method[] = Indent::_(4) . "\$message = Joomla__"."_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('"
			. $selectImportFileNote . "');";
		// if this view has custom script it must have as custom import (model, veiw, controller)
		if ($this->importcustomscripts->get($nameListCode))
		{
			$method[] = Indent::_(4)
				. "\$this->setRedirect(Joomla__"."_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_"
				. $this->config->component_code_name . "&view=import_"
				. $nameListCode . "', false), \$message);";
		}
		else
		{
			$method[] = Indent::_(4)
				. "\$this->setRedirect(Joomla__"."_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_"
				. $this->config->component_code_name
				. "&view=import', false), \$message);";
		}
		$method[] = Indent::_(4) . "return;";
		$method[] = Indent::_(3) . "}";
		$method[] = Indent::_(2) . "}";
		$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Redirect to the list screen with error.";
		$method[] = Indent::_(2) . "\$message = Joomla__"."_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('"
			. $this->config->lang_prefix . "_IMPORT_FAILED');";
		$method[] = Indent::_(2)
			. "\$this->setRedirect(Joomla__"."_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_"
			. $this->config->component_code_name . "&view=" . $nameListCode
			. "', false), \$message, 'error');";
		$method[] = Indent::_(2) . "return;";
		$method[] = Indent::_(1) . "}";

		return implode(PHP_EOL, $method);
	}

	/**
	 * Get the statement that puts the current user in scope.
	 *
	 * @return  string
	 * @since   6.1.7
	 */
	protected function getUserObject(): string
	{
		return Indent::_(2) . "\$user = Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getIdentity();";
	}

	/**
	 * Get the statement that puts the import session in scope.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getSessionObject(): string
	{
		return Indent::_(4) . "\$session = Joomla__"
			. "_39403062_84fb_46e0_bac4_0023f766e827___Power::getSession();";
	}

	/**
	 * Put the controller request input in scope for the generated method.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function getInputObject(): string
	{
		return Indent::_(3) . '$input = Joomla__' . '_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->input;';
	}
}

