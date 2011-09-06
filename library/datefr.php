<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function longmonth_fr($unixtime) {
	static $longmonthname=array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');

	$month = idate('m', $unixtime);

	return $longmonthname[$month-1];
}

function shortmonth_fr($unixtime) {
	static $shortmonthname=array('jan', 'fév', 'mar', 'avri', 'mai', 'jun', 'jul', 'aoû', 'sep', 'oct', 'nov', 'déc');

	$month = idate('m', $unixtime);

	return $shortmonthname[$month-1];
}

function longday_fr($unixtime) {
	static $longdayname=array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi');

	$weekday = idate('w', $unixtime);

	return $longdayname[$weekday];
}

function shortday_fr($unixtime) {
	static $shortdayname=array('dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam');

	$weekday = idate('w', $unixtime);

	return $shortdayname[$weekday];
}

function longdate_fr($unixtime) {
	$day = date('j', $unixtime);
	$year = date('Y', $unixtime);

	$month = longmonth_fr($unixtime);

	return "$day $month $year";
}

function shortdate_fr($unixtime) {
	$day = date('j', $unixtime);
	$year = date('Y', $unixtime);

	$month = shortmonth_fr($unixtime);

	return "$day-$month-$year";
}

function shortdatetime_fr($unixtime) {
	$date = shortdate_fr($unixtime);

	$hour = date('H', $unixtime);
	$minute = date('i', $unixtime);

	return "$date ${hour}h{$minute}";
}

function longdatetime_fr($unixtime) {
	$date = longdate_fr($unixtime);

	$hour = date('H', $unixtime);
	$minute = date('i', $unixtime);

	return "$date ${hour}h{$minute}";
}

