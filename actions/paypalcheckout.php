<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'paypal.php';
require_once 'userisidentified.php';
require_once 'userprofile.php';
require_once 'validatecurrency.php';

function paypalcheckout($lang, $amount, $currency=false) {
	global $base_url, $paypal_url, $sitename, $supported_currencies, $supported_languages;

	if (!user_is_identified()) {
		return run('error/unauthorized', $lang);
	}

	if (!(is_numeric($amount) and $amount > 0)) {
		return run('error/badrequest', $lang);
	}
	$amt=paypal_amt($amount);
	if ($currency) {
		if (!validate_currency($currency)) {
			return run('error/badrequest', $lang);
		}
		$currencycode=$currency;
	}
	else {
		$currencycode=$supported_currencies[0];
	}

	$name=translate('donate:name', $lang);

	$locale = $lang;
	if (!$locale) {
		$locale=user_profile('locale');
	}
	if (!$locale) {
		$locale=$supported_languages[0];
	}
	$localecode=paypal_localecode($locale);

	$email=user_profile('mail');
	$brandname=$sitename;
	$hdrimg=$base_url . '/logos/sitelogo.png';

	$returnurl=$base_url . url('paypalreturn', $lang);
	$cancelurl=$base_url . url('paypalcancel', $lang);

	$params = array(
		'LOCALECODE' 						=> $localecode,
		'PAYMENTREQUEST_0_PAYMENTACTION'	=> 'Sale',
		'PAYMENTREQUEST_0_CURRENCYCODE' 	=> $currencycode,
		'PAYMENTREQUEST_0_AMT' 				=> $amt,
		'PAYMENTREQUEST_0_ITEMAMT' 			=> $amt,
		'L_PAYMENTREQUEST_0_NAME0'			=> $name,
		'L_PAYMENTREQUEST_0_AMT0'			=> $amt,
		'L_PAYMENTREQUEST_0_QTY0'			=> '1',
		'NOSHIPPING' 						=> '1',
		'ALLOWNOTE' 						=> '0',
		'EMAIL'								=> $email,
		'BRANDNAME'							=> $sitename,
		'HDRIMG'							=> $hdrimg,
		'RETURNURL'							=> $returnurl,
		'CANCELURL'							=> $cancelurl,
		);

	$r = paypal_setexpresscheckout($params);

	if (!$r) {
		return run('error/internalerror', $lang);
	}

	$token = $r['TOKEN'];

	$_SESSION['paypal'] = array('token' => $token, 'amt' => $amt, 'currencycode' => $currencycode);

	reload($paypal_url . '/webscr&cmd=_express-checkout&token=' . $token);
}

