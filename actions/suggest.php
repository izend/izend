<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'models/cloud.inc';

function suggest($lang, $arglist=false) {
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

		$r = thread_get($lang, $cloud_id);
		if (!$r) {
			header('HTTP/1.1 404 Not Found');
			return false;
		}
		extract($r); /* thread_type thread_nosearch */

		if ($thread_nosearch) {
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

