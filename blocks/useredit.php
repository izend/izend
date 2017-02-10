<?php

/**
 *
 * @copyright  2011-2017 izend.org
 * @version    15
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'userhasrole.php';
require_once 'userprofile.php';
require_once 'validatemail.php';
require_once 'validatelocale.php';
require_once 'validatepassword.php';
require_once 'validaterole.php';
require_once 'validateusername.php';
require_once 'validatetimezone.php';
require_once 'validatewebsite.php';
require_once 'models/user.inc';

function useredit($lang, $user_id) {
	global $system_languages, $supported_roles;

	$is_admin = user_has_role('administrator');
	$is_owner = $user_id == user_profile('id');

	$with_name=true;
	$with_status=($user_id != 1 and $is_admin);
	$with_delete=($user_id != 1 and $is_admin and !$is_owner);
	$with_newpassword=false; 	// ($user_id != 1 and $is_owner);
	$with_locale=count($system_languages) > 1 ? true : false;
	$with_role=($user_id != 1 and $is_admin);
	$with_timezone=($user_id != 1 and $is_admin);
	$with_website=true;

	$with_info=false;

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

	$user_name=$user_mail=$user_locale=$user_timezone=false;
	$user_website=false;

	$user_active=$user_banned=false;
	$user_accessed=false;

	$user_role=false;

	$user_newpassword=false;

	$user_lastname=$user_firstname=false;

	$token=false;

	switch($action) {
		case 'init':
		case 'reset':
			$r = user_get($user_id);
			if ($r) {
				extract($r);		/* user_name user_password user_newpassword user_seed user_mail user_timezone user_website user_created user_modified user_accessed user_locale user_active user_banned */
			}
			$user_newpassword=false;

			if ($with_info) {
				$r = user_get_info($user_id);
				if ($r) {
					extract($r);	/* user_lastname, user_firstname */
				}
			}

			if ($with_role) {
				$user_role = user_get_role($user_id);
			}
			break;
		case 'modify':
		case 'change':
		case 'delete':
		case 'cancel':
			if ($with_info) {
				if (isset($_POST['useredit_lastname'])) {
					$user_lastname=readarg($_POST['useredit_lastname']);
				}
				if (isset($_POST['useredit_firstname'])) {
					$user_firstname=readarg($_POST['useredit_firstname']);
				}
			}
			if (isset($_POST['useredit_name'])) {
				$user_name=strtolower(strflat(readarg($_POST['useredit_name'])));
			}
			if (isset($_POST['useredit_mail'])) {
				$user_mail=strtolower(strflat(readarg($_POST['useredit_mail'])));
			}
			if (isset($_POST['useredit_website'])) {
				$user_website=strtolower(strflat(readarg($_POST['useredit_website'])));
			}
			if (isset($_POST['useredit_timezone'])) {
				$user_timezone=readarg($_POST['useredit_timezone']);
			}
			if (isset($_POST['useredit_locale'])) {
				$user_locale=readarg($_POST['useredit_locale']);
			}
			if ($with_role) {
				if (isset($_POST['useredit_role'])) {
					$user_role=readarg($_POST['useredit_role']);
				}
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

	$missing_lastname=false;
	$missing_firstname=false;

	$missing_name=false;
	$bad_name=false;
	$duplicated_name=false;
	$missing_mail=false;
	$bad_mail=false;
	$duplicated_mail=false;
	$bad_role=false;
	$bad_website=false;
	$missing_locale=false;
	$bad_locale=false;
	$bad_timezone=false;

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

			if ($with_info) {
				if (!$user_lastname) {
					$missing_lastname=true;
				}
				if (!$user_firstname) {
					$missing_firstname=true;
				}
			}

			if ($with_name and !$user_name) {
				$missing_name=true;
			}
			if ($user_name) {
				if (!validate_user_name($user_name)) {
					$bad_name=true;
				}
				else if (!user_check_name($user_name, $user_id)) {
					$duplicated_name=true;
				}
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

			if ($user_role) {
				foreach ($user_role as $role) {
					if (!validate_role($role)) {
						$bad_role=true;
						break;
					}
				}
			}

			if ($user_website) {
				if (!validate_website($user_website)) {
					$bad_website=true;
				}
				else {
					$user_website=normalize_website($user_website);
				}
			}

			if ($user_timezone) {
				if (!validate_timezone($user_timezone)) {
					$bad_timezone=true;
				}
			}

			if ($with_locale and !$user_locale) {
				$missing_locale=true;
			}
			if ($user_locale) {
				if (!validate_locale($user_locale)) {
					$bad_locale=true;
				}
			}

			if ($user_banned) {
				$user_active=false;
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
			if ($bad_token or $missing_name or $bad_name or $duplicated_name or $missing_mail or $bad_mail or $duplicated_mail or $bad_role or $bad_website or $bad_timezone or $missing_locale or $bad_locale or $missing_lastname or $missing_firstname) {
				break;
			}

			$r = user_set($user_id, $user_name, $user_mail, $user_website, $user_locale, $user_timezone);

			if (!$r) {
				$internal_error=true;
				break;
			}

			if ($is_owner) {
				$_SESSION['user']['name'] = $user_name;
				$_SESSION['user']['mail'] = $user_mail;
				$_SESSION['user']['website'] = $user_website;
				$_SESSION['user']['locale'] = $user_locale;
				$_SESSION['user']['timezone'] = $user_timezone;
			}

			if ($with_info) {
				$r = user_set_info($user_id, $user_lastname, $user_firstname);
				if (!$r) {
					$internal_error=true;
					break;
				}

				if ($is_owner) {
					$_SESSION['user']['lastname'] = $user_lastname;
					$_SESSION['user']['firstname'] = $user_firstname;
				}
			}

			if ($with_role) {
				$r = user_set_role($user_id, $user_role);
				if (!$r) {
					$internal_error=true;
					break;
				}
			}

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
			if ($bad_token or $missing_newpassword or $bad_newpassword) {
				break;
			}

			$r = user_set_newpassword($user_id, $user_newpassword);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$password_changed=true;

			break;

		case 'delete':
			if ($bad_token) {
				break;
			}

			if (!$confirmed) {
				$confirm_delete=true;
				break;
			}

			$r = user_delete($user_id);

			if (!$r) {
				$internal_error=true;
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

	$errors = compact('missing_name', 'bad_name', 'duplicated_name', 'missing_mail', 'bad_mail', 'duplicated_mail', 'bad_timezone', 'bad_website', 'missing_locale', 'bad_locale', 'missing_newpassword', 'bad_newpassword', 'missing_lastname', 'missing_firstname', 'internal_error', 'contact_page');
	$infos = compact('account_modified', 'password_changed');

	$output = view('useredit', $lang, compact('token', 'errors', 'infos', 'with_name', 'user_name', 'user_mail', 'with_timezone', 'user_timezone', 'with_website', 'user_website', 'with_role', 'user_role', 'supported_roles', 'with_locale', 'user_locale', 'with_status', 'user_banned', 'user_active', 'user_accessed', 'with_newpassword', 'user_newpassword', 'with_info', 'user_lastname', 'user_firstname', 'with_delete', 'confirm_delete'));

	return $output;
}

