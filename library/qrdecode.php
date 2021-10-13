<?php

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'vendor/autoload.php';

use zxing\QrReader;

function qrdecode($qr, $type='file', $imagick=false) {
	if (!$qr or !$type or !in_array($type, array('file', 'blob', 'resource'))) {
		return false;
	}
	
	$qrcode = new Zxing\QrReader($qr, $type, $imagick);

	return $qrcode !== false ? $qrcode->text() : false;
}

