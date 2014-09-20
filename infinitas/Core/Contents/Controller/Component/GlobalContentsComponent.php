<?php
App::uses('InfinitasComponent', 'Libs.Controller/Component');

class GlobalContentsComponent extends InfinitasComponent {

	protected $_methods = array(
		'admin_edit',
		'admin_add'
	);

	public function beforeRender(Controller $Controller) {
		if (empty($Controller->uses)) {
			return true;
		}

		$isContentable = isset($Controller->{$Controller->modelClass}->contentable) && $Controller->{$Controller->modelClass}->contentable;
		if ($isContentable && in_array($Controller->params['action'], $this->_methods)) {
			$this->_setFormVariables($Controller);
		}

		$this->_loadLayout($Controller, array(
			'plugin' => $Controller->{$Controller->modelClass}->plugin,
			'model' => $Controller->{$Controller->modelClass}->alias,
			'action' => $Controller->params['action']
		));
	}

	protected function _setFormVariables(Controller $Controller) {
		if (isset($Controller->{$Controller->modelClass}->GlobalContent)) {
			$Model = $Controller->{$Controller->modelClass}->GlobalContent;
		} else if (isset($Controller->GlobalContent)) {
			$Model = $Controller->GlobalContent;
		} else {
			throw new Exception('Could not find the Content model');
		}

		$Controller->set('contentGroups', $Model->Group->find('list'));
		$Controller->set('contentAuthors', $Model->ContentAuthor->find('adminList'));
		$Controller->set('contentLayouts', $Model->GlobalLayout->find('layoutList', array(
			'plugin' => $Controller->plugin,
			'model' => $Controller->modelClass
		)));
		$Controller->set('contentCategories', $Model->GlobalCategory->find('categoryList'));
	}

	protected function _loadLayout($Controller, $options) {
		if ($this->Controller->request->params['admin']) {
			return;
		}

		$layout = ClassRegistry::init('Contents.GlobalLayout')->find('autoLoadLayout', $options);

		if ($layout) {
			$Controller->set('globalLayoutTemplate', $layout);
		}
	}
}