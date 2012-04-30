<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'paypal.php';
require_once 'userisidentified.php';
require_once 'userprofile.php';

function paypalreturn($lang, $arglist=false) {
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

	unset($_SESSION['paypal']);

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
			$completed=true;
			break;
		case 'PENDING':
			$pendingreason=strtoupper($r['PAYMENTINFO_0_PENDINGREASON']);
			break;
		default:
			break;
	}

	if (!$completed) {
		require_once 'actions/paymentrejected.php';

		$output = paymentrejected($lang, $amt, $currencycode);
	}
	else {
		require_once 'actions/paymentaccepted.php';

		$output = paymentaccepted($lang, $amt, $currencycode);
	}

	return $output;
}

