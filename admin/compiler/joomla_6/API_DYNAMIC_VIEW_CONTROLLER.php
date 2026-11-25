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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Api\Controller;

###API_DYNAMIC_VIEW_CONTROLLER_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###ApiName### Api Controller
 *
 * The read-only item resource of the ###sview### view, answered by the
 * view's own model and the dynamic get it was built from.
 *
 * @since  4.0.0
 */
class ###ApiName###Controller extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = '###apiname###';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = '###apiname###';

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
	{###API_DYNAMIC_VIEW_CONTROLLER_GETMODEL###
	}

	/**
	 * Display the item of the ###sview### view.###API_DYNAMIC_VIEW_CONTROLLER_EXPECTATIONS###
	 *
	 * @param   integer  $id  The id of the item, when the route carries one.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  NotAllowed
	 * @since   4.0.0
	 */
	public function displayItem($id = null)
	{
		if (!$this->allowView())
		{
			throw new NotAllowed(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		return parent::displayItem($id ?? $this->input->getInt('id', 0));
	}

	/**
	 * The item resource does not serve a list.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function displayList()
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
	 * Whether the calling user may read the ###sview### view.
	 *
	 * @return  bool
	 *
	 * @since   4.0.0
	 */
	protected function allowView(): bool
	{###API_DYNAMIC_VIEW_CONTROLLER_ALLOWVIEW###
	}
}
