<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
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
			return run('error/notfound', $lang);
		}

		$r = thread_get($lang, $cloud_id);
		if (!$r) {
			return run('error/notfound', $lang);
		}
		extract($r); /* thread_type thread_nosearch */

		if ($thread_nosearch) {
			return run('error/notfound', $lang);
		}
	}

	$term=isset($arglist['term']) ? $arglist['term'] : false;
	if (!$term) {
		return run('error/badrequest', $lang);
	}

	$r = cloud_suggest($lang, $cloud_id, $term);

	if (!$r) {
		return run('error/notfound', $lang);
	}

	$taglist=array();
	foreach ($r as $tag) {
		$taglist[]=$tag['tag_name'];
	}

	return json_encode($taglist);
}

