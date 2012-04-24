<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'models/cloud.inc';

function search($lang, $arglist=false) {
	$cloud=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$cloud=$arglist[0];
		}
	}

	$cloud_id=$cloud_name=false;

	if ($cloud) {
		$cloud_id = cloud_id($cloud);
		if (!$cloud_id) {
			return run('error/notfound', $lang);
		}

		$r = cloud_get($lang, $cloud_id);
		if (!$r) {
			return run('error/notfound', $lang);
		}
		extract($r); /* cloud_name cloud_title cloud_action */

		$r = thread_get($lang, $cloud_id);
		if (!$r) {
			return run('error/notfound', $lang);
		}
		extract($r); /* thread_type thread_nocloud thread_nosearch */

		if ($thread_nosearch and $thread_nocloud) {
			return run('error/notfound', $lang);
		}
	}

	$action='none';
	if (isset($_POST['search'])) {
		$action='search';
	}

	$searchtext=$taglist=false;
	$rsearch=false;
	switch($action) {
		case 'none':
			$tag=isset($arglist['q']) ? $arglist['q'] : false;
			if ($tag) {
				$taglist=array($tag);
				$searchtext=$tag;
			}
			break;
		case 'search':
			if (isset($_POST['searchtext'])) {
				$searchtext=readarg($_POST['searchtext'], true, false);	// trim but DON'T strip!

				if ($searchtext) {
					global $search_distance, $search_closest;

					$taglist=cloud_match($lang, $cloud_id, $searchtext, $search_distance, $search_closest);
				}
			}
			break;
		default:
			break;
	}

	if ($taglist) {
		$rsearch=cloud_search($lang, $cloud_id, $taglist);
	}

	$search_title=translate('search:title', $lang);

	$search_url=false;

	$search=$cloud=false;

	if ($rsearch) {
		if ($cloud_id) {
			if (!$thread_nosearch) {
				$search_url=url('search', $lang, $cloud_name);
			}
			if (!$thread_nocloud) {
				$cloud = build('cloud', $lang, $cloud_id, false, 60, true, true);
			}
		}
		else {
			$search_url=url('search', $lang);
			$cloud = build('cloud', $lang, false, false, 60, true, true);
		}
		$headline_text=$search_title;
		$headline_url=false;
		$headline = compact('headline_text', 'headline_url');
		$title = view('headline', false, $headline);

		$content = build('searchlist', $lang, $rsearch, $taglist);
	}
	else {
		if ($cloud_id) {
			$headline_text=$cloud_title;
			$headline_url=false;
			if (!$thread_nosearch) {
				$search_url=url('search', $lang, $cloud_name);
			}
		}
		else {
			$headline_text=$search_title;
			$headline_url=false;
			$search_url=url('search', $lang);
		}
		$headline = compact('headline_text', 'headline_url');
		$title = view('headline', false, $headline);

		$content = build('cloud', $lang, $cloud_id, false, false, true, false, false);
	}

	if ($search_url) {
		$search_text=$searchtext;
		$suggest_url=url('suggest', $lang, $cloud_name);
		$search=view('searchinput', $lang, compact('search_url', 'search_text', 'suggest_url'));
	}

	$sidebar = view('sidebar', false, compact('search', 'cloud', 'title'));

	if ($search) {
		$search=compact('search_url', 'search_text', 'suggest_url', 'suggest_url');
	}
	$banner = build('banner', $lang, compact('headline', 'search'));

	head('title', $cloud_id ? $cloud_title : $search_title);
	head('description', false);
	head('keywords', false);

	$output = layout('standard', compact('banner', 'content', 'sidebar'));

	return $output;
}

