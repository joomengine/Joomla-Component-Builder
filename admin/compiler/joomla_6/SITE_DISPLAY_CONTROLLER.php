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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Site\Controller;

use Joomla\Input\Input;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### master site display controller.
 *
 * @since   4.0
 */
class DisplayController extends BaseController
{
	/**
	 * The allowed edit views.
	 *
	 * @var array
	 * @since  4.0.0
	 */
	protected array $allowed_edit_views = [###SITE_ALLOW_EDIT_VIEWS_ARRAY###
	];

	/**
	 * The application identity object.
	 *
	 * @var User
	 * @since  4.0.0
	 */
	protected $identity;

	/**
	 * @param   array                     $config       An optional associative array of configuration settings.
	 *                                                  Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                                  'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface|null  $factory      The factory.
	 * @param   CMSApplication|null       $app          The Application for the dispatcher
	 * @param   Input|null                $input        The Input object for the request
	 *
	 * @throws \Exception
	 * @since   3.0.1
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		$app ??= Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication();
		$this->identity ??= $app->getIdentity();

		parent::__construct($config, $factory, $app, $input);
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean        $cachable   If true, the view output will be cached.
	 * @param   boolean|array  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  DisplayController  This object to support chaining.
	 * @throws \Exception
     * @since   1.5
	 */
	function display($cachable = false, $urlparams = false)
	{
		// set default view if not set
		$view          = $this->input->getCmd('view', '###SITE_DEFAULT_VIEW###');
		$this->input->set('view', $view);
		$isEdit        = $this->checkEditView($view);
		$layout        = $this->input->get('layout', null, 'WORD');
		$id            = $this->input->getInt('id');
		$cachable      = true;

		// ensure that the view is not cashable if edit view or if user is logged in
		if ($this->identity->id || $this->input->getMethod() === 'POST' || $isEdit)
		{
			$cachable = false;
		}

		// Check for edit form.
		if ($isEdit && !$this->checkEditId($view, $id))
		{
			// check if item was opened from other than its own list view
			$ref    = $this->input->getCmd('ref', 0);
			$refid  = $this->input->getInt('refid', 0);

			// set redirect
			if ($refid > 0 && Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($ref))
			{
				// redirect to item of ref
				$this->setRedirect(Joomla___d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_###component###&view=' . (string) $ref . '&layout=edit&id=' . (int) $refid, false));
			}
			elseif (Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($ref))
			{
				// redirect to ref
				 $this->setRedirect(Joomla___d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_###component###&view=' . (string) $ref, false));
			}
			else
			{
				// normal redirect back to the list default site view
				$this->setRedirect(Joomla___d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_###component###&view=###SITE_DEFAULT_VIEW###', false));
			}

			// Somehow the person just went to the form - we don't allow that.
			throw new \Exception(Joomla___ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 403);
		}

		// we may need to make this more dynamic in the future. (TODO)
		$safeurlparams = array(
			'catid' => 'INT',
			'id' => 'INT',
			'cid' => 'ARRAY',
			'year' => 'INT',
			'month' => 'INT',
			'limit' => 'UINT',
			'limitstart' => 'UINT',
			'showall' => 'INT',
			'return' => 'BASE64',
			'filter' => 'STRING',
			'filter_order' => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search' => 'STRING',
			'print' => 'BOOLEAN',
			'lang' => 'CMD',
			'Itemid' => 'INT');

		// should these not merge?
		if (Super___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check($urlparams))
		{
			$safeurlparams = Super___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::merge(array($urlparams, $safeurlparams));
		}

		parent::display($cachable, $safeurlparams);

		return $this;
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The view name.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  boolean  True if the ID is in the edit list.
	 *
	 * @throws \Exception
	 * @since   3.0
	 */
	protected function checkEditId($context, $id)
	{
		if (parent::checkEditId("com_###component###.edit.{$context}", $id))
		{
			return true;
		}

		// check user edit access
		if ($this->canEditId($context, $id))
		{
			$this->holdEditId("com_###component###.edit.{$context}", $id);

			return true;
		}

		return false;
	}

	/**
	 * Method to check whether an ID is allowed to be edited by the active user.
	 *
	 * @param   string   $view    The view name.
	 * @param   integer  $id      The ID of the record to add to the edit list.
	 *
	 * @return  boolean  True if the ID is in the edit list.
	 *
	 * @since   5.0.2
	 */
	protected function canEditId($view, $id): bool
	{
		// check that this view is even allowed
		$allowed = $this->getAllowedEditView($view);
		if ($allowed === null)
		{
			return false;
		}

		// check if this item has custom function set for canEditId
		if (isset($allowed['function'])
			&& method_exists($this, $allowed['function'])
			&& $this->{$allowed['function']}(['id' => $id], 'id'))
		{
			return true;
		}

		// check if this item can be accessed (and has access)
		$access = true;
		if (isset($allowed['access']))
		{
			$access = ($this->identity->authorise($allowed['access'], "com_###component###.{$view}." . (int) $id)
				&& $this->identity->authorise($allowed['access'], 'com_###component###'));
		}

		// check if this item can be edited
		$edit = false;
		if ($access && isset($allowed['edit']))
		{
			$edit = ($this->identity->authorise($allowed['edit'], "com_###component###.{$view}." . (int) $id)
				&& $this->identity->authorise($allowed['edit'], 'com_###component###'));
		}

		// check if this item can be edited by owner
		if ($access && !$edit && isset($allowed['edit.own']))
		{
			$edit = ($this->identity->authorise($allowed['edit.own'], "com_###component###.{$view}." . (int) $id)
				&& $this->identity->authorise($allowed['edit.own'], 'com_###component###'));
		}

		return $edit;
	}

	/**
	 * Checks if the provided view is an edit view.
	 *
	 * This method verifies whether the given view name is recognized as an edit view.
	 * It uses the StringHelper::check() method to validate the input and then checks
	 * against a predefined list of edit views.
	 *
	 * @param  string|null  $view  The name of the view to check.
	 *
	 * @return  bool   True if the view is an edit view, false otherwise.
	 * @since   4.0.0
	 */
	protected function checkEditView(?string $view): bool
	{
		if (Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($view))
		{
			// check if this is an edit view
			if (isset($this->allowed_edit_views[$view]))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the allowed edit view permission map
	 *
	 * @param  string|null  $view  The name of the view to check.
	 *
	 * @return  array|null   The permissions map
	 * @since   5.0.2
	 */
	protected function getAllowedEditView(?string $view): ?array
	{
		if (Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($view))
		{
			// check if this is an edit view
			if (isset($this->allowed_edit_views[$view]))
			{
				return $this->allowed_edit_views[$view];
			}
		}

		return null;
	}###SITE_ALLOW_EDIT_VIEWS_FUNCTIONS###
}
