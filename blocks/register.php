<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    13
 * @link       http://www.izend.org
 */

require_once 'isusernameallowed.php';
require_once 'ismailallowed.php';
require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'validatemail.php';
require_once 'validatepassword.php';
require_once 'validateusername.php';
require_once 'validatewebsite.php';
require_once 'models/user.inc';

function register($lang) {
	$with_name=true;
	$with_website=false;
	$with_password=true;
	$with_newsletter=false;
	$with_captcha=true;
	$with_facebook=false;

	$with_info=false;

	if ($with_facebook) {
		require_once 'facebook.php';

		$facebook=facebook();
	}

	$action='init';
	if (isset($_POST['register_register'])) {
		$action='register';
	}

	$name=$mail=$website=$password=$confirmed=$code=$token=false;
	$locale=$lang;

	$newsletter=false;

	$lastname=$firstname=false;

	switch($action) {
		case 'init':
			if ($with_facebook) {
				$facebook_user=$facebook->getUser();
				if ($facebook_user) {
					try {
						$facebook_user_profile = $facebook->api('/me', 'GET');
						if (!empty($facebook_user_profile['email'])) {
							$mail=$facebook_user_profile['email'];
						}
						if ($with_info) {
							if (!empty($facebook_user_profile['last_name'])) {
								$lastname=$facebook_user_profile['last_name'];
							}
							if (!empty($facebook_user_profile['first_name'])) {
								$firstname=$facebook_user_profile['first_name'];
							}
						}
						if ($with_name) {
							if (!empty($facebook_user_profile['username'])) {
								$name=$facebook_user_profile['username'];
							}
						}
						if ($with_website) {
							if (!empty($facebook_user_profile['website'])) {
								$website=$facebook_user_profile['website'];
							}
						}
						$action='facebook';
					}
					catch(FacebookApiException $e) {
					}
					$facebook->destroySession();
				}
			}

			if ($with_newsletter) {
				$newsletter=true;
			}
			break;

		case 'register':
			if ($with_info) {
				if (isset($_POST['register_lastname'])) {
					$lastname=readarg($_POST['register_lastname']);
				}
				if (isset($_POST['register_firstname'])) {
					$firstname=readarg($_POST['register_firstname']);
				}
			}
			if ($with_name) {
				if (isset($_POST['register_name'])) {
					$name=strtolower(strflat(readarg($_POST['register_name'])));
				}
			}
			if (isset($_POST['register_mail'])) {
				$mail=strtolower(strflat(readarg($_POST['register_mail'])));
			}
			if ($with_website) {
				if (isset($_POST['register_website'])) {
					$website=strtolower(strflat(readarg($_POST['register_website'])));
				}
			}
			if ($with_password) {
				if (isset($_POST['register_password'])) {
					$password=readarg($_POST['register_password']);
				}
			}
			if ($with_newsletter) {
				if (isset($_POST['register_newsletter'])) {
					$newsletter=readarg($_POST['register_newsletter']) == 'on' ? true : false;
				}
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

	$missing_lastname=false;
	$missing_firstname=false;

	$missing_name=false;
	$bad_name=false;
	$duplicated_name=false;
	$missing_mail=false;
	$bad_mail=false;
	$duplicated_mail=false;
	$bad_website=false;
	$missing_password=false;
	$bad_password=false;
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

		case 'facebook':
			if ($with_info) {
				if (!$lastname) {
					$missing_lastname=true;
				}
				if (!$firstname) {
					$missing_firstname=true;
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
			if ($website) {
				if (!validate_website($website)) {
					$bad_website=true;
				}
				else {
					$website=normalize_website($website);
				}
			}
			if ($with_password) {
				if (!$password) {
					$missing_password=true;
				}
				else if (!validate_password($password)) {
					$bad_password=true;
				}
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
			if ($bad_token or $missing_code or $bad_code ) {
				break;
			}
		case 'facebook':
			if ($missing_name or $bad_name or $duplicated_name or $missing_mail or $bad_mail or $duplicated_mail or $bad_website or $missing_password or $bad_password or $missing_lastname or $missing_firstname or $missing_confirmation) {
				break;
			}

			if (!$with_password) {
				require_once 'newpassword.php';

				$password=newpassword();
			}

			$r = user_create($name, $password, $mail, $locale, $website);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$user_id = $r;

			if ($with_info) {
				user_set_info($user_id, $lastname, $firstname);
			}

			if ($newsletter) {
				require_once 'models/newsletter.inc';

				newsletter_create_user($mail, $locale);
			}

			$_SESSION['login'] = $name ? $name : $mail;

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

			$password=false;

			$account_created=true;
			$confirmed=false;

			break;
		default:
			break;
	}

	$connectbar=false;
	if ($with_facebook) {
		$scope=$with_website ? 'email, user_website' : 'email';
		$facebook_login_url=$facebook->getLoginUrl(compact('scope'));
		$connectbar=view('connect', $lang, compact('facebook_login_url'));
	}

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}
	else if ($account_created) {
		$user_page=url('user', $lang);
	}

	$_SESSION['register_token'] = $token = token_id();

	$errors = compact('missing_name', 'bad_name', 'missing_mail', 'bad_mail', 'bad_website', 'missing_confirmation', 'missing_code', 'bad_code', 'duplicated_name', 'duplicated_mail', 'missing_password', 'bad_password', 'missing_lastname', 'missing_firstname', 'internal_error', 'contact_page');
	$infos = compact('user_page');

	$output = view('register', $lang, compact('token', 'connectbar', 'with_captcha', 'with_name', 'with_website', 'with_password', 'with_newsletter', 'name', 'mail', 'website', 'password', 'with_info', 'lastname', 'firstname', 'newsletter', 'confirmed', 'account_created', 'errors', 'infos'));

	return $output;
}

