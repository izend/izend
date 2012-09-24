<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function pagecontent($lang, $arglist=false) {
	global $content_folder;

	if (!$content_folder) {
		header('HTTP/1.1 404 Not Found');
		return false;
	}

	$page=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$page=$arglist[0];
		}
	}

	if (!$page) {
		header('HTTP/1.1 404 Not Found');
		return false;
	}

	$folder_id=$page_id=false;

	foreach (is_array($content_folder) ? $content_folder : array($content_folder) as $folder) {
		$folder_id = thread_id($folder);
		if ($folder_id) {
			$page_id = thread_node_id($folder_id, $page);
			if ($page_id) {
				break;
			}
		}
	}

	if (!$folder_id or !$page_id) {
		header('HTTP/1.1 404 Not Found');
		return false;
	}

	$r = thread_get_node($lang, $folder_id, $page_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_ignored node_name node_title node_abstract node_cloud node_nocomment node_nomorecomment node_novote node_nomorevote node_ilike node_tweet node_plusone node_linkedin */

	if ($node_ignored) {
		header('HTTP/1.1 404 Not Found');
		return false;
	}

	$content = build('nodecontent', $lang, $page_id);

	return $content;
}

