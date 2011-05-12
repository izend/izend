<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'strlogo.php';

function logo($lang, $arglist=false) {
	$name=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$name = $arglist[0];
		}
	}

	if (!$name) {
		return run('error/badrequest', $lang);
	}

	$img = strlogo($name);

	header('Content-Type: image/png');
	header("Content-Disposition: inline; filename=$name");

	imagepng($img);
	imagedestroy($img);

	return false;
}

