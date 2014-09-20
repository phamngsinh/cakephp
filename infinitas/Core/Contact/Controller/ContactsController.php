<?php
/**
 * ContactsController
 *
 * @package Infinitas.Contact.Controller
 */

/**
 * ContactsController
 *
 * Used for managing contacts at the company of the application
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Contact.Controller
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.7a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class ContactsController extends ContactAppController {

/**
 * View a contacts details
 *
 * @return void
 */
	public function view() {
		if (!isset($this->request->params['slug'])) {
			$this->notice('invalid');
		}

		$contact = $this->Contact->find('first', array(
			'conditions' => array(
				'Contact.slug' => $this->request->params['slug'],
				'Contact.active' => 1
			),
			'contain' => array(
				'Branch' => array(
					'fields' => array(
						'id',
						'name',
						'slug',
						'active'
					),
					'ContactAddress' => array(
						'Country'
					)
				)
			)
		));

		if (!$contact['Branch']['active']) {
			$this->notice('invalid');
		}

		$this->set(
			'title_for_layout',
			__d('contact', 'Contact details for %s %s', $contact['Contact']['first_name'], $contact['Contact']['last_name'])
		);
		$this->set(compact('contact'));
	}

/**
 * View all contacts
 *
 * @return void
 */
	public function admin_index() {
		$this->Paginator->settings = array(
			'contain' => array(
				'Branch'
			)
		);

		$contacts = $this->Paginator->paginate(
			null,
			$this->Filter->filter
		);

		$filterOptions = $this->Filter->filterOptions;
		$filterOptions['fields'] = array(
			'name',
			'branch_id' => array(null => __d('contact', 'All branches')) + $this->Contact->Branch->find('list'),
			'active' => (array)Configure::read('CORE.active_options')
		);

		$this->set(compact('contacts', 'filterOptions'));
	}

/**
 * Create a new record
 *
 * @return void
 */
	public function admin_add() {
		parent::admin_add();

		$branches = $this->Contact->Branch->find('list');
		if (empty($branches)) {
			$this->notice(__d('contact', 'Please add a branch first'), array('level' => 'notice','redirect' => array('controller' => 'branches')));
		}
		$this->set(compact('branches'));
	}

/**
 * Edit contanct record
 *
 * @param string $id the record id
 *
 * @return void
 */
	public function admin_edit($id = null) {
		parent::admin_edit($id);

		$branches = $this->Contact->Branch->find('list');
		$this->set(compact('branches'));
	}
}