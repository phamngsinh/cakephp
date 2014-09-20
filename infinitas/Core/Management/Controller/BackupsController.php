<?php
/**
 * BackupsController
 *
 * @package Infinitas.Management.Controller
 */

/**
 * BackupsController
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Management.Controller
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.5a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class BackupsController extends AppController {
/**
 * Backup a set of data
 *
 * @return void
 */
	public function admin_backup() {
		if (!isset($this->request->params['named']['m'])) {
			$this->notice('invalid');
		}

		$fullModel = $model = Inflector::classify($this->request->params['named']['m']);
		$plugin = '';
		if (isset($this->request->params['named']['p']) && $this->request->params['named']['p'] != '') {
			$plugin = Inflector::classify($this->request->params['named']['p']);
			$fullModel = $plugin . '.' . $fullModel;
		}

		$this->Backup->getLastBackup($model, $plugin);

		$Model = ClassRegistry::init($fullModel);

		$data['Backup']['plugin'] = $plugin;
		$data['Backup']['model'] = $model;
		$data['Backup']['last_id'] = $this->__checkBackups($Model);
		$data['Backup']['data'] = serialize($this->Backup->getRecordsForBackup($Model));

		$this->__saveBackup($data, $Model);
	}

	/**
	 * check the backups.
	 *
	 * First sees if there is any records in the model, then checks if the
	 * last backup is older than the current records.
	 *
	 * if there is nothing or the current id in the backups is the same or
	 * greater than the current it will just redirect with a message
	 *
	 * @param mixed $Model from {@see ClassRegistry::init }
	 *
	 * @return integer
	 */
	private function __checkBackups($Model) {
		$newLastId = $Model->find('first', array(
			'fields' => array(
				$Model->name . '.id'
			),
			'conditions' => array(
				$Model->name . '.id > ' => $this->Backup->last_id
			),
			'order' => array(
				$Model->name . '.id' => 'DESC'
			),
			'contain' => false
		));

		if (empty($newLastId)) {
			$this->notice('invalid');
		}

		if (isset($newLastId[$Model->name]['id']) && $this->Backup->last_id >= $newLastId[$Model->name]['id']) {
			$this->notice(
				__d('management', 'Nothing new to backup'),
				array(
					'redirect' => true
				)
			);
		}

		return $newLastId[$Model->name]['id'];
	}

	/**
	 * Saves the data to the backup table.
	 *
	 * the records from the backed up model are serialized and saved in
	 * "data" field.
	 *
	 * @param array $data the array for a cakephp save
	 *
	 * @return void
	 */
	private function __saveBackup($data, $Model) {
		if (isset($Model->hasAndBelongsToMany)) {
			foreach ($Model->hasAndBelongsToMany as $__model => $relation) {
				if (isset($relation['joinTable'])) {
					Inflector::classify($relation['joinTable']);
				}
			}
		}

		$this->Backup->create();
		if ($this->Backup->save($data)) {
			$this->notice('saved');
		}

		$this->notice('not_saved');
	}

}