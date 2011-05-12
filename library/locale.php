<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function locale() {
	if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		return false;
	}

	$httplanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

	if (empty($httplanguages) === true) {
		return false;
	}

	$lang = false;
	$quality = 0.0;

	$accepted = preg_split('/,\s*/', $httplanguages);

	foreach ($accepted as $accept) {
		$match = null;
		$result = preg_match('/^([a-z]{1,8}(?:[-_][a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accept, $match);

		if ($result < 1) {
			continue;
		}

		$q = isset($match[2]) ? (float) $match[2] : 1.0;

		if ($q > $quality) {
			$quality = $q;

			$lang = current(explode('_', current(explode('-', $match[1]))));

			if ($quality == 1.0) {
				break;
			}
		}
	}

	return $lang;
}

