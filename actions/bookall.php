<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function bookall($lang) {
	head('title', translate('bookall:title', $lang));

	$edit=user_has_role('writer') ? url('bookedit', $_SESSION['user']['locale']) . '?' . 'clang=' . $lang : false;
	$banner = build('banner', $lang, compact('edit'));

	$booklist = build('threadlist', $lang, 'book');

	$content = view('bookall', $lang, compact('booklist'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

