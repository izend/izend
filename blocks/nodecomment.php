<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';

function nodecomment($lang, $page_id, $page_url, $nomore=false) {
	$comments=$newcomment=$user_page=false;

	if (!$nomore) {
		if (user_is_identified()) {
			$newcomment = build('newcomment', $lang, $page_id);
		}
		else {
			$user_page = url('user', $lang) . '?page=' . $page_url . '#newcomment';
		}
	}
	$comments = build('comments', $lang, $page_id);

	$output = view('nodecomment', $lang, compact('comments', 'newcomment', 'user_page'));

	return $output;
}

