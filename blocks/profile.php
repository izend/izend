<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'validateusername.php';
require_once 'validatemail.php';
require_once 'validatelocale.php';
require_once 'validatepassword.php';
require_once 'tokenid.php';

function profile($lang) {
	$token=false;
	if (isset($_POST['profile_token'])) {
		$token=readarg($_POST['profile_token']);
	}

	$action='init';
	if (isset($_POST['profile_change'])) {
		$action='change';
	}

	$name=$mail=$locale=$newpassword=$password=false;

	$oldname=$_SESSION['user']['name'];
	$oldmail=$_SESSION['user']['mail'];
	$oldlocale=$_SESSION['user']['locale'];

	$name=$oldname;
	$mail=$oldmail;
	$locale=$oldlocale;

	switch($action) {
		case 'change':
			if (isset($_POST['profile_name'])) {
				$name=strtolower(strflat(readarg($_POST['profile_name'])));
			}
			if (isset($_POST['profile_mail'])) {
				$mail=strtolower(strflat(readarg($_POST['profile_mail'])));
			}
			if (isset($_POST['profile_locale'])) {
				$locale=readarg($_POST['profile_locale']);
			}
			if (isset($_POST['profile_newpassword'])) {
				$newpassword=readarg($_POST['profile_newpassword']);
			}
			if (isset($_POST['profile_password'])) {
				$password=readarg($_POST['profile_password']);
			}
			break;
		default:
			break;
	}

	$bad_name=false;
	$bad_mail=false;
	$bad_locale=false;

	$missing_password=false;
	$wrong_password=false;

	$same_name=false;
	$same_mail=false;
	$same_locale=false;

	$duplicate_name=false;
	$duplicate_mail=false;

	$weak_password=false;

	$internal_error=false;
	$contact_page=false;

	$profile_changed=false;
	$password_changed=false;
	$home_page=false;

	switch($action) {
		case 'change':
			if (!$name) {
				$name = $oldname;
				$same_name = true;
			}
			else if ($name == $oldname) {
				$same_name = true;
			}
			else if (!validate_user_name($name)) {
				$bad_name=true;
			}
			if (!$mail) {
				$mail=$oldmail;
				$same_mail = true;
			}
			else if ($mail == $oldmail) {
				$same_mail = true;
			}
			else if (!validate_mail($mail)) {
				$bad_mail=true;
			}
			if (!$locale) {
				$locale = $oldlocale;
				$same_locale = true;
			}
			else if ($locale == $oldlocale) {
				$same_locale = true;
			}
			else if (!validate_locale($locale)) {
				$bad_locale=true;
			}

			if ($newpassword) {
				if (!validate_password($newpassword)) {
					$weak_password = true;
				}
			}

			if (!$password) {
				$missing_password=true;
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'change':
			if ($same_name and $same_mail and $same_locale and !$newpassword) {
				$missing_password=false;
				break;
			}

			if ($bad_name or $bad_mail or $bad_locale or $weak_password or $missing_password) {
				break;
			}

			require_once 'models/user.inc';

			$user_id = $_SESSION['user']['id'];
			$user_password = $_SESSION['user']['password'];

			if (md5($password) != $user_password) {
				$wrong_password=true;
				break;
			}

			if ($newpassword) {
				if (md5($newpassword) != $user_password) {
					if (!user_set_newpassword($user_id, $newpassword)) {
						$internal_error=true;
						break;
					}
					$password_changed=true;
				}
			}

			if ($same_name and $same_mail and $same_locale) {
				break;
			}

			if (!$same_name) {
				$r = user_check_name($name);

				if (!$r) {
					$duplicate_name=true;
					break;
				}
			}

			if (!$same_mail) {
				$r = user_check_mail($mail);

				if (!$r) {
					$duplicate_mail=true;
					break;
				}
			}

			$r = user_set($user_id, $name, $mail, $locale);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$_SESSION['user']['name'] = $name;
			$_SESSION['user']['mail'] = $mail;
			$_SESSION['user']['locale'] = $locale;

			if ($internal_error) {
				$contact_page=url('contact', $lang);
			}
			$home_page=url('home', $lang);
			$profile_changed=true;
			$password=false;

			break;
		default:
			break;
	}

	$focus=false;
	if ($bad_name or $duplicate_name) {
		$focus='#profile_name';
	}
	else if ($bad_mail or $duplicate_mail) {
		$focus='#profile_mail';
	}
	else if ($weak_password) {
		$focus='#profile_newpassword';
	}
	else if ($weak_password) {
		$focus='#profile_newpassword';
	}
	else if ($missing_password or $wrong_password) {
		$focus='#profile_password';
	}

	if (!$token) {
		$_SESSION['profile_token'] = $token = token_id();
	}

	$errors = compact('bad_name', 'bad_mail', 'bad_locale', 'duplicate_name', 'duplicate_mail', 'weak_password', 'missing_password', 'wrong_password', 'internal_error', 'contact_page');
	$infos = compact('profile_changed', 'password_changed', 'home_page');

	$output = view('profile', $lang, compact('token', 'name', 'mail', 'locale', 'newpassword', 'password', 'errors', 'infos', 'focus'));

	return $output;
}

