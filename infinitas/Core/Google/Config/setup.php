<?php
	/**
	 * Google config class file.
	 *
	 * setup the google helper.
	 *
	 * Copyright (c) 2009 Carl Sutton ( dogmatic69 )
	 *
	 *
	 *
	 *
	 * @filesource
	 * @copyright Copyright (c) 2009 Carl Sutton ( dogmatic69 )
	 * @link http://infinitas-cms.org
	 * @package Infinitas.Google.Config
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 */
	class GoogleConfig {
		/**
		 * Current version: http://github.com/dogmatic/cakephp_google_plugin
		 *
		 * @var string
		 */
		public $version = '0.1';

		/**
		 * Settings
		 *
		 * @var array
		 */
		public $settings = array();

		/**
		 * Singleton Instance
		 *
		 * @var GoogleConfig
		 */
		private $__instance;

		/**
		 *
		 * @return void
		 */
		public function __construct() {
			$analytics = array(
				'profile_id' => 'xxxxxxxx-x'
			);

			Configure::write('Google.Analytics', $analytics);
		}
	}