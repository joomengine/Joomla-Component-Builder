<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace VDM\Component\Componentbuilder\Api\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\Access\Exception\NotAllowed;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Compiler Api Controller
 *
 * The read-only list resource of the compiler view, answered by the
 * view's own model and the dynamic get it was built from.
 *
 * @since  4.0.0
 */
class CompilerController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'compiler';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'compiler';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|boolean  Model object on success; otherwise false on failure.
	 *
	 * @since   4.0.0
	 */
	public function getModel($name = '', $prefix = '', $config = [])
	{
		// The administrator model of the compiler view, its request state ignored.
		return parent::getModel('Compiler', 'Administrator', array_merge(['ignore_request' => true], $config));
	}

	/**
	 * Display the list of the compiler view.
	 *
	 * The dynamic get expects, as far as it shows:
	 *  - where a.published = 1
	 *  - ordered by a.modified DESC
	 *  - ordered by a.created DESC
	 * Every record is returned, the get does not paginate.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  NotAllowed
	 * @since   4.0.0
	 */
	public function displayList()
	{
		if (!$this->allowView())
		{
			throw new NotAllowed(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		return parent::displayList();
	}

	/**
	 * The list resource does not serve one item.
	 *
	 * @param   integer  $id  The primary key to display.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function displayItem($id = null)
	{
		throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 405);
	}

	/**
	 * The resource is read-only.
	 *
	 * @return  void
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function add()
	{
		throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 405);
	}

	/**
	 * The resource is read-only.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function edit()
	{
		throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 405);
	}

	/**
	 * The resource is read-only.
	 *
	 * @param   integer  $id  The primary key to delete item.
	 *
	 * @return  void
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function delete($id = null)
	{
		throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 405);
	}

	/**
	 * Whether the calling user may read the compiler view.
	 *
	 * @return  bool
	 *
	 * @since   4.0.0
	 */
	protected function allowView(): bool
	{
		// Get the calling user.
		$user = Factory::getApplication()->getIdentity();

		// The administrator area asks for core.manage.
		if (!$user->authorise('core.manage', 'com_componentbuilder'))
		{
			return false;
		}

		// The compiler.access permission the compiler view link asks for.
		return $user->authorise('compiler.access', 'com_componentbuilder');
	}
}
