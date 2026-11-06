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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\CustomView;


use VDM\Joomla\Componentbuilder\Compiler\Builder\CustomAdminViewListId;
use VDM\Joomla\Componentbuilder\Compiler\Builder\CustomForm;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\CustomView\FormInterface;


/**
 * Custom View Form Class.
 * 
 * Builds the form tag a custom view is wrapped in, and the hidden fields and
 * token that close it. Only a view that was found to carry a form gets one.
 * 
 * Where a site form posts to is what the compile target decides, and it is the
 * extension point below.
 * 
 * @since  6.1.7
 */
class Form implements FormInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Custom Form Builder Class.
	 *
	 * @var   CustomForm
	 * @since 6.1.7
	 */
	protected CustomForm $customform;

	/**
	 * The Custom Admin View List Id Builder Class.
	 *
	 * @var   CustomAdminViewListId
	 * @since 6.1.7
	 */
	protected CustomAdminViewListId $customadminviewlistid;

	/**
	 * Constructor.
	 *
	 * @param Config                 $config                 The Config Class.
	 * @param CustomForm             $customform             The Custom Form Builder Class.
	 * @param CustomAdminViewListId  $customadminviewlistid  The Custom Admin View List Id Builder Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		CustomForm $customform,
		CustomAdminViewListId $customadminviewlistid)
	{
		$this->config = $config;
		$this->customform = $customform;
		$this->customadminviewlistid = $customadminviewlistid;
	}

	/**
	 * Build the form tag a custom view is wrapped in.
	 *
	 * @param   string  $view     The view being built.
	 * @param   int     $gettype  What the main get method of the view returns.
	 * @param   int     $type     Which half of the form is wanted, the top or the bottom.
	 *
	 * @return  string  The markup, or nothing when the view carries no form.
	 *
	 * @since   6.1.7
	 */
	public function get(&$view, &$gettype, $type): string
	{
		if ($this->customform->exists($this->config->build_target . "." . $view))
		{
			switch ($type)
			{
				case 1:
					// top
					if ('site' === $this->config->build_target)
					{
						return $this->siteAction($view);
					}
					else
					{
						if ($gettype == 2)
						{
							return '<form action="<?php echo Joomla__'.'_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_(\'index.php?option=com_'
								. $this->config->component_code_name . '&view=' . $view
								. '\'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">'
								. PHP_EOL;
						}
						else
						{
							return '<form action="<?php echo Joomla__'.'_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_(\'index.php?option=com_'
								. $this->config->component_code_name . '&view=' . $view
								. '\' . $urlId); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">'
								. PHP_EOL;
						}
					}
					break;
				case 2:
					// bottom
					$input = '';
					if ('admin' === $this->config->build_target
						&& $this->customadminviewlistid->exists($view))
					{
						$input = PHP_EOL . Indent::_(1)
							. '<input type="hidden" name="id" value="<?php echo $this->app->getInput()->getInt(\'id\', 0); ?>" />';
					}

					return $input . PHP_EOL
						. '<input type="hidden" name="task" value="" />'
						. PHP_EOL . "<?php echo Html::_('form.token'); ?>"
						. PHP_EOL . '</form>';
					break;
			}
		}

		return '';
	}

	/**
	 * The form tag a site view is wrapped in.
	 *
	 * @param   string  $view  The view being built.
	 *
	 * @return  string  The markup.
	 *
	 * @since   6.1.7
	 */
	protected function siteAction(&$view): string
	{
		return '<form action="<?php echo Joomla__'.'_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_(\'index.php?option=com_'
			. $this->config->component_code_name . '&view=' . $view
			. '\'); ?>" method="post" name="adminForm" id="adminForm">'
			. PHP_EOL;
	}
}

