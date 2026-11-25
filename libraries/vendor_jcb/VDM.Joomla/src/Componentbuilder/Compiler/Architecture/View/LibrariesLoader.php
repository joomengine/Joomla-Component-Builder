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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\View;


use VDM\Joomla\Componentbuilder\Compiler\Builder\LibraryManager;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Library\Document;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Registry;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\View\LibrariesLoaderInterface;


/**
 * View Libraries Loader Class.
 * 
 * Builds the statements a view runs to load what it needs before it renders:
 * jQuery, the header checker, and every library the view was linked to.
 * 
 * A module asks for the same statements, and is given them written against the
 * document it holds rather than the one a view reaches for.
 * 
 * How the header checker is reached is what the compile target decides, and it
 * is the extension point below.
 * 
 * @since  6.1.7
 */
class LibrariesLoader implements LibrariesLoaderInterface
{
	/**
	 * Separation before the header checker statements.
	 *
	 * @var    string
	 * @since  6.2.0
	 */
	protected const HEADER_SEPARATOR = '';

	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Compiler Registry Class.
	 *
	 * @var   Registry
	 * @since 6.1.7
	 */
	protected Registry $registry;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 6.1.7
	 */
	protected Placeholder $placeholder;

	/**
	 * The Library Manager Builder Class.
	 *
	 * @var   LibraryManager
	 * @since 6.1.7
	 */
	protected LibraryManager $librarymanager;

	/**
	 * The Library Document Class.
	 *
	 * @var   Document
	 * @since 6.1.7
	 */
	protected Document $document;

	/**
	 * Constructor.
	 *
	 * @param Config          $config          The Config Class.
	 * @param Registry        $registry        The Compiler Registry Class.
	 * @param Placeholder     $placeholder     The Placeholder Class.
	 * @param LibraryManager  $librarymanager  The Library Manager Builder Class.
	 * @param Document        $document        The Library Document Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Registry $registry,
		Placeholder $placeholder,
		LibraryManager $librarymanager,
		Document $document)
	{
		$this->config = $config;
		$this->registry = $registry;
		$this->placeholder = $placeholder;
		$this->librarymanager = $librarymanager;
		$this->document = $document;
	}

	/**
	 * Build the statements that load the libraries a view needs.
	 *
	 * @param   mixed  $view  The view being built, or the module asking for them.
	 *
	 * @return  string  The statements.
	 *
	 * @since   6.1.7
	 */
	public function get($view): string
	{
		// check call sig
		if (isset($view['settings']) && isset($view['settings']->code))
		{
			$code        = $view['settings']->code;
			$view_active = true;
		}
		elseif (isset($view->code_name))
		{
			$code        = $view->code_name;
			$view_active = false;
		}
		// reset bucket
		$setter = '';
		// always load these in
		if ($view_active)
		{
			$setter .= PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Only load jQuery if needed. (default is true)";
			$setter .= PHP_EOL . Indent::_(2) . "if (\$this->params->get('add_jquery_framework', 1) == 1)";
			$setter .= PHP_EOL . Indent::_(2) . "{";
			$setter .= PHP_EOL . Indent::_(3) . $this->jquery();
			$setter .= PHP_EOL . Indent::_(2) . "}";
			$setter .= static::HEADER_SEPARATOR . PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
				. " Load the header checker class.";

			$setter .= $this->headerChecker();
		}
		// check if this view should get libraries
		if (($data = $this->librarymanager->
			get($this->config->build_target . '.' . $code)) !== null)
		{
			foreach ($data as $id => $data_item)
			{
				// get the library
				$library = $this->registry->get("builder.libraries.$id", null);
				if (is_object($library) && isset($library->document)
					&& StringHelper::check($library->document))
				{
					$setter .= PHP_EOL . PHP_EOL . $this->placeholder->update_(
							str_replace(
								[
									'$document->',
									'$this->document->'
								],
								'$this->getDocument()->',
								(string) $library->document
							)
						);
				}
				elseif (is_object($library)
					&& isset($library->how))
				{
					$setter .= $this->document->get($id);
				}
			}
		}
		// convert back to $document if module call (oops :)
		if (!$view_active)
		{
			return str_replace(['$this->getDocument()->', '$this->document->'], '$document->', $setter);
		}

		return $setter;
	}

	/**
	 * Build the jQuery activation statement.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function jquery(): string
	{
		return "Html::_('jquery.framework');";
	}

	/**
	 * Build the statements that make the header checker available.
	 *
	 * @return  string  The statements.
	 *
	 * @since   6.1.7
	 */
	protected function headerChecker(): string
	{
		$setter = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Initialize the header checker.";
		$setter .= PHP_EOL . Indent::_(2) . "\$HeaderCheck = new HeaderCheck();";

		return $setter;
	}
}

