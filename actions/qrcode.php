<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'qrencode.php';

function qrcode($lang, $arglist=false) {
	$s=false;
	$size=100;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$s = $arglist[0];
		}
		else if (isset($arglist['s'])) {
			$s = $arglist['s'];
		}
		if (isset($arglist[1])) {
			$size = $arglist[1];
		}
		else if (isset($arglist['size'])) {
			$size = $arglist['size'];
		}
	}

	if (!$s or !$size or !is_numeric($size)) {
		return run('error/badrequest', $lang);
	}

	$png=qrencode($s, $size);

	if (!$png) {
		return run('error/internalerror', $lang);
	}

	header('Content-Type: image/png');
	header("Content-Disposition: inline; filename=qr.png");

	echo $png;

	return false;
}

