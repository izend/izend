<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function story($lang, $arglist=false) {
	$story=$page=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$story=$arglist[0];
		}
		if (isset($arglist[1])) {
			$page=$arglist[1];
		}
	}

	if (!$story) {
		return run('error/notfound', $lang);
	}

	$story_id = thread_id($story);
	if (!$story_id) {
		return run('error/notfound', $lang);
	}

	$page_id = thread_node_id($story_id, $page);
	if (!$page_id and $page) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $story_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud thread_nocloud thread_nosearch thread_nocomment thread_nomorecomment */

	if ($thread_type != 'story') {
		return run('error/notfound', $lang);
	}

	$story_name = $thread_name;
	$story_title = $thread_title;
	$story_abstract = $thread_abstract;
	$story_cloud = $thread_cloud;
	$story_nocloud = $thread_nocloud;
	$story_nosearch = $thread_nosearch;

	if (!$page_id) {
		head('title', $story_title);
		head('description', $story_abstract);
		head('keywords', $story_cloud);

		$edit=user_has_role('writer') ? url('storyedit', $_SESSION['user']['locale']) . '/'. $story_id . '?' . 'clang=' . $lang : false;
		$validate=url('story', $lang) . '/' . $story_name;
		$banner = build('banner', $lang, compact('edit', 'validate'));

		$content = false;

		$output = layout('standard', compact('banner', 'content'));

		return $output;
	}

	$r = thread_get_node($lang, $story_id, $page_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_name node_title node_abstract node_cloud node_nocomment node_nomorecomment */

	$page_name=$node_name;
	$page_title=$node_title;
	$page_number=$node_number;

	$page_contents = build('nodecontent', $lang, $page_id);

	$page_comment=false;
	if (!($thread_nocomment or $node_nocomment)) {
		$page_url = url('story', $lang) . '/'. $story_name . '/' . $page_name;
		$page_comment = build('nodecomment', $lang, $page_id, $page_url, ($thread_nomorecomment or $node_nomorecomment));
	}

	$search=false;
	if (!$story_nosearch) {
		$search_text='';
		$search_url= url('search', $lang) . '/'. $story_name;
		$search=view('searchinput', $lang, compact('search_url', 'search_text'));
	}

	$cloud=false;
	if (!$story_nocloud) {
		$cloud = build('cloud', $lang, $story_id, 50, true, true);
	}

	$summary=array();
	$r = thread_get_contents($lang, $story_id);
	if ($r) {
		$story_url = url('story', $lang) . '/'. $story_name;
		foreach ($r as $c) {
			extract($c);	/* node_id node_name node_title node_number */
			$node_url=$story_url . '/' . $node_name;
			$summary[] = array($node_title, $node_url);
		}
	}

	$headline_text=$story_title;
	$headline_url=false;
	$headline=compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('search', 'cloud', 'title', 'summary'));

	head('title', $story_title);
	head('description', empty($node_abstract) ? $story_abstract : $node_abstract);
	head('keywords', $node_cloud);

	$search=!$story_nosearch ? compact('search_url', 'search_text') : false;
	$edit=user_has_role('writer') ? url('storyedit', $_SESSION['user']['locale']) . '/'. $story_id . '/' . $page_id . '?' . 'clang=' . $lang : false;
	$validate=url('story', $lang) . '/' . $story_name . '/' . $page_name;
	$banner = build('banner', $lang, compact('headline', 'edit', 'validate', 'search'));

	$content = view('storycontent', false, compact('page_title', 'page_contents', 'page_comment', 'page_number'));

	$output = layout('standard', compact('banner', 'sidebar', 'content'));

	return $output;
}
