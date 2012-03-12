<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function paymentrejected($lang) {
	head('title', translate('payment_rejected:title', $lang));
	head('robots', 'noindex, nofollow');

	$contact=true;
	$banner = build('banner', $lang, compact('contact'));

	$contact_page=url('contact', $lang);
	$content = view('paymentrejected', $lang, compact('contact_page'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

