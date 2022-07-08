<?php

/**
 *
 * @copyright  2012-2022 izend.org
 * @version    9
 * @link       http://www.izend.org
 */

require_once 'ismailallowed.php';
require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'userprofile.php';
require_once 'validatemail.php';
require_once 'validatelocale.php';
require_once 'models/newsletter.inc';

function subscribe($lang) {
	global $system_languages;

	$with_locale=count($system_languages) > 1;	// true, false

	$with_captcha=true;

	$with_confirmation=true;

	$with_validation=true;

	$action='init';
	if (isset($_POST['subscribe_send'])) {
		$action='subscribe';
	}

	$confirmed=$code=$token=false;

	$user_mail=user_profile('mail');
	$user_locale=user_profile('locale');
	if (!$user_locale) {
		$user_locale=$lang;
	}

	$unsubscribe_page=false;

	switch($action) {
		case 'init':
			$unsubscribe_page=url('newsletterunsubscribe', $lang);
			break;

		case 'subscribe':
			if (isset($_POST['subscribe_mail'])) {
				$user_mail=strtolower(strflat(readarg($_POST['subscribe_mail'])));
			}
			if ($with_locale) {
				if (isset($_POST['subscribe_locale'])) {
					$user_locale=readarg($_POST['subscribe_locale']);
				}
			}
			if ($with_confirmation) {
				if (isset($_POST['subscribe_confirmed'])) {
					$confirmed=readarg($_POST['subscribe_confirmed']) == 'on' ? true : false;
				}
			}
			if (isset($_POST['subscribe_code'])) {
				$code=readarg($_POST['subscribe_code']);
			}
			if (isset($_POST['subscribe_token'])) {
				$token=readarg($_POST['subscribe_token']);
			}
			break;
		default:
			break;
	}

	$missing_code=false;
	$bad_code=false;

	$bad_token=false;

	$missing_mail=false;
	$bad_mail=false;
	$duplicated_mail=false;
	$missing_locale=false;
	$bad_locale=false;

	$missing_confirmation=false;

	$email_registered=false;
	$validation_mail=false;

	$internal_error=false;
	$contact_page=false;

	switch($action) {
		case 'subscribe':
			if (!isset($_SESSION['subscribe_token']) or $token != $_SESSION['subscribe_token']) {
				$bad_token=true;
			}

			if ($with_captcha) {
				if (!$code) {
					$missing_code=true;
					break;
				}
				$captcha=isset($_SESSION['captcha']['subscribe']) ? $_SESSION['captcha']['subscribe'] : false;
				if (!$captcha or $captcha != strtoupper($code)) {
					$bad_code=true;
					break;
				}
			}

			if (!$user_mail) {
				$missing_mail=true;
			}
			else if (!validate_mail($user_mail) or !is_mail_allowed($user_mail)) {
				$bad_mail=true;
			}
			else if (newsletter_get_user($user_mail)) {
				$duplicated_mail=true;
			}
			if ($with_locale) {
				if (!$user_locale) {
					$missing_locale=true;
				}
				else if (!validate_locale($user_locale)) {
					$bad_locale=true;
				}
			}
			if ($with_confirmation) {
				if (!$confirmed) {
					$missing_confirmation=true;
				}
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'subscribe':
			if ($bad_token or $missing_code or $bad_code or $missing_mail or $bad_mail or $duplicated_mail or $missing_locale or $bad_locale or $missing_confirmation) {
				break;
			}

			if ($with_validation) {
				require_once 'emailconfirmsubscribe.php';

				$r = emailconfirmsubscribe($user_mail, $user_locale);

				if (!$r) {
					$internal_error=true;
				}
				else {
					$validation_mail=$user_mail;

					$user_mail=false;
				}
			}
			else {
				$r = newsletter_create_user($user_mail, $user_locale);

				if (!$r) {
					$internal_error=true;
				}
				else {
					require_once 'serveripaddress.php';
					require_once 'emailme.php';

					global $sitename;

					$ip=server_ip_address();
					$timestamp=date('Y-m-d H:i:s');
					$subject = 'subscribe' . '@' . $sitename;
					$msg = $ip . ' ' . $timestamp . ' ' . $lang . ' ' . $user_mail;
					@emailme($subject, $msg);

					$email_registered=$user_mail;

					$user_mail=false;

					$unsubscribe_page=url('newsletterunsubscribe', $lang);
				}
			}

			$confirmed=false;

			break;

		default:
			break;
	}

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}

	$_SESSION['subscribe_token'] = $token = token_id();

	$errors = compact('missing_mail', 'bad_mail', 'missing_locale', 'bad_locale', 'duplicated_mail', 'missing_confirmation', 'missing_code', 'bad_code', 'internal_error', 'contact_page');
	$infos = compact('email_registered', 'validation_mail');

	$output = view('subscribe', $lang, compact('token', 'with_captcha', 'user_mail', 'with_locale', 'user_locale', 'with_confirmation', 'confirmed', 'unsubscribe_page', 'errors', 'infos'));

	return $output;
}

