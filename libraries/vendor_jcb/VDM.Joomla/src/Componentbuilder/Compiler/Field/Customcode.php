<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Field;


use VDM\Joomla\Componentbuilder\Compiler\Factory as Compiler;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;


/**
 * Compiler Field Customcode
 * 
 * @since 3.2.0
 */
class Customcode
{
	/**
	 * Tracking the update of fields per/view
	 *
	 * @var    array
	 * @since 3.2.0
	 */
	protected array $views;

	/**
	 * Compiler Customcode Dispenser
	 *
	 * @var    Dispenser
	 * @since 3.2.0
	 */
	protected Dispenser $dispenser;

	/**
	 * Constructor
	 *
	 * @param Dispenser|null      $dispenser     The compiler customcode dispenser object.
	 *
	 * @since 3.2.0
	 */
	public function __construct(?Dispenser $dispenser = null)
	{
		$this->dispenser = $dispenser ?: Compiler::_('Customcode.Dispenser');
	}

	/**
	 * Update field customcode
	 *
	 * @param   int          $id              The field id
	 * @param   object       $field           The field object
	 * @param   string|null  $singleViewName  The view edit or single name
	 * @param   string|null  $listViewName    The view list name
	 *
	 * @return void
	 * @since 3.2.0
	 */
	public function update(int $id, object &$field, $singleViewName = null, $listViewName = null)
	{
		// check if we should load scripts for single view
		if ($singleViewName && StringHelper::check($singleViewName)
			&& !isset($this->views[$singleViewName][$id]))
		{
			// add_javascript_view_footer
			if ($field->add_javascript_view_footer == 1
				&& StringHelper::check(
					$field->javascript_view_footer
				))
			{
				$convert__ = true;
				if (isset($field->javascript_view_footer_decoded)
					&& $field->javascript_view_footer_decoded)
				{
					$convert__ = false;
				}
				$this->dispenser->set(
					$field->javascript_view_footer,
					'view_footer',
					$singleViewName,
					null,
					array(
						'table'  => 'field',
						'id'     => (int) $id,
						'field'  => 'javascript_view_footer',
						'type'   => 'js',
						'prefix' => PHP_EOL),
					$convert__,
					$convert__,
					true
				);
				if (!isset($field->javascript_view_footer_decoded))
				{
					$field->javascript_view_footer_decoded
						= true;
				}

				if (strpos((string) $field->javascript_view_footer, "token") !== false
					|| strpos((string) $field->javascript_view_footer, "task=ajax") !== false)
				{
					if (!isset($this->dispenser->hub['token']))
					{
						$this->dispenser->hub['token'] = [];
					}
					if (!isset($this->dispenser->hub['token'][$singleViewName])
						|| !$this->dispenser->hub['token'][$singleViewName])
					{
						$this->dispenser->hub['token'][$singleViewName]
							= true;
					}
				}
			}

			// add_css_view
			if ($field->add_css_view == 1)
			{
				$convert__ = true;
				if (isset($field->css_view_decoded)
					&& $field->css_view_decoded)
				{
					$convert__ = false;
				}
				$this->dispenser->set(
					$field->css_view,
					'css_view',
					$singleViewName,
					null,
					array('prefix' => PHP_EOL),
					$convert__,
					$convert__,
					true
				);
				if (!isset($field->css_view_decoded))
				{
					$field->css_view_decoded = true;
				}
			}

			// add this only once to single view.
			$this->views[$singleViewName][$id] = true;
		}

		// check if we should load scripts for list views
		if ($listViewName && StringHelper::check($listViewName)
			&& !isset($this->views[$listViewName][$id]))
		{
			// add_javascript_views_footer
			if ($field->add_javascript_views_footer == 1
				&& StringHelper::check(
					$field->javascript_views_footer
				))
			{
				$convert__ = true;
				if (isset($field->javascript_views_footer_decoded)
					&& $field->javascript_views_footer_decoded)
				{
					$convert__ = false;
				}
				$this->dispenser->set(
					$field->javascript_views_footer,
					'views_footer',
					$singleViewName,
					null,
					array(
						'table'  => 'field',
						'id'     => (int) $id,
						'field'  => 'javascript_views_footer',
						'type'   => 'js',
						'prefix' => PHP_EOL),
					$convert__,
					$convert__,
					true
				);
				if (!isset($field->javascript_views_footer_decoded))
				{
					$field->javascript_views_footer_decoded = true;
				}
				if (strpos((string) $field->javascript_views_footer, "token") !== false
					|| strpos((string) $field->javascript_views_footer, "task=ajax") !== false)
				{
					if (!isset($this->dispenser->hub['token']))
					{
						$this->dispenser->hub['token'] = [];
					}
					if (!isset($this->dispenser->hub['token'][$listViewName])
						|| !$this->dispenser->hub['token'][$listViewName])
					{
						$this->dispenser->hub['token'][$listViewName]
							= true;
					}
				}
			}

			// add_css_views
			if ($field->add_css_views == 1)
			{
				$convert__ = true;
				if (isset($field->css_views_decoded)
					&& $field->css_views_decoded)
				{
					$convert__ = false;
				}
				$this->dispenser->set(
					$field->css_views,
					'css_views',
					$singleViewName,
					null,
					array('prefix' => PHP_EOL),
					$convert__,
					$convert__,
					true
				);
				if (!isset($field->css_views_decoded))
				{
					$field->css_views_decoded = true;
				}
			}

			// add this only once to list view.
			$this->views[$listViewName][$id] = true;
		}
	}
}

