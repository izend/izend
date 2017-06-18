<?php

/**
 *
 * @copyright  2010-2017 izend.org
 * @version    12
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'readarg.php';
require_once 'strtofname.php';
require_once 'models/thread.inc';

function threadeditnode($lang, $clang, $thread, $node) {
	global $with_toolbar, $supported_threads, $supported_contents, $limited_contents;

	$thread_id = thread_id($thread);
	if (!$thread_id) {
		return run('error/notfound', $lang);
	}

	$node_id = thread_node_id($thread_id, $node, $clang);
	if (!$node_id) {
		return run('error/notfound', $lang);
	}

	$thread_name=$thread_title=$thread_abstract=$thread_cloud=$thread_type=false;
	$r = thread_get($clang, $thread_id, false);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_name thread_title thread_abstract thread_cloud thread_type */

	if (!in_array($thread_type, $supported_threads)) {
		return run('error/badrequest', $lang);
	}

	$content_types=$supported_contents;
	if ($thread_type and $limited_contents and array_key_exists($thread_type, $limited_contents)) {
		$content_types=$limited_contents[$thread_type];
	}
	$node_editor = build('nodeeditor', $lang, $clang, $node_id, $content_types);

	$node_name=$node_title=false;
	$r = thread_get_node($clang, $thread_id, $node_id, false);
	if ($r) {
		extract($r); /* node_name node_title */
	}

	head('title', $thread_title ? $thread_title : $thread_id);
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$headline_text=$thread_title ? $thread_title : $thread_id;
	$headline_url=url('threadedit', $lang) . '/'. $thread_id . '?' . 'clang=' . $clang;
	$headline = compact('headline_text', 'headline_url');
	$view=$node_name ? url('thread', $lang) . '/'. $thread_id . '/'. $node_id . '?' . 'clang=' . $clang : false;

	$banner = build('banner', $lang, $with_toolbar ? compact('headline') : compact('headline', 'view'));

	$scroll=true;
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('view', 'scroll')) : false;

	$prev_node_label=$prev_node_url=false;
	$r=thread_node_prev($clang, $thread_id, $node_id, false);
	if ($r) {
		extract($r);
		$prev_node_label = $prev_node_title ? $prev_node_title : $prev_node_id;
		$prev_node_url=url('threadedit', $lang) . '/'. $thread_id . '/'. $prev_node_id . '?' . 'clang=' . $clang;
	}

	$next_node_label=$next_node_url=false;
	$r=thread_node_next($clang, $thread_id, $node_id, false);
	if ($r) {
		extract($r);
		$next_node_label = $next_node_title ? $next_node_title : $next_node_id;
		$next_node_url=url('threadedit', $lang) . '/'. $thread_id . '/'. $next_node_id . '?' . 'clang=' . $clang;
	}

	$title = view('headline', false, $headline);
	$sidebar = view('sidebar', false, compact('title'));

	$content = view('editing/threadeditnode', $lang, compact('node_editor', 'node_id', 'node_title', 'prev_node_url', 'prev_node_label', 'next_node_url', 'next_node_label'));

	$output = layout('editing', compact('toolbar', 'banner', 'content', 'sidebar'));

	return $output;
}

