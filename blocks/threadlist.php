<?php

/**
 *
 * @copyright  2010-2017 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function threadlist($clang, $type=false, $strict=true, $lang=false) {
	$r = thread_list($clang, $type, $strict);

	if (!$r) {
		return false;
	}

	$thread_list = array();
	$url = url($type ? $type : 'thread', $lang ? $lang : $clang);
	foreach ($r as $thread) {
		extract($thread);	/* thread_id thread_name thread_title thread_abstract thread_number */
		$thread_url = $url . '/' . ($type ? $thread_name : $thread_id);
		if ($lang) {
			$thread_url .= '?' . 'clang=' . $clang;
		}
		$thread_list[] = compact('thread_id', 'thread_title', 'thread_url');
	}

	$output = view('threadlist', false, compact('thread_list'));

	return $output;
}

