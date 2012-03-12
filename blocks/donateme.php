<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'tokenid.php';
require_once 'validatecurrency.php';

function donateme($lang) {
	global $supported_currencies;

	$action='init';
	if (isset($_POST['donateme_donate'])) {
		$action='donate';
	}

	$amount=$currency=$token=false;

	switch($action) {
		case 'donate':
			if (isset($_POST['donateme_amount'])) {
				$amount=readarg($_POST['donateme_amount']);
			}
			if (isset($_POST['donateme_currency'])) {
				$currency=readarg($_POST['donateme_currency']);
			}
			if (isset($_POST['donateme_token'])) {
				$token=readarg($_POST['donateme_token']);
			}
			break;
		default:
			break;
	}

	$missing_amount=false;
	$bad_amount=false;
	$missing_currency=false;
	$bad_currency=false;

	$bad_token=false;

	switch($action) {
		case 'donate':
			if (!isset($_SESSION['donateme_token']) or $token != $_SESSION['donateme_token']) {
				$bad_token=true;
				break;
			}

			if (!$amount) {
				$missing_amount=true;
			}
			else if (!(is_numeric($amount) and $amount > 0)) {
				$bad_amount=true;
			}

			if (!$currency) {
				$missing_currency=true;
			}
			else if (!validate_currency($currency)) {
				$bad_currency=true;
			}
			break;
		default:
			break;
	}

	switch($action) {
		case 'donate':
			if ($bad_token or $missing_amount or $bad_amount or $missing_currency or $bad_currency) {
				break;
			}

			require_once 'actions/paypalcheckout.php';

			paypalcheckout($lang, $amount, $currency);

			break;
		default:
			break;
	}

	$_SESSION['donateme_token'] = $token = token_id();

	$errors = compact('missing_amount', 'bad_amount', 'missing_currency', 'bad_currency');

	$output = view('donateme', $lang, compact('token', 'supported_currencies', 'amount', 'currency', 'errors'));

	return $output;
}

