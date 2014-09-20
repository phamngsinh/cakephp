<?php
/**
 * Module
 *
 * @package Infinitas.Modules.Model
 */

/**
 * Module
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Modules.Model
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.7a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class Module extends ModulesAppModel {
/**
 * enable row locking see Locks plugin
 *
 * @var boolean
 */
	public $lockable = true;

/**
 * Behaviors to load
 *
 * @var array
 */
	public $actsAs = array(
		'Libs.Sequence' => array(
			'groupFields' => array(
				'position_id'
			)
		)
	);

/**
 * BelongsTo relations
 *
 * @var array
 */
	public $belongsTo = array(
		'Position' => array(
			'className' => 'Modules.ModulePosition',
			'foreignKey' => 'position_id',
			'counterCache' => true,
			'counterScope' => array('Module.active' => 1)
		),
		'Users.Group',
		'Theme' => array(
			'className' => 'Themes.Theme',
			'foreignKey' => 'theme_id'
		),
	);

/**
 * HasMany relations
 *
 * @var array
 */
	public $hasMany = array(
		'ModuleRoute' => array(
			'className' => 'Modules.ModulesRoute',
			'foreignKey' => 'module_id'
		)
	);

/**
 * HasAndBelongsToMany relations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Route' => array(
			'className' => 'Routes.Route',
			'with' => 'Modules.ModulesRoute',
			'foreignKey' => 'module_id',
			'associationForeignKey' => 'route_id',
			'unique' => true
		)
	);

/**
 * reusable contain array
 *
 * @var array
 */
	private $__contain = array(
		'Position' => array(
			'fields' => array(
				'Position.id',
				'Position.name'
			)
		),
		'Group' => array(
			'fields' => array(
				'Group.id',
				'Group.name'
			)
		),
		'Route' => array(
			'fields' => array(
				'Route.id',
				'Route.name',
				'Route.url'
			)
		),
		'Theme' => array(
			'fields' => array(
				'Theme.id',
				'Theme.name'
			)
		)
	);

/**
 * Constructor
 *
 * @param type $id
 * @param type $table
 * @param type $ds
 *
 * @return void
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		$this->subPath = 'View' . DS . 'Elements' . DS . 'modules' . DS;

		$this->virtualFields = array(
			'list_name' => "if (:alias.admin = 1, CONCAT('Admin :: ', :alias.name), :alias.name)",
			'save_name' => "if (:alias.admin = 1, CONCAT('admin/', :alias.module), :alias.module)"
		);

		foreach ($this->virtualFields as &$field) {
			$field = String::insert($field, array('alias' => $this->alias));
		}

		$this->order = array(
			$this->alias . '.position_id' => 'ASC',
			$this->alias . '.ordering' => 'ASC'
		);

		$this->validate = array(
			'name' => array(
				'notEmpty' => array(
					'required' => true,
					'rule' => 'notEmpty',
					'message' => __d('modules', 'Please enter a name for this module')),
				'alpha' => array(
					'required' => true,
					'rule' => '/^[a-z ]{1,255}$/i',
					'message' => __d('modules', 'Please enter a valid name (alpha numeric with spaces)'))),
			'config' => array(
				'validateJson' => array(
					'rule' => 'validateJson',
					'allowEmpty' => true,
					'message' => __d('modules', 'Please enter a valid json config or leave blank'))),
			'group_id' => array(
				'notEmpty' => array(
					'required' => true,
					'rule' => 'notEmpty',
					'message' => __d('modules', 'Please select the group this module is for'))),
			'position_id' => array(
				'notEmpty' => array(
					'required' => true,
					'rule' => 'notEmpty',
					'message' => __d('modules', 'Please select the position this module will show in')),
				'validateValidPosition' => array(
					'required' => true,
					'rule' => 'validateValidPosition',
					'message' => __d('modules', 'Please select a valid position'))),
			'plugin' => array(
				'notEmpty' => array(
					'required' => true,
					'rule' => 'notEmpty',
					'message' => __d('modules', 'Please select the plugin this module is loaded from')),
				'validatePluginExists' => array(
					'required' => true,
					'rule' => 'validatePluginExists',
					'message' => __d('modules', 'Please select a valid plugin'))),
			'module' => array(
				'notEmpty' => array(
					'required' => true,
					'rule' => 'notEmpty',
					'message' => __d('modules', 'Please select the module to load')),
				'validateValidModule' => array(
					'required' => true,
					'rule' => 'validateValidModule',
					'message' => __d('modules', 'Please select a valid module'))));
	}

/**
 * check that the position entered is valid
 *
 * @param array $field the field being validated
 *
 * @return boolean
 */
	public function validateValidPosition($field = array()) {
		return $this->Position->exists(current($field));
	}

/**
 * check that a module is valid
 * @param type $field
 */
	public function validateValidModule($field = array()) {
		if (empty($this->data[$this->alias]['plugin'])) {
			return false;
		}

		try {
			return $this->hasModule(
				$this->data[$this->alias]['plugin'],
				current($field),
				$this->data[$this->alias]['admin']
			);
		}

		catch(Exception $e) {
			return false;
		}
	}

/**
 * after getting modules add the related routes to the find
 *
 * @param type $results
 * @param type $primary
 * @return type
 */
	public function afterFind($results, $primary = false) {
		foreach ($results as &$result) {
			if (empty($result[$this->alias]['id'])) {
				$result['ModuleRoute'] = array();
				continue;
			}
			$result['ModuleRoute'] = $this->ModuleRoute->find(
				'all',
				array(
					'fields' => array(
						'ModuleRoute.*',
						'Route.id',
						'Route.url',
						'Route.name',
					),
					'conditions' => array(
						'ModuleRoute.module_id' => $result[$this->alias]['id']
					),
					'joins' => array(
						array(
							'table' => 'core_routes',
							'alias' => 'Route',
							'type' => 'LEFT',
							'conditions' => array(
								'ModuleRoute.route_id = Route.id'
							)
						),
					)
				)
			);

			$result['ModuleRoute'] = Set::extract('/', $result['ModuleRoute']);
		}

		return parent::afterFind($results, $primary);
	}

/**
 * decide if its an admin module or not.
 *
 * @param type $options array
 * @return boolean
 */
	public function beforeValidate($options = array()) {
		if (!empty($this->data[$this->alias]['module'])) {
			$this->data[$this->alias]['admin'] = strstr($this->data[$this->alias]['module'], 'admin/') ? 1 : 0;
		}

		return parent::beforeValidate($options);
	}

/**
 * get a list of modules based on the select position and admin flag
 *
 * @param string $position the name of the position to look up
 * @param boolean $admin true to get admin modules, false for frontend stuff
 *
 * @return array
 */
	public function getModules($position = null, $admin = false) {
		if (!$position) {
			return array();
		}

		$lockerBehavior = false;
		if ($this->Behaviors->enabled('Lockable')) {
			$lockerBehavior = true;
			$this->Behaviors->disable('Lockable');
		}

		$modules = $this->find(
			'all',
			array(
				'fields' => array(
					$this->alias . '.id',
					$this->alias . '.name',
					$this->alias . '.plugin',
					$this->alias . '.content',
					$this->alias . '.module',
					$this->alias . '.config',
					$this->alias . '.show_heading',
					'Position.id',
					'Position.name',
					'Group.id',
					'Group.name',
					'Theme.id',
					'Theme.name',
				),
				'conditions' => array(
					'Position.name' => $position,
					$this->alias . '.admin' => $admin,
					$this->alias . '.active' => 1
				),
				'joins' => array(
					array(
						'table' => 'core_module_positions',
						'alias' => 'Position',
						'type' => 'LEFT',
						'conditions' => array(
							$this->alias . '.position_id = Position.id'
						)
					),
					array(
						'table' => 'core_groups',
						'alias' => 'Group',
						'type' => 'LEFT',
						'conditions' => array(
							$this->alias . '.group_id = Group.id'
						)
					),
					array(
						'table' => 'core_themes',
						'alias' => 'Theme',
						'type' => 'LEFT',
						'conditions' => array(
							$this->alias . '.theme_id = Theme.id'
						)
					)
					/**
						* @todo join Routes on
						*/
				)
			)
		);

		if ($lockerBehavior) {
			$this->Behaviors->enable('Lockable');
		}

		return $modules;
	}

/**
 * get a single module
 *
 * @param string $module the name of the module to find
 * @param boolean $admin the type (could be admin / frontend with the same name)
 *
 * @return array
 */
	public function getModule($module, $admin = false) {
		$lockerBehavior = false;
		if ($this->Behaviors->enabled('Lockable')) {
			$lockerBehavior = true;
			$this->Behaviors->disable('Lockable');
		}

		$module = $this->find('first', array(
			'fields' => array(
				$this->alias . '.id',
				$this->alias . '.name',
				$this->alias . '.plugin',
				$this->alias . '.content',
				$this->alias . '.module',
				$this->alias . '.config',
				$this->alias . '.show_heading'
			),
			'conditions' => array(
				$this->alias . '.name' => $module,
				$this->alias . '.admin' => $admin,
				$this->alias . '.active' => 1
			),
			'contain' => $this->__contain
		));

		if ($lockerBehavior) {
			$this->Behaviors->enable('Lockable');
		}

		return $module;
	}

/**
 * get a list of modules with the option to limit by plugin
 *
 * @param string|null $plugin the name of a plugin or nothing for all modules
 *
 * @return array
 */
	public function getModuleList($plugin = null) {
		$admin = $nonAdmin = array();

		$conditions = array();
		$path = APP;
		if ($plugin) {
			$path = InfinitasPlugin::path($plugin);
			$conditions = array($this->alias . '.plugin' => $plugin);
		}

		App::import('File');
		$this->Folder = new Folder($path . $this->subPath);

		$files = $this->Folder->read();

		foreach ($files[0] as $folder) {
			if ($folder !== 'admin') {
				$nonAdmin[$folder] = Inflector::humanize($folder);
			}
		}
		foreach ($files[1] as $file) {
			$file = str_replace('.ctp', '', $file);
			$nonAdmin[$file] = Inflector::humanize($file);
		}

		if (!empty($files[0]) && is_dir($path . $this->subPath . 'admin')) {
			$this->Folder->cd($path . $this->subPath . 'admin');
			$files = $this->Folder->read();

			foreach ($files[0] as $folder) {
				if ($folder !== 'admin') {
					$admin['admin/' . $folder] = Inflector::humanize($folder);
				}
			}
			foreach ($files[1] as &$file) {
				$file = str_replace('.ctp', '', $file);
				$admin['admin/' . $file] = Inflector::humanize($file);
			}
		}

		return array(
			'admin' => $admin,
			'user' => $nonAdmin
		);
	}

/**
 * check if a plugin has the specified module
 *
 * @param string $plugin the name of the plugin
 * @param string $module the name of the module
 * @param boolean $admin true for admin modules, false for frontend modules
 *
 * @return boolean
 */
	public function hasModule($plugin, $module, $admin = false) {
		$moduleList = $this->getModuleList($plugin);

		$userType = $admin ? 'admin' : 'user';

		return !empty($moduleList[$userType][$module]);
	}
}
