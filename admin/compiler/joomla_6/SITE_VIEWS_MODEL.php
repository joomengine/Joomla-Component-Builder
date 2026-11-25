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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Site\Model;

###SITE_VIEWS_MODEL_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### List Model for ###SViews###
 *
 * @since  1.6
 */
class ###SViews###Model extends ListModel
{
	/**
	 * Represents the current user object.
	 *
	 * @var   User  The user object representing the current user.
	 * @since 3.2.0
	 */
	protected User $user;

	/**
	 * The unique identifier of the current user.
	 *
	 * @var   int|null  The ID of the current user.
	 * @since 3.2.0
	 */
	protected ?int $userId;

	/**
	 * Flag indicating whether the current user is a guest.
	 *
	 * @var   int  1 if the user is a guest, 0 otherwise.
	 * @since 3.2.0
	 */
	protected int $guest;

	/**
	 * An array of groups that the current user belongs to.
	 *
	 * @var   array|null  An array of user group IDs.
	 * @since 3.2.0
	 */
	protected ?array $groups;

	/**
	 * An array of view access levels for the current user.
	 *
	 * @var   array|null  An array of access level IDs.
	 * @since 3.2.0
	 */
	protected ?array $levels;

	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

	/**
	 * The input object, providing access to the request data.
	 *
	 * @var   Input  The input object.
	 * @since 3.2.0
	 */
	protected Input $input;

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'media/com_###component###/css/site/site.css',
		'media/com_###component###/css/site/###sview###.css'
	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'media/com_###component###/js/site/site.js'
	];

	/**
	 * A custom property for UIKit components. (not used unless you load v2)
	 */
	protected $uikitComp;

	/**
	 * Authorised groups for the injected user.
	 *
	 * @var   array
	 * @since 6.2.0
	 */
	public array $authorisedGroups = [];

	/**
	 * Compatibility flag for existing custom model code.
	 *
	 * @var   bool
	 * @since 6.2.0
	 */
	public bool $initSet = false;

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$this->app ??= Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$this->input ??= $this->app->getInput();

		// will be removed
		$this->initSet = true;
	}

	/**
	 * Refresh identity-dependent state after Joomla injects the current user.
	 *
	 * @param   User  $currentUser  The user supplied by the controller or helper.
	 *
	 * @return  void
	 * @since   6.2.0
	 */
	public function setCurrentUser(User $currentUser): void
	{
		parent::setCurrentUser($currentUser);
		$this->user = $currentUser;
		$this->userId = $currentUser->id;
		$this->guest = $currentUser->guest;
		$this->groups = $currentUser->groups;
		$this->authorisedGroups = $currentUser->getAuthorisedGroups();
		$this->levels = $currentUser->getAuthorisedViewLevels();
		$this->cache = [];
		$this->query = [];
		$this->lastQueryStoreId = null;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return   string  An SQL query
	 * @since    1.6
	 */
	protected function getListQuery()
	{###SITE_GET_LIST_QUERY###
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6
	 */
	public function getItems()
	{
		$user = $this->user;###USER_PERMISSION_CHECK_ACCESS######LICENSE_LOCKED_CHECK######SITE_BEFORE_GET_ITEMS###
		// load parent items
		$items = parent::getItems();

		// Get the global params
		$globalParams = Joomla___aeb8e463_291f_4445_9ac4_34b637c12dbd___Power::getParams('com_###component###', true);###SITE_GET_ITEMS######SITE_AFTER_GET_ITEMS###

		// return items
		return $items;
	}

	/**
	 * Method to get the styles that have to be included on the view
	 *
	 * @return  array    styles files
	 * @since   4.3
	 */
	public function getStyles(): array
	{
		return $this->styles;
	}

	/**
	 * Method to set the styles that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setStyles(string $path): void
	{
		$this->styles[] = $path;
	}

	/**
	 * Method to get the script that have to be included on the view
	 *
	 * @return  array    script files
	 * @since   4.3
	 */
	public function getScripts(): array
	{
		return $this->scripts;
	}

	/**
	 * Method to set the script that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setScript(string $path): void
	{
		$this->scripts[] = $path;
	}###LICENSE_LOCKED_SET_BOOL######SITE_CUSTOM_METHODS######SITE_CUSTOM_BUTTONS_METHOD###
}
