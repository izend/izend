<?php

/**
 *
 * @copyright  2013 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'aesencrypt.php';

function urlencodebase64($s) {
	return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($s));
}

function urldecodebase64($s) {
	$s64 = str_replace(array('-', '_' ),array('+', '/'), $s);

	$mod4 = strlen($s64) % 4;
	if ($mod4) {
		$s64 .= substr('====', $mod4);
	}

	return base64_decode($s64);
}

function urlencrypt($s, $key) {
	return urlencodebase64(aesencrypt($s, $key));
}

function urldecrypt($s64, $key) {
	return aesdecrypt(urldecodebase64($s64), $key);
}
