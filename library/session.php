<?php

/**
 *
 * @copyright  2010-2015 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function session_reopen($name=false) {
	session_close();
	session_open($name);
}

function session_open($name=false) {
	if ($name) {
		session_name($name);
	}
	session_start();
}

function session_close() {
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}

	session_destroy();
	$_SESSION=array();
}

function session_regenerate() {
	session_regenerate_id(true);
}
