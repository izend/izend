<?php

/**
 *
 * @copyright  2013-2018 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function newsletterunsubscribe($lang) {
	head('title', translate('newsletter:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$banner = build('banner', $lang);

	$unsubscribe = build('unsubscribe', $lang);

	$content = view('newsletterunsubscribe', $lang, compact('unsubscribe'));

	$output = layout('standard', compact('lang', 'banner', 'content'));

	return $output;
}

