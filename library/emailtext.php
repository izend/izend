<?php

/**
 *
 * @copyright  2013 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function emailtext($text, $to, $subject, $sender=false) {
	global $signature, $mailer, $webmaster;

	if (!$sender) {
		$sender = $webmaster;
	}

	$headers = <<<_SEP_
From: $sender
Return-Path: $sender
Content-Type: text/plain; charset=utf-8
X-Mailer: $mailer
_SEP_;

	$body = <<<_SEP_
$text

$signature

_SEP_;

	return @mail($to, $subject, $body, $headers);
}

