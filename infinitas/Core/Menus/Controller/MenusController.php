<?php
/**
 * MenusController
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Menus.Controller
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

/**
 * MenusController
 *
 * The MenuItem model handles the items within a menu, these are the indervidual
 * links that are used to build up the menu required.
 *
 * @package Infinitas.Menus.Controller
 */
class MenusController extends MenusAppController {

/**
 * List all menus
 *
 * @return void
 */
	public function admin_index() {
		$menus = $this->Paginator->paginate(
			null,
			$this->Filter->filter
		);

		$filterOptions = $this->Filter->filterOptions;
		$filterOptions['fields'] = array(
			'name',
			'type',
			'active' => (array)Configure::read('CORE.active_options')
		);

		$this->set(compact('menus', 'filterOptions'));
	}
}