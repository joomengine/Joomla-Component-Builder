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

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###
use Joomla\CMS\WebAsset\WebAssetManager;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Google Charts helper retaining the constructor, load and draw project API.
 *
 * @since 6.2.0
 */
class Chartbuilder
{
	/**
	 * Number of chart instances created in this response.
	 *
	 * @var    int
	 * @since  6.2.0
	 */
	public static $_count = 0;

	/**
	 * Visualization class to construct.
	 *
	 * @var    string
	 * @since  6.2.0
	 */
	private string $chartType;

	/**
	 * JSON DataTable input.
	 *
	 * @var    string
	 * @since  6.2.0
	 */
	private string $data = '{"cols":[],"rows":[]}';

	/**
	 * Whether the first row contains column labels.
	 *
	 * @var    bool
	 * @since  6.2.0
	 */
	private bool $skipFirstRow;

	/**
	 * Unique response-local chart identifier.
	 *
	 * @var    int
	 * @since  6.2.0
	 */
	private int $chartId;

	/**
	 * Draw calls made by this instance in the current response.
	 *
	 * @var    int
	 * @since  6.2.0
	 */
	private int $drawCount = 0;

	/**
	 * Select a Google visualization chart type.
	 *
	 * @param   string  $chartType     Google visualization class name.
	 * @param   bool    $skipFirstRow  Whether the first row contains labels.
	 *
	 * @since   6.2.0
	 */
	public function __construct($chartType, $skipFirstRow = false)
	{
		$this->chartType = (string) $chartType;
		$this->skipFirstRow = (bool) $skipFirstRow;
		$this->chartId = ++self::$_count;
	}

	/**
	 * Load JSON DataTable data or an array of rows.
	 *
	 * @param   mixed   $data      DataTable JSON or rows.
	 * @param   string  $dataType  The historical json/array selector.
	 *
	 * @return  void
	 * @since   6.2.0
	 */
	public function load($data, $dataType = 'json')
	{
		$this->data = $dataType === 'json' ? (string) $data : $this->dataToJson((array) $data);
	}

	/**
	 * Schedule a chart through the document's Web Asset Manager.
	 *
	 * @param   string  $div      Element ID that receives the chart.
	 * @param   array   $options  Google options plus optional tabclickdraw class.
	 *
	 * @return  string  Empty markup; WAM renders the registered script.
	 * @since   6.2.0
	 */
	public function draw($div, array $options = [])
	{
		$assets = $this->assets();

		if (!$assets->assetExists('script', 'google.charts'))
		{
			$assets->registerScript('google.charts', 'https://www.gstatic.com/charts/loader.js');
		}

		$assets->useScript('google.charts');
		$flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR;
		$divCode = json_encode((string) $div, $flags);
		$typeCode = json_encode($this->chartType, $flags);
		$dataCode = json_encode(json_decode($this->data, true, 512, JSON_THROW_ON_ERROR), $flags);
		$tabCode = json_encode((string) ($options['tabclickdraw'] ?? ''), $flags);
		unset($options['tabclickdraw']);
		$optionsCode = json_encode($options, $flags);
		$script = <<<JS
(function () {
	const initialise = function () {
		google.charts.load('current', {packages: ['corechart']});
		google.charts.setOnLoadCallback(function () {
			const element = document.getElementById({$divCode});
			if (!element) { return; }
			const chart = new google.visualization[{$typeCode}](element);
			const data = new google.visualization.DataTable({$dataCode});
			const originalOptions = {$optionsCode};
			const draw = function () {
				const options = Object.assign({}, originalOptions);
				const ratio = window.innerWidth < 1000 ? window.innerWidth / 1200 : 1;
				if (typeof options.height === 'number') { options.height *= ratio; }
				if (typeof options.width === 'number') { options.width *= ratio; }
				chart.draw(data, options);
			};
			draw();
			window.addEventListener('resize', draw);
			const tabClass = {$tabCode};
			if (tabClass) { Array.from(document.getElementsByClassName(tabClass)).forEach(function (tab) { tab.addEventListener('click', draw); }); }
			document.querySelectorAll('.icon').forEach(function (icon) { icon.addEventListener('click', draw); });
		});
	};
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initialise, {once: true}); }
	else { initialise(); }
})();
JS;
		$assets->addInlineScript($script, ['name' => 'com_###component###.chart.' . $this->chartId . '.' . ++$this->drawCount], [], ['google.charts']);

		return '';
	}

	/**
	 * Get the current document asset graph.
	 *
	 * @return  WebAssetManager
	 * @since   6.2.0
	 */
	private function assets(): WebAssetManager
	{
		return Joomla___39403062_84fb_46e0_bac4_0023f766e827___Power::getApplication()->getDocument()->getWebAssetManager();
	}

	/**
	 * Convert the historical array format into a Google DataTable.
	 *
	 * @param   array  $data  Input rows with numeric labels or associative keys.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	private function dataToJson(array $data): string
	{
		$data = array_values($data);
		$columns = [];

		foreach ($data[0] ?? [] as $key => $value)
		{
			$numeric = is_numeric($key);
			$sample = $numeric ? ($data[1][$key] ?? '') : $value;
			$columns[] = ['id' => '', 'label' => $numeric ? $value : $key, 'type' => is_string($sample) ? 'string' : 'number'];
			$this->skipFirstRow = $this->skipFirstRow || $numeric;
		}

		$rows = [];

		foreach ($data as $index => $row)
		{
			if ($index === 0 && $this->skipFirstRow)
			{
				continue;
			}

			$rows[] = ['c' => array_map(static fn ($value) => ['v' => $value], array_values($row))];
		}

		return json_encode(['cols' => $columns, 'rows' => $rows], JSON_THROW_ON_ERROR);
	}
}
