<?php

/**
 *
 * @copyright  2010-2017 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'paypal.php';
require_once 'userisidentified.php';
require_once 'userprofile.php';
require_once 'validatecurrency.php';

function paypalcheckout($lang, $amount, $currency, $tax=0, $context=false) {
	global $base_url, $paypal_url, $sitename, $supported_languages;

	if (!user_is_identified()) {
		return run('error/unauthorized', $lang);
	}

	if (!(is_numeric($amount) and $amount > 0)) {
		return run('error/badrequest', $lang);
	}
	$amt=paypal_amt($amount);

	if (!validate_currency($currency)) {
		return run('error/badrequest', $lang);
	}
	$currencycode=$currency;

	if (!(is_numeric($tax) and $tax >= 0)) {
		return run('error/badrequest', $lang);
	}
	$taxamt=paypal_amt($tax);

	$itemamt=paypal_amt($amount-$tax);

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
		'PAYMENTREQUEST_0_ITEMAMT' 			=> $itemamt,
		'PAYMENTREQUEST_0_TAXAMT' 			=> $taxamt,
		'L_PAYMENTREQUEST_0_NAME0'			=> $name,
		'L_PAYMENTREQUEST_0_AMT0'			=> $itemamt,
		'L_PAYMENTREQUEST_0_TAXAMT0'		=> $taxamt,
		'L_PAYMENTREQUEST_0_QTY0'			=> '1',
		'NOSHIPPING' 						=> '1',
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

	$_SESSION['paypal'] = compact('token', 'amt', 'itemamt', 'taxamt', 'currencycode', 'context');

	reload($paypal_url . '/webscr&cmd=_express-checkout&token=' . $token);
}

