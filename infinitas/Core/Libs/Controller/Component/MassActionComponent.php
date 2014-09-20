<?php
/**
 * Mass action component
 *
 * This handles all the different form actions, especialy delete / copy /
 * toggle where you need to manipulate many records at a time.
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Libs.Controller.Component
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.7a
 *
 * @author Carl Sutton ( dogmatic69 )
 * @author dakota
 */
App::uses('InfinitasComponent', 'Libs.Controller/Component');

class MassActionComponent extends InfinitasComponent {
/**
 * check if cancel has been clicked to allow things such as locking to act
 *
 * @param Controller $Controller
 */
	public function initialize(Controller $Controller) {
		parent::initialize($Controller);

		if ($this->getAction(false) == 'cancel') {
			$Controller->Event->trigger(
				'editCanceled',
				!empty($Controller->request->data[$Controller->modelClass]['id']) ? $Controller->request->data[$Controller->modelClass]['id'] : null
			);
			$Controller->redirect($Controller->getPageRedirectVar());
		}
	}

/**
 * Method to handle mass actions (Such as mass deletions, toggles, etc.)
 *
 * @return void
 */
	public function actionAdminMass() {
		$massAction = $this->getAction();

		if (isset($this->Controller->modelClass)) {
			$modelName = $this->Controller->modelClass;
		}

		if (!empty($this->Controller->request->data['Confirm']['model'])) {
			$modelName = $this->Controller->request->data['Confirm']['model'];
		}

		$ids = $this->getIds(
			$massAction,
			!empty($this->Controller->request->data[$modelName]) ? $this->Controller->request->data[$modelName] : array()
		);

		$massActionMethod = '__massAction' . ucfirst($massAction);

		if (method_exists($this->Controller, $massActionMethod)) {
			return $this->Controller->{$massActionMethod}($ids);
		} else if (method_exists($this, $massAction)) {
			return $this->{$massAction}($ids);
		}

		return $this->generic($massAction, $ids);
	}

/**
 * Get submitted ids.
 *
 * Checks the form data and returns an array of all the ids found for that
 * model.
 *
 * @param string $massAction the action to preform
 * @param array $data the form data
 *
 * @return array
 */
	public function getIds($massAction, $data) {
		if (in_array($massAction, array('add', 'filter', 'install'))) {
			return null;
		}

		$ids = array_values(array_filter(Hash::extract($data, '{n}.massCheckBox')));
		if (empty($ids)) {
			$this->Controller->notice(__d('libs', 'Nothing was selected, please select something and try again.'), array(
				'level' => 'warning',
				'redirect' => ''
			));
		}

		return $ids;
	}

/**
 * Get the action to preform.
 *
 * Gets the action that was selected from the form.
 *
 * @param array $form the data from the form submition $this->request->params['form']
 *
 * @return string
 */
	public function getAction($redirect = true) {
		if (!empty($this->Controller->request->data['action'])) {
			return $this->Controller->request->data['action'];
		}

		if ($redirect) {
			$this->Controller->notice(
				__d('libs', 'I dont know what to do.'),
				array(
					'level' => 'error',
					'redirect' => true
				)
			);
		}
	}

/**
 * Filter records.
 *
 * This will auto filter out the InfinitasComponent::massActionCheckBox() fields
 *
 * Checks the data posted from the form and redirects to the url with the params
 * for the filter component to catch.
 */
	public function filter($null = null) {
		$data = array();
		foreach ($this->Controller->data[$this->Controller->modelClass] as $k => $field ) {
			if (is_int($k) || $k == 'all' || $k == 'massCheckBox') {
				continue;
			}

			if (empty($field) && $field !== 0) {
				continue;
			}

			$data[$this->Controller->modelClass.'.'.$k] = $field;
		}

		foreach ($this->Controller->{$this->Controller->modelClass}->belongsTo as $model => $options) {
			if (empty($this->Controller->data[$model])) {
				continue;
			}

			foreach ((array)$this->Controller->data[$model] as $k => $field) {
				if ((empty($field) && $field !== 0) || is_int($k) || $k == 'all' || $k == 'massCheckBox') {
					continue;
				}

				$data[$model . '.' . $k] = $field;
			}
		}

		$this->Controller->redirect(array(
			'plugin' => $this->Controller->request->params['plugin'],
			'controller' => $this->Controller->request->params['controller'],
			'action' => 'index'
		) + $this->Controller->request->params['named'] + $data);
	}

/**
 * Delete records.
 *
 * Take the array of ids and checks that the deletion was confirmed. if it is
 * they will be sent for delete processing (could be soft|hard delete).
 *
 * If there was no javascript confirmation a page is displayed with the confirmation
 */
	public function delete($ids) {
		$delete = (isset($this->Controller->data['Confirm']['confirmed']) && $this->Controller->data['Confirm']['confirmed']) ||
				(isset($this->Controller->{$this->Controller->modelClass}->noConfirm));
		if ($delete) {
			if (method_exists($this->Controller, '__handleDeletes')) {
				$this->Controller->__handleDeletes($ids);
			} else {
				$this->__handleDeletes($ids);
			}
		}

		$rows = $this->Controller->{$this->Controller->modelClass}->find('list', array(
			'conditions' => array(
				$this->Controller->modelClass . '.id' => $ids
			)
		));

		$this->Controller->set('model', $this->Controller->modelClass);
		$this->Controller->set(compact('rows'));

		$pluginOverload = App::pluginPath($this->Controller->plugin) . 'View' . DS . 'Global' . DS . 'delete.ctp';
		if (is_file($pluginOverload)) {
			$this->Controller->render($this->Controller->plugin . '.Global/delete');
			return;
		}

		$this->Controller->render('Libs.Global/delete');
		$this->Controller->saveRedirectMarker();
	}

/**
 * Handle delete requests.
 *
 * Takes the ids and if the model is using the soft delete behavior it will
 * stick them in the trash (set a delete flag on the record) or it will do a hard
 * delete.
 *
 * @param array $ids the ids to delete.
 */
	public function __handleDeletes($ids) {
		$this->Controller->{$this->Controller->modelClass}->transaction();
		$deleted = true;
		foreach ($ids as $id) {
			$deleted = $deleted && $this->Controller->{$this->Controller->modelClass}->delete($id);
		}

		if ($deleted) {
			$this->Controller->{$this->Controller->modelClass}->transaction(true);
			$this->Controller->notice('deleted');
		}

		$this->Controller->{$this->Controller->modelClass}->transaction(false);
		$this->Controller->notice('not_deleted');
	}

/**
 * toggle records.
 *
 * Takes the array of ids that are passed in and toggles them. If they are active
 * they will be inactive and inactive records will be active.
 *
 * @param array $ids array of ids.
 * @param string $message optional string you want the notice to display
 */
	public function toggle($ids, $message = null) {
		if (!$this->Controller->{$this->Controller->modelClass}->hasField('active')) {
			throw new Exception(sprintf('The model "%s" does not have an active field', $this->Controller->modelClass));
		}

		if (empty($ids)) {
			return false;
		}

		$conditions = array($this->Controller->modelClass . '.id' => $ids);
		$newValues = array(
			$this->Controller->modelClass . '.active' => '1 - `' . $this->Controller->modelClass . '`.`active`'
		);

		if ($this->Controller->{$this->Controller->modelClass}->hasField('modified')) {
			$newValues[$this->Controller->modelClass . '.modified'] = '\'' . date('Y-m-d H:m:s') . '\'';
		}

		// unbind things for the update. dont need all the models for this.
		$this->Controller->{$this->Controller->modelClass}->unbindModel(array(
			'belongsTo' => array_keys($this->Controller->{$this->Controller->modelClass}->belongsTo),
			'hasOne' => array_keys($this->Controller->{$this->Controller->modelClass}->hasOne)
		));

		if ($this->Controller->{$this->Controller->modelClass}->updateAll($newValues, $conditions)) {
			$this->Controller->{$this->Controller->modelClass}->afterSave(false);

			if (!empty($message)) {
				$this->Controller->notice($message, array(
					'redirect' => true
				));
			}

			$this->Controller->notice(__d('libs', 'The %s were toggled', $this->Controller->prettyModelName), array(
				'redirect' => true
			));
		}

		$this->Controller->notice(__d('libs', 'The %s could not be toggled', $this->Controller->prettyModelName), array(
			'level' => 'error',
			'redirect' => true
		));
	}

/**
 * Copy a record.
 *
 * Takes a record id and reads it from the database. Then unset some data
 * that is not needed like id and created times and saves the new record.
 *
 * @todo open add page that is filled out.
 *
 * @param array $ids array of ids.
 */
	public function copy($ids) {
		$copyText = sprintf(' - %s (%s)', __d('libs', 'copy'), date('Y-m-d'));
		$saves = 0;

		if ($this->Controller->{$this->Controller->modelClass}->Behaviors->attached('Contentable')) {
			$this->Controller->notice(__d('content', 'Copy is not currently supported for this data'), array(
				'redirect' => '',
				'level' => 'warning'
			));
		}

		foreach ($ids as $id) {
			$record = $this->Controller->{$this->Controller->modelClass}->read(null, $id);

			unset($record[$this->Controller->modelClass]['id']);

			$check = $record[$this->Controller->modelClass][$this->Controller->{$this->Controller->modelClass}->displayField] != $this->Controller->{$this->Controller->modelClass}->primaryKey;
			if ($check) {
				$record[$this->Controller->modelClass][$this->Controller->{$this->Controller->modelClass}->displayField] =
					$record[$this->Controller->modelClass][$this->Controller->{$this->Controller->modelClass}->displayField] . $copyText;
			}

			unset(
				$record[$this->Controller->modelClass]['created'],
				$record[$this->Controller->modelClass]['modified'],
				$record[$this->Controller->modelClass]['lft'],
				$record[$this->Controller->modelClass]['rght'],
				$record[$this->Controller->modelClass]['ordering'],
				$record[$this->Controller->modelClass]['order_id'],
				$record[$this->Controller->modelClass]['views']
			);
			$record[$this->Controller->modelClass]['active'] = 0;

			foreach ($record[$this->Controller->modelClass] as $field => $value) {
				$schema = $this->Controller->{$this->Controller->modelClass}->schema($field);
				if (!empty($schema['key']) && $schema['key'] == 'unique') {
					$record[$this->Controller->modelClass][$field] .= ' - ' . time();
				}

				if (strstr($field, '_count')) {
					unset($record[$this->Controller->modelClass][$field]);
				}
			}

			$this->Controller->{$this->Controller->modelClass}->create();
			if ($this->Controller->{$this->Controller->modelClass}->save($record, array('validate' => false))) {
				$saves++;
			}
		}

		if ($saves) {
			$this->Controller->notice(__d('libs', '%s copies of %s were made', $saves, $this->Controller->prettyModelName), array(
				'redirect' => true
			));
		}

		$this->Controller->notice(__d('libs', 'No copies of %s could be made', $this->Controller->prettyModelName), array(
			'level' => 'error',
			'redirect' => true
		));
	}

/**
 * Move records
 *
 * find out relations like belongsTo and habtm and send the ids to a view
 * so you can easily move many items
 *
 * @param array $ids array of ids.
 */
	public function move($ids) {
		if (isset($this->Controller->data['Move']['confirmed']) && $this->Controller->data['Move']['confirmed']) {
			if (method_exists($this->Controller, '__handleMove')) {
				$this->Controller->__handleMove($ids);
			} else {
				$this->__handleMove($ids);
			}
		}

		$rows = $this->Controller->{$this->Controller->modelClass}->find('all', array(
			'conditions' => array($this->Controller->modelClass.'.id' => $ids), 
			'contain' => false
		));

		$relations['belongsTo'] = array();
		if (isset($this->Controller->{$this->Controller->modelClass}->belongsTo)) {
			$relations['belongsTo'] = $this->Controller->{$this->Controller->modelClass}->belongsTo;

			foreach ($relations['belongsTo'] as $alias => $belongsTo) {
				switch($alias) {
					case 'Locker':
						break;

					case 'Parent Post':
					case 'Parent':
						$_Model = ClassRegistry::init($this->Controller->plugin . '.' . $this->Controller->modelClass);

						if (in_array('Tree', $_Model->Behaviors->_attached)) {
							$_Model->order = array();
							$this->Controller->set(strtolower(Inflector::pluralize($alias)), $_Model->generateTreeList());
						} else{
							$this->Controller->set(strtolower(Inflector::pluralize($alias)), $_Model->find('list'));
						}
						break;

					default:
						$_Model = ClassRegistry::init($this->Controller->plugin . '.' . $alias);
						$_Model->order = array();
						$this->Controller->set(strtolower(Inflector::pluralize($alias)), $_Model->find('list'));
						break;
				}
			}
		}

		$relations['hasAndBelongsToMany'] = array();
		if (isset($this->Controller->{$this->Controller->modelClass}->hasAndBelongsToMany)) {
			$relations['hasAndBelongsToMany'] = $this->Controller->{$this->Controller->modelClass}->hasAndBelongsToMany;

			foreach ($relations['hasAndBelongsToMany'] as $alias => $belongsTo) {
				$_Model = ClassRegistry::init($this->Controller->plugin . '.' . $alias);
				$this->Controller->set(strtolower($alias), $_Model->find('list'));
			}
		}

		if (array_filter(Set::flatten($relations)) == array()) {
			$this->Controller->notice(__d($this->Controller->request->params['plugin'], 'There is nothing to move for these records'), array(
				'redirect' => true
			));
		}

		$modelSetup['displayField'] = $this->Controller->{$this->Controller->modelClass}->displayField;
		$modelSetup['primaryKey'] = $this->Controller->{$this->Controller->modelClass}->primaryKey;
		$this->Controller->set(compact('rows', 'model', 'modelSetup', 'relations'));

		$this->Controller->saveRedirectMarker();

		$pluginOverload = App::pluginPath($this->Controller->plugin) . 'View' . DS . 'Global' . DS . 'move.ctp';
		if (is_file($pluginOverload)) {
			$this->Controller->render($this->Controller->plugin . '.Global/move');
			return;
		}

		$this->Controller->render('Libs.Global/move');
	}

/**
 * Handle move requests.
 *
 * get the id's passed and assign the new relations so the records are
 * moved.
 *
 * @param array $ids the ids to delete.
 */
	private function __handleMove($ids) {
		$movedTo = $this->Controller->data['Move'];
		unset($movedTo['model'], $movedTo['confirmed']);

		$result = true;

		foreach ($ids as $id) {
			$row = array('id' => $id);
			$_data = array_merge(array_filter($movedTo), $row);

			$_mn = $this->Controller->modelClass;
			foreach ($_data as $key => $value) {
				if (is_array($value)) {
					$save[$key][$key] = $value;
				} else {
					$save[$_mn][$key] = $value;
				}
			}

			$data = $this->Controller->{$this->Controller->modelClass}->read(null, $id);
			unset($data[$this->Controller->modelClass]['lft']);
			unset($data[$this->Controller->modelClass]['rght']);
			$save[$this->Controller->modelClass] = array_merge($data[$this->Controller->modelClass], $save[$this->Controller->modelClass]);
			// @todo this is messing up trees, need to see why the lft and rght is not updating.
			$result = $result && $this->Controller->{$this->Controller->modelClass}->saveAll($save);
			unset($save);
		}

		if (in_array('Tree', $this->Controller->{$this->Controller->modelClass}->Behaviors->attached())) {
			//$this->Controller->{$this->Controller->modelClass}->recover('parent');
		}

		if ($result == true) {
			$params = array(
				'message' => __d('libs', 'The %s have been moved', $this->Controller->prettyModelName)
			);
		} else {
			$params = array(
				'level' => 'warning',
				'message' => __d('libs', 'Some of the %s could not be moved', $this->Controller->prettyModelName)
			);
		}

		$params['redirect'] = '';

		$this->Controller->notice($params['message'], $params);
	}

/**
 * Generic action.
 *
 * This method handles the actions like add and edit. If there is no ids or
 * there is no id in the array it will redirect to the action without passing
 * an id.
 *
 * @param string $action the action to redirect to.
 * @param int $id the id of the record that is selected.
 */
	public function generic($action = 'add', $ids = null) {
		$url = array('action' => $action);
		if (!empty($ids)) {
			$url = array_merge($url, $ids);
		}

		$this->Controller->redirect(array_merge($url, $this->Controller->request->params['named'], (array)$this->Controller->request->params['pass']));
	}
}