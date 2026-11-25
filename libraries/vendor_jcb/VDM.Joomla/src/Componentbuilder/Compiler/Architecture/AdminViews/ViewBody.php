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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminViews;


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Builder\AdminFilterType;
use VDM\Joomla\Componentbuilder\Compiler\Templatelayout\Data as TemplatelayoutData;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface as Event;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\AdminViews\ViewBodyInterface;


/**
 * Admin List View Body Class.
 * 
 * Generates the default and modal body layouts of an admin list view: the
 * form wrapper, the search-tools or sidebar filter controls, the empty
 * result notice, and the list table that loads the head, foot and body
 * templates.
 * 
 * Extension events are triggered at the top of the body, inside the form,
 * at the end of the form and at the end of the body. Their names, order,
 * and by-reference arguments are a compatibility seam.
 * 
 * The shared implementation emits the single container used from Joomla 4
 * onwards; the Joomla 3 variant adds the sidebar container and the batch
 * processing modal.
 * 
 * @since  6.1.7
 */
class ViewBody implements ViewBodyInterface
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The AdminFilterType Class.
	 *
	 * @var   AdminFilterType
	 * @since 6.1.7
	 */
	protected AdminFilterType $adminfiltertype;

	/**
	 * The Templatelayout Data Class.
	 *
	 * @var   TemplatelayoutData
	 * @since 6.1.7
	 */
	protected TemplatelayoutData $templatelayoutdata;

	/**
	 * The Event Class.
	 *
	 * @var   Event
	 * @since 6.1.7
	 */
	protected Event $event;

	/**
	 * Constructor.
	 *
	 * @param Config               $config               The Config Class.
	 * @param AdminFilterType      $adminfiltertype      The AdminFilterType Class.
	 * @param TemplatelayoutData   $templatelayoutdata   The Templatelayout Data Class.
	 * @param Event                $event                The Event Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config, AdminFilterType $adminfiltertype,
		TemplatelayoutData $templatelayoutdata, Event $event)
	{
		$this->config = $config;
		$this->adminfiltertype = $adminfiltertype;
		$this->templatelayoutdata = $templatelayoutdata;
		$this->event = $event;
	}

	/**
	 * Get the default admin list view body.
	 *
	 * @param   string  $nameSingleCode  The single code name of the view.
	 * @param   string  $nameListCode    The list code name of the view.
	 *
	 * @return  string  The generated default view body.
	 *
	 * @since   6.1.7
	 */
	public function getDefault(string $nameSingleCode, string $nameListCode): string
	{
		// set component name
		$component = $this->config->component_code_name;
		$Component = ucfirst((string) $component);
		$COMPONENT = strtoupper((string) $component);
		// set uppercase view
		$VIEWS = strtoupper($nameListCode);
		// build the body
		$body = [];
		// check if the filter type is sidebar (1 = sidebar)
		if ($this->adminfiltertype->get($nameListCode, 1) == 1)
		{
			foreach ($this->getOrderingScript() as $line)
			{
				$body[] = $line;
			}
		}
		// Trigger Event: jcb_ce_onSetDefaultViewsBodyTop
		$this->event->trigger(
			'jcb_ce_onSetDefaultViewsBodyTop', [&$body, &$nameSingleCode, &$nameListCode]
		);
		$body[] = "<form action=\"<?php echo Joomla__" . "_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_"
			. $component . "&view=" . $nameListCode
			. "'); ?>\" method=\"post\" name=\"adminForm\" id=\"adminForm\">";
		foreach ($this->getContainerOpen() as $line)
		{
			$body[] = $line;
		}
		// Trigger Event: jcb_ce_onSetDefaultViewsFormTop
		$this->event->trigger(
			'jcb_ce_onSetDefaultViewsFormTop', [&$body, &$nameSingleCode, &$nameListCode]
		);
		// check if the filter type is sidebar (2 = topbar)
		if ($this->adminfiltertype->get($nameListCode, 1) == 2)
		{
			foreach ($this->getSearchTools($nameListCode) as $line)
			{
				$body[] = $line;
			}
		}
		$body[] = "<?php if (empty(\$this->items)): ?>";
		// check if the filter type is sidebar (1 = sidebar)
		if ($this->adminfiltertype->get($nameListCode, 1) == 1)
		{
			$body[] = Indent::_(1)
				. "<?php echo \$this->loadTemplate('toolbar');?>";
		}
		$body[] = Indent::_(1)
			. "<div class=\"alert alert-no-items\">";
		$body[] = Indent::_(2)
			. "<?php echo Joomla__" . "_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>";
		$body[] = Indent::_(1) . "</div>";
		$body[] = "<?php else : ?>";
		// check if the filter type is sidebar (1 = sidebar)
		if ($this->adminfiltertype->get($nameListCode, 1) == 1)
		{
			$body[] = Indent::_(1)
				. "<?php echo \$this->loadTemplate('toolbar');?>";
		}
		foreach ($this->getTable($nameSingleCode) as $line)
		{
			$body[] = $line;
		}
		foreach ($this->getBatchModal($COMPONENT, $VIEWS) as $line)
		{
			$body[] = $line;
		}
		// check if the filter type is sidebar (1 = sidebar)
		if ($this->adminfiltertype->get($nameListCode, 1) == 1)
		{
			$body[] = Indent::_(1)
				. "<input type=\"hidden\" name=\"filter_order\" value=\"<?php echo \$this->listOrder; ?>\" />";
			$body[] = Indent::_(1)
				. "<input type=\"hidden\" name=\"filter_order_Dir\" value=\"<?php echo \$this->listDirn; ?>\" />";
		}
		$body[] = Indent::_(1)
			. "<input type=\"hidden\" name=\"boxchecked\" value=\"0\" />";
		$body[] = Indent::_(1) . "</div>";
		$body[] = "<?php endif; ?>";
		$body[] = Indent::_(1)
			. "<input type=\"hidden\" name=\"task\" value=\"\" />";
		$body[] = Indent::_(1) . "<?php echo Html::_('form.token'); ?>";
		// Trigger Event: jcb_ce_onSetDefaultViewsFormBottom
		$this->event->trigger(
			'jcb_ce_onSetDefaultViewsFormBottom', [&$body, &$nameSingleCode, &$nameListCode]
		);
		$body[] = "</form>";
		// Trigger Event: jcb_ce_onSetDefaultViewsBodyBottom
		$this->event->trigger(
			'jcb_ce_onSetDefaultViewsBodyBottom', [&$body, &$nameSingleCode, &$nameListCode]
		);

		return implode(PHP_EOL, $body);
	}

	/**
	 * Get the modal admin list view body.
	 *
	 * The closing event of this body is deliberately the default-views
	 * bottom event; that is the current emitted contract.
	 *
	 * @param   string  $nameSingleCode  The single code name of the view.
	 * @param   string  $nameListCode    The list code name of the view.
	 *
	 * @return  string  The generated modal view body.
	 *
	 * @since   6.1.7
	 */
	public function getModal(string $nameSingleCode, string $nameListCode): string
	{
		// set component name
		$component = $this->config->component_code_name;
		$Component = ucfirst((string) $component);
		$COMPONENT = strtoupper((string) $component);
		// set uppercase view
		$VIEWS = strtoupper($nameListCode);
		// build the body
		$body = [];
		// Trigger Event: jcb_ce_onSetModalViewsBodyTop
		$this->event->trigger(
			'jcb_ce_onSetModalViewsBodyTop', [&$body, &$nameSingleCode, &$nameListCode]
		);
		$body[] = "<form action=\"<?php echo Joomla__" . "_d4c76099_4c32_408a_8701_d0a724484dfd___Power::_('index.php?option=com_"
			. $component . "&view=" . $nameListCode
			. "&layout=modal&tmpl=component&titleKey=' . \$this->getModalTitleKey()); ?>\" method=\"post\" name=\"adminForm\" id=\"adminForm\">";
		$body[] = Indent::_(1)
			. "<div id=\"j-main-container\">";
		// Trigger Event: jcb_ce_onSetModalViewsFormTop
		$this->event->trigger(
			'jcb_ce_onSetModalViewsFormTop', [&$body, &$nameSingleCode, &$nameListCode]
		);
		// check if the filter type is sidebar (2 = topbar)
		if ($this->adminfiltertype->get($nameListCode, 1) == 2)
		{
			foreach ($this->getSearchTools($nameListCode) as $line)
			{
				$body[] = $line;
			}
		}
		$body[] = "<?php if (empty(\$this->items)): ?>";
		$body[] = Indent::_(1)
			. "<div class=\"alert alert-no-items\">";
		$body[] = Indent::_(2)
			. "<?php echo Joomla__" . "_ba6326ef_cb79_4348_80f4_ab086082e3c5___Power::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>";
		$body[] = Indent::_(1) . "</div>";
		$body[] = "<?php else : ?>";
		foreach ($this->getTable($nameSingleCode) as $line)
		{
			$body[] = $line;
		}
		$body[] = Indent::_(1)
			. "<input type=\"hidden\" name=\"boxchecked\" value=\"0\" />";
		$body[] = Indent::_(1) . "</div>";
		$body[] = "<?php endif; ?>";
		$body[] = Indent::_(1)
			. "<input type=\"hidden\" name=\"task\" value=\"\" />";
		$body[] = Indent::_(1) . "<?php echo Html::_('form.token'); ?>";
		// Trigger Event: jcb_ce_onSetModalViewsFormBottom
		$this->event->trigger(
			'jcb_ce_onSetModalViewsFormBottom', [&$body, &$nameSingleCode, &$nameListCode]
		);
		$body[] = "</form>";
		// Trigger Event: the modal body closes on the default-views bottom event
		$this->event->trigger(
			'jcb_ce_onSetDefaultViewsBodyBottom', [&$body, &$nameSingleCode, &$nameListCode]
		);

		return implode(PHP_EOL, $body);
	}

	/**
	 * Get the generated main container opening lines.
	 *
	 * From Joomla 4 the list view has a single main container.
	 *
	 * @return  array<int, string>  The generated container lines.
	 *
	 * @since   6.1.7
	 */
	protected function getContainerOpen(): array
	{
		return [
			Indent::_(1) . "<div id=\"j-main-container\">",
		];
	}

	/**
	 * Get the generated batch processing modal lines.
	 *
	 * Only Joomla 3 renders the batch modal from the list view body.
	 *
	 * @param   string  $COMPONENT  The upper case component code name.
	 * @param   string  $VIEWS      The upper case list code name.
	 *
	 * @return  array<int, string>  The generated batch modal lines.
	 *
	 * @since   6.1.7
	 */
	protected function getBatchModal(string $COMPONENT, string $VIEWS): array
	{
		return [];
	}

	/**
	 * Get the generated search-tools lines of a top-bar filter list view.
	 *
	 * @param   string  $nameListCode  The list code name of the view.
	 *
	 * @return  array<int, string>  The generated search-tools lines.
	 *
	 * @since   6.1.7
	 */
	protected function getSearchTools(string $nameListCode): array
	{
		$lines = [];
		$lines[] = "<?php";
		// build code to add the trash helper layout
		$addTrashHelper = Indent::_(1)
			. "echo Joomla__" . "_7ab82272_0b3d_4bb1_af35_e63a096cfe0b___Power::render('trashhelper', \$this);";
		// add the trash helper layout if found in JCB
		if ($this->templatelayoutdata->set($addTrashHelper, $nameListCode))
		{
			$lines[] = Indent::_(1) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Add the trash helper layout";
			$lines[] = $addTrashHelper;
		}
		// add the new search toolbar ;)
		$lines[] = Indent::_(1) . "//" . Line::_(
				__LINE__,__CLASS__
			) . " Add the searchtools";
		$lines[] = Indent::_(1)
			. "echo Joomla__" . "_7ab82272_0b3d_4bb1_af35_e63a096cfe0b___Power::render('joomla.searchtools.default', array('view' => \$this));";
		$lines[] = "?>";

		return $lines;
	}

	/**
	 * Get the generated list table lines.
	 *
	 * @param   string  $nameSingleCode  The single code name of the view.
	 *
	 * @return  array<int, string>  The generated table lines.
	 *
	 * @since   6.1.7
	 */
	protected function getTable(string $nameSingleCode): array
	{
		return [
			Indent::_(1) . "<table class=\"table table-striped\" id=\""
				. $nameSingleCode . "List\">",
			Indent::_(2)
				. "<thead><?php echo \$this->loadTemplate('head');?></thead>",
			Indent::_(2)
				. "<tfoot><?php echo \$this->loadTemplate('foot');?></tfoot>",
			Indent::_(2)
				. "<tbody><?php echo \$this->loadTemplate('body');?></tbody>",
			Indent::_(1) . "</table>",
		];
	}

	/**
	 * Build the sidebar ordering script.
	 *
	 * @return  array<int, string>  The generated script lines.
	 * @since   6.2.0
	 */
	protected function getOrderingScript(): array
	{
		$lines = [];
		$lines[] = "<script type=\"text/javascript\">";
		$lines[] = Indent::_(1) . "Joomla.orderTable = function()";
		$lines[] = Indent::_(1) . "{";
		$lines[] = Indent::_(2)
			. "table = document.getElementById(\"sortTable\");";
		$lines[] = Indent::_(2)
			. "direction = document.getElementById(\"directionTable\");";
		$lines[] = Indent::_(2)
			. "order = table.options[table.selectedIndex].value;";
		$lines[] = Indent::_(2)
			. "if (order != '<?php echo \$this->listOrder; ?>')";
		$lines[] = Indent::_(2) . "{";
		$lines[] = Indent::_(3) . "dirn = 'asc';";
		$lines[] = Indent::_(2) . "}";
		$lines[] = Indent::_(2) . "else";
		$lines[] = Indent::_(2) . "{";
		$lines[] = Indent::_(3)
			. "dirn = direction.options[direction.selectedIndex].value;";
		$lines[] = Indent::_(2) . "}";
		$lines[] = Indent::_(2) . "Joomla.tableOrdering(order, dirn, '');";
		$lines[] = Indent::_(1) . "}";
		$lines[] = "</script>";

		return $lines;
	}
}

