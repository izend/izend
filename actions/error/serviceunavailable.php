<?php

/**
 *
 * @copyright  2010-2016 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function serviceunavailable($lang, $arglist=false) {
	head('title', translate('http_service_unavailable:title', $lang));
	head('robots', 'noindex, nofollow');

	$contact=false;
	$banner = build('banner', $lang, compact('contact'));

	$closing_time=true;
	$opening_time=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$closing_time=$arglist[0];
		}
		if (isset($arglist[1])) {
			$opening_time=$arglist[1];
		}
	}

	$content = view('error/serviceunavailable', $lang, compact('closing_time', 'opening_time'));

	$output = layout('standard', compact('banner', 'content'));

	header('HTTP/1.1 503 Service Unavailable');

	return $output;
}

