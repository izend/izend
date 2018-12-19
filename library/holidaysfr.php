<?php

/**
 *
 * @copyright  2018 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function holidays_fr($year=false) {
	if (!$year)	{
		$year = intval(date('Y'));
	}

	$easter = mktime(0, 0, 0, 3, 21 + easter_days($year), $year);
	$easterday = date('j', $easter);
	$eastermonth = date('n', $easter);
	$easteryear = date('Y', $easter);

	$holidays = array(
		mktime(0, 0, 0,  1,  1, $year),	// New Year's Day
		mktime(0, 0, 0,  5,  1, $year),	// Labor Day
		mktime(0, 0, 0,  5,  8, $year),	// Victory Day
		mktime(0, 0, 0,  7, 14, $year),	// Bastille Day
		mktime(0, 0, 0,  8, 15, $year),	// Assumption of Mary
		mktime(0, 0, 0, 11,  1, $year),	// All Saints' Day
		mktime(0, 0, 0, 11, 11, $year),	// Armistice Day
		mktime(0, 0, 0, 12, 25, $year),	// Christmas Day

		mktime(0, 0, 0, $eastermonth, $easterday + 1,  $easteryear),	// Easter Monday
		mktime(0, 0, 0, $eastermonth, $easterday + 39, $easteryear),	// Ascension Thursday
		mktime(0, 0, 0, $eastermonth, $easterday + 50, $easteryear),	// Pentecost Monday
	);

	sort($holidays);

	return $holidays;
}
