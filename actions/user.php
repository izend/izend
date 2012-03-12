<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function user($lang, $arglist=false) {
	$login = build('login', $lang);

	if ($login === true) {
		global $base_url;

		$next_page = (is_array($arglist) and isset($arglist['r'])) ? $arglist['r'] : url('home', $lang);

		return reload($base_url . $next_page);
	}

	$banner = build('banner', $lang);
	$content = view('user', $lang, compact('login'));

	head('title', translate('user:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

