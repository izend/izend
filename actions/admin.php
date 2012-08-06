<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'paypal.inc';
require_once 'userhasrole.php';

function admin($lang) {
	global $paypal_username, $paypal_password, $paypal_signature;
	global $newsletter_thread;

	if (!user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	head('title', translate('admin:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$newsletter_page=$newsletter_thread ? url('newsletter', $lang) : false;

	$balance=false;
	if (!(empty($paypal_username) or empty($paypal_password) or empty($paypal_signature))) {
		$balance = build('balance', $lang);
	}
	$upload = build('upload', $lang);
	$usersearch = build('usersearch', $lang);
	$content = view('admin', $lang, compact('newsletter_page', 'balance', 'usersearch', 'upload'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

