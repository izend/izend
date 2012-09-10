<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'filemimetype.php';

function emailhtml($text, $html, $css, $to, $subject, $sender=false) {
	global $mailer, $webmaster, $sitename;

	if (!$sender) {
		$sender = $webmaster;
	}

	$textheader=$textbody=$htmlheader=$htmlbody=false;

	if ($text) {
		$textheader = 'Content-Type: text/plain; charset=utf-8';
		$textbody = <<<_SEP_
$text

_SEP_;
	}

	$related=false;

	if ($html) {
		$related=array();
		if (preg_match_all('#<img[^>]+src="([^"]*)"[^>]*>#is', $html, $matches)) {
			$pattern=array();
			$replacement=array();
			foreach ($matches[1] as $url) {
				if ($url[0] != '/')
					continue;
				if (array_key_exists($url, $related))
					continue;
				$fname=ROOT_DIR . $url;
				$filetype=file_mime_type($fname, false);
				if (!$filetype or strpos($filetype, 'image') !== 0)
					continue;
				$data=file_get_contents($fname);
				if (get_magic_quotes_runtime()) {
					$data = stripslashes($data);
				}
				if (!$data)
					continue;
				$base64=chunk_split(base64_encode($data));
				$cid=md5(uniqid('cid'));
				$qfname=preg_quote($url);
				$pattern[]='#(<img[^>]+src=)"' . $qfname . '"([^>]*>)#is';
				$replacement[]='${1}"cid:' . $cid . '"${2}';
				$related[$url]=array(basename($fname), $filetype, $cid, $base64);
			}

			$html=preg_replace($pattern, $replacement, $html);
		}

		$title=htmlspecialchars($sitename, ENT_COMPAT, 'UTF-8');

		$htmlheader = 'Content-Type: text/html; charset=utf-8';
		$htmlbody = <<<_SEP_
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>$title</title>
<style type="text/css">
$css
</style>
</head>
<body>
$html
</body>
</html>

_SEP_;
	}

	$headers = <<<_SEP_
From: $sender
Return-Path: $sender
X-Mailer: $mailer

_SEP_;

	$body='';

	if ($related) {
		if ($textbody) {
			$sep=md5(uniqid('sep'));

			$body .= <<<_SEP_
Content-Type: multipart/alternative; boundary="$sep"

--$sep
$textheader

$textbody
--$sep
$htmlheader

$htmlbody
--$sep--


_SEP_;
		}
		else {
			$body .= <<<_SEP_
$htmlheader

$htmlbody

_SEP_;
		}

		$sep=md5(uniqid('sep'));

		$headers .= <<<_SEP_
Content-Type: multipart/related; boundary="$sep"
_SEP_;

		foreach ($related as $url => $r) {
			list($filename, $filetype, $cid, $base64)=$r;
			$body .= <<<_SEP_
--$sep
Content-Type: $filetype
Content-Transfer-Encoding: base64
Content-Disposition: inline; filename="$filename"
Content-ID: <$cid>

$base64

_SEP_;
		}

		$body = <<<_SEP_
--$sep
$body
--$sep--
_SEP_;
	}
	else if ($textbody and $htmlbody) {
		$sep=md5(uniqid('sep'));

		$headers .= <<<_SEP_
Content-Type: multipart/alternative; boundary="$sep"
_SEP_;
		$body .= <<<_SEP_
--$sep
$textheader

$textbody
--$sep
$htmlheader

$htmlbody
--$sep--
_SEP_;
	}
	else if ($textbody) {
		$headers .= $textheader;
		$body=$textbody;
	}
	else if ($htmlbody) {
		$headers .= $htmlheader;
		$body=$htmlbody;
	}

	return @mail($to, $subject, $body, $headers);
}

