<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'paypal.inc';
require_once 'userisidentified.php';

function donation($lang) {
	global $paypal_username, $paypal_password, $paypal_signature;

	if (empty($paypal_username) or empty($paypal_password) or empty($paypal_signature)) {
		return run('error/notimplemented', $lang);
	}

	if (!user_is_identified()) {
		return run('user', $lang, array('r' => url('donation', $lang)));
	}

	head('title', translate('donation:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$contact=true;
	$banner = build('banner', $lang, compact('contact'));

	$donateme = build('donateme', $lang);

	$content = view('donation', $lang, compact('donateme'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

