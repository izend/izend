<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function array_extract($arr, $keys) {
	$r = array();

	foreach ($arr as $k => $v) {
		if (in_array($k, $keys)) {
			$r[$k] = $v;
		}
		else if (array_key_exists($k, $keys)) {
			$r[$keys[$k]] = $v;
		}
	}

	return $r;
}

