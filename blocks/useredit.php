<?php

/**
 *
 * @copyright  2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'userprofile.php';
require_once 'validateusername.php';
require_once 'validatemail.php';
require_once 'validatelocale.php';
require_once 'validatepassword.php';
require_once 'validatewebsite.php';
require_once 'models/user.inc';

function useredit($lang, $user_id, $administrator=false) {
	$with_status=($user_id != 1 and $administrator == true);
	$with_delete=($user_id != 1 and $user_id != user_profile('id'));
	$with_newpassword=false; 	// ($user_id != 1 and $user_id == user_profile('id'));

	$confirmed=false;

	$action='init';
	if (isset($_POST['useredit_modify'])) {
		$action='modify';
	}
	if ($with_newpassword) {
		if (isset($_POST['useredit_change'])) {
			$action='change';
		}
	}
	if ($with_delete) {
		if (isset($_POST['useredit_delete'])) {
			$action='delete';
		}
		else if (isset($_POST['useredit_confirmdelete'])) {
			$action='delete';
			$confirmed=true;
		}
		else if (isset($_POST['useredit_cancel'])) {
			$action='cancel';
		}
	}

	$user_name=$user_mail=$user_locale=false;
	$user_website=false;

	$user_active=$user_banned=false;
	$user_accessed=false;

	$user_newpassword=false;

	$token=false;

	switch($action) {
		case 'init':
		case 'reset':
			$r = user_get($user_id);
			if ($r) {
				extract($r);
			}
			$user_newpassword=false;
			break;
		case 'modify':
		case 'change':
		case 'delete':
		case 'cancel':
			if (isset($_POST['useredit_name'])) {
				$user_name=strtolower(strflat(readarg($_POST['useredit_name'])))                                                                                                      ;
			}
			if (isset($_POST['useredit_mail'])) {
				$user_mail=strtolower(strflat(readarg($_POST['useredit_mail'])));
			}
			if (isset($_POST['useredit_website'])) {
				$user_website=strtolower(strflat(readarg($_POST['useredit_website'])));
			}
			if (isset($_POST['useredit_locale'])) {
				$user_locale=readarg($_POST['useredit_locale']);
			}
			if ($with_status) {
				if (isset($_POST['useredit_active'])) {
					$user_active=readarg($_POST['useredit_active']) == 'on';
				}
				if (isset($_POST['useredit_banned'])) {
					$user_banned=readarg($_POST['useredit_banned']) == 'on';
				}
				if (isset($_POST['useredit_accessed'])) {
					$user_accessed=(int)readarg($_POST['useredit_accessed']);
				}
			}
			if ($with_newpassword) {
				if (isset($_POST['useredit_newpassword'])) {
					$user_newpassword=readarg($_POST['useredit_newpassword']);
				}
			}
			if (isset($_POST['useredit_token'])) {
				$token=readarg($_POST['useredit_token']);
			}
			break;
		default:
			break;
	}

	$bad_token=false;

	$missing_name=false;
	$bad_name=false;
	$duplicated_name=false;
	$missing_mail=false;
	$bad_mail=false;
	$duplicated_mail=false;
	$bad_website=false;
	$missing_locale=false;
	$bad_locale=false;

	$missing_newpassword=false;
	$bad_newpassword=false;

	$account_modified=false;
	$password_changed=false;

	$internal_error=false;
	$contact_page=false;

	switch($action) {
		case 'modify':
			if (!isset($_SESSION['useredit_token']) or $token != $_SESSION['useredit_token']) {
				$bad_token=true;
			}

			if (!$user_name) {
				$missing_name=true;
			}
			else if (!validate_user_name($user_name)) {
				$bad_name=true;
			}
			else if (!user_check_name($user_name, $user_id)) {
				$duplicated_name=true;
			}

			if (!$user_mail) {
				$missing_mail=true;
			}
			else if (!validate_mail($user_mail)) {
				$bad_mail=true;
			}
			else if (!user_check_mail($user_mail, $user_id)) {
				$duplicated_mail=true;
			}

			if ($user_website and !validate_website($user_website)) {
				$bad_website=true;
			}
			else {
				$user_website=normalize_website($user_website);
			}

			if (!$user_locale) {
				$missing_locale=true;
			}
			else if (!validate_locale($user_locale)) {
				$bad_locale=true;
			}
			break;

		case 'change':
			if (!$user_newpassword) {
				$missing_newpassword=true;
			}
			else if (!validate_password($user_newpassword)) {
				$bad_newpassword=true;
			}
			break;

		default:
			break;
	}

	$confirm_delete=false;

	switch($action) {
		case 'modify':
			if ($bad_token or $missing_name or $bad_name or $duplicated_name or $missing_mail or $bad_mail or $duplicated_mail or $bad_website or $missing_locale or $bad_locale) {
				break;
			}

			$r = user_set($user_id, $user_name, $user_mail, $user_website, $user_locale);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$_SESSION['user']['name'] = $user_name;
			$_SESSION['user']['mail'] = $user_mail;
			$_SESSION['user']['website'] = $user_website;
			$_SESSION['user']['locale'] = $user_locale;

			if ($with_status) {
				$r = user_set_status($user_id, $user_active, $user_banned);
				if (!$r) {
					$internal_error=true;
					break;
				}
			}

			$account_modified=true;

			break;

		case 'change':
			if ($missing_newpassword or $bad_newpassword) {
				break;
			}

			$r = user_set_newpassword($user_id, $user_newpassword);

			$password_changed=true;

			break;

		case 'delete':
			if (!$confirmed) {
				$confirm_delete=true;
				break;
			}

			$r = user_delete($user_id);

			if (!$r) {
				break;
			}

			return false;

		default:
			break;
	}

	$user_newpassword=false;

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}

	$_SESSION['useredit_token'] = $token = token_id();

	$errors = compact('missing_name', 'bad_name', 'duplicated_name', 'missing_mail', 'bad_mail', 'duplicated_mail', 'bad_website', 'missing_locale', 'bad_locale', 'missing_newpassword', 'bad_newpassword', 'internal_error', 'contact_page');
	$infos = compact('account_modified', 'password_changed');

	$output = view('useredit', $lang, compact('token', 'errors', 'infos', 'user_name', 'user_mail', 'user_website', 'user_locale', 'with_status', 'user_banned', 'user_active', 'user_accessed', 'with_newpassword', 'user_newpassword', 'with_delete', 'confirm_delete'));

	return $output;
}

