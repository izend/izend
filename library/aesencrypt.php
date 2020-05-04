<?php

/**
 *
 * @copyright  2013-2020 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function aesencrypt($s, $key) {
	$cipher = 'aes-256-cbc';
	$iv_size = openssl_cipher_iv_length($cipher);
	$iv = openssl_random_pseudo_bytes($iv_size);

	$crypto = @openssl_encrypt($s, $cipher, $key, 0, $iv);

	return $iv . $crypto;
}

function aesdecrypt($s, $key) {
	$cipher = 'aes-256-cbc';
	$iv_size = openssl_cipher_iv_length($cipher);
	$iv = substr($s, 0, $iv_size);

	$crypto = substr($s, $iv_size);

	return @openssl_decrypt($crypto, $cipher, $key, 0, $iv);
}
