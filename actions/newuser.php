<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function newuser($lang) {
	global $with_toolbar;

	$register = build('register', $lang);

	head('title', translate('newuser:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$admin=true;
	$banner = build('banner', $lang, $with_toolbar ? false : compact('admin'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('admin')) : false;

	$content = view('newuser', $lang, compact('register'));

	$output = layout('standard', compact('lang', 'toolbar', 'banner', 'content'));

	return $output;
}

