<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function strlogo($name) {
	$waspfile=ROOT_DIR . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'wasp.png';
	$fontfile=ROOT_DIR . DIRECTORY_SEPARATOR . 'font.ttf';
	$fontsize=24.0;

	$bbox = imageftbbox($fontsize, 0, $fontfile, $name);

	$w=$bbox[2]+48+5;
	$h=40;

	$wasp = @imagecreatefrompng($waspfile) or die();
	$img = @imagecreatetruecolor($w, $h) or die();

	$bg=imagecolorallocate($img, 255, 255, 255);
	$fg=imagecolorallocate($img, 0x33, 0x33, 0x33);

	imagecolortransparent($img, $bg);
	imagefill($img, 0, 0, $bg);

	imagettftext($img, $fontsize, 0, 0, 30, $fg, $fontfile, $name);

	imagecopy($img, $wasp, $w-48, 0, 0, 0, 48, 48);

	return $img;
}
