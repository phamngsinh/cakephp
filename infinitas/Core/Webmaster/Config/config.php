<?php
/**
 * Webmaster Config
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Webmaster.Config
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.9a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

$config['Webmaster'] = array(
	'last_modified' => '-2 weeks',   // default time ago that things were changed
	'change_frequency' => 'monthly', // never, yearly, monthly, daily, hourly, always
	'priority' => 0.5,				 // 0 -> 1
	'track_404' => true, 				// when set to false Infinitas will not track page not found errors
);