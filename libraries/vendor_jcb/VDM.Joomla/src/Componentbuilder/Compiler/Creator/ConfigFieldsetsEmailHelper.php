<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Creator;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Component;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ConfigFieldsets;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ConfigFieldsetsCustomfield as Customfield;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * Config Fieldsets Email Helper Creator Class
 * 
 * @since 3.2.0
 */
class ConfigFieldsetsEmailHelper
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 3.2.0
	 */
	protected Language $language;

	/**
	 * The Component Class.
	 *
	 * @var   Component
	 * @since 3.2.0
	 */
	protected Component $component;

	/**
	 * The ConfigFieldsets Class.
	 *
	 * @var   ConfigFieldsets
	 * @since 3.2.0
	 */
	protected ConfigFieldsets $configfieldsets;

	/**
	 * The ConfigFieldsetsCustomfield Class.
	 *
	 * @var   Customfield
	 * @since 3.2.0
	 */
	protected Customfield $customfield;

	/**
	 * Constructor.
	 *
	 * @param Config            $config            The Config Class.
	 * @param Language          $language          The Language Class.
	 * @param Component         $component         The Component Class.
	 * @param ConfigFieldsets   $configfieldsets   The ConfigFieldsets Class.
	 * @param Customfield       $customfield       The ConfigFieldsetsCustomfield Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Config $config, Language $language, Component $component,
		ConfigFieldsets $configfieldsets,
		Customfield $customfield)
	{
		$this->config = $config;
		$this->language = $language;
		$this->component = $component;
		$this->configfieldsets = $configfieldsets;
		$this->customfield = $customfield;
	}

	/**
	 * Set Email Helper Config Fieldsets
	 *
	 * @param string $lang
	 *
	 * @since 3.2.0
	 */
	public function set(string $lang): void
	{
		if ($this->component->get('add_email_helper'))
		{
			// main lang prefix
			$lang = $lang . '';

			// set main lang string
			$this->language->set(
				$this->config->lang_target, $lang . '_MAIL_CONFIGURATION', "Mail Configuration"
			);

			$this->language->set($this->config->lang_target, $lang . '_DKIM', "DKIM");

			// start building field set for email helper functions
			$this->configfieldsets->add('component', PHP_EOL . Indent::_(1) . "<fieldset");
			$this->configfieldsets->add('component', Indent::_(2)
				. "name=\"mail_configuration_custom_config\"");
			$this->configfieldsets->add('component', Indent::_(2) . "label=\"" . $lang
				. "_MAIL_CONFIGURATION\">");

			// add custom Mail Configurations
			if ($this->customfield->isArray('Mail Configuration'))
			{
				$this->configfieldsets->add('component', implode(
					"", $this->customfield->get('Mail Configuration')
				));
				$this->customfield->remove('Mail Configuration');
			}
			else
			{
				// set all the laguage strings
				$this->language->set(
					$this->config->lang_target, $lang . '_MAILONLINE_LABEL', "Mailer Status"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_MAILONLINE_DESCRIPTION',
					"Warning this will stop all emails from going out."
				);
				$this->language->set($this->config->lang_target, $lang . '_ON', "On");
				$this->language->set($this->config->lang_target, $lang . '_OFF', "Off");
				$this->language->set(
					$this->config->lang_target, $lang . '_MAILER_LABEL', "Mailer"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_MAILER_DESCRIPTION',
					"Select what mailer you would like to use to send emails."
				);
				$this->language->set($this->config->lang_target, $lang . '_GLOBAL', "Global");
				$this->language->set(
					$this->config->lang_target, $lang . '_PHP_MAIL', "PHP Mail"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SENDMAIL', "Sendmail"
				);
				$this->language->set($this->config->lang_target, $lang . '_SMTP', "SMTP");
				$this->language->set(
					$this->config->lang_target, $lang . '_EMAILFROM_LABEL', " From Email"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_EMAILFROM_DESCRIPTION',
					"The global email address that will be used to send system email."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_EMAILFROM_HINT', "Email Address Here"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_FROMNAME_LABEL', "From Name"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_FROMNAME_DESCRIPTION',
					"Text displayed in the header &quot;From:&quot; field when sending a site email. Usually the site name."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_FROMNAME_HINT', "From Name Here"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_EMAILREPLY_LABEL', " Reply to Email"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_EMAILREPLY_DESCRIPTION',
					"The global email address that will be used to set as the reply email. (leave blank for none)"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_EMAILREPLY_HINT',
					"Email Address Here"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_REPLYNAME_LABEL', "Reply to Name"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_REPLYNAME_DESCRIPTION',
					"Text displayed in the header &quot;Reply To:&quot; field when replying to the site email. Usually the the person that receives the response. (leave blank for none)"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_REPLYNAME_HINT', "Reply Name Here"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SENDMAIL_LABEL', "Sendmail Path"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SENDMAIL_DESCRIPTION',
					"Enter the path to the sendmail program directory on your host server."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SENDMAIL_HINT', "/usr/sbin/sendmail"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPAUTH_LABEL',
					"SMTP Authentication"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPAUTH_DESCRIPTION',
					"Select yes if your SMTP host requires SMTP Authentication."
				);
				$this->language->set($this->config->lang_target, $lang . '_YES', "Yes");
				$this->language->set($this->config->lang_target, $lang . '_NO', "No");
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPSECURE_LABEL', "SMTP Security"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPSECURE_DESCRIPTION',
					"Select the security model that your SMTP server uses."
				);
				$this->language->set($this->config->lang_target, $lang . '_NONE', "None");
				$this->language->set($this->config->lang_target, $lang . '_SSL', "SSL");
				$this->language->set($this->config->lang_target, $lang . '_TLS', "TLS");
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPPORT_LABEL', "SMTP Port"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPPORT_DESCRIPTION',
					"Enter the port number of your SMTP server. Use 25 for most unsecured servers and 465 for most secure servers."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPPORT_HINT', "25"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPUSER_LABEL', "SMTP Username"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPUSER_DESCRIPTION',
					"Enter the username for access to the SMTP host."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPUSER_HINT', "email@demo.com"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPPASS_LABEL', "SMTP Password"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPPASS_DESCRIPTION',
					"Enter the password for access to the SMTP host."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPHOST_LABEL', "SMTP Host"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPHOST_DESCRIPTION',
					"Enter the name of the SMTP host."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_SMTPHOST_HINT', "localhost"
				);

				// set the mailer fields
				$this->configfieldsets->add('component', PHP_EOL . Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Mailonline Field. Type: Radio. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"radio\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"mailonline\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_MAILONLINE_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_MAILONLINE_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "class=\"btn-group btn-group-yesno\"");
				$this->configfieldsets->add('component', Indent::_(3) . "default=\"1\">");
				$this->configfieldsets->add('component', Indent::_(3) . "<!--"
					. Line::_(__Line__, __Class__) . " Option Set. -->");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"1\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_ON</option>");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"0\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_OFF</option>");
				$this->configfieldsets->add('component', Indent::_(2) . "</field>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Mailer Field. Type: List. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"list\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"mailer\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_MAILER_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_MAILER_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "class=\"list_class\"");
				$this->configfieldsets->add('component', Indent::_(3) . "multiple=\"false\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"WORD\"");
				$this->configfieldsets->add('component', Indent::_(3) . "required=\"true\"");
				$this->configfieldsets->add('component', Indent::_(3) . "default=\"global\">");
				$this->configfieldsets->add('component', Indent::_(3) . "<!--"
					. Line::_(__Line__, __Class__) . " Option Set. -->");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"global\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_GLOBAL</option>");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"default\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_PHP_MAIL</option>");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"sendmail\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_SENDMAIL</option>");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"smtp\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_SMTP</option>");
				$this->configfieldsets->add('component', Indent::_(2) . "</field>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Emailfrom Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"emailfrom\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_EMAILFROM_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_EMAILFROM_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3) . "validate=\"email\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add email address here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_EMAILFROM_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp,sendmail,default\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Fromname Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"fromname\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_FROMNAME_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_FROMNAME_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add some name here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_FROMNAME_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp,sendmail,default\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Email reply to Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"replyto\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_EMAILREPLY_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_EMAILREPLY_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3) . "validate=\"email\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add email address here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_EMAILREPLY_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp,sendmail,default\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Reply to name Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "name=\"replytoname\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_REPLYNAME_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_REPLYNAME_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add some name here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_REPLYNAME_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp,sendmail,default\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Sendmail Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"sendmail\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_SENDMAIL_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_SENDMAIL_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "required=\"false\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"PATH\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add path to you local sendmail here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_SENDMAIL_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:sendmail\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Smtpauth Field. Type: Radio. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"radio\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"smtpauth\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_SMTPAUTH_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_SMTPAUTH_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "class=\"btn-group btn-group-yesno\"");
				$this->configfieldsets->add('component', Indent::_(3) . "default=\"0\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp\">");
				$this->configfieldsets->add('component', Indent::_(3) . "<!--"
					. Line::_(__Line__, __Class__) . " Option Set. -->");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"1\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_YES</option>");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"0\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_NO</option>");
				$this->configfieldsets->add('component', Indent::_(2) . "</field>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Smtpsecure Field. Type: List. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"list\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"smtpsecure\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_SMTPSECURE_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_SMTPSECURE_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "class=\"list_class\"");
				$this->configfieldsets->add('component', Indent::_(3) . "multiple=\"false\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"WORD\"");
				$this->configfieldsets->add('component', Indent::_(3) . "default=\"none\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp\">");
				$this->configfieldsets->add('component', Indent::_(3) . "<!--"
					. Line::_(__Line__, __Class__) . " Option Set. -->");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"none\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_NONE</option>");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"ssl\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_SSL</option>");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"tls\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_TLS</option>");
				$this->configfieldsets->add('component', Indent::_(2) . "</field>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Smtpport Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"smtpport\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_SMTPPORT_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "default=\"25\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_SMTPPORT_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"INT\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add the port number of your SMTP server here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_SMTPPORT_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Smtpuser Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"smtpuser\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_SMTPUSER_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_SMTPUSER_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add the username for SMTP server here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_SMTPUSER_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Smtppass Field. Type: Password. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"password\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"smtppass\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_SMTPPASS_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_SMTPPASS_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"raw\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add the password for SMTP server here.\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Smtphost Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"smtphost\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_SMTPHOST_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "default=\"localhost\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_SMTPHOST_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add the name of the SMTP host here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_SMTPHOST_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "showon=\"mailer:smtp\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
			}
			// close that fieldset
			$this->configfieldsets->add('component', Indent::_(1) . "</fieldset>");

			// start dkim field set
			$this->configfieldsets->add('component', Indent::_(1) . "<fieldset");
			$this->configfieldsets->add('component', Indent::_(2)
				. "name=\"dkim_custom_config\"");
			$this->configfieldsets->add('component', Indent::_(2) . "label=\"" . $lang
				. "_DKIM\">");
			// add custom DKIM fields
			if ($this->customfield->isArray('DKIM'))
			{
				$this->configfieldsets->add('component', implode(
					"", $this->customfield->get('DKIM')
				));
				$this->customfield->remove('DKIM');
			}
			else
			{
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_LABEL', "Enable DKIM"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_DESCRIPTION',
					"Set this option to Yes if you want to sign your emails using DKIM."
				);
				$this->language->set($this->config->lang_target, $lang . '_YES', "Yes");
				$this->language->set($this->config->lang_target, $lang . '_NO', "No");
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_DOMAIN_LABEL', "Domain"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_DOMAIN_DESCRIPTION',
					"Set the domain. Eg. domain.com"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_DOMAIN_HINT', "domain.com"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_SELECTOR_LABEL', "Selector"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_SELECTOR_DESCRIPTION',
					"Set your DKIM/DNS selector."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_SELECTOR_HINT', "vdm"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_PASSPHRASE_LABEL', "Passphrase"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_PASSPHRASE_DESCRIPTION',
					"Enter your passphrase here."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_IDENTITY_LABEL', "Identity"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_IDENTITY_DESCRIPTION',
					"Set DKIM identity. This can be in the format of an email address 'you@yourdomain.com' typically used as the source of the email."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_IDENTITY_HINT',
					"you@yourdomain.com"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_PRIVATE_KEY_LABEL',
					"Private key"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_PRIVATE_KEY_DESCRIPTION',
					"set private key"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_PUBLIC_KEY_LABEL', "Public key"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_PUBLIC_KEY_DESCRIPTION',
					"set public key"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_NOTE_DKIM_USE_LABEL',
					"Server Configuration"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_NOTE_DKIM_USE_DESCRIPTION', $this->dkimDescription()
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_KEY_LABEL', "Key"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_KEY_DESCRIPTION',
					"This is the KEY to use in the DNS record."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_KEY_HINT', "vdm._domainkey"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_VALUE_LABEL', "Value"
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_VALUE_DESCRIPTION',
					"This is the TXT value to use in the DNS. Replace the PUBLICKEY with your public key."
				);
				$this->language->set(
					$this->config->lang_target, $lang . '_DKIM_VALUE_HINT',
					"v=DKIM1;k=rsa;g=*;s=email;h=sha1;t=s;p=PUBLICKEY"
				);

				$this->configfieldsets->add('component', PHP_EOL . Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Dkim Field. Type: Radio. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"radio\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"dkim\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_DKIM_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_DKIM_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "class=\"btn-group btn-group-yesno\"");
				$this->configfieldsets->add('component', Indent::_(3) . "default=\"0\"");
				$this->configfieldsets->add('component', Indent::_(3) . "required=\"true\">");
				$this->configfieldsets->add('component', Indent::_(3) . "<!--"
					. Line::_(__Line__, __Class__) . " Option Set. -->");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"1\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_YES</option>");
				$this->configfieldsets->add('component', Indent::_(3)
					. "<option value=\"0\">");
				$this->configfieldsets->add('component', Indent::_(4) . $lang
					. "_NO</option>");
				$this->configfieldsets->add('component', Indent::_(2) . "</field>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Dkim_domain Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "name=\"dkim_domain\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_DKIM_DOMAIN_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_DKIM_DOMAIN_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add DKIM Domain here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_DKIM_DOMAIN_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3) . "showon=\"dkim:1\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "onchange=\"vdm_dkim();\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Dkim_selector Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "name=\"dkim_selector\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_DKIM_SELECTOR_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "default=\"vdm\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_DKIM_SELECTOR_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add DKIM/DNS selector here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_DKIM_SELECTOR_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3) . "showon=\"dkim:1\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "onchange=\"vdm_dkim();\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Dkim_passphrase Field. Type: Password. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"password\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "name=\"dkim_passphrase\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_DKIM_PASSPHRASE_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_DKIM_PASSPHRASE_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"raw\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add  passphrase here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "showon=\"dkim:1\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Dkim_identity Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "name=\"dkim_identity\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_DKIM_IDENTITY_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"60\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_DKIM_IDENTITY_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"raw\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add  DKIM Identity here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_DKIM_IDENTITY_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3) . "showon=\"dkim:1\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Dkim_private_key Field. Type: Textarea. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"textarea\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "name=\"dkim_private_key\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_DKIM_PRIVATE_KEY_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "rows=\"15\"");
				$this->configfieldsets->add('component', Indent::_(3) . "cols=\"5\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_DKIM_PRIVATE_KEY_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "class=\"input-xxlarge span12\"");
				$this->configfieldsets->add('component', Indent::_(3) . "showon=\"dkim:1\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Dkim_public_key Field. Type: Textarea. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"textarea\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "name=\"dkim_public_key\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_DKIM_PUBLIC_KEY_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "rows=\"5\"");
				$this->configfieldsets->add('component', Indent::_(3) . "cols=\"5\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_DKIM_PUBLIC_KEY_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "class=\"input-xxlarge span12\"");
				$this->configfieldsets->add('component', Indent::_(3) . "showon=\"dkim:1\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "onchange=\"vdm_dkim();\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Note_dkim_use Field. Type: Note. A None Database Field. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2)
					. "<field " . $this->dkimNoteAttributes() . " name=\"note_dkim_use\" label=\""
					. $lang . "_NOTE_DKIM_USE_LABEL\" description=\"" . $lang
					. "_NOTE_DKIM_USE_DESCRIPTION\" heading=\"h4\" class=\"note_dkim_use\" showon=\"dkim:1\" />");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Dkim_key Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"dkim_key\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_DKIM_KEY_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"40\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"150\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_DKIM_KEY_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add KEY here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_DKIM_KEY_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3) . "showon=\"dkim:1\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
				$this->configfieldsets->add('component', Indent::_(2) . "<!--"
					. Line::_(__Line__, __Class__)
					. " Dkim_value Field. Type: Text. (joomla) -->");
				$this->configfieldsets->add('component', Indent::_(2) . "<field");
				$this->configfieldsets->add('component', Indent::_(3) . "type=\"text\"");
				$this->configfieldsets->add('component', Indent::_(3) . "name=\"dkim_value\"");
				$this->configfieldsets->add('component', Indent::_(3) . "label=\"" . $lang
					. "_DKIM_VALUE_LABEL\"");
				$this->configfieldsets->add('component', Indent::_(3) . "size=\"80\"");
				$this->configfieldsets->add('component', Indent::_(3) . "maxlength=\"350\"");
				$this->configfieldsets->add('component', Indent::_(3) . "description=\""
					. $lang . "_DKIM_VALUE_DESCRIPTION\"");
				$this->configfieldsets->add('component', Indent::_(3) . "class=\"text_area\"");
				$this->configfieldsets->add('component', Indent::_(3) . "filter=\"STRING\"");
				$this->configfieldsets->add('component', Indent::_(3)
					. "message=\"Error! Please add TXT record here.\"");
				$this->configfieldsets->add('component', Indent::_(3) . "hint=\"" . $lang
					. "_DKIM_VALUE_HINT\"");
				$this->configfieldsets->add('component', Indent::_(3) . "showon=\"dkim:1\"");
				$this->configfieldsets->add('component', Indent::_(2) . "/>");
			}

			// close that fieldset
			$this->configfieldsets->add('component', Indent::_(1) . "</fieldset>");
		}
	}
	/**
	 * Render the DKIM note description for targets that embed its script.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function dkimDescription(): string
	{
		return '<p>Using the below details, you need to configure your DNS by adding a TXT record on your domain: <b><span id=\'a_dkim_domain\'></span></b></p>'
			. PHP_EOL . '<script>'
			. PHP_EOL . 'document.addEventListener(\'DOMContentLoaded\', function() {'
			. PHP_EOL . Indent::_(1) . 'var jformDkimDomain = document.querySelector(\'#jform_dkim_domain\');'
			. PHP_EOL . Indent::_(1) . 'if (!jformDkimDomain.value) {'
			. PHP_EOL . Indent::_(2) . 'jformDkimDomain.value = window.location.hostname;'
			. PHP_EOL . Indent::_(1) . '}'
			. PHP_EOL . Indent::_(1) . 'document.querySelector(\'#jform_dkim_key\').addEventListener(\'click\', function() {'
			. PHP_EOL . Indent::_(2) . 'this.select();'
			. PHP_EOL . Indent::_(1) . '});'
			. PHP_EOL . Indent::_(1) . 'document.querySelector(\'#jform_dkim_value\').addEventListener(\'click\', function() {'
			. PHP_EOL . Indent::_(2) . 'this.select();'
			. PHP_EOL . Indent::_(1) . '});'
			. PHP_EOL . Indent::_(1) . 'vdm_dkim();'
			. PHP_EOL . '});'
			. PHP_EOL . 'function vdm_dkim() {'
			. PHP_EOL . Indent::_(1) . 'var jformDkimDomain = document.querySelector(\'#jform_dkim_domain\');'
			. PHP_EOL . Indent::_(1) . 'document.querySelector(\'#a_dkim_domain\').textContent = jformDkimDomain.value;'
			. PHP_EOL . Indent::_(1) . 'var jformDkimKey = document.querySelector(\'#jform_dkim_key\');'
			. PHP_EOL . Indent::_(1) . 'jformDkimKey.value = document.querySelector(\'#jform_dkim_selector\').value + \'._domainkey\';'
			. PHP_EOL . Indent::_(1) . 'var jformDkimPublicKey = document.querySelector(\'#jform_dkim_public_key\').value;'
			. PHP_EOL . Indent::_(1) . 'var jformDkimValue = document.querySelector(\'#jform_dkim_value\');'
			. PHP_EOL . Indent::_(1) . 'if (!jformDkimPublicKey) {'
			. PHP_EOL . Indent::_(2) . 'jformDkimValue.value = \'v=DKIM1;k=rsa;g=*;s=email;h=sha1;t=s;p=PUBLICKEY\';'
			. PHP_EOL . Indent::_(1) . '} else {'
			. PHP_EOL . Indent::_(2) . 'jformDkimValue.value = \'v=DKIM1;k=rsa;g=*;s=email;h=sha1;t=s;p=\' + jformDkimPublicKey;'
			. PHP_EOL . Indent::_(1) . '}'
			. PHP_EOL . '}'
			. PHP_EOL . '</script>';
	}

	/**
	 * Select the native note field attributes for the target.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function dkimNoteAttributes(): string
	{
		return 'type="note"';
	}
}

