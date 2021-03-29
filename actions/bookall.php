<?php

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'userhasaccess.php';

function bookall($lang) {
	global $with_toolbar;

	head('title', translate('bookall:title', $lang));

	$edit=user_has_role('writer') ? url('bookedit', $_SESSION['user']['locale']) . '?' . 'clang=' . $lang : false;
	$validate=url('book', $lang);

	$banner = build('banner', $lang, $with_toolbar ? false : compact('edit', 'validate'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$not_in=user_noread_list();

	$booklist = build('threadlist', $lang, 'book', $not_in);

	$content = view('bookall', $lang, compact('booklist'));

	$output = layout('standard', compact('lang', 'toolbar', 'banner', 'content'));

	return $output;
}

