<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'identicon.php';

function avatar($lang, $arglist=false) {
	$name=false;
	$size=128;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$name = $arglist[0];
		}
		else if (isset($arglist['name'])) {
			$name = $arglist['name'];
		}
		if (isset($arglist[1])) {
			$size = $arglist[1];
		}
		else if (isset($arglist['size'])) {
			$size = $arglist['size'];
		}
	}

	if (!$name or !$size or !is_numeric($size) or $size < 16 or $size > 128) {
		return run('error/badrequest', $lang);
	}

	$img = identicon($name, $size);

	header('Content-Type: image/png');
	header("Content-Disposition: inline; filename=$name");

	imagepng($img);
	imagedestroy($img);

	return false;
}

