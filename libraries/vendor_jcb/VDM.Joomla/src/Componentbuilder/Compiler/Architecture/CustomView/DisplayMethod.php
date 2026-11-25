<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    16th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\CustomView\DisplayMethodInterface;


/**
 * Custom View Display Method Class.
 * 
 * Generates the body of a site or custom admin view display method: the
 * main and custom Dynamic Get retrieval, the view's own display PHP, the
 * toolbar and document preparation for its build target, the error check,
 * and the content plugin event triggers.
 * 
 * The shared implementation emits the model-based retrieval and the
 * event-object dispatch used from Joomla 5 onwards. Joomla 3 and Joomla 4
 * select the legacy dispatcher mechanics through the extension points
 * below; Joomla 3 additionally selects the legacy view get() proxy.
 * 
 * @since  6.1.7
 */
class DisplayMethod implements DisplayMethodInterface
{
	/**
	 * Whether generated views drain legacy model errors after retrieval.
	 *
	 * @var   bool
	 * @since 6.2.0
	 */
	protected bool $checkErrors = true;

	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

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
	 * @param Config        $config        The Config Class.
	 * @param Placeholder   $placeholder   The Placeholder Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config, Placeholder $placeholder)
	{
		$this->config = $config;
		$this->placeholder = $placeholder;
	}

	/**
	 * Get the site or custom admin view display method code.
	 *
	 * @param   array  $view  The view definition, mutated when custom display
	 *                        PHP is exploded into lines.
	 *
	 * @return  string  The PHP to place in the view display method.
	 *
	 * @since   6.1.7
	 */
	public function get(array &$view): string
	{
		$method = '';
		if (isset($view['settings']->main_get)
			&& ObjectHelper::check($view['settings']->main_get))
		{
			// add events if needed
			if ($view['settings']->main_get->gettype == 1
				&& ArrayHelper::check(
					$view['settings']->main_get->plugin_events
				))
			{
				$method .= $this->getDispatcherInit();
			}

			if ($view['settings']->main_get->gettype == 1)
			{
				// for single views
				$method .= PHP_EOL . Indent::_(2) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Initialise variables.";
				$method .= $this->getItem();
			}
			elseif ($view['settings']->main_get->gettype == 2)
			{
				// for list views
				$method .= PHP_EOL . Indent::_(2) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Initialise variables.";
				$method .= $this->getItems();
				// only add if pagination is requered
				if ($view['settings']->main_get->pagination == 1)
				{
					$method .= $this->getPagination();
				}
			}
			// add the custom get methods
			if (isset($view['settings']->custom_get)
				&& ArrayHelper::check(
					$view['settings']->custom_get
				))
			{
				foreach ($view['settings']->custom_get as $custom_get)
				{
					$custom_get_name = str_replace(
						'get', '', (string) $custom_get->getcustom
					);

					$method .= $this->getCustomGet($custom_get_name, $custom_get->getcustom);
				}
			}
			// add custom script
			if ($view['settings']->add_php_jview_display == 1)
			{
				$view['settings']->php_jview_display = (array) explode(
					PHP_EOL, (string) $view['settings']->php_jview_display
				);
				if (ArrayHelper::check(
					$view['settings']->php_jview_display
				))
				{
					$_tmp   = PHP_EOL . Indent::_(2) . implode(
							PHP_EOL . Indent::_(2),
							$view['settings']->php_jview_display
						);
					$method .= $this->placeholder->update_(
						$_tmp
					);
				}
			}
			if ('site' === $this->config->build_target)
			{
				$method .= PHP_EOL . PHP_EOL . Indent::_(2) . "//"
					. Line::_(__Line__, __Class__) . " Set the toolbar";
				$method .= PHP_EOL . Indent::_(2) . "\$this->addToolBar();";
				$method .= PHP_EOL . PHP_EOL . Indent::_(2) . "//"
					. Line::_(__Line__, __Class__) . " Set the html view document stuff";
				$method .= PHP_EOL . Indent::_(2)
					. "\$this->_prepareDocument();";
			}
			elseif ('custom_admin' === $this->config->build_target)
			{
				$method .= PHP_EOL . PHP_EOL . Indent::_(2) . "//"
					. Line::_(__Line__, __Class__)
					. " We don't need toolbar in the modal window.";
				$method .= PHP_EOL . Indent::_(2)
					. "if (\$this->getLayout() !== 'modal')";
				$method .= PHP_EOL . Indent::_(2) . "{";
				$method .= PHP_EOL . Indent::_(3) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " add the tool bar";
				$method .= PHP_EOL . Indent::_(3) . "\$this->addToolBar();";
				$method .= PHP_EOL . Indent::_(2) . "}";

				$method .= $this->getSetDocument();
			}

			if ($this->checkErrors)
			{
				$method .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Check for errors.";

				$method .= $this->getErrorsCheck();

				$method .= PHP_EOL . Indent::_(2) . "{";
				$method .= PHP_EOL . Indent::_(3)
					. "throw new \Exception(implode(PHP_EOL, \$errors), 500);";
				$method .= PHP_EOL . Indent::_(2) . "}";
			}
			// add events if needed
			if ($view['settings']->main_get->gettype == 1
				&& ArrayHelper::check(
					$view['settings']->main_get->plugin_events
				))
			{
				$method .= PHP_EOL . Indent::_(2) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Process the content plugins.";
				$method .= PHP_EOL . Indent::_(2) . "if ("
					. "Super_" . "__91004529_94a9_4590_b842_e7c6b624ecf5___Power::check(\$this->item))";
				$method .= PHP_EOL . Indent::_(2) . "{";
				$method .= PHP_EOL . Indent::_(3)
					. "Joomla__" . "_7934665b_e432_4ec6_b38d_27bf32730eb9___Power::importPlugin('content');";
				$method .= PHP_EOL . Indent::_(3) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Setup Event Object.";
				$method .= PHP_EOL . Indent::_(3)
					. "\$this->item->event = new \stdClass;";
				$method .= PHP_EOL . Indent::_(3) . "//" . Line::_(
						__LINE__,__CLASS__
					) . " Check if item has params, or pass global params";
				$method .= PHP_EOL . Indent::_(3)
					. "\$params = (isset(\$this->item->params) && "
					. "Super_" . "__4b225c51_d293_48e4_b3f6_5136cf5c3f18___Power::check(\$this->item->params)) ? json_decode(\$this->item->params) : \$this->params;";
				// load the defaults
				foreach ($view['settings']->main_get->plugin_events as $plugin_event)
				{
					// load the events
					if ('onContentPrepare' === $plugin_event)
					{
						// the onContentPrepare already gets triggered on the fields of its relation
					}
					else
					{
						$method .= $this->getPluginEvent(
							$plugin_event, $view['settings']->context
						);

						$method .= PHP_EOL . Indent::_(3)
							. '$this->item->event->' . $plugin_event
							. ' = trim(implode("\n", $results));';
					}
				}
				$method .= PHP_EOL . Indent::_(2) . "}";
			}
		}

		return $method;
	}

	/**
	 * Get the generated event-dispatcher initialization lines.
	 *
	 * From Joomla 5 the view dispatches through its own dispatcher, so no
	 * initialization line is generated.
	 *
	 * @return  string  The generated dispatcher lines.
	 *
	 * @since   6.1.7
	 */
	protected function getDispatcherInit(): string
	{
		return '';
	}

	/**
	 * Get the generated single-item retrieval line.
	 *
	 * @return  string  The generated item line.
	 *
	 * @since   6.1.7
	 */
	protected function getItem(): string
	{
		return PHP_EOL . Indent::_(2)
			. "\$this->item = \$model->getItem();";
	}

	/**
	 * Get the generated list-items retrieval line.
	 *
	 * @return  string  The generated items line.
	 *
	 * @since   6.1.7
	 */
	protected function getItems(): string
	{
		return PHP_EOL . Indent::_(2)
			. "\$this->items = \$model->getItems();";
	}

	/**
	 * Get the generated pagination retrieval line.
	 *
	 * @return  string  The generated pagination line.
	 *
	 * @since   6.1.7
	 */
	protected function getPagination(): string
	{
		return PHP_EOL . Indent::_(2)
			. "\$this->pagination = \$model->getPagination();";
	}

	/**
	 * Get the generated custom Dynamic Get retrieval line.
	 *
	 * @param   string  $name    The custom get name without its get prefix.
	 * @param   string  $method  The complete custom get method name.
	 *
	 * @return  string  The generated custom get line.
	 *
	 * @since   6.1.7
	 */
	protected function getCustomGet(string $name, string $method): string
	{
		return PHP_EOL . Indent::_(2) . "\$this->"
			. StringHelper::safe($name)
			. " = \$model->{$method}();";
	}

	/**
	 * Get the generated custom-admin document lines.
	 *
	 * Only Joomla 3 sets the document from the custom admin view.
	 *
	 * @return  string  The generated document lines.
	 *
	 * @since   6.1.7
	 */
	protected function getSetDocument(): string
	{
		return '';
	}

	/**
	 * Get the generated error-check condition line.
	 *
	 * @return  string  The generated error-check line.
	 *
	 * @since   6.1.7
	 */
	protected function getErrorsCheck(): string
	{
		return PHP_EOL . Indent::_(2)
			. "if (count(\$errors = \$model->getErrors()))";
	}

	/**
	 * Get the generated content plugin event trigger lines.
	 *
	 * @param   string  $pluginEvent  The plugin event name.
	 * @param   string  $context      The view context.
	 *
	 * @return  string  The generated event trigger lines.
	 *
	 * @since   6.1.7
	 */
	protected function getPluginEvent(string $pluginEvent, string $context): string
	{
		$joomla_power = 'error';
		if ('onContentAfterTitle' === $pluginEvent)
		{
			$joomla_power = "Joomla__" . "_fa9c1320_a115_452a_a0a8_534fcdea490b___Power";
		}
		elseif ('onContentBeforeDisplay' === $pluginEvent)
		{
			$joomla_power = "Joomla__" . "_fc1ab159_0df1_4be8_babd_0bd18d35f467___Power";
		}
		elseif ('onContentAfterDisplay' === $pluginEvent)
		{
			$joomla_power = "Joomla__" . "_a42c4e8e_ead1_442d_8b9a_99236a1ee9a9___Power";
		}

		$method = PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__) . " {$pluginEvent} Event Trigger";
		$method .= PHP_EOL . Indent::_(3) . "\$results = \$this->getDispatcher()->dispatch('{$pluginEvent}',";
		$method .= PHP_EOL . Indent::_(4) . "new {$joomla_power}(";
		$method .= PHP_EOL . Indent::_(5) . "'{$pluginEvent}',";
		$method .= PHP_EOL . Indent::_(5) . "[";
		$method .= PHP_EOL . Indent::_(6) . "'context' => '" . $this->config->component_code_name . "." . $context . "',";
		$method .= PHP_EOL . Indent::_(6) . "'subject' => \$this->item,";
		$method .= PHP_EOL . Indent::_(6) . "'params' => \$params,";
		$method .= PHP_EOL . Indent::_(6) . "'page' => 0";
		$method .= PHP_EOL . Indent::_(5) . "]";
		$method .= PHP_EOL . Indent::_(4) . ")";
		$method .= PHP_EOL . Indent::_(3) . ")->getArgument('result', []);";

		return $method;
	}

	/**
	 * Get the legacy event-dispatcher initialization lines.
	 *
	 * Joomla 3 and Joomla 4 obtain the global event dispatcher instance
	 * before the content plugins are processed.
	 *
	 * @return  string  The generated dispatcher lines.
	 *
	 * @since   6.1.7
	 */
	protected function getLegacyDispatcherInit(): string
	{
		$method = PHP_EOL . Indent::_(2) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " Initialise dispatcher.";
		$method .= PHP_EOL . Indent::_(2)
			. "\$dispatcher = JEventDispatcher::getInstance();";

		return $method;
	}

	/**
	 * Get the legacy content plugin event trigger lines.
	 *
	 * Joomla 3 and Joomla 4 trigger content plugins through the global
	 * event dispatcher with a positional argument array.
	 *
	 * @param   string  $pluginEvent  The plugin event name.
	 * @param   string  $context      The view context.
	 *
	 * @return  string  The generated event trigger lines.
	 *
	 * @since   6.1.7
	 */
	protected function getLegacyPluginEvent(string $pluginEvent, string $context): string
	{
		$method = PHP_EOL . Indent::_(3) . "//"
			. Line::_(__Line__, __Class__) . " " . $pluginEvent . " Event Trigger.";
		$method .= PHP_EOL . Indent::_(3)
			. "\$results = \$dispatcher->trigger('"
			. $pluginEvent . "', array('com_"
			. $this->config->component_code_name . "."
			. $context
			. "', &\$this->item, &\$params, 0));";

		return $method;
	}
}

