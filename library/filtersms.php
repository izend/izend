<?php

/**
 *
 * @copyright  2017 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function filtersms($s) {
	$charset7bit = "@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
	$charset7bitext = "\f^{}\\[~]|€";

	$s = preg_replace('/\R/', "\r\n", trimsms($s));

	if (preg_match('/[^' . preg_quote($charset7bit . $charset7bitext, '/') . ']/', $s)) {
		$coding='16bit';
		$maxlen=70;
		$extlen=67;
	}
	else {
		$coding='7bit';
		$maxlen=160;
		$extlen=153;

		$s = preg_replace('/([' . preg_quote($charset7bitext, '/') . '])/u', "\e\\1", $s);
	}

	$msglen = mb_strlen($s, 'UTF-8');

	if ($msglen > $maxlen) {
		$count = 1 + floor($msglen / $extlen);
		$len = $count * $extlen;
	}
	else {
		$count = 1;
		$len = $maxlen;
	}

	return array($s, $coding, $msglen, $count, $len);
}

function trimsms($s) {
	return preg_replace('/ *(\R)+/', '\1', preg_replace('/^ /m', '', preg_replace('/[ \t]+/', ' ', trim($s))));
}
