<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function count_format($n, $point='.', $sep=',') {
	if ($n < 0) {
		return 0;
	}

	if ($n < 10000) {
		return number_format($n, 0, $point, $sep);
	}

	$d = $n < 1000000 ? 1000 : 1000000;

	$f = round($n / $d, 1);

	return number_format($f, $f - intval($f) ? 1 : 0, $point, $sep) . ($d == 1000 ? 'k' : 'M');
}
