<?php
/**
 * InfiniTime Helper class file.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 *
 *
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package Infinitas.Libs.Test.Helper
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Time Helper class for easy use of time data.
 *
 * Manipulation of time data.
 *
 * @package Infinitas.Libs.Test.Helper
 * @link http://book.cakephp.org/view/1470/Time
 */
App::uses('TimeHelper', 'View/Helper');

class InfiniTimeHelper extends TimeHelper {

/**
 * Returns a UNIX timestamp, given either a UNIX timestamp or a valid strtotime() date string.
 *
 * @param string $dateString Datetime string
 * @param int $userOffset User's offset from GMT (in hours)
 * @return string
 *
 * @link http://book.cakephp.org/view/1471/Formatting
 */
	public function fromString($dateString, $userOffset = null) {
		return parent::fromString($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns a nicely formatted date string for given Datetime string.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param int $userOffset User's offset from GMT (in hours)
 * @return string
 *
 * @link http://book.cakephp.org/view/1471/Formatting
 */
	public function nice($dateString = null, $userOffset = null) {
		return parent::nice($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns a formatted descriptive date string for given datetime string.
 *
 * If the given date is today, the returned string could be "Today, 16:54".
 * If the given date was yesterday, the returned string could be "Yesterday, 16:54".
 * If $dateString's year is the current year, the returned string does not
 * include mention of the year.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param int $userOffset User's offset from GMT (in hours)
 * @return string
 *
 * @link http://book.cakephp.org/view/1471/Formatting
 */
	public function niceShort($dateString = null, $userOffset = null) {
		return parent::niceShort($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns a partial SQL string to search for all records between two times
 * occurring on the same day.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param string $fieldName Name of database field to compare with
 * @param int $userOffset User's offset from GMT (in hours)
 * @return string
 *
 * @link http://book.cakephp.org/view/1471/Formatting
 */
	public function dayAsSql($dateString, $fieldName, $userOffset = null) {
		return parent::dayAsSql($dateString, $fieldName, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns true if given datetime string is today.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param int $userOffset User's offset from GMT (in hours)
 * @return boolean
 *
 */
	public function isToday($dateString, $userOffset = null) {
		return parent::isToday($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns true if given datetime string is within this week
 * @param string $dateString
 * @param int $userOffset User's offset from GMT (in hours)
 * @return boolean
 *
 * @link http://book.cakephp.org/view/1472/Testing-Time
 */
	public function isThisWeek($dateString, $userOffset = null) {
		return parent::isThisWeek($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns true if given datetime string is within this month
 * @param string $dateString
 * @param int $userOffset User's offset from GMT (in hours)
 * @return boolean
 *
 * @link http://book.cakephp.org/view/1472/Testing-Time
 */
	public function isThisMonth($dateString, $userOffset = null) {
		return parent::isThisMonth($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns true if given datetime string is within current year.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @return boolean
 *
 * @link http://book.cakephp.org/view/1472/Testing-Time
 */
	public function isThisYear($dateString, $userOffset = null) {
		return parent::isThisYear($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns true if given datetime string was yesterday.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param int $userOffset User's offset from GMT (in hours)
 * @return boolean
 *
 * @link http://book.cakephp.org/view/1472/Testing-Time
 *
 */
	public function wasYesterday($dateString, $userOffset = null) {
		return parent::wasYesterday($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns true if given datetime string is tomorrow.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param int $userOffset User's offset from GMT (in hours)
 * @return boolean
 *
 * @link http://book.cakephp.org/view/1472/Testing-Time
 */
	public function isTomorrow($dateString, $userOffset = null) {
		return parent::isTomorrow($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns a UNIX timestamp from a textual datetime description. Wrapper for PHP function strtotime().
 *
 * @param string $dateString Datetime string to be represented as a Unix timestamp
 * @param int $userOffset User's offset from GMT (in hours)
 * @return integer
 *
 * @link http://book.cakephp.org/view/1471/Formatting
 */
	public function toUnix($dateString, $userOffset = null) {
		return parent::toUnix($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns a date formatted for Atom RSS feeds.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param int $userOffset User's offset from GMT (in hours)
 * @return string
 *
 * @link http://book.cakephp.org/view/1471/Formatting
 */
	public function toAtom($dateString, $userOffset = null) {
		return parent::toAtom($dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Formats date for RSS feeds
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param int $userOffset User's offset from GMT (in hours)
 * @return string
 *
 * @link http://book.cakephp.org/view/1471/Formatting
 */
	public function toRSS($dateString, $userOffset = null) {
		$userOffset = $this->__userOffset($dateString, $userOffset);

		if ($userOffset == 0) {
			$timeZoneString = '+0000';
		} else {
			$hours = (int) floor(abs($userOffset));
			$minutes = (int) (fmod(abs($userOffset), $hours) * 60);
			$timeZoneString = ($userOffset < 0 ? '-' : '+') . str_pad($hours, 2, '0', STR_PAD_LEFT) . str_pad($minutes, 2, '0', STR_PAD_LEFT);
		}

		$date = parent::fromString($dateString, $userOffset);
		return date('D, d M Y H:i:s', $date) . ' ' . $timeZoneString;
	}

/**
 * Returns either a relative date or a formatted date depending
 * on the difference between the current time and given datetime.
 * $datetime should be in a <i>strtotime</i> - parsable format, like MySQL's datetime datatype.
 *
 * ### Options:
 *
 * - `format` => a fall back format if the relative time is longer than the duration specified by end
 * - `end` => The end of relative time telling
 * - `userOffset` => Users offset from GMT (in hours)
 *
 * Relative dates look something like this:
 *	3 weeks, 4 days ago
 *	15 seconds ago
 *
 * Default date formatting is d/m/yy e.g: on 18/2/09
 *
 * The returned string includes 'ago' or 'on' and assumes you'll properly add a word
 * like 'Posted ' before the function output.
 *
 * @param string $dateString Datetime string or Unix timestamp
 * @param array $options Default format if timestamp is used in $dateString
 * @return string
 *
 * @link http://book.cakephp.org/view/1471/Formatting
 */
	public function timeAgoInWords($dateTime, $options = array()) {
		return parent::timeAgoInWords($dateTime, $this->__userOffset($dateTime, $options));
	}

/**
 * Alias for timeAgoInWords
 *
 * @param mixed $dateTime Datetime string (strtotime-compatible) or Unix timestamp
 * @param mixed $options Default format string, if timestamp is used in $dateTime, or an array of options to be passed
 *   on to timeAgoInWords().
 * @return string
 * @see TimeHelper::timeAgoInWords
 *
 * @deprecated This method alias will be removed in future versions.
 * @link http://book.cakephp.org/view/1471/Formatting
 */
	public function relativeTime($dateTime, $options = array()) {
		return parent::relativeTime($dateTime, $this->__userOffset($dateTime, $options));
	}

/**
 * Returns true if specified datetime was within the interval specified, else false.
 *
 * @param mixed $timeInterval the numeric value with space then time type.
 *    Example of valid types: 6 hours, 2 days, 1 minute.
 * @param mixed $dateString the datestring or unix timestamp to compare
 * @param int $userOffset User's offset from GMT (in hours)
 * @return boolean
 *
 * @link http://book.cakephp.org/view/1472/Testing-Time
 */
	public function wasWithinLast($timeInterval, $dateString, $userOffset = null) {
		return parent::wasWithinLast($timeInterval, $dateString, $this->__userOffset($dateString, $userOffset));
	}

/**
 * Returns a formatted date string, given either a UNIX timestamp or a valid strtotime() date string.
 * This function also accepts a time string and a format string as first and second parameters.
 * In that case this function behaves as a wrapper for TimeHelper::i18nFormat()
 *
 * @param string $format date format string (or a DateTime string)
 * @param string $dateString Datetime string (or a date format string)
 * @param boolean $invalid flag to ignore results of fromString == false
 * @param int $userOffset User's offset from GMT (in hours)
 * @return string
 */
	public function format($format, $date = null, $invalid = false, $userOffset = null) {
		return parent::format($format, $date, $invalid, $this->__userOffset($date, $userOffset));
	}

/**
 * Returns a formatted date string, given either a UNIX timestamp or a valid strtotime() date string.
 * It take in account the default date format for the current language if a LC_TIME file is used.
 *
 * @param string $dateString Datetime string
 * @param string $format strftime format string.
 * @param boolean $invalid flag to ignore results of fromString == false
 * @param int $userOffset User's offset from GMT (in hours)
 * @return string
 */
	public function i18nFormat($date, $format = null, $invalid = false, $userOffset = null) {
		return parent::i18nFormat($date, $format, $invalid, $this->__userOffset($date, $userOffset));
	}

	private function __userOffset($dateString, $userOffset = null) {
		if (is_array($userOffset)) {
			if (!array_key_exists('userOffset', $userOffset) || is_null($userOffset['userOffset'])) {
				$userOffset['userOffset'] = $this->__userOffset($dateString);
			}

			return $userOffset;
		}

		if (is_null($userOffset) && CakeSession::check('Auth.User.time_zone')) {
			$timeZone = CakeSession::read('Auth.User.time_zone');

			if (phpversion() >= 5.2) {
				if (is_int($dateString)) {
					$dateString = '@' . $dateString;
				}
				$date = new DateTime($dateString, new DateTimeZone('UTC'));
				$date->setTimezone(new DateTimeZone($timeZone));
				$userOffset = $date->getOffset() / 3600;
			} else {
				//TODO: add support for older versions
			}
		}

		return $userOffset;
	}
}