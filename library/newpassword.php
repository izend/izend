<?php

/**
 *
 * @copyright  2010-2022 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'strrand.php';

function newpassword($len=8) {
	$charset = array('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz', '0123456789');

	$pwd = strrand($charset[0], 1) . strrand($charset[1], 1) . strrand($charset[2], 1);

	$len = max($len, 4) - strlen($pwd);

	while ($len-- > 0) {
		$pwd .= strrand($charset[rand(0, count($charset) - 1)], 1);
	}

	return str_shuffle($pwd);
}

