<?php

	/**
	 * @page AppModel AppModel
	 *
	 * @section app_model-overview What is it
	 *
	 * AppModel is the main model class that all other models will eventually
	 * extend. AppModel provides some methods through inheritance and also sets up
	 * a few configurations that are used throughout the application.
	 *
	 * A lot of the code that is found here is to help make development simpler
	 * but can easily be overloaded should you requre something a little bit
	 * different. Take the cache clearing for example, the default is that after
	 * a change in the database is detected any related cache will be deleted. Should
	 * you want something else to happen just overload the method in your model
	 * or the MyPluginAppModel.
	 *
	 * @section app_model-usage How to use it
	 *
	 * Usage is simple, extend your MyPluginAppModel from this class and then the
	 * models in your plugin just extend MyPluginAppModel. Example below:
	 *
	 * @code
	 *	// in APP/plugins/my_plugin/my_plugin_app_model.php create
	 *	class MyPluginAppModel extends AppModel{
	 *		// do not set the name in this model, there be gremlins
	 *	}
	 *
	 *	// then in APP/plugins/my_plugin/models/something.php
	 *	class Something extends MyPluginAppModel{
	 *		public $name = 'Something';
	 *		//...
	 *	}
	 * @endcode
	 *
	 * After that you will be able to directly access the public methods that
	 * are available from this class as if they were in your model.
	 *
	 * @code
	 *	$this->someMethod();
	 * @endcode
	 *
	 * @section app_model-see-also Also see
	 * @ref LazyModel
	 * @ref InfinitasBehavior
	 * @ref Event
	 * @ref InfinitasBehavior
	 */

	/**
	 * @brief main model class to extend
	 *
	 * AppModel is the base Model that all models should extend, unless you are
	 * doing something completely different.
	 *
	 * @copyright Copyright (c) 2009 Carl Sutton ( dogmatic69 )
	 * @link http://infinitas-cms.org
	 * @package Infinitas
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 * @since 0.5a
	 *
	 * @author dogmatic69
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice.
	 */
	App::uses('Model', 'Model');
	class AppModel extends Model {

		/**
		 * @brief The prefix for the table this model is using
		 *
		 * This Should be the same throughout a plugin, and should be the same
		 * as the plugins name with a trailing _ some my_plugin should have a
		 * prefix of 'my_plugin_'
		 *
		 * @todo make this auto set in the constructor
		 *
		 * @var string
		 * @access public
		 */
		public $tablePrefix;

	/**
	 * Cache queries
	 *
	 * This is cakes own in memory cache which is only per request. See
	 * cacheFinds / cachePagination for more permanent model query cache
	 *
	 * @var boolean
	 */
		public $cacheQueries = false;

	/**
	 * Cache general queries or not
	 *
	 * @var boolean
	 */
		public $cacheFinds = true;

	/**
	 * Cache pagination queries or not
	 *
	 * @var boolean
	 */
		public $cachePagination = false;

		public $__jsonErrors = array();

		/**
		 * Behaviors to attach to the site.
		 *
		 * @var string
		 * @access public
		 */
		public $actsAs = array(
			'Libs.Infinitas',
			'Events.Event'
		);
		/**
		 * recursive level should always be -1
		 *
		 * @var string
		 * @access public
		 */
		public $recursive = -1;

		/**
		 * error messages in the model
		 *
		 * @todo this should either be named $errors or made protected
		 *
		 * @var string
		 * @access public
		 */
		public $errors = array();

		/**
		 * Plugin that the model belongs to.
		 *
		 * @var string
		 * @access public
		 */
		public $plugin = null;

		/**
		 * auto delete cache
		 *
		 * @var bool
		 * @access public
		 */
		public $autoCacheClear = true;

		public $queryLimit = 20;

		/**
		 * @brief Constructor for models
		 *
		 * Throughout Infinitas this method is mainly used to define the validation
		 * rules for models. See below if there is any thing else specific to the
		 * model calling this method.
		 *
		 * @link http://api13.cakephp.org/class/model#method-Model__construct
		 *
		 * @throw E_USER_WARNING if the model is using AppModel for a virtual model.
		 *
		 * @param mixed $id Set this ID for this model on startup, can also be an array of options, see Model::__construct().
		 * @param string $table Name of database table to use.
		 * @param string $ds DataSource connection name.
		 * @access public
		 *
		 * @return void
		 */
		public function __construct($id = false, $table = null, $ds = null) {
			$this->__getPlugin();

			$this->findMethods['active'] = true;
			$this->findMethods['inactive'] = true;

			parent::__construct($id, $table, $ds);
			if($this->tablePrefix != '') {
				$config = $this->getDataSource()->config;

				if(isset($config['prefix'])) {
					$this->tablePrefix = $config['prefix'] . $this->tablePrefix;
				}
			}

			$this->__setupDatabaseConnections();

			$thisClass = get_class($this);

			$ignore = array(
				'SchemaMigration',
				'Session'
			);
			if(php_sapi_name() != 'cli' && !in_array($this->alias, $ignore) && ($thisClass == 'AppModel' || $thisClass == 'Model')) {
				trigger_error(sprintf(__('%s is using AppModel, please create a model file'), $this->alias), E_USER_WARNING);
			}

			$schema = $this->schema();
			if (get_class($this) !== 'AppModel' && !empty($schema) && $this->Behaviors->enabled('Event')) {
				$this->triggerEvent('attachBehaviors', array(
					'cache' => false
				));
				$this->Behaviors->attach('Containable');
			}
		}

		/**
		 * @brief Called before each save operation, after validation. Return a non-true result
		 * to halt the save.
		 *
		 * @link http://api13.cakephp.org/class/model#method-ModelbeforeSave
		 *
		 * @param $created True if this save created a new record
		 * @access public
		 *
		 * @return boolean True if the operation should continue, false if it should abort
		 */
		public function beforeSave($options = array()) {
			return parent::beforeSave($options);
		}

		/**
		 * @brief called after something is saved
		 *
		 * @link http://api13.cakephp.org/class/model#method-ModelafterSave
		 *
		 * @param $created True if this save created a new record
		 * @access public
		 *
		 * @return void
		 */
		public function afterSave($created) {
			return $this->__clearCache();
		}

		/**
		 * @brief called after something is deleted.
		 *
		 * @link http://api13.cakephp.org/class/model#method-ModelafterDelete
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function afterDelete() {
			return $this->__clearCache();
		}

		/**
		 * Read from the data source
		 *
		 * This method is overloaded to provide cache on queries
		 *
		 * @param string $type the find type being done
		 * @param array $query the query array of conditions
		 *
		 * @return array
		 */
		protected function _readDataSource($type, $query) {
			$cache = $this->cacheFinds;
			if (array_key_exists('page', $query)) {
				$cache = $this->cachePagination;
			}
			$query = array_merge(array('cache' => $cache), $query);

			if ($query['cache'] === false) {
				return parent::_readDataSource($type, $query);
			}

			$cacheName = cacheName(sprintf('find.%s.%s.', $this->alias, $type), $query);
			$results = Cache::read($cacheName, Inflector::underscore($this->plugin));
			if ($results !== false) {
				return $results;
			}

			$results = parent::_readDataSource($type, $query);
			$written = Cache::write($cacheName, $results, Inflector::underscore($this->plugin));
			if (!$written) {
				$written = Cache::write($cacheName, $results, 'infinitas');
			}
			return $results;
		}

		/**
		 * @brief Delete all cahce for the plugin.
		 *
		 * Will automaticaly delete all the cache for a model that it can detect.
		 * you can overlaod after save/delete to stop this happening if you dont
		 * want all your cache rebuilt after a save/delete.
		 *
		 * @todo should use the clear_cache plugin for this
		 *
		 * @access private
		 *
		 * @return void
		 */
		private function __clearCache() {
			return ClearCache::engines(Inflector::underscore($this->plugin));
		}

		/**
		 * @brief get the number of rows that was affected by the last query
		 *
		 * @return integer
		 */
		public function getAffectedRows() {
			return ConnectionManager::getDataSource($this->useDbConfig)->lastAffected();
		}

		/**
		 * @brief create model joins
		 *
		 * Create a join automatically from what has been defined in the models
		 * already.
		 *
		 * The method can be called with one param having an array of options or
		 * just the model being joined in simple cases.
		 *
		 *  - from: The model joining from (optional, will use calling model if not set)
		 *  - model: Used as the to model if passing first argument as array (required)
		 *  - table: if you need to change the table from the default in the model (optional)
		 *  - alias: alias of the join being made (optional)
		 *  - conditions: built using the relation fk if defined, else the conditions. see AppModel::_joinConditions()
		 *
		 * @param Model|array|string $Model see AppModel::_getModelObject()
		 * @param array $options join options
		 *
		 * @return array
		 */
		public function autoJoinModel($Model, $options = array()) {
			if(is_array($Model) && !empty($Model['model'])) {
				$options = $Model;
				$Model = $options['model'];
				unset($options['model']);
			}

			$Model = $this->_getModelObject($Model);

			$options = array_merge(
				array(
					'table' => $this->fullTableName($Model),
					'alias' => $Model->alias,
					'type' => 'left',
					'conditions' => null
				),
				$options
			);

			$this->_joinConditions($Model, $options);

			if($Model->alias !== $options['alias']) {
				$conditions = array();
				foreach($options['conditions'] as $k => $v) {
					$k = str_replace($Model->alias, $options['alias'], $k);
					$v = str_replace($Model->alias, $options['alias'], $v);
					$conditions[$k] = $v;
				}
				$options['conditions'] = $conditions;
			}
			unset($options['from']);

			return $options;
		}

		/**
		 * @brief figure out the relation between two models
		 *
		 * will return a relation type such as hasOne, belongsTo etc if found,
		 * false if nothing is found.
		 *
		 * @param Model|array|string $Model see AppModel::_getModelObject()
		 * @param Model|array|string $FromModel see AppModel::_getModelObject()
		 *
		 * @return string|boolean
		 */
		protected function _relationType($Model, $FromModel = null) {
			$Model = $this->_getModelObject($Model);
			$FromModel = $this->_getModelObject($FromModel);

			foreach(array('hasOne', 'belongsTo', 'hasMany', 'hasAndBelongsToMany') as $relation) {
				if(!empty($FromModel->{$relation}[$Model->alias])) {
					return $relation;
				}
			}

			return false;
		}

		/**
		 * @brief build the join conditions
		 *
		 * @param Model|array|string $Model see AppModel::_getModelObject()
		 * @param array $options The options being built for the join
		 *
		 * @return void
		 *
		 * @throws InvalidArgumentException
		 */
		protected function _joinConditions($Model, &$options) {
			if(!empty($options['conditions'])) {
				return;
			}

			$FromModel = $this;
			if(!empty($options['from'])) {
				$FromModel = $this->_getModelObject($options['from']);
			}

			$alias = $Model->alias;
			if(!empty($options['alias'])) {
				$alias = $options['alias'];
			}

			$relationType = $this->_relationType($Model, $FromModel);
			switch($relationType) {
				case 'hasOne':
					if(empty($FromModel->{$relationType}[$Model->alias]['foreignKey'])) {
						if(empty($FromModel->{$relationType}[$Model->alias]['conditions'])) {
							throw new InvalidArgumentException('Unable to determin relation');
						}

						return $options['conditions'] = $FromModel->{$relationType}[$Model->alias]['conditions'];
					}

					if(!empty($FromModel->{$relationType}[$Model->alias]['conditions'])) {
						$options['conditions'] = $FromModel->{$relationType}[$Model->alias]['conditions'];
					}
					$options['conditions'][] = $options['alias'] . '.' . $FromModel->{$relationType}[$Model->alias]['foreignKey'] . ' = ' . $FromModel->alias . '.' . $FromModel->primaryKey;
					return;
					break;

				case 'hasMany':
				case 'belongsTo':
					if(empty($FromModel->{$relationType}[$Model->alias]['foreignKey'])) {
						if(empty($FromModel->{$relationType}[$Model->alias]['conditions'])) {
							throw new InvalidArgumentException('Unable to determin relation');
						}

						return $options['conditions'] = $FromModel->{$relationType}[$Model->alias]['conditions'];
					}

					if($relationType == 'belongsTo') {
						return $options['conditions'] = array(
							$options['alias'] . '.' . $FromModel->primaryKey . ' = ' . $FromModel->alias . '.' . $FromModel->{$relationType}[$Model->alias]['foreignKey']
						);
					} elseif($relationType == 'hasMany') {
						return $options['conditions'] = array(
							$options['alias'] . '.' . $FromModel->{$relationType}[$Model->alias]['foreignKey'] . ' = ' . $FromModel->alias . '.' . $FromModel->primaryKey
						);
					}
					break;

				default:
					throw new InvalidArgumentException(sprintf('Unknown join "%s" to "%s"', $FromModel->alias, $Model->alias));
					break;
			}
		}

		/**
		 * @brief figure out the model object from a string or model
		 *
		 * This is used to find the correct model object from a string, in a relation
		 * or it will be loaded.
		 *
		 * @param Model|string $Model A model instance or Plugin.Model string
		 *
		 * @return \Model
		 *
		 * @throws InvalidArgumentException
		 */
		protected function _getModelObject($Model) {
			if($Model === null) {
				return $this;
			}

			if($Model instanceof Model) {
				return $Model;
			}

			if(strstr($Model, '.') === false) {
				throw new InvalidArgumentException('Invalid model name passed for relation');
			}

			list(, $model) = pluginSplit($Model);
			if(!empty($this->{$model}) && $this->{$model} instanceof Model) {
				return $this->{$model};
			}

			return ClassRegistry::init($Model);
		}

		/**
		 * @brief generate the full table for joins that are safe accros database connections
		 *
		 * @param Model|string $Model A model instance or Plugin.Model string
		 *
		 * @return string
		 */
		public function fullTableName($Model = null, $schema = true) {
			$Model = $this->_getModelObject($Model);

			if($schema) {
				return sprintf(
					'%s.%s%s',
					$Model->schemaName,
					$Model->tablePrefix,
					$Model->useTable
				);
			}

			return sprintf(
				'%s%s',
				$Model->tablePrefix,
				$Model->useTable
			);
		}

		/**
		 * @brief get a unique list of any model field, used in the search
		 *
		 * @param string $displayField the field to search by
		 * @param bool $primaryKey if true will return array(id, field) else array(field, field)
		 * @access public
		 *
		 * @return array the data from the find
		 */
		public function uniqueList($displayField = '', $primaryKey = false, $order = null) {
			if(empty($displayField) || !is_string($displayField) || !$this->hasField($displayField)) {
				$displayField = $this->displayField;
			}

			if(empty($primaryKey) || !is_string($primaryKey) || !$this->hasField($primaryKey)) {
				$primaryKey = $this->primaryKey;
			}

			if(empty($order)) {
				$order = array(
					$this->alias . '.' . $displayField => 'asc'
				);
			} elseif(is_string($order) && in_array(strtolower($order), array('asc', 'desc'))) {
				$order = array(
					$this->alias . '.' . $displayField => $order
				);
			}

			return $this->find(
				'list',
				array(
					'fields' => array(
						$this->alias . '.' . $primaryKey,
						$this->alias . '.' . $displayField
					),
					'group' => array(
						$this->alias . '.' . $displayField
					),
					'order' => $order
				)
			);
		}

		/**
		 * @brief Get the name of the plugin
		 *
		 * Get a model name with the plugin prepended in the format used in
		 * CR::init() and Usefull for polymorphic relations.
		 *
		 * @return string Name of the model in the form of Plugin.Name
		 *
		 * @deprecated see AppModel::fullModelName()
		 */
		public function modelName() {
			if($this->plugin == null) {
				$this->__getPlugin();
			}

			return ($this->plugin == null) ? $this->name : $this->plugin . '.' . $this->name;
		}

		/**
		 * @brief Get the current plugin.
		 *
		 * try and get the name of the current plugin from the parent model class
		 *
		 * @access private
		 *
		 * @return void
		 */
		private function __getPlugin() {
			$parentName = get_parent_class($this);

			if($parentName !== 'AppModel' && $parentName !== 'Model' && strpos($parentName, 'AppModel') !== false) {
				$this->plugin = str_replace('AppModel', '', $parentName);
			}
		}

		/**
		 * @brief add connection to the connection manager
		 *
		 * allow plugins to use their own db configs. If there is a conflict,
		 * eg: a plugin tries to set a config that alreay exists an error will
		 * be thrown and the connection will not be created.
		 *
		 * default is a reserved connection that can only be set in database.php
		 * and not via the events.
		 *
		 * @code
		 *  // for databases
		 *	array(
		 *		'my_connection' => array(
		 *			'driver' => 'mysqli',
		 *			'persistent' => true,
		 *			'host' => 'localhost',
		 *			'login' => 'username',
		 *			'password' => 'pw',
		 *			'database' => 'db_name',
		 *			'encoding' => 'utf8'
		 *		)
		 *	)
		 *
		 *	// or other datasources
		 *	array(
		 *		'my_connection' => array(
		 *			'datasource' => 'Emails.Imap'
		 *		)
		 *	)
		 * @endcode
		 *
		 * @access private
		 *
		 * @return void
		 */
		private function __setupDatabaseConnections() {
			$connections = array_filter(current(EventCore::trigger($this, 'requireDatabaseConfigs')));

			foreach($connections as $plugin => $connection) {
				$key = current(array_keys($connection));
				$connection = current($connection);

				$alreadyUsed = strtolower($key) == 'default' || in_array($key, ConnectionManager::sourceList());

				if($alreadyUsed) {
					continue;
					throw new Exception(sprintf(__('The connection "%s" in the plugin "%s" has already been used. Skipping'), $key, $plugin));
				}

				ConnectionManager::create($key, $connection);
			}
		}

		/**
		 * @brief wrapper for transactions
		 *
		 * Allow you to easily call transactions manually if you need to do saving
		 * of lots of data, or just nested relations etc.
		 *
		 * @code
		 *	// start a transaction
		 *	$this->transaction();
		 *
		 *	// rollback if things are wrong (undo)
		 *	$this->transaction(false);
		 *
		 *	// commit the sql if all is good
		 *	$this->transaction(true);
		 * @endcode
		 *
		 * @access public
		 *
		 * @param mixed $action what the command should do
		 *
		 * @return see the methods for tranasactions in cakephp dbo
		 */
		public function transaction($action = null) {
			$this->__dataSource = $this->getDataSource();

			$return = false;

			if($action === null) {
				$return = $this->__dataSource->begin($this);
			}

			else if($action === true) {
				$return = $this->__dataSource->commit($this);
			}

			else if($action === false) {
				$return = $this->__dataSource->rollback($this);
			}

			return $return;
		}

		/**
		 * @auto bind models that can be contained
		 *
		 * This is used for polymorphic type relations and will auto bind the relations
		 * to the model. you can then contain any of them in the finds so that the
		 * related data is availble in your views
		 *
		 * the field saving the models should be plugin.model format in the
		 * database so they can be loaded correctly
		 *
		 * @access public
		 *
		 * @param string $field the field that holds the model name
		 * @param sting $foreignKey the field used to hold the foreign key for the join
		 * @param array $conditions optional conditions for the join
		 * @param array $defaultContains optional array of default contains to use
		 *
		 * @return array list of available contains that you can now use
		 */
		public function buildContainsFromData($field = 'model', $foreignKey = 'foreign_key', $conditions = array(), $defaultContains = array()) {
			if(!$this->hasField($field)) {
				return false;
			}

			$models = array_unique(array_merge($defaultContains, array_filter($this->uniqueList($field))));

			$this->possibleContains = array('');
			foreach($models as $model) {
				list($pluginName, $modelName) = pluginSplit($model);
				$this->bindModel(
					array(
						'belongsTo' => array(
							$modelName => array(
								'className' => $model,
								'foreignKey' => $foreignKey,
								'conditions' => $conditions
							)
						)
					),
					false
				);

				$this->possibleContains[] = $modelName;
			}

			$this->possibleContains = array_filter($this->possibleContains);

			return $this->possibleContains;
		}

		/**
		 * @brief Get the full model name, opposit of plugin split
		 *
		 * @access public
		 *
		 * @return string the models full plugin.model name
		 */
		public function fullModelName() {
			return $this->plugin . '.' . $this->alias;
		}

		/**
		 * @brief get the full field name Model.field
		 *
		 * @param string $field the field
		 * @param string $alias use a different alias to the models own
		 *
		 * @return string
		 */
		public function fullFieldName($field, $alias = null) {
			if ($alias === null) {
				$alias = $this->alias;
			}
			return $alias . '.' . $field;
		}

		/**
		 * @brief find active rows
		 *
		 * @throws CakeException
		 *
		 * @param string $state Either "before" or "after"
		 * @param array $query
		 * @param array $results
		 *
		 * @return int The active rows
		 */
		protected function _findActive($state, $query, $results = array()) {
			if ($state === 'before') {
				if(!$this->hasField('active')) {
					throw new CakeException('Missing active field in model ' . $this->name);
				}

				$query['conditions'][$this->alias . '.active'] = 1;
				return $query;
			}

			return $results;
		}

		/**
		 * @brief find inactive rows
		 *
		 * @throws CakeException
		 *
		 * @param string $state Either "before" or "after"
		 * @param array $query
		 * @param array $results
		 *
		 * @return int The active rows
		 */
		protected function _findInactive($state, $query, $results = array()) {
			if ($state === 'before') {
				if(!$this->hasField('active')) {
					throw new CakeException('Missing active field in model ' . $this->name);
				}

				$query['conditions'][$this->alias . '.active'] = 0;
				return $query;
			}

			return $results;
		}

	}

	EventCore::trigger(new stdClass(), 'loadAppModel');