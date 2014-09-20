<?php
	/**
	 * @mainpage Infinitas - CakePHP powered Content Management Framework
	 *
	 * @section infinitas-overview What is it
	 *
	 * Infinitas is a content management framework that allows you to create powerful
	 * application using the CakePHP framework in the fastes way posible. It follows
	 * the same convention over configuration design paradigm. All the coding standards
	 * of CakePHP are followed, and the core libs are used as much as possible to
	 * limit the amount of time that is required to get a hang of what is going on.
	 *
	 * Infinitas aims to take care of all the normal things that most sites require
	 * so that you can spend time building the application instead. Things like
	 * comments, users, auth, geo location, emailing and view counting is built into
	 * the core.
	 *
	 * There is a powerful Event system that makes plugins truly seperate from
	 * the core, so seperate that they can be disabled from the backend and its like
	 * the plugin does not exist.
	 *
	 * The bulk of work that has been done to Infinitas has been to the admin
	 * backend and internal libs. The final product is something that is extreamly
	 * easy to extend, but also very usable. Knowing that one day you will need to
	 * hand the project over to a client that may not be to technical has always been
	 * one of the main considerations.
	 *
	 * Infinitas is here to bridge the gap between usablity and extendability offering
	 * the best of both worlds, something that developers can build upon and end users
	 * can actually use.
	 *
	 * @section categories-usage How to use it
	 *
	 * To get started check out the installation guide, currently there is only
	 * a web based installer but shortly we will have some shell commands for
	 * the people that are not fond of icons.
	 *
	 * You may also want to check the feature list and versions to get an overview
	 * of what the project has to offer.
	 */

	/**
	 * @page AppController AppController
	 *
	 * @section app_controller-overview What is it
	 *
	 * AppController is the main controller method that all other countrollers
	 * should normally extend. This gives you a lot of power through inheritance
	 * allowing things like mass deletion, copying, moving and editing with absolutly
	 * no code.
	 *
	 * AppController also does a lot of basic configuration for the application
	 * to run like automatically putting components in to load, compressing output
	 * setting up some security and more.
	 *
	 * @section app_controller-usage How to use it
	 *
	 * Usage is simple, extend your MyPluginAppController from this class and then the
	 * controllers in your plugin just extend MyPluginAppController. Example below:
	 *
	 * @code
	 *	// in APP/plugins/my_plugin/my_plugin_app_controller.php create
	 *	class MyPluginAppController extends AppModel{
	 *		// do not set the name in this controller class, there be gremlins
	 *	}
	 *
	 *	// then in APP/plugins/my_plugin/controllers/something.php
	 *	class SomethingsController extends MyPluginAppController{
	 *		public $name = 'Somethings';
	 *		//...
	 *	}
	 * @endcode
	 *
	 * After that you will be able to directly access the public methods that
	 * are available from this class as if they were in your controller.
	 *
	 * @code
	 *	$this->someMethod();
	 * @endcode
	 *
	 * @section app_controller-see-also Also see
	 * @ref GlobalActions
	 * @ref InfinitasComponent
	 * @ref Event
	 * @ref MassActionComponent
	 * @ref InfinitasView
	 */

	App::uses('InfinitasComponent', 'Libs.Controller/Component');
	App::uses('InfinitasHelper', 'Libs.View/Helper');
	App::uses('Controller', 'Controller');

	/**
	 * @brief AppController is the main controller class that all plugins should extend
	 *
	 * This class offers a lot of methods that should be inherited to other controllers
	 * as it is what allows you to build plugins with minimal code.
	 *
	 * @property      InfinitasComponent $Infinitas
	 * @property      InfinitasActionsComponent $InfinitasActions
	 * @property      MassActionComponent $MassAction
	 * @property      WizardComponent $Wizard
	 * @property      CommentsComponent $Comments
	 * @property      GlobalContentsComponent $GlobalContents
	 * @property      EventComponent $Event
	 * @property      FileUploadComponent $FileUpload
	 * @property      FilterComponent $Filter
	 * @property      GeoLocationComponent $GeoLocation
	 * @property      LockerComponent $Locker
	 * @property      EmailerComponent $Emailer
	 * @property      InfinitasSecurityComponent $InfinitasSecurity
	 * @property      ThemesComponent $Themes
	 * @property      VisitorComponent $Visitor
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

	class AppController extends Controller {
		/**
		 * the View Class that will load by defaul is the Infinitas View to take
		 * advantage which extends the ThemeView and auto loads the Mustache class.
		 * This changes when requests are json etc
		 */
		public $viewClass = 'Libs.Infinitas';

		public $modelClass;

		/**
		 * components should not be included here
		 *
		 * @var array
		 * @access public
		 */
		public $components = array();

		/**
		 * reference to the model name for user output
		 *
		 * @var string
		 * @access public
		 */
		public $prettyModelName;

		/**
		 * empty paginate option.
		 */
		public $paginate = array();

		/**
		 * @brief defaults for AppController::notice()
		 * @var array
		 */
		public $notice = array(
			'saved' => array(
				'message' => 'Your %s was saved',
				'redirect' => ''
			),
			'not_saved' => array(
				'message' => 'There was a problem saving your %s',
				'level' => 'warning'
			),
			'invalid' => array(
				'message' => 'Invalid %s selected, please try again',
				'level' => 'error',
				'redirect' => true
			),
			'deleted' => array(
				'message' => 'Your %s was deleted',
				'redirect' => ''
			),
			'not_deleted' => array(
				'message' => 'Your %s was not deleted',
				'level' => 'error',
				'redirect' => true
			),
			'disabled' => array(
				'message' => 'That action has been disabled',
				'level' => 'error',
				'redirect' => true
			),
			'not_implemented' => array(
				'message' => 'The selected action has not been implemented',
				'level' => 'warning',
				'redirect' => true
			),
			'auth' => null,
			'require_auth' => array(
				'message' => 'Authentication required, please login to continue',
				'level' => 'warning',
				'redirect' => array(
					'plugin' => 'users',
					'controller' => 'users',
					'action' => 'login'
				)
			)
		);

		/**
		 * internal cache of css files to load
		 *
		 * @var array
		 * @access private
		 */
		private $__addCss = array();

		/**
		 * internal cache of javascript files to load
		 *
		 * @var array
		 * @access private
		 */
		private $__addJs  = array();

		private $__callBacks = array(
			'beforeFilter' => false,
			'beforeRender' => false,
			'afterFilter' => false
		);

		/**
		 * @brief Construct the Controller
		 *
		 * Currently getting components that are needed by the application. they
		 * are then loaded into $components making them available to the entire
		 * application.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function __construct($request = null, $response = null) {
			if(get_class($this) == 'InfinitasErrorController') {
				parent::__construct($request, $response);
				return;
			}

			$this->__setupConfig();
			$event = EventCore::trigger($this, 'requireComponentsToLoad');

			if(isset($event['requireComponentsToLoad']['libs'])) {
				$libs['libs'] = $event['requireComponentsToLoad']['libs'];
				$event['requireComponentsToLoad'] = $libs + $event['requireComponentsToLoad'];
			}

			foreach($event['requireComponentsToLoad'] as $plugin => $components) {
				if(!empty($components)) {
					if(!is_array($components)) {
						$components = array($components);
					}
					$this->components = array_merge((array)$this->components, (array)$components);
				}
			}

			parent::__construct($request, $response);
		}

		/**
		 * @brief normal before filter.
		 *
		 * set up some variables and do a bit of pre processing before handing
		 * over to the controller responsible for the request.
		 *
		 * @link http://api.cakephp.org/class/controller#method-ControllerbeforeFilter
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function beforeFilter() {
			parent::beforeFilter();

			$this->request->params['admin'] = isset($this->request->params['admin']) ? $this->request->params['admin'] : false;

			if($this->request->params['admin'] && $this->request->params['action'] != 'admin_login' && $this->Auth->user('group_id') != 1) {
				$this->redirect(array('admin' => 1, 'plugin' => 'users', 'controller' => 'users', 'action' => 'login'));
			}

			if (isset($this->request->data['PaginationOptions']['pagination_limit'])) {
				$this->Infinitas->changePaginationLimit( $this->request->data['PaginationOptions'], $this->request->params );
			}

			if (isset($this->request->params['named']['limit'])) {
				$this->request->params['named']['limit'] = $this->Infinitas->paginationHardLimit($this->request->params['named']['limit']);
			}

			if (sizeof($this->uses) && (isset($this->{$this->modelClass}->Behaviors) && $this->{$this->modelClass}->Behaviors->attached('Logable'))) {
				$this->{$this->modelClass}->setUserData($this->Auth->user());
			}

			$this->__callBacks[__FUNCTION__] = true;

			$this->prettyModelName = prettyName($this->modelClass);
			if(!empty($this->Session) && !$this->Session->read('ip_address')) {
				$this->Session->write('ip_address', $this->request->clientIp());
			}

			$modelName = !empty($this->prettyModelName) ? $this->prettyModelName : prettyName($this->name);
			$modelName = Inflector::singularize($modelName);
			foreach($this->notice as $type => &$config) {
				if(empty($config['message'])) {
					continue;
				}

				if(strstr($config['message'], '%s')) {
					$plugin = Inflector::underscore($this->plugin);
					$config['message'] = __d($plugin, $config['message'], $modelName);
				}
			}
		}

		/**
		 * @brief called before a page is loaded
		 *
		 * before render is called before the page is rendered, but after all the
		 * processing is done.
		 *
		 * @link http://api.cakephp.org/class/controller#method-ControllerbeforeRender
		 *
		 * @todo this could be moved to the InfinitasView class
		 */
		public function beforeRender() {
			parent::beforeRender();

			switch(true) {
				case $this->request->is('ajax'):
				case (!empty($this->request->params['ext']) && $this->request->params['ext'] == 'json'):
					if(isset($this->viewVars['json'])) {
						$this->viewVars['json'] = array('json' => $this->viewVars['json']);
						$this->set('_serialize', 'json');
					}
					//Configure::write('debug', 0);
					break;

				case $this->request->is('ajax'):

					break;

				/*case $this->RequestHandler->prefers('rss'):
					;
					break;

				case $this->RequestHandler->prefers('vcf'):
					;
					break;*/
			}

			$this->Infinitas->getPluginAssets();
			$this->set('css_for_layout', array_filter($this->__addCss));
			$this->set('js_for_layout', array_filter($this->__addJs));

			$fields = array(
				$this->request->params['plugin'],
				$this->request->params['controller'],
				$this->request->params['action']
			);

			$this->set('class_name_for_layout', implode(' ', $fields));
			unset($fields);

			$this->__callBacks[__FUNCTION__] = true;
		}

		/**
		 * @brief redirect pages
		 *
		 * Redirect method, will remove the last_page session var that is stored
		 * when adding/editing things in admin. makes redirect() default to /index
		 * if there is no last page.
		 *
		 * @access public
		 *
		 * @link http://api.cakephp.org/class/controller#method-Controllerredirect
		 *
		 * @param mixed $url string or array url
		 * @param int $status the code for the redirect 301 / 302 etc
		 * @param bool $exit should the script exit after the redirect
		 *
		 * @return void
		 */
		public function redirect($url = null, $status = null, $exit = true) {
			if(!$url || $url == '') {
				$url = $this->getPageRedirectVar();

				if(!$url) {
					$url = array('action' => 'index');
				}
			}

			parent::redirect($url, $status, $exit);
		}

		/**
		 * @brief get the variable for the last page saved.
		 *
		 * Making this a bit more dry so that its less error prone
		 *
		 * @access public
		 *
		 * @return string the session key used for lookups
		 */
		public function lastPageRedirectVar() {
			return 'Infinitas.last_page.' . $this->request->here;
		}

		/**
		 * @brief save a maker for a later redirect
		 *
		 * @access public
		 *
		 * This will set a session var for the current page
		 *
		 * @return void
		 */
		public function saveRedirectMarker() {
			$var = $this->lastPageRedirectVar();

			$lastPage = $this->Session->read($var);
			if(!$lastPage && InfinitasRouter::url($lastPage) != $this->referer()) {
				$this->Session->write($var, $this->referer());
			}
		}

		/**
		 * @brief get the page redirect value
		 *
		 * This will get the correct place to redirect to if there is a value
		 * saved for the current location, if there is nothing false is returned
		 *
		 * @param bool $delete should the value be removed from session
		 *
		 * @return mixed, false for nothing, string url if available
		 */
		public function getPageRedirectVar($delete = true) {
			$var = $this->lastPageRedirectVar();

			$url = false;
			if($this->Session->check($var)) {
				$url = $this->Session->read($var);

				if($delete === true) {
					$this->Session->delete($var);
				}
			}

			return $url;
		}

		/**
		 * @brief add css from other controllers.
		 *
		 * way to inject css from plugins to the layout. call addCss(false) to
		 * clear current stack, call addCss(true) to get a list back of what is there.
		 *
		 * @param mixed $css array of paths like HtmlHelper::css or a string path
		 * @access public
		 *
		 * @return mixed bool for adding/removing or array when requesting data
		 */
		public function addCss($css = false) {
			return $this->__loadAsset($css, __FUNCTION__);
		}

		/**
		 * @brief add js from other controllers.
		 *
		 * way to inject js from plugins to the layout. call addJs(false) to
		 * clear current stack, call addJs(true) to get a list back of what is there.
		 *
		 * @param mixed $js array of paths like HtmlHelper::css or a string path
		 * @access public
		 *
		 * @return mixed bool for adding/removing or array when requesting data
		 */
		public function addJs($js = false) {
			return $this->__loadAsset($js, __FUNCTION__);
		}

		/**
		 * @brief DRY method for AppController::addCss() and AppController::addJs()
		 *
		 * loads the assets into a var that will be sent to the view, used by
		 * addCss / addJs. if false is passed in the var is reset, if true is passed
		 * in you get back what is currently set.
		 *
		 * @param mixed $data takes bool for reseting, strings and arrays for adding
		 * @param string $method where its going to store / remove
		 * @access private
		 *
		 * @return mixed true on success, arry if you pass true
		 */
		private function __loadAsset($data, $method) {
			$property = '__' . $method;
			if($data === false) {
				$this->{$property} = array();
				return true;
			}

			else if($data === true) {
				return $this->{$property};
			}

			foreach((array)$data as $_data) {
				if(is_array($_data)) {
					$this->{$method}($_data);
					continue;
				}

				if(!in_array($_data, $this->{$property}) && !empty($_data)) {
					$this->{$property}[] = $_data;
				}
			}

			return true;
		}

		/**
		 * @brief render method
		 *
		 * Infinits uses this method to use admin_form.ctp for add and edit views
		 * when there is no admin_add / admin_edit files available.
		 *
		 * @access public
		 *
		 * @link http://api.cakephp.org/class/controller#method-Controllerrender
		 *
		 * @param string $view View to use for rendering
		 * @param string $layout Layout to use
		 *
		 * @return Full output string of view contents
		 */
		public function render($view = null, $layout = null) {
			if(($this->request->action == 'admin_edit' || $this->request->action == 'admin_add')) {
				$viewPath = App::pluginPath($this->plugin) . 'View' . DS . $this->viewPath . DS . $this->request->action . '.ctp';
				if(!file_exists($viewPath)) {
					$view = 'admin_form';
				}
			}

			else if(($this->request->action == 'edit' || $this->request->action == 'add')) {
				$viewPath = App::pluginPath($this->plugin) . 'View' . DS . $this->viewPath . DS . $this->request->action . '.ctp';
				if(!file_exists($viewPath)) {
					$view = 'form';
				}
			}

			return parent::render($view, $layout);
		}

		/**
		 * @brief blackHole callback for security component
		 *
		 * this function is just here to stop wsod confusion. it will become more
		 * usefull one day
		 *
		 * @todo maybe add some emailing in here to notify admin when requests are
		 * being black holed.
		 *
		 * @link http://api.cakephp.org/view_source/security-component/#l-427
		 *
		 * @param object $Controller the controller object that triggered the blackHole
		 * @param string $error the error message
		 * @access public
		 *
		 * @return it ends the script
		 */
		public function blackHole($Controller) {
			var_dump($Controller);
			pr('you been blackHoled');
			exit;
		}

		/**
		 * @brief Create a generic warning to display usefull information to the user
		 *
		 * The method can be used in two ways, using the $this->notice param and setting
		 * up some defaults or direclty passing the message and config.
		 *
		 * @code
		 *	// manual
		 *	$this->notice(__d('plugin', 'foo bar'), array('redirect' => true, 'level' => 'warning'));
		 *
		 *	// pre set
		 *	$this->notice['my_message'] = array(
		 *		'message' => 'foo bar',
		 *		'redirect' => true, // false, '', array() '/url'
		 *		'level' => 'warning', // success, error etc
		 *	);
		 *
		 *	$this->notice('my_message');
		 *
		 *	// custom pre set uses config from ->notice['my_message'] but will
		 *	// have level of success
		 *	$this->notice('my_message', array('level' => 'success'));
		 * @endcode
		 *
		 * Infintias sets up a number of defaults for notices including saved, not_saved,
		 * invalid, deleted, not_deleted, disabled, auth. See $this->notice for more
		 * on what they are.
		 *
		 * You can overwrite the defaults by creating them in your __construct() or any time
		 * before calling Controller::notice().
		 *
		 * The code passed can be used for linking to error pages with more information
		 * eg: creating some pages on your site like /errors/<code> and then making it
		 * a clickable link the user can get more detailed information.
		 *
		 * @access public
		 *
		 * @param string $message the message to show to the user
		 * @param array $config array of options for the redirect and message
		 *
		 * @return string the markup for the error
		 */
		public function notice($message, $config = array()) {
			$_default = array(
				'level' => 'success',
				'code' => 0,
				'plugin' => 'assets',
				'redirect' => false
			);
			if($message instanceof Exception) {
				$config = array_merge(
					array('level' => 'error', 'redirect' => ''),
					$config
				);
				$message = $message->getMessage();
			}

			if(!empty($this->notice[$message])) {
				if(!is_array($this->notice[$message])) {
					$message = $this->notice[$message];
				}

				else if(!empty($this->notice[$message]['message'])) {
					$config = array_merge($this->notice[$message], $config);
					$message = $config['message'];
					unset($config['message']);
				}
			}

			$config = array_merge($_default, (array)$config);

			$vars = array(
				'code' => $config['code'],
				'plugin' => $config['plugin']
			);

			$element = 'messages/' . $config['level'];
			if(isset($this->request->params['admin']) && $this->request->params['admin']) {
				$element = 'messages/admin/' . $config['level'];
			}

			$_redirect = false;
			if($config['redirect'] || $config['redirect'] === '') {
				if($config['redirect'] === true) {
					$config['redirect'] = $this->referer();
				}
				$_redirect = $config['redirect'];
				if(isset($this->request->params['admin']) && $this->request->params['admin'] && $_redirect == '/') {
					$_redirect = '/admin';
				}
			}

			if(!$this->request->is('ajax')) {
				$this->Session->setFlash($message, $element, $vars);
				if($_redirect !== false) {
					$this->redirect($_redirect);
				}
			} else {
				$vars['level'] = $config['level'];
				$vars['message'] = $message;
				$this->set('json', array(
					'flash' => $vars,
					'redirect' => InfinitasRouter::url($_redirect)
				));
			}

			unset($_default, $config, $vars);
		}

		/**
		 * @brief Set up system configuration.
		 *
		 * Load the default configuration and check if there are any configs
		 * to load from the current plugin. configurations can be completely rewriten
		 * or just added to.
		 *
		 * @access private
		 *
		 * @return void
		 */
		protected function __setupConfig() {
			$configs = ClassRegistry::init('Configs.Config')->getConfig();

			$eventData = EventCore::trigger($this, $this->plugin.'.setupConfigStart', $configs);
			if (isset($eventData['setupConfigStart'][$this->plugin])) {
				$configs = (array)$eventData['setupConfigStart'][$this->plugin];

				if (!array($configs)) {
					$this->cakeError('eventError', array('message' => 'Your config is wrong.', 'event' => $eventData));
				}
			}

			$eventData = EventCore::trigger($this, $this->plugin.'.setupConfigEnd');
			if (isset($eventData['setupConfigEnd'][$this->plugin])) {
				$configs = $configs + (array)$eventData['setupConfigEnd'][$this->plugin];
			}

			if (!$this->__writeConfigs($configs)) {
				$this->cakeError('configError', array('message' => 'Config was not written'));
			}

			unset($configs, $eventData);
		}

		/**
		 * Write the configuration.
		 *
		 * Write all the config values that have been called found in InfinitasComponent::setupConfig()
		 *
		 * @access private
		 *
		 * @return bool
		 */
		private function __writeConfigs($configs) {
			foreach($configs as $config) {
				if(empty($config) || !is_array($config)) {
					continue;
				}

				if (!(isset($config['Config']['key']) || isset($config['Config']['value']))) {
					$config['Config']['key'] = isset($config['Config']['key']) ? $config['Config']['key'] : 'NOT SET';
					$config['Config']['value'] = isset($config['Config']['value']) ? $config['Config']['value'] : 'NOT SET';
					$this->log(serialize($config['Config']), 'configuration_error');
					continue;
				}

				Configure::write($config['Config']['key'], $config['Config']['value']);
			}

			unset($configs);
			return true;
		}

		/**
		 * Dispatches the controller action.  Checks that the action
		 * exists and isn't private.
		 *
		 * @throws PrivateActionException, MissingActionException
		 *
		 * @param CakeRequest $request the current request
		 *
		 * @return mixed The resulting response.
		 */
		public function invokeAction(CakeRequest $request) {
			try {
				parent::invokeAction($request);
			} catch (MissingActionException $e) {
				return $this->invokeComponentAction($request, $e);
			}
		}

		/**
		 * @brief catch calls to parent::missing_action() and see if a component can handle it
		 *
		 * @throws MissingActionException
		 *
		 * @param string $method the method being called
		 * @param CakeRequest $args
		 *
		 * @return mixed The resulting response.
		 */
		public function __call($method, $args) {
			return $this->invokeComponentAction($this->request, $args);
		}

		/**
		 * @brief Try invoke an action through a component
		 *
		 * @throws MissingActionException
		 *
		 * @param CakeRequest $request $the request object
		 * @param Exception $e any exceptions that were caught before
		 *
		 * @return mixed The resulting response.
		 */
		public function invokeComponentAction($request, $e = null) {
			$action = 'action' . Inflector::camelize($request->params['action']);
			foreach($this->Components->enabled() as $component) {
				if(method_exists($this->{$component}, $action)) {
					if($e instanceof MissingActionException) {
						$e = null;
					}

					if(empty($e)) {
						$e = $request->params['pass'];
					}

					return $this->{$component}->dispatchMethod($action, $e);
				}
			}

			if($e instanceof MissingActionException) {
				throw $e;
			}

			throw new MissingActionException(
				sprintf(
					'Tried to dispatch "%s()" to a component as "%s(). No component found to handle the request"',
					$this->request->params['action'],
					$action
				)
			);
		}

		/**
		 * @brief make sure everything is running or throw an error
		 */
		public function __destruct() {
			$check = array_unique($this->__callBacks);

			if(count($check) != 1 || current($check) != true) {
				//user_error('Some callbacks were not triggered, check for methods returning false', E_USER_NOTICE);
			}
		}
	}

	EventCore::trigger(new stdClass(), 'loadAppController');
