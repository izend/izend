<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'validateusername.php';
require_once 'validatemail.php';
require_once 'validatepassword.php';
require_once 'tokenid.php';

function login($lang) {
	$action='init';
	if (isset($_POST['login_enter'])) {
		$action='enter';
	}

	$login=$password=$code=$token=false;

	switch($action) {
		case 'enter':
			if (isset($_POST['login_login'])) {
				$login=strtolower(strflat(readarg($_POST['login_login'], true)));
			}
			if (isset($_POST['login_password'])) {
				$password=readarg($_POST['login_password'], true);
			}
			if (isset($_POST['login_code'])) {
				$code=readarg($_POST['login_code'], true);
			}
			if (isset($_POST['login_token'])) {
				$token=readarg($_POST['login_token']);
			}
			break;
		default:
			break;
	}

	$missing_code=false;
	$bad_code=false;

	$bad_token=false;

	$missing_login=false;
	$bad_login=false;
	$missing_password=false;
	$bad_password=false;
	$access_denied=false;

	$with_captcha=true;

	switch($action) {
		case 'enter':
			if (!isset($_SESSION['login_token']) or $token != $_SESSION['login_token']) {
				$bad_token=true;
				break;
			}

			if ($with_captcha) {
				if (!$code) {
					$missing_code=true;
					break;
				}
				$captcha=isset($_SESSION['captcha']['login']) ? $_SESSION['captcha']['login'] : false;
				if (!$captcha or $captcha != strtoupper($code)) {
					$bad_code=true;
					break;
				}
			}

			if (!$login) {
				$missing_login=true;
			}
			else if (!validate_user_name($login) and !validate_mail($login)) {
				$bad_login=true;
			}
			if (!$password) {
				$missing_password=true;
			}
			else if (!validate_password($password)) {
				$bad_password = true;
			}
			break;
		default:
			break;
	}

	switch($action) {
		case 'enter':
			if ($bad_token or $missing_code or $bad_code or $missing_login or $bad_login or $missing_password or $bad_password) {
				break;
			}

			require_once 'models/user.inc';

			$user = user_login($login, $password);

			if (!$user) {
				$access_denied=true;

				require_once 'log.php';
				write_log('enter.err', substr($login, 0, 40));

				break;
			}

			$user['ip'] = client_ip_address();
			$_SESSION['user'] = $user;

			unset($_SESSION['login_token']);

			return true;

		default:
			break;
	}

	$_SESSION['login_token'] = $token = token_id();

	$errors = compact('missing_code', 'bad_code', 'missing_login', 'bad_login', 'missing_password', 'bad_password', 'access_denied');

	$password_page=url('password', $lang);
	$newuser_page=url('newuser', $lang);

	$output = view('login', $lang, compact('token', 'with_captcha', 'password_page', 'newuser_page', 'login', 'errors'));

	return $output;
}

