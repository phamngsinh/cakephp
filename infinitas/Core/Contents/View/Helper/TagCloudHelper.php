<?php
	/**
	 * CakePHP Tags Plugin
	 *
	 * Copyright 2009 - 2010, Cake Development Corporation
	 * 						1785 E. Sahara Avenue, Suite 490-423
	 * 						Las Vegas, Nevada 89104
	 *
	 *
	 *
	 *
	 * @copyright 2009 - 2010, Cake Development Corporation (http://cakedc.com)
	 * @link	  http://github.com/CakeDC/Tags
	 * @package Infinitas.Contents.Helper
	 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
	 */

	/**
	 * Tag cloud helper
	 *
	 * @package Infinitas.Contents.Helper
	 *
	 * @property HtmlHelper $Html
	 * @property TextHelper $Text
	 * @property DesignHelper $Design
	 */
	class TagCloudHelper extends AppHelper {

		/**
		 * Other helpers to load
		 *
		 * @var public $helpers
		 */
		public $helpers = array(
			'Html', 'Text',
			'Libs.Design'
		);

		/**
		 * Method to output a tag-cloud formatted based on the weight of the tags
		 *
		 * @param array $tags
		 * @param array $options Display options. Valid keys are:
		 * 	- shuffle: true to shuffle the tag list, false to display them in the same order than passed [default: true]
		 *  - extract: Set::extract() compatible format string. Path to extract weight values from the $tags array [default: {n}.GlobalTag.weight]
		 *  - before: string to be displayed before each generated link. "%size%" will be replaced with tag size calculated from the weight [default: empty]
		 *  - after: string to be displayed after each generated link. "%size%" will be replaced with tag size calculated from the weight [default: empty]
		 *  - maxSize: size of the heaviest tag [default: 160]
		 *  - minSize: size of the lightest tag [default: 80]
		 *  - url: an array containing the default url
		 *  - named: the named parameter used to send the tag [default: by]
		 *
		 * @return string
		 */
		public function display($tags = null, $options = array()) {
			if (empty($tags) || !is_array($tags)) {
				return '';
			}
			$defaults = array(
				'shuffle' => true,
				'extract' => '{n}.GlobalTag.weight',
				'between' => ' | ',
				'maxSize' => 160,
				'minSize' => 80,
				'url' => array(
					'plugin' => $this->request->params['plugin'],
					'action' => 'index'
				),
				'named' => 'by',
				'return' => false
			);
			$options = array_merge($defaults, array_filter($options));

			$weights = Set::extract($options['extract'], $tags);
			$maxWeight = $minWeight = 1;
			if ($weights) {
				$maxWeight = max($weights);
				$minWeight = min($weights);
			}

			// find the range of values
			$spread = $maxWeight - $minWeight;
			if (0 == $spread) {
				$spread = 1;
			}

			if ($options['shuffle'] == true) {
				shuffle($tags);
			}

			$cloud = array();
			foreach ($tags as $tag) {
				$options['url'] = array_merge(array(
					'plugin' => $this->request->params['plugin']
				), $options['url']);
				$options['url'][$options['named']] = $tag['GlobalTag']['keyname'];

				$url = EventCore::trigger($this, Inflector::camelize($options['url']['plugin']) . '.slugUrl', array('type' => 'tag', 'data' => $options['url']));

				$size = $options['minSize'] + (($tag['GlobalTag']['weight'] - $minWeight) * (($options['maxSize'] - $options['minSize']) / ($spread)));
				$size = ceil($size);

				$cloud[] = $this->Html->link(
					$tag['GlobalTag']['name'],
					current($url['slugUrl']),
					array(
						'class' => 'tag tag-' . $tag['GlobalTag']['id']
					)
				);
			}

			if ($options['return']) {
				return $cloud;
			}

			return implode($options['between'], $cloud);
		}

		/**
		 * convert an array of tags to a list of tags with links
		 *
		 * @param array $data the row of data from find
		 * @param string $seperator what to seperate with
		 * @param integer $limit max number of tags to show
		 *
		 * @return string
		 */
		public function tagList($data, $seperator = 'and', $limit = 5) {
			if (empty($data['GlobalTagged'])) {
				return __d('contents', 'No tags');
			}

			$return = array();
			foreach ($data['GlobalTagged'] as $tagged) {
				if ($limit <= 0) {
					continue;
				}

				$return[] = $this->Html->link(
					$this->Design->icon('tag') . $tagged['GlobalTag']['name'],
					$this->here . '#',
					array(
						'escape' => false,
						'class' => 'icon tag'
					)
				);

				$limit--;
			}
			if ($seperator == ',') {
				return implode(', ', $return);
			}

			return $this->Text->toList($return, $seperator);
		}

		/**
		 * Replaces %size% in strings with the calculated "size" of the tag
		 *
		 * @return string
		 */
		protected function _replace($string, $size) {
			return str_replace("%size%", $size, $string);
		}

	}