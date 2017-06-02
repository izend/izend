<?php

/**
 *
 * @copyright  2010-2017 izend.org
 * @version    15
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function threadnode($lang, $clang, $thread, $node) {
	global $with_toolbar;

	$thread_id = thread_id($thread);
	if (!$thread_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($clang, $thread_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_name thread_title thread_type thread_abstract thread_cloud thread_image thread_nocloud thread_nosearch thread_created thread_modified */

	$node_id = thread_node_id($thread_id, $node, $clang);
	if (!$node_id) {
		return run('error/notfound', $lang);
	}

	$r = node_get($clang, $node_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_ignored node_name node_title node_abstract node_cloud node_image node_visits node_nocomment node_nomorecomment node_novote node_nomorevote node_ilike node_tweet node_plusone node_linkedin node_pinit */

	$node_comment=!$node_nocomment;
	$node_morecomment=!$node_nomorecomment;
	$node_vote=!$node_novote;
	$node_morevote=!$node_nomorevote;

	$node_contents = build('nodecontent', $clang, $node_id);

	$headline_text=$thread_title ? $thread_title : $thread_id;
	$headline_url=url('thread', $lang) . '/' . $thread_id . '?' . 'clang=' . $clang;
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('title'));

	$prev_node_label=$prev_node_url=false;
	$r=thread_node_prev($clang, $thread_id, $node_id, false);
	if ($r) {
		extract($r);
		$prev_node_label = $prev_node_title ? $prev_node_title : $prev_node_id;
		$prev_node_url=url('thread', $lang) . '/'. $thread_id . '/'. $prev_node_id . '?' . 'clang=' . $clang;
	}

	$next_node_label=$next_node_url=false;
	$r=thread_node_next($clang, $thread_id, $node_id, false);
	if ($r) {
		extract($r);
		$next_node_label = $next_node_title ? $next_node_title : $next_node_id;
		$next_node_url=url('thread', $lang) . '/'. $thread_id . '/'. $next_node_id . '?' . 'clang=' . $clang;
	}

	$inlanguages=view('inlanguages', false, compact('clang'));

	head('title', $thread_title ? $thread_title : $thread_id);
	head('description', $node_abstract);
	head('keywords', $node_cloud);
	head('robots', 'noindex, nofollow');

	$edit=user_has_role('writer') ? url('threadedit', $_SESSION['user']['locale']) . '/' . $thread_id . '/' . $node_id . '?' . 'clang=' . $clang : false;

	$banner = build('banner', $lang, $with_toolbar ? compact('headline') : compact('headline', 'edit'));

	$scroll=true;
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'scroll')) : false;

	$content = view('threadnode', $lang, compact('node_id', 'node_name', 'node_title', 'node_abstract', 'node_cloud', 'node_image', 'node_created', 'node_modified', 'node_visits', 'node_comment', 'node_morecomment', 'node_vote', 'node_morevote', 'node_ilike', 'node_tweet', 'node_plusone', 'node_linkedin', 'node_pinit', 'node_contents', 'prev_node_url', 'prev_node_label', 'next_node_url', 'next_node_label', 'inlanguages'));

	$output = layout('viewing', compact('toolbar', 'banner', 'content', 'sidebar'));

	return $output;
}

