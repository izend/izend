<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function emailme($subject, $msg, $sender=false) {
	global $webmaster, $mailer;

	if (empty($sender)) {
		$sender = $webmaster;
	}

	$headers = <<<_SEP_
From: $sender
Return-Path: $sender
Content-Type: text/plain; charset=utf-8
X-Mailer: $mailer
_SEP_;

	return @mail($webmaster, $subject, $msg, $headers);
}

