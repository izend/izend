<?php

/**
 *
 * @copyright  2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'userprofile.php';
require_once 'validateusername.php';
require_once 'validatemail.php';
require_once 'validatelocale.php';
require_once 'models/user.inc';

function useredit($lang, $user_id, $administrator=false) {
	$with_status=$administrator == true;
	$with_delete=$user_id != user_profile('id');

	$confirmed=false;

	$action='init';
	if (isset($_POST['useredit_modify'])) {
		$action='modify';
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

	$user_active=$user_banned=false;
	$user_accessed=false;

	$token=false;

	switch($action) {
		case 'init':
		case 'reset':
			$r = user_get($user_id);
			if ($r) {
				extract($r);
			}
			break;
		case 'modify':
		case 'delete':
		case 'cancel':
			if (isset($_POST['useredit_name'])) {
				$user_name=strtolower(strflat(readarg($_POST['useredit_name'])))                                                                                                      ;
			}
			if (isset($_POST['useredit_mail'])) {
				$user_mail=strtolower(strflat(readarg($_POST['useredit_mail'])));
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
	$missing_mail=false;
	$bad_mail=false;
	$missing_locale=false;
	$bad_locale=false;

	$duplicated_name=false;
	$duplicated_mail=false;

	$account_modified=false;

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

			if (!$user_locale) {
				$missing_locale=true;
			}
			else if (!validate_locale($user_locale)) {
				$bad_locale=true;
			}

			break;

		default:
			break;
	}

	$confirm_delete=false;

	switch($action) {
		case 'modify':
			if ($bad_token or $missing_name or $bad_name or $duplicated_name or $missing_mail or $bad_mail or $duplicated_mail or $missing_locale or $bad_locale) {
				break;
			}

			$r = user_set($user_id, $user_name, $user_mail, $user_locale);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$_SESSION['user']['name'] = $user_name;
			$_SESSION['user']['mail'] = $user_mail;
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

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}

	$_SESSION['useredit_token'] = $token = token_id();

	$errors = compact('missing_name', 'bad_name', 'duplicated_name', 'missing_mail', 'bad_mail', 'duplicated_mail', 'missing_locale', 'bad_locale', 'internal_error', 'contact_page');
	$infos = compact('account_modified');

	$output = view('useredit', $lang, compact('token', 'errors', 'infos', 'user_name', 'user_mail', 'user_locale', 'user_banned', 'user_active', 'user_accessed', 'with_status', 'with_delete', 'confirm_delete'));

	return $output;
}

