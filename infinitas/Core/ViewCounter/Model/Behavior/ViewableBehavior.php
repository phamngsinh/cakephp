<?php
/**
 * ViewableBehavior
 *
 * @package Infinitas.ViewCounter.Model.Behavior
 */

/**
 * ViewableBehavior
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.ViewCounter.Model.Behavior
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.5a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class ViewableBehavior extends ModelBehavior {
/**
 * Contain settings indexed by model name.
 *
 * @var array
 */
	public $__settings = array();

/**
 * Initiate behavior for the model using specified settings.
 *
 * Available settings:
 *
 * - view_counter: string :: the field in the table that has the count
 * - session_tracking false to disable, int for number of views to keep track of
 * 	views are tracked by displayField and will do funny things if displayField is not a string.
 *
 * @param Model $Model Model using the behaviour
 * @param array $settings Settings to override for model.
 *
 * @return void
 */
	public function setup(Model $Model, $settings = array()) {
		$default = array(
			'view_counter' => 'views',
			'session_tracking' => 20
		);

		if (!isset($this->__settings[$Model->alias])) {
			$this->__settings[$Model->alias] = $default;
		}

		$this->__settings[$Model->alias] = array_merge(
			$this->__settings[$Model->alias],
			$settings
		);
		if (!$Model->useTable) {
			return false;
		}

		if (empty($Model->ViewCount)) {
			$Model->ViewCount = ClassRegistry::init('ViewCounter.ViewCounterView');
		}
		$Model->bindModel(array(
			'hasMany' => array(
				$Model->ViewCount->alias => array(
					'className' => 'ViewCounter.ViewCounterView',
					'foreignKey' => 'foreign_key',
					'conditions' => array(
						'model' => $Model->plugin . '.' . $Model->alias
					),
					'limit' => 0
				)
			)
		), false);

		$Model->ViewCount->bindModel(array(
			'belongsTo' => array(
				$Model->alias => array(
					'className' => $Model->plugin . '.' .$Model->alias,
					'foreignKey' => 'foreign_key',
					'counterCache' => 'views'
				)
			)
		), false);
	}

/**
 * This happens after a find happens.
 *
 * @param Model $Model Model about to be saved.
 *
 * @return boolean
 */
	public function afterFind(Model $Model, $data, $primary) {
		// skip finds with more than one result.
		$skip = $Model->findQueryType == 'neighbors' || $Model->findQueryType == 'count' ||
			empty($data) || isset($data[0][0]['count']) || isset($data[0]) && count($data) > 1 ||
			!isset($data[0][$Model->alias][$Model->primaryKey]);
		if ($skip) {
			return $data;
		}

		if (isset($this->__settings[$Model->alias]['session_tracking']) && $this->__settings[$Model->alias]['session_tracking']) {
			$sessionName = 'Viewable.' . $Model->alias;
			$views = array_flip((array)CakeSession::read($sessionName));
			$record = $data[0][$Model->alias][$Model->primaryKey];
			unset($views[$record]);
			array_unshift($views, $record);
			CakeSession::write($sessionName, array_flip($views));
		}

		$user_id = AuthComponent::user('id');
		$view[$Model->ViewCount->alias] = array(
			'user_id' => $user_id > 0 ? $user_id : 0,
			'model' => Inflector::camelize($Model->plugin).'.'.$Model->name,
			'foreign_key' => $data[0][$Model->alias][$Model->primaryKey],
			'referer' => str_replace(InfinitasRouter::url('/'), '/', $Model->__referer)
		);

		$location = EventCore::trigger($this, 'GeoLocation.getLocation');
		$location = current($location['getLocation']);

		foreach ((array)$location as $k => $v) {
			$view[$Model->ViewCount->alias][$k] = $v;
		}
		$view[$Model->ViewCount->alias]['year'] = date('Y');
		$view[$Model->ViewCount->alias]['month'] = date('m');
		$view[$Model->ViewCount->alias]['day'] = date('j');
		$view[$Model->ViewCount->alias]['day_of_year'] = date('z');
		$view[$Model->ViewCount->alias]['week_of_year'] = date('W');
		$view[$Model->ViewCount->alias]['hour'] = date('G'); // no leading 0

		$view[$Model->ViewCount->alias]['city'] = !empty($view[$Model->ViewCount->alias]['city']) ? $view[$Model->ViewCount->alias]['city'] : 'Unknown';

		/**
		 * http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_dayofweek
		 * sunday is 1, php uses 0
		 */
		$view[$Model->ViewCount->alias]['day_of_week'] = date('w') + 1;

		$Model->ViewCount->unBindModel(array(
			'belongsTo' => array('GlobalCategory')
		));

		$Model->ViewCount->create();
		$Model->ViewCount->save($view);
		return $data;
	}

}