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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\ComHelperClass;


use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\ComHelperClass\CreateUserInterface;


/**
 * Helper Class Create User Class for Joomla 3
 * 
 * @since 5.0.2
 */
final class CreateUser implements CreateUserInterface
{
	/**
	 * Generates the method definition for creating or updating a user based on the provided parameters.
	 *
	 * This method returns a string representation of a PHP function that includes various 
	 * steps for handling user creation and updates, depending on the mode (site registration or admin registration).
	 * 
	 * @param   $add    Determines whether to generate the user creation method or not.
	 *                      If true, the method will be generated and returned as a string.
	 *
	 * @return  string  The generated method code as a string if $add is true. 
	 *                  Returns an empty string if $add is false.
	 */
	public function get($add): string
	{
		if ($add)
		{
			$method   = [];
			$method[] = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
			$method[] = Indent::_(1) . " * Greate user and update given table";
			$method[] = Indent::_(1) . " *";
			$method[] = Indent::_(1)
				. " * @param   array  \$credentials  Array('name' => string, 'username' => string, 'email' => string, 'password' => string, 'password2' => string)";
			$method[] = Indent::_(1) . " * @param   int    \$autologin";
			$method[] = Indent::_(1)
				. " * @param   array  \$params  Array('useractivation' => int, 'sendpassword' => int, 'allowUserRegistration' => int)";
			$method[] = Indent::_(1)
				. " * @param   array  \$mode 1 = Site Registrations; 0 = Admin Registration; 2 = Custom Helper Method Called registerUser";
			$method[] = Indent::_(1) . " *";
			$method[] = Indent::_(1)
				. " * @return  int|Error  User ID on success, or an error.";
			$method[] = Indent::_(1) . " */";
			$method[] = Indent::_(1)
				. "public static function createUser(\$credentials, \$autologin = 0,";
			$method[] = Indent::_(2) . "\$params = array(";
			$method[] = Indent::_(3)
				. "'useractivation' => 0, 'sendpassword' => 1";
			$method[] = Indent::_(2) . "), \$mode = 1";
			$method[] = Indent::_(1) . ")";
			$method[] = Indent::_(1) . "{";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Override mode";
			$method[] = Indent::_(2)
				. "if (\$mode == 2 && method_exists(__CLASS__, 'registerUser'))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Update params";
			$method[] = Indent::_(3) . "\$params['autologin'] = \$autologin;";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Now Register User";
			$method[] = Indent::_(3)
				. "return self::registerUser(\$credentials, \$params);";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "elseif (\$mode == 2)";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Fallback to Site Registrations";
			$method[] = Indent::_(3) . "\$mode = 1;";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " load the user component language files if there is an error.";
			$method[] = Indent::_(2) . "\$lang = Factory::getLanguage();";
			$method[] = Indent::_(2) . "\$extension = 'com_users';";
			$method[] = Indent::_(2) . "\$base_dir = JPATH_SITE;";
			$method[] = Indent::_(2) . "\$language_tag = 'en-GB';";
			$method[] = Indent::_(2) . "\$reload = true;";
			$method[] = Indent::_(2)
				. "\$lang->load(\$extension, \$base_dir, \$language_tag, \$reload);";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Load the correct user model.";
			$method[] = Indent::_(2) . "if (\$mode == 1) //" . Line::_(
					__LINE__,__CLASS__
				)
				. " 1 = Site Registrations";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Load the user site-registration model";
			$method[] = Indent::_(3)
				. "\$model = self::getModel('registration', \$base_dir . '/components/' . \$extension, 'Users');";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "else //" . Line::_(__Line__, __Class__)
				. " 0 = Admin Registration";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Load the backend-user model";
			$method[] = Indent::_(3)
				. "\$model = self::getModel('user', JPATH_ADMINISTRATOR . '/components/' . \$extension, 'Users');";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Check if we have params/config";
			$method[] = Indent::_(2) . "if (Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$params))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Make changes to user config";
			$method[] = Indent::_(3)
				. "foreach (\$params as \$param => \$set)";
			$method[] = Indent::_(3) . "{";
			$method[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " If you know of a better path, let me know";
			$method[] = Indent::_(4)
				. "\$params[\$param] = self::setParams(\$extension, \$param, \$set);";
			$method[] = Indent::_(3) . "}";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Set username to email if not set";
			$method[] = Indent::_(2)
				. "if (!isset(\$credentials['username']) || !Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$credentials['username']))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3)
				. "\$credentials['username'] = \$credentials['email'];";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Lineup new user data array";
			$method[] = Indent::_(2) . "\$data = array(";
			$method[] = Indent::_(3)
				. "'username' => \$credentials['username'],";
			$method[] = Indent::_(3) . "'name' => \$credentials['name'],";
			$method[] = Indent::_(3) . "'block' => 0 );";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Added details based on mode";
			$method[] = Indent::_(2) . "if (\$mode == 1) //" . Line::_(
					__LINE__,__CLASS__
				)
				. " 1 = Site-registration mode";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3)
				. "\$data['email1'] = \$credentials['email'];";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "else //" . Line::_(__Line__, __Class__)
				. " 0 = Admin-registration mode";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3)
				. "\$data['email'] = \$credentials['email'];";
			$method[] = Indent::_(3)
				. "\$data['registerDate'] = Factory::getDate()->toSql();";
			$method[] = Indent::_(2) . "}";

			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Check if password was set";
			$method[] = Indent::_(2)
				. "if (\$mode == 1 && (!isset(\$credentials['password']) || !isset(\$credentials['password2']) || !Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$credentials['password']) || !Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$credentials['password2'])))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Set random password when empty password was submitted,";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " when using the 1 = site-registration mode";
			$method[] = Indent::_(3)
				. "\$credentials['password'] = self::randomkey(8);";
			$method[] = Indent::_(3)
				. "\$credentials['password2'] = \$credentials['password'];";
			$method[] = Indent::_(2) . "}";

			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Now Add password if set";
			$method[] = Indent::_(2)
				. "if (isset(\$credentials['password']) && isset(\$credentials['password2'])  && Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$credentials['password']) && Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$credentials['password2']))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "if (\$mode == 1) //" . Line::_(
					__LINE__,__CLASS__
				)
				. " 1 = Site-registration mode";
			$method[] = Indent::_(3) . "{";
			$method[] = Indent::_(4)
				. "\$data['password1'] = \$credentials['password'];";
			$method[] = Indent::_(3) . "}";
			$method[] = Indent::_(3) . "else //" . Line::_(__Line__, __Class__)
				. " 0 = Admin-registration mode";
			$method[] = Indent::_(3) . "{";
			$method[] = Indent::_(4)
				. "\$data['password'] = \$credentials['password'];";
			$method[] = Indent::_(3) . "}";
			$method[] = Indent::_(3)
				. "\$data['password2'] = \$credentials['password2'];";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Load the group/s value if set, only for Admin Registration (\$mode == 0)";
			$method[] = Indent::_(2)
				. "if (\$mode == 0 && isset(\$credentials['groups']) && Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$credentials['groups']))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3)
				. "\$data['groups'] = \$credentials['groups'];";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Create the new user";
			$method[] = Indent::_(2) . "if (\$mode == 1) //" . Line::_(
					__LINE__,__CLASS__
				)
				. " 1 = Site-registration mode";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "\$userId = \$model->register(\$data);";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "else //" . Line::_(__Line__, __Class__)
				. " 0 = Admin-registration mode";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "\$model->save(\$data);";
			$method[] = Indent::_(3)
				. "\$userId = \$model->getState('user.id', 0);";
			$method[] = Indent::_(2) . "}";

			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Check if we have params";
			$method[] = Indent::_(2) . "if (Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$params))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Change user params/config back";
			$method[] = Indent::_(3)
				. "foreach (\$params as \$param => \$set)";
			$method[] = Indent::_(3) . "{";
			$method[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " If you know of a better path, let me know";
			$method[] = Indent::_(4)
				. "self::setParams(\$extension, \$param, \$set);";
			$method[] = Indent::_(3) . "}";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " if user is created";
			$method[] = Indent::_(2) . "if (\$userId > 0)";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Auto Login if Needed";
			$method[] = Indent::_(3)
				. "if (\$autologin && isset(\$credentials['password']))";
			$method[] = Indent::_(3) . "{";
			$method[] = Indent::_(4) . "//" . Line::_(__Line__, __Class__)
				. " Try to login";
			$method[] = Indent::_(4) . "try{";
			$method[] = Indent::_(5)
				. "Factory::getApplication()->login(\$credentials);";
			$method[] = Indent::_(4) . "} catch (\Exception \$exception){";
			$method[] = Indent::_(5) . "//" . Line::_(__Line__, __Class__)
				. " Do noting for now, may want to set redirect.";
			$method[] = Indent::_(4) . "}";
			$method[] = Indent::_(3) . "}";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Return ID";
			$method[] = Indent::_(3) . "return \$userId;";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "return \$model->getError();";
			$method[] = Indent::_(1) . "}";

			$method[] = PHP_EOL . Indent::_(1)
				. "public static function setParams(\$component,\$target,\$value)";
			$method[] = Indent::_(1) . "{";
			$method[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Get the params and set the new values";
			$method[] = Indent::_(2)
				. "\$params = ComponentHelper::getParams(\$component);";
			$method[] = Indent::_(2) . "\$was = \$params->get(\$target, null);";
			$method[] = Indent::_(2) . "if (\$was != \$value)";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "\$params->set(\$target, \$value);";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Get a new database query instance";
			$method[] = Indent::_(3) . "\$db = Factory::getDBO();";
			$method[] = Indent::_(3) . "\$query = \$db->getQuery(true);";
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Build the query";
			$method[] = Indent::_(3) . "\$query->update('#__extensions AS a');";
			$method[] = Indent::_(3)
				. "\$query->set('a.params = ' . \$db->quote((string)\$params));";
			$method[] = Indent::_(3)
				. "\$query->where('a.element = ' . \$db->quote((string)\$component));";
			$method[] = Indent::_(3);
			$method[] = Indent::_(3) . "//" . Line::_(__Line__, __Class__)
				. " Execute the query";
			$method[] = Indent::_(3) . "\$db->setQuery(\$query);";
			$method[] = Indent::_(3) . "\$db->execute();";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "return \$was;";
			$method[] = Indent::_(1) . "}";

			$method[] = PHP_EOL . Indent::_(1) . "/**";
			$method[] = Indent::_(1) . " * Update user values";
			$method[] = Indent::_(1) . " */";
			$method[] = Indent::_(1)
				. "public static function updateUser(\$new)";
			$method[] = Indent::_(1) . "{";
			$method[] = Indent::_(2)
				. "//" . Line::_(__LINE__, __CLASS__) . " load the user component language files if there is an error.";
			$method[] = Indent::_(2) . "\$lang = Factory::getLanguage();";
			$method[] = Indent::_(2) . "\$extension = 'com_users';";
			$method[] = Indent::_(2) . "\$base_dir = JPATH_ADMINISTRATOR;";
			$method[] = Indent::_(2) . "\$language_tag = 'en-GB';";
			$method[] = Indent::_(2) . "\$reload = true;";
			$method[] = Indent::_(2)
				. "\$lang->load(\$extension, \$base_dir, \$language_tag, \$reload);";
			$method[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " load the user model";
			$method[] = Indent::_(2)
				. "\$model = self::getModel('user', JPATH_ADMINISTRATOR . '/components/com_users', 'Users');";
			$method[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " Check if password was set";
			$method[] = Indent::_(2)
				. "if (isset(\$new['password']) && isset(\$new['password2']) && Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$new['password']) && Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$new['password2']))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "//" . Line::_(__LINE__, __CLASS__) . " Use the users passwords";
			$method[] = Indent::_(3) . "\$password = \$new['password'];";
			$method[] = Indent::_(3) . "\$password2 = \$new['password2'];";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " set username";
			$method[] = Indent::_(2)
				. "if (!isset(\$new['username']) || !Super_" . "__1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check(\$new['username']))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3)
				. "\$new['username'] = \$new['email'];";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " lineup update user data";
			$method[] = Indent::_(2) . "\$data = array(";
			$method[] = Indent::_(3) . "'id' => \$new['id'],";
			$method[] = Indent::_(3) . "'username' => \$new['username'],";
			$method[] = Indent::_(3) . "'name' => \$new['name'],";
			$method[] = Indent::_(3) . "'email' => \$new['email'],";
			$method[] = Indent::_(3)
				. "'password' => \$password, // First password field";
			$method[] = Indent::_(3)
				. "'password2' => \$password2, // Confirm password field";
			$method[] = Indent::_(3) . "'block' => 0 );";
			$method[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " set groups if found";
			$method[] = Indent::_(2)
				. "if (isset(\$new['groups']) && Super_" . "__0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check(\$new['groups']))";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "\$data['groups'] = \$new['groups'];";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " register the new user";
			$method[] = Indent::_(2) . "\$done = \$model->save(\$data);";
			$method[] = Indent::_(2) . "//" . Line::_(__LINE__, __CLASS__) . " if user is updated";
			$method[] = Indent::_(2) . "if (\$done)";
			$method[] = Indent::_(2) . "{";
			$method[] = Indent::_(3) . "return \$new['id'];";
			$method[] = Indent::_(2) . "}";
			$method[] = Indent::_(2) . "return \$model->getError();";
			$method[] = Indent::_(1) . "}";

			// return the help method
			return implode(PHP_EOL, $method);
		}

		return '';
	}
}

