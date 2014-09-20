<?php
/**
 * MailSystem
 *
 * @package Infinitas.Emails.Model
 */

/**
 * MailSystem
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Emails.Model
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class MailSystem extends EmailsAppModel {
/**
 * database configuration to use
 *
 * @var string
 */
	public $useDbConfig = 'emails';

/**
 * Behaviors to attach
 *
 * @var boolean
 */
	public $actsAs = false;

/**
 * database table to use
 *
 * @var string
 */
	public $useTable = false;

/**
 * The details of the server to connect to
 *
 * @var array
 */
	public $server = array();

/**
 * Test a connection
 *
 * Validation method before saving an email account.
 *
 * @todo not implemented
 *
 * @param array $details the connection details
 *
 * @return integer
 */
	public function testConnection($details) {
		$this->server = $details;
		return $this->find('count');
	}

/**
 * Check for new mail
 *
 * @param string $account the account id
 *
 * @return array
 */
	public function checkNewMail($account) {
		$mails = $this->find(
			'all',
			array(
				'conditions' => Set::flatten(array($this->alias => $account))
			)
		);

		// @todo save to the db here

		// @todo delete messages from server here

		return $mails;
	}

}