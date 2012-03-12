<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/paypal.inc';

function paypalbalance($lang) {
	if (!user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	$r = paypal_getbalance();

	if (!$r) {
		return false;
	}

	dump($r);

	return false;
}

