<?php

/**
 *
 * @copyright  2013 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function newsletterunsubscribe($lang) {
	head('title', translate('newsletter:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$unsubscribe = build('unsubscribe', $lang);

	$content = view('newsletterunsubscribe', $lang, compact('unsubscribe'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

