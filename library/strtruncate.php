<?php

/**
 *
 * @copyright  2018-2019 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function strtruncate($s, $len, $hellip='...', $atword=false) {
	return strlen($s) > $len ? ($atword ? preg_replace("/^(.{1,$len})(\s.*|$)/s", '\\1' . $hellip, $s) : substr($s, 0, $len) . $hellip) : $s;
}
