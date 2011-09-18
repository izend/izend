<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function threadall($lang) {
	global $system_languages;

	$slang=false;
	if (isset($_GET['slang'])) {
		$slang = $_GET['slang'];
	}
	else {
		$slang=$lang;
	}
	if (!in_array($slang, $system_languages)) {
		return run('error/notfound', $lang);
	}

	head('title', translate('threadall:title', $slang));

	$edit=user_has_role('writer') ? url('threadedit', $_SESSION['user']['locale']) . '?' . 'clang=' . $lang : false;
	$validate=url('thread', $lang);
	$banner = build('banner', $lang, compact('edit', 'validate'));

	$threadlist = build('threadlist', $lang, false, $slang);

	$content = view('threadall', $slang, compact('threadlist'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

