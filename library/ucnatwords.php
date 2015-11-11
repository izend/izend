<?php

/**
 *
 * @copyright  2010-2015 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'strflat.php';

function ucnatwords($s) {
	// capitalize first letter in every word after removing accents
	return preg_replace_callback("#(^|[ '~-])(\w+)#", function($e) { return $e[1].strtoupper($e[2][0]).substr($e[2], 1); }, strtolower(strflat($s)));
}

