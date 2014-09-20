<?php
/**
 * ThemesComponent
 *
 * @package Infinitas.Themes.Controller.Component
 */

App::uses('InfinitasComponent', 'Libs.Controller/Component');

/**
 * ThemesComponent
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Themes.Controller.Component
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class ThemesComponent extends InfinitasComponent {
/**
 * BeforeRender callback
 *
 * Figure out what theme is being used for the current page
 *
 * @param Controller $Controller
 *
 * @return boolean
 */
	public function beforeRender(Controller $Controller) {
		parent::beforeRender($Controller);

		if ($Controller->layout == 'ajax') {
			return;
		}

		if (!empty($Controller->viewVars['error']) && $Controller->viewVars['error'] instanceof Exception) {
			$error = $Controller->viewVars['error'];
			unset($Controller->viewVars['error']);
		}

		$layout = array_values($Controller->viewVars);
		$theme = current(Set::extract('/Layout/theme_id', $layout));
		$tmp = current(Set::extract('/Layout/layout', $layout));
		if (empty($tmp)) {
			$tmp = current(Set::extract('/GlobalLayout/layout', $layout));
		}
		$layout = $tmp;

		if (!empty($error)) {
			$Controller->viewVars['error'] = $error;
			$layout = 'error';
		}

		if ($layout) {
			Configure::write('Themes.default_layout', $layout);
		}

		$event = $Controller->Event->trigger($Controller->plugin . '.setupThemeStart');

		if (isset($event['setupThemeStart'][$Controller->plugin]) && $event['setupThemeStart'][$Controller->plugin] === false) {
			return;
		}
		if (!empty($event['setupThemeStart'][$Controller->plugin]['theme'])) {
			$Controller->theme = $event['setupThemeStart'][$Controller->plugin]['theme'];
			if (!empty($event['setupThemeStart'][$Controller->plugin]['layout'])) {
				$Controller->layout = $event['setupThemeStart'][$Controller->plugin]['layout'];
			}
			return true;
		}

		$Controller->layout = Configure::read('Themes.default_layout');
		$theme = Cache::read('currentTheme');
		if ($theme === false) {
			$admin = isset($Controller->request->params['admin']) && $Controller->request->params['admin'];
			$theme = ClassRegistry::init('Themes.Theme')->find('currentTheme', array(
				'admin' => $admin
			));
		}

		if (!empty($theme['Theme']['default_layout'])) {
			$Controller->layout = $theme['Theme']['default_layout'];
		}

		if (isset($Controller->request->params['admin']) && $Controller->request->params['admin']) {
			$Controller->layout = Configure::read('Themes.default_layout_admin');
		}

		$event = $Controller->Event->trigger(
			$Controller->plugin . '.setupThemeLayout',
			array(
				'layout' => $Controller->layout,
				'params' => $Controller->request->params
			)
		);

		if (isset($event['setupThemeLayout'][$Controller->plugin]) && is_string($event['setupThemeLayout'][$Controller->plugin])) {
			$Controller->layout = $event['setupThemeLayout'][$Controller->plugin];
		}

		if (!isset($theme['Theme']['name'])) {
			$theme['Theme'] = array('name' => null);
		} else {
			$event = $Controller->Event->trigger($Controller->plugin . '.setupThemeSelector', array(
				'theme' => $theme['Theme'],
				'params' => $Controller->request->params
			));

			if (isset($event['setupThemeSelector'][$Controller->plugin]) && is_array($event['setupThemeSelector'][$Controller->plugin])) {
				$theme['Theme'] = $event['setupThemeSelector'][$Controller->plugin];
				if (!isset($theme['Theme']['name'])) {
					$this->cakeError('eventError', array('message' => 'The theme is invalid.', 'event' => $event));
				}
			}
		}

		$Controller->theme = $theme['Theme']['name'];
		Configure::write('Theme', $theme['Theme']);

		$event = $Controller->Event->trigger($Controller->plugin.'.setupThemeRoutes', array('params' => $Controller->request->params));
		if (isset($event['setupThemeRoutes'][$Controller->plugin]) && !$event['setupThemeRoutes'][$Controller->plugin]) {
			return false;
		}

		if (empty($routes)) {
			$routes = Classregistry::init('Routes.Route')->getRoutes();
		}

		$currentRoute = Router::currentRoute(Configure::read('CORE.current_route'));
		if (!empty($routes) && is_object($currentRoute)) {
			foreach ($routes as $route) {
				if ($route['Route']['url'] == $currentRoute->template) {
					if (!empty($route['Route']['theme'])) {
						$Controller->theme = $route['Route']['theme'];
					}

					if (!empty($route['Route']['layout'])) {
						$Controller->layout = $route['Route']['layout'];
					}
				}
			}
		}

		$event = $Controller->Event->trigger($Controller->plugin.'.setupThemeEnd', array('theme' => $Controller->theme, 'params' => $Controller->request->params));
		if (isset($event['setupThemeEnd'][$Controller->plugin])) {
			if (is_string($event['setupThemeEnd'][$Controller->plugin])) {
				$Controller->theme = $event['setupThemeEnd'][$Controller->plugin];
			}
		}

		return true;
	}

/**
 * Json method for getting available theme layouts
 *
 * This method is a 'controller action' that is available to every controller.
 *
 * @return void
 */
	public function actionAdminGetThemeLayouts() {
		if (empty($this->Controller->request->data[$this->Controller->modelClass]['theme_id'])) {
			$this->Controller->set('json', false);
		}

		$this->Controller->set('json', InfinitasTheme::layouts($this->Controller->request->data[$this->Controller->modelClass]['theme_id']));
	}

}