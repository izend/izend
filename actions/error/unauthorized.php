<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function unauthorized($lang) {
	head('title', translate('http_unauthorized:title', $lang));
	head('robots', 'noindex, nofollow');

	$contact=true;
	$banner = build('banner', $lang, compact('contact'));

	$contact_page=url('contact', $lang);
	$content = view('error/unauthorized', $lang, compact('contact_page'));

	$output = layout('standard', compact('header', 'banner', 'content'));

	header('HTTP/1.1 401 Unauthorized');

	return $output;
}

