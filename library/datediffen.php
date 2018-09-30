<?php

/**
 *
 * @copyright  2018 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function datediff_en($d1, $d2) {
	$tz=new DateTimeZone('UTC');

	$diff=date_diff(date_create(date('Y-m-d', $d2 > $d1 ? $d1 : $d2), $tz), date_create(date('Y-m-d', $d2 > $d1 ? $d2 : $d1), $tz));

	$s='';

	if ($diff->y) {
		$s .= $diff->y . ' year';
		if ($diff->y > 1) {
			$s .= 's';
		}
	}
	if ($diff->m) {
		if ($diff->y) {
			$s .= $diff->d ? ' ' : ' and ';
		}
		$s .= $diff->m . ' month';
		if ($diff->m > 1) {
			$s .= 's';
		}
	}
	if ($diff->d) {
		if ($diff->y or $diff->m) {
			$s .= ' and ';
		}
		$s .= $diff->d . ' day';
		if ($diff->d > 1) {
			$s .= 's';
		}
	}

	return $s;
}
