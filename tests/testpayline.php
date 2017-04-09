<?php

/**
 *
 * @copyright  2017 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

define('ROOT_DIR', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library');
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes');

function abort($msg, $code=1) {
	echo $msg, PHP_EOL;
	exit($code);
}

require_once 'payline.inc';

if ($payline_context != 'homo') {
	abort('payline.inc?');
}

if (! ($payline_merchant_id and $payline_access_key and $payline_contract_number)) {
	abort('payline.inc?');
}

require_once 'vendor/autoload.php';

use \Payline\PaylineSDK;

$payline = new PaylineSDK($payline_merchant_id, $payline_access_key, $payline_proxy_host, $payline_proxy_port, $payline_proxy_login, $payline_proxy_password, PaylineSDK::ENV_HOMO);

$doWebPaymentRequest = array();

$doWebPaymentRequest['payment']['amount'] = 1000;	// this value has to be an integer amount is sent in cents
$doWebPaymentRequest['payment']['currency'] = 978;	// ISO 4217 code for euro
$doWebPaymentRequest['payment']['action'] = 101;	// 101 stand for "authorization+capture"
$doWebPaymentRequest['payment']['mode'] = 'CPT';	// one shot payment

$doWebPaymentRequest['order']['ref'] = 'order_' . time();	// the reference of your order
$doWebPaymentRequest['order']['amount'] = 1000;		// may differ from payment.amount if currency is different
$doWebPaymentRequest['order']['currency'] = 978; 	// ISO 4217 code for euro
$doWebPaymentRequest['order']['date'] = date('d/m/Y H:i'); 	// dd/mm/yyy hh:mm

$doWebPaymentRequest['payment']['contractNumber'] = '1234567';

$doWebPaymentRequest['returnURL'] = 'http://localhost/paylinereturn';
$doWebPaymentRequest['cancelURL'] = 'http://localhost/paylinecancel';

$doWebPaymentResponse = $payline->doWebPayment($doWebPaymentRequest);

print_r($doWebPaymentResponse);
