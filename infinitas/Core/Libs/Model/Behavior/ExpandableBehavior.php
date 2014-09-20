<?php
	/**
	 * undocumented class
	 *
	 * @package default
	 */
	/**
	 * http://github.com/felixge/debuggable-scraps/blob/master/cakephp/behaviors/expandable/expandable.php
	 * @author felixge
	 */
	class ExpandableBehavior extends ModelBehavior{
		/**
		 * the settings for the behavior
		 *
		 * @var array
		 */
		public $settings = array();

		public function setup(Model $Model, $settings = array()) {
			$base = array('schema' => $Model->schema());
			if (isset($settings['with'])) {
				$conventions = array('foreignKey', $Model->hasMany[$settings['with']]['foreignKey']);
				return $this->settings[$Model->alias] = am($base, $conventions, $settings);
			}

			foreach ($Model->hasMany as $assoc => $option) {
				if (strpos($assoc, 'Attribute') !== false) {
					$conventions = array('with' => $assoc, 'foreignKey' => $Model->hasMany[$assoc]['foreignKey']);
					return $this->settings[$Model->alias] = am($base, $conventions, !empty($settings) ? $settings : array());
				}
			}
		}

		/**
		 * after a find this will join on the extra fields to the array so
		 * they are available in the view.
		 *
		 * @var $Model object the model that did the find
		 * @var $results array what was found
		 * @var $primary if its the main model doing the call
		 *
		 * @return array
		 */
		public function afterFind(Model $Model, $results, $primary) {
			extract($this->settings[$Model->alias]);
			if (!Set::matches('/'.$with, $results)) {
				return;
			}

			foreach ($results as $i => $item) {
				$done = false;
				$keys = array_keys($item);
				foreach ($keys as $key) {
					if (strstr($key, 'Attribute')) {
						$item[$Model->alias][$item[$key]['key']] = $item[$key]['val'];
						$done = true;
					}
				}
				if ($done) {
					continue;
				}

				foreach ($item[$with] as $field) {
					$results[$i][$Model->alias][$field['key']] = $field['val'];
				}
			}

			return $results;
		}

		/**
		 * After the main record has been saved this will get a diff of the
		 * schema and work out what needs to be added to the attributes table
		 * linked to the main model. first checking if its an update / insert and
		 * then saving what is required
		 *
		 * @var $Model object the model object of the "main" model
		 *
		 * @return boolean
		 */
		public function afterSave(Model $Model, $created) {
			extract($this->settings[$Model->alias]);
			$fields = array_diff_key($Model->data[$Model->alias], $schema);
			$id = $Model->id;
			foreach ($fields as $key => $val) {
				$field = $Model->{$with}->find(
					'first',
					array(
						'fields' => array($with.'.id'),
						'conditions' => array($with.'.'.$foreignKey => $id, $with.'.key' => $key),
						'contain' => false
					)
				);

				$Model->{$with}->create(false);

				if ($field) {
					$Model->{$with}->set('id', $field[$with]['id']);
				}

				else {
					$Model->{$with}->set(array($foreignKey => $id, 'key' => $key));
				}

				$Model->{$with}->set('val', $val);
				$Model->{$with}->save();
			}

			return true;
		}

	}
