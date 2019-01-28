<?php

/**
 *
 * @copyright  2018-2019 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'datediff.php';

function datediff_en($d1, $d2) {
	$diff=datediff($d1, $d2);

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
