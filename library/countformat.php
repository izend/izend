<?php

/**
 *
 * @copyright  2012-2017 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function count_format($n, $point='.', $sep=',', $spacing='') {
	if ($n < 0) {
		return 0;
	}

	if ($n < 10000) {
		return number_format($n, 0, $point, $sep);
	}

	$d = $n < 1000000 ? 1000 : 1000000;

	$f = round($n / $d, 1);

	return number_format($f, $f - intval($f) ? 1 : 0, $point, $sep) . $spacing . ($d == 1000 ? 'k' : 'M');
}
