<?php

/**
 *
 * @copyright  2010-2017 izend.org
 * @version    17
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function threadsummary($lang, $clang, $thread) {
	global $with_toolbar, $supported_threads;

	$thread_id = thread_id($thread);
	if (!$thread_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($clang, $thread_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_name thread_title thread_type thread_abstract thread_cloud thread_image thread_visits thread_nosearch thread_nocloud thread_nocomment thread_nomorecomment thread_novote thread_nomorevote thread_created thread_modified */

	$thread_search=!$thread_nosearch;
	$thread_tag=!$thread_nocloud;
	$thread_comment=!$thread_nocomment;
	$thread_morecomment=!$thread_nomorecomment;
	$thread_vote=!$thread_novote;
	$thread_morevote=!$thread_nomorevote;

	$thread_contents = array();
	$r = thread_get_contents($clang, $thread_id, false);
	if ($r) {
		$thread_url = url('thread', $lang) . '/'. $thread_id;
		foreach ($r as $c) {
			extract($c);	/* node_id node_name node_title node_number node_ignored */
			$node_url = $thread_url . '/' . $node_id . '?' . 'clang=' . $clang;
			$thread_contents[] = compact('node_id', 'node_title' , 'node_url', 'node_ignored');
		}
	}

	$inlanguages=view('inlanguages', false, compact('clang'));

	$headline_text=	translate('threadall:title', $lang);
	$headline_url=url('thread', $lang) . '?' . 'clang=' . $clang;;
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('title'));

	head('title', $thread_title ? $thread_title : $thread_id);
	head('description', $thread_abstract);
	head('keywords', $thread_cloud);
	head('robots', 'noindex, nofollow');

	$edit=(user_has_role('writer') and in_array($thread_type, $supported_threads)) ? url('threadedit', $_SESSION['user']['locale']) . '/'. $thread_id . '?' . 'clang=' . $clang : false;

	$banner = build('banner', $lang, $with_toolbar ? compact('headline') : compact('headline', 'edit'));

	$scroll=true;
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'scroll')) : false;

	$content = view('threadsummary', $lang, compact('thread_id', 'thread_title', 'thread_abstract', 'thread_cloud', 'thread_image', 'thread_visits', 'thread_search', 'thread_tag', 'thread_comment', 'thread_morecomment', 'thread_vote', 'thread_morevote', 'thread_ilike', 'thread_tweet', 'thread_plusone', 'thread_linkedin', 'thread_pinit', 'thread_created', 'thread_modified', 'thread_contents', 'inlanguages'));

	$output = layout('viewing', compact('toolbar', 'banner', 'content', 'sidebar'));

	return $output;
}

