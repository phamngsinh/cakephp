<?php
/**
 * EncryptableBehavior
 *
 * @package Infinitas.Security.Model.Behavior
 */

/**
 * EncryptableBehavior
 *
 * Behavior for encryption / decription of data automatically
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Security.Model.Behavior
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.9a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */
class EncryptableBehavior extends ModelBehavior {
/**
 * Contain settings indexed by model name.
 *
 * @var array
 */
	public $settings = array();

/**
 * Default options
 *
 * some default options of the behavior, you can pass this in the setup
 * to change the way it works.
 *
 * any fields that are encrypted can be serached with plain text (to a
 * degree) by adding a field called `<name>_search` where <name> is the name
 * of the encrypted field
 *
 * This search field will be stripped down to a very bare version for basic
 * LIKE searches. If the encrypted data was less than `searchWordMinLenght`
 * long it will be balank so that it does not give away to much.
 *
 * all words that are shorter than `searchWordLength` will be removed. the
 * final search field will be mixed up and then saved so that it can be later
 * searched.
 *
 * @var array
 */
	protected $_defaults = array(
		'fields' => array(),
		'searchWordLength' => 4,
		'searchWordMinLenght' => 50
	);

/**
 * Initiate behaviour for the model using settings.
 *
 * @param object $Model Model using the behaviour
 * @param array $settings Settings to override for model.
 *
 * @return void
 */
	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = array_merge($this->_defaults, $settings);

		if (empty($this->settings[$Model->alias]['fields']) && is_array($this->settings[$Model->alias]['fields'])) {
			foreach ($Model->schema() as $key => $value) {
				if ($value['type'] == 'text') {
					$this->settings[$Model->alias]['fields'][] = $key;
				}
			}
		}
	}

/**
 * Generate a secrect key
 *
 * Generate a hash secret key that can be used later for encryption and
 * decryption.
 *
 * It will generate a binary secret key 15 bytes in lenght. This should be
 * used in conjunction with Security.encryption_salt
 *
 * @param object $Model the model object the behavior is working with
 *
 * @return string
 */
	public function generateSecret(Model $Model) {
		$string = Configure::read('Security.salt') . serialize($Model->data[$Model->alias]) . time();
		return substr(sha1($string, true), 0, 15);
	}

/**
 * BeforeFind callback
 *
 * before finding something encode the fields that need to be encoded to
 * find a match
 *
 * @param Model $Model the model object being worked with
 * @param array $queryData the conditions for the find that should / could be encrypted
 *
 * @return array
 */
	public function beforeFind(Model $Model, $queryData) {
		foreach ($this->settings[$Model->alias]['fields'] AS $field) {
			if (isset($queryData['conditions'][$Model->alias.'.'.$field])) {
				$queryData['conditions'][$Model->alias.'.'.$field] = $this->encrypt($queryData['conditions'][$Model->alias.'.'.$field]);
			}
		}

		return $queryData;
	}

/**
 * AfterFind callback
 *
 * After getting data from the database decrypt any fields that were
 * encrypted before returning.
 *
 * @param Model $Model the model object being worked with
 * @param array $results the data that was found
 * @param bool $primary if it is the primary model in the find or not
 *
 * @return array
 */
	public function afterFind(Model $Model, $results, $primary) {
		foreach ($this->settings[$Model->alias]['fields'] AS $field) {
			if ($primary) {
				foreach ($results AS $key => $value) {
					if (isset($value[$Model->alias][$field])) {
						$results[$key][$Model->alias][$field] = $this->decrypt($value[$Model->alias][$field]);
					}
				}
			}

			else {
				if (isset($results[$field])) {
					$results[$field] = $this->decrypt($results[$field]);
				}
			}
		}

		return $results;
	}

/**
 * BeforeSave callback
 *
 * before saving loop through any fields that need to be encrypted and
 * encrypt them
 *
 * @param Model $Model the model being encrypted
 *
 * @return boolean
 */
	public function beforeSave(Model $Model) {
		if (is_array(current($Model->data[$Model->alias]))) {
			// saveall
			foreach ($Model->data[$Model->alias] as $k => $row) {
				foreach ($this->settings[$Model->alias]['fields'] AS $field) {
					if (isset($Model->data[$Model->alias][$k][$field])) {
						$Model->data[$Model->alias][$k][$field . '_search'] = $this->searchFieldData($Model->alias, $Model->data[$Model->alias][$k][$field]);
						$Model->data[$Model->alias][$k][$field] = $this->encrypt($Model->data[$Model->alias][$k][$field]);
					}
				}
			}
		}

		else{
			// normal save
			foreach ($this->settings[$Model->alias]['fields'] AS $field) {
				if (isset($Model->data[$Model->alias][$field])) {
					$Model->data[$Model->alias][$field . '_search'] = $this->searchFieldData($Model->alias, $Model->data[$Model->alias][$field]);
					$Model->data[$Model->alias][$field] = $this->encrypt($Model->data[$Model->alias][$field]);
				}
			}
		}

		return parent::beforeSave($Model);
	}

/**
 * Encrypt data
 *
 * If called from a model the first param will be the Model object which is not
 * used.
 *
 * @param Model|string $data
 * @param string $data2
 *
 * @return string
 */
	public function encrypt($data, $data2 = null) {
		if (is_object($data)) {
			$data = $data2;
		}

		return trim(
			base64_encode(
				mcrypt_encrypt(
					MCRYPT_RIJNDAEL_256,
					$this->_secret(),
					$data,
					MCRYPT_MODE_ECB,
					mcrypt_create_iv(
						mcrypt_get_iv_size(
							MCRYPT_RIJNDAEL_256,
							MCRYPT_MODE_ECB
						),
						MCRYPT_RAND
					)
				)
			)
		);
	}

/**
 * Get the encryption secrect
 *
 * @return string
 */
	protected function _secret() {
		return Configure::read('Security.encryption_secret') . Configure::read('Security.encryption_salt');
	}

/**
 * Decrypt a string
 *
 * If called from a model the first param will be the Model object which is not
 * used.
 *
 * @param Model|string $data
 * @param string $data2
 *
 * @return string
 */
	public function decrypt($data, $data2 = null) {
		if (is_object($data)) {
			unset($data);
			$data = $data2;
		}

		if (empty($data)) {
			return '';
		}

		return trim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_256,
					$this->_secret(),
				base64_decode($data),
				MCRYPT_MODE_ECB,
				mcrypt_create_iv(
					mcrypt_get_iv_size(
						MCRYPT_RIJNDAEL_256,
						MCRYPT_MODE_ECB
					),
					MCRYPT_RAND
				)
			)
		);
	}

/**
 * Generate word index for doing lookups on the encrypted data
 *
 * strip out loads of text and mix it up so you can still search for terms
 * but it does not mean anything to anyone.
 *
 * if the string is less than the minimum length nothing will be saved in
 * the search box as it may give away something usefull.
 *
 * @param alias $alias the model alias
 * @param string $string the raw info that has been saved
 *
 * @return string
 */
	public function searchFieldData($alias, $string) {
		if (empty($string) || strlen($string) < $this->settings[$alias]['searchWordMinLenght']) {
			return '';
		}

		foreach (explode(' ', (string)$string) as $k => $v) {
			if (strlen($v) > $this->settings[$alias]['searchWordLength']) {
				$words[] = $v;
			}
		}

		shuffle($words);

		return implode(' ', array_unique($words));
	}

/**
 * encrypt all the records in a table for existing data
 *
 * @param Model $Model the model being used
 */
	public function initializeEncryption(Model $Model) {

	}

}