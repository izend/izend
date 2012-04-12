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
	$size=100;
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

	if ($size < 21) {
		$size=21;
	}
	else if ($size > 531) {
		$size=531;
	}

	if ($margin < 0) {
		$margin=0;
	}
	else if ($margin > 10) {
		$margin=10;
	}

	$png=qrencode($s, $size, $quality, $margin);

	if (!$png) {
		return run('error/internalerror', $lang);
	}

	if ($fg or $bg) {
		$img=imagecreatefromstring($png);
		imagetruecolortopalette($img, false, 255);

		if ($fg) {
			$rgb=str_split($fg[0] == '#' ? substr($fg, 1, 6) : $fg, 2);
			imagecolorset($img, 0, hexdec($rgb[0]), hexdec($rgb[1]), hexdec($rgb[2]));
		}
		if ($bg) {
			$rgb=str_split($bg[0] == '#' ? substr($bg, 1, 6) : $bg, 2);
			imagecolorset($img, 1, hexdec($rgb[0]), hexdec($rgb[1]), hexdec($rgb[2]));
		}

		ob_start();
		imagepng($img);
		$png=ob_get_clean();
		imagedestroy($img);
	}

	header('Content-Type: image/png');
	header("Content-Disposition: inline; filename=qr.png");

	echo $png;

	return false;
}

