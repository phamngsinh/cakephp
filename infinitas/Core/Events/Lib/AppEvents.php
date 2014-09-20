<?php
/**
 * Base class for events.
 *
 * This is the base class for events that can provide some documentation about
 * what events are available and what they do. It extends Object so you can
 * do things like $this->log(), redirect() and other basic methods
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.App
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class AppEvents extends Object {
/**
 * internal list of available methods
 */
	private $__events = array();

/**
 * builds a list of available events
 */
	public function  __construct() {
		if (!empty($this->__events)) {
			return true;
		}

		$this->__events = get_class_methods('AppEvents');
		foreach ($this->__events as $k => $event) {
			if (substr($event, 0, 2) != 'on') {
				unset($this->__events[$k]);
			}
		}

		sort($this->__events);
	}

/**
 * available methods.
 *
 * returns an array of all the available events.
 */
	final public function availableEvents() {
		return $this->__events;
	}

/**
 * test if things are working
 */
	final public function testEvent() {
		echo '<h1>your event is working</h1>';
		echo '<p>The following events are available for use in the Infinitas core</p>';
		pr($this->availableEvents());
		exit;
	}

	public function onPluginRollCall(Event $Event) {}

/**
 * allow plugins to include libs early on in the request.
 */
	public function onRequireLibs(Event $Event) {}

/**
 * Load up some of your own 'AppControllers' so that you can create a base
 * class for a collection of plugins. Say you have a few plugins that all
 * use a specific database you can create 'CustomGlobalAppController' that
 * extends 'AppController' and then have all the 'PluginAppControllers'
 * extend your 'CustomGlobalAppController'
 *
 * They will be loaded when 'AppController' is included, before it runs.
 */
	public function onLoadAppController(Event $Event) {}


/**
 * Load up some of your own 'AppModels' so that you can create a base
 * class for a collection of plugins. Say you have a few plugins that all
 * use a specific database you can create 'CustomGlobalAppModel' that
 * extends 'AppModel' and then have all the 'PluginAppModels'
 * extend your 'CustomGlobalAppModel'
 *
 * They will be loaded when 'AppModel' is included, before it runs.
 */
	public function onLoadAppModel(Event $Event) {}

/**
 * Add database connections from your plugins with this trigger.
 * Its called in appModel before anything is created, even before default
 * (which is a reserved value)
 *
 * Event should return in the format 'name' => array('configs')
 *
 * @param object $event
 */
	public function onRequireDatabaseConfigs(Event $Event) {}

/**
 * Load the default cache settings.
 *
 * allows all the parts of the app to set up any cache configs that are
 * needed.
 *
 * Called in InfinitasComponent::initialize
 *
 * @return true
 */
	public function onSetupCache(Event $Event) {}

	public function onSetupConfigStart(Event $Event) {}

	public function onSetupConfigEnd(Event $Event) {}

/**
 * Adding routes
 *
 * Add routing to for the app from your plugin by calling Router::connect
 * This should be used for routes that will not change if your plugin
 * has routes that can be configured they should be in the database.
 *
 * @return nothing, it wont do anything
 */
	public function onSetupRoutes(Event $Event) {}

	public function onRouteParse(Event $Event, $data = null) {}

/**
 * parse extensions
 *
 * This will allow your plugin to register extensions with parseExtensions
 *
 * @return array
 */
	public function onSetupExtensions(Event $Event) {}

/**
 * called when editing a row is canceled
 *
 * If id is null it is because it was a new row not something being updated
 *
 * @param Event $event the event being called
 * @param string $id
 */
	public function onEditCanceled(Event $Event, $id) {}

/**
 *
 */
	public function onAttachBehaviors(Event $Event) {}

	#public function onSetupThemeStart($event, $data = null) {}

	#public function onSetupThemeSelector($event, $data = null) {}

	#public function onSetupThemeEnd($event, $data = null) {}

	#public function onFindBrowser($event, $data = null) {}

	#public function onFindOperatingSystem($event, $data = null) {}

	#public function onSetupThemeLayout($event, $data = null) {}

/**
 * Triggered when a user logs into the site
 *
 * @param Event $Event
 * @param array $user
 */
	public function onUserLogin(Event $Event, array $user) {}

/**
 * Triggered when a user registers on the site
 *
 * @param Event $Event
 * @param array $user
 */
	public function onUserRegistration(Event $Event, array $user) {}

	#public function onUserActivation($event, $data = null) {}

/**
 * Require Global templates used in page rendering.
 *
 * This is called just before a page is rendered by the InfinitasView
 * and takes an array of items to add. Its used for adding global pieces
 * to your template.
 *
 * if you pass back array('phone' => '012 345 6789') from foo plugin
 * you can use {{templates.foo.phone}} in your views to show your phone
 * number.
 */
	public function onRequireGlobalTemplates(Event $Event) {}

/**
 * used to build the menus for the admin pages
 */
	public function onAdminMenu(Event $Event) {}

/**
 * Load helpers.
 *
 * This event provides a way for you to attach your helper to the entire
 * application without needin to modify AppController.php
 *
 * Return should be the same as what is used in AppController::$helpers
 *
 * @see AppController::beforeRender()
 *
 * @param Event $Event The event that was triggered
 *
 * @return array
 */
	public function onRequireHelpersToLoad(Event $Event) {
		return array();
	}

/**
 * Get js files to include
 *
 * Allows you to include javascript from your plugin that can be loaded
 * on any page
 *
 * called in AppController::beforeRender()
 *
 * @param $event some data for the current event
 * @return mixed string | array() of javascript like HtmlHelper::script() takes
 */
	public function onRequireJavascriptToLoad(Event $Event) {}

/**
 * Get vcss files to include
 *
 * Allows you to include css from your plugin that can be loaded
 * on any page
 *
 * called in AppController::beforeRender()
 *
 * @param $event some data for the current event
 * @return mixed string | array() of css like HtmlHelper::css() takes
 */
	public function onRequireCssToLoad(Event $Event) {}

/**
 * Load components
 *
 * Allows you to include components into the app that can be accessed
 * globaly.
 *
 * called before AppController::__construct()
 *
 * @param $event some data for the current event
 * @return mixed string | array() of css like HtmlHelper::css() takes
 */
	public function onRequireComponentsToLoad(Event $Event) {
		return array();
	}

/**
 * Slug urls.
 *
 * Use this method to figure out what vars are needed for the
 * controller / action pair you need to get to. You  will get some data,
 * normally in the form of Model::find(first) that you can use.
 *
 */
	public function onSlugUrl(Event $Event, $data = null, $type = null) {
		if (empty($data) || $type == null) {
			return false;
		}

		$plugin = str_replace('Events', '', get_class($this));
		$data = Set::flatten($data);

		$urlConfig = Configure::read($plugin . '.slugUrl.' . $type);
		if (!$urlConfig) {
			throw new Exception(sprintf('Configuration for url types "%s.%s" not found', $plugin, $type));
		}

		$url = !empty($urlConfig['url']) ? $urlConfig['url'] : array();
		unset($urlConfig['url']);

		$url['plugin'] = Inflector::underscore($plugin);

		$urlParams = array();
		foreach ($urlConfig as $key => $value) {
			if (!empty($data[$key])) {
				$urlParams[$value] = $data[$key];
			}
		}

		return InfinitasRouter::bestMatch($url, $urlParams);
	}

/**
 * Todo list
 *
 * Gives plugins a chance to do some checks and generate a todo list for
 * the admin page. This can be anything like warnings about missing
 * dependancies or configs, new records like pending comments etc.
 *
 * @code
 *	// format should be
 *	$return[0]['name'] = 'something';
 *	$return[0]['type'] = 'warning|error';
 *	$return[0]['url']  = array();
 * @endcode
 */
	public function onRequireTodoList(Event $Event) {}

/**
 * Last event to fire.
 *
 * Called after everything is done and finished. should not really be used
 * for output unless in debug mode.
 */
	public function onRequestDone(Event $Event) {}

/**
 * Called when the system crons are being run
 *
 * Use this method to do maintainence to you plugin like clearing logs,
 * populating db data or what ever. Normally the system is set to run
 * often so dont always run the job
 *
 * @param object $event the event object (this is normally the shell)
 */
	public function onRunCrons(Event $Event) {
		return false;
	}

/**
 * get required fixtures
 *
 * @return array
 */
	public function onGetRequiredFixtures(Event $Event) {}
}