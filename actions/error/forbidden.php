<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function forbidden($lang) {
	head('title', translate('http_forbidden:title', $lang));
	head('robots', 'noindex');

	$contact=true;
	$banner = build('banner', $lang, compact('contact'));

	$contact_page=url('contact', $lang);
	$content = view('error/forbidden', $lang, compact('contact_page'));

	$output = layout('standard', compact('banner', 'content'));

	header('HTTP/1.1 403 Forbidden');

	return $output;
}

