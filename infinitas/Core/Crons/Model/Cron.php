<?php
/**
 * Cron
 *
 * @package Infinitas.Crons.Model
 */

/**
 * Cron
 *
 * @copyright Copyright (c) 2010 Carl Sutton ( dogmatic69 )
 * @link http://www.infinitas-cms.org
 * @package Infinitas.Crons.Model
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @since 0.7a
 *
 * @author Carl Sutton <dogmatic69@infinitas-cms.org>
 */
class Cron extends CronsAppModel {
/**
 * Custom table
 *
 * @var string
 */
	public $useTable = 'crons';

/**
 * The process that is currently running
 *
 * @var string
 */
	protected $_currentProcess;

/**
 * Constructor
 *
 * Create some virtual fields for easier finds later on
 *
 * @param type $id
 * @param type $table
 * @param type $ds
 *
 * @return void
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		$this->virtualFields['created']  = 'CONCAT(' . $this->alias . '.year, "-", ' . $this->alias . '.month, "-", `' . $this->alias.'.day, " ", ' .
				$this->alias . '.start_time)';
		$this->virtualFields['modified'] = 'CONCAT(' . $this->alias.'.year, "-", ' . $this->alias . '.month, "-", `' . $this->alias.'.day, " ", ' .
				$this->alias.'.end_time)';
	}

/**
 * save the start of a cron run
 *
 * This is later used to check if any processes are running, along with
 * some stats befor the cron starts. This will enable infinitas to show
 * how much resources the crons are taking up.
 *
 * @return boolean
 */
	public function start() {
		$data = null;
		$memUsage = memoryUsage(false, false);
		$serverLoad = serverLoad(false);
		$serverLoad[0] = ($serverLoad[0] >= 0) ? $serverLoad[0] : 0;

		$data['Cron'] = array(
			'process_id' => @getmypid(),
			'year'	=> date('Y'),
			'month' => date('m'),
			'day'   => date('d'),
			'start_time' => date('H:i:s'),
			'start_mem' => $memUsage['current'],
			'start_load' => $serverLoad[0]
		);
		unset($memUsage, $serverLoad);

		$this->create();
		$alreadyRunning = $this->_isRunning();
		if ($this->save($data)) {
			$this->_currentProcess = $this->id;
			return $alreadyRunning === false;
		}
		
		return false;
	}

/**
 * updates the cron row to show the process as complete
 *
 * When the cron run is done this method is called to mark the end of the
 * process, along with recording some stats on the system that can
 * later be used for analysys.
 *
 * @param integer $tasksRan the number of events that did something
 * @param string $memAverage average memory usage for the run
 * @param string $loadAverage system load average for the run
 *
 * @return boolean|array
 */
	public function end($tasksRan = 0, $memAverage = 0, $loadAverage = 0) {
		if (!$this->_currentProcess) {
			throw new CronsNotStartedException(array());
		}

		$data = null;
		$memUsage = memoryUsage(false, false);
		$serverLoad = serverLoad(false);
		$serverLoad[0] = ($serverLoad[0] >= 0) ? $serverLoad[0] : 0;

		$data['Cron'] = array(
			'id' => $this->_currentProcess,
			'end_time' => date('H:i:s'),
			'end_mem' => $memUsage['current'],
			'end_load' => $serverLoad[0],
			'mem_ave' => $memAverage,
			'load_ave' => $loadAverage,
			'tasks_ran' => $tasksRan,
			'done' => 1
		);
		unset($memUsage, $serverLoad);

		$this->_currentProcess = null;

		return $this->save($data);
	}

/**
 * check if a cron is already running
 *
 * This does a simple check against the database to see if any jobs are
 * open (not marked done). If there are there could be something still
 * running.
 *
 * @todo check using the process_id to see if the process is still active
 *
 * @return boolean
 */
	protected function _isRunning() {
		return (bool)$this->find('count', array(
			'conditions' => array(
				$this->alias . '.done' => 0
			)
		));
	}

/**
 * check if enough time has elapsed since the last run
 *
 * the query checks if there are any jobs between the desired date and the
 * last run. If there are that means there was a job that ran more recently
 * than the time span required.
 *
 * @param string $date the datetime since the last cron should have run
 *
 * @return integer
 */
	public function countJobsAfter($date) {
		$data = $this->find('count', array(
			'conditions' => array(
				$this->alias . '.year' => date('Y', strtotime($date)),
				$this->alias . '.month' => (int)date('m', strtotime($date)),
				$this->alias . '.day' => (int)date('d', strtotime($date)),
				$this->alias . '.start_time >' => date('H:i:s', strtotime($date)),
			)
		));

		return $data;
	}

/**
 * get the last run job
 *
 * This gets the last time a cron ran, and can be used for checking if
 * the crons are setup or if they are running.
 *
 * @return string|boolean
 */
	public function getLastRun() {
		$last = $this->find('first', array(
			'fields' => array(
				$this->alias . '.id',
				'created'
			),
			'order' => array(
				'created' => 'desc'
			)
		));

		return !empty($last['Cron']['created']) ? $last['Cron']['created'] : false;
	}

/**
 * clear out old data
 *
 * This method is used to clear out old data, normally it is called via
 * crons to happen automatically, but could be used in other places.
 *
 * @param string $data the date to clear from
 *
 * @return boolean
 */
	public function clearOldLogs($date = null) {
		if (!$date) {
			$date = Configure::read('Cron.clear_logs');
		}

		$date = date('Y-m-d H:i:s', strtotime('- ' . $date));

		return $this->deleteAll(array(
			'Cron.created <= ' => $date
		), false);
	}

}