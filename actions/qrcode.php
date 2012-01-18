<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'qrencode.php';

function qrcode($lang, $arglist=false) {
	$s=false;
	$size=100;
	$color=false;
	$quality='L';

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$s = $arglist[0];
		}
		else if (isset($arglist['s'])) {
			$s = $arglist['s'];
		}
		if (isset($arglist['size'])) {
			$size = $arglist['size'];
		}
		if (isset($arglist['color'])) {
			$color = $arglist['color'];
		}
		if (isset($arglist['quality'])) {
			$quality = $arglist['quality'];
		}
	}

	if (!$s or !$size or !is_numeric($size) or ($color and !preg_match('/([0-9A-F]){6}/i', $color))) {
		return run('error/badrequest', $lang);
	}

	$png=qrencode($s, $size, $quality);

	if (!$png) {
		return run('error/internalerror', $lang);
	}

	if ($color) {
		$rgb=str_split($color, 2);
		$img=imagecreatefromstring($png);
		imagefilter($img, IMG_FILTER_COLORIZE, hexdec($rgb[0]), hexdec($rgb[1]), hexdec($rgb[2]), 0);

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

