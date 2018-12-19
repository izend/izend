<?php

/**
 *
 * @copyright  2018 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function holidays_us($year=false) {
	if (!$year)	{
		$year = intval(date('Y'));
	}

	function _adjustday($date) {
		static $oneday=24*60*60;

		$weekday=date('w', $date);

		if ($weekday == 0) {
			return $date + $oneday;
		}
		else if ($weekday == 6) {
			return $date - $oneday;
		}

		return $date;
	}

	$holidays = array(
		_adjustday(mktime(0, 0, 0,  1,  1, $year)),  				// New Year's Day
		strtotime("3 Mondays", mktime(0, 0, 0, 1, 1, $year)),		// Birthday of Martin Luther King, Jr.
		strtotime("3 Mondays", mktime(0, 0, 0, 2, 1, $year)),		// Wasthington's Birthday
		strtotime("last Monday of May $year"),						// Memorial Day
		_adjustday(mktime(0, 0, 0,  7,  4, $year)),					// Independence day
		strtotime("first Monday of September $year"),				// Labor Day
		strtotime("2 Mondays", mktime(0, 0, 0, 10, 1, $year)),		// Columbus Day
		_adjustday(mktime(0, 0, 0, 11, 11, $year)),					// Veteran's Day
		strtotime("4 Thursdays", mktime(0, 0, 0, 11, 1, $year)),	// Thanksgiving Day
		_adjustday(mktime(0, 0, 0, 12, 25, $year)),					// Christmas
	);

	return $holidays;
}
