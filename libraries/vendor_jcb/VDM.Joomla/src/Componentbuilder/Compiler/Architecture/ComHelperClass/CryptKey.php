<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    15th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\ComHelperClass;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Component;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ModelBasicField;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ModelMediumField;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ModelWhmcsField;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Structure;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Component\Whmcs;


/**
 * Crypt Key Helper Method Generation Class.
 * 
 * Generates the component helper `getCryptKey()` method, and when medium
 * encryption fields are used, the `getMediumCryptKey()` method with its
 * static key property. When the WHMCS encryption or license option is
 * active, the `whmcs.php` file is added to the build structure and its
 * encryption body and manifest filename are stored in the shared content
 * registries.
 * 
 * @since  6.1.7
 */
final class CryptKey
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Component Class.
	 *
	 * @var   Component
	 * @since 6.1.7
	 */
	protected Component $component;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * The ContentMulti Class.
	 *
	 * @var   ContentMulti
	 * @since 6.1.7
	 */
	protected ContentMulti $contentmulti;

	/**
	 * The ModelBasicField Class.
	 *
	 * @var   ModelBasicField
	 * @since 6.1.7
	 */
	protected ModelBasicField $modelbasicfield;

	/**
	 * The ModelMediumField Class.
	 *
	 * @var   ModelMediumField
	 * @since 6.1.7
	 */
	protected ModelMediumField $modelmediumfield;

	/**
	 * The ModelWhmcsField Class.
	 *
	 * @var   ModelWhmcsField
	 * @since 6.1.7
	 */
	protected ModelWhmcsField $modelwhmcsfield;

	/**
	 * The Structure Class.
	 *
	 * @var   Structure
	 * @since 6.1.7
	 */
	protected Structure $structure;

	/**
	 * The Whmcs Class.
	 *
	 * @var   Whmcs
	 * @since 6.1.7
	 */
	protected Whmcs $whmcs;

	/**
	 * Constructor.
	 *
	 * @param Config             $config             The Config Class.
	 * @param Component          $component          The Component Class.
	 * @param ContentOne         $contentone         The ContentOne Class.
	 * @param ContentMulti       $contentmulti       The ContentMulti Class.
	 * @param ModelBasicField    $modelbasicfield    The ModelBasicField Class.
	 * @param ModelMediumField   $modelmediumfield   The ModelMediumField Class.
	 * @param ModelWhmcsField    $modelwhmcsfield    The ModelWhmcsField Class.
	 * @param Structure          $structure          The Structure Class.
	 * @param Whmcs              $whmcs              The Whmcs Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config, Component $component,
		ContentOne $contentone, ContentMulti $contentmulti,
		ModelBasicField $modelbasicfield, ModelMediumField $modelmediumfield,
		ModelWhmcsField $modelwhmcsfield, Structure $structure, Whmcs $whmcs)
	{
		$this->config = $config;
		$this->component = $component;
		$this->contentone = $contentone;
		$this->contentmulti = $contentmulti;
		$this->modelbasicfield = $modelbasicfield;
		$this->modelmediumfield = $modelmediumfield;
		$this->modelwhmcsfield = $modelwhmcsfield;
		$this->structure = $structure;
		$this->whmcs = $whmcs;
	}

	/**
	 * Get the helper crypt-key method code.
	 *
	 * Always clears the `WHMCS_ENCRYPT_FILE` placeholder first. When no
	 * encryption field or license option is active an empty string is
	 * returned. Otherwise the WHMCS file is built when required, and the
	 * generated `getCryptKey()` (and possibly `getMediumCryptKey()`)
	 * method code is returned.
	 *
	 * @return  string  The generated helper method code, or an empty string.
	 *
	 * @since   6.1.7
	 */
	public function get(): string
	{
		// WHMCS_ENCRYPT_FILE
		$this->contentone->set('WHMCS_ENCRYPT_FILE', '');
		// check if encryption is ative
		if ($this->modelbasicfield->isActive()
			|| $this->modelmediumfield->isActive()
			|| $this->modelwhmcsfield->isActive()
			|| $this->component->get('add_license'))
		{
			if ($this->modelwhmcsfield->isActive()
				|| $this->component->get('add_license'))
			{
				// set whmcs encrypt file into place
				$target = array('admin' => 'whmcs');
				$done   = $this->structure->build($target, 'whmcs');
				// the text for the file WHMCS_ENCRYPTION_BODY
				$this->contentmulti->set('whmcs' . '|WHMCS_ENCRYPTION_BODY', $this->whmcs->get());
				// ENCRYPT_FILE
				$this->contentone->set('WHMCS_ENCRYPT_FILE', PHP_EOL . Indent::_(3) . "<filename>whmcs.php</filename>");
			}
			// get component name
			$component = $this->config->component_code_name;
			// set the getCryptKey function to the helper class
			$function = [];
			// start building the getCryptKey function/class method
			$function[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
			$function[] = Indent::_(1) . " *" . Indent::_(1) . "Get The Encryption Keys";
			$function[] = Indent::_(1) . " *";
			$function[] = Indent::_(1)
				. " *" . Indent::_(1) . "@param  string        \$type     The type of key";
			$function[] = Indent::_(1)
				. " *" . Indent::_(1) . "@param  string/bool   \$default  The return value if no key was found";
			$function[] = Indent::_(1) . " *";
			$function[] = Indent::_(1) . " *" . Indent::_(1) . "@return  string   On success";
			$function[] = Indent::_(1) . " *";
			$function[] = Indent::_(1) . " **/";
			$function[] = Indent::_(1)
				. "public static function getCryptKey(\$type, \$default = false)";
			$function[] = Indent::_(1) . "{";
			$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Get the global params";
			$function[] = Indent::_(2)
				. "\$params = ComponentHelper::getParams('com_" . $component
				. "', true);";
			// add the basic option
			if ($this->modelbasicfield->isActive())
			{
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Basic Encryption Type";
				$function[] = Indent::_(2) . "if ('basic' === \$type)";
				$function[] = Indent::_(2) . "{";
				$function[] = Indent::_(3)
					. "\$basic_key = \$params->get('basic_key', \$default);";
				$function[] = Indent::_(3)
					. "if (Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$basic_key))";
				$function[] = Indent::_(3) . "{";
				$function[] = Indent::_(4) . "return \$basic_key;";
				$function[] = Indent::_(3) . "}";
				$function[] = Indent::_(2) . "}";
			}
			// add the medium option
			if ($this->modelmediumfield->isActive())
			{
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Medium Encryption Type";
				$function[] = Indent::_(2) . "if ('medium' === \$type)";
				$function[] = Indent::_(2) . "{";
				$function[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " check if medium key is already loaded.";
				$function[] = Indent::_(3)
					. "if (Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(self::\$mediumCryptKey))";
				$function[] = Indent::_(3) . "{";
				$function[] = Indent::_(4)
					. "return (self::\$mediumCryptKey !== 'none') ? trim(self::\$mediumCryptKey) : \$default;";
				$function[] = Indent::_(3) . "}";
				$function[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " get the path to the medium encryption key.";
				$function[] = Indent::_(3)
					. "\$medium_key_path = \$params->get('medium_key_path', null);";
				$function[] = Indent::_(3)
					. "if (Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$medium_key_path))";
				$function[] = Indent::_(3) . "{";
				$function[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
					. " load the key from the file.";
				$function[] = Indent::_(4)
					. "if (self::getMediumCryptKey(\$medium_key_path))";
				$function[] = Indent::_(4) . "{";
				$function[] = Indent::_(5)
					. "return trim(self::\$mediumCryptKey);";
				$function[] = Indent::_(4) . "}";
				$function[] = Indent::_(3) . "}";
				$function[] = Indent::_(2) . "}";
			}
			// end the function
			$function[] = PHP_EOL . Indent::_(2) . "return \$default;";
			$function[] = Indent::_(1) . "}";
			// set the getMediumCryptKey class/method
			if ($this->modelmediumfield->isActive())
			{
				$function[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
				$function[] = Indent::_(1) . " *" . Indent::_(1) . "The Medium Encryption Key";
				$function[] = Indent::_(1) . " *";
				$function[] = Indent::_(1) . " *" . Indent::_(1) . "@var  string/bool";
				$function[] = Indent::_(1) . " **/";
				$function[] = Indent::_(1)
					. "protected static \$mediumCryptKey = false;";
				$function[] = PHP_EOL . Indent::_(1) . "/**";
				$function[] = Indent::_(1)
					. " *" . Indent::_(1) . "Get The Medium Encryption Key";
				$function[] = Indent::_(1) . " *";
				$function[] = Indent::_(1)
					. " *" . Indent::_(1) . "@param   string    \$path  The path to the medium crypt key folder";
				$function[] = Indent::_(1) . " *";
				$function[] = Indent::_(1)
					. " *" . Indent::_(1) . "@return  string    On success";
				$function[] = Indent::_(1) . " *";
				$function[] = Indent::_(1) . " **/";
				$function[] = Indent::_(1)
					. "public static function getMediumCryptKey(\$path)";
				$function[] = Indent::_(1) . "{";
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Prep the path a little";
				$function[] = Indent::_(2)
					. "\$path = '/'. trim(str_replace('//', '/', \$path), '/');";
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Check if folder exist";
				$function[] = Indent::_(2) . "if (!is_dir(\$path))";
				$function[] = Indent::_(2) . "{";
				$function[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " Lock key.";
				$function[] = Indent::_(3) . "self::\$mediumCryptKey = 'none';";
				$function[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " Set the error message.";
				$function[] = Indent::_(3)
					. "Joomla__" . "_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->enqueueMessage(Joomla__" . "_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('"
					. $this->config->lang_prefix
					. "_CONFIG_MEDIUM_KEY_PATH_ERROR'), 'Error');";
				$function[] = Indent::_(3) . "return false;";
				$function[] = Indent::_(2) . "}";
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Create FileName and set file path";
				$function[] = Indent::_(2)
					. "\$filePath = \$path.'/.'.md5('medium_crypt_key_file');";
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Check if we already have the file set";
				$function[] = Indent::_(2)
					. "if ((self::\$mediumCryptKey = @file_get_contents(\$filePath)) !== FALSE)";
				$function[] = Indent::_(2) . "{";
				$function[] = Indent::_(3) . "return true;";
				$function[] = Indent::_(2) . "}";
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Set the key for the first time";
				$function[] = Indent::_(2)
					. "self::\$mediumCryptKey = self::randomkey(128);";
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Open the key file";
				$function[] = Indent::_(2) . "\$fh = @fopen(\$filePath, 'w');";
				$function[] = Indent::_(2) . "if (!is_resource(\$fh))";
				$function[] = Indent::_(2) . "{";
				$function[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " Lock key.";
				$function[] = Indent::_(3) . "self::\$mediumCryptKey = 'none';";
				$function[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " Set the error message.";
				$function[] = Indent::_(3)
					. "Joomla__" . "_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->enqueueMessage(Joomla__" . "_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('"
					. $this->config->lang_prefix
					. "_CONFIG_MEDIUM_KEY_PATH_ERROR'), 'Error');";
				$function[] = Indent::_(3) . "return false;";
				$function[] = Indent::_(2) . "}";
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Write to the key file";
				$function[] = Indent::_(2)
					. "if (!fwrite(\$fh, self::\$mediumCryptKey))";
				$function[] = Indent::_(2) . "{";
				$function[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " Close key file.";
				$function[] = Indent::_(3) . "fclose(\$fh);";
				$function[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " Lock key.";
				$function[] = Indent::_(3) . "self::\$mediumCryptKey = 'none';";
				$function[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
					. " Set the error message.";
				$function[] = Indent::_(3)
					. "Joomla__" . "_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->enqueueMessage(Joomla__" . "_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('"
					. $this->config->lang_prefix
					. "_CONFIG_MEDIUM_KEY_PATH_ERROR'), 'Error');";
				$function[] = Indent::_(3) . "return false;";
				$function[] = Indent::_(2) . "}";
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Close key file.";
				$function[] = Indent::_(2) . "fclose(\$fh);";
				$function[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
					. " Key is set.";
				$function[] = Indent::_(2) . "return true;";
				$function[] = Indent::_(1) . "}";
			}

			// return the help methods
			return implode(PHP_EOL, $function);
		}

		return '';
	}
}

