<?php
	/**
	 * Google Contacts Datasource
	 *
	 * Simplifies managing contacts with the google contacts api.
	 *
	 * Copyright (c) 2009 Juan Carlos del Valle ( imekinox )
	 *
	 *
	 *
	 *
	 * @copyright	 Copyright (c) 2009 Juan Carlos del Valle ( imekinox )
	 * @link http://www.imekinox.com
	 * @package Infinitas.Google.Model.Datasource
	 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	 */
	App::import('Lib', 'GoogleApiContacts');

	/**
	 * GoogleContactsSource
	 *
	 * Datasource for Google Contacts
	 */
	class GoogleContactsSource extends DataSource {

		/**
		 * Version for this Data Source.
		 *
		 * @public string
		 */
		public $version = '0.1';
		/**
		 * Description string for this Data Source.
		 *
		 * @public string
		 */
		public $description = 'GoogleContacts Datasource';
		/**
		 * Google api base class
		 *
		 * @public Object
		 */
		public $GoogleApiBase;
		/**
		 * Google Contacts custom schema
		 *
		 * @public Array
		 */
		protected $_schema;
		/**
		 * Url to request contacts
		 *
		 * @public String
		 */
		public $readUri = "http://www.google.com/m8/feeds/contacts/default/full";

		/**
		 * Default Constructor
		 *
		 * @param array $config options
		 */
		function __construct($config) {
			//Select contacts service for login token
			$this->GoogleApiContacts = new GoogleApiContacts($config);
			$this->_schema = $this->GoogleApiContacts->getSchema();
			parent::__construct($config);
		}

		/**
		 * Read method for find calls
		 *
		 * @param object $model
		 * @param array $queryData
		 */
		function read($model, $queryData = array()) {
			if (isset($queryData['conditions']['id'])) {
				return $this->findById($queryData['conditions']['id']);
			}

			else {
				$args['max-results'] = ($queryData['limit'] != null) ? $queryData['limit'] : '25';

				//Sorting order direction. Can be either ascending or descending.
				if (isset($queryData['order'][0]) && $queryData['order'][0] != NULL) {
					//If no order is specified (ascending || descending) google will set default ordering criteria
					$args['sortorder'] = $queryData['order'][0];
				}

				if (isset($queryData['conditions'])) {
					foreach ($queryData['conditions'] AS $key => $value) {
						$args[$key] = $value;
					}
				}

				if (isset($queryData['conditions']['query'])) {
					$args['q'] = $queryData['conditions']['query'];
				}

				$query = $this->readUri . "?" . http_build_query($args, "", "&");
				$result = $this->GoogleApiContacts->sendRequest($query, "READ");
				if (isset($queryData['fields']['COUNT']) && $queryData['fields']['COUNT'] == 1) {
					$count[0][0] = array('count' => count($result['Feed']['Entry']));
					return $count;
				}

				else {
					if ($queryData['limit'] == 1) {
						$tmp[0] = $result['Feed']['Entry'];
					}

					else {
						$tmp = $result['Feed']['Entry'];
					}

					return $tmp;
				}
			}
		}

		/**
		 * Create method for model
		 *
		 * @param object $model
		 * @param array $fields
		 * @param array $values
		 */
		function create($model, $fields = array(), $values = array()) {
			$baseObject = $model->data['GoogleContacts'];
			debug($baseObject);
			// $atom = $this->GoogleApiContacts->toAtom($baseObject);
			// return $this->GoogleApiContacts->sendRequest($this->readUri, "CREATE", $atom);
		}

		/**
		 * Update method for model
		 *
		 * @param object $model
		 * @param array $fields
		 * @param array $values
		 */
		function update($model, $fields = array(), $values = array()) {
			$baseObject = $model->data['GoogleContacts'];
			$atom = $this->GoogleApiContacts->toAtom($baseObject);
			$query = $baseObject['Link'][1]['href'];
			return $this->GoogleApiContacts->sendRequest($query, "UPDATE", $atom);
		}

		/**
		 * Delete method for model
		 *
		 * @param object $model
		 * @param string $id
		 */
		function delete($model, $id = null) {
			debug("delete");
		}

		/**
		 * Calculate some specific prameters to find like count before calling read
		 *
		 * @param object $model
		 * @param string $func
		 * @param array $params
		 */
		public function calculate(&$model, $func, $params = array()) {
			$params = (array) $params;
			switch (strtolower($func)) {
				case 'count':
					return array('COUNT' => true);
					break;
				case 'max':
					break;
				case 'min':
					break;
			}
		}

		/**
		 * Handle specific query's
		 *
		 * @param array $query
		 * @param array $params
		 * @param object $model
		 */
		public function query($query, $params, $model) {
			switch ($query) {
				case "findById":
					$result = $this->GoogleApiContacts->sendRequest($params[0], "READ");
					return $result['Entry'];
					break;
			}
		}

		public function listSources() {
			return array('google_contacts');
		}

		public function describe($model) {
			return $this->_schema['google_contacts'];
		}

		/**
		 * @todo public function insertQueryData($query, $data, $association, $assocData, $model, $linkModel, $stack) { debug("iq"); }
		 * @todo public function resolveKey( $model, $key ) { debug("key"); }
		 * @todo public function rollback( $model ) { debug("rollback"); }
		 * @todo public function sources( $reset = false ) {}
		 * @todo public function column( $real ) {}
		 * @todo public function commit( $model ) { debug("commit"); }
		 * @todo public function begin( $model ) { debug("begin"); }
		 * @todo public function __cacheDescription( $object, $data = NULL ) {}
		 * @todo public function __destruct() {}
		 * @todo public function isInterfaceSupported( $interface ) { debug("interface"); }
		 * @todo public function lastAffected( $source = NULL ) {}
		 * @todo public function lastInsertId( $source = NULL ) {}
		 * @todo public function lastNumRows( $source = NULL ) {}
		 * @todo public function cakeError( $method, $messages = array ( ) ) { debug("Error"); }
		 * @todo public function dispatchMethod( $method, $params = array ( ) ) { debug("method" . $method); }
		 */
	}