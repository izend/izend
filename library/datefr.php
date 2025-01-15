<?php

/**
 *
 * @copyright  2010-2025 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

function longmonth_fr($unixtime) {
	static $longmonthname=array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');

	$month = idate('m', $unixtime);

	return $longmonthname[$month-1];
}

function shortmonth_fr($unixtime) {
	static $shortmonthname=array('jan', 'fév', 'mar', 'avr', 'mai', 'jun', 'jul', 'aoû', 'sep', 'oct', 'nov', 'déc');

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

	return "$date {$hour}h{$minute}";
}

function longdatetime_fr($unixtime) {
	$date = longdate_fr($unixtime);

	$hour = date('H', $unixtime);
	$minute = date('i', $unixtime);

	return "$date {$hour}h{$minute}";
}

function longtime_fr($d) {
	if ($d < 60*60) {
		return sprintf('%dm %ds', $d / 60, $d % 60);
	}
	else if ($d < 24*60*60) {
		return sprintf('%dh %dm %ds', $d / 3600, ($d % 3600) / 60, $d % 60);
	}
	else {
		return sprintf('%dj %dh %dm %ds', $d / 86400, ($d % 86400) / 3600, ($d % 3600) / 60, $d % 60);
	}
}

