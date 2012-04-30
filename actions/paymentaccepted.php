<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function paymentaccepted($lang, $amount, $currency) {
	head('title', translate('payment_accepted:title', $lang));
	head('robots', 'noindex, nofollow');

	$contact=true;
	$banner = build('banner', $lang, compact('contact'));

	$content = view('paymentaccepted', $lang, compact('amount', 'currency', 'contact_page'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

