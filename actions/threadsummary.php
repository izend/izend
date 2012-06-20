<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    10
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function threadsummary($lang, $thread) {
	global $system_languages, $with_toolbar;

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
	extract($r); /* thread_name thread_title thread_type thread_abstract thread_cloud thread_nosearch thread_nocloud thread_nocomment thread_nomorecomment thread_novote thread_nomorevote thread_created thread_modified */

	$thread_search=!$thread_nosearch;
	$thread_tag=!$thread_nocloud;
	$thread_comment=!$thread_nocomment;
	$thread_morecomment=!$thread_nomorecomment;
	$thread_vote=!$thread_novote;
	$thread_morevote=!$thread_nomorevote;

	$thread_contents = array();
	$r = thread_get_contents($lang, $thread_id);
	if ($r) {
		$thread_url = url('thread', $lang) . '/'. $thread_name;
		foreach ($r as $c) {
			extract($c);	/* node_id node_name node_title node_number node_ignored */
			$node_url = $thread_url . '/' . ($node_name ? $node_name : $node_id) . '?' . 'slang=' . $slang;
			$thread_contents[] = compact('node_id', 'node_title' , 'node_url', 'node_ignored');
		}
	}

	$headline_text=	translate('threadall:title', $slang);
	$headline_url=url('thread', $lang) . '?' . 'slang=' . $slang;;
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('title'));

	head('title', $thread_title ? $thread_title : $thread_id);
	head('description', $thread_abstract);
	head('keywords', $thread_cloud);
	head('robots', 'noindex, nofollow');

	$edit=user_has_role('writer') ? url('threadedit', $_SESSION['user']['locale']) . '/'. $thread_id . '?' . 'clang=' . $lang : false;
	$validate=url('thread', $lang) . '/'. $thread_name;

	$banner = build('banner', $lang, $with_toolbar ? compact('headline') : compact('headline', 'edit', 'validate'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$content = view('threadsummary', $slang, compact('thread_id', 'thread_title', 'thread_abstract', 'thread_cloud', 'thread_search', 'thread_tag', 'thread_comment', 'thread_morecomment', 'thread_vote', 'thread_morevote', 'thread_ilike', 'thread_tweet', 'thread_plusone', 'thread_linkedin', 'thread_created', 'thread_modified', 'thread_contents'));

	$output = layout('viewing', compact('toolbar', 'banner', 'content', 'sidebar'));

	return $output;
}

