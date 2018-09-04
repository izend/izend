<?php

/**
 *
 * @copyright  2018 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'emailtext.php';
require_once 'urlencodeaction.php';

function emailconfirmcomment($node_id, $comment_id, $user_mail, $locale, $sender=false) {
	global $base_url;

	$saction_page=url('saction', $locale);

	if (!$saction_page) {
		return false;
	}

	$id=CONFIRMCOMMENT;
	$param=array($node_id, $comment_id, $locale);

	$s64=urlencodeaction($id, $param);

	if (!$s64) {
		return false;
	}

	$url = $base_url . $saction_page . '/' . $s64;

	$to=$user_mail;
	$subject = translate('email:comment_subject', $locale);
	$f=translate('email:comment_confirm', $locale);
	$s=sprintf($f, $url);
	$msg = $s . "\n\n" . translate('email:salutations', $locale);

	return @emailtext($msg, $to, $subject, $sender);
}
