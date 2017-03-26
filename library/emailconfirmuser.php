<?php

/**
 *
 * @copyright  2017 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'emailtext.php';
require_once 'urlencodeaction.php';

function emailconfirmuser($user_id, $user_mail, $locale, $sender=false) {
	global $base_url;

	$saction_page=url('saction', $locale);

	if (!$saction_page) {
		return false;
	}

	$id=2;	// confirmnewuser, see saction
	$param=$user_id;

	$s64=urlencodeaction($id, $param);

	if (!$s64) {
		return false;
	}

	$url = $base_url . $saction_page . '/' . $s64;

	$to=$user_mail;
	$subject = translate('email:new_user_subject', $locale);
	$f=translate('email:new_user_confirm', $locale);
	$s=sprintf($f, $url);
	$msg = $s . "\n\n" . translate('email:salutations', $locale);

	return @emailtext($msg, $to, $subject, $sender);
}
