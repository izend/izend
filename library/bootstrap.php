<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

require_once 'session.php';
require_once 'unsetglobals.php';
require_once 'validatehostname.php';

function bootstrap() {
	global $base_url, $base_path, $base_root;
	global $db_url, $session_name, $login_lifetime;

	if (isset($_SERVER['HTTP_HOST'])) {
		$_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
		if (!validate_host_name($_SERVER['HTTP_HOST'])) {
			header('HTTP/1.1 400 Bad Request');
			exit;
		}
	}
	else {
		$_SERVER['HTTP_HOST'] = '';
	}

	unset_globals();

	@include 'settings.inc';
	@include 'config.inc';
	@include 'db.inc';

	if ($db_url == 'mysql://username:password@localhost/databasename') {
		$db_url = false;
	}

	if ($db_url) {
		require_once 'pdo.php';
		db_connect($db_url);
	}

	if ($base_url) {
		$base_url = trim($base_url, '/');

		$url = parse_url($base_url);

		if (!isset($url['path'])) {
			$url['path'] = '';
		}

		$base_path = $url['path'];
		$base_root = substr($base_url, 0, strlen($base_url) - strlen($base_path));
	}
	else {
		$base_root = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';

		$base_url = $base_root .= '://'. $_SERVER['HTTP_HOST'];

		if ($dir = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/')) {
			$base_path = '/' . $dir;
			$base_url .= $base_path;
		}
		else {
			$base_path = '';
		}
	}

	if (!$session_name) {
		list( , $session_name) = explode('://', $base_url, 2);
		$session_name = 'izend@' . $session_name;

		if (ini_get('session.cookie_secure')) {
			$session_name .= 'SSL';
		}
	}

	session_open(md5($session_name));

	if (isset($_SESSION['user']['lasttime'])) {
		$now = time();
		if ($now - $_SESSION['user']['lasttime'] > $login_lifetime) {
			unset($_SESSION['user']);
		}
		else {
			$_SESSION['user']['lasttime'] = $now;
		}
	}

	if (!empty($_SESSION['user']['timezone'])) {
		date_default_timezone_set($_SESSION['user']['timezone']);
	}
}

