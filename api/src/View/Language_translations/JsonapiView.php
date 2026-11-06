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
namespace VDM\Component\Componentbuilder\Api\View\Language_translations;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Registry\Registry;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Component\Componentbuilder\Api\Serializer\Language_translationSerializer;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Componentbuilder Json View class for the Language_translations
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render items in the documents
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'source',
		'plugins',
		'modules',
		'components',
		'translation',
		'created',
		'created_by',
		'modified',
		'modified_by',
		'published',
		'ordering',
		'access',
		'version',
		'hits',
	];

	/**
	 * The relationships the items have
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $relationship = [
		'plugins',
		'modules',
		'components',
		'created_by',
		'modified_by',
	];

	/**
	 * Constructor.
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 *                          contentType: the name (optional) of the content type to use for the serialization
	 *
	 * @since   4.0.0
	 */
	public function __construct($config = [])
	{
		if (\array_key_exists('contentType', $config))
		{
			$this->serializer = new Language_translationSerializer($config['contentType']);
		}

		parent::__construct($config);
	}

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
		if (isset($item->plugins) && is_string($item->plugins) && $item->plugins !== '')
		{
			// Convert the plugins field to an array.
			$registry = new Registry;
			$registry->loadString($item->plugins);
			$item->plugins = $registry->toArray();
		}

		if (isset($item->modules) && is_string($item->modules) && $item->modules !== '')
		{
			// Convert the modules field to an array.
			$registry = new Registry;
			$registry->loadString($item->modules);
			$item->modules = $registry->toArray();
		}

		if (isset($item->components) && is_string($item->components) && $item->components !== '')
		{
			// Convert the components field to an array.
			$registry = new Registry;
			$registry->loadString($item->components);
			$item->components = $registry->toArray();
		}

		if (isset($item->translation) && is_string($item->translation) && $item->translation !== '')
		{
			// Convert the translation field to an array.
			$registry = new Registry;
			$registry->loadString($item->translation);
			$item->translation = $registry->toArray();
		}

		return parent::prepareItem($item);
	}
}
