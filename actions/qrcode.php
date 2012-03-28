<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'qrencode.php';
require_once 'validatecolor.php';

function qrcode($lang, $arglist=false) {
	$s=false;
	$size=1;
	$fg=$bg=false;
	$quality='L';

	$qs = array('L' => 25, 'M' => 29, 'Q' => 29, 'H' => 33);

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
	}

	if (!$s or !$size or !is_numeric($size) or $size < 1 or $size > 10 or !$quality or !isset($qs[$quality]) or ($fg and !validate_color($fg)) or ($bg and !validate_color($bg))) {
		return run('error/badrequest', $lang);
	}

	$png=qrencode($s, $qs[$quality] * $size, $quality);

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

