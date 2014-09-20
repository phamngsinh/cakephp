<?php
/**
 * CronsEventsTest
 *
 * These tests are extended from InfinitasEventTestCase which does most of the
 * automated testing for simple events
 */

App::uses('InfinitasEventTestCase', 'Events.Test/Lib');

class CronsEventsTest extends InfinitasEventTestCase {
	public $fixtures = array(
		'plugin.crons.cron'
	);

/**
 * adding the cron model for the tests
 */
	public function setUp() {
		parent::setUp();
		$this->Cron = ClassRegistry::init('Crons.Cron');
	}

/**
 * clean up after testing
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Cron);
	}

/**
 * test if the checks are working fine
 */
	public function testAreCronsSetup() {
		$result = $this->Event->trigger($this, 'Crons.areCronsSetup');
		$expected = array('areCronsSetup' => array('Crons' => '2010-12-07 14:21:01'));
		$this->assertEquals($expected, $result);

		$this->Cron->deleteAll(array('Cron.id != 1'));
		$result = $this->Event->trigger($this, 'Crons.areCronsSetup');
		$expected = array('areCronsSetup' => array('Crons' => false));
		$this->assertEquals($expected, $result);
	}

/**
 * test the todo list stuff is working fine
 */
	public function testRequireTodoList() {
		$expected = array('requireTodoList' => array('Crons' => array(
			array('name' => '/^The crons are not running, last run was (.*)$/', 'type' => 'error', 'url' => '#')
		)));
		$event = $this->Event->trigger($this, 'Crons.requireTodoList');
		$this->assertEquals($expected['requireTodoList']['Crons'][0]['type'], $event['requireTodoList']['Crons'][0]['type']);
		$this->assertEquals($expected['requireTodoList']['Crons'][0]['url'], $event['requireTodoList']['Crons'][0]['url']);
		$result = preg_match($expected['requireTodoList']['Crons'][0]['name'], $event['requireTodoList']['Crons'][0]['name']);
		$this->assertSame(1, $result);

		$this->Cron->deleteAll(array('Cron.id != 1'));
		$result = $this->Event->trigger($this, 'Crons.requireTodoList');
		$expected = array('requireTodoList' => array('Crons' => array(
			array('name' => 'Crons are not configured yet', 'type' => 'warning', 'url' => '#')
		)));
		$this->assertEquals($expected, $result);

		$result = $this->Cron->start();
		$this->assertTrue($result);

		$result = $this->Event->trigger($this, 'Crons.requireTodoList');
		$expected = array('requireTodoList' => array('Crons' => true));
		$this->assertEqual($expected, $result);
	}

/**
 * test that the crons are run correctly
 */
	public function testRunCrons() {
		$count = ClassRegistry::init('Crons.Cron')->find('count');
		$this->assertEquals(139, $count);
		$this->Event->trigger($this, 'Crons.runCrons');

		$count = ClassRegistry::init('Crons.Cron')->find('count');
		$this->assertEquals(0, $count);
	}
}