<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function readarg($s, $trim=true, $strip=true) {
	if (get_magic_quotes_gpc()) {
		$s = stripslashes($s);
	}

	if ($trim) {
		$s = trim($s);
	}

	if ($strip) {
		$s = strip_tags($s);
	}

	return $s;
}

