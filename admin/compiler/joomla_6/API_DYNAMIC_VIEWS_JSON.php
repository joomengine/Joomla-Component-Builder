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
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Api\View\###ApiName###;

###API_DYNAMIC_VIEWS_JSON_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### Json View class for the ###ApiName### resource
 *
 * The attributes are whatever the model of the ###sview### view returns.
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

		$this->fieldsToRenderList = array_keys($fields) ?: ['id'];###API_DYNAMIC_VIEWS_JSON_META###

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
		}###API_DYNAMIC_VIEWS_JSON_PREPAREITEM###

		return parent::prepareItem($item);
	}
}
