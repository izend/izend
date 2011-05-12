<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function array_power_set($arr) {
	$r = array(array( ));

	foreach ($arr as $e) {
		foreach ($r as $c) {
			array_push($r, array_merge(array($e), $c));
		}
	}

	return $r;
}

