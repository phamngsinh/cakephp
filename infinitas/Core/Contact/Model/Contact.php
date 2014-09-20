<?php
/**
 * Contact
 *
 * @package Infinitas.Contacts.Model
 */

/**
 * Contact
 *
 * The Contact model handles the CRUD for user details.
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Contacts.Model
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.7a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class Contact extends ContactAppModel {

/**
 * Behaviors to load
 *
 * @var array
 */
	public $actsAs = array(
		'Libs.Sequence' => array(
			'group_fields' => array(
				'branch_id'
			)
		)
	);

/**
 * BelongsTo relations
 *
 * @var array
 */
	public $belongsTo = array(
		'Branch' => array(
			'className' => 'Contact.Branch',
			'counterCache' => 'user_count',
			'counterScope' => array('Contact.active' => 1)
		)
	);

/**
 * Constructor
 *
 * @param type $id
 * @param type $table
 * @param type $ds
 *
 * @return void
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		$this->validate = array(
			'first_name' => array(
				'notEmpty' => array(
					'rule' => 'notEmpty',
					'message' => __d('contact', 'Please enter the contacts first name')
				),
			),
			'last_name' => array(
				'notEmpty' => array(
					'rule' => 'notEmpty',
					'message' => __d('contact', 'Please enter the contacts last name')
				),
			),
			'phone' => array(
				'phone' => array(
					'rule' => array('phone', '/\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/'), //Configure::read('Website.phone_regex')),
					'message' => __d('contact', 'The number does not seem to be valid'),
					'allowEmpty' => true
				)
			),
			'mobile' => array(
				'phone' => array(
					'rule' => array('phone', '/\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/'), //Configure::read('Website.phone_regex')),
					'message' => __d('contact', 'Please enter a valid mobile number'),
					'allowEmpty' => true
				)
			),
			'email' => array(
				'notEmpty' => array(
					'rule' => 'notEmpty',
					'message' => __d('contact', 'Please enter an email address')
				),
				'email' => array(
					'rule' => array('email', true),
					'message' => __d('contact', 'That email address does not seem valid')
				)
			),
			'branch_id' => array(
				'rule' => array('comparison', '>', 0),
				'message' => __d('contact', 'Please select a branch')
			)
		);
	}
}