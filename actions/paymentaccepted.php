<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function paymentaccepted($lang, $amount, $currency, $context) {
	head('title', translate('payment_accepted:title', $lang));
	head('robots', 'noindex');

	$contact=true;
	$banner = build('banner', $lang, compact('contact'));

	$content = view('paymentaccepted', $lang, compact('amount', 'currency'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

