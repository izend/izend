<?php

/**
 *
 * @copyright  2010-2019 izend.org
 * @version    32
 * @link       http://www.izend.org
 */

require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'models/thread.inc';

function folderpage($lang, $folder, $page) {
	global $with_toolbar, $siteshot;

	$folder_id = thread_id($folder);
	if (!$folder_id) {
		return run('error/notfound', $lang);
	}

	$page_id = thread_node_id($folder_id, $page, $lang);
	if (!$page_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $folder_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud thread_image */

	if ($thread_type != 'folder') {
		return run('error/notfound', $lang);
	}

	$folder_name = $thread_name;
	$folder_title = $thread_title;
	$folder_abstract = $thread_abstract;
	$folder_cloud = $thread_cloud;
	$folder_image = $thread_image;

	$r = thread_get_node($lang, $folder_id, $page_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_ignored node_name node_title node_abstract node_cloud node_image node_user_id node_visits node_nocomment node_nomorecomment node_novote node_nomorevote node_ilike node_tweet node_linkedin node_pinit node_whatsapp */

	if ($node_ignored) {
		return run('error/notfound', $lang);
	}

	$page_user_id=$node_user_id;
	$page_name=$node_name;
	$page_title=$node_title;
	$page_abstract=$node_abstract;
	$page_cloud=$node_cloud;
	$page_image=$node_image;
	$page_modified=$node_modified;

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
	if ($page_image) {
		head('image', $page_image);
	}
	else if ($folder_image) {
		head('image', $folder_image);
	}
	head('date', $page_modified);

	$page_contents = build('nodecontent', $lang, $page_id);

	$page_comment=false;
	if (!($thread_nocomment or $node_nocomment)) {
		$nomore=(!$page_contents or $thread_nomorecomment or $node_nomorecomment) ? true : false;
		$page_url = url('folder', $lang) . '/' . $folder_name. '/' . $page_name;
		$page_comment = build('nodecomment', $lang, $page_id, $page_user_id, $page_url, $nomore);
	}

	$vote=false;
	if (!($thread_novote or $node_novote)) {
		$nomore=(!$page_contents or $thread_nomorevote or $node_nomorevote) ? true : false;
		$vote=build('vote', $lang, $page_id, 'node', $nomore);
	}

	$visits=false;
	if ($thread_visits and $node_visits) {
		$nomore=user_has_role('writer');
		$visits=build('visits', $lang, $page_id, $nomore);
	}

	$besocial=$sharebar=false;
	if ($page_contents or $page_comment) {
		$ilike=$thread_ilike && $node_ilike;
		$tweetit=$thread_tweet && $node_tweet;
		$linkedin=$thread_linkedin && $node_linkedin;
		$pinit=$thread_pinit && $node_pinit;
		$whatsapp=$thread_whatsapp && $node_whatsapp;
		if ($tweetit) {
			$tweet_text=$node_abstract ? $node_abstract : ($node_title ? $node_title : $thread_title);
			$tweetit=$tweet_text ? compact('tweet_text') : true;
		}
		if ($pinit) {
			$pinit_text=$node_abstract ? $node_abstract : ($node_title ? $node_title : $thread_title);
			$pinit_image=$node_image ? $node_image : $siteshot;
			$pinit=$pinit_text && $pinit_image ? compact('pinit_text', 'pinit_image') : true;
		}
		list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'linkedin', 'pinit', 'whatsapp'));
	}

	$content = view('folderpage', false, compact('page_title', 'page_contents', 'page_comment', 'besocial', 'vote', 'visits'));

	$edit=user_has_role('writer') ? url('folderedit', $_SESSION['user']['locale']) . '/'. $folder_id . '/'. $page_id . '?' . 'clang=' . $lang : false;
	$validate=url('folder', $lang) . '/'. $folder_name . '/' . $page_name;

	$banner = build('banner', $lang, $with_toolbar ? false : compact('edit', 'validate'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$output = layout('standard', compact('lang', 'sharebar', 'toolbar', 'banner', 'content'));

	return $output;
}

