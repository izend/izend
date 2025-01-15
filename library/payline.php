<?php

/**
 *
 * @copyright  2017-2025 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'payline.inc';
require_once 'vendor/autoload.php';

use \Payline\PaylineSDK;

function payline_sdk() {
	global $payline_merchant_id, $payline_access_key;
	global $payline_proxy_host, $payline_proxy_port, $payline_proxy_login, $payline_proxy_password;
	global $payline_context;

	static $sdk=null;

	if (!$sdk) {
		$sdk = new PaylineSDK($payline_merchant_id, $payline_access_key, $payline_proxy_host, $payline_proxy_port, $payline_proxy_login, $payline_proxy_password, $payline_context == 'prod' ? PaylineSDK::ENV_PROD : PaylineSDK::ENV_HOMO);
	}

	return $sdk;
}

function payline_dowebpayment($params) {
	global $payline_log;

	$sdk = payline_sdk();

	if (!$sdk) {
		return false;
	}

	$r = $sdk->doWebPayment($params);

	if ($payline_log) {
		logpayline('WebPayment', $r);
	}

	return $r;
}

function payline_getwebpaymentdetails($params) {
	global $payline_log;

	$sdk = payline_sdk();

	if (!$sdk) {
		return false;
	}

	$r = $sdk->getWebPaymentDetails($params);

	if ($payline_log) {
		logpayline('WebPaymentDetails', $r);
	}

	return $r;
}

function payline_amt($amt) {
	return number_format($amt, 2, '', '');
}

function payline_currency($cur) {
	$codes=array('EUR' => '978', 'USD' => '840', 'GPB' => '826');

	return isset($codes[$cur]) ? $codes[$cur] : '978';
}

function payline_language($locale) {
	$codes=array('en' => 'eng', 'fr' => 'fra');

	return isset($codes[$locale]) ? $codes[$locale] : 'eng';
}

function logpayline($method, $r) {
	global $payline_log;

	require_once 'log.php';

	$code=$r['result']['code'];
	$shortmsg=$r['result']['shortMessage'];
	$longmsg=$r['result']['longMessage'];

	$token = isset($r['token']) ? $r['token'] : false;
	$transaction_id = isset($r['transaction']['id']) ? $r['transaction']['id'] : false;

	$msg=array("METHOD={$method}", "CODE={$code}");
	if ($token) {
		$msg[] = "TOKEN={$token}";
	}
	if ($transaction_id) {
		$msg[] = "ID={$transaction_id}";
	}
	$msg[]="MESSAGE={$shortmsg}:{$longmsg}";

	$logmsg=implode(';', $msg);

	write_log($payline_log === true ? 'payline.log' : $payline_log, $logmsg);
}
