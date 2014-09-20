<?php
	/**
	 * Static pages model
	 *
	 * The model for saving and finding static pages.
	 *

	 *
	 * @filesource
	 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
	 * @link http://www.infinitas-cms.org
	 * @package Infinitas.Contents.Model
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @since 0.7a
	 *
	 * @author dakota
	 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
	 */

	App::uses('File', 'Utility');

	class GlobalPage extends ContentsAppModel {
		public $useTable = false;

		public $actsAs = false;

		public $primaryKey = 'file_name';

		public function __construct( $id = false, $table = null, $ds = null ) {
			parent::__construct($id, $table, $ds);

			$this->_schema = array(
				'file_name' => array(
					'type' => 'string',
					'null' => false,
					'default' => null,
					'key' => 'primary'
				),
				'slug' => array(
					'type' => 'string',
					'null' => false,
					'default' => null,
					'length' => 255
				),
				'name' => array(
					'type' => 'string',
					'null' => false,
					'default' => null,
					'key' => 'unique'
				),
				'body' => array(
					'type' => 'text',
					'null' => false,
					'default' => '',
					'length' => null
				)
			);

			$this->validate = array(
				'file_name' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => __d('contents', 'Please enter a filename for this item')
					),
					'isUnique' => array(
						'rule' => 'isUnique',
						'message' => __d('contents', 'The page name must be unique'),
						'on' => 'create'
					),
					'validFileName' => array(
						'rule' => '/^[A-Za-z0-9_]*\.ctp$/',
						'message' => __d('contents', 'The filename can only be alphanumeric or _ (underscore)')
					)
				),
				'body' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => __d('contents', 'The page can not be empty')
					)
				)
			);
		}

		public function isUnique($fields, $or = true) {
			return !is_file($this->__path($this->data[$this->alias]['file_name']));
		}

		public function schema($field = false) {
			if (is_string($field)) {
				return $this->_schema[$field];
			}

			return $this->_schema;
		}

		public function paginate($conditions, $fields, $order, $limit, $page = 1, $recursive = null, $extra = array()) {
			App::import('Core', 'Folder');

			$Folder = new Folder($this->__path());

			$pages = $Folder->read();
			$pages = $pages[1];

			unset($Folder);

			$returnPages = array();
			foreach ($pages as $page) {
				if (strpos($page, '.ctp') !== false) {
					$returnPages[][$this->alias] = $this->__getPageData(basename($page));
				}
			}

			if (empty($returnPages)) {
				return array();
			}

			return Set::sort($returnPages, '{n}.' . $this->alias . '.file_name', 'asc');
		}

		private function __path($id = null) {
			if ($id) {
				$id = DS . $id;
			}

			$path = str_replace(array('/', '\\'), DS, Configure::read('Contents.page_path')) . $id;

			if (!is_dir($path) && !strstr($path, '.ctp')) {
				new Folder($path, true);
			}

			return $path;
		}

		private function __getPageData($page) {
			return array(
				'name' => Inflector::humanize(substr($page, 0, strlen($page) - 4)),
				'file_name' => $page,
				'size' => filesize($this->__path($page)),
				'created' => date('Y-m-d H:i:s', filectime($this->__path($page))),
				'modified' => date('Y-m-d H:i:s', filemtime($this->__path($page)))
			);
		}

		public function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
			App::import('Core', 'Folder');

			$Folder = new Folder($this->__path());

			$pages = $Folder->read();
			unset($Folder);

			return count($pages[1]);
		}

		public function read($fields = null, $filename = null) {
			if ($filename === null) {
				$filename = $this->id;
			}

			if (!strstr($filename, '.ctp')) {
				$filename .= '.ctp';
			}

			$pageFile = $this->__path($filename);

			$this->id = null;
			if (file_exists($pageFile)) {
				$this->id = $filename;
				return array($this->alias => array_merge(
					array('body' => file_get_contents($pageFile)),
					$this->__getPageData(basename($pageFile))
				));
			}

			return false;
		}

		public function find($type = 'first', $query = array()) {
			$Folder = new Folder($this->__path());

			$conditions = '.*';
			if (isset($query['conditions'][$this->alias . '.file_name'])) {
				$conditions = regexEscape($query['conditions'][$this->alias . '.file_name']);
			}

			$results = $Folder->find($conditions);

			switch($type) {
				case 'count' :
					return count($results);
					break;
				case 'all' :
					return $results;
					break;
				case 'first' :
					if (isset($results[0]))
						return $results[0];
					else
						return false;
					break;
			}
		}

		public function save($data = null, $validate = true, $fieldList = array()) {
			if (!empty($data[$this->alias])) {
				$this->data[$this->alias] = $data[$this->alias];
			}

			if (empty($this->data)) {
				return false;
			}

			if (strpos($this->data[$this->alias]['file_name'], '.ctp') === false) {
				$this->data[$this->alias]['file_name'] .= '.ctp';
			}

			$this->id = $this->data[$this->alias]['file_name'];

			if ($validate === false || $this->validates()) {
				$File = new File($this->__path($this->id), true);
				return $File->write($this->data[$this->alias]['body']);
			}

			return false;
		}

		public function delete($id = null, $cascade = true) {
			if (!$id) {
				return false;
			}

			if (is_file($this->__path($id))) {
				return unlink($this->__path($id));
			}

			return false;
		}

		private function __massActionCopy($ids) {
			// read file

			// new name

			// save
		}
	}