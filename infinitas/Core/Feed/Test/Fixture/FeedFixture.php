<?php
/**
 * fixture file for Feed tests.
 *
 * @package Feed.Fixture
 * @since 0.9b1
 */
class FeedFixture extends CakeTestFixture {
	public $name = 'Feed';
	
	public $table = 'global_feeds';

	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'plugin' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'model' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'controller' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'action' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'slug' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'description' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'fields' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'conditions' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'order' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'limit' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'group_id' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 36),
		'views' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $records = array(
		array(
			'id' => 1,
			'plugin' => 'blog',
			'model' => '',
			'controller' => 'posts',
			'action' => 'index',
			'created' => '2010-12-10 04:31:28',
			'name' => 'Blog',
			'slug' => 'blog',
			'description' => 'some rss feed',
			'fields' => '[\\"Post.*\\"]',
			'conditions' => '{\\"Post.active\\":\\"1\\"}',
			'order' => '{\\"Post.created\\":\\"desc\\"}',
			'limit' => 10,
			'active' => 1,
			'group_id' => 2,
			'views' => 140,
			'modified' => '2010-12-10 04:32:48'
		),
		array(
			'id' => 2,
			'plugin' => 'cms',
			'model' => '',
			'controller' => 'contents',
			'action' => 'index',
			'created' => '2010-12-10 04:35:10',
			'name' => 'Cms',
			'slug' => 'cms',
			'description' => 'some rss feed',
			'fields' => '[\\"Content.*\\"]',
			'conditions' => '{\\"Content.active\\":\\"1\\"}',
			'order' => '{\\"Content.created\\":\\"desc\\"}',
			'limit' => 10,
			'active' => 1,
			'group_id' => 2,
			'views' => 0,
			'modified' => '2010-12-10 04:37:17'
		),
	);
}