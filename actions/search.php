<?php

/**
 *
 * @copyright  2010-2019 izend.org
 * @version    23
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'userhasrole.php';
require_once 'models/cloud.inc';
require_once 'models/thread.inc';

function search($lang, $arglist=false) {
	global $search_default, $search_all, $search_pertinence, $search_cloud, $limited_languages;

	$cloud=$search_default;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$cloud=$arglist[0];
		}
	}

	$cloud_id=$cloud_name=false;
	$thread_nocloud=$thread_nosearch=false;

	$clang=$lang;

	if ($cloud) {
		$cloud_id = cloud_id($cloud);
		if (!$cloud_id) {
			return run('error/notfound', $lang);
		}

		$thread_type = thread_type($cloud_id);

		if ($thread_type == 'rss') {
			if (!user_has_role('administrator')) {
				return run('error/unauthorized', $lang);
			}
		}

		if (isset($limited_languages[$thread_type]) and !in_array($clang, $limited_languages[$thread_type])) {
			$clang = $limited_languages[$thread_type][0];
		}

		$r = cloud_get($clang, $cloud_id);
		if (!$r) {
			return run('error/notfound', $lang);
		}
		extract($r); /* cloud_name cloud_title cloud_action */

		$r = thread_get($clang, $cloud_id);
		if (!$r) {
			return run('error/notfound', $lang);
		}
		extract($r); /* thread_type thread_nocloud thread_nosearch */

		if ($thread_type == 'thread' or ($thread_nosearch and $thread_nocloud)) {
			return run('error/notfound', $lang);
		}

		if ($cloud_id == $search_default) {
			$cloud_name=false;
		}
	}
	else {
		if ($search_all === false) {
			return run('error/notfound', $lang);
		}
		if ($search_all !== true) {
			$thread_nosearch=true;
		}
	}

	$searchtext=$rsearch=false;

	$action='none';
	if (!$thread_nosearch) {
		if (isset($_POST['search'])) {
			$action='search';
		}
	}

	$taglist=false;

	switch($action) {
		case 'none':
			if (!empty($arglist['q'])) {
				$searchtext=$arglist['q'];
				$taglist=explode(' ', $searchtext);
			}
			break;
		case 'search':
			if (isset($_POST['searchtext'])) {
				$searchtext=readarg($_POST['searchtext'], true, false);	// trim but DON'T strip!

				if ($searchtext) {
					global $search_distance, $search_closest;

					$taglist=cloud_match($clang, $cloud_id, $searchtext, $search_distance, $search_closest);
				}
			}
			break;
		default:
			break;
	}

	if ($taglist) {
		$rsearch=cloud_search($clang, $cloud_id, $taglist, $search_pertinence);
	}

	$search_title=translate('search:title', $lang);

	$search_url=false;

	$search=$cloud=$title=false;

	if ($rsearch) {
		if (!$thread_nosearch) {
			$search_url=url('search', $lang, $cloud_name);
		}
		if (!$thread_nocloud) {
			$cloud_url=url('search', $lang, $cloud_name);
			$byname=$bycount=$index=true;
			$cloud = build('cloud', $clang, $cloud_url, $cloud_id, false, $search_cloud, compact('byname', 'bycount', 'index'));
		}
		$headline_text=$search_title;
		$headline_url=false;
		$headline = compact('headline_text', 'headline_url');
		$title = view('headline', false, $headline);

		$content = build('searchlist', $lang, $rsearch, $taglist);
	}
	else {
		$url=url('search', $lang, $cloud_name);
		if (!$thread_nosearch) {
			$search_url=$url;
		}
		$cloud_url=$url;
		$headline_text=$cloud_id ? $cloud_title : $search_title;
		$headline_url=false;
		$headline = compact('headline_text', 'headline_url');
		$title = view('headline', false, $headline);

		$byname=true;
		$bycount=$index=false;
		$content = build('cloud', $clang, $cloud_url, $cloud_id, false, false, compact('byname', 'bycount', 'index'));
	}

	if ($search_url) {
		$search_text=$searchtext;
		$suggest_url=url('suggest', $lang, $cloud_name);
		$search=view('searchinput', $lang, compact('search_url', 'search_text', 'suggest_url'));
	}

	$sidebar = view('sidebar', false, compact('search', 'cloud', 'title'));

	if ($search) {
		$search=compact('search_url', 'search_text', 'suggest_url');
	}
	$banner = build('banner', $lang, compact('headline', 'search'));

	head('title', $cloud_id ? $cloud_title : $search_title);
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$output = layout('standard', compact('lang', 'banner', 'content', 'sidebar'));

	return $output;
}

