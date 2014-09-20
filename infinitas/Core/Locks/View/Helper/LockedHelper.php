<?php
/**
 * LockedHelper
 *
 * @package Infinitas.Locks.Helper
 */

App::uses('InfinitasHelper', 'Libs.View/Helper');

/**
 * LockedHelper
 *
 * Helper for generating markup displaying what is locked and by who
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Locks.Helper
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class LockedHelper extends InfinitasHelper {

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Time',
		'Html',
		'Libs.Image', 'Libs.Design'
	);

/**
 * Create a locked icon.
 *
 * takes the data from a find and shows if it is locked and if so who by
 * and when.
 *
 * @param array $row the row of data from the find
 * @param string $model the key where the Lock data is available
 *
 * @return string
 */
	public function display($row = array(), $model = 'Lock') {
		$row = array_filter($row[$model]);
		if (!empty($row['id'])) {
			return $this->Html->link($this->Design->icon('locked'), $this->here . '#', array(
				'class' => 'icon locks',
				'title' => __d('locks', 'Locked :: This record was locked %s by %s',
					$this->Time->timeAgoInWords($row['created']),
					$row['Locker']['username']
				),
				'escape' => false
			));
		}
	}
}