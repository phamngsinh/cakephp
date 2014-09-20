<?php
/**
 * Security component
 *
 * @package Infinitas.Security.Controller.Component
 */

App::uses('InfinitasComponent', 'Libs.Controller/Component');

/**
 * Security component
 *
 * Component for dealing with security within Infinitas
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Security.Controller.Component
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.9a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */
class InfinitasSecurityComponent extends InfinitasComponent {
/**
 * Initialise the component
 *
 * @param Controller $Controller
 *
 * @return void
 */
	public function initialize(Controller $Controller) {
		parent::initialize($Controller);

		$admin = array_key_exists('admin', $Controller->request->params) && $Controller->request->params['admin'];
		$groupId = AuthComponent::user('group_id');
		if ($admin && $groupId && $groupId != 1) {
			$Controller->Session->delete('Auth');
			$Controller->notice(__d('security', 'Administrator access required'), array(
				'redirect' => array(
					'plugin' => 'users',
					'controller' => 'users',
					'action' => 'login'
				),
				'level' => 'warning'
			));
		}

		$this->_checkBadLogins();
		$this->_blockByIp();

		$this->_setupAuth();
		$this->_setupSecurity();
	}

/**
 * look for bots fillilng out honey traps and stop them from being a pain
 *
 * Bots are redirected to /?spam=true in which case you can use your web
 * server to block them or redirect them to another site.
 *
 * should look into this http://www.projecthoneypot.org/httpbl_implementations.php
 *
 * @return void
 */
	protected function _detectBot() {
		if (!empty($this->Controller->request->data[$this->Controller->modelClass]['om_nom_nom'])) {
			$this->Controller->Session->write('Spam.bot', true);
			$this->Controller->Session->write('Spam.detected', time());

			$this->redirect('/?spam=true');
		}

		if ($this->Controller->Session->read('Spam.bot')) {
			if ((time() - 3600) > $this->Controller->Session->read('Spam.detected')) {
				$this->Controller->Session->write('Spam', null);
			}
		}
	}

/**
 * Set up Auth.
 *
 * Define some things that auth needs to work
 *
 * @return void
 */
	protected function _setupAuth() {
		$this->Controller->Auth->allow('display');

		if (!isset($this->Controller->request->params['prefix']) || $this->Controller->request->params['prefix'] != 'admin') {
			$this->Controller->Auth->allow();
		}

		$this->Controller->Auth->loginAction = array(
			'plugin' => 'users',
			'controller' => 'users',
			'action' => 'login'
		);

		if (Configure::read('Website.login_type') == 'email') {
			$this->Controller->fields = array('username' => 'email', 'password' => 'password');
		}

		$this->Controller->Auth->loginRedirect = '/';

		if (isset($this->Controller->params['prefix']) && $this->Controller->params['prefix'] == 'admin') {
			$this->Controller->Auth->loginRedirect = '/admin';
		}

		$this->Controller->Auth->logoutRedirect = '/';
		$this->Controller->Auth->userModel = 'Users.User';

		$this->Controller->Auth->userScope = array('User.active' => 1);
	}

/**
 * Configure security settings
 *
 * @return void
 */
	protected function _setupSecurity() {
		$this->Controller->Security->blackHoleCallback = 'blackHole';
		$this->Controller->Security->validatePost = false;
	}

/**
 * Stop blocked ip addresses from accessing the site.
 *
 * Will get a list of ip addresses that are saved to be blocked and
 * if the user matches that address they will be black holed.
 *
 * If the user is allowed it is saved to their session so that the test
 * is not done on every request.
 *
 * @return boolean
 *
 * @throws SecurityIpAddressBlockedException
 */
	protected function _blockByIp() {
		$currentIp = $this->Controller->request->clientIp();

		if (ClassRegistry::init('Security.IpAddress')->getBlockedIpAddresses($currentIp)) {
			$this->blockAccess();
		}

		$this->Controller->Session->write('Infinitas.Security.ip_checked', true);

		return true;
	}

/**
 * Record bad logins.
 *
 * This will record each time a user tries to log in with the incorect
 * username / password combination.
 *
 * @param array $data the username and password form the login atempt.
 *
 * @return boolean
 */
	public function badLoginAttempt($data) {
		$old = (array)$this->Controller->Session->read('Infinitas.Security.loginAttempts');
		$old[] = $data;
		$this->Controller->Session->write('Infinitas.Security.loginAttempts', $old);
		$this->Controller->Session->delete('Infinitas.Security.ip_checked');
		return true;
	}

/**
 * Check the bad logins.
 *
 * If the bad logins are more than the system allows the user will be band.
 *
 * @return true or blackHole;
 */
	protected function _checkBadLogins() {
		if ($this->Controller->Auth->user('id') || empty($this->Controller->request->data)) {
			return true;
		}
		$field = null;
		if (!empty($this->Controller->request->data['User']['username'])) {
			$field = $this->Controller->request->data['User']['username'];
		} else if (!empty($this->Controller->request->data['User']['email'])) {
			$field = $this->Controller->request->data['User']['email'];
		}

		if (!$field) {
			return false;
		}

		$old = $this->Controller->Session->read('Infinitas.Security.loginAttempts');
		if (count($old) > 0) {
			$this->risk = ClassRegistry::init('Security.IpAddress')->findSimmilarAttempts(
				$this->Controller->RequestHandler->getClientIp(),
				$field
			);
		}

		if (count($old) >= Configure::read('Security.login_attempts')) {
			ClassRegistry::init('Security.IpAddress')->blockIp(
				$this->Controller->request->clientIp(),
				$this->Controller->Session->read('Infinitas.Security.loginAttempts'),
				$this->risk
			);
			$this->Controller->Session->destroy();

			$this->blockAccess();
		}

		return true;
	}

/**
 * Block a user from accessing the site
 *
 * @throws SecurityIpAddressBlockedException
 */
	public function blockAccess() {
		throw new SecurityIpAddressBlockedException(array(
			$this->Controller->request->clientIp()
		));
	}

}