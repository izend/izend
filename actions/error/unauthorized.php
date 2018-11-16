<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function unauthorized($lang) {
	head('title', translate('http_unauthorized:title', $lang));
	head('robots', 'noindex');

	$contact=$account=true;
	$banner = build('banner', $lang, compact('contact', 'account'));

	$contact_page=url('contact', $lang);
	$content = view('error/unauthorized', $lang, compact('contact_page'));

	$output = layout('standard', compact('lang', 'banner', 'content'));

	header('HTTP/1.1 401 Unauthorized');

	return $output;
}

