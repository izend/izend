<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function paypalcancel($lang, $arglist=false) {
	if (!isset($_SESSION['paypal']['token'])) {
		return run('error/badrequest', $lang);
	}

	$token=$_SESSION['paypal']['token'];

	if (!isset($arglist['token']) or $arglist['token'] != $token) {
		return run('error/badrequest', $lang);
	}

	unset($_SESSION['paypal']);

	return run('paymentcancelled', $lang);
}

