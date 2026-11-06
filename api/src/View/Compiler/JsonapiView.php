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
namespace VDM\Component\Componentbuilder\Api\View\Compiler;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Json View class for the Compiler resource
 *
 * The attributes are whatever the model of the compiler view returns.
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render items in the documents, taken from the items
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [];

	/**
	 * The position of the last row prepared, the id of a row without one
	 *
	 * @var    int
	 * @since  4.0.0
	 */
	protected int $position = 0;

	/**
	 * Execute and display a template script.
	 *
	 * @param   ?array  $items  Array of items
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayList(?array $items = null)
	{
		if ($items === null)
		{
			$items = [];

			foreach ($this->getModel()->getItems() ?: [] as $item)
			{
				$items[] = $this->prepareItem($item);
			}
		}

		// The attributes are the keys the model returned (a field selection may follow later).
		$fields = [];

		foreach ($items as $item)
		{
			if (is_object($item))
			{
				$fields += array_flip(array_keys(get_object_vars($item)));
			}
		}

		$this->fieldsToRenderList = array_keys($fields) ?: ['id'];

		return parent::displayList($items);
	}

	/**
	 * Prepare item before render.
	 *
	 * @param   object  $item  The model item
	 *
	 * @return  object
	 *
	 * @since   4.0.0
	 */
	protected function prepareItem($item)
	{
		if (!is_object($item))
		{
			return $item;
		}
		// A JSON:API resource needs an id.
		if (!isset($item->id))
		{
			$item->id = ++$this->position;
		}

		return parent::prepareItem($item);
	}
}
