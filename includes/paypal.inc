<?php

/**
 *
 * @copyright  2010-2015 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

global $paypal_context, $paypal_url, $paypal_api_url, $paypal_api_version;

$paypal_context = 'sandbox';	// 'sandbox' or 'live'

if ($paypal_context == 'sandbox') {
	$paypal_api_url = 'https://api-3t.sandbox.paypal.com/nvp';
	$paypal_url = 'https://www.sandbox.paypal.com';
}
else {
	$paypal_api_url = 'https://api-3t.paypal.com/nvp';
	$paypal_url = 'https://www.paypal.com';
}

$paypal_api_version='124';

global $paypal_username, $paypal_password, $paypal_signature;

if ($paypal_context == 'sandbox') {
	$paypal_username = false;
	$paypal_password = false;
	$paypal_signature = false;
}
else {
	$paypal_username = false;
	$paypal_password = false;
	$paypal_signature = false;
}

global $paypal_log;

$paypal_log=true;
