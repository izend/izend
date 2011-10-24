<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'models/cloud.inc';

function cloud($lang, $cloud_id, $node_id, $size=false, $byname=false, $bycount=false, $index=true) {
	$search_url = url('search', $lang);
	if (!$search_url) {
		return false;
	}

	$cloud_name=false;
	if ($cloud_id) {
		$r = cloud_get($lang, $cloud_id);
		if (!$r) {
			return false;
		}
		extract($r); /* cloud_name */
	}

	$linklist=false;

	$r = cloud_list_tags($lang, $cloud_id, $node_id, $byname, $bycount);

	if ($r) {
		if ($size > 0 && $size < count($r)) {
			$r = array_intersect_key($r, array_flip(array_rand($r, $size)));
		}
		$linklist = array();
		$cloud_url = $cloud_name ? $search_url . '/'. $cloud_name : $search_url;
		foreach ($r as $tag) {
			extract($tag);	/* tag_id tag_name tag_count */
			$name=$tag_name;
			$count=$tag_count;
			$url=$cloud_url . '?' . 'q=' . urlencode($tag_name);
			$linklist[] = compact('name', 'count', 'url');
		}
		if ($index) {
			$index=$cloud_url;
		}
	}

	$output = view('cloud', false, compact('linklist', 'index'));

	return $output;
}

