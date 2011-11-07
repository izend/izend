<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    10
 * @link       http://www.izend.org
 */

require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'models/thread.inc';

function folderpage($lang, $folder, $page) {
	global $with_toolbar, $socialize;

	$folder_id = thread_id($folder);
	if (!$folder_id) {
		return run('error/notfound', $lang);
	}

	$page_id = thread_node_id($folder_id, $page);
	if (!$page_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $folder_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud */

	$folder_name = $thread_name;
	$folder_title = $thread_title;
	$folder_abstract = $thread_abstract;
	$folder_cloud = $thread_cloud;

	$r = thread_get_node($lang, $folder_id, $page_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_ignored node_name node_title node_abstract node_cloud node_nocomment node_nomorecomment node_ilike node_tweet node_plusone */

	if ($node_ignored) {
		return run('error/notfound', $lang);
	}

	$page_name=$node_name;
	$page_title=$node_title;
	$page_abstract=$node_abstract;
	$page_cloud=$node_cloud;

	if ($page_title) {
		head('title', $page_title);
	}
	else if ($folder_title) {
		head('title', $folder_title);
	}
	if ($page_abstract) {
		head('description', $page_abstract);
	}
	else if ($folder_abstract) {
		head('description', $folder_abstract);
	}
	if ($page_cloud) {
		head('keywords', $page_cloud);
	}
	else if ($folder_cloud) {
		head('keywords', $folder_cloud);
	}

	$page_contents = build('nodecontent', $lang, $page_id);

	$page_comment = false;
	if (!($thread_nocomment or $node_nocomment)) {
		$moderate=user_has_role('moderator');
		$nomore=(!$page_contents or $thread_nomorecomment or $node_nomorecomment) ? true : false;
		$page_url = url('folder', $lang) . '/' . $folder_name. '/' . $page_name;
		$page_comment = build('nodecomment', $lang, $page_id, $page_url, $nomore, $moderate);
	}

	$besocial=$sharebar=false;
	if ($page_contents or $page_comment) {
		$ilike=$node_ilike;
		$tweetit=$node_tweet;
		$plusone=$node_plusone;
		if ($tweetit) {
			$tweet_text=$page_title ? $page_title : $folder_title;
			$tweetit=$tweet_text ? compact('tweet_text') : true;
		}
		list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone'));
	}

	$content = view('folderpage', false, compact('page_title', 'page_contents', 'page_comment', 'besocial'));

	$edit=user_has_role('writer') ? url('folderedit', $_SESSION['user']['locale']) . '/'. $folder_id . '/'. $page_id . '?' . 'clang=' . $lang : false;
	$validate='/' . $lang . '/'. $page_name;

	$banner = build('banner', $lang, $with_toolbar ? false : compact('edit', 'validate'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$output = layout('standard', compact('sharebar', 'toolbar', 'banner', 'content'));

	return $output;
}

