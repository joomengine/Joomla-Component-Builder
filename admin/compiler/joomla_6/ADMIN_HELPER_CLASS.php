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

###POWER_AUTOLOADER###

###ADMIN_HELPER_CLASS_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### component helper.
 *
 * @since   3.0
 */
abstract class ###Component###Helper
{
	/**
	 * Composer Switch
	 *
	 * @var      array
	 */
	protected static $composer = [];

	/**
	 * The Main Active Language
	 *
	 * @var      string
	 */
	public static $langTag;###ADMIN_GLOBAL_EVENT_HELPER######CUSTOM_HELPER_SCRIPT######BOTH_CUSTOM_HELPER_SCRIPT###

	/**
	 * Load the Composer Vendors
	 */
	public static function composerAutoload($target)
	{
		// insure we load the composer vendor only once
		if (!isset(self::$composer[$target]))
		{
			// get the function name
			$functionName = Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe('compose' . $target);
			// check if method exist
			if (method_exists(__CLASS__, $functionName))
			{
				return self::{$functionName}();
			}
			return false;
		}
		return self::$composer[$target];
	}

	/**
	 * Load the Component xml manifest.
	 */
	public static function manifest()
	{
		$manifestUrl = JPATH_ADMINISTRATOR."/components/com_###component###/###component###.xml";
		return simplexml_load_file($manifestUrl);
	}

	/**
	 * Joomla version object
	 */
	protected static $JVersion;

	/**
	 * set/get Joomla version
	 */
	public static function jVersion()
	{
		// check if set
		if (!Super___91004529_94a9_4590_b842_e7c6b624ecf5___Power::check(self::$JVersion))
		{
			self::$JVersion = new Version();
		}
		return self::$JVersion;
	}

	/**
	 * Load the Contributors details.
	 */
	public static function getContributors()
	{
		// get params
		$params    = Joomla___aeb8e463_291f_4445_9ac4_34b637c12dbd___Power::getParams('com_###component###');
		// start contributors array
		$contributors = [];
		// get all Contributors (max 20)
		$searchArray = range('0','20');
		foreach($searchArray as $nr)
		{
			if ((NULL !== $params->get("showContributor".$nr)) && ($params->get("showContributor".$nr) == 1 || $params->get("showContributor".$nr) == 3))
			{
				// set link based of selected option
				if($params->get("useContributor".$nr) == 1)
				{
					$link_front = '<a href="mailto:'.$params->get("emailContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
				elseif($params->get("useContributor".$nr) == 2)
				{
					$link_front = '<a href="'.$params->get("linkContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
				else
				{
					$link_front = '';
					$link_back = '';
				}
				$contributors[$nr]['title']   = Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::html($params->get("titleContributor".$nr));
				$contributors[$nr]['name']    = $link_front.Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::html($params->get("nameContributor".$nr)).$link_back;
			}
		}
		return $contributors;
	}###HELP###

	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu)
	{
		// load user for access menus
		$user = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getIdentity();
		// load the submenus to sidebar
		###SUBMENU###
	}###HELPER_CREATEUSER######HELPER_UIKIT######HELPER_EXEL###

	/**
	 * Get a Variable
	 *
	 * @param   string   $table        The table from which to get the variable
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 *
	 * @return  mix string/int/float
	 * @deprecated 3.3 Use Super___db87c339_5bb6_4291_a7ef_2c48ea1b06bc___Power::var(...);
	 */
	public static function getVar($table, $where = null, $whereString = 'user', $what = 'id', $operator = '=', $main = '###component###')
	{
		return Super___db87c339_5bb6_4291_a7ef_2c48ea1b06bc___Power::var(
			$table,
			$where,
			$whereString,
			$what,
			$operator,
			$main
		);
	}

	/**
	 * Get array of variables
	 *
	 * @param   string   $table        The table from which to get the variables
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 * @param   bool     $unique       The switch to return a unique array
	 *
	 * @return  array
	 * @deprecated 3.3 Use Super___db87c339_5bb6_4291_a7ef_2c48ea1b06bc___Power::vars(...);
	 */
	public static function getVars($table, $where = null, $whereString = 'user', $what = 'id', $operator = 'IN', $main = '###component###', $unique = true)
	{
		return Super___db87c339_5bb6_4291_a7ef_2c48ea1b06bc___Power::vars(
			$table,
			$where,
			$whereString,
			$what,
			$operator,
			$main,
			$unique
		);
	}

	/**
	 * Convert a json object to a string
	 *
	 * @input    string  $value  The json string to convert
	 *
	 * @returns a string
	 * @deprecated 3.3 Use Super___4b225c51_d293_48e4_b3f6_5136cf5c3f18___Power::string(...);
	 */
	public static function jsonToString($value, $sperator = ", ", $table = null, $id = 'id', $name = 'name')
	{
		return Super___4b225c51_d293_48e4_b3f6_5136cf5c3f18___Power::string(
			$value,
			$sperator,
			$table,
			$id,
			$name
		);
	}

	public static function isPublished($id,$type)
	{
		if ($type == 'raw')
		{
			$type = 'item';
		}
		$db = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->get(DatabaseInterface::class);
		$query = $db->createQuery();
		$query->select(array('a.published'));
		$query->from('#__###component###_'.$type.' AS a');
		$query->where('a.id = '. (int) $id);
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		{
			return true;
		}
		return false;
	}

	public static function getGroupName($id)
	{
		$db = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->get(DatabaseInterface::class);
		$query = $db->createQuery();
		$query->select(array('a.title'));
		$query->from('#__usergroups AS a');
		$query->where('a.id = '. (int) $id);
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		  {
			return $db->loadResult();
		}
		return $id;
	}

	/**
	 * Get the permitted actions of a user.
	 *
	 * @param  string   $view        The related view name
	 * @param  ?object  $record      The item to act upon
	 * @param  ?string  $views       The related list view name
	 * @param  mixed    $target      Only get this permission (like edit, create, delete)
	 * @param  string   $component   The target component
	 * @param  object   $user        The user whose permissions we are loading
	 *
	 * @return  object   The Registry of permission/authorised actions
	 * @since   2.5.0
	 *
	 * @deprecated 5.1.4 Use Super___7d95ce74_53dc_4672_bd8a_3b71cdacabea___Power::get(...);
	 */
	public static function getActions($view, &$record = null, $views = null, $target = null, $component = '###component###', $user = 'null')
	{
		return Super___7d95ce74_53dc_4672_bd8a_3b71cdacabea___Power::get($view, $record, $views, $target, $component, $user);
	}

	/**
	 * Returns any Model object.
	 *
	 * @param   string  $type       The model type to instantiate
	 * @param   string  $prefix     Prefix for the model class name. Optional.
	 * @param   string  $component  Component name the model belongs to. Optional.
	 * @param   array   $config     Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @throws \Exception
	 * @since   4.4
	 */
	public static function getModel(string $type, string $prefix = 'Administrator',
		string $component = '###component###', array $config = [])
	{
		// make sure the name is correct
		$type = Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe($type, 'F');
		$component = strtolower($component);

		if ($prefix !== 'Site' && $prefix !== 'Administrator')
		{
			$prefix = self::getPrefixFromModelPath($prefix);
		}

		// Get the model through the MVCFactory
		$app = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$model = $app->bootComponent('com_' . $component)->getMVCFactory()->createModel($type, $prefix, $config);

		if ($model instanceof \Joomla\CMS\User\CurrentUserInterface)
		{
			$model->setCurrentUser($app->getIdentity() ?? new \Joomla\CMS\User\User());
		}

		return $model;
	}

	/**
	 * Get the prefix from the model path
	 *
	 * @param   string  $path    The model path
	 *
	 * @return  string  The prefix value
	 * @since    4.4
	 */
	protected static function getPrefixFromModelPath(string $path): string
	{
		// Check if $path starts with JPATH_ADMINISTRATOR path
		if (str_starts_with($path, JPATH_ADMINISTRATOR . '/components/'))
		{
			return 'Administrator';
		}
		// Check if $path starts with JPATH_SITE path
		elseif (str_starts_with($path, JPATH_SITE . '/components/'))
		{
			return 'Site';
		}

		return 'Administrator';
	}

	/**
	 * Add to asset Table
	 */
	public static function setAsset($id, $table, $inherit = true)
	{
		$db = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->get(DatabaseInterface::class);

		try
		{
			$parent = new \Joomla\CMS\Table\Asset($db);
			$parent->setUseExceptions(true);
			$parent->loadByName('com_###component###');

			$parentId = $parent->id;
			$name = 'com_###component###.' . $table . '.' . $id;
			$asset = new \Joomla\CMS\Table\Asset($db);
			$asset->setUseExceptions(true);
			$asset->loadByName($name);

			// Specify how a new or moved node asset is inserted into the tree.
			if ($asset->parent_id != $parentId)
			{
				$asset->setLocation($parentId, 'last-child');
			}

			$asset->parent_id = $parentId;
			$asset->name = $name;
			$asset->title = '';
			$rules = self::getDefaultAssetRules('com_###component###', $table, $inherit);

			if ($rules instanceof AccessRules)
			{
				$asset->rules = (string) $rules;
			}

			if (!$asset->check() || !$asset->store())
			{
				throw new \RuntimeException(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', $name));
			}

			// Create an asset_id or heal one that is corrupted.
			$object = new \stdClass();
			$object->id = $id;
			$object->asset_id = (int) $asset->id;

			return $db->updateObject('#__###component###_' . $table, $object, 'id');
		}
		catch (\Exception $e)
		{
			Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->enqueueMessage($e->getMessage(), 'warning');

			return false;
		}
	}

	/**
	 * Gets the default asset Rules for a component/view.
	 */
	protected static function getDefaultAssetRules($component, $view, $inherit = true)
	{
		// if new or inherited
		$assetId = 0;
		// Only get the actual item rules if not inheriting
		if (!$inherit)
		{
			// Need to find the asset id by the name of the component.
			$db = Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getContainer()->get(DatabaseInterface::class);
			$query = $db->createQuery()
				->select($db->quoteName('id'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('name') . ' = ' . $db->quote($component));
			$db->setQuery($query);
			$db->execute();
			// check that there is a value
			if ($db->getNumRows())
			{
				// asset already set so use saved rules
				$assetId = (int) $db->loadResult();
			}
		}
		// get asset rules
		$result =  Access::getAssetRules($assetId);
		if ($result instanceof AccessRules)
		{
			$_result = (string) $result;
			$_result = json_decode($_result);
			foreach ($_result as $name => &$rule)
			{
				$v = explode('.', $name);
				if ($view !== $v[0])
				{
					// remove since it is not part of this view
					unset($_result->$name);
				}
				elseif ($inherit)
				{
					// clear the value since we inherit
					$rule = [];
				}
			}
			// check if there are any view values remaining
			if (count((array) $_result))
			{
				$_result = json_encode($_result);
				$_result = array($_result);
				// Instantiate and return the AccessRules object for the asset rules.
				$rules = new AccessRules($_result);
				// return filtered rules
				return $rules;
			}
		}
		return $result;
	}

	/**
	 * xmlAppend
	 *
	 * @param   SimpleXMLElement   $xml      The XML element reference in which to inject a comment
	 * @param   mixed              $node     A SimpleXMLElement node to append to the XML element reference, or a stdClass object containing a comment attribute to be injected before the XML node and a fieldXML attribute containing a SimpleXMLElement
	 *
	 * @return  void
	 * @deprecated 3.3 Use Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::append($xml, $node);
	 */
	public static function xmlAppend(&$xml, $node)
	{
		Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::append($xml, $node);
	}

	/**
	 * xmlComment
	 *
	 * @param   SimpleXMLElement   $xml        The XML element reference in which to inject a comment
	 * @param   string             $comment    The comment to inject
	 *
	 * @return  void
	 * @deprecated 3.3 Use Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::comment($xml, $comment);
	 */
	public static function xmlComment(&$xml, $comment)
	{
		Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::comment($xml, $comment);
	}

	/**
	 * xmlAddAttributes
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $attributes   The attributes to apply to the XML element
	 *
	 * @return  null
	 * @deprecated 3.3 Use Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::attributes($xml, $attributes);
	 */
	public static function xmlAddAttributes(&$xml, $attributes = [])
	{
		Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::attributes($xml, $attributes);
	}

	/**
	 * xmlAddOptions
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $options      The options to apply to the XML element
	 *
	 * @return  void
	 * @deprecated 3.3 Use Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::options($xml, $options);
	 */
	public static function xmlAddOptions(&$xml, $options = [])
	{
		Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::options($xml, $options);
	}

	/**
	 * get the field object
	 *
	 * @param   array      $attributes   The array of attributes
	 * @param   string     $default      The default of the field
	 * @param   array      $options      The options to apply to the XML element
	 *
	 * @return  object
	 * @deprecated 3.3 Use Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::field($attributes, $default, $options);
	 */
	public static function getFieldObject(&$attributes, $default = '', $options = null)
	{
		return Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::field($attributes, $default, $options);
	}

	/**
	 * get the field xml
	 *
	 * @param   array      $attributes   The array of attributes
	 * @param   array      $options      The options to apply to the XML element
	 *
	 * @return  object
	 * @deprecated 3.3 Use Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::xml($attributes, $options);
	 */
	public static function getFieldXML(&$attributes, $options = null)
	{
		return Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::xml($attributes, $options);
	}

	/**
	 * Render Bool Button
	 *
	 * @param   array   $args   All the args for the button
	 *                             0) name
	 *                             1) additional (options class) // not used at this time
	 *                             2) default
	 *                             3) yes (name)
	 *                             4) no (name)
	 *
	 * @return  string    The input html of the button
	 *
	 */
	public static function renderBoolButton()
	{
		$args = func_get_args();
		// check if there is additional button class
		$additional = isset($args[1]) ? (string) $args[1] : ''; // not used at this time
		// button attributes
		$buttonAttributes = array(
			'type' => 'radio',
			'name' => isset($args[0]) ? Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::html($args[0]) : 'bool_button',
			'label' => isset($args[0]) ? Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe(Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::html($args[0]), 'Ww') : 'Bool Button', // not seen anyway
			'class' => 'btn-group',
			'filter' => 'INT',
			'default' => isset($args[2]) ? (int) $args[2] : 0);
		// set the button options
		$buttonOptions = array(
			'1' => isset($args[3]) ? Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::html($args[3]) : 'JYES',
			'0' => isset($args[4]) ? Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::html($args[4]) : 'JNO');
		// return the input
		return Super___1198aecf_84c6_45d2_aea8_d531aa4afdfa___Power::field($buttonAttributes, $buttonAttributes['default'], $buttonOptions)->input;
	}

	/**
	 * Check if have an json string
	 *
	 * @input    string   The json string to check
	 *
	 * @returns bool true on success
	 * @deprecated 3.3 Use Super___4b225c51_d293_48e4_b3f6_5136cf5c3f18___Power::check($string);
	 */
	public static function checkJson($string)
	{
		return Super___4b225c51_d293_48e4_b3f6_5136cf5c3f18___Power::check($string);
	}

	/**
	 * Check if have an object with a length
	 *
	 * @input    object   The object to check
	 *
	 * @returns bool true on success
	 * @deprecated 3.3 Use Super___91004529_94a9_4590_b842_e7c6b624ecf5___Power::check($object);
	 */
	public static function checkObject($object)
	{
		return Super___91004529_94a9_4590_b842_e7c6b624ecf5___Power::check($object);
	}

	/**
	 * Check if have an array with a length
	 *
	 * @input    array   The array to check
	 *
	 * @returns bool/int  number of items in array on success
	 * @deprecated 3.3 Use Super___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check($array, $removeEmptyString);
	 */
	public static function checkArray($array, $removeEmptyString = false)
	{
		return Super___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check($array, $removeEmptyString);
	}

	/**
	 * Check if have a string with a length
	 *
	 * @input    string   The string to check
	 *
	 * @returns bool true on success
	 * @deprecated 3.3 Use Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($string);
	 */
	public static function checkString($string)
	{
		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($string);
	}

	/**
	 * Check if we are connected
	 * Thanks https://stackoverflow.com/a/4860432/1429677
	 *
	 * @returns bool true on success
	 */
	public static function isConnected()
	{
		// If example.com is down, then probably the whole internet is down, since IANA maintains the domain. Right?
		$connected = @fsockopen("www.example.com", 80);
		// website, port  (try 80 or 443)
		if ($connected)
		{
			//action when connected
			$is_conn = true;
			fclose($connected);
		}
		else
		{
			//action in connection failure
			$is_conn = false;
		}
		return $is_conn;
	}

	/**
	 * Merge an array of array's
	 *
	 * @input    array   The arrays you would like to merge
	 *
	 * @returns array on success
	 * @deprecated 3.3 Use Super___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::merge($arrays);
	 */
	public static function mergeArrays($arrays)
	{
		return Super___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::merge($arrays);
	}

	// typo sorry!
	public static function sorten($string, $length = 40, $addTip = true)
	{
		return self::shorten($string, $length, $addTip);
	}

	/**
	 * Shorten a string
	 *
	 * @input    string   The you would like to shorten
	 *
	 * @returns string on success
	 * @deprecated 3.3 Use Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::shorten(...);
	 */
	public static function shorten($string, $length = 40, $addTip = true)
	{
		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::shorten($string, $length, $addTip);
	}

	/**
	 * Making strings safe (various ways)
	 *
	 * @input    string   The you would like to make safe
	 *
	 * @returns string on success
	 * @deprecated 3.3 Use Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe(...);
	 */
	public static function safeString($string, $type = 'L', $spacer = '_', $replaceNumbers = true, $keepOnlyCharacters = true)
	{
		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe(
			$string,
			$type,
			$spacer,
			$replaceNumbers,
			$keepOnlyCharacters
		);
	}

	/**
	 * Convert none English strings to code usable string
	 *
	 * @input    an string
	 *
	 * @returns a string
	 * @deprecated 3.3 Use Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::transliterate($string);
	 */
	public static function transliterate($string)
	{
		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::transliterate($string);
	}

	/**
	 * make sure a string is HTML save
	 *
	 * @input    an html string
	 *
	 * @returns a string
	 * @deprecated 3.3 Use Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::html(...);
	 */
	public static function htmlEscape($var, $charset = 'UTF-8', $shorten = false, $length = 40)
	{
		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::html(
			$var,
			$charset,
			$shorten,
			$length
		);
	}###HELPER_LICENSE_LOCK###

	/**
	 * Convert all int in a string to an English word string
	 *
	 * @input    an string with numbers
	 *
	 * @returns a string
	 * @deprecated 3.3 Use Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::numbers($string);
	 */
	public static function replaceNumbers($string)
	{
		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::numbers($string);
	}

	/**
	 * Convert an integer into an English word string
	 * Thanks to Tom Nicholson <http://php.net/manual/en/function.strval.php#41988>
	 *
	 * @input    an int
	 * @returns a string
	 * @deprecated 3.3 Use Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::number($x);
	 */
	public static function numberToString($x)
	{
		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::number($x);
	}

	/**
	 * Random Key
	 *
	 * @returns a string
	 * @deprecated 3.3 Use Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::random($size);
	 */
	public static function randomkey($size)
	{
		return Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::random($size);
	}###GET_CRYPT_KEY###
}
