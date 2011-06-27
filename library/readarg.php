<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function readarg($s, $trim=true, $strip=true) {
	if (is_array($s)) {
		$r=array();
		foreach ($s as $ss) {
			$r[]=readarg($ss, $trim, $strip);
		}
		return $r;
	}

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

