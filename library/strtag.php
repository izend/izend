<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function strtag($text) {
	$len=strlen($text);

	$fontfile=ROOT_DIR  . DIRECTORY_SEPARATOR . 'font.ttf';
	$fontsize=24.0;

	$bbox = imageftbbox($fontsize, 0, $fontfile, $text);

	$w=$bbox[2]+($len-1)*20;
	$h=40;

	$img = @imagecreatetruecolor($w, $h) or die();

	$bg=imagecolorallocate($img, 255, 255, 224);
	$fg=imagecolorallocate($img, 64, 64, 64);

	imagefill($img, 0, 0, $bg);

	// print text unevenly
	for ($x=15, $i=0; $i<$len; $i++) {
		$y = rand($h/2,$h/2+15);
		$r = rand(-45, 45);
		imagettftext($img, $fontsize, $r, $x, $y, $fg, $fontfile, $text[$i]);
		$x += rand(25, 35);
	}

	// blur with colored dots
	for ($i=0; $i<$w*$h/2.0; $i++) {
		$color=imagecolorallocate($img, rand(128,255), rand(128,255), rand(128,255));
		imagesetpixel($img, rand(0,$w-1), rand(0,$h-1), $color);
	}

	return $img;
}

