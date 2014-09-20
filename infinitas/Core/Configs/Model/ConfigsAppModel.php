<?php
/**
 * ConfigsAppModel
 *
 * @package Infinitas.Configs.Model
 */

/**
 * ConfigsAppModel
 *
 * ConfigsAppModel is the main model class that all other configuration models
 * extend.
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Configs.Model
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.5a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

class ConfigsAppModel extends AppModel {

/**
 * Custom prefix
 *
 * @var string
 */
	public $tablePrefix = 'core_';
}