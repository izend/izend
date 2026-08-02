<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function locale() {
	if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		return false;
	}

	$httplanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

	$lang = false;
	$quality = 0.0;

	$accepted = preg_split('/,\s*/', $httplanguages);

	foreach ($accepted as $accept) {
		$match = null;
		$result = preg_match('/^([a-z]{1,8}(?:[-_][a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accept, $match);

		if ($result < 1) {
			continue;
		}

		$q = isset($match[2]) ? (float)$match[2] : 1.0;

		if ($q > $quality) {
			$quality = $q;

			$lang = strtok($match[1], '-_');

			if ($quality == 1.0) {
				break;
			}
		}
	}

	return $lang;
}

