<?php

/**
 *
 * @copyright  2021 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_db_password($s) {
	$charset = array('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz', '0123456789', '~!@#$%^&*()_-+={}[]/<>,.;?:|');

	if (strlen($s) < 10) {
		return false;
	}

	foreach ($charset as $chars) {
		if (preg_match_all('/[' . preg_quote($chars, '/') . ']/', $s) < 2) {
			return false;
		}
	}

	return true;
}

