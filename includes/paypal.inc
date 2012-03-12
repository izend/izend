<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

global $paypal_context, $paypal_url, $paypal_api_url, $paypal_api_version;

$paypal_context='sandbox';	// 'sandbox' or 'live'

$paypal_api_url=$paypal_context == 'sandbox' ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
$paypal_url=$paypal_context == 'sandbox' ? 'https://www.sandbox.paypal.com' : 'https://www.paypal.com';

$paypal_api_version='65.3';

global $paypal_username, $paypal_password, $paypal_signature;

$paypal_username=false;
$paypal_password=false;
$paypal_signature=false;

global $paypal_log;

$paypal_log=true;

