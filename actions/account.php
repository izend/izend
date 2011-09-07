<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';
require_once 'userprofile.php';

function account($lang) {
	if (!user_is_identified()) {
		return run('error/unauthorized', $lang);
	}

	head('title', translate('account:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$user_id = user_profile('id');
	$useredit = build('useredit', $lang, $user_id, false);

	$content = view('account', $lang, compact('useredit'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

