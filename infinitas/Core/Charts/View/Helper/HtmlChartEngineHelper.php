<?php
/**
 * Html Chart Engine Helper
 *
 * Chart engine for generating HTML charts
 *
 * @link http://infinitas-cms.org/infinitas_docs/Charts Infinitas Charts
 *
 * @package Infinitas.Charts.Helper
 */

App::uses('ChartsBaseEngineHelper', 'Charts.Lib');

/**
 * Html chart engine
 *
 * This is an example of engine use, that will generate a range of html based
 * charts. It is automatically called via the ChartsHelper and should not be used
 * directly for generating charts.
 *
 * To use this set up your controller with something like the following:
 * <code>
 *	public $helpers = array(
 *		...
 *		'Charts.Charts' => array(
 *			'Charts.Html'
 *		)
 *	);
 * </code>
 *
 * Then in your code you will just call it in your views like below:
 * <code>
 *	<?php echo $this->Charts->draw('bar', $dataArray); ?>
 * </code>
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://infinitas-cms.org/infinitas_docs/Charts Infinitas Charts
 * @package Infinitas.Charts.Helper
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class HtmlChartEngineHelper extends ChartsBaseEngineHelper {

/**
 * Some helpers that are needed internally for this helper to function
 *
 * @var array
 */
	public $helpers = array(
		'Html'
	);

/**
 * a small template for the charts.
 *
 * @var string
 */
	private $__chartWrapper = '<div class="html-chart bar verticle">%s %s</div>';

/**
 * Generate a html markup based bar chart.
 *
 * @link http://meyerweb.com/eric/css/edge/bargraph/demo.html
 *
 * @param array $data the formatted data from the ChartsHelper
 *
 * @return string
 */
	public function bar($data) {
		$legend = '';
		$chart = '';

		$y = $rows = $cols = array();
		$last = 0;
		foreach ($data['data'][0] as $key => $value) {
			$change = ($value > $last) ? __d('charts', 'up (%s%%)') : __d('charts', 'down (%s%%)');
			$change = sprintf($change, abs($last - $value));
			if ($value == $last) {
				$change = __d('charts', 'no change');
			}
			$cols[] = sprintf(
				'<td class="col" title="%s"><div class="empty e%d"></div><div class="fill f%d"></div></td>',
				sprintf($data['tooltip'], $value, round($data['values']['max'] * ($value / 100)), $change),
				100 - $value,
				$value
			);
			$last = $value;
		}

		foreach ($data['labels']['y'] as $label) {
			$y[] = $label;
		}

		rsort($y);

		$rows[] = '<tr class="data"><td class="y-axis"><table><tr><td>' .
			implode('</td></tr><tr><td>', $y) . '</td></tr></table></td>' .
			implode('', $cols) . '</tr>';
		$rows[] = '<tr class="x-axis"><td>&nbsp;</td><td>' .
			implode('</td><td>', $data['labels'][$data['axes'][0]]) . '</td><tr>';

		$chart = sprintf(
			'<table>%s</table><div class="legend">%s</div>',
			implode('', $rows),
			$legend
		);

		$html = sprintf($this->__chartWrapper, $data['title'], $chart);

		return sprintf(
			'%s<style type=text/css>%s %s</style>%s',
			$this->Html->css('Charts.html_chart_engine'),
			$this->__generateBarCss($data),
			$this->__css($data),
			$html
		);
	}

/**
 * build some on-the-fly css for the chart.
 *
 * @param array $data the data for the chart
 *
 * @return string
 */
	private function __generateBarCss($data) {
		$colWidth = (($data['width'] + $data['spacing']['padding']) / count($data['data'][0]) / $data['width']) * 100;
		$margin = round($data['spacing']['padding'] / 2);
		$colWidth -= $data['spacing']['padding'];

		return <<<cssData
	.html-chart.bar.verticle{
		width: {$data['width']}px;
		height: {$data['height']}px;
		background-color: #{$data['color']['background']};
		color: #{$data['color']['text']};
		border-bottom: 1px solid #{$data['color']['lines']};
	}

	.html-chart.bar.verticle .y-axis{
		border-right: 1px solid #{$data['color']['lines']};
	}

	.html-chart.bar.verticle table{
		height: {$data['height']}px;
	}

	.html-chart.bar.verticle td.col div{
		margin-left: {$margin}px;
		margin-right: {$margin}px;
	}

	.html-chart.bar.verticle .empty{
		background-color: #{$data['color']['background']};
	}

	.html-chart.bar.verticle .fill{
		background-color: #{$data['color']['fill']};
	}

cssData;
	}

/**
 * Generate css for the charts
 *
 * @param array $data options for the css
 *
 * @return string
 */
	private function __css($data) {
		$css = array();
		foreach (range(1, 100) as $num) {
			$css[] = '.html-chart.bar.verticle .empty.e' . $num . ', .html-chart.bar.verticle .fill.f' .
				$num . ' {height: ' . round($num * ($data['height'] / 100)) . 'px;}' . "\n";
		}

		return implode('', $css);
	}

}