<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function longmonth_de($unixtime) {
	static $longmonthname=array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');

	$month = idate('m', $unixtime);

	return $longmonthname[$month-1];
}

function shortmonth_de($unixtime) {
	static $shortmonthname=array('jan', 'feb', 'mär', 'apr', 'mai', 'jun', 'jul', 'aug;', 'sep', 'okt', 'nov', 'dez');

	$month = idate('m', $unixtime);

	return $shortmonthname[$month-1];
}

function longday_de($unixtime) {
	static $longdayname=array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');

	$weekday = idate('w', $unixtime);

	return $longdayname[$weekday];
}

function shortday_de($unixtime) {
	static $shortdayname=array('Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam');

	$weekday = idate('w', $unixtime);

	return $shortdayname[$weekday];
}

function longdate_de($unixtime) {
	$day = date('j', $unixtime);
	$year = date('Y', $unixtime);

	$month = longmonth_de($unixtime);

	return "$day $month $year";
}

function shortdate_de($unixtime) {
	$day = date('j', $unixtime);
	$year = date('Y', $unixtime);

	$month = shortmonth_de($unixtime);

	return "$day-$month-$year";
}

function shortdatetime_de($unixtime) {
	$date = shortdate_de($unixtime);

	$hour = date('H', $unixtime);
	$minute = date('i', $unixtime);

	return "$date ${hour}:{$minute}";
}

function longdatetime_de($unixtime) {
	$date = longdate_de($unixtime);

	$hour = date('H', $unixtime);
	$minute = date('i', $unixtime);

	return "$date ${hour}:{$minute}";
}

