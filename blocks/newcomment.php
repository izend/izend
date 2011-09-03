<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'userprofile.php';
require_once 'models/node.inc';

function newcomment($lang, $node_id) {
	$comment_name = user_profile('name');
	$comment_created = time();

	$token=false;
	if (isset($_POST['newcomment_token'])) {
		$token=readarg($_POST['newcomment_token']);
	}

	$action='init';
	if (isset($_POST['newcomment_comment'])) {
		$action='comment';
	}
	else if (isset($_POST['newcomment_validate'])) {
		$action='validate';
	}
	else if (isset($_POST['newcomment_edit'])) {
		$action='edit';
	}

	$message=false;

	switch($action) {
		case 'comment':
		case 'validate':
		case 'edit':
			if (isset($_POST['newcomment_message'])) {
				$message=readarg($_POST['newcomment_message'], true, false);	// trim but DON'T strip!
			}
			break;
		default:
			break;
	}

	$missing_message=false;
	$message_too_long=false;

	$internal_error=false;
	$contact_page=false;

	switch($action) {
		case 'comment':
		case 'validate':
		case 'edit':
			if (!$message) {
				$missing_message=true;
				break;
			}

			if (strlen($message) > 2000) {
				$message_too_long=true;
				break;
			}

		default:
			break;
	}

	switch($action) {
		case 'validate':
			if ($missing_message or $message_too_long) {
				break;
			}

			if (isset($_SESSION['newcomment_token']) and $_SESSION['newcomment_token'] != $token) {
				break;
			}

			$user_id = $_SESSION['user']['id'];

			if (!$user_id) {
				$internal_error=true;
				break;
			}

			require_once 'models/node.inc';

			$r=node_add_comment($node_id, $user_id, $message, $lang);

			if (!$r) {
				$internal_error=true;
				break;
			}

			require_once 'emailme.php';

			global $sitename;

			$timestamp=strftime('%d-%m-%Y %H:%M:%S', time());
			$subject = 'new_comment' . '@' . $sitename;
			$msg = $timestamp . ' ' . $user_id . ' ' . $lang . ' ' . $node_id;
			emailme($subject, $msg);

			$message=false;
			$token=false;

			break;
		default:
			break;
	}

	if (!$token) {
		$_SESSION['newcomment_token'] = $token = token_id();
	}

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}

	$errors = compact('missing_message', 'message_too_long', 'internal_error', 'contact_page');

	$output = view('newcomment', $lang, compact('token', 'message', 'comment_created', 'comment_name', 'errors'));

	return $output;
}

