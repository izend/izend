<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'strtag.php';

function emailcrypto($text, $tag, $to, $subject, $sender) {
	global $signature, $mailer, $webmaster;

	$img=strtag($tag);

	ob_start();
	imagepng($img);
	imagedestroy($img);
	$imgdata=ob_get_contents();
	ob_end_clean();

	$sep=md5(uniqid('sep'));
	$data=chunk_split(base64_encode($imgdata));

	$headers = <<<_SEP_
From: $sender
Return-Path: $webmaster
Content-Type: multipart/mixed; boundary="$sep"
X-Mailer: $mailer
_SEP_;

	$body = '';

	if ($text) {
		$body .= <<<_SEP_
--$sep
Content-Type: text/plain; charset=utf-8

$text

$signature

_SEP_;
	}

	$body .= <<<_SEP_
--$sep
Content-Type: image/png; name="crypto.png"
Content-Transfer-Encoding: base64
Content-Disposition: inline; filename="crypto.png"

$data
--$sep--
_SEP_;

	return @mail($to, $subject, $body, $headers);
}

