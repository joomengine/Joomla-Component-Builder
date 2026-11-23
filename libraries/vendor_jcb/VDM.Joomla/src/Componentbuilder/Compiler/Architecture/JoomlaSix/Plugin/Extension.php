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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Plugin;


use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne as Builder;
use VDM\Joomla\Componentbuilder\Power\Parser;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Plugin\ExtensionInterface;


/**
 * Plugin Extension Class for Joomla 6
 * 
 * @since 5.1.2
 */
final class Extension implements ExtensionInterface
{
	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 5.0.2
	 */
	protected Placeholder $placeholder;

	/**
	 * The ContentOne Class.
	 *
	 * @var   Builder
	 * @since 5.0.2
	 */
	protected Builder $builder;

	/**
	 * The Parser Class.
	 *
	 * @var   Parser
	 * @since 5.0.2
	 */
	protected Parser $parser;

	/**
	 * Constructor.
	 *
	 * @param Placeholder   $placeholder   The Placeholder Class.
	 * @param Builder       $builder       The Content One Class.
	 * @param Parser        $parser        The Parser Class.
	 *
	 * @since 5.0.2
	 */
	public function __construct(Placeholder $placeholder, Builder $builder, Parser $parser)
	{
		$this->placeholder = $placeholder;
		$this->builder = $builder;
		$this->parser = $parser;
	}

	/**
	 * Get the updated placeholder content for the given plugin.
	 *
	 * @param  object  $plugin   The plugin object containing the necessary data.
	 *
	 * @return string  The updated placeholder content.
	 *
	 * @since 5.0.2
	 */
	public function get(object $plugin): string
	{
		$add_subscriber_interface = $this->addNeededMethods($plugin->main_class_code);

		$extension = [];
		$extension[] = $plugin->comment . PHP_EOL . 'final class ';
		$extension[] = $plugin->class_name . ' extends ' . $plugin->extends;
		if ($add_subscriber_interface)
		{
			$extension[] = ' implements Joomla__' . '_c06c5116_6b9d_487c_9b09_5094ec4506a3___Power';
		}
		$extension[] = PHP_EOL . '{' . PHP_EOL;
		$extension[] = $plugin->main_class_code;
		$extension[] = PHP_EOL . '}' . PHP_EOL;

		return $this->placeholder->update(
			implode('', $extension),
			$this->builder->allActive()
		);
	}

	/**
	 * Ensures that the required methods are present in the plugin code.
	 *
	 * This method checks the plugin's code for the presence of required methods,
	 * particularly the method that indicates implementation of the SubscriberInterface.
	 * If the necessary method is missing, it adds it to the code.
	 *
	 * @param  string  $code  The main code of the plugin, passed by reference.
	 *
	 * @return bool  Returns true if the SubscriberInterface implementation is added or already present, false otherwise.
	 *
	 * @since 5.0.2
	 */
	protected function addNeededMethods(string &$code): bool
	{
		// Parse the code to extract its structure, particularly its methods.
		$code_structure = $this->parser->code($code);

		if (empty($code_structure['methods']))
		{
			return false;
		}

		// Check if methods are defined and if getSubscribedEvents is not present.
		if (!$this->getSubscribedEvents($code_structure['methods']))
		{
			// Attempt to add the getSubscribedEvents method.
			$method = $this->addGetSubscribedEvents($code_structure['methods']);
			if ($method !== null)
			{
				// Append the new method to the code and indicate that the interface must be added.
				$code .= $method;

				return true;
			}

			// Return false if the event method could not be added.
			return false;
		}

		// Return true if getSubscribedEvents is already present.
		return true;
	}

	/**
	 * Add the getSubscribedEvents method
	 *
	 * @param  array  $methods  The plugin methods.
	 *
	 * @return string|null  The getSubscribedEvents code
	 *
	 * @since 5.0.2
	 */
	protected function addGetSubscribedEvents(array $methods): ?string
	{
		$events = [];
		$counter = 0;
		foreach ($methods as $method)
		{
			if ($this->validEventName($method))
			{
				$events[$method['name']] =  Indent::_(3) . "'{$method['name']}' => '{$method['name']}'";

				// autoloaded when method start with 'on'
				// so we can ignore adding the getSubscribedEvents
				if (substr($method['name'], 0, 2) === 'on')
				{
					$counter++;
				}
			}
		}

		if ($events === [] || $counter == count($events))
		{
			return null;
		}

		$method = [];
		$method[] = PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$method[] = Indent::_(1) . ' * Returns an array of events this subscriber will listen to.';
		$method[] = Indent::_(1) . ' *';
		$method[] = Indent::_(1) . ' * @return  array';
		$method[] = Indent::_(1) . ' *';
		$method[] = Indent::_(1) . ' * @since   5.0.0';
		$method[] = Indent::_(1) . ' */';
		$method[] = Indent::_(1) . 'public static function getSubscribedEvents(): array';
		$method[] = Indent::_(1) . '{';
		$method[] = Indent::_(2) . 'return [';
		$method[] = implode(',' . PHP_EOL, $events);
		$method[] = Indent::_(2) . '];';
		$method[] = Indent::_(1) . '}';

		return implode(PHP_EOL, $method);
	}

	/**
	 * Validates if a method name is a valid event name for a Joomla plugin.
	 *
	 * The method must meet the following criteria:
	 * - It must be public, not static, and not abstract.
	 * - It must not be a magic method (i.e., should not start with '__').
	 *
	 * @param  array  $method  The method details, including 'name', 'access', 'static', and 'abstract'.
	 *
	 * @return bool  Returns true if the method is a valid event name, otherwise false.
	 *
	 * @since 5.0.2
	 */
	protected function validEventName(array $method): bool
	{
		// Check if the method is public, static, and not abstract
		if ($method['access'] !== 'public' || $method['static'] || $method['abstract']) 
		{
			return false;
		}

		// Check if the method is a magic method (starts with '__')
		if (substr($method['name'], 0, 2) === '__') 
		{
			return false;
		}

		// If all checks pass, the method is a valid event name
		return true;
	}

	/**
	 * Check if the getSubscribedEvents is set
	 *
	 * @param  array  $methods  The plugin methods.
	 *
	 * @return bool
	 *
	 * @since 5.0.2
	 */
	protected function getSubscribedEvents(array $methods): bool
	{
		foreach ($methods as $method)
		{
			if ($method['name'] === 'getSubscribedEvents' && $method['static'] && !$method['abstract'])
			{
				return true;
			}
		}
		return false;
	}
}

