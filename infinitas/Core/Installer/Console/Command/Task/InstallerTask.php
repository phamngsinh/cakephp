<?php
App::uses('InstallerLib', 'Installer.Lib');
App::uses('MigrationInformation', 'Migrations.Lib');

/**
 * InstallShell
 *
 * @param InstallerTask $Installer
 * @param InfinitasPluginTask $InfinitasPlugin
 * @param InstallerLib $InstallerLib
 *
 * @author dakota
 */

class InstallerTask extends AppShell {

/**
 * tasks used for the installer
 *
 * @var array
 */
	public $tasks = array(
		'Installer.Installer',
		'Installer.InfinitasPlugin'
	);

/**
 * install config defaults
 *
 * @var array
 */
	public $config = array(
		'engine' => '',
		'connection' => array(
			'host' => 'localhost',
			'login' => 'infinitas',
			'password' => 'infinitas',
			'database' => 'infinitas',
			'port' => 3306,
			'prefix' => ''
		),
		'root' => array(
			'login' => 'root',
			'password' => 'root',
		)
	);

/**
 * Constructor
 *
 * @return void
 */
	public function __construct($stdout = null, $stderr = null, $stdin = null) {
		parent::__construct($stdout, $stderr, $stdin);

		$this->InstallerLib = new InstallerLib();
	}

/**
 * Installer welcome
 * 
 * display the licence. If you need it confirmed it will display and require
 * the user to accept. if they do not it will exit.
 *
 * @param bool $confirm should the user confirm
 * @return mixed bool true, accepted
 */
	public function welcome($confirm = true) {
		$this->h1('Welcome to Infinitas');
		$this->out($this->InstallerLib->getWelcome('text'));
		$this->h2('MIT Licence');
		$this->out($this->InstallerLib->getLicense('text'));

		if ($confirm) {
			$this->li(array(
				'[Y]es',
				'[N]o',
				'[Q]uit'
			));
			$this->br();
			$input = strtoupper($this->in('Do you accept the MIT license?', array('Y', 'N', 'Q')));

			switch ($input) {
				case 'Y':
					return true;
					break;

				default:
					$this->quit();
					break;
			}
		}

	}

/**
 * Database connection checks
 * 
 * collect database related configurations and validate them so the installer
 * can later
 *
 * @param boolean $validationFailed flag if validation failed or not
 *
 * @return void
 */
	public function database($validationFailed = false) {
		$this->_getDbEngine($validationFailed);
		$this->_getDbConnection($validationFailed);
		$this->_validateDbConnection();
	}

/**
 * Run the installer
 *
 * @return void
 */
	public function install() {
		$this->h1(__d('insatller', 'Installing'));
		foreach ($this->config['connection'] as $k => $v) {
			echo $k . ' :: ' . $v . "\r\n";
		}

		$this->_getSampleDataOption();

		App::import('Core', 'ConnectionManager');

		$dbConfig = $this->InstallerLib->cleanConnectionDetails($this->config);
		$this->InstallerLib->config = $this->config;

		$db = ConnectionManager::create('default', $dbConfig);

		$plugins = App::objects('plugin');
		natsort($plugins);

		App::import('Lib', 'Installer.ReleaseVersion');
		$Version = new ReleaseVersion();

		//Install app tables first
		$this->interactive('Installing: App data');
		$result['app'] = $this->InstallerLib->installPlugin($Version, $dbConfig, 'app');

		$this->interactive('Installing: Installer');
		$result['Installer'] = $this->InstallerLib->installPlugin($Version, $dbConfig, 'Installer');

		//Then install all other plugins
		foreach ($plugins as $plugin) {
			if ($plugin == 'Installer') {
				continue;
			}

			$this->interactive(sprintf('Installing: %s', $plugin));
			$result[$plugin] = $this->InstallerLib->installPlugin($Version, $dbConfig, $plugin);
		}

		$this->interactiveClear();

		$this->Plugin = ClassRegistry::init('Installer.Plugin');
		foreach ($plugins as $pluginName) {
			$this->interactive(sprintf('Updating: %s', $plugin));
			$this->Plugin->installPlugin($pluginName, array('sampleData' => false, 'installRelease' => false));
		}

		$this->interactiveClear();


		$this->InstallerLib->writeDbConfig(array('Install' => $this->config['connection']));

		return $result;
	}

/**
 * Get the admin users details and save them
 * 
 * @return boolean
 */
	public function admin_user() {
		$user = array();
		$user['email'] = $this->in(__d('installer', 'Email'));
		$user['group_id'] = 1;
		$user['username'] = $this->in(__d('installer', 'Username'), null, 'admin');
		$user['password'] = $this->in(__d('installer', 'Password'), null, 'Admin123');

		return (bool)$this->InstallerLib->createUser($user);
	}

/**
 * Install local plugins
 * 
 * @return void
 */
	public function installLocalPlugin() {
		$plugins = $this->__getPluginToInstall();
		if (!$plugins) {
			return false;
		}

		if (!is_array($plugins)) {
			$plugins = array($plugins);
		}

		$Plugin = ClassRegistry::init('Installer.Plugin');

		foreach ($plugins as $plugin) {
			try {
				$Plugin->installPlugin($plugin, array('sampleData' => false, 'installRelease' => false));
				$output = sprintf('%s Plugin updated', $plugin);
			} catch(Exception $e) {
				$output = sprintf('Update for %s has failed (%s)', $plugin, $e->getMessage());
			}

			$this->out($output);
		}

		$this->pause();
	}

/**
 * Update a plugin
 * 
 * @return void
 */
	public function updatePlugin() {
		$plugins = $this->__getPluginToUpdate();
		if (!$plugins) {
			return false;
		}

		if (!is_array($plugins)) {
			$plugins = array($plugins);
		}

		$Plugin = ClassRegistry::init('Installer.Plugin');

		foreach ($plugins as $plugin) {
			try{
				$Plugin->installPlugin($plugin);
				$output = sprintf('%s Plugin updated', $plugin);
			} catch(Exception $e) {
				$this->out(sprintf('Update for %s has failed (%s)', $plugin, $e->getMessage()));
				$e->getTrace();
				continue;
			}

			$this->out($output);
		}

		$this->pause();
		$this->updatePlugin();
	}


/**
 * get the users database engine preference
 * 
 * @param boolean $validationFailed flag if validation failed or not
 * @return void
 */
	public function _getDbEngine($validationFailed) {
		$this->h1(__d('insatller', 'Database configuration'));

		if ($validationFailed) {
			$this->p(__d('insatller', 'The connection test failed to connect to '.
			'your database engine, please ensure the details provided are '.
			'correct', true));
		}

		$dbs = $this->InstallerLib->getSupportedDbs();

		$this->br();
		$this->config['connection']['datasource'] = $this->in(
			'Which database engine should be used?',
			array_keys($dbs),
			current(array_keys($dbs))
		);
	}

/**
 * get the connection details for the selected database engine
 * 
 * @param boolean $validationFailed flag if validation failed or not
 * @return boolean
 */
	public function _getDbConnection($validationFailed) {
		$this->h1(sprintf('%s (%s)', __d('insatller', 'Database configuration'), $this->config['connection']['datasource']));

		if ($validationFailed) {
			$this->p(__d('insatller', 'The connection test failed to connect to '.
			'your database engine, please ensure the details provided are '.
			'correct', true));
		}

		$this->config['connection']['host']     = $this->in('HostName', null, $this->config['connection']['host']);
		$this->config['connection']['login']    = $this->in('Username', null, $this->config['connection']['login']);
		$this->config['connection']['password'] = $this->in('Password', null, $this->config['connection']['password']);
		$this->config['connection']['database'] = $this->in('Database', null, $this->config['connection']['database']);
		$this->config['connection']['prefix']   = $this->in('Prefix', null, $this->config['connection']['prefix']);

		$this->br();
		$this->out('Would you like to use a root pw for the installer');
		$this->out('Root logins will not be saved');
		$this->out('[Y]es, [N]o or [B]ack');
		$input = strtoupper($this->in('Use Root password', array('Y', 'N', 'B'), 'N'));

		$databaseEngine = null;
		switch ($input) {
			case 'Y':
				$this->config['root']['login']    = $this->in('Root Username', null, $this->config['root']['login']);
				$this->config['root']['password'] = $this->in('Root Password', null, $this->config['root']['password']);
				break;

			case 'Q':
				$this->welcome();
				break;

			default:
				// reset defaults
				$this->config['root'] = array('username' => '', 'password' => '');
				break;
		}

		return true;
	}

/**
 * check that the details for the database given are correct.
 *
 * @return void
 */
	public function _validateDbConnection() {
		$this->h1(sprintf(__d('insatller', 'Testing %s connection'), $this->config['connection']['datasource']));
		if (!$this->InstallerLib->testConnection($this->config['connection'])) {
			$this->database(false);
		}
	}

/**
 * Get the option for sample data
 *
 * @return void
 */
	public function _getSampleDataOption() {
		$this->out('Would you like to install sample data');
		$this->out('[Y]es, [N]o or [B]ack');
		$input = strtoupper($this->in('Sample Data', array('Y', 'N', 'B'), 'N'));

		$this->config['sample_data'] = false;
		switch ($input) {
			case 'Y':
				$this->config['sample_data'] = true;
				break;

			case 'B':
				$this->welcome();
				break;
		}
	}

/**
 * Get plugins that require updates
 *
 * @return void
 */
	private function __getPluginToUpdate() {
		$Plugin = ClassRegistry::init('Installer.Plugin');
		$plugins = array();
		foreach ($Plugin->getInstalledPlugins() as $plugin) {
			$status = MigrationInformation::status($plugin);

			if ($status['migrations_behind']) {
				$plugins[] = $plugin;
			}
		}

		do {
			$this->h1('Interactive Install Shell');
			foreach ($plugins as $i => $plugin) {
				$this->out($i + 1 . ') ' . $plugin);
			}
			$this->out('A)ll');

			$this->br();
			$input = strtoupper($this->in('Which plugin do you want to update?'));

			if (isset($plugins[$input - 1])) {
				return $plugins[$input - 1];
			}

			if ($input == 'A') {
				return $plugins;
			}
		} while($input != 'Q');
	}

/**
 * get plugins that require installation
 *
 * @return void
 */
	private function __getPluginToInstall() {
		$plugins = ClassRegistry::init('Installer.Plugin')->getNonInstalledPlugins();
		sort($plugins);

		do {
			$this->h1('Interactive Install Shell');
			foreach ($plugins as $i => $plugin) {
				$this->out($i + 1 . ') ' . $plugin);
			}
			$this->out('A)ll');

			$this->br();
			$input = strtoupper($this->in('Which plugin do you want to install?'));

			if (isset($plugins[$input - 1])) {
				return $plugins[$input - 1];
			}

			if ($input == 'A') {
				return $plugins;
			}
		} while($input != 'Q');
	}

}