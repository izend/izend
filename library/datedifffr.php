<?php

/**
 *
 * @copyright  2018-2019 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'datediff.php';

function datediff_fr($d1, $d2) {
	$diff=datediff($d1, $d2);

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
