<?php
/**
 * Chart Engine
 *
 * @link http://infinitas-cms.org/infinitas_docs/Charts Infinitas Charts
 *
 * @package Infinitas.Charts.Helper
 */

App::uses('BaseChartEngine', 'Charts.Lib');
App::uses('AppHelper', 'View/Helper');

/**
 * Charts helper is a charting abstraction that is extended by using different
 * engines.
 *
 * The charts helper uses the same sort of Engine pattern found in CakePHP's
 * Js helper. It takes a set of data and formats it to be easilly used by
 * any chart engines.
 *
 * Once the data has been formatted correctly and there are no errors its passed
 * along to the chart engine that was set, to be rendered.
 *
 * Need to add in some caching. If something is cached only the key should be passed
 * along to the engine. This means that the engine should use the same cache key
 * to store a chache of the actuall chart. It could be possible to just pass
 * back the cahce without even calling the engine.
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://infinitas-cms.org/infinitas_docs/Charts Infinitas Charts
 * @package Infinitas.Charts.Helper
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class ChartsHelper extends AppHelper {

/**
 * the engine to use when rendering the chart
 *
 * @var string
 */
	public $engine = null;

/**
 * The raw chart data.
 *
 * @var array
 */
	public $data = array();

/**
 * Normalize data to a percentage
 *
 * should the data be normalized to a base 100, good for high numbers
 * in the data set. look will still be the same as the y axis will use
 * the original data for display
 *
 * @deprecated
 *
 * @var boolean
 */
	public $normalize = true;

/**
 * Current Javascript Engine that is being used
 *
 * @var string
 */
	private $__engineName = null;

/**
 * Defaults for the charts
 *
 * @var array
 */
	private $__defaults = array(
		'title' => 'Chart Title',
		'width' => 640,
		'height' => 480,
		'type' => array(),
		'color' => array(
			'background' => 'FFFFFF',
			'fill' => 'FFCC33',
			'series' => array(),
			'text' => '989898',
			'lines' => '989898',
		),
		'labels' => array(),
		'data' => array(),
		'tooltip' => 'Summary :: <b>%d%%</b> of highest<br/><b>%d</b> for this range<br/><b>%s</b> from last range'
	);

/**
 * Construct the charts object.
 *
 * This will take the settings passed to the helper and set the engine
 * to that value. A default of HtmlChartEngine is used when nothing matches
 *
 * The engine is then added to the helpers array so that it is available
 * for use later on in the request.
 *
 * @param View $View The view class being rendered
 * @param array $settings settings for the chart engines.
 *
 * @return void
 */
	public function __construct(View $View, $settings = array()) {
		$className = 'Html';
		if (is_array($settings) && isset($settings[0])) {
			$className = $settings[0];
		} elseif (is_string($settings)) {
			$className = $settings;
		}

		$engineName = $className;
		list(, $className) = pluginSplit($className);

		$this->__engineName = $className . 'ChartEngine';
		$this->helpers[] = $engineName . 'ChartEngine';
		parent::__construct($View, $settings);
	}

/**
 * draw the chart
 *
 * if passing the data to this method it will call the processing methods
 * and then dispatch the data to the engine that was set. If you have already
 * called the methods to set the data it will be dispatched directly.
 *
 * Below are the methods that will be called depending on the data that
 * is passed to draw. Shown in alphabetical order, but need to be called
 * in a specific order as some methods rely on the data from others. See
 * each method for specifics.
 *
 * * ChartsHelper::setAxes()
 * * ChartsHelper::setColors()
 * * ChartsHelper::setHeight()
 * * ChartsHelper::setLabels()
 * * ChartsHelper::setScale()
 * * ChartsHelper::setSize()
 * * ChartsHelper::setSpacing()
 * * ChartsHelper::setTitle()
 * * ChartsHelper::setTooltip()
 * * ChartsHelper::setType()
 * * ChartsHelper::setWidth()
 *
 * @param mixed $type the type of chart
 * @param array $data the data for the chart
 * @param string $engine the engine to use
 * @throws E_USER_WARNING when the type of chart is not specified
 * @throws E_USER_WARNING when there is no engine chosen
 *
 * @return string
 */
	public function draw($type = '', $data = array(), $engine = null) {
		if (!$type && !isset($this->data['type'])) {
			trigger_error(__d('charts', 'Please specify the chart type'), E_USER_WARNING);
			return false;
		}

		$engine = (string)$engine;
		$this->__engineName = !empty($engine) ? $engine : $this->__engineName;
		if (!$this->__engineName) {
			trigger_error(__d('charts', 'You need to specify the engine to use'), E_USER_WARNING);
			return false;
		}

		if (!empty($data)) {
			$this->__buildChartData($type, $data);
		}

		return $this->__dispatch();
	}

/**
 * Set the type of chart to draw
 *
 * set the type of chart. $type is the method that will be called in the
 * selected engine. if an array is passed the extra details will be sent
 * to data['config'] where the engine will have access to it.
 *
 * @param mixed $type string or array
 *
 * @return ChartsHelper
 */
	public function setType($type = null) {
		if (is_string($type) && !empty($type)) {
			$this->data['type'] = $type;
			return $this;
		} else if (is_array($type) && !empty($type)) {
			$this->data['type'] = current(array_keys($type));
			$this->data['config'] = current($type);
		} else {
			$this->data['type'] = 'nothing';
			trigger_error(__d('charts', 'Could not detect the type of chart'), E_USER_NOTICE);
		}

		return $this;
	}

/**
 * Set the title of the chart
 *
 * @param string $title a title for the chart
 *
 * @return ChartsHelper
 */
	public function setTitle($title = null) {
		// nothing set and somthing from draw()
		if ((!isset($this->data['title']) || empty($this->data['title'])) && isset($this->__originalData['title'])) {
			$this->data['title'] = $this->__originalData['title'];
		}

		// something was passed.
		if ($title) {
			$this->data['title'] = $title;
		}

		// still nothing, just set it to false
		if (!isset($this->data['title']) || !is_string($this->data['title']) || empty($this->data['title'])) {
			$this->data['title'] = false;
		}

		return $this;
	}

/**
 * Set the charts width
 *
 * @param integer $width the width of the chart
 *
 * @throws E_USER_NOTICE if widht is too small or not of type int
 *
 * @return ChartsHelper
 */
	public function setWidth($width) {
		$this->data['size']['width'] = $width;
		if (!is_int($this->data['size']['width']) || (int)$this->data['size']['width'] < 1) {
			$this->data['size']['width'] = $this->__defaults['width'];
			trigger_error(sprintf(__d('charts', 'Width (%s) is not an int or too small, using default'), $width), E_USER_NOTICE);
		}

		return $this;
	}

/**
 * Set the charts height
 *
 * @param integer $height the height of the chart
 *
 * @throws E_USER_NOTICE if widht is too small or not of type int
 *
 * @return ChartsHelper
 */
	public function setHeight($height) {
		$this->data['size']['height'] = $height;
		if (!is_int($this->data['size']['height']) || (int)$this->data['size']['height'] < 1) {
			$this->data['size']['height'] = $this->__defaults['height'];
			trigger_error(sprintf(__d('charts', 'Height (%s) is not an int or too small, using default'), $height), E_USER_NOTICE);
		}

		return $this;
	}

/**
 * Set the width and height in one call
 *
 * set the charts size, can take an array of width / height or just width,
 * an int for width or a comma seperated list of width,height
 *
 * * ChartsHelper::setWidth()
 * * ChartsHelper::setHeight()
 *
 * @param mixed $size string in the form 'w,h' or array
 * @param string $delimiter the delimiter of the data, can be anything defaults to
 *
 * @throws E_USER_WARNING if none, or more than 2 elements are passed for the size.
 *
 * @return ChartsHelper
 */
	public function setSize($size = null, $delimiter = ',') {
		if (!$size && isset($this->__originalData['size'])) {
			$size = $this->__originalData['size'];
		}

		if (!$size && isset($this->__originalData['width'])) {
			$size = $this->__originalData['width'];
			if (isset($this->__originalData['height'])) {
				$size .= $delimiter . $this->__originalData['height'];
			}
		}

		if (!$size) {
			trigger_error(__d('charts', 'Size could not be determined, using default'), E_USER_NOTICE);
			$size = $this->__defaults['width'] . $delimiter . $this->__defaults['height'];
		}

		if (!is_array($size)) {
			$size = explode($delimiter, $size);
		}

		$count = count($size);
		if (isset($size['width'])) {
			$size[0] = $size['width'];
		}
		if (isset($size['height'])) {
			$size[1] = $size['height'];
		}
		switch($count) {
			case 1:
				$this->setWidth((int)trim($size[0]));
				break;

			case 2:
				$this->setWidth((int)trim($size[0]));
				$this->setHeight((int)trim($size[1]));
				break;

			default:
				trigger_error(sprintf(__d('charts', 'Size should be an array of either one or two values, you passed %s'), $count), E_USER_NOTICE);
				break;
		}

		return $this;
	}

/**
 * Set the axes available in the chart.
 *
 * the array passed should be a key => value array where the
 * keys are the axes, the values would be the lables for that axis
 * when the draw() method is passed all the data directly.
 *
 * This method should be called before setting labels as the labels are
 * per axis.
 *
 * @param array $axes the array of axes to set
 *
 * @return ChartsHelper
 */
	public function setAxes($axes = null) {
		if (!$axes && isset($this->__originalData['axes'])) {
			$axes = $this->__originalData['axes'];
		}
		$this->data['axes'] = array_keys((array)$axes);

		$this->setLabels($axes);

		return $this;
	}

/**
 * Build the labels for each axis
 *
 * This method fills each axis with labels, they can either be passed in
 * or generated automaically.
 *
 * * ChartsHelper::__anythingToArray()
 * * ChartsHelper::__defaultLablesFromData()
 *
 * It should only be called after axes have been populated.
 *
 * @param mixed $data the lables can be any delimeted string or an array
 * @param string $delimiter the delimiter to use in the explode
 *
 * @throws E_USER_NOTICE if trying to set before axes has been set
 * @throws E_USER_NOTICE when there is no data set
 *
 * @return ChartsHelper
 */
	public function setLabels($data, $delimiter = ',') {
		if (!isset($this->data['axes'])) {
			trigger_error(__d('charts', 'Axes should be set before labels, skipping'), E_USER_NOTICE);
			return $this;
		}

		if (!isset($this->data['data'])) {
			trigger_error(__d('charts', 'Data should be set before labels, skipping'), E_USER_NOTICE);
			return $this;
		}

		foreach ((array)$this->data['axes'] as $axes) {
			if (!isset($this->__originalData['axes'][$axes]) || $this->__originalData['axes'][$axes] === true) {
				$this->data['labels'][$axes] = $this->__defaultLablesFromData($this->data['data']);
				continue;
			}

			$this->data['labels'][$axes] = $this->__anythingToArray($axes, $data[$axes], (string)$delimiter, true);
		}

		return $this;
	}

/**
 * Set the chart legend
 *
 * @param array $data the charts legend data
 * @param string $delimiter the delimiter
 *
 * @return ChartsHelper
 */
	public function setLegend($data = null, $delimiter = ',') {
		if (!$data) {
			$data = isset($this->__originalData['legend']) ? $this->__originalData['legend'] : array();
		}

		if (!empty($data)) {
			$this->data['legend'] = array_merge(array(
				'position' => null,
				'order' => null,
				'labels' => array()
			), (array)$data);
		}

		return $this;
	}

/**
 * Set the chart data
 *
 * This method sets the actuall data for the chart. if the normalize key
 * is true the data will be converted to a % or 100.
 *
 * - ChartsHelper::__normalizeData()
 *
 * @param array $data array of data to set for the chart ranges
 * @param bool $normalize switch the normalizing on or off passing this param
 *
 * @return ChartsHelper
 */
	public function setData($data = null, $normalize = null) {
		if (!$data) {
			$data = $this->data['data'];
		}

		if (is_bool($normalize)) {
			$this->normalize = $normalize;
		} else if (isset($this->__originalData['normalize']) && is_bool($this->__originalData['normalize'])) {
			$this->normalize = $this->__originalData['normalize'];
		}

		$this->data['data'] = (!$this->normalize) ? $data : $this->__normalizeData($data);

		unset($data);

		return $this;
	}

/**
 * set color options for the chart
 *
 * @param array $colors key values like background -> ff0000
 *
 * @return ChartsHelper
 */
	public function setColors($colors = null) {
		if (!$colors) {
			$colors = isset($this->__originalData['color']) ? $this->__originalData['color'] : array();
		}

		if (!is_array($colors) || empty($colors)) {
			$this->data['color'] = $this->__defaults['color'];
		} else {
			$this->data['color'] = array_merge($this->__defaults['color'], $colors);
		}

		return $this;
	}

/**
 * set the scale and increments for the graph.
 *
 * @param array $data the data for the chart
 * @param integer $increments the number of steps in the axis defaults to 6
 *
 * @return ChartsHelper
 */
	public function setScale($data, $increments = null) {
		// could be nested data sets
		// get min or 0
		// get max
		$increments = ((int)$increments > 0) ? $increments : 6;

		$this->data['scale'] = array(
			'min' => 0,
			'max' => 100,
			'increments' => $increments
		);

		return $this;
	}

/**
 * Set spacing.
 *
 * Adjust the spacing of values and elemnts in the chart passing the
 * options here.
 *
 * @param array $spacing key value array
 *
 * @return ChartsHelper
 */
	public function setSpacing($spacing = null) {
		if (!$spacing) {
			$spacing = isset($this->__originalData['spacing']) ? $this->__originalData['spacing'] : array();
		}

		$this->data['spacing'] = array_merge(
			array(
				'padding' => 0,
				'width' => 0,
			),
			(array)$spacing
		);

		return $this;
	}

/**
 * Set the tool tip
 *
 * Used to set the tool tip pattern that will be applied where possible
 * to the elements in the chart to display some more detailed information
 * about that part of the chart.
 *
 * @param mixed $tooltip the string used through sprintf, pass true to use the default
 *
 * @return ChartsHelper
 */
	public function setTooltip($tooltip = null) {
		if (!$tooltip || !is_string($tooltip)) {
			$tooltip = isset($this->__originalData['tooltip']) ? $this->__originalData['tooltip'] : false;
		}

		$this->data['tooltip'] = isset($this->data['tooltip']) ? $this->data['tooltip'] : null;
		if ($tooltip === true) {
			$this->data['tooltip'] = $this->__defaults['tooltip'];
		} else {
			$this->data['tooltip'] = $tooltip;
		}

		return $this;
	}

/**
 * set some extra data for the engine
 *
 * This is used to pass things like extra params to the engine building
 * the chart.
 *
 * @param array|string $extra the extra data you would like to pass
 *
 * @return void
 */
	public function setExtra($extra = array()) {
		if (!$extra) {
			$extra = isset($this->__originalData['extra']) ? $this->__originalData['extra'] : array();
		}

		$this->data['extra'] = $extra;
	}

/**
 * Build the data array to be passed to the engine selected
 *
 * This will take the data when it is passed to the main method (not using the
 * seperate methods) and call all the required methods to properly format the data
 * so that when passed to the engine its in a standard format.
 *
 * @param mixed $type string type, or array with type and configs
 * @param array $data the data to build the chart
 *
 * @return void
 */
	private function __buildChartData($type, $data) {
		$this->__originalData = $data;

		if (isset($data['normalize'])) {
			$this->normalize = (bool)$data['normalize'];
		}

		$this
			->validateData()
			->setData()
			->setType($type)
			->setTitle()
			->setSize()
			->setAxes()
			->setColors()
			->setSpacing()
			->setTooltip()
			->setLegend()
			->setExtra();
	}

/**
 * Validate the chart data.
 *
 * This makes sure that the data is in a std format and converts any
 * comma seperated lists of data into arrays.
 *
 * * ChartsHelper::__anythingToArray()
 * * ChartsHelper::__getStats()
 *
 * @param array $data the data array for the charts
 *
 * @return ChartsHelper
 */
	public function validateData($data = null) {
		if (!$data) {
			$data = $this->__originalData['data'];
		}

		if (!isset($data[0][0])) {
			if (!is_array($data)) {
				$data = array($data);
			}

			$data = array($data);
		}

		foreach ($data as $k => $v) {
			$this->data['data'][$k] = $this->__anythingToArray('data', $v, ',', true);
		}

		unset($data);

		$this->__getStats();

		return $this;
	}

/**
 * Normalize data to percentage values
 *
 * convert large values to % values so that the data being manipulated
 * is much smaller. There is no difference in the presentation
 *
 * @param array $data the data for the chart to be normalized
 * @param integer $max used internally, do not pass things in here.
 *
 * @deprecated
 *
 * @return array
 */
	private function __normalizeData($data, $max = null) {
		if (!$this->normalize) {
			$this->data['ratio'] = 'fixed';
			return $this;
		}

		$this->data['ratio'] = 'percentage';

		foreach ($data as $k => $_data) {
			foreach ($_data as $kk => $vv) {
				if (is_array($vv)) {
					foreach ($vv as $kkk => $vvv) {
						if ($this->data['values']['max'] == 0) {
							$data[$k][$kk][$kkk] = 0;
							continue;
						}

						$data[$k][$kk][$kkk] = round(($vvv / $this->data['values']['max']) * 100);
					}
					continue;
				}

				$data[$k][$kk] = 0;
				if ($this->data['values']['max']) {
					if ($this->data['values']['max'] == 0) {
						$data[$k][$kk] = 0;
						continue;
					}

					$data[$k][$kk] = round(($vv / $this->data['values']['max']) * 100);
				}
			}
		}

		return $data;
	}

/**
 * Convert strings to arrays.
 *
 * Defaults to comma seperated lists but could be anything like | for
 * example.
 *
 * @param string $field the field that should be set with this data
 * @param mixed $data the string to be exploded
 * @param string $delimiter what to explode on
 * @param bool $return to return the data or just set it in the data array
 *
 * @return array|boolean
 */
	private function __anythingToArray($field, $data, $delimiter = ',', $return = false) {
		if (!$data && isset($this->__originalData[$field])) {
			$data = $this->__originalData[$field];
		}

		if (!is_array($data) && !empty($data)) {
			$data = explode($delimiter, $data);
		}

		if (!$data || empty($data)) {
			if ($return) {
				return false;
			}

			$this->data[$field] = false;
		} else {
			if ($return) {
				return $data;
			}

			$this->data[$field] = $data;
			unset($data);
		}

		return isset($this->data[$field]);
	}

/**
 * Generate labels for the chart.
 *
 * When there is no lables passed but there is a value set for an axis
 * this method is called to build that array of labels. normally used
 * for the y axis it will take the values from the data and build a list
 * in some increment depending on the size of the data values.
 *
 * * ChartsHelper::__getMaxDataValue()
 * * ChartsHelper::__getMinDataValue()
 * * ChartsHelper::__getAverageDataValue()
 *
 * @param array $data the data array to use for building the labels
 *
 * @return array
 */
	private function __defaultLablesFromData($data) {
		$max = $this->__getMaxDataValue($data);
		$min = $this->__getMinDataValue($data);
		$average = $this->__getAverageDataValue($data);

		return range($min, $max, round(($max - $min) / 6, 2));
	}

/**
 * Wrapper for stats.
 *
 * Lazy way to get the various averages, min max etx that is used to
 * workout things like labels, position siezes and build the chart later
 *
 * * ChartsHelper::__getMaxDataValue()
 * * ChartsHelper::__getMinDataValue()
 * * ChartsHelper::__getAverageDataValue()
 *
 * @return void
 */
	private function __getStats() {
		$this->__getMaxDataValue();
		$this->__getMinDataValue();
		$this->__getAverageDataValue();
	}

/**
 * Get the maximum value that is in the data array.
 *
 * The value is cached to the data array and just returned when its set.
 *
 * @param array $data the data array
 *
 * @return integer
 */
	private function __getMaxDataValue($data = null) {
		if (!$data) {
			$data = $this->data['data'];
		}

		if (!isset($this->data['values']['max'])) {
			$this->data['values']['max'] = max(Set::flatten($data));
		}

		unset($data);
		return $this->data['values']['max'];
	}

/**
 * Get the minimum value that is in the data array.
 *
 * The value is cached to the data array and just returned when its set.
 *
 * @param array $data the data array
 *
 * @return integer
 */
	private function __getMinDataValue($data = null) {
		if (!$data) {
			$data = $this->data['data'];
		}

		if (!isset($this->data['values']['min'])) {
			$this->data['values']['min'] = min(Set::flatten($data));
		}

		unset($data);
		return $this->data['values']['min'];
	}

/**
 * Get the average of all data values
 *
 * get the average amount for all the data that was passed for chart
 * rendering.
 *
 * @param array $data the data used for the
 *
 * @return integer
 */
	private function __getAverageDataValue($data = null) {
		if (!$data) {
			$data = $this->data['data'];
		}

		if (!isset($this->data['values']['average'])) {
			$flat = Set::flatten($data);
			$this->data['values']['average'] = round(array_sum($flat) / count($flat));
		}

		unset($data, $flat);
		return $this->data['values']['average'];
	}

/**
 * Send the request to the engine specified.
 *
 * do some final checks and then if all is good trigger the chart engine
 * that is needed and return the chart.
 *
 * @throws CakeException
 *
 * @return string
 */
	private function __dispatch() {
		if (empty($this->data)) {
			throw new CakeException(__d('charts', 'You need to pass data, or use the methods to set data'));
		}

		if (!is_callable(array($this->{$this->__engineName}, $this->data['type']))) {
			throw new CakeException(sprintf('(%s) does not have a (%s) chart type', get_class($this->{$this->__engineName}), $this->data['type']));
		}

		$chart = $this->{$this->__engineName}->{$this->data['type']}($this->data);

		$this->data = null;

		return $chart;
	}
}