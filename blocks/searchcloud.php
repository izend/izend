<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function searchcloud($lang, $searchtext, $cloud_id, $cloud_name) {
	$searchinput = true;
	$searchurl = url('search', $lang) . '/'. $cloud_name;
	$searchcloud = false;
	$searchbox = view('searchbox', $lang, compact('searchurl', 'searchinput', 'searchtext', 'searchcloud'));

	$cloud = build('cloud', $lang, $cloud_id, false, true);

	$output = view('searchcloud', $lang, compact('cloud', 'searchbox'));

	return $output;
}

