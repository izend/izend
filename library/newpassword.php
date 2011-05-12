<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'strrand.php';

function newpassword($len=6) {
	$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	return strrand($charset, $len);
}

