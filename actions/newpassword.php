<?php

/**
 *
 * @copyright  2022 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'userisidentified.php';
require_once 'userprofile.php';

function newpassword($lang) {
	if (!user_is_identified() or user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	head('title', translate('password:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$banner = build('banner', $lang);

	$user_id = user_profile('id');
	$userpassword = build('userpassword', $lang, $user_id);

	$content = view('newpassword', $lang, compact('userpassword'));

	$output = layout('standard', compact('lang', 'banner', 'content'));

	return $output;
}

