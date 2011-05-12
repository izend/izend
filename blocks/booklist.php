<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function booklist($lang) {
	$r = thread_list($lang, 'book');

	if (!$r) {
		return false;
	}

	$book_list = array();
	$url = url('book', $lang);
	foreach ($r as $book) {
		extract($book);	/* thread_id thread_name thread_title thread_abstract thread_number */
		$book_title = $thread_title;
		$book_url = $url . '/' . $thread_name;
		$book_list[] = compact('book_title', 'book_url');
	}

	$output = view('booklist', false, compact('book_list'));

	return $output;
}

