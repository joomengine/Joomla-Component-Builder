<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    19th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Controller;


use VDM\Joomla\Componentbuilder\Compiler\Builder\DynamicButtons;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Controller\CustomAdminDynamicButtonInterface;


/**
 * Controller Custom Admin Dynamic Button Class.
 * 
 * Builds the controller method behind every dynamic button a custom admin view
 * was given: the token check, the permission check, and the redirect back to
 * the view with whatever the model answered.
 * 
 * How the current user is reached is what the compile target decides, and it
 * is the extension point below.
 * 
 * @since 6.1.7
 */
class CustomAdminDynamicButton implements CustomAdminDynamicButtonInterface
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
	 * The Dynamic Buttons Builder Class.
	 *
	 * @var   DynamicButtons
	 * @since 6.1.7
	 */
	protected DynamicButtons $dynamicbuttons;

	/**
	 * Constructor.
	 *
	 * @param Config         $config         The Config Class.
	 * @param Language       $language       The Language Class.
	 * @param DynamicButtons $dynamicbuttons The Dynamic Buttons Builder Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Language $language,
		DynamicButtons $dynamicbuttons)
	{
		$this->config = $config;
		$this->language = $language;
		$this->dynamicbuttons = $dynamicbuttons;
	}

	/**
	 * Build the controller methods the dynamic buttons of a view call.
	 *
	 * A view that was given no buttons is given no methods.
	 *
	 * @param   string  $nameListCode  The list view name.
	 *
	 * @return  string  The methods, or nothing when the view has no buttons.
	 *
	 * @since   6.1.7
	 */
	public function get($nameListCode): string
	{
		$method = '';
		if ($this->dynamicbuttons->isArray($nameListCode))
		{
			$method = [];
			foreach ($this->dynamicbuttons->get($nameListCode) as $custom_button)
			{
				// add the custom redirect method
				$method[] = PHP_EOL . PHP_EOL . Indent::_(1)
					. "public function redirectTo"
					. StringHelper::safe(
						$custom_button['link'], 'F'
					) . "()";
				$method[] = Indent::_(1) . "{";
				$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Check for request forgeries";
				$method[] = Indent::_(2)
					. "Joomla__"."_5ba38513_5c4f_4b0d_935e_49e986a6bce8___Power::checkToken() or die(Text:"
					. ":_('JINVALID_TOKEN'));";
				$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " check if export is allowed for this user.";
				$method[] = $this->currentUser();
				$method[] = Indent::_(2) . "if (\$user->authorise('"
					. $custom_button['link'] . ".access', 'com_"
					. $this->config->component_code_name . "'))";
				$method[] = Indent::_(2) . "{";
				$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " Get the input";
				$method[] = $this->getInputObject();
				$method[] = Indent::_(3)
					. "\$pks = \$input->post->get('cid', array(), 'array');";
				$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " Sanitize the input";
				$method[] = Indent::_(3)
					. "\$pks = ArrayHelper::toInteger(\$pks);";
				$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " convert to string";
				$method[] = Indent::_(3) . "\$ids = implode('_', \$pks);";
				$method[] = Indent::_(3)
					. "\$this->setRedirect(Joomla__"."_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_"
					. $this->config->component_code_name . "&view="
					. $custom_button['link'] . "&cid='.\$ids, false));";
				$method[] = Indent::_(3) . "return;";
				$method[] = Indent::_(2) . "}";
				$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Redirect to the list screen with error.";
				$method[] = Indent::_(2) . "\$message = Joomla__"."_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('"
					. $this->config->lang_prefix . "_ACCESS_TO_" . $custom_button['NAME']
					. "_FAILED');";
				$method[] = Indent::_(2)
					. "\$this->setRedirect(Joomla__"."_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_"
					. $this->config->component_code_name . "&view=" . $nameListCode
					. "', false), \$message, 'error');";
				$method[] = Indent::_(2) . "return;";
				$method[] = Indent::_(1) . "}";
				// add to lang array
				$lankey = $this->config->lang_prefix . "_ACCESS_TO_"
					. $custom_button['NAME'] . "_FAILED";
				$this->language->set(
					$this->config->lang_target, $lankey,
					'Access to ' . $custom_button['link'] . ' was denied.'
				);
			}

			return implode(PHP_EOL, $method);
		}

		return $method;
	}
	/**
	 * The statement that reaches the user the button was pressed by.
	 *
	 * @return  string  The statement.
	 *
	 * @since   6.1.7
	 */
	protected function currentUser(): string
	{
		return Indent::_(2) . "\$user = Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getIdentity();";
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

