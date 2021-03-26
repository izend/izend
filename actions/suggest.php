<?php

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'userhasaccess.php';
require_once 'models/cloud.inc';
require_once 'models/thread.inc';

function suggest($lang, $arglist=false) {
	global $search_default, $search_all, $limited_languages;

	$cloud=$search_default;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$cloud=$arglist[0];
		}
	}

	$cloud_id=false;
	$not_in=false;

	$clang=$lang;

	if ($cloud) {
		$cloud_id = cloud_id($cloud);
		if (!$cloud_id) {
			header('HTTP/1.1 404 Not Found');
			return false;
		}

		if (!user_can_read($cloud_id)) {
			return run('error/unauthorized', $lang);
		}

		$thread_type = thread_type($cloud_id);

		if ($thread_type == 'rss') {
			if (!user_has_role('administrator')) {
				header('HTTP/1.1 401 Unauthorized');
				return false;
			}
		}

		if (isset($limited_languages[$thread_type]) and !in_array($clang, $limited_languages[$thread_type])) {
			$clang = $limited_languages[$thread_type][0];
		}

		$r = thread_get($clang, $cloud_id);
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

		$not_in=user_noread_list();
	}

	$term=isset($arglist['term']) ? $arglist['term'] : false;
	if (!$term) {
		header('HTTP/1.1 400 Bad Request');
		return false;
	}

	$r = cloud_suggest($clang, $cloud_id, $not_in, $term);

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
