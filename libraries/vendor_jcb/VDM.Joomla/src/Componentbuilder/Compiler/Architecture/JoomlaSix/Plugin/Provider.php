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
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Plugin\ProviderInterface;


/**
 * Plugin Provider Class for Joomla 6
 * 
 * @since 5.1.2
 */
final class Provider implements ProviderInterface
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
	 * Constructor.
	 *
	 * @param Placeholder   $placeholder   The Placeholder Class.
	 * @param Builder       $builder       The Content One Class.
	 *
	 * @since 5.0.2
	 */
	public function __construct(Placeholder $placeholder, Builder $builder)
	{
		$this->placeholder = $placeholder;
		$this->builder = $builder;
	}

	/**
	 * Get the updated provider for the given plugin.
	 *
	 * @param  object  $plugin   The plugin object containing the necessary data.
	 *
	 * @return string  The provider content.
	 *
	 * @since 5.0.2
	 */
	public function get(object $plugin): string
	{
		$group = strtolower((string) $plugin->group);

		$provider = [];
		$provider[] = PHP_EOL . PHP_EOL . "return new class () implements ServiceProviderInterface {";
		$provider[] = Indent::_(1) . "/**";
		$provider[] = Indent::_(1) . "*" . Line::_(__Line__, __Class__)
			. " Registers the service provider with a DI container.";
		$provider[] = Indent::_(1) . "*";
		$provider[] = Indent::_(1) . "* @param   Container  \$container  The DI container.";
		$provider[] = Indent::_(1) . "*";
		$provider[] = Indent::_(1) . "* @return  void";
		$provider[] = Indent::_(1) . "* @since   4.3.0";
		$provider[] = Indent::_(1) . "*/";
		$provider[] = Indent::_(1) . "public function register(Container \$container)";
		$provider[] = Indent::_(1) . "{";
		$provider[] = Indent::_(2) . "\$container->set(";
		$provider[] = Indent::_(3) . "PluginInterface::class,";
		$provider[] = Indent::_(3) . "function (Container \$container) {";
		$provider[] = Indent::_(4) . "\$plugin = new {$plugin->class_name}(";
		$provider[] = Indent::_(5) . "(array) PluginHelper::getPlugin('{$group}', '{$plugin->context_name}')";
		$provider[] = Indent::_(4) . ");";
		$provider[] = Indent::_(4) . "\$plugin->setApplication(Factory::getApplication());";
		$provider[] = $plugin->service_provider ?? ''; // to add extra plug-in suff
		$provider[] = Indent::_(4) . "return \$plugin;";
		$provider[] = Indent::_(3) . "}";
		$provider[] = Indent::_(2) . ");";
		$provider[] = Indent::_(1) . "}";
		$provider[] = "};";

		return $this->placeholder->update(
			implode(PHP_EOL, $provider). PHP_EOL,
			$this->builder->allActive()
		);
	}
}

