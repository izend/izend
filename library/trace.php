<?php

/**
 *
 * @copyright  2016 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'dump.php';

function trace() {
	static $trace = null;

	if (func_num_args() == 0) {
		return $trace;
	}

	$var = func_get_arg(0);
	$label = func_num_args() > 1 ? func_get_arg(1) : null;

	if (!$trace) {
		$trace = array();
	}

	$trace[]=dump($var, $label, false);

	return true;
}
