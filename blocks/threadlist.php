<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function threadlist($lang, $type=false, $slang=false ) {
	$r = thread_list($lang, $type);

	if (!$r) {
		return false;
	}

	$thread_list = array();
	$url = url($type ? $type : 'thread', $lang);
	foreach ($r as $thread) {
		extract($thread);	/* thread_id thread_name thread_title thread_abstract thread_number */
		$thread_url = $url . '/' . $thread_name;
		if ($slang) {
			 $thread_url .= '?' . 'slang=' . $slang;
		}
		$thread_list[] = compact('thread_id', 'thread_title', 'thread_url');
	}

	$output = view('threadlist', false, compact('thread_list'));

	return $output;
}

