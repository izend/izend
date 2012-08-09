<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function newsletteruser($lang) {
	head('title', translate('newslettter:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$subscribe = build('subscribe', $lang);

	$content = view('newsletteruser', $lang, compact('subscribe'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

