<?php

/**
 *
 * @copyright  2010-2025 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

function longmonth_en($unixtime) {
	static $longmonthname=array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

	$month = idate('m', $unixtime);

	return $longmonthname[$month-1];
}

function shortmonth_en($unixtime) {
	static $shortmonthname=array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');

	$month = idate('m', $unixtime);

	return $shortmonthname[$month-1];
}

function longday_en($unixtime) {
	static $longdayname=array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

	$weekday = idate('w', $unixtime);

	return $longdayname[$weekday];
}

function shortday_en($unixtime) {
	static $shortdayname=array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

	$weekday = idate('w', $unixtime);

	return $shortdayname[$weekday];
}

function longdate_en($unixtime) {
	$day = date('j', $unixtime);
	$year = date('Y', $unixtime);

	$month = longmonth_en($unixtime);

	return "$month $day, $year";
}

function shortdate_en($unixtime) {
	$day = date('j', $unixtime);
	$year = date('Y', $unixtime);

	$month = shortmonth_en($unixtime);

	return "$day-$month-$year";
}

function shortdatetime_en($unixtime) {
	$date = shortdate_en($unixtime);

	$hour = date('H', $unixtime);
	$minute = date('i', $unixtime);

	return "$date {$hour}:{$minute}";
}

function longdatetime_en($unixtime) {
	$date = longdate_en($unixtime);

	$hour = date('H', $unixtime);
	$minute = date('i', $unixtime);

	return "$date {$hour}:{$minute}";
}

function longtime_en($d) {
	if ($d < 60*60) {
		return sprintf('%dm %ds', $d / 60, $d % 60);
	}
	else if ($d < 24*60*60) {
		return sprintf('%dh %dm %ds', $d / 3600, ($d % 3600) / 60, $d % 60);
	}
	else {
		return sprintf('%dd %dh %dm %ds', $d / 86400, ($d % 86400) / 3600, ($d % 3600) / 60, $d % 60);
	}
}
