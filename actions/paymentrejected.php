<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function paymentrejected($lang, $amount, $currency, $context) {
	head('title', translate('payment_rejected:title', $lang));
	head('robots', 'noindex');

	$contact=true;
	$banner = build('banner', $lang, compact('contact'));

	$contact_page=url('contact', $lang);
	$content = view('paymentrejected', $lang, compact('amount', 'currency', 'contact_page'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

