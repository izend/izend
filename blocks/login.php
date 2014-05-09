<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    17
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'validatemail.php';
require_once 'validatepassword.php';
require_once 'validateusername.php';

function login($lang) {
	$with_name=true;
	$with_captcha=true;
	$with_facebook=false;

	if ($with_facebook) {
		require_once 'facebook.php';

		$facebook=facebook();
	}

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
			if ($with_facebook) {
				$facebook_user=$facebook->getUser();
				if ($facebook_user) {
					try {
						$facebook_user_profile = $facebook->api('/me', 'GET');
						if (!empty($facebook_user_profile['email'])) {
							$login=$facebook_user_profile['email'];
						}
						$action='facebook';
					}
					catch(FacebookApiException $e) {
					}
					$facebook->destroySession();
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

		case 'facebook':
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
		case 'facebook':
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

			$user['ip'] = client_ip_address();

			if (in_array('administrator', $user['role'])) {
				require_once 'serveripaddress.php';
				require_once 'emailme.php';

				global $sitename;

				$ip=server_ip_address();
				$timestamp=strftime('%d-%m-%Y %H:%M:%S', time());
				$subject = 'login' . '@' . $sitename;
				$msg = $ip . ' ' . $timestamp . ' ' . $user['id'] . ' ' . $lang . ' ' . $user['ip'];
				emailme($subject, $msg);

				if ($action == 'facebook') {
					$access_denied=true;
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

	$connectbar=false;
	if ($with_facebook) {
		$scope='email';
		$facebook_login_url=$facebook->getLoginUrl(compact('scope'));
		$connectbar=view('connect', $lang, compact('facebook_login_url'));
	}

	$password_page=url('password', $lang);
	$newuser_page=url('newuser', $lang);

	$_SESSION['login_token'] = $token = token_id();

	$errors = compact('missing_code', 'bad_code', 'missing_login', 'bad_login', 'missing_password', 'access_denied');

	$output = view('login', $lang, compact('token', 'connectbar', 'with_captcha', 'with_name', 'password_page', 'newuser_page', 'login', 'errors'));

	return $output;
}

