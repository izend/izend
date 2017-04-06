<?php

/**
 *
 * @copyright  2017 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'payline.php';

function paylinereturn($lang, $arglist=false) {
	if (!isset($_SESSION['payline'])) {
		return run('error/badrequest', $lang);
	}

	extract($_SESSION['payline']);	// token, amount, tax, currency, context

	unset($_SESSION['payline']);

	if (!isset($arglist['token']) or $arglist['token'] != $token) {
		return run('error/badrequest', $lang);
	}

	$params = array();

	$params['token'] = $token;

	$r = payline_getwebpaymentdetails($params);

	if (!$r) {
		return run('error/internalerror', $lang);
	}

	$return_code=$r['result']['code'];

	$transaction_id=$r['transaction']['id'];
	$authorization_number=$r['authorization']['number'];

	require_once 'actions/paymentaccepted.php';

	$output = paymentaccepted($lang, $amount, $currency, $context);

	return $output;
}
