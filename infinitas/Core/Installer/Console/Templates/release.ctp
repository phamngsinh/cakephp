<?php
/**
 * Infinitas Releases
 * 
 * Adapted from CakeDC Migration
 *
 * Copyright 2009 - 2010, Cake Development Corporation
 *                        1785 E. Sahara Avenue, Suite 490-423
 *                        Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009 - 2010, Cake Development Corporation
 * @link      http://codaset.com/cakedc/migrations/
 * @package   plugns.migrations
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
echo "<?php\n";
?>
	/**
	 * Infinitas Releas
	 *
	 * Auto generated database update
	 */
	 
	class <?php echo $class; ?> extends CakeRelease {

	/**
	* Migration description
	*
	* @var string
	* @access public
	*/
		public $description = 'Migration for <?php echo $plugin; ?> version <?php echo $version ?>';

	/**
	* Plugin name
	*
	* @var string
	* @access public
	*/
		public $plugin = '<?php echo $plugin ?>';

	/**
	* Actions to be performed
	*
	* @var array $migration
	* @access public
	*/
		public $migration = array(
	<?php echo $migration; ?>
		);

	<?php if (!empty($fixtures)) { ?>
	/**
	* Fixtures for data
	*
	* @var array $fixtures
	* @access public
	*/
		public $fixtures = array(
	<?php echo $fixtures; ?>
		);
	<?php } ?>

	/**
	* Before migration callback
	*
	* @param string $direction, up or down direction of migration process
	* @return boolean Should process continue
	* @access public
	*/
		public function before($direction) {
			return true;
		}

	/**
	* After migration callback
	*
	* @param string $direction, up or down direction of migration process
	* @return boolean Should process continue
	* @access public
	*/
		public function after($direction) {
			return true;
		}
	}