<?php

/**
 *
 * @copyright  2017 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'filemimetype.php';

function emailmefile($subject, $msg, $file, $filename=false, $from=false, $to=false) {
	global $webmaster, $mailer;

	if (!$from) {
		$from = $webmaster;
	}
	if (!$to) {
		$to = $webmaster;
	}

	$filetype=file_mime_type($file);

	if (!$filetype) {
		return false;
	}

	$filedata=@file_get_contents($file);

	if (!$filedata) {
		return false;
	}

	if (!$filename) {
		$filename=basename($file);
	}

	$sep=md5(uniqid('sep'));
	$data=chunk_split(base64_encode($filedata));

	$headers = <<<_SEP_
From: $from
Return-Path: $from
Content-Type: multipart/mixed; boundary="$sep"
X-Mailer: $mailer
_SEP_;

	$body = <<<_SEP_
--$sep
Content-Type: text/plain; charset=utf-8

$msg
--$sep
Content-Type: $filetype; name="$filename"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="$filename"

$data
--$sep--
_SEP_;

	return @mail($to, $subject, $body, $headers);
}

