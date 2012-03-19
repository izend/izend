<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function admin($lang) {
	if (!user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	head('title', translate('admin:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	$usersearch = build('usersearch', $lang);
	$balance = build('balance', $lang);
	$content = view('admin', $lang, compact('usersearch', 'balance'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

