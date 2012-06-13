<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'models/cloud.inc';

function cloud($lang, $cloud_url, $cloud_id=false, $node_id=false, $size=false, $options=false) {
	if (!$cloud_url) {
		return false;
	}

	$inclusive=false;
	$byname=$bycount=false;
	$index=true;
	$flat=false;

	extract($options, EXTR_IF_EXISTS);

	$linklist=false;

	$r = cloud_list_tags($lang, $cloud_id, $node_id, $byname, $bycount, $inclusive);

	if ($r) {
		if ($size > 0 && $size < count($r)) {
			$r = array_intersect_key($r, array_flip(array_rand($r, $size)));
		}
		$linklist = array();
		foreach ($r as $tag) {
			extract($tag);	/* tag_id tag_name tag_count */
			$name=$tag_name;
			$count=$tag_count;
			$url=$cloud_url . '?q=' . urlencode($tag_name);
			$linklist[] = compact('name', 'count', 'url');
		}
		if ($index) {
			$index=$cloud_url;
		}
	}

	$output = view('cloud', false, compact('linklist', 'index', 'flat'));

	return $output;
}

