<?php

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'strrand.php';

function newpassword($len=8) {
	$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

	return strrand($charset, $len);
}

