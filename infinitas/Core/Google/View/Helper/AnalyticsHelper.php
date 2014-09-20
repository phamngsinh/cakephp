<?php
	App::import(
		array(
			'type' => 'File',
			'name' => 'Google.GoogleConfig',
			'file' => 'config' . DS . 'setup.php'
		)
	);

	/**
	 * Google Analytics Helper class file.
	 *
	 * A helper for Analytics.
	 *
	 * atm it is just used for the tracking code, but when the code for
	 * getting stats from analytics is done there will be more here.
	 *
	 * Copyright (c) 2009 Carl Sutton ( dogmatic69 )
	 *
	 *
	 *
	 *
	 * @filesource
	 * @copyright Copyright (c) 2009 Carl Sutton ( dogmatic69 )
	 * @link http://infinitas-cms.org
	 * @package Infinitas.Google.Helper
	 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
	 */
	class AnalyticsHelper extends AppHelper {
		protected $_trackingInstalled = false;

		public $login = array(
			'uri' => array(
				'scheme' => 'https',
				'host' => 'www.google.com',
				'path' => '/accounts/ClientLogin',
				'method' => 'POST'
			),
			'auth' => array(
				'method' => 'Basic',
				'email' => 'infinit8s@gmail.com',
				'password' => 'infinitas123',
				'profile' => '25571140'
			)
		);

		public $setup = array(
			'uri' => array(
				'scheme' => 'https',
				'host' => 'www.google.com',
				'path' => '/analytics/feeds/data?',
				'method' => 'POST'
			),
			'auth' => array(
				'method' => 'Basic',
				'email' => 'infinit8s@gmail.com',
				'password' => 'infinitas123',
				'profile' => '25571140'
			)
		);

		/**
		 * get the settings for analytics.
		 */
		public function __construct() {
			if (!Configure::read('Google')) {
				$GoogleConfig = new GoogleConfig();
			}

			return true;
		}

		/**
		 * generate tracking code.
		 *
		 * generates the tracking code for your site based on the config in
		 * Configure::read( 'Google.Analytics' ); this will load automaticaly from
		 * the config file in /google/config/setup.php
		 *
		 * @param string $params stuff you would normaly put in pageTracker._trackPageview()
		 * @return the script generated.
		 */
		public function tracker($params = '') {
			// already run.
			if ($this->_trackingInstalled) {
				return true;
			}

			$out = '<script type="text/javascript"><!--' . "\n";
			$out .= "\t" . 'var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");' . "\n";
			$out .= "\t" . 'document.write(unescape("%3Cscript src=\'" + gaJsHost + ' .
				'"google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));' . "\n";
			$out .= "\t" . '$(document).ready(function() {' . "\n";
			$out .= "\t" . 'var pageTracker = _gat._getTracker("UA-' . Configure::read('Google.Analytics.profile_id') . '");' . "\n";
			$out .= "\t" . 'pageTracker._trackPageview( ' . $params . ' );' . "\n";
			$out .= "\t" . '});' . "\n";
			$out .= '--></script>' . "\n";

			$this->_trackingInstalled = true;

			return $out;
		}

		public function chart() {
			$this->__sendRequest();
		}

		private function __sendRequest() {
			App::import('Core', 'HttpSocket');
			$this->HttpSocket = new HttpSocket();

			$request = $this->setup;

			$request['uri']['path'] .=
					'dimensions=ga:pagePath&' .
					'metrics=ga:pageviews';

			pr($this->HttpSocket->request($request));
			exit;

			return $this->HttpSocket->request($request);
		}
	}