<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function session_reopen() {
	session_close();
	session_open();
}

function session_open() {
	session_start();
}

function session_close() {
	session_destroy();
	$_SESSION=array();
}

function session_check($lang=false) {
	if (!isset($_SESSION['user'])) {
		$login_page = url('user', $lang);
		header("Location: $login_page");
		die();
	}

	$now=time();
	if (isset($_SESSION['user']['lasttime'])) {
		$lasttime=$_SESSION['user']['lasttime'];
		if ($now-$lasttime > SESSION_EXPIRE*60) {
			$login_page = url('home', $lang);
			header("Location: $login_page");
			die();
		}
	}
	$_SESSION['user']['lasttime'] = $now;
}

