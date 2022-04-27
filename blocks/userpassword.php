<?php

/**
 *
 * @copyright  2022 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'tokenid.php';
require_once 'validatepassword.php';
require_once 'models/user.inc';

function userpassword($lang, $user_id) {
	$action='init';
	if (isset($_POST['userpassword_change'])) {
		$action='change';
	}

	$with_oldpassword=true;

	$with_viewpassword=$with_oldpassword ? true : false;

	$newpassword=$oldpassword=false;
	$token=false;

	switch($action) {
		case 'change':
			if (isset($_POST['userpassword_newpassword'])) {
				$newpassword=readarg($_POST['userpassword_newpassword']);
			}
			if ($with_oldpassword) {
				if (isset($_POST['userpassword_oldpassword'])) {
					$oldpassword=readarg($_POST['userpassword_oldpassword']);
				}
			}
			if (isset($_POST['userpassword_token'])) {
				$token=readarg($_POST['userpassword_token']);
			}
			break;
		default:
			break;
	}

	$bad_token=false;

	$missing_newpassword=false;
	$bad_newpassword=false;
	$missing_oldpassword=false;
	$bad_oldpassword=false;

	$password_changed=false;

	$user_page=false;

	$internal_error=false;
	$contact_page=false;

	switch($action) {
		case 'change':
			if (!isset($_SESSION['userpassword_token']) or $token != $_SESSION['userpassword_token']) {
				$bad_token=true;
			}

			if (!$newpassword) {
				$missing_newpassword=true;
			}
			else if (!validate_password($newpassword)) {
				$bad_newpassword=true;
			}
			else if ($with_oldpassword and $newpassword == $oldpassword) {
				$bad_newpassword=true;
			}

			if ($with_oldpassword) {
				if (!$oldpassword) {
					$missing_oldpassword=true;
				}
				else if (!validate_password($oldpassword)) {
					$bad_oldpassword=true;
				}
				else if (!user_verify_password($user_id, $oldpassword)) {
					$bad_oldpassword=true;
				}
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'change':
			if ($bad_token or $missing_newpassword or $bad_newpassword or $missing_oldpassword or $bad_oldpassword) {
				break;
			}

			$r = user_set_newpassword($user_id, $newpassword);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$newpassword=false;

			$password_changed=true;

			break;
		default:
			break;
	}

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}
	else if ($password_changed) {
		$user_page=url('user', $lang);
	}

	$_SESSION['userpassword_token'] = $token = token_id();

	$errors = compact('missing_newpassword', 'bad_newpassword', 'missing_oldpassword', 'bad_oldpassword', 'internal_error', 'contact_page');
	$infos = compact('password_changed', 'user_page');

	$output = view('userpassword', $lang, compact('token', 'with_oldpassword', 'with_viewpassword', 'newpassword', 'errors', 'infos'));

	return $output;
}

