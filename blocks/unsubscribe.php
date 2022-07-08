<?php

/**
 *
 * @copyright  2013-2022 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'ismailallowed.php';
require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'urlencrypt.php';
require_once 'userprofile.php';
require_once 'validatemail.php';
require_once 'models/newsletter.inc';

function unsubscribe($lang) {
	$with_captcha=true;

	$with_confirmation=true;

	$with_validation=true;

	$action='init';
	if (isset($_POST['unsubscribe_send'])) {
		$action='unsubscribe';
	}

	$confirmed=$code=$token=false;

	$user_mail=user_profile('mail');

	$subscribe_page=false;

	switch($action) {
		case 'init':
			$subscribe_page=url('newslettersubscribe', $lang);
			break;

		case 'unsubscribe':
			if (isset($_POST['unsubscribe_mail'])) {
				$user_mail=strtolower(strflat(readarg($_POST['unsubscribe_mail'])));
			}
			if ($with_confirmation) {
				if (isset($_POST['unsubscribe_confirmed'])) {
					$confirmed=readarg($_POST['unsubscribe_confirmed']) == 'on' ? true : false;
				}
			}
			if (isset($_POST['unsubscribe_code'])) {
				$code=readarg($_POST['unsubscribe_code']);
			}
			if (isset($_POST['unsubscribe_token'])) {
				$token=readarg($_POST['unsubscribe_token']);
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
	$unknown_mail=false;

	$missing_confirmation=false;

	$email_unregistered=false;
	$validation_mail=false;

	$internal_error=false;
	$contact_page=false;

	switch($action) {
		case 'unsubscribe':
			if (!isset($_SESSION['unsubscribe_token']) or $token != $_SESSION['unsubscribe_token']) {
				$bad_token=true;
			}

			if ($with_captcha) {
				if (!$code) {
					$missing_code=true;
					break;
				}
				$captcha=isset($_SESSION['captcha']['unsubscribe']) ? $_SESSION['captcha']['unsubscribe'] : false;
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
			else if (!newsletter_get_user($user_mail)) {
				$unknown_mail=true;
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
		case 'unsubscribe':
			if ($bad_token or $missing_code or $bad_code or $missing_mail or $bad_mail or $unknown_mail or $missing_confirmation) {
				break;
			}

			if ($with_validation) {
				require_once 'emailconfirmunsubscribe.php';

				$r = emailconfirmunsubscribe($user_mail, $lang);

				if (!$r) {
					$internal_error=true;
				}
				else {
					$validation_mail=$user_mail;

					$user_mail=false;
				}
			}
			else {
				$r = newsletter_delete_user($user_mail);

				if (!$r) {
					$internal_error=true;
				}
				else {
					require_once 'serveripaddress.php';
					require_once 'emailme.php';

					global $sitename;

					$ip=server_ip_address();
					$timestamp=date('Y-m-d H:i:s');
					$subject = 'unsubscribe' . '@' . $sitename;
					$msg = $ip . ' ' . $timestamp . ' ' . $lang . ' ' . $user_mail;
					@emailme($subject, $msg);

					$email_unregistered=$user_mail;

					$user_mail=false;

					$subscribe_page=url('newslettersubscribe', $lang);
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

	$_SESSION['unsubscribe_token'] = $token = token_id();

	$errors = compact('missing_mail', 'bad_mail', 'unknown_mail', 'missing_confirmation', 'missing_code', 'bad_code', 'internal_error', 'contact_page');
	$infos = compact('email_unregistered', 'validation_mail');

	$output = view('unsubscribe', $lang, compact('token', 'with_captcha', 'user_mail', 'with_confirmation', 'confirmed', 'subscribe_page', 'errors', 'infos'));

	return $output;
}
