<?php

/**
 *
 * @copyright  2010-2014 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/cloud.inc';

function suggest($lang, $arglist=false) {
	global $search_all, $rss_thread;

	$cloud=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$cloud=$arglist[0];
		}
	}

	$cloud_id=false;

	if ($cloud) {
		$cloud_id = cloud_id($cloud);
		if (!$cloud_id) {
			header('HTTP/1.1 404 Not Found');
			return false;
		}

		if ($cloud_id == $rss_thread) {
			if (!user_has_role('administrator')) {
				header('HTTP/1.1 401 Unauthorized');
				return false;
			}
		}

		$r = thread_get($lang, $cloud_id);
		if (!$r) {
			header('HTTP/1.1 404 Not Found');
			return false;
		}
		extract($r); /* thread_type thread_nosearch */

		if ($thread_type == 'thread' or $thread_nosearch) {
			header('HTTP/1.1 404 Not Found');
			return false;
		}
	}
	else {
		if ($search_all !== true) {
			header('HTTP/1.1 404 Not Found');
			return false;
		}
	}

	$term=isset($arglist['term']) ? $arglist['term'] : false;
	if (!$term) {
		header('HTTP/1.1 400 Bad Request');
		return false;
	}

	$r = cloud_suggest($lang, $cloud_id, $term);

	if (!$r) {
		header('HTTP/1.1 404 Not Found');
		return false;
	}

	$taglist=array();
	foreach ($r as $tag) {
		$taglist[]=$tag['tag_name'];
	}

	return json_encode($taglist);
}

