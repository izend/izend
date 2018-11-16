<?php

/**
 *
 * @copyright  2018 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';

function confirmcomment($lang, $arglist) {
	head('title', translate('comment:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$banner = build('banner', $lang);

	list($timestamp, $param)=$arglist;
	list($node_id, $comment_id, $locale)=$param;

	$bad_comment=false;
	$bad_time=false;

	$comment_confirmed=false;

	$internal_error=false;
	$contact_page=false;

	$comment=node_get_comment($node_id, $comment_id, $locale);

	if (!$comment) {
		$bad_comment=true;
	}
	else if ($comment['comment_confirmed']) {
		$comment_confirmed=true;
	}
	else if (time() - $timestamp > 3600) {
		$bad_time=true;

		require_once 'emailconfirmcomment.php';

		if (!$comment['comment_user_mail']) {
			$internal_error=true;
		}
		else {
			$r=emailconfirmcomment($node_id, $comment_id, $comment['comment_user_mail'], $locale);

			if (!$r) {
				$internal_error=true;
			}
		}
	}
	else {
		$r = node_confirm_comment($node_id, $comment_id, $locale);

		if (!$r) {
			$internal_error=true;
		}
		else {
			$comment_confirmed=true;
		}
	}

	if ($internal_error) {
		$contact_page=url('contact', $lang);
	}

	$errors = compact('bad_comment', 'bad_time', 'internal_error', 'contact_page');

	$content = view('confirmcomment', $lang, compact('comment_confirmed', 'errors'));

	$output = layout('standard', compact('lang', 'banner', 'content'));

	return $output;
}

