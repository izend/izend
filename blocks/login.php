<?php

/**
 *
 * @copyright  2010-2023 izend.org
 * @version    27
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'validatemail.php';
require_once 'validatepassword.php';
require_once 'validateusername.php';

function login($lang) {
	global $googleclientid;

	$with_name=true;
	$with_captcha=true;
	$with_google=$googleclientid ? true : false;;

	$with_newuser=true;
	$with_newpassword=true;

	$with_viewpassword=true;

	$login=$password=$code=$token=false;

	if (isset($_SESSION['login'])) {
		$login=$_SESSION['login'];
	}

	$action='init';
	if (isset($_POST['login_enter'])) {
		$action='enter';
	}

	switch($action) {
		case 'init':
			if ($with_google) {
				$credential=false;

				if (isset($_POST['credential'])) {
					$credential=$_POST['credential'];
				}

				if ($credential) {
					require_once 'verifyidtoken.php';

					$payload=verifyidtoken($credential, $client_id=$googleclientid);

					if ($payload) {
						$login=$payload['email'];
						$action='google';
					}
				}
			}

			break;

		case 'enter':
			if (isset($_POST['login_login'])) {
				$login=strtolower(strflat(readarg($_POST['login_login'])));
			}
			if (isset($_POST['login_password'])) {
				$password=readarg($_POST['login_password']);
			}
			if (isset($_POST['login_code'])) {
				$code=readarg($_POST['login_code']);
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
	$access_denied=false;
	$not_confirmed=false;

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

			if (!$password) {
				$missing_password=true;
			}
			/* fall thru */

		case 'google':
			if (!$login) {
				$missing_login=true;
			}
			else if (!(validate_user_name($login) or validate_mail($login))) {
				$bad_login=true;
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'enter':
		case 'google':
			if ($bad_token or $missing_code or $bad_code or $missing_login or $bad_login or $missing_password) {
				break;
			}

			require_once 'models/user.inc';

			$user = user_login($login, $password);

			if (!$user) {
				$access_denied=true;

				require_once 'log.php';

				write_log('enter.err', substr($login, 0, 100));

				$_SESSION['login']=$login;

				break;
			}

			if (!$user['confirmed']) {
				$not_confirmed=true;

				require_once 'emailconfirmuser.php';

				$r=emailconfirmuser($user['id'], $user['mail'], $user['locale']);

				if (!$r) {
					$internal_error=true;
					break;
				}
				break;
			}

			$user['ip'] = client_ip_address();

			if ($user['role'] and in_array('administrator', $user['role'])) {
				require_once 'serveripaddress.php';
				require_once 'emailme.php';

				global $sitename;

				$ip=server_ip_address();
				$timestamp=date('Y-m-d H:i:s');
				$subject = 'login' . '@' . $sitename;
				$msg = $ip . ' ' . $timestamp . ' ' . $user['id'] . ' ' . $lang . ' ' . $user['ip'];
				@emailme($subject, $msg);

				if ($action == 'google') {
					$access_denied=true;	// force login + password for an administrator
					break;
				}
			}

			session_regenerate();

			$_SESSION['user'] = $user;

			unset($_SESSION['login']);
			unset($_SESSION['login_token']);

			return true;

		default:
			break;
	}

	$user_page=$with_google ? url('user', $lang) : false;
	$password_page=$with_newpassword ? url('password', $lang) : false;
	$newuser_page=$with_newuser ? url('newuser', $lang) : false;

	$_SESSION['login_token'] = $token = token_id();

	$errors = compact('missing_code', 'bad_code', 'missing_login', 'bad_login', 'missing_password', 'access_denied', 'not_confirmed');

	$output = view('login', $lang, compact('token', 'with_google', 'user_page', 'with_captcha', 'with_name', 'with_viewpassword', 'password_page', 'newuser_page', 'login', 'errors'));

	return $output;
}

