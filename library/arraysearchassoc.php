<?php

/**
 *
 * @copyright  2022 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function array_search_assoc($array, $key, $value, $path=null) {
	if (array_key_exists($key, $array) && $array[$key] == $value) {
		$path[]=$key;

		return $path;
	}

	foreach ($array as $k => $v ) {
		if (is_array($v)) {
			$path[]=$k;

			$p = array_search_assoc($v, $key, $value, $path);

			if ($p !== false) {
				return $p;
			}
		}
	}

	return false;
}

