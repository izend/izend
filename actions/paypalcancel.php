<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function paypalcancel($lang, $arglist=false) {
	if (!isset($_SESSION['paypal'])) {
		return run('error/badrequest', $lang);
	}

	$token=$_SESSION['paypal']['token'];

	$amt=$_SESSION['paypal']['amt'];
	$currencycode=$_SESSION['paypal']['currencycode'];
	$context=$_SESSION['paypal']['context'];

	unset($_SESSION['paypal']);

	if (!isset($arglist['token']) or $arglist['token'] != $token) {
		return run('error/badrequest', $lang);
	}

	require_once 'actions/paymentcancelled.php';

	return paymentcancelled($lang, $amt, $currencycode, $context);
}

