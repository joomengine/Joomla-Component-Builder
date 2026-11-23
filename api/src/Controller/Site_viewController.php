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
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Controller\Exception\ResourceNotFound;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Site_view Api Controller
 *
 * The item resource of the site_view view: read, create, update and delete
 * a record by its id or by any unique key of its table.
 *
 * @since  4.0.0
 */
class Site_viewController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'site_views';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'site_view';

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
		// The controller role selects its explicit native model.
		$name = 'site_view';

		// The API carries no request state for the model, as the form controller does not:
		// the id a save sets must never be replaced by a later read of the request.
		if (!array_key_exists('ignore_request', $config))
		{
			$config['ignore_request'] = true;
		}

		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Basic display of an item view
	 *
	 * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayItem($id = null)
	{
		if ($id === null)
		{
			$id = $this->getRecordId();
		}

		if ($id > 0 && !$this->allowView((int) $id))
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		return parent::displayItem($id);
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function edit()
	{
		// resolve the record by its id or by any unique key of the table
		$this->input->set('id', $this->getRecordId());

		return parent::edit();
	}

	/**
	 * Prepare inherited PATCH values in the edit model's input representation.
	 *
	 * @param   array  $data  Submitted values plus Joomla's raw stored-column merge.
	 *
	 * @return  array
	 * @throws  \RuntimeException  When the existing record cannot be loaded.
	 * @since   6.2.0
	 */
	protected function preprocessSaveData(array $data): array
	{
		$data = parent::preprocessSaveData($data);

		if ($this->input->getMethod() !== 'PATCH')
		{
			return $data;
		}

		$model = $this->getModel();
		$table = $model->getTable();
		$key = $table->getKeyName();
		$id = (int) ($data[$key] ?? 0);

		if ($id < 1)
		{
			return $data;
		}

		// Keep the original payload separate: null and empty values are explicit input.
		$submitted = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');
		$submitted = is_array($submitted) ? $submitted : [];
		$item = $model->getItem($id);

		if (!is_object($item))
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_RECORD_LOAD'), 500);
		}

		// getItem owns the inverse of each generated or expert storage transformation.
		// Limit hydration to table columns; computed display properties are not input.
		foreach ($table->getFields() as $field)
		{
			$name = $field->Field;

			if ($name !== $key && !array_key_exists($name, $submitted) && property_exists($item, $name))
			{
				$data[$name] = $item->{$name};
			}
		}

		// Tags are related data and therefore are absent from Joomla's table merge.
		if (!array_key_exists('tags', $submitted) && isset($item->tags))
		{
			$tags = $item->tags;

			if ($tags instanceof \Joomla\CMS\Helper\TagsHelper)
			{
				$data['tags'] = empty($tags->tags) ? [] : explode(',', $tags->tags);
			}
			elseif (is_array($tags))
			{
				$data['tags'] = $tags;
			}
		}

		return $data;
	}

	/**
	 * Removes an item.
	 *
	 * @param   integer  $id  The primary key to delete item.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function delete($id = null)
	{
		if (!$this->allowDelete())
		{
			throw new NotAllowed(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 403);
		}

		if ($id === null)
		{
			$id = $this->getRecordId();
		}

		$id = (int) $id;
		$model = $this->getModel();
		$table = $model->getTable();

		if ($id < 1 || !$table->load($id))
		{
			throw new ResourceNotFound(Text::_('JLIB_APPLICATION_ERROR_RECORD'), 404);
		}

		$pks = [$id];

		if (!$model->delete($pks))
		{
			$session = $this->app->getSession();

			if ($session->get('http_status_code_404', false))
			{
				$session->clear('http_status_code_404');

				throw new ResourceNotFound(Text::_('JLIB_APPLICATION_ERROR_RECORD'), 404);
			}

			if ($session->get('http_status_code_409', false))
			{
				$session->clear('http_status_code_409');

				throw new \RuntimeException('Resource not in state that can be deleted, must be trashed before it can be deleted', 409);
			}

			$error = $model->getError();

			if ($error)
			{
				throw new \RuntimeException($error, 500);
			}

			throw new NotAllowed(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 403);
		}

		$this->app->setHeader('status', 204);
	}

	/**
	 * Get the id of the record the request targets.
	 *
	 * The primary key is taken when the request carries it, else the record
	 * is resolved through the first unique key of the table the request carries.
	 *
	 * @return  integer  The record id, or 0 when no record matches.
	 *
	 * @since   4.0.0
	 */
	protected function getRecordId(): int
	{
		// Take the primary key when the request carries it.
		$id = $this->input->getInt('id', 0);

		if ($id > 0)
		{
			return $id;
		}

		// Resolve the record through the first unique key the request carries.
		foreach (['guid'] as $key)
		{
			$value = $this->input->getString($key, '');

			if ($value === '')
			{
				continue;
			}

			$table = $this->getModel()->getTable();

			if ($table->load([$key => $value]))
			{
				return (int) $table->id;
			}

			return 0;
		}

		return 0;
	}

	/**
	 * Method to check if you can view a record.
	 *
	 * @param   integer  $id  The record id.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function allowView(int $id): bool
	{
		// Get user object.
		$user = $this->app->getIdentity();

		// Access check.
		return ($user->authorise('site_view.access', 'com_componentbuilder.site_view.' . $id) && $user->authorise('site_view.access', 'com_componentbuilder'));
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = [])
	{
		// Get user object.
		$user = $this->app->getIdentity();
		// Access check.
		$access = $user->authorise('site_view.access', 'com_componentbuilder');
		if (!$access)
		{
			return false;
		}

		// In the absence of better information, revert to the component permissions.
		return parent::allowAdd($data);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = [], $key = 'id')
	{
		// get user object.
		$user = $this->app->getIdentity();
		// get record id.
		$recordId = isset($data[$key]) ? (int) $data[$key] : 0;


		// Access check.
		$access = ($user->authorise('site_view.access', 'com_componentbuilder.site_view.' . (int) $recordId) && $user->authorise('site_view.access', 'com_componentbuilder'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('core.edit', 'com_componentbuilder.site_view.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('core.edit.own', 'com_componentbuilder.site_view.' . (int) $recordId))
				{
					// Now test the owner is the user.
					// The owner comes from the stored record, never from $data.
					$record = $this->getModel()->getItem($recordId);

					if (empty($record))
					{
						return false;
					}
					$ownerId = (int) $record->created_by;

					// If the owner matches 'me' then allow.
					if ($ownerId == $user->id)
					{
						if ($user->authorise('core.edit.own', 'com_componentbuilder'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to check if it's allowed to delete a record.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function allowDelete(): bool
	{
		// Get user object.
		$user = $this->app->getIdentity();
		// Access check.
		$access = $user->authorise('site_view.access', 'com_componentbuilder');
		if (!$access)
		{
			return false;
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('core.delete', $this->option);
	}
}
