<?php

/**
 *
 * @copyright  2020 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userprofile.php';

function reloadonlogin($lang, $arglist=false) {
	global $login_verified, $base_url;

	$reload_path=false;

	if (!empty($arglist['r'])) {
		$r=@parse_url($arglist['r']);
		if ($r) {
			$reload_path=$r['path'];
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
		if ($reload_path) {
			$next_page .= '?r=' . urlencode($reload_path);
		}
	}
	else {
		$next_page = $reload_path ? $reload_path : url('home', $lang);
	}

	reload($base_url . $next_page);
}

