<?php

/**
 *
 * @copyright  2017 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

global $payline_context;

$payline_context='homo';	// 'homo' or 'prod'

global $payline_merchant_id, $payline_access_key, $payline_contract_number;

if ($payline_context == 'homo') {
	$payline_merchant_id = false;
	$payline_access_key = false;
	$payline_contract_number = '1234567';
}
else {
	$payline_merchant_id = false;
	$payline_access_key = false;
	$payline_contract_number = false;
}

global $payline_proxy_host, $payline_proxy_port, $payline_proxy_login, $payline_proxy_password;

$payline_proxy_host=false;
$payline_proxy_port=false;
$payline_proxy_login=false;
$payline_proxy_password=false;

global $payline_log;

$payline_log=true;
