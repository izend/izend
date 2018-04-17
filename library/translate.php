<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

global $strings;

$strings = array();

@include 'strings.inc';

function translate($s, $lang, $from=false) {
	global $strings;

	$stab=$from ? $from : $strings;

	if ($s) {
		if ($lang && array_key_exists($lang, $stab) && array_key_exists($s, $stab[$lang])) {
			return $stab[$lang][$s];
		}
		if (array_key_exists(0, $stab) && array_key_exists($s, $stab[0])) {
			return $stab[0][$s];
		}
	}

	return false;
}

