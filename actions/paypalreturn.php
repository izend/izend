<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'paypal.php';
require_once 'userisidentified.php';
require_once 'userprofile.php';

function paypalreturn($lang, $arglist=false) {
	if (!isset($_SESSION['paypal'])) {
		return run('error/badrequest', $lang);
	}

	$token=$_SESSION['paypal']['token'];

	$amt=$_SESSION['paypal']['amt'];
	$itemamt=$_SESSION['paypal']['itemamt'];
	$taxamt=$_SESSION['paypal']['taxamt'];
	$currencycode=$_SESSION['paypal']['currencycode'];
	$context=$_SESSION['paypal']['context'];

	unset($_SESSION['paypal']);

	if (!isset($arglist['token']) or $arglist['token'] != $token) {
		return run('error/badrequest', $lang);
	}

	$params = array(
		'TOKEN' 							=> $token,
	);

	$r = paypal_getexpresscheckoutdetails($params);

	if (!$r) {
		return run('error/internalerror', $lang);
	}

	if ($r['TOKEN'] != $token or $r['PAYMENTREQUEST_0_AMT'] != $amt or $r['PAYMENTREQUEST_0_ITEMAMT'] != $itemamt or $r['PAYMENTREQUEST_0_TAXAMT'] != $taxamt or $r['PAYMENTREQUEST_0_CURRENCYCODE'] != $currencycode) {
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
		'PAYMENTREQUEST_0_ITEMAMT' 			=> $itemamt,
		'PAYMENTREQUEST_0_TAXAMT' 			=> $taxamt,
	);

	$r = paypal_doexpresscheckoutpayment($params);

	if (!$r) {
		return run('error/internalerror', $lang);
	}

	if ($r['TOKEN'] != $token or $r['PAYMENTINFO_0_AMT'] != $amt or $r['PAYMENTINFO_0_TAXAMT'] != $taxamt or $r['PAYMENTINFO_0_CURRENCYCODE'] != $currencycode) {
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

		$output = paymentrejected($lang, $amt, $currencycode, $context);
	}
	else {
		require_once 'actions/paymentaccepted.php';

		$output = paymentaccepted($lang, $amt, $currencycode, $context);
	}

	return $output;
}
