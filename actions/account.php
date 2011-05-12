<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';

function account($lang) {
	if (!user_is_identified()) {
		return run('error/unauthorized', $lang);
	}

	head('title', translate('account:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$profile = build('profile', $lang);

	$content = view('account', $lang, compact('profile'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

