<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'validatecolor.php';
require_once 'phpqrcode/qrlib.php';

function qrencode($s, $size=1, $quality='M', $fg=false, $bg=false, $margin=0) {
	$ql = array('L' => QR_ECLEVEL_L, 'M' => QR_ECLEVEL_M, 'Q' => QR_ECLEVEL_Q, 'H' => QR_ECLEVEL_H);

	if (!$s or !is_numeric($size) or !is_numeric($margin) or !$quality or !array_key_exists($quality, $ql) or ($fg and !validate_color($fg)) or ($bg and !validate_color($bg))) {
		return false;
	}

	if ($size < 1) {
		$size=1;
	}

	if ($margin < 0) {
		$margin=0;
	}

	$q=$ql[$quality];

	$frame = @QRcode::text($s, false, $q, 1, 0);

	if (!$frame) {
		return false;
	}

	$h = count($frame);
    $w = strlen($frame[0]);

    $img = imagecreatetruecolor($w+2*$margin, $h);
    $qrimg = imagecreatetruecolor($w*$size+2*$margin, $h*$size+2*$margin);

	if ($bg) {
		$rgb=str_split($bg[0] == '#' ? substr($bg, 1, 6) : $bg, 2);
		$r=hexdec($rgb[0]);
		$g=hexdec($rgb[1]);
		$b=hexdec($rgb[2]);
	}
	else {
		$r=$g=$b=255;
	}

	$bg=imagecolorallocate($img, $r, $g, $b);
	imagefill($img, 0, 0, $bg);

	$bg=imagecolorallocate($qrimg, $r, $g, $b);
	imagefill($qrimg, 0, 0, $bg);

	if ($fg) {
		$rgb=str_split($fg[0] == '#' ? substr($fg, 1, 6) : $fg, 2);
		$r=hexdec($rgb[0]);
		$g=hexdec($rgb[1]);
		$b=hexdec($rgb[2]);
	}
	else {
		$r=$g=$b=0;
	}

	$fg=imagecolorallocate($img, $r, $g, $b);

	for ($y=0; $y < $h; $y++) {
		for ($x=0; $x < $w; $x++) {
			if ($frame[$y][$x] == '1') {
				imagesetpixel($img, $x, $y, $fg);
            }
        }
    }

	imagecopyresized($qrimg, $img, $margin, $margin, 0, 0, $w*$size, $h*$size, $w, $h);

	imagedestroy($img);

	ob_start();
	imagepng($qrimg);
	$png=ob_get_clean();
	imagedestroy($qrimg);

	return $png;
}

