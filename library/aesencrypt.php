<?php

/**
 *
 * @copyright  2013 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function aesencrypt($s, $key) {
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

    $crypto = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $s, MCRYPT_MODE_CBC, $iv);

	return $iv . $crypto;
}

function aesdecrypt($s, $key) {
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $iv = substr($s, 0, $iv_size);

	$crypto=substr($s, $iv_size);

	return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypto, MCRYPT_MODE_CBC, $iv);
}
