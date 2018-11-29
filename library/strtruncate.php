<?php

/**
 *
 * @copyright  2018 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function strtruncate($s, $len, $atword=false) {
	return strlen($s) > $len ? ($atword ? preg_replace("/^(.{1,$len})(\s.*|$)/s", '\\1...', $s) : substr($s, 0, $len) . '...') : $s;
}
