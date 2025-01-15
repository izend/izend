<?php

/**
 *
 * @copyright  2010-2025 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'paypal.inc';
require_once 'sendhttp.php';

function paypal_amt($amt) {
	return number_format($amt, 2, '.', '');
}

function paypal_localecode($locale) {
	$codes=array('en' => 'en_US', 'fr' => 'fr_FR');

	return isset($codes[$locale]) ? $codes[$locale] : 'en_US';
}

function paypal_setexpresscheckout($params) {
	return paypal_sendapi('SetExpressCheckout', $params);
}

function paypal_getexpresscheckoutdetails($params) {
	return paypal_sendapi('GetExpressCheckoutDetails', $params);
}

function paypal_doexpresscheckoutpayment($params) {
	return paypal_sendapi('DoExpressCheckoutPayment', $params);
}

function paypal_gettransactiondetails($params) {
	return paypal_sendapi('GetTransactionDetails', $params);
}

function paypal_refundtransaction($params) {
	return paypal_sendapi('RefundTransaction', $params);
}

function paypal_getbalance() {
	return paypal_sendapi('GetBalance');
}

function paypal_sendapi($method, $params=false) {
	$r = sendpaypal($method, $params);

	if (!$r) {
		return false;
	}

	$ack=strtoupper($r['ACK']);

	$success=($ack == 'SUCCESS' or $ack == 'SUCCESSWITHWARNING');

	if (!$success) {
		return false;
	}

	return $r;
}

function sendpaypal($method, $params=false) {
	global $paypal_api_url, $paypal_api_version;
	global $paypal_username, $paypal_password, $paypal_signature;
	global $paypal_log;

	$args = array(
		'METHOD'		=> $method,
		'VERSION'		=> $paypal_api_version,
		'USER'			=> $paypal_username,
		'PWD'			=> $paypal_password,
		'SIGNATURE'		=> $paypal_signature,
	);

	if (is_array($params)) {
		$args += $params;
	}

	$response=sendpost($paypal_api_url, $args);

	if (!$response or $response[0] != 200) {
		return false;
	}

	parse_str($response[2], $r);

	if ($paypal_log) {
		logpaypal($method, $r);
	}

	return $r;
}

function logpaypal($method, $r) {
	global $paypal_log;

	require_once 'log.php';

	$ack=strtoupper($r['ACK']);

	$token = isset($r['TOKEN']) ? $r['TOKEN'] : false;

	$msg=array("METHOD={$method}", "ACK={$ack}");
	if ($token) {
		$msg[] = "TOKEN={$token}";
	}

	$success=($ack == 'SUCCESS' or $ack == 'SUCCESSWITHWARNING');

	if (!$success) {
		$n=0;
		$err=array();
		while (isset($r["L_ERRORCODE{$n}"])) {
			$e=$r["L_ERRORCODE{$n}"];
			if (isset($r["L_LONGMESSAGE{$n}"])) {
				$e .= ':' . $r["L_LONGMESSAGE{$n}"];
			}
			$err[]=$e;
			$n++;
		}

		if ($err) {
			$msg[] = 'ERRORCODE=' . implode('|', $err);
		}
	}

	$logmsg=implode(';', $msg);

	write_log($paypal_log === true ? 'paypal.log' : $paypal_log, $logmsg);
}
