<?php

/**
 *
 * @copyright  2012-2013 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function newslettersubscribe($lang) {
	head('title', translate('newsletter:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$subscribe = build('subscribe', $lang);

	$content = view('newslettersubscribe', $lang, compact('subscribe'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

