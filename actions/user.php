<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

require_once 'userprofile.php';

function user($lang, $arglist=false) {
	global $login_verified, $base_url;

	$login = build('login', $lang);

	if ($login === true) {
		$reload=false;

		if (!empty($arglist['r'])) {
			$r=@parse_url($arglist['r']);
			if ($r) {
				$reload=$r['path'];
			}
		}

		if ($login_verified and array_intersect($login_verified, user_profile('role'))) {
			$user=$_SESSION['user'];
			unset($_SESSION['user']);

			if (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') {
				return run('error/unauthorized', $lang);
			}

			$_SESSION['unverified_user']=$user;

			$next_page=url('sslverifyclient');
			if ($reload) {
				$next_page .= '?r=' . urlencode($reload);
			}
		}
		else {
			$next_page = $reload ? $reload : url('home', $lang);
		}

		return reload($base_url . $next_page);
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

