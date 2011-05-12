<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function longmonth_fr($unixtime) {
	static $longmonthname=array('janvier', 'f&eacute;vrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'ao&ucirc;t', 'septembre', 'octobre', 'novembre', 'd&eacute;cembre');

	$thismonth = idate('m', $unixtime);

	return $longmonthname[$thismonth-1];
}

function shortmonth_fr($unixtime) {
	static $shortmonthname=array('jan', 'f&eacute;v', 'mar', 'avri', 'mai', 'juin', 'juil', 'ao&ucirc;', 'sep', 'oct', 'nov', 'd&eacute;c');

	$thismonth = idate('m', $unixtime);

	return $shortmonthname[$thismonth-1];
}

function longday_fr($unixtime) {
	static $longdayname=array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi');

	$thisweekday = idate('w', $unixtime);

	return $longdayname[$thisweekday];
}

function shortday_fr($unixtime) {
	static $shortdayname=array('dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam');

	$thisweekday = idate('w', $unixtime);

	return $shortdayname[$thisweekday];
}

function longdate_fr($unixtime) {
	$thisday = idate('d', $unixtime);
	$thisyear = idate('Y', $unixtime);

	$month = longmonth_fr($unixtime);

	return "$thisday $month $thisyear";
}

function shortdate_fr($unixtime) {
	$thisday = idate('d', $unixtime);
	$thisyear = idate('Y', $unixtime);

	$month = shortmonth_fr($unixtime);

	return "$thisday-$month-$thisyear";
}

