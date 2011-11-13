<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function emailme($subject, $msg, $from=false, $to=false) {
	global $webmaster, $mailer;

	if (!$from) {
		$from = $webmaster;
	}
	if (!$to) {
		$to = $webmaster;
	}

	$headers = <<<_SEP_
From: $from
Return-Path: $from
Content-Type: text/plain; charset=utf-8
X-Mailer: $mailer
_SEP_;

	return @mail($to, $subject, $msg, $headers);
}

