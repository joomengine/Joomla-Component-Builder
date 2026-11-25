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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Model;

###CUSTOM_ADMIN_VIEW_MODEL_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### ###SView### Item Model
 *
 * @since  1.6
 */
class ###SView###Model extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var     string
	 * @since   1.6
	 */
	protected $_context = 'com_###component###.###sview###';

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
		'media/com_###component###/css/admin/admin.css',
		'media/com_###component###/css/admin/###sview###.css'
	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'media/com_###component###/js/admin/admin.js'
	];

	/**
	 * A custom property for UI Kit components.
	 *
	 * @var   mixed  Property for storing UI Kit component-related data or objects.
	 * @since 3.2.0
	 */
	protected $uikitComp = [];

	/**
	 * @var     object item
	 * @since   1.6
	 */
	protected $item;

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
	 * @since   3.0
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
		$this->_item = null;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function populateState()
	{
		// Get the item main id
		$id = $this->input->getInt('id', null);
		$this->setState('###sview###.id', $id);

		// Load the parameters.
		parent::populateState();
	}

	/**
	 * Method to get article data.
	 *
	 * @param   integer  $pk  The id of the article.
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		// check if this user has permission to access item
		if (!$this->user->authorise('###sview###.access', 'com_###component###'))
		{
			$this->app->enqueueMessage(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('Not authorised!'), 'error');
			// redirect away if not a correct to cPanel/default view
			$this->app->redirect('index.php?option=com_###component###');
			return false;
		}

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('###sview###.id');###CUSTOM_ADMIN_BEFORE_GET_ITEM###

		if ($this->_item === null)
		{
			$this->_item = [];
		}###LICENSE_LOCKED_CHECK###

		if (!isset($this->_item[$pk]))
		{###CUSTOM_ADMIN_GET_ITEM###
		}###CUSTOM_ADMIN_AFTER_GET_ITEM###

		return $this->_item[$pk];
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
	}###CUSTOM_ADMIN_CUSTOM_METHODS######LICENSE_LOCKED_SET_BOOL######CUSTOM_ADMIN_CUSTOM_BUTTONS_METHOD###
}
