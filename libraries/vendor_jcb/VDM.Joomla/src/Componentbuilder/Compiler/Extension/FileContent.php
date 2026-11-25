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

namespace VDM\Joomla\Componentbuilder\Compiler\Extension;


use VDM\Joomla\Componentbuilder\Compiler\Registry;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Customcode;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface as Event;
use VDM\Joomla\Componentbuilder\Compiler\Power\Injector as PowerInjector;
use VDM\Joomla\Componentbuilder\Compiler\JoomlaPower\Injector as JoomlaPowerInjector;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\File;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Counter;
use VDM\Joomla\Utilities\FileHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;


/**
 * File Content Class
 * 
 * @since 5.1.2
 */
final class FileContent
{
	/**
	 * The Registry Class.
	 *
	 * @var   Registry
	 * @since 5.1.2
	 */
	protected Registry $registry;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 5.1.2
	 */
	protected Placeholder $placeholder;

	/**
	 * The Customcode Class.
	 *
	 * @var   Customcode
	 * @since 5.1.2
	 */
	protected Customcode $customcode;

	/**
	 * The EventInterface Class.
	 *
	 * @var   Event
	 * @since 5.1.2
	 */
	protected Event $event;

	/**
	 * The Injector Class.
	 *
	 * @var   PowerInjector
	 * @since 5.1.2
	 */
	protected PowerInjector $powerinjector;

	/**
	 * The Injector Class.
	 *
	 * @var   JoomlaPowerInjector
	 * @since 5.1.2
	 */
	protected JoomlaPowerInjector $joomlapowerinjector;

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
	 * The File Class.
	 *
	 * @var   File
	 * @since 5.1.2
	 */
	protected File $file;

	/**
	 * The Counter Class.
	 *
	 * @var   Counter
	 * @since 5.1.2
	 */
	protected Counter $counter;

	/**
	 * Constructor.
	 *
	 * @param Registry              $registry              The Registry Class.
	 * @param Placeholder           $placeholder           The Placeholder Class.
	 * @param Customcode            $customcode            The Customcode Class.
	 * @param Event                 $event                 The EventInterface Class.
	 * @param PowerInjector         $powerinjector         The Injector Class.
	 * @param JoomlaPowerInjector   $joomlapowerinjector   The Injector Class.
	 * @param ContentOne            $contentone            The ContentOne Class.
	 * @param ContentMulti          $contentmulti          The ContentMulti Class.
	 * @param File                  $file                  The File Class.
	 * @param Counter               $counter               The Counter Class.
	 *
	 * @since 5.1.2
	 */
	public function __construct(Registry $registry, Placeholder $placeholder,
		Customcode $customcode, Event $event,
		PowerInjector $powerinjector,
		JoomlaPowerInjector $joomlapowerinjector,
		ContentOne $contentone, ContentMulti $contentmulti,
		File $file, Counter $counter)
	{
		$this->registry = $registry;
		$this->placeholder = $placeholder;
		$this->customcode = $customcode;
		$this->event = $event;
		$this->powerinjector = $powerinjector;
		$this->joomlapowerinjector = $joomlapowerinjector;
		$this->contentone = $contentone;
		$this->contentmulti = $contentmulti;
		$this->file = $file;
		$this->counter = $counter;
	}

	/**
	 * Set the content of a file with dynamic replacements and injections.
	 *
	 * This method:
	 * - Triggers pre-processing events.
	 * - Reads and processes the file.
	 * - Applies placeholders, BOMs, and PHP headers.
	 * - Injects Joomla and SuperPower code.
	 * - Writes the final content back to disk.
	 *
	 * @param   string       $name   The name of the file.
	 * @param   string       $path   The absolute path of the file.
	 * @param   string       $bom    The BOM to add if applicable.
	 * @param   mixed|null   $view   The view context for placeholder replacements.
	 *
	 * @return  void
	 *
	 * @throws  \RuntimeException  If the file cannot be read or written.
	 * @since   5.1.2
	 */
	public function set(string $name, string $path, string $bom, mixed $view = null): void
	{
		$this->event->trigger('jcb_ce_onBeforeSetFileContent', [&$name, &$path, &$bom, &$view]);

		$this->contentone->set('FILENAME', $name);

		$phpHeader = $this->isPhpFile($name) ? '<?php' . PHP_EOL : '';

		$string = FileHelper::getContent($path);

		$this->event->trigger('jcb_ce_onGetFileContents', [&$string, &$name, &$path, &$bom, &$view]);

		if (str_contains((string) $string, (string) Placefix::_h('BOM')))
		{
			[, $code] = explode(Placefix::_h('BOM'), (string) $string, 2);
			$string = $phpHeader . $bom . $code;
		}

		$answer = ($name === 'code.power')
			? $string
			: $this->placeholder->update($string, $this->contentone->allActive(), 3);

		if ($view !== null)
		{
			$placeholders = $this->contentmulti->get($view, []);
			if (is_array($placeholders))
			{
				$answer = $this->placeholder->update($answer, $placeholders, 3);
			}
			else
			{
				throw new \RuntimeException(
					sprintf('Invalid placeholder data for view [%s]. Expected array, got %s.', $view, gettype($placeholders))
				);
			}
			unset($placeholders);
		}

		if ($name !== 'code.power' && $this->registry->exists('update.file.content.' . $path))
		{
			$answer = $this->customcode->update($answer);
		}

		$this->event->trigger('jcb_ce_onBeforeWriteFileContent', [&$answer, &$name, &$path, &$bom, &$view]);

		$answer = $this->joomlapowerinjector->power(
			$this->powerinjector->power($answer)
		);

		$this->file->write($path, $answer);

		$this->counter->line += substr_count((string) $answer, PHP_EOL);
	}

	/**
	 * Convenience wrapper for PHP file type checking.
	 *
	 * @param   string  $file  The filename.
	 *
	 * @return  bool  True if the file has .php extension.
	 * @since   5.1.2
	 */
	protected function isPhpFile(string $file): bool
	{
		return $this->checkFileType($file, 'php');
	}

	/**
	 * Check if a file is of a specific type by suffix.
	 *
	 * @param   string  $file   The file name to check.
	 * @param   string  $suffix The suffix (extension) to check for.
	 *
	 * @return  bool  True if the file ends with the suffix, otherwise false.
	 * @since   5.1.2
	 */
	protected function checkFileType(string $file, string $suffix): bool
	{
		return $suffix === '' || str_ends_with(strtolower($file), '.' . strtolower($suffix));
	}
}

