<?php
	/**
	 * Infinitas controller actions bake template
	 *
	 * This is the file that is used to bake the controller actions when
	 * using infinitas skel
	 *
	 * Copyright (c) 2010 Carl Sutton ( dogmatic69 )
	 *
	 * @filesource
	 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
	 * @link http://www.infinitas-cms.org
	 * @package bake
	 * @subpackage bake.classes.actions
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @since 0.7a
	 *
	 * @author Carl Sutton ( dogmatic69 )
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice.
	 *
	 * variables available
	 *
	 * [directory] => actions
	 * [filename] => controller_actions
	 * [vars] =>
	 * [themePath] => C:\xampp\htdocs\infinitas\vendors\shells\templates\infinitas\
	 * [templateFile] => C:\xampp\htdocs\infinitas\vendors\shells\templates\infinitas\actions\controller_actions.ctp
	 * [admin] => admin_
	 * [controllerPath] => core_logs
	 * [pluralName] => coreLogs
	 * [singularName] => coreLog
	 * [singularHumanName] => Core Log
	 * [pluralHumanName] => coreLogs
	 * [wannaUseSession] => 1
	 *
	 * [modelObj] => CoreLog Object (
	 *	[name] => ''
	 *	[displayField] => ''
	 *	[order] => Array()
	 *	[hasOne] => Array()
	 *	[belongsTo] => Array()
	 *	[hasMany] => Array()
	 *	[hasAndBelongsToMany] => Array()
	 *	[useDbConfig] => ''
	 *	[actsAs] => Array()
	 *	[blockedPlugins] => Array()
	 *	[useTable] => ''
	 *	[id] => ''
	 *	[data] => Array()
	 *	[table] => ''
	 *	[primaryKey] => ''
	 *	[_schema] => Array()
	 *	[validate] => Array()
	 *	[validationErrors] => Array()
	 *	[tablePrefix] => ''
	 *	[alias] => ''
	 */

	if (!function_exists('relatedFinds')) {
		function relatedFinds($modelObj) {
		}
	}

	/**
	* generate the index code
	*/
	echo <<<COMMENT
/**
 * @brief the index method
 *
 * Show a paginated list of $currentModelName records.
 *
 * @todo update the documentation
 *
 * @return void
 */

COMMENT;
	echo "\tpublic function {$admin}index() {\n";
		echo "\t\t\$this->Paginator->settings = array(\n";
		echo "\t\t\t'contain' => array(\n";

		foreach (array('belongsTo', 'hasOne') as $assoc) {
			foreach ($modelObj->{$assoc} as $associationName => $relation) {
				echo "\t\t\t\t'{$associationName}',\n";
			}
		}

		echo "\t\t\t)\n";
		echo "\t\t);\n\n";
		echo "\t\t\$$pluralName = \$this->Paginator->paginate(null, \$this->Filter->filter);\n\n";

		echo "\t\t\$filterOptions = \$this->Filter->filterOptions;\n";
		echo "\t\t\$filterOptions['fields'] = array(\n";
			foreach ($modelObj->_schema as $field => $data) {
				switch($field) {
					case $modelObj->displayField:
						echo "\t\t\t'{$modelObj->displayField}',\n";
						break;

					case 'active':
						echo "\t\t\t'active' => (array)Configure::read('CORE.active_options'),\n";
						break;

					case 'locked':
						echo "\t\t\t'locked' => (array)Configure::read('CORE.locked_options'),\n";
						break;

					case substr($field, -1, 2) == 'id':
						echo "\t\t\t'$field' => \$this->{$currentModelName}->".Inflector::classify(substr($field, -1, 2))."->find('list'),\n";
						break;
				} // switch
				// like 'layout_id' => $this->Content->Layout->find('list'),
			}
		echo "\t\t);\n\n";
		echo "\t\t\$this->set(compact('$pluralName', 'filterOptions'));\n";
	echo "\t}\n\n";

	/**
	 * generate the view code
	 */
	$idDocs = "\n * @param mixed \$id int or string uuid or the row to find";
	echo <<<COMMENT
/**
 * @brief view method for a single row
 *
 * Show detailed information on a single $currentModelName
 *
 * @todo update the documentation $idDocs
 *
 * @return void
 */

COMMENT;
	if (!$admin && in_array('slug', array_keys($modelObj->_schema))) {
		// for tabels with slugs
		echo "\tpublic function {$admin}view() {\n";
		echo "\t\tif (!isset(\$this->request->params['slug']) || !\$this->request->params['slug']) {\n";
			echo "\t\t\t\$this->Infinitas->noticeInvalidRecord();\n";
		echo "\t\t}\n\n";

		echo "\t\t\${$singularName} = \$this->{$currentModelName}->getViewData(\n";
			echo "\t\t\tarray(\$this->{$currentModelName}->alias . '.slug' => \$this->request->params['slug'])\n";
		echo "\t\t);\n\n";
	}

	else{
		// for admin and non-slugged tables
		echo "\tpublic function {$admin}view(\$id = null) {\n";
		echo "\t\tif (!\$id) {\n";
			echo "\t\t\t\$this->Infinitas->noticeInvalidRecord();\n";
		echo "\t\t}\n\n";

		echo "\t\t\${$singularName} = \$this->{$currentModelName}->getViewData(\n";
			echo "\t\t\tarray(\$this->{$currentModelName}->alias . '.' . \$this->{$currentModelName}->primaryKey => \$id)\n";
		echo "\t\t);\n\n";
	}

		echo "\t\t\$this->set(compact('$singularName'));\n";
	echo "\t}\n\n";

	/**
	* generaly only need admin add / edit methods.
	*/
	if ($admin) {
		/**
		 * generate the add code
		 */
	echo <<<COMMENT
/**
 * @brief admin create action
 *
 * Adding new $currentModelName records.
 *
 * @todo update the documentation
 *
 * @return void
 */

COMMENT;
		echo "\tpublic function {$admin}add() {\n";
			echo "\t\tparent::{$admin}add();\n\n";

			$compact = array();
			foreach (array('belongsTo', 'hasAndBelongsToMany') as $assoc) {
				foreach ($modelObj->{$assoc} as $associationName => $relation) {
					if (!empty($associationName) && $this->_modelName($associationName) != 'Locker') {
						$otherModelName  = $this->_modelName($associationName);
						$otherPluralName = $this->_pluralName($associationName);

						echo "\t\t\${$otherPluralName} = \$this->{$currentModelName}->{$otherModelName}->find('list');\n";
						$compact[] = "'{$otherPluralName}'";
					}
				}
			}

			if (!empty($compact)) {
				echo "\t\t\$this->set(compact(".join(', ', $compact)."));\n";
			}
		echo "\t}\n\n";

		/**
		 * generate the edit code
		 */
		echo <<<COMMENT
/**
 * @brief admin edit action
 *
 * Edit old $currentModelName records.
 *
 * @todo update the documentation
 * @param mixed \$id int or string uuid or the row to edit
 *
 * @return void
 */

COMMENT;
		echo "\tpublic function {$admin}edit(\$id = null) {\n";
			echo "\t\tparent::{$admin}edit(\$id);\n\n";

			$compact = array();
			foreach (array('belongsTo', 'hasAndBelongsToMany') as $assoc) {
				foreach ($modelObj->{$assoc} as $associationName => $relation) {
					if (!empty($associationName) && $this->_modelName($associationName) != 'Locker') {
						$otherModelName  = $this->_modelName($associationName);
						$otherPluralName = $this->_pluralName($associationName);

						echo "\t\t\${$otherPluralName} = \$this->{$currentModelName}->{$otherModelName}->find('list');\n";
						$compact[] = "'{$otherPluralName}'";
					}
				}
			}

			if (!empty($compact)) {
				echo "\t\t\$this->set(compact(".join(', ', $compact)."));\n";
			}
		echo "\t}\n";
	}