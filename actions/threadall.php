<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function threadall($lang) {
	head('title', translate('threadall:title', $lang));

	$edit=user_has_role('writer') ? url('threadedit', $_SESSION['user']['locale']) . '?' . 'clang=' . $lang : false;
	$banner = build('banner', $lang, compact('edit'));

	$threadlist = build('threadlist', $lang);

	$content = view('threadall', $lang, compact('threadlist'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

