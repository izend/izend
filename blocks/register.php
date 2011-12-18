<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

require_once 'isusernameallowed.php';
require_once 'ismailallowed.php';
require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'validatemail.php';
require_once 'validateusername.php';
require_once 'models/user.inc';

function register($lang) {
	$with_name=true;
	$with_captcha=true;

	$action='init';
	if (isset($_POST['register_register'])) {
		$action='register';
	}

	$name=$mail=$confirmed=$code=$token=false;
	$locale=$lang;

	switch($action) {
		case 'register':
			if (isset($_POST['register_name'])) {
				$name=strtolower(strflat(readarg($_POST['register_name'])));
			}
			if (isset($_POST['register_mail'])) {
				$mail=strtolower(strflat(readarg($_POST['register_mail'])));
			}
			if (isset($_POST['register_confirmed'])) {
				$confirmed=readarg($_POST['register_confirmed']) == 'on' ? true : false;
			}
			if (isset($_POST['register_code'])) {
				$code=readarg($_POST['register_code']);
			}
			if (isset($_POST['register_token'])) {
				$token=readarg($_POST['register_token']);
			}
			break;
		default:
			break;
	}

	$missing_code=false;
	$bad_code=false;

	$bad_token=false;

	$missing_name=false;
	$bad_name=false;
	$duplicated_name=false;
	$missing_mail=false;
	$bad_mail=false;
	$duplicated_mail=false;
	$missing_confirmation=false;

	$account_created=false;
	$user_page=false;

	$internal_error=false;
	$contact_page=false;

	switch($action) {
		case 'register':
			if (!isset($_SESSION['register_token']) or $token != $_SESSION['register_token']) {
				$bad_token=true;
			}

			if ($with_captcha) {
				if (!$code) {
					$missing_code=true;
					break;
				}
				$captcha=isset($_SESSION['captcha']['register']) ? $_SESSION['captcha']['register'] : false;
				if (!$captcha or $captcha != strtoupper($code)) {
					$bad_code=true;
					break;
				}
			}

			if ($with_name) {
				if (!$name) {
					$missing_name=true;
				}
				else if (!validate_user_name($name) or !is_user_name_allowed($name)) {
					$bad_name=true;
				}
				else if (!user_check_name($name)) {
					$duplicated_name=true;
				}
			}
			if (!$mail) {
				$missing_mail=true;
			}
			else if (!validate_mail($mail) or !is_mail_allowed($mail)) {
				$bad_mail=true;
			}
			else if (!user_check_mail($mail)) {
				$duplicated_mail=true;
			}
			if (!$confirmed) {
				$missing_confirmation=true;
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'register':
			if ($bad_token or $missing_code or $bad_code or $missing_name or $bad_name or $duplicated_name or $missing_mail or $bad_mail or $duplicated_mail or $missing_confirmation) {
				break;
			}

			require_once 'newpassword.php';

			$password=newpassword();

			$r = user_create($name, $password, $mail, $locale);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$user_id = $r;

			require_once 'emailcrypto.php';

			global $sitename, $webmaster;

			$to=$mail;

			$subject = translate('email:new_user_subject', $lang);
			$msg = translate('email:new_user_text', $lang) . "\n\n" . translate('email:salutations', $lang);
			if (!emailcrypto($msg, $password, $to, $subject, $webmaster)) {
				$internal_error=true;
				break;
			}

			require_once 'emailme.php';

			global $sitename;

			$timestamp=strftime('%d-%m-%Y %H:%M:%S', time());
			$subject = 'new_account' . '@' . $sitename;
			$msg = $timestamp . ' ' . $user_id . ' ' . $lang . ' ' . $mail;
			emailme($subject, $msg);

			$account_created=true;
			$confirmed=false;

			break;
		default:
			break;
	}

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}
	else if ($account_created) {
		$user_page=url('user', $lang);
	}

	$_SESSION['register_token'] = $token = token_id();

	$errors = compact('missing_name', 'bad_name', 'missing_mail', 'bad_mail', 'missing_confirmation', 'missing_code', 'bad_code', 'duplicated_name', 'duplicated_mail', 'internal_error', 'contact_page');
	$infos = compact('user_page');

	$output = view('register', $lang, compact('token', 'with_captcha', 'with_name', 'name', 'mail', 'confirmed', 'account_created', 'errors', 'infos'));

	return $output;
}

