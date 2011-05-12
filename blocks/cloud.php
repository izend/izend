<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/cloud.inc';

require_once 'strflat.php';

function cloud($lang, $cloud, $size=false, $sort=false, $index=true) {
	$cloud_id = cloud_id($cloud);
	if (!$cloud_id) {
		return false;
	}

	$r = cloud_get($lang, $cloud_id);
	if (!$r) {
		return false;
	}
	extract($r); /* cloud_name cloud_title */

	$linklist=false;
	$r = cloud_list_tags($lang, $cloud_id, $size);

	if ($r) {
		if ($size > 0 && $size < count($r)) {
			shuffle($r);
			$r = array_slice($r, 0, $size);
		}
		if ($sort) {
			usort($r, create_function('$a1, $a2', 'return strnatcasecmp(strflat($a1[\'tag_name\']), strflat($a2[\'tag_name\']));'));
		}
		$linklist = array();
		$cloud_url = url('search', $lang) . '/'. $cloud_name;
		foreach ($r as $tag) {
			extract($tag);	/* tag_id tag_name tag_count */
			$name=$tag_name;
			$url=$cloud_url . '?' . 'q=' . urlencode($tag_name);
			$linklist[] = compact('name', 'url');
		}
		if ($index) {
			$index=$cloud_url;
		}
	}

	$output = view('cloud', false, compact('linklist', 'index'));

	return $output;
}

