<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'userprofile.php';

function user($lang, $arglist=false) {
	global $login_verified, $base_url;

	$login = build('login', $lang);

	if ($login === true) {
		$r=!empty($arglist['r']) ? $arglist['r'] : false;

		if ($login_verified and array_intersect($login_verified, user_profile('role'))) {
			$user=$_SESSION['user'];
			unset($_SESSION['user']);

			if (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') {
				return run('error/unauthorized', $lang);
			}

			$_SESSION['unverified_user']=$user;

			$next_page=url('sslverifyclient');
			if ($r) {
				$next_page .= '?r=' . $r;
			}
		}
		else {
			$next_page = $r ? $r : url('home', $lang);
		}

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

