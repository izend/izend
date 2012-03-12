<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'paypal.inc';

function donate($lang) {
	global $paypal_username, $paypal_password, $paypal_signature;

	if (empty($paypal_username) or empty($paypal_password) or empty($paypal_signature)) {
		return false;
	}

	$donation_page=url('donation', $lang);

	$output = view('donate', $lang, compact('donation_page'));

	return $output;
}

