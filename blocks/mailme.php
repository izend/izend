<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'validatemail.php';
require_once 'ismailinjected.php';
require_once 'tokenid.php';

function mailme($lang) {
	$action='init';
	if (isset($_POST['mailme_send'])) {
		$action='send';
	}

	$mail=$subject=$message=$code=$token=false;

	if (isset($_SESSION['user']['mail'])) {
		$mail=$_SESSION['user']['mail'];
	}

	switch($action) {
		case 'send':
			if (isset($_POST['mailme_mail'])) {
				$mail=strtolower(strflat(strip_tags(readarg($_POST['mailme_mail'], true))));
			}
			if (isset($_POST['mailme_subject'])) {
				$subject=strip_tags(readarg($_POST['mailme_subject'], true));
			}
			if (isset($_POST['mailme_message'])) {
				$message=strip_tags(readarg($_POST['mailme_message'], true));
			}
			if (isset($_POST['mailme_code'])) {
				$code=readarg($_POST['mailme_code'], true);
			}
			if (isset($_POST['mailme_token'])) {
				$token=readarg($_POST['mailme_token']);
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

	$missing_subject=false;
	$bad_subject=false;

	$missing_message=false;

	$email_sent=false;
	$home_page=false;

	$internal_error=false;

	$with_captcha=true;

	switch($action) {
		case 'send':
			if (!isset($_SESSION['mailme_token']) or $token != $_SESSION['mailme_token']) {
				$bad_token=true;
			}

			if ($with_captcha) {
				if (!$code) {
					$missing_code=true;
					break;
				}
				$captcha=isset($_SESSION['captcha']['mailme']) ? $_SESSION['captcha']['mailme'] : false;
				if (!$captcha or $captcha != strtoupper($code)) {
					$bad_code=true;
					break;
				}
			}

			if (!$mail) {
				$missing_mail=true;
			}
			else if (!validate_mail($mail)) {
				$bad_mail=true;
			}
			if (!$subject) {
				$missing_subject=true;
			}
			else if (is_mail_injected($subject)) {
				$bad_subject=true;
			}
			if (!$message) {
				$missing_message=true;
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'send':
			if ($bad_token or $missing_code or $bad_code or $missing_mail or $bad_mail or $missing_subject or $bad_subject or $missing_message) {
				break;
			}

			require_once 'emailme.php';

			$r = emailme($subject, $message, $mail);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$subject=$message=false;

			global $home_action;

			$home_page=url($home_action, $lang);
			$email_sent=true;

			break;
		default:
			break;
	}

	$_SESSION['mailme_token'] = $token = token_id();

	$errors = compact('missing_code', 'bad_code', 'missing_mail', 'bad_mail', 'missing_subject', 'bad_subject', 'missing_message', 'internal_error');
	$infos = compact('email_sent', 'home_page');

	$output = view('mailme', $lang, compact('token', 'with_captcha', 'mail', 'subject', 'message', 'infos', 'errors', 'infos'));

	return $output;
}

