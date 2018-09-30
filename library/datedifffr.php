<?php

/**
 *
 * @copyright  2018 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function datediff_fr($d1, $d2) {
	$tz=new DateTimeZone('UTC');

	$diff=date_diff(date_create(date('Y-m-d', $d2 > $d1 ? $d1 : $d2), $tz), date_create(date('Y-m-d', $d2 > $d1 ? $d2 : $d1), $tz));

	$s='';

	if ($diff->y) {
		$s .= $diff->y . ' an';
		if ($diff->y > 1) {
			$s .= 's';
		}
	}
	if ($diff->m) {
		if ($diff->y) {
			$s .= $diff->d ? ' ' : ' et ';
		}
		$s .= $diff->m . ' mois';
	}
	if ($diff->d) {
		if ($diff->y or $diff->m) {
			$s .= ' et ';
		}
		$s .= $diff->d . ' jour';
		if ($diff->d > 1) {
			$s .= 's';
		}
	}

	return $s;
}
