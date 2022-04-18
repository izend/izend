<?php

/**
 *
 * @copyright  2022 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function array_put(&$v, $keys, $value) {
	$array=null;

	foreach ($keys as $k) {
		if (!is_array($v))
			return false;

		if (!array_key_exists($k, $v))
			$v[$k] = array();

		$array=&$v;

        $v = &$v[$k];
    } 
	   
	return $array ? $array[$k]=$value : false;
}
