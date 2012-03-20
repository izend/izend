<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'paypal.php';

function balance($lang) {
	$action='init';
	if (isset($_POST['balance_balance'])) {
		$action='balance';
	}

	$balance=$currency=false;

	switch($action) {
		case 'balance':
			$r = paypal_getbalance();

			if (!$r) {
				break;
			}

			if (!(isset($r['L_AMT0']) and isset($r['L_CURRENCYCODE0']))) {
				break;
			}

			$balance=$r['L_AMT0'];
			$currency=$r['L_CURRENCYCODE0'];

			break;
		default:
			break;
	}

	$output = view('balance', $lang, compact('balance', 'currency'));

	return $output;
}

