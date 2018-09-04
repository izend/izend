<?php

/**
 *
 * @copyright  2010-2016 izend.org
 * @version    11
 * @link       http://www.izend.org
 */

require_once 'ismailallowed.php';
require_once 'readarg.php';
require_once 'strflat.php';
require_once 'tokenid.php';
require_once 'userhasrole.php';
require_once 'userprofile.php';
require_once 'validatemail.php';
require_once 'models/node.inc';

function nodecomment($lang, $node_id, $node_user_id, $node_url, $nomore) {
	$user_id=user_profile('id');
	$is_moderator=user_has_role('moderator');	// $user_id == $node_user_id || user_has_role('moderator')

	$now=time();

	$message_maxlen=2000;

	$with_validation=false;	// !$user_id;
	$with_mail=$with_validation;

	$with_captcha=false;

	$action='init';
	if ($user_id or $with_validation) {
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

	$id=$message=$mail=$token=false;

	if ($with_mail and isset($_SESSION['comment']['mail'])) {
		$mail=$_SESSION['comment']['mail'];
	}

	switch($action) {
		case 'validate':
			if (isset($_POST['comment_code'])) {
				$code=readarg($_POST['comment_code']);
			}
			/* fall thru */
		case 'comment':
		case 'edit':
			if ($with_mail) {
				if (isset($_POST['comment_mail'])) {
					$mail=strtolower(strflat(readarg($_POST['comment_mail'])));
				}
			}
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

	$missing_code=false;
	$bad_code=false;

	$bad_token=false;

	$missing_id=false;
	$bad_id=false;
	$missing_message=false;
	$message_too_long=false;
	$missing_mail=false;
	$bad_mail=false;

	switch($action) {
		case 'validate':
			if ($with_captcha) {
				if (!$code) {
					$missing_code=true;
					break;
				}
				$captcha=isset($_SESSION['captcha']['comment']) ? $_SESSION['captcha']['comment'] : false;
				if (!$captcha or $captcha != strtoupper($code)) {
					$bad_code=true;
					break;
				}
			}
			/* fall thru */
		case 'comment':
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

			if (!$is_moderator) {
				$r = node_get_comment($node_id, $id, $lang);
				if (!$r) {
					$id=false;
					$bad_id=true;
					break;
				}
				extract($r);	/* comment_user_id, comment_created */

				if ($user_id) {
					if (!($comment_user_id == $user_id and $comment_created + 20*60 > $now)) {
						$id=false;
						$bad_id=true;
					}
				}
				else {

					if (!isset($_SESSION['comment']['id']) or !(in_array($id, $_SESSION['comment']['id']) and $comment_created + 20*60 > $now)) {
						$id=false;
						$bad_id=true;
					}
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
			if ($bad_token or $missing_code or $bad_code or $missing_id or $bad_id) {
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
			if ($bad_token or $missing_code or $bad_code or $missing_id or $bad_id or $missing_message or $message_too_long) {
				break;
			}

			if ($with_mail) {
				if (!$mail) {
					$missing_mail=true;
				}
				else if (!validate_mail($mail) or !is_mail_allowed($mail)) {
					$bad_mail=true;
				}
			}
			break;

		default:
			break;
	}

	switch($action) {
		case 'validate':
			if ($bad_token or $missing_code or $bad_code or $missing_message or $message_too_long or $missing_mail or $bad_mail) {
				break;
			}

			$ip_address=client_ip_address();
			$comment_user_id=$user_id ? $user_id : 0;

			$r=node_add_comment($node_id, $comment_user_id, $mail, $ip_address, $message, $lang, !$with_validation);

			if (!$r) {
				$internal_error=true;
				break;
			}

			$comment_id=$r;

			if ($with_validation) {
				if (!isset($_SESSION['comment'])) {
					$_SESSION['comment'] = array();
				}
				if (!isset($_SESSION['comment']['id'])) {
					$_SESSION['comment']['id'] = array();
				}
				$_SESSION['comment']['mail'] = $mail;
				$_SESSION['comment']['id'][] = $comment_id;
			}

			require_once 'serveripaddress.php';
			require_once 'emailme.php';

			global $sitename;

			$ip=server_ip_address();
			$timestamp=strftime('%Y-%m-%d %H:%M:%S', time());
			$subject = 'comment' . '@' . $sitename;
			$msg = $ip . ' ' . $timestamp . ' ' . $comment_user_id . ' ' . $lang . ' ' . $node_id . ' ' . $node_url;
			if ($with_mail) {
				$msg .= ' ' . $mail;
			}

			@emailme($subject, $msg);

			if ($with_validation) {
				require_once 'emailconfirmcomment.php';

				@emailconfirmcomment($node_id, $comment_id, $mail, $lang);
			}

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
		if ($user_id or $with_validation) {
			$newcomment = true;
		}
		else {
			$user_page = url('user', $lang);
		}
	}

	$published=!$is_moderator;
	$cids=$published && isset($_SESSION['comment']['id']) ? $_SESSION['comment']['id'] : false;

	$comments = node_get_all_comments($node_id, $lang, $published, $cids);

	$moderated=false;
	if ($comments) {
		if ($is_moderator) {
			$moderated=true;
		}
		else {
			if ($user_id) {
				$moderated=array();
				foreach ($comments as $c) {
					if ($c['comment_user_id'] == $user_id and $c['comment_created'] + 20*60 > $now) {
						$moderated[] = $c['comment_id'];
					}
				}
			}
			else if ($cids) {
				$moderated=array();
				foreach ($comments as $c) {
					if (in_array($c['comment_id'], $cids) and $c['comment_created'] + 20*60 > $now) {
						$moderated[] = $c['comment_id'];
					}
				}
			}
		}
	}

	$_SESSION['comment_token'] = $token = token_id();

	$errors = compact('missing_code', 'bad_code', 'missing_message', 'message_too_long', 'missing_mail', 'bad_mail');

	$output = view('nodecomment', $lang, compact('token', 'with_mail', 'with_captcha', 'comments', 'moderated', 'id', 'newcomment', 'message', 'message_maxlen', 'mail', 'user_page', 'node_url', 'errors'));

	return $output;
}

