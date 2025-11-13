<?php

/**
 *
 * @copyright  2025 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function time_format($n, $d=false) {
	if ($n < 0) {
		return 0;
	}

	if ($n < 60) {
		return sprintf('%ds', $n);
	}

	if ($n < 3600) {
		return sprintf('%dm%02ds', $n / 60, $n % 60);
	}

	if ($n < 24*3600 or $d === false) {
		return sprintf('%dh%02dm%02ds', $n / 3600, ($n / 60) % 60, $n % 60);
	}

	return sprintf('%d%s%02dh%02dm%02ds', $n / (24*3600), $d, ($n / 3600) % 24, ($n / 60) % 60, $n % 60);
}
