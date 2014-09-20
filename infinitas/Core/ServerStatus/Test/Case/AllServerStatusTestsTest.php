<?php
App::uses('AllTestsBase', 'Test/Lib');

class AllServerStatusTestsTest extends AllTestsBase {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All ServerStatus test');

		$path = CakePlugin::path('ServerStatus') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}
}
