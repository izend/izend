<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'qrencode.php';
require_once 'validatecolor.php';

function qrcode($lang, $arglist=false) {
	$s=false;
	$size=1;
	$margin=0;
	$fg=$bg=false;
	$quality='M';

	$token=false;

	$qs = array('L', 'M', 'Q', 'H');

	$with_token=false;

	if (is_array($arglist)) {
		if (isset($arglist['s'])) {
			$s = $arglist['s'];
		}
		if (isset($arglist['fg'])) {
			$fg = $arglist['fg'];
		}
		if (isset($arglist['bg'])) {
			$bg = $arglist['bg'];
		}
		if (isset($arglist['quality'])) {
			$quality = $arglist['quality'];
		}
		if (isset($arglist['size'])) {
			$size = $arglist['size'];
		}
		if (isset($arglist['margin'])) {
			$margin = $arglist['margin'];
		}
		if ($with_token) {
			if (isset($arglist['token'])) {
				$token = $arglist['token'];
			}
		}
	}

	if ($with_token) {
		if (!isset($_SESSION['qrcode_token']) or $token != $_SESSION['qrcode_token']) {
			return run('error/badrequest', $lang);
		}
		unset($_SESSION['qrcode_token']);
	}

	if (!$s or !is_numeric($size) or !is_numeric($margin) or !$quality or !in_array($quality, $qs) or ($fg and !validate_color($fg)) or ($bg and !validate_color($bg))) {
		return run('error/badrequest', $lang);
	}

	if ($size < 1) {
		$size=1;
	}

	if ($margin < 0) {
		$margin=0;
	}

	$png=qrencode($s, $size, $quality, $fg, $bg, $margin);

	if (!$png) {
		return run('error/internalerror', $lang);
	}

	header('Content-Type: image/png');
	header("Content-Disposition: inline; filename=qr.png");

	echo $png;

	return false;
}

