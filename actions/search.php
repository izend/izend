<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'wmatch.php';

require_once 'models/cloud.inc';

function search($lang, $arglist=false) {
	$cloud=$tag=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$cloud=$arglist[0];
		}
	}

	if (!$cloud) {
		return run('error/notfound', $lang);
	}

	$cloud_id = cloud_id($cloud);
	if (!$cloud_id) {
		return run('error/notfound', $lang);
	}

	$r = cloud_get($lang, $cloud_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* cloud_name cloud_title */

	$r = thread_get($lang, $cloud_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud thread_nocloud thread_nosearch thread_nocomment thread_nomorecomment */

	$action='none';
	if (isset($_POST['search'])) {
		$action='search';
	}

	$searchtext=$tag=$taglist=false;
	$rsearch=false;
	switch($action) {
		case 'none':
			$tag=isset($arglist['q']) ? $arglist['q'] : false;
			if ($tag) {
				$taglist=array($tag);
			}
			break;
		case 'search':
			if (isset($_POST['searchtext'])) {
				$searchtext=readarg($_POST['searchtext']);
				preg_match_all('/(\S+)/', $searchtext, $r);
				$searchtext=implode(' ', array_slice(array_unique($r[0]), 0, 10));
			}
			if ($searchtext) {
				global $search_distance, $search_closest;

				$taglist=cloud_match($lang, $cloud_id, $searchtext, $search_distance, $search_closest);
			}
			break;
		default:
			break;
	}

	if ($taglist) {
		$rsearch=cloud_search($lang, $cloud_id, $taglist);
	}

	$search=false;
	if (!$thread_nosearch) {
		$search_text=$searchtext;
		$search_url=url('search', $lang) . '/'. $cloud_name;
		$search=view('searchinput', $lang, compact('search_url', 'search_text'));
	}

	$cloud=false;
	if (!$thread_nocloud and $rsearch) {
		$cloud = build('cloud', $lang, $cloud_id,  60, true, true);
	}

	$headline_text=$cloud_title;
	$headline_url=url($cloud_action, $lang) . '/'. $cloud_name;
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('search', 'cloud', 'title'));

	if ($rsearch) {
		$searchlist = build('searchlist', $lang, $searchtext, $cloud_id, $cloud_name, $cloud_action, $rsearch, $taglist);
		$content = view('search', $lang, compact('searchlist'));
	}
	else {
		$content = build('cloud', $lang, $cloud_id, false, true, false);
	}

	head('title', $cloud_title);
	head('description', false);
	head('keywords', false);

	$search=!$thread_nosearch ? compact('search_url', 'search_text') : false;
	$banner = build('banner', $lang, compact('headline', 'search'));

	$output = layout('standard', compact('banner', 'content', 'sidebar'));

	return $output;
}

