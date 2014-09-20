<?php
/**
 * InfinitasComment
 *
 * This is the model that handles comment saving and other CRUD actions, the
 * commentable behavior will auto relate and attach this model to the models
 * that need it. If your tables do not allow this you can do it yourself using
 * $hasMany param in your model
 *
 * The model has a few methods for getting some data like new comments, pending
 * and other thigns that may be of use in an application
 *
 * @copyright Copyright (c) 2009 Carl Sutton ( dogmatic69 )
 * @link http://infinitas-cms.org
 * @package Infinitas.Comments.Model
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.8a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */

/**
 * InfinitasComment
 *
 * @package Infinitas.Comments.Model
 *
 * @property User $User
 * @property CommentAttribute $InfinitasCommentAttribute
 */
class InfinitasComment extends CommentsAppModel {

/**
 * Custom find methods
 *
 * @var array
 */
	public $findMethods = array(
		'linkedComments' => true,
		'adminIndex' => true,
		'otherCommentors' => true,
		'urlData' => true,
	);

/**
 * behaviors that are attached to the model.
 *
 * @var array
 */
	public $actsAs = array(
		'Libs.Expandable',
	);

/**
 * relations for the model
 *
 * @var array
 */
	public $hasMany = array(
		'CommentAttribute' => array(
			'className' => 'Comments.InfinitasCommentAttribute',
		),
	);

	public $belongsTo = array(
		'User' => array(
			'className' => 'Users.User',
		),
	);

/**
 * @copydoc AppModel::__construct()
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		$this->validate = array(
			'email' => array(
				'notEmpty' => array(
					'rule' => 'notEmpty',
					'message' => __d('comments', 'Please enter your email address'),
				),
				'email' => array(
					'rule' => array('email'),
					'message' => __d('comments', 'Please enter a valid email address'),
				),
			),
			'comment' => array(
				'notEmpty' => array(
					'rule' => 'notEmpty',
					'message' => __d('comments', 'Please enter your comments'),
				),
			),
		);
	}

/**
 * Find details of other commentors on the thread
 *
 * Finds other active commentors on the thread so they can be notified of updates
 *
 * @param string $state
 * @param array $query
 * @param array $results
 *
 * @return array
 *
 * @throws InvalidArgumentException
 */
	protected function _findOtherCommentors($state, array $query, array $results = array()) {
		if ($state == 'before') {
			if (empty($query['class'])) {
				throw new InvalidArgumentException(__d('comments', 'Class not passed'));
			}
			if (empty($query['foreign_id'])) {
				throw new InvalidArgumentException(__d('comments', 'Record not passed'));
			}

			$query['fields'] = array_merge((array)$query['fields'], array(
				$this->alias . '.' . $this->primaryKey,
				$this->alias . '.email',
				$this->User->alias . '.*',
			));

			$query['conditions'] = array_merge((array)$query['conditions'], array(
				$this->alias . '.class' => $query['class'],
				$this->alias . '.foreign_id' => $query['foreign_id'],
				$this->alias . '.active' => 1,
				$this->alias . '.subscribed' => 1,
			));

			if ($query['email']) {
				$query['conditions']['not'][$this->alias . '.email'] = $query['email'];
			}

			$query['joins'] = (array)$query['joins'];
			$query['joins'][] = $this->autoJoinModel($this->User);
			return $query;
		}
		if (empty($results)) {
			return array();
		}

		$attributes = $this->CommentAttribute->find('all', array(
			'conditions' => array(
				$this->CommentAttribute->alias . '.infinitas_comment_id' => Hash::extract($results, '{n}.' . $this->alias . '.' . $this->primaryKey),
			),
		));
		foreach ($results as &$result) {
			$template = sprintf('{n}.%s[infinitas_comment_id=%s]',
				$this->CommentAttribute->alias,
				$result[$this->alias][$this->primaryKey]
			);
			$result[$this->alias] = array_merge(
				$result[$this->alias],
				Hash::combine(Hash::extract($attributes, $template), '{n}.key', '{n}.val')
			);
			if (!empty($result[$this->User->alias][$this->User->id])) {
				if ($result[$this->alias]['prefered_name']) {
					$result[$this->alias]['name'] = $result[$this->alias]['prefered_name'];
				} else {
					$result[$this->alias]['name'] = $result[$this->alias]['username'];
				}
			} else {
				$result[$this->alias]['name'] = $result[$this->alias]['username'];
			}
		}

		return $results;
	}

/**
 * Get the data required to link to a comment
 *
 * @param string $state
 * @param array $query
 * @param array $results
 *
 * @return array
 *
 * @throws InvalidArgumentException
 */
	protected function _findUrlData($state, array $query, array $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__d('comments', 'Unknown comment selected'));
			}

			$query['fields'] = array_merge((array)$query['fields'], array(
				$this->alias . '.class',
				$this->alias . '.foreign_id',
			));

			$query['conditions'] = array_merge((array)$query['conditions'], array(
				$this->alias . '.' . $this->primaryKey => $query[0],
			));

			$query['limit'] = 1;

			return $query;
		}

		if (empty($results)) {
			return array();
		}
		$results = $results[0][$this->alias];

		list($plugin, $model) = pluginSplit($results['class']);
		return array(
			'data' => array(
				$model => array(
					ClassRegistry::init($results['class'])->primaryKey => $results['foreign_id'],
				),
			),
			'id' => $query[0],
			'plugin' => Inflector::camelize($plugin),
			'model' => $model,
			'class' => $results['class'],
		);
	}

/**
 * Find comments linked
 *
 * @param string $state
 * @param array $query
 * @param array $results
 *
 * @return string
 */
	protected function _findLinkedComments($state, array $query, array $results = array()) {
		if ($state === 'before') {
			$query['fields'] = array_merge((array)$query['fields'],array(
				$this->alias . '.id',
				$this->alias . '.user_id',
				$this->alias . '.email',
				$this->alias . '.class',
				$this->alias . '.foreign_id',
				$this->alias . '.comment',
				$this->alias . '.created',
				'CommentAttribute.id',
				'CommentAttribute.key',
				'CommentAttribute.val',
				'CommentAttribute.infinitas_comment_id',
			));

			$query['conditions'][$this->alias . '.active'] = 1;

			$query['order'] = array(
				$this->alias . '.created' => 'asc',
			);

			$query['joins'][] = array(
				'table' => 'infinitas_comment_attributes',
				'alias' => 'CommentAttribute',
				'type' => 'LEFT',
				'conditions' => array(
					'CommentAttribute.infinitas_comment_id = ' . $this->alias . '.id',
				),
			);

			$query['joins'][] = array(
				'table' => 'core_users',
				'alias' => 'CommentUser',
				'type' => 'LEFT',
				'conditions' => array(
					'CommentUser.id = ' . $this->alias . '.user_id',
				),
			);

			return $query;
		}

		$return = $map = array();
		$i = 0;
		foreach ($results as $result) {
			$index = $result[$this->alias][$this->primaryKey];

			if (!isset($map[$index])) {
				$map[$index] = $i++;
			}
			$mapIndex = $map[$index];

			if (empty($return[$mapIndex][$this->alias])) {
				$return[$mapIndex][$this->alias] = array_merge(
					array_fill_keys(explode(',', Configure::read('Comments.fields')), null),
					$result[$this->alias]
				);
			}

			$return[$mapIndex][$this->alias][$result['CommentAttribute']['key']] = $result['CommentAttribute']['val'];
		}

		return $return;
	}

/**
 * hack to get the attributes for comments
 *
 * this is a hack to get the atributes in the comment, this should
 * be handled in the attributes behavior but cake does not do model callbacks
 * 3 relations deep
 *
 * @param array $results the data found
 * @param bool $primary is this the primary model doing the find
 *
 * @return array
 */
	public function afterFind($results, $primary = false) {
		if ($this->findQueryType == 'linkedComments') {
			return $results;
		}

		if (isset($results[0][0]['count']) || empty($results)) {
			return $results;
		}

		$base = array_merge(
			array('schema' => $this->schema()),
			array('with' => 'InfinitasCommentAttribute', 'foreignKey' => $this->hasMany['InfinitasCommentAttribute']['foreignKey'])
		);

		if (!Set::matches('/' . $base['with'], $results)) {
			return $results;
		}

		if (isset($results[0]) || $primary) {
			foreach ($results as $k => $item) {
				foreach ($item[$base['with']] as $field) {
					$results[$k][$field['key']] = $field['val'];
				}

				unset($results[$k][$base['with']]);
			}
			return $results;
		}

		foreach ($results[$base['with']] as $field) {
			$results[$field['key']] = $field['val'];
		}

		return $results;
	}

/**
 * get comments by user
 *
 * Find all comments that a particulat user has created with a limit of
 * $limit
 *
 * @param string $user_id the users id
 * @param int $limit the max number of records to get
 *
 * @return array
 */
	public function getUsersComments($userId = null, $limit = 5) {
		$comments = $this->find('all', array(
			'conditions' => array(
				$this->alias . '.user_id' => $userId,
			),
			'order' => array(
				$this->alias . '.created' => 'asc',
			),
		));

		return $comments;
	}

/**
 * get some stats for notices in admin
 *
 * Find the number of comments that are pending and active so admin will
 * be able to take action.
 *
 * @param string $class the model class that the comments should be in
 * eg blog.post for blog comments
 *
 * @return array
 */
	public function getCounts($class = null) {
		if (!$class) {
			return false;
		}

		$counts = Cache::read('comments_count_' . $class);
		if ($counts !== false) {
			return $counts;
		}

		$counts['active'] = $this->find('count', array(
			'conditions' => array(
				$this->alias . '.active' => 1,
				$this->alias . '.class' => $class,
			),
			'contain' => false,
		));

		$counts['pending'] = $this->find('count', array(
			'conditions' => array(
				$this->alias . '.active' => 0,
				$this->alias . '.class' => $class,
			),
			'contain' => false,
		));

		Cache::write('comments_count_' . $class, $counts, 'blog');

		return $counts;
	}

/**
 * get a list of all the models that have comments
 *
 * @return array
 */
	public function getUniqueClassList() {
		$this->displayField = 'class';
		$classes = $this->find('list', array(
			'group' => array(
				$this->alias . '.class',
			),
			'order' => array(
				$this->alias . '.class' => 'asc',
			),
		));

		if (empty($classes)) {
			return array();
		}

		return array_combine($classes, $classes);
	}

/**
 * get a list of the latest comments
 *
 * used in things like comment wigets etc. will get a list of comments
 * from the site.
 *
 * @param bool $all all or just active
 * @param int $limit the msx number of comments to get
 *
 * @return array
 */
	public function latestComments($all = true, $limit = 10) {
		$cacheName = cacheName('latest_comments_', array($all, $limit));
		$comments = Cache::read($cacheName, 'core');
		if (!empty($comments)) {
			return $comments;
		}

		$conditions = array();
		if (!$all) {
			$conditions = array($this->alias . '.active' => 1);
		}

		$comments = $this->find('all', array(
			'conditions' => $conditions,
			'limit' => (int)$limit,
			'order' => array(
				$this->alias . '.created' => 'DESC',
			),
		));

		Cache::write($cacheName, $comments, 'core');

		return $comments;
	}

/**
 * Block ip addresse
 *
 * This will only allow block ip addresses of users that have posted comments before
 *
 * @param array $ipAddresses
 *
 * @return void
 */
	public function blockIp($ipAddresses) {
		$IpAddress = ClassRegistry::init('Security.IpAddress');
		if (!is_array($ipAddresses)) {
			$ipAddresses = array($ipAddresses);
		}

		$ipAddresses = $this->find('list', array(
			'fields' => array(
				$this->alias . '.ip_address',
				$this->alias . '.ip_address',
			),
			'conditions' => array(
				$this->alias . '.' . $this->primaryKey => $ipAddresses
			)
		));

		foreach ($ipAddresses as $ip) {
			$IpAddress->blockIp($ip, 'Blocked for spam comments');
		}
	}

/**
 * Paginated list for admin to manage comments
 * 
 * @param string $state the state of the query before / after
 * @param array $query the query conditions
 * @param array $results the query results
 * 
 * @return array
 */
	protected function _findAdminIndex($state, $query, $results = array()) {
		if ($state === 'before') {
			$query['fields'] = array_merge((array)$query['fields'], array(
				$this->alias . '.id',
				$this->alias . '.class',
				$this->alias . '.email',
				$this->alias . '.user_id',
				$this->alias . '.comment',
				$this->alias . '.active',
				$this->alias . '.subscribed',
				$this->alias . '.status',
				$this->alias . '.points',
				$this->alias . '.foreign_id',
				$this->alias . '.mx_record',
				$this->alias . '.ip_address',
				$this->alias . '.created',
			));

			if (empty($query['order'])) {
				$query['order'] = array(
					$this->alias . '.active' => 'asc',
					$this->alias . '.created' => 'desc',
				);
			}

			if (empty($query['limit'])) {
				$query['limit'] = $this->queryLimit;
			}

			return $query;
		}

		return $results;
	}

}