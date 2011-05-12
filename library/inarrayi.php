<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function in_arrayi($s, $arr) {
	foreach ($arr as $v) {
		if (strcasecmp($s, $v) == 0) {
			return true;
		}
	}

	return false;
}

