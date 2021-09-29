<?php

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    9
 * @link       http://www.izend.org
 */

function user($lang, $arglist=false) {
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

