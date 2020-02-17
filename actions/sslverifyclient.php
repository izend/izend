<?php

/**
 *
 * @copyright  2013-2020 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function sslverifyclient($lang, $arglist=false) {
	global $base_url;

	if (empty($_SESSION['unverified_user'])) {
		return run('error/badrequest', $lang);
	}

	$user=$_SESSION['unverified_user'];
	unset($_SESSION['unverified_user']);

	if (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') {
		return run('error/unauthorized', $lang);
	}

	$_SESSION['user']=$user;

	$next_page=!empty($arglist['r']) ? $arglist['r'] : url('home', $lang);

	reload($base_url . $next_page);
}
