<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function paymentaccepted($lang) {
	head('title', translate('payment_accepted:title', $lang));
	head('robots', 'noindex, nofollow');

	$contact=true;
	$banner = build('banner', $lang, compact('contact'));

	$content = view('paymentaccepted', $lang);

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

