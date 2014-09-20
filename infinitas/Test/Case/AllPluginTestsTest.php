<?php
App::uses('AllTestsBase', 'Test/Lib');

class AllPluginTestsTest extends AllTestsBase {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Plugin plugins test');
		$plugins = App::objects('plugin', APP . 'Plugin', false);
		foreach ($plugins as $plugin) {
			if (CakePlugin::loaded($plugin)) {
				$file = CakePlugin::path($plugin) . 'Test' . DS . 'Case' . DS . 'All' . $plugin . 'TestsTest.php';
				if (file_exists($file)) {
					$suite->addTestFile($file);
				}
			}
		}
		return $suite;
	}
}
