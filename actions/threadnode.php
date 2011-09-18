<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function threadnode($lang, $thread, $node) {
	global $system_languages;

	if (!user_has_role('writer')) {
		return run('error/unauthorized', $lang);
	}
	
	$slang=false;
	if (isset($_GET['slang'])) {
		$slang = $_GET['slang'];
	}
	else {
		$slang=$lang;
	}
	if (!in_array($slang, $system_languages)) {
		return run('error/notfound', $lang);
	}

	$thread_id = thread_id($thread);
	if (!$thread_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $thread_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_name thread_title thread_type thread_abstract thread_cloud thread_nocloud thread_nosearch thread_created thread_modified */

	$node_id = thread_node_id($thread_id, $node);
	if (!$node_id) {
		return run('error/notfound', $lang);
	}

	$r = node_get($lang, $node_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_name node_title node_abstract node_cloud node_created node_modified */

	$node_contents = build('nodecontent', $lang, $node_id);

	$headline_text=$thread_title;
	$headline_url=url('thread', $lang) . '/' . $thread_name . '?' . 'slang=' . $slang;
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('title'));

	$prev_node_label=$prev_node_url=false;
	$r=thread_node_prev($lang, $thread_id, $node_id, false);
	if ($r) {
		extract($r);
		$prev_node_label = $prev_node_title ? $prev_node_title : $prev_node_number;
		$prev_node_url=url('thread', $lang) . '/'. $thread_name . '/'. $prev_node_name . '?' . 'slang=' . $slang;
	}

	$next_node_label=$next_node_url=false;
	$r=thread_node_next($lang, $thread_id, $node_id, false);
	if ($r) {
		extract($r);
		$next_node_label = $next_node_title ? $next_node_title : $next_node_number;
		$next_node_url=url('thread', $lang) . '/'. $thread_name . '/'. $next_node_name . '?' . 'slang=' . $slang;
	}

	head('title', $thread_title);
	head('description', $node_abstract);
	head('keywords', $node_cloud);
	head('robots', 'noindex, nofollow');

	$edit=user_has_role('writer') ? url('threadedit', $_SESSION['user']['locale']) . '/' . $thread_id . '/' . $node_id . '?' . 'clang=' . $lang : false;
	$validate=url('thread', $lang) . '/'. $thread_id . '/'. $node_id;
	$banner = build('banner', $lang, compact('headline', 'edit', 'validate'));

	$content = view('threadnode', $slang, compact('node_name', 'node_title', 'node_abstract', 'node_cloud', 'node_created', 'node_modified', 'node_contents', 'prev_node_url', 'prev_node_label', 'next_node_url', 'next_node_label'));

	$output = layout('standard', compact('banner', 'content', 'sidebar'));

	return $output;
}

