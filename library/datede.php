<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function longmonth_de($unixtime) {
	static $longmonthname=array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');

	$thismonth = idate('m', $unixtime);

	return $longmonthname[$thismonth-1];
}

function shortmonth_de($unixtime) {
	static $shortmonthname=array('jan', 'feb', 'm&auml;r', 'apr', 'mai', 'jun', 'jul', 'aug;', 'sep', 'okt', 'nov', 'dez');

	$thismonth = idate('m', $unixtime);

	return $shortmonthname[$thismonth-1];
}

function longday_de($unixtime) {
	static $longdayname=array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');

	$thisweekday = idate('w', $unixtime);

	return $longdayname[$thisweekday];
}

function shortday_de($unixtime) {
	static $shortdayname=array('Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam');

	$thisweekday = idate('w', $unixtime);

	return $shortdayname[$thisweekday];
}

function longdate_de($unixtime) {
	$thisday = idate('d', $unixtime);
	$thisyear = idate('Y', $unixtime);

	$month = longmonth_de($unixtime);

	return "$thisday $month $thisyear";
}

function shortdate_de($unixtime) {
	$thisday = idate('d', $unixtime);
	$thisyear = idate('Y', $unixtime);

	$month = shortmonth_de($unixtime);

	return "$thisday-$month-$thisyear";
}

