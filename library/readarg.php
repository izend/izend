<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function readarg($s, $trim=true) {
	if (is_array($s)) {
		return array_map('readarg', $s, array_fill(0, count($s), $trim));
	}

	if ($trim) {
		$s = trim($s);
	}

	return get_magic_quotes_gpc() ? stripslashes($s) : $s;
}

