<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function threadlist($lang) {
	$r = thread_list($lang, false);

	if (!$r) {
		return false;
	}

	$thread_list = array();
	$url = url('thread', $lang);
	foreach ($r as $thread) {
		extract($thread);	/* thread_id thread_name thread_title thread_abstract thread_number */
		$thread_url = $url . '/' . $thread_name;
		$thread_list[] = compact('thread_title', 'thread_url');
	}

	$output = view('threadlist', false, compact('thread_list'));

	return $output;
}

