<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';
require_once 'userprofile.php';
require_once 'models/paypal.inc';

function paypalreturn($lang, $arglist=false) {
	global $supported_languages;

	if (!isset($_SESSION['paypal']['token'])) {
		return run('error/badrequest', $lang);
	}

	$token=$_SESSION['paypal']['token'];

	if (!isset($arglist['token']) or $arglist['token'] != $token) {
		return run('error/badrequest', $lang);
	}

	if (!isset($_SESSION['paypal']['amt']) or !isset($_SESSION['paypal']['currencycode'])) {
		return run('error/badrequest', $lang);
	}

	$amt=paypal_amt($_SESSION['paypal']['amt']);
	$currencycode=$_SESSION['paypal']['currencycode'];

	$params = array(
		'TOKEN' 							=> $token,
	);

	$r = paypal_getexpresscheckoutdetails($params);

	if (!$r) {
		return run('error/internalerror', $lang);
	}

	if ($r['TOKEN'] != $token or $r['PAYMENTREQUEST_0_AMT'] != $amt or $r['PAYMENTREQUEST_0_CURRENCYCODE'] != $currencycode) {
		return run('error/internalerror', $lang);
	}

	$payerid = $r['PAYERID'];
	$email = $r['EMAIL'];

	$_SESSION['paypal']['payerid'] = $payerid;

	$params = array(
		'TOKEN' 							=> $token,
		'PAYERID' 							=> $payerid,
		'PAYMENTREQUEST_0_PAYMENTACTION'	=> 'Sale',
		'PAYMENTREQUEST_0_CURRENCYCODE' 	=> $currencycode,
		'PAYMENTREQUEST_0_AMT' 				=> $amt,
	);

	$r = paypal_doexpresscheckoutpayment($params);

	if (!$r) {
		return run('error/internalerror', $lang);
	}

	if ($r['TOKEN'] != $token or $r['PAYMENTINFO_0_AMT'] != $amt or $r['PAYMENTINFO_0_CURRENCYCODE'] != $currencycode) {
		return run('error/internalerror', $lang);
	}

	$transactionid=$r['PAYMENTINFO_0_TRANSACTIONID'];
	$paymentstatus=strtoupper($r['PAYMENTINFO_0_PAYMENTSTATUS']);

	$completed=false;

	switch ($paymentstatus) {
		case 'COMPLETED':
			$feeamt=$r['PAYMENTINFO_0_FEEAMT'];
			$taxamt=$r['PAYMENTINFO_0_TAXAMT'];
			$completed=true;
			break;
		case 'PENDING':
			$pendingreason=strtoupper($r['PAYMENTINFO_0_PENDINGREASON']);
			break;
		default:
			break;
	}

	unset($_SESSION['paypal']);

	return run($completed ? 'paymentaccepted' : 'paymentrejected', $lang);
}

