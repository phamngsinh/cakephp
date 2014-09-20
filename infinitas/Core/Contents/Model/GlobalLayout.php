<?php
	/**
	 * GlobalLayout handles the CRUD for layouts within infinitas
	 *
	 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
	 * @link http://www.infinitas-cms.org
	 * @package Infinitas.Contents.Model
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @since 0.8a
	 *
	 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
	 */

	class GlobalLayout extends ContentsAppModel {
		/**
		 * enable the row locking for layouts
		 *
		 * @ref LockableBehavior
		 *
		 * @var bool
		 */
		public $lockable = true;

		public $contentable = true;

		/**
		 * The table to use for layouts
		 *
		 * @bug this could be causing the installer to not include the prefix when
		 * installing infinitas
		 *
		 * @var string
		 */
		public $useTable = 'global_layouts';

		/**
		 * belongs to relations for the GlobalLayout model
		 *
		 * @var array
		 */
		public $hasMany = array(
			'GlobalContent' => array(
				'className' => 'Contents.GlobalContent',
				'counterCache' => true
			)
		);

		public $belongsTo = array(
			'Theme' => array(
				'className' => 'Themes.Theme'
			)
		);

		/**
		 * @copydoc AppModel::__construct()
		 */
		public function __construct($id = false, $table = null, $ds = null) {
			parent::__construct($id, $table, $ds);

			$this->validate = array(
				'name' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => __d('contents', 'Please enter the name of this template')
					),
					'isUnique' => array(
						'rule' => 'isUnique',
						'message' => __d('contents', 'There is already a template with that name')
					)
				),
				'html' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => __d('contents', 'Please create the html for your template')
					)
				),
				'plugin' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => __d('contents', 'Please select the plugin that this layout is for')
					)
				),
				'model' => array(
					'notEmpty' => array(
						'rule' => 'notEmpty',
						'message' => __d('contents', 'Please select the model that this layout is for')
					)
				)
			);

			$this->findMethods['autoLoadLayout'] = true;
			$this->findMethods['layoutList'] = true;
		}

		/**
		 * @copydoc AppModel::beforeSave()
		 *
		 * Before saving the record make sure that the model is set to the correct
		 * value so that it will be linked up properly to the related rows.
		 */
		public function beforeSave($options = array()) {
			if (!empty($this->data['GlobalLayout']['model'])) {
				$this->data['GlobalLayout']['model'] =
					$this->data['GlobalLayout']['plugin'] . '.' . $this->data['GlobalLayout']['model'];
				return true;
			}
		}

		/**
		 * @copydoc AppModel::afterFind()
		 *
		 * after getting the data split the model into its plugin / model parts
		 * for the ajax selects to work properly
		 */
		public function afterFind($results, $primary = false) {
			parent::afterFind($results, $primary);

			if (isset($results[0][$this->alias]['model'])) {
				foreach ($results as $k => $result) {
					$parts = pluginSplit($results[$k][$this->alias]['model']);
					$results[$k][$this->alias]['model_class'] = $results[$k][$this->alias]['model'];
					$results[$k][$this->alias]['plugin'] = $parts[0];
					$results[$k][$this->alias]['model'] = $parts[1];
				}
			}

			return $results;
		}

		public function _findAutoLoadLayout($state, $query, $results = array()) {
			if ($state === 'before') {
				if (empty($query['plugin']) || empty($query['model']) || empty($query['action'])) {
					return $query;
				}
				$query['conditions'] = array_merge((array)$query['conditions'], array(
					$this->alias . '.model' => implode('.', array(
						$query['plugin'],
						$query['model'],
					)),
					$this->alias . '.auto_load' => $query['action']
				));

				$query['fields'] = array(
					'GlobalLayout.*'
				);

				return $query;
			}
			return current($results);
		}

		public function _findLayoutList($state, $query, $results = array()) {
			if ($state === 'before') {
				$data = array();
				if (isset($query['plugin'])) {
					$data[] = $query['plugin'];
				}

				if (isset($query['model'])) {
					$data[] = $query['model'];
				}

				$query['_data'] = implode('.', $data);

				$query['fields'] = array(
					$this->alias . '.id',
					$this->alias . '.name',
					$this->alias . '.model'
				);

				unset($query['model'], $query['plugin']);
				return $query;
			}

			$return = array(
				__d('contents', 'Related') => array(),
				__d('contents', 'Other') => array()
			);

			foreach ($results as $result) {
				if (strstr($result[$this->alias]['model_class'], $query['_data'])) {
					$return[__d('contents', 'Related')][$result[$this->alias][$this->primaryKey]] = $result[$this->alias]['name'];
					continue;
				}

				$return[__d('contents', 'Other')][$result[$this->alias][$this->primaryKey]] = $result[$this->alias]['name'];
			}

			return $return;
		}

		public function hasLayouts($model) {
			return $this->find(
				'count',
				array(
					'conditions' => array(
						$this->alias . '.model' => $model
					)
				)
			) > 0;
		}
	}