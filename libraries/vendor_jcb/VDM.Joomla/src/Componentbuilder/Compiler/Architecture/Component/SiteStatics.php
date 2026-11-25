<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    19th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Component;


use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Component;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * Everything the site side of the component needs whether or not it has views.
 * 
 * @since 6.1.7
 */
final class SiteStatics
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Customcode Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 6.1.7
	 */
	protected Dispenser $dispenser;

	/**
	 * The Content One Builder Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * The Component Class.
	 *
	 * @var   Component
	 * @since 6.1.7
	 */
	protected Component $component;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 6.1.7
	 */
	protected Placeholder $placeholder;

	/**
	 * Constructor.
	 *
	 * @param Config      $config          The Config Class.
	 * @param Dispenser   $dispenser       The Customcode Dispenser Class.
	 * @param ContentOne  $contentone      The Content One Builder Class.
	 * @param Component   $component       The Component Class.
	 * @param Placeholder $placeholder     The Placeholder Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Dispenser $dispenser,
		ContentOne $contentone,
		Component $component,
		Placeholder $placeholder)
	{
		$this->config = $config;
		$this->dispenser = $dispenser;
		$this->contentone = $contentone;
		$this->component = $component;
		$this->placeholder = $placeholder;
	}

	/**
	 * Set everything the site side of the component needs.
	 *
	 * A component keeping neither site folder needs none of it. Every other
	 * one is given a default view to open on, the site half of the helper
	 * class, and the global site event when it was asked for.
	 *
	 * @return  void
	 * @since   6.1.7
	 */
	public function set(): void
	{
		// load the site statics
		if (!$this->config->remove_site_folder || !$this->config->remove_site_edit_folder)
		{
			$this->config->build_target = 'site';
			// if no default site view was set, the redirect to root
			if (!$this->contentone->exists('SITE_DEFAULT_VIEW'))
			{
				$this->contentone->set('SITE_DEFAULT_VIEW', '');
			}
			// set site custom script to helper class
			// SITE_CUSTOM_HELPER_SCRIPT
			$this->contentone->set('SITE_CUSTOM_HELPER_SCRIPT',
				$this->placeholder->update_(
				$this->dispenser->hub['component_php_helper_site']
			));
			// SITE_GLOBAL_EVENT_HELPER
			if (!$this->contentone->exists('SITE_GLOBAL_EVENT'))
			{
				$this->contentone->set('SITE_GLOBAL_EVENT', '');
			}
			if (!$this->contentone->exists('SITE_GLOBAL_EVENT_HELPER'))
			{
				$this->contentone->set('SITE_GLOBAL_EVENT_HELPER', '');
			}
			// now load the data for the global event if needed
			if ($this->component->get('add_site_event', 0) == 1)
			{
				$this->contentone->add('SITE_GLOBAL_EVENT', PHP_EOL . PHP_EOL . "//" . Line::_(
						__LINE__,__CLASS__
					) . "Trigger the Global Site Event");
				$this->contentone->add('SITE_GLOBAL_EVENT',
					PHP_EOL . $this->contentone->get('Component')
					. 'Helper::globalEvent(Factory::getDocument());');
				// SITE_GLOBAL_EVENT_HELPER
				$this->contentone->add('SITE_GLOBAL_EVENT_HELPER',
					PHP_EOL . PHP_EOL . Indent::_(1) . '/**'
				);
				$this->contentone->add('SITE_GLOBAL_EVENT_HELPER',
					PHP_EOL . Indent::_(1)
					. '*' . Indent::_(1) . 'The Global Site Event Method.');
				$this->contentone->add('SITE_GLOBAL_EVENT_HELPER',
					PHP_EOL . Indent::_(1) . '**/'
				);
				$this->contentone->add('SITE_GLOBAL_EVENT_HELPER',
					PHP_EOL . Indent::_(1)
					. 'public static function globalEvent($document)');
				$this->contentone->add('SITE_GLOBAL_EVENT_HELPER',
					PHP_EOL . Indent::_(1) . '{'
				);
				$this->contentone->add('SITE_GLOBAL_EVENT_HELPER',
					PHP_EOL . $this->placeholder->update_(
						$this->dispenser->hub['component_php_site_event']
					));
				$this->contentone->add('SITE_GLOBAL_EVENT_HELPER',
					PHP_EOL . Indent::_(1) . '}'
				);
			}
		}
	}
}

