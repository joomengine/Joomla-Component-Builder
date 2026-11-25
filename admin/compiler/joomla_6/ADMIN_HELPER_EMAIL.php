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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Mail\Mail;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * ###Component### component email helper
 *
 * Provides a complete and configurable mailer integration for Joomla components.
 * Allows for custom headers, DKIM signing, embedded images, and HTML styling.
 *
 * @since  3.0
 */
abstract class ###Component###Email
{
	/**
	 * The active recipient.
	 *
	 * @var    array<string, mixed>
	 * @since  3.0
	 */
	public static array $active = [];

	/**
	 * Mail instances container.
	 *
	 * @var    array<string, Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power>
	 * @since  1.7.3
	 */
	protected static array $instances = [];

	/**
	 * Global Configuration object.
	 *
	 * @var    Registry|null
	 * @since  5.1.1
	 */
	protected static ?Registry $gConfig = null;

	/**
	 * Component Configuration object.
	 *
	 * @var    Registry|null
	 * @since  3.0
	 */
	protected static ?Registry $config = null;

	/**
	 * Mailer object.
	 *
	 * @var    Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power|null
	 * @since  3.0
	 */
	protected static ?Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power $mailer = null;

	/**
	 * Custom email headers.
	 *
	 * @var    array<string, string>
	 * @since  3.0
	 */
	protected static array $header = [];

	/**
	 * Retrieve the component configuration.
	 *
	 * @return  Registry  Component configuration object
	 * @since   3.0
	 */
	protected static function getConfig(): Registry
	{
		return self::$config ??= Joomla___aeb8e463_291f_4445_9ac4_34b637c12dbd___Power::getParams('com_###component###');
	}

	/**
	 * Retrieve the global configuration.
	 *
	 * @return  Registry  Global configuration object
	 * @since   3.0
	 */
	protected static function getGlobalConfig(): Registry
	{
		return self::$gConfig ??= Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getConfig();
	}

	/**
	 * Get or create a Mailer instance.
	 *
	 * @return  Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power  A cloned Mail object instance
	 * @since   3.0
	 */
	public static function getMailer(): Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power
	{
		return self::$mailer ??= self::createMailer();
	}

	/**
	 * Validate an email address using a selected pattern or callable.
	 *
	 * @param   string               $address        Email address to validate.
	 * @param   string|callable|null $patternselect  Validation pattern or callable.
	 * * `auto` Pick best pattern automatically;
	 * * `pcre8` Use the squiloople.com pattern, requires PCRE > 8.0, PHP >= 5.3.2, 5.2.14;
	 * * `pcre` Use old PCRE implementation;
	 * * `php` Use PHP built-in FILTER_VALIDATE_EMAIL;
	 * * `html5` Use the pattern given by the HTML5 spec for 'email' type form input elements.
	 * * `noregex` Don't use a regex: super fast, really dumb.
	 * Alternatively you may pass in a callable to inject your own validator, for example:
	 * PHPMailer::validateAddress('user@example.com', function($address) {
	 *     return (strpos($address, '@') !== false);
	 * });
	 * You can also set the PHPMailer::$validator static to a callable, allowing built-in methods to use your validator.
	 *
	 * @return  bool  True if valid, false otherwise
	 * @since   3.0
	 */
	public static function validateAddress(string $address, $patternselect = null): bool
	{
		return self::getMailer()->validateAddress($address, $patternselect);
	}

	/**
	 * Set a custom email header (sanitized).
	 *
	 * @param   string  $key    Header name.
	 * @param   string  $value  Header value.
	 *
	 * @return  void
	 * @since   3.0
	 */
	public static function setHeader(string $key, string $value): void
	{
		$cleanKey   = preg_replace('/[^A-Za-z0-9\-]/', '', $key) ?? '';
		$cleanValue = str_replace(["\r", "\n"], '', $value);

		if ($cleanKey !== '')
		{
			self::$header[$cleanKey] = $cleanValue;
		}
	}

	/**
	 * Get or create a Mail instance with specific configuration.
	 *
	 * @param   string  $id          Instance ID.
	 * @param   bool    $exceptions  Enable exceptions.
	 *
	 * @return  Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power  Configured Mail instance
	 * @since   5.1.1
	 */
	public static function getInstance(string $id = 'Joomla', bool $exceptions = true): Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power
	{
		if (!isset(self::$instances[$id]))
		{
			$config = clone self::getGlobalConfig();
			$config->set('throw_exceptions', $exceptions);

			/** @var Joomla___3e2779e9_b33f_42b8_a13b_53f08d99f15b___Power $factory */
			$factory = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->get(Joomla___3e2779e9_b33f_42b8_a13b_53f08d99f15b___Power::class);
			self::$instances[$id] = $factory->createMailer($config);
		}

		return self::$instances[$id];
	}

	/**
	 * Create a configured Mail instance.
	 *
	 * @return  Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power  The created Mail object with sender, reply-to and transport settings.
	 * @since   3.0
	 */
	protected static function createMailer(): Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power
	{
		$conf   = self::getConfig();
		$mailer = (string) $conf->get('mailer', 'global');
		$mail   = self::getInstance();

		if ($mailer === 'global')
		{
			$global = self::getGlobalConfig();
			$mailer = (string) $global->get('mailer');

			$params = [
				'smtpauth'     => $global->get('smtpauth') ? 1 : null,
				'smtpuser'     => $global->get('smtpuser'),
				'smtppass'     => $global->get('smtppass'),
				'smtphost'     => $global->get('smtphost'),
				'smtpsecure'   => $global->get('smtpsecure'),
				'smtpport'     => $global->get('smtpport'),
				'sendmail'     => $global->get('sendmail'),
				'from'         => $global->get('mailfrom'),
				'name'         => $global->get('fromname'),
				'replyto'      => $global->get('replyto'),
				'replytoname'  => $global->get('replytoname'),
			];
		}
		else
		{
			$params = [
				'smtpauth'     => $conf->get('smtpauth') ? 1 : null,
				'smtpuser'     => $conf->get('smtpuser'),
				'smtppass'     => $conf->get('smtppass'),
				'smtphost'     => $conf->get('smtphost'),
				'smtpsecure'   => $conf->get('smtpsecure'),
				'smtpport'     => $conf->get('smtpport'),
				'sendmail'     => $conf->get('sendmail'),
				'from'         => $conf->get('emailfrom'),
				'name'         => $conf->get('fromname'),
				'replyto'      => $conf->get('replyto'),
				'replytoname'  => $conf->get('replytoname'),
			];
		}

		$mail->setSender([$params['from'], $params['name']]);

		if (!empty($params['replyto']) && !empty($params['replytoname']))
		{
			$mail->ClearReplyTos();
			$mail->addReplyTo($params['replyto'], $params['replytoname']);
		}

		switch ($mailer)
		{
			case 'smtp':
				$mail->useSMTP(
					$params['smtpauth'],
					$params['smtphost'],
					$params['smtpuser'],
					$params['smtppass'],
					$params['smtpsecure'],
					$params['smtpport']
				);
				break;
			case 'sendmail':
				$mail->useSendmail($params['sendmail']);
				$mail->IsSendmail();
				break;
			default:
				$mail->IsMail();
		}

		return $mail;
	}

	/**
	 * Compose and send an email with full options including attachments, HTML, DKIM, and reply-to support.
	 *
	 * @param   array|string       $recipient    Email or list of recipients.
	 * @param   string             $subject      Subject line.
	 * @param   string             $body         HTML body.
	 * @param   string|null        $textonly     Optional plain text fallback.
	 * @param   int                $mode         1 = HTML, 0 = plain text.
	 * @param   string|null        $bounce_email Optional bounce email address.
	 * @param   string|null        $idsession    Optional message tracking tag.
	 * @param   string|array|null  $mailreply    Optional reply-to address(es).
	 * @param   string|array|null  $replyname    Optional reply-to name(s).
	 * @param   string|null        $mailfrom     Optional sender email override.
	 * @param   string|null        $fromname     Optional sender name override.
	 * @param   array|null         $cc           CC recipients.
	 * @param   array|null         $bcc          BCC recipients.
	 * @param   array|string|null  $attachment   Attachments.
	 * @param   array|string|null  $embeds       Embedded image definitions.
	 *
	 * @return  bool  True on success, false on failure.
	 * @since   3.0
	 */
	public static function send(
		$recipient,
		string $subject,
		string $body,
		?string $textonly,
		int $mode = 0,
		?string $bounce_email = null,
		?string $idsession = null,
		$mailreply = null,
		$replyname = null,
		?string $mailfrom = null,
		?string $fromname = null,
		?array $cc = null,
		?array $bcc = null,
		$attachment = null,
		$embeds = null
	): bool {
		$mail = self::getMailer();
		$conf = self::getConfig();

		$sent = false;
		$tmp = null;

		try
		{
			if ($mailfrom)
			{
				$mail->setSender($mailfrom, (string) ($fromname ?? ''));
			}

			if ($bounce_email)
			{
				$mail->Sender = $bounce_email;
			}

			if ($idsession)
			{
				$mail->addCustomHeader('X-VDMmethodID:' . $idsession);
			}

			foreach (self::$header as $key => $val)
			{
				$mail->addCustomHeader($key . ':' . $val);
			}

			$mail->setSubject(str_replace(["\r", "\n"], '', $subject));
			$mail->setBody($body);

			if ($mode)
			{
				$mail->isHTML(true);
				$mail->AltBody = $textonly ?? strip_tags($body);
			}

			// Recipients
			self::applyRecipient($mail, $recipient, 'to');
			self::applyRecipient($mail, $cc, 'cc');
			self::applyRecipient($mail, $bcc, 'bcc');

			// Reply-to
			if (!empty($mailreply))
			{
				self::applyRecipient($mail, $mailreply, 'reply', $replyname);
			}

			// EmbeddedImage
			self::applyEmbeds($mail, $embeds);

			// Attachments
			self::applyAttachments($mail, $attachment);

			// DKIM
			if (
				$conf->get('enable_dkim') &&
				($domain = $conf->get('dkim_domain')) &&
				($selector = $conf->get('dkim_selector')) &&
				($privateKey = $conf->get('dkim_private'))
			) {
				$mail->DKIM_domain     = $domain;
				$mail->DKIM_selector   = $selector;
				$mail->DKIM_identity   = $conf->get('dkim_identity') ?: $domain;
				$mail->DKIM_passphrase = $conf->get('dkim_passphrase');

				$tmp = tempnam(sys_get_temp_dir(), 'VDM');
				if ($tmp === false || file_put_contents($tmp, $privateKey) === false)
				{
					throw new \RuntimeException('Failed to create temporary DKIM private key file.');
				}

				$mail->DKIM_private = $tmp;
			}

			$mail->send();
			$sent = true;
		} catch (\Throwable $e) {
			$sent = false;
			Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getLogger()->error('Email send failed: ' . $e->getMessage());
		} finally {
			if ($tmp && file_exists($tmp))
			{
				@unlink($tmp);
			}
		}

		if (\method_exists('###Component###Helper', 'storeMessage'))
		{
			$data = self::$active[$recipient] ?? $recipient;
			###Component###Helper::storeMessage($sent, $data, $subject, $body, $textonly, $mode, 'email');
			unset(self::$active[$recipient]);
		}

		return $sent;
	}

	/**
	 * Build a complete minimal HTML email body with basic headers.
	 * Use <br /> instead of <p> for layout consistency in emails.
	 *
	 * @param   string  $html     Body HTML content.
	 * @param   string  $subject  Email subject/title used in the <title> tag.
	 *
	 * @return  string  Full HTML email body.
	 * @since   3.0
	 */
	public static function setBasicBody(string $html, string $subject): string
	{
		return implode("\n", [
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
			'<html xmlns="http://www.w3.org/1999/xhtml">',
			'<head>',
			'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
			'<meta name="viewport" content="width=device-width, initial-scale=1.0"/>',
			'<title>' . htmlspecialchars($subject) . '</title>',
			'<style type="text/css">',
			'#outlook a {padding:0;} .ExternalClass {width:100%;} .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}',
			// 'p {margin: 0; padding: 0; font-size: 0px; line-height: 0px;}',
			'table, table td {border-collapse: collapse;}',
			'img {display:block; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;}',
			'a img {border:none;} a {text-decoration:none; color:#000001;} a.phone {pointer-events:auto; cursor:default; color:#000001 !important;}',
			'span {font-size:13px; line-height:17px; font-family:monospace; color:#000001;}',
			'</style>',
			'<!--[if gte mso 9]><style>/* Target Outlook 2007 and 2010 */</style><![endif]-->',
			'</head>',
			'<body style="width:100%; margin:0; padding:0; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">',
			$html,
			'</body>',
			'</html>'
		]);
	}

	/**
	 * Build a styled HTML email with outer table formatting for wide layout support.
	 * Suitable for rich content emails that need outer table structure.
	 *
	 * @param   string  $html     Inner body HTML content.
	 * @param   string  $subject  Email subject/title used in the <title> tag.
	 *
	 * @return  string  Complete HTML email content.
	 * @since   3.0
	 */
	public static function setTableBody(string $html, string $subject): string
	{
		return implode("\n", [
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
			'<html xmlns="http://www.w3.org/1999/xhtml">',
			'<head>',
			'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
			'<meta name="viewport" content="width=device-width, initial-scale=1.0"/>',
			'<title>' . htmlspecialchars($subject) . '</title>',
			'<style type="text/css">',
			'#outlook a {padding:0;} .ExternalClass {width:100%;} .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}',
			// 'p {margin: 0; padding: 0; font-size: 0px; line-height: 0px;}',
			'table, table td {border-collapse: collapse;}',
			'img {display:block; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic;}',
			'a img {border:none;} a {text-decoration:none; color:#000001;} a.phone {pointer-events:auto; cursor:default; color:#000001 !important;}',
			'span {font-size:13px; line-height:17px; font-family:monospace; color:#000001;}',
			'</style>',
			'<!--[if gte mso 9]><style>/* Target Outlook 2007 and 2010 */</style><![endif]-->',
			'</head>',
			'<body style="width:100%; margin:0; padding:0; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">',
			'<table cellpadding="0" cellspacing="0" border="0" width="100%" style="line-height:100% !important;">',
			'<tr><td valign="top">',
			'<table cellpadding="0" cellspacing="0" border="0" align="center" width="800">',
			'<tr><td valign="top">',
			'<table cellpadding="0" cellspacing="0" border="0" align="center" width="780">',
			'<tr><td valign="top" style="vertical-align:top;">',
			$html,
			'</td></tr></table>',
			'</td></tr></table>',
			'</td></tr></table>',
			'</body>',
			'</html>'
		]);
	}

	/**
	 * Apply a set of recipients (TO, CC, BCC, or REPLY-TO) to the given mailer instance.
	 *
	 * This method normalizes input into individual address/name pairs and safely
	 * calls the corresponding MailerInterface methods:
	 *  - 'to'    → addRecipient()
	 *  - 'cc'    → addCc()
	 *  - 'bcc'   → addBcc()
	 *  - 'reply' → addReplyTo()
	 *
	 * Accepted input formats:
	 *  - string  A single email address.
	 *  - array<string>  A list of email addresses.
	 *  - array<array{email:string,name?:string}>  A list of associative arrays.
	 *  - mixed keys will be matched with $names if provided.
	 *
	 * @param  MailerInterface  $mail   The mailer instance to apply recipients to.
	 * @param  mixed            $input  The recipient data (string or array).
	 * @param  string           $type   The recipient type: 'to', 'cc', 'bcc', or 'reply'. Defaults to 'to'.
	 * @param  mixed|null       $names  Optional parallel names array or string for labeling recipients.
	 *
	 * @return void
	 * @since  5.1.5
	 */
	protected static function applyRecipient(MailerInterface $mail, $input, string $type = 'to', $names = null): void
	{
		if (empty($input))
		{
			return;
		}

		$list = is_array($input) ? $input : [$input];

		foreach ($list as $key => $item)
		{
			$email = '';
			$name  = '';

			if (is_array($item))
			{
				$email = (string) ($item['email'] ?? reset($item));
				$name  = (string) ($item['name'] ?? '');
			}
			elseif (is_string($item))
			{
				$email = $item;
				if (is_array($names) && isset($names[$key]))
				{
					$name = (string) $names[$key];
				}
				elseif (is_string($names))
				{
					$name = $names;
				}
			}

			if ($email === '')
			{
				continue;
			}

			switch ($type)
			{
				case 'cc':
					$mail->addCc($email, $name);
					break;
				case 'bcc':
					$mail->addBcc($email, $name);
					break;
				case 'reply':
					$mail->addReplyTo($email, $name);
					break;
				default:
					$mail->addRecipient($email, $name);
			}
		}
	}

	/**
	 * Apply embedded images to the email.
	 *
	 * Accepts string paths, associative arrays, or objects containing
	 * `Path` and optional `FileName` properties.
	 *
	 * @param  Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power  $mail    The mailer instance to apply embeds to.
	 * @param  mixed            $embeds  A single embed or list of embeds (string|array|object).
	 *
	 * @return void
	 * @throws \Exception
	 * @since  5.1.4
	 */
	protected static function applyEmbeds(Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power $mail, $embeds): void
	{
        $mail->clearAttachments();

		if (empty($embeds))
		{
			return;
		}

		$list = is_array($embeds) ? $embeds : [$embeds];

		foreach ($list as $embed)
		{
			if (is_string($embed) && file_exists($embed))
			{
				$mail->addEmbeddedImage($embed, basename($embed));
			}
			elseif (is_array($embed) && isset($embed['Path']) && file_exists($embed['Path']))
			{
				$mail->addEmbeddedImage($embed['Path'], $embed['FileName'] ?? basename($embed['Path']));
			}
			elseif (is_object($embed) && isset($embed->Path) && file_exists($embed->Path))
			{
				$mail->addEmbeddedImage($embed->Path, $embed->FileName ?? basename($embed->Path));
			}
		}
	}

	/**
	 * Apply file attachments to the email.
	 *
	 * Accepts string paths, associative arrays, or objects containing
	 * `Path` and optional `FileName` properties.
	 *
	 * @param  Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power  $mail         The mailer instance to apply attachments to.
	 * @param  mixed            $attachment   A single file or list of files (string|array|object).
	 *
	 * @return void
	 * @throws  \InvalidArgumentException
	 * @since  5.1.4
	 */
	protected static function applyAttachments(Joomla___890fd6b1_0127_4f35_9b6e_ee6f2dc61bcc___Power $mail, $attachment): void
	{
        if (empty($attachment))
		{
			return;
		}

		$list = is_array($attachment) ? $attachment : [$attachment];

		foreach ($list as $file)
		{
			if (is_string($file) && file_exists($file))
			{
				$mail->addAttachment($file, basename($file));
			}
			elseif (is_array($file) && isset($file['Path']) && file_exists($file['Path']))
			{
				$mail->addAttachment($file['Path'], $file['FileName'] ?? basename($file['Path']));
			}
			elseif (is_object($file) && isset($file->Path) && file_exists($file->Path))
			{
				$mail->addAttachment($file->Path, $file->FileName ?? basename($file->Path));
			}
		}
	}
}
