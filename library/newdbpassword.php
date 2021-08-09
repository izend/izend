<?php

/**
 *
 * @copyright  2021 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'strrand.php';

function newdbpassword($len=10) {
	$charset = array('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz', '0123456789', '~!@#$%^&*()_-+={}[]/<>,.;?:|');

	$pwd = strrand($charset[0], 2) . strrand($charset[1], 2) . strrand($charset[2], 2) . strrand($charset[3], 2);

	$len = max($len, 10) - strlen($pwd);

	while ($len-- > 0) {
		$pwd .= strrand($charset[rand(0, count($charset) - 1)], 1);
	}

	return str_shuffle($pwd);
}
