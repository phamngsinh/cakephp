<?php
	App::import(
		array(
			'type' => 'File',
			'name' => 'Google.GoogleConfig',
			'file' => 'config' . DS . 'setup.php'
		)
	);

	/**
	 * Google App Controller class file.
	 *
	 * the google_appcontroller file, extends AppController
	 *
	 * Copyright (c) 2009 Carl Sutton ( dogmatic69 )
	 *
	 *
	 *
	 *
	 * @filesource
	 * @copyright Copyright (c) 2009 Carl Sutton ( dogmatic69 )
	 * @link http://infinitas-cms.org
	 * @package Infinitas.Google.Controller
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 */
	class GoogleAppController extends AppController {
		public function beforeFilter() {
			parent::beforeFilter();
			$GoogleConfig = new GoogleConfig();
			return true;
		}
	}