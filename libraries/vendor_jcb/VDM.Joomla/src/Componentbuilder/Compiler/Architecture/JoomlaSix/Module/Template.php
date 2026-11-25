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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Module;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\HeaderInterface as Header;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Customcode\DispenserInterface as Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Builder\TemplateData;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Module\TemplateInterface;


/**
 * Module Template Joomla 6
 * 
 * @since 5.1.2
 */
final class Template implements TemplateInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 5.1.2
	 */
	protected Config $config;

	/**
	 * The Header Class.
	 *
	 * @var   Header
	 * @since 5.1.2
	 */
	protected Header $header;

	/**
	 * The Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 5.1.2
	 */
	protected Dispenser $dispenser;

	/**
	 * The TemplateData Class.
	 *
	 * @var   TemplateData
	 * @since 5.1.2
	 */
	protected TemplateData $templatedata;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 5.1.2
	 */
	protected Placeholder $placeholder;

	/**
	 * The ContentOne Class.
	 *
	 * @var   ContentOne
	 * @since 5.1.2
	 */
	protected ContentOne $contentone;

	/**
	 * The ContentMulti Class.
	 *
	 * @var   ContentMulti
	 * @since 5.1.2
	 */
	protected ContentMulti $contentmulti;

	/**
	 * Constructor.
	 *
	 * @param Config         $config         The Config Class.
	 * @param Header         $header         The Header Class.
	 * @param Dispenser      $dispenser      The Dispenser Class.
	 * @param TemplateData   $templatedata   The TemplateData Class.
	 * @param Placeholder    $placeholder    The Placeholder Class.
	 * @param ContentOne     $contentone     The ContentOne Class.
	 * @param ContentMulti   $contentmulti   The ContentMulti Class.
	 *
	 * @since 5.1.2
	 */
	public function __construct(Config $config, Header $header, Dispenser $dispenser,
		TemplateData $templatedata, Placeholder $placeholder,
		ContentOne $contentone, ContentMulti $contentmulti)
	{
		$this->config = $config;
		$this->header = $header;
		$this->dispenser = $dispenser;
		$this->templatedata = $templatedata;
		$this->placeholder = $placeholder;
		$this->contentone = $contentone;
		$this->contentmulti = $contentmulti;
	}

	/**
	 * Get the updated placeholder default template content for the given module.
	 *
	 * @param  object  $module   The module object containing the necessary data.
	 * @param  string  $key      The dispenser key for this given module.
	 *
	 * @return string  The updated placeholder content.
	 * @since  5.1.2
	 */
	public function default(object $module, string $key): string
	{
		$css = (string) $this->dispenser->get('css_views', $key, '', '', true, null, '');
		$default = $this->inlineAsset($module, 'style', $css);
		$body = trim((string) ($module->default ?? ''));

		if ($body !== '')
		{
			$default .= PHP_EOL . $body . PHP_EOL;
		}

		$javascript = (string) $this->dispenser->get('views_footer', $key, '', '', true, null, '');

		if ($javascript !== '')
		{
			$default .= PHP_EOL . '<script type="text/javascript">'
				. $javascript . PHP_EOL . '</script>' . PHP_EOL;
		}

		// return the default content for the model default area
		return $this->placeholder->update(
			$default,
			$this->contentone->allActive()
		);
	}

	/**
	 * Capture a field asset, including embedded PHP, without writing a raw tag.
	 *
	 * @param   object  $module   The compiler module definition.
	 * @param   string  $type     Asset type.
	 * @param   string  $content  Asset source.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function inlineAsset(object $module, string $type, string $content): string
	{
		if ($content === '')
		{
			return '';
		}

		$name = var_export($module->folder_name . '.fields.' . $type . '.', true);
		$method = ucfirst($type);
		$dependencies = $type === 'script' ? "['core', 'jquery']" : '[]';

		return PHP_EOL . Indent::_(0) . '<?php'
			. PHP_EOL . Indent::_(0) . '$jcbModuleBufferLevel = ob_get_level();'
			. PHP_EOL . Indent::_(0) . 'ob_start();'
			. PHP_EOL . Indent::_(0) . 'try'
			. PHP_EOL . Indent::_(0) . '{'
			. PHP_EOL . Indent::_(1) . '?>'
			. PHP_EOL . $content
			. PHP_EOL . Indent::_(0) . '<?php'
			. PHP_EOL . Indent::_(1) . '$jcbModuleAsset = ob_get_contents();'
			. PHP_EOL . Indent::_(0) . '}'
			. PHP_EOL . Indent::_(0) . 'finally'
			. PHP_EOL . Indent::_(0) . '{'
			. PHP_EOL . Indent::_(1) . 'while (ob_get_level() > $jcbModuleBufferLevel)'
			. PHP_EOL . Indent::_(1) . '{'
			. PHP_EOL . Indent::_(2) . 'ob_end_clean();'
			. PHP_EOL . Indent::_(1) . '}'
			. PHP_EOL . Indent::_(0) . '}'
			. PHP_EOL . Indent::_(0) . '$jcbModuleAssets = Joomla__' . '_39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getDocument()->getWebAssetManager();'
			. PHP_EOL . Indent::_(0) . "\$jcbModuleAssets->addInline{$method}(\$jcbModuleAsset, ['name' => {$name} . hash('sha256', \$jcbModuleAsset)], [], {$dependencies});"
			. PHP_EOL . Indent::_(0) . '?>';
	}

	/**
	 * Get the updated placeholder default header template content for the given module.
	 *
	 * @param  object  $module   The module object containing the necessary data.
	 *
	 * @return string  The updated placeholder content.
	 * @since  5.1.2
	 */
	public function header(object $module): string
	{
		// first add the header
		$add_default_header = (int) ($module->add_default_header ?? 0);
		$default = $add_default_header === 1 ? trim((string) ($module->default_header ?? '')) : '';

		if (empty($default))
		{
			return '';
		}

		// return the header for the model default area
		return PHP_EOL . PHP_EOL . $this->placeholder->update($default, $this->contentone->allActive());
	}

	/**
	 * Get the updated placeholder extra template content for the given module.
	 *
	 * @param  object  $module  The module object containing the necessary data.
	 *
	 * @return void
	 * @since  5.1.2
	 */
	public function extra(object $module): void
	{
		// Build the data key and fetch template data once
		$data = $this->templatedata->get($module->key . '.' . $module->code_name);

		// Nothing to do if there's no data
		if (!is_array($data) || $data === [])
		{
			return;
		}

		// Cache these to avoid repeated calls
		$activePlaceholders = $this->contentone->allActive();

		// --- HEADER (text) ---
		$header = (string) ($this->header->get('module.extra.template.header', $module->class_name) ?? '');
		if ($header !== '')
		{
			$header = PHP_EOL . PHP_EOL . $this->placeholder->update($header, $activePlaceholders);
		}

		foreach ($data as $template => $item)
		{
			$target = StringHelper::safe("MODDEFAULT_HEADER_{$template}", 'U');
			$key    = $module->key . '|' . $target;
			$this->contentmulti->set($key, $header);

			// --- HEADER CODE (php_view) ---
			$phpView     = trim((string) ($item['php_view'] ?? ''));
			$headerCode  = ($phpView === '')
				? ''
				: PHP_EOL . $this->placeholder->update($phpView, $activePlaceholders);

			$target = StringHelper::safe("MODDEFAULT_HEADER_CODE_{$template}", 'U');
			$key    =  $module->key . '|' . $target;
			$this->contentmulti->set($key, $headerCode);

			// --- BODY (html) ---
			$html  = (string) ($item['html'] ?? '');
			$body  = ($html === '')
				? PHP_EOL
				: PHP_EOL . $this->placeholder->update($html, $activePlaceholders);

			$target = StringHelper::safe("MODDEFAULT_{$template}", 'U');
			$key    = $module->key . '|' . $target;
			$this->contentmulti->set($key, $body);
		}
	}
}

