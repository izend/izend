<?php

/**
 *
 * @copyright  2017 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/user.inc';

function confirmnewuser($lang, $arglist) {
	head('title', translate('newuser:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$banner = build('banner', $lang);

	list($timestamp, $user_id)=$arglist;

	$bad_user=false;
	$bad_time=false;

	$account_confirmed=false;
	$user_page=false;

	$internal_error=false;
	$contact_page=false;

	$user=user_get($user_id);

	if (!$user) {
		$bad_user=true;
	}
	else if ($user['user_confirmed']) {
		$account_confirmed=true;
	}
	else if (time() - $timestamp > 3600) {
		$bad_time=true;

		require_once 'emailconfirmuser.php';

		$r=emailconfirmuser($user_id, $user['user_locale']);

		if (!$r) {
			$internal_error=true;
		}
	}
	else {
		$r = user_confirm($user_id);

		if (!$r) {
			$internal_error=true;
		}
		else {
			$account_confirmed=true;

			$_SESSION['login'] = $user['user_name'] ? $user['user_name'] : $user['user_mail'];
		}
	}

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}
	else if ($account_confirmed) {
		$user_page=url('user', $lang);
	}

	$errors = compact('bad_user', 'bad_time', 'internal_error', 'contact_page');
	$infos = compact('user_page');

	$content = view('confirmnewuser', $lang, compact('account_confirmed', 'errors', 'infos'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

