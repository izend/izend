<?php

/**
 *
 * @copyright  2017 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'payline.php';
require_once 'userisidentified.php';
require_once 'validatecurrency.php';

function paylinecheckout($lang, $amount, $currency, $tax=0, $context=false) {
	global $base_url, $payline_contract_number;

	if (!user_is_identified()) {
		return run('error/unauthorized', $lang);
	}

	if (!(is_numeric($amount) and $amount > 0)) {
		return run('error/badrequest', $lang);
	}
	$amt=payline_amt($amount);

	if (!validate_currency($currency)) {
		return run('error/badrequest', $lang);
	}
	$currencycode=payline_currency($currency);

	if (!(is_numeric($tax) and $tax >= 0)) {
		return run('error/badrequest', $lang);
	}
	$taxamt=payline_amt($tax);

	$itemamt=payline_amt($amount-$tax);

	$params = array();

	$params['payment']['contractNumber'] = $payline_contract_number;

	$params['payment']['amount'] = $amt;
	$params['payment']['currency'] = $currencycode;
	$params['payment']['action'] = 101;
	$params['payment']['mode'] = 'CPT';

	$params['order']['ref'] = 'P' . time();
	$params['order']['amount'] = $itemamt;
	$params['order']['taxes'] = $taxamt;
	$params['order']['currency'] = $currencycode;
	$params['order']['date'] = date('d/m/Y H:i');

	$params['returnURL'] = $base_url . url('paylinereturn', $lang);
	$params['cancelURL'] = $base_url . url('paylinecancel', $lang);

	$params['languageCode'] = $lang;

	$r = payline_dowebpayment($params);

	if (!$r) {
		return run('error/internalerror', $lang);
	}

	$token = $r['token'];
	$url = $r['redirectURL'];

	$_SESSION['payline'] = compact('token', 'amount', 'currency', 'tax', 'context');

	reload($url);
}

