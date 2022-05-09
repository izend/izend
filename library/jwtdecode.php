<?php

/**
 *
 * @copyright  2022 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function jwtdecode($token) {
	return json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));
}

