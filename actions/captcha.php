<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'strrand.php';
require_once 'strtag.php';

function captcha($lang, $arglist=false) {
	$id=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$id=$arglist[0];
		}
	}

	$accepted = array('login', 'register', 'remindme', 'mailme', 'subscribe', 'unsubscribe', 'comment');

	if ($id and !in_array($id, $accepted)) {
		return run('error/badrequest', $lang);
	}

	$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	$code = strrand($charset, 4);

	if ($id) {
		$_SESSION['captcha'][$id] = $code;
	}

	$img = strtag($code);

	header('Content-Type: image/png');
	header("Content-Disposition: inline; filename=captcha.png");
	header("Cache-Control: no-cache");

	imagepng($img);
	imagedestroy($img);

	return false;
}

