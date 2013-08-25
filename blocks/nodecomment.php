<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'userhasrole.php';
require_once 'userprofile.php';
require_once 'tokenid.php';
require_once 'models/node.inc';

function nodecomment($lang, $node_id, $node_user_id, $node_url, $nomore) {
	$user_id=user_profile('id');
	$moderator=user_has_role('moderator');	// $user_id == $node_user_id || user_has_role('moderator')

	$now=time();

	$message_maxlen=2000;

	$action='init';
	if ($user_id) {
		if (isset($_POST['comment_comment'])) {
			$action='comment';
		}
		else if (isset($_POST['comment_edit'])) {
			$action='edit';
		}
		else if (isset($_POST['comment_validate'])) {
			$action='validate';
		}
		else if (isset($_POST['comment_moderate'])) {
			$action='moderate';
		}
		else if (isset($_POST['comment_modify'])) {
			$action='modify';
		}
		else if (isset($_POST['comment_delete'])) {
			$action='delete';
		}
	}

	$id=$message=$token=false;

	switch($action) {
		case 'comment':
		case 'validate':
		case 'edit':
			if (isset($_POST['comment_message'])) {
				$message=readarg($_POST['comment_message'], true, false);	// trim but DON'T strip!
			}
			if (isset($_POST['comment_token'])) {
				$token=readarg($_POST['comment_token']);
			}
			break;

		case 'moderate':
			if (isset($_POST['comment_moderate'])) {
				$id=readarg($_POST['comment_moderate']);
			}
			break;

		case 'modify':
		case 'delete':
			if (isset($_POST['comment_id'])) {
				$id=readarg($_POST['comment_id']);
			}
			if (isset($_POST['comment_message'])) {
				$message=readarg($_POST['comment_message'], true, false);	// trim but DON'T strip!
			}
			if (isset($_POST['comment_token'])) {
				$token=readarg($_POST['comment_token']);
			}
			break;

		default:
			break;
	}

	$bad_token=false;

	$missing_id=false;
	$bad_id=false;
	$missing_message=false;
	$message_too_long=false;

	switch($action) {
		case 'comment':
		case 'validate':
		case 'edit':
		case 'modify':
		case 'delete':
			if (!isset($_SESSION['comment_token']) or $token != $_SESSION['comment_token']) {
				$bad_token=true;
			}
			break;

		default:
			break;
	}

	switch($action) {
		case 'moderate':
		case 'modify':
		case 'delete':
			if ($bad_token) {
				break;
			}

			if (!$id) {
				$missing_id=true;
				break;
			}

			if (!is_numeric($id)) {
				$id=false;
				$bad_id=true;
				break;
			}

			if (!$moderator) {
				$r = node_get_comment($node_id, $id, $lang);
				if (!$r) {
					$id=false;
					$bad_id=true;
					break;
				}
				extract($r);	/* comment_user_id, comment_created */

				if (!($comment_user_id == $user_id and $comment_created + 15*60 > $now)) {
					$id=false;
					$bad_id=true;
					break;
				}
			}
			break;

		default:
			break;
	}

	switch($action) {
		case 'comment':
		case 'validate':
		case 'edit':
		case 'modify':
			if ($bad_token or $missing_id or $bad_id) {
				break;
			}

			if (!$message) {
				$missing_message=true;
			}
			else if (strlen(utf8_decode($message)) > $message_maxlen) {
				$message_too_long=true;
			}
			break;

		default:
			break;
	}

	switch($action) {
		case 'validate':
			if ($bad_token or $missing_message or $message_too_long) {
				break;
			}

			$ip_address=client_ip_address();

			$r=node_add_comment($node_id, $user_id, $ip_address, $message, $lang);

			if (!$r) {
				$internal_error=true;
				break;
			}

			require_once 'emailme.php';

			global $sitename;

			$timestamp=strftime('%d-%m-%Y %H:%M:%S', time());
			$subject = 'new_comment' . '@' . $sitename;
			$msg = $timestamp . ' ' . $user_id . ' ' . $lang . ' ' . $node_id . ' ' . $node_url;
			emailme($subject, $msg);

			$message=false;

			break;

		case 'modify':
			if ($bad_token or $missing_id or $bad_id or $missing_message or $message_too_long) {
				break;
			}

			$r = node_set_comment($node_id, $id, $message, $lang);
			if (!$r) {
				$internal_error=true;
				break;
			}

			$id=$message=false;

			break;

		case 'delete':
			if ($bad_token or $missing_id or $bad_id) {
				break;
			}

			$r = node_delete_comment($node_id, $id);
			if (!$r) {
				$internal_error=true;
				break;
			}

			$id=$message=false;

			break;

		default:
			break;
	}

	$newcomment=$user_page=false;

	if (!$id and !$nomore) {
		if ($user_id) {
			$newcomment = true;
		}
		else {
			$user_page = url('user', $lang);
		}
	}

	$comments = node_get_all_comments($node_id, $lang);

	$moderated=false;
	if ($comments) {
		if ($moderator) {
			$moderated=true;
		}
		else {
			$moderated=array();
			foreach ($comments as $c) {
				if ($c['comment_user_id'] == $user_id and $c['comment_created'] + 15*60 > $now) {
					$moderated[] = $c['comment_id'];
				}
			}
		}
	}

	$_SESSION['comment_token'] = $token = token_id();

	$errors = compact('missing_message', 'message_too_long');

	$output = view('nodecomment', $lang, compact('token', 'comments', 'moderated', 'id', 'newcomment', 'message', 'message_maxlen', 'user_page', 'node_url', 'errors'));

	return $output;
}

