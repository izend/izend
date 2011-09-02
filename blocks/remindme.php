<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'validateusername.php';
require_once 'validatemail.php';
require_once 'isusernameallowed.php';
require_once 'ismailallowed.php';
require_once 'tokenid.php';

function remindme($lang, $login=false) {
	$action='init';
	if (isset($_POST['remindme_send'])) {
		$action='remindme';
	}

	$login=$confirmed=$code=$token=false;

	if (isset($_SESSION['form']['login'])) {
		$login=$_SESSION['form']['login']['login'];
	}

	switch($action) {
		case 'remindme':
			if (isset($_POST['remindme_login'])) {
				$login=strtolower(strflat(readarg($_POST['remindme_login'])));
			}
			if (isset($_POST['remindme_confirmed'])) {
				$confirmed=readarg($_POST['remindme_confirmed']) == 'on' ? true : false;
			}
			if (isset($_POST['remindme_code'])) {
				$code=readarg($_POST['remindme_code']);
			}
			if (isset($_POST['remindme_token'])) {
				$token=readarg($_POST['remindme_token']);
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
	$missing_confirmation=false;

	$email_sent=false;
	$user_page=false;

	$internal_error=false;
	$contact_page=false;

	$with_captcha=true;

	switch($action) {
		case 'remindme':
			if (!isset($_SESSION['remindme_token']) or $token != $_SESSION['remindme_token']) {
				$bad_token=true;
			}

			if ($with_captcha) {
				if (!$code) {
					$missing_code=true;
					break;
				}
				$captcha=isset($_SESSION['captcha']['remindme']) ? $_SESSION['captcha']['remindme'] : false;
				if (!$captcha or $captcha != strtoupper($code)) {
					$bad_code=true;
					break;
				}
			}

			if (!$login) {
				$missing_login=true;
			}
			else if ((!validate_user_name($login) or !is_user_name_allowed($login)) and (!validate_mail($login) or !is_mail_allowed($login))) {
				$bad_login=true;
			}
			if (!$confirmed) {
				$missing_confirmation=true;
			}
			if (!$code) {
				$missing_code=true;
			}
			break;
		default:
			break;
	}

	switch($action) {
		case 'remindme':
			if ($bad_token or $missing_code or $bad_code or $missing_login or $bad_login or $missing_confirmation) {
				break;
			}

			require_once 'models/user.inc';

			$user_id = user_find($login);

			if (!$user_id) {
				$bad_login=true;

				require_once 'log.php';
				write_log('password.err', substr($login, 0, 40));

				break;
			}

			$user = user_get($user_id);

			if (!$user) {
				$internal_error=true;
				break;
			}

			require_once 'newpassword.php';

			$newpassword=newpassword();

			if (!user_set_newpassword($user_id, $newpassword)) {
				$internal_error=true;
				break;
			}

			require_once 'emailcrypto.php';

			global $sitename, $webmaster;

			$to=$user['user_mail'];

			$subject = translate('email:new_password_subject', $lang);
			$msg = translate('email:new_password_text', $lang) . "\n\n" . translate('email:salutations', $lang);
			if (!emailcrypto($msg, $newpassword, $to, $subject, $webmaster)) {
				$internal_error=true;
			}
			else {
				$_SESSION['form']['login']['login'] = $login;
				$email_sent=$to;
			}

			$confirmed=false;

			break;
		default:
			break;
	}

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}
	else if ($email_sent) {
		$user_page=url('user', $lang);
	}

	$_SESSION['remindme_token'] = $token = token_id();

	$errors = compact('missing_login', 'bad_login', 'missing_confirmation', 'missing_code', 'bad_code', 'internal_error', 'contact_page');
	$infos = compact('email_sent', 'user_page');

	$output = view('remindme', $lang, compact('token', 'with_captcha', 'login', 'confirmed', 'errors', 'infos'));

	return $output;
}

