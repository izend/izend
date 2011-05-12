<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function serviceunavailable($lang, $arglist) {
	list($closing_time, $opening_time) = $arglist;

	head('title', translate('http_service_unavailable:title', $lang));
	head('robots', 'noindex, nofollow');

	$contact=true;
	$banner = build('banner', $lang, compact('contact'));

	$content = view('error/serviceunavailable', $lang, compact('closing_time', 'opening_time'));

	$output = layout('standard', compact('header', 'banner', 'content'));

	header('HTTP/1.1 503 Service Unavailable');

	return $output;
}

