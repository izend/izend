<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

global $strings;

$strings = array();

@include 'strings.inc';

function translate($s, $lang) {
	global $strings;

	if ($s) {
		if ($lang && array_key_exists($lang, $strings) && array_key_exists($s, $strings[$lang])) {
			return $strings[$lang][$s];
		}
		if (array_key_exists(0, $strings) && array_key_exists($s, $strings[0])) {
			return $strings[0][$s];
		}
	}

	return false;
}

