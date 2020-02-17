<?php

/**
 *
 * @copyright  2010-2020 izend.org
 * @version    8
 * @link       http://www.izend.org
 */

function user($lang, $arglist=false) {
	global $login_verified, $base_url;

	$login = build('login', $lang);

	if ($login === true) {
		return build('reloadonlogin', $lang, $arglist);
	}

	$banner = build('banner', $lang);
	$content = view('user', $lang, compact('login'));

	head('title', translate('user:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$output = layout('standard', compact('lang', 'banner', 'content'));

	return $output;
}

