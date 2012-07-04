<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function threadlist($lang, $type=false, $strict=true, $slang=false ) {
	$r = thread_list($lang, $type, $strict);

	if (!$r) {
		return false;
	}

	$thread_list = array();
	$url = url($type ? $type : 'thread', $lang);
	foreach ($r as $thread) {
		extract($thread);	/* thread_id thread_name thread_title thread_abstract thread_number */
		$thread_url = $url . '/' . ($type ? $thread_name : $thread_id);
		if ($slang) {
			 $thread_url .= '?' . 'slang=' . $slang;
		}
		$thread_list[] = compact('thread_id', 'thread_title', 'thread_url');
	}

	$output = view('threadlist', false, compact('thread_list'));

	return $output;
}

