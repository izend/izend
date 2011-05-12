<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function longmonth_en($unixtime) {
	static $longmonthname=array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

	$thismonth = idate('m', $unixtime);

	return $longmonthname[$thismonth-1];
}

function shortmonth_en($unixtime) {
	static $shortmonthname=array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug;', 'sep', 'oct', 'nov', 'dec');

	$thismonth = idate('m', $unixtime);

	return $shortmonthname[$thismonth-1];
}

function longday_en($unixtime) {
	static $longdayname=array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

	$thisweekday = idate('w', $unixtime);

	return $longdayname[$thisweekday];
}

function shortday_en($unixtime) {
	static $shortdayname=array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

	$thisweekday = idate('w', $unixtime);

	return $shortdayname[$thisweekday];
}

function longdate_en($unixtime) {
	$thisday = idate('d', $unixtime);
	$thisyear = idate('Y', $unixtime);

	$month = longmonth_en($unixtime);

	return "$month $thisday, $thisyear";
}

function shortdate_en($unixtime) {
	$thisday = idate('d', $unixtime);
	$thisyear = idate('Y', $unixtime);

	$month = shortmonth_en($unixtime);

	return "$thisday-$month-$thisyear";
}

