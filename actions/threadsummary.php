<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function threadsummary($lang, $thread) {
	$thread_id = thread_id($thread);
	if (!$thread_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $thread_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_name thread_title thread_type thread_abstract thread_cloud thread_nocloud thread_nosearch thread_created thread_modified */

	$thread_contents = array();
	$r = thread_get_contents($lang, $thread_id);
	if ($r) {
		$thread_url = url('thread', $lang) . '/'. $thread_name;
		foreach ($r as $c) {
			extract($c);	/* node_id node_name node_title node_number */
			$node_url = $thread_url  . '/' . $node_name;
			$thread_contents[] = compact('node_title' , 'node_url');
		}
	}

	head('title', $thread_title);
	head('description', $thread_abstract);
	head('keywords', $thread_cloud);

	$headline_text=	translate('threadall:title', $lang);
	$headline_url=url('thread', $lang);
	$headline = compact('headline_text', 'headline_url');
	$edit=user_has_role('writer') ? url('threadedit', $_SESSION['user']['locale']) . '/'. $thread_id . '?' . 'clang=' . $lang : false;
	$validate=url('thread', $lang) . '/'. $thread_name;
	$banner = build('banner', $lang, compact('headline', 'edit', 'validate'));

	$content = view('threadsummary', $lang, compact('thread_title', 'thread_abstract', 'thread_cloud', 'thread_created', 'thread_modified', 'thread_contents'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

