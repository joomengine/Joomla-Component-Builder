<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    23rd October, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\CustomView;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView\SubmitButtonScript as SharedSubmitButtonScript;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Register custom-view submit handlers through the Joomla 6 asset manager.
 * 
 * @since  6.2.0
 */
final class SubmitButtonScript extends SharedSubmitButtonScript
{
	/**
	 * The compiler target configuration.
	 *
	 * @var    Config
	 * @since  6.2.0
	 */
	protected Config $config;

	/**
	 * Constructor.
	 *
	 * @param   Config  $config  The compiler target configuration.
	 *
	 * @since   6.2.0
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * Register the unchanged handler after Joomla core without writing a script tag.
	 *
	 * @param   string  $script  The submit handler source.
	 * @param   string  $name    The custom view code name.
	 *
	 * @return  string  The generated template fragment.
	 * @since   6.2.0
	 */
	protected function renderScript(string $script, string $name): string
	{
		$assetName = 'com_' . $this->config->component_code_name . '.' . $name . '.submit';

		$lines = explode(PHP_EOL, $script);
		$code = PHP_EOL . Indent::_(0) . '<?php';
		$code .= PHP_EOL . Indent::_(0) . '$jcbSubmitScript = ' . var_export(array_shift($lines), true);

		foreach ($lines as $line)
		{
			$code .= PHP_EOL . Indent::_(1) . '. PHP_EOL . ' . var_export($line, true);
		}

		$code .= ';';
		$code .= PHP_EOL . Indent::_(0) . '$this->getDocument()->getWebAssetManager()->addInlineScript(';
		$code .= PHP_EOL . Indent::_(1) . '$jcbSubmitScript, [\'name\' => ' . var_export($assetName, true) . "], [], ['core']";
		$code .= PHP_EOL . Indent::_(0) . ');';
		$code .= PHP_EOL . Indent::_(0) . '?>';

		return $code;
	}
}

