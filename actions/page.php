<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function page($lang, $arglist=false) {
	$page=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$page=$arglist[0];
		}
	}

	if (!$page) {
		return run('error/notfound', $lang);
	}

	$thread_id = 1;

	$r = thread_get($lang, $thread_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud thread_nocloud thread_nosearch thread_nocomment thread_nomorecomment */

	$page_id = thread_node_id($thread_id, $page);
	if (!$page_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get_node($lang, $thread_id, $page_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_name node_title node_abstract node_cloud node_nocomment node_nomorecomment */

	$page_name=$node_name;
	$page_title=$node_title;
	$page_abstract=$node_abstract;
	$page_cloud=$node_cloud;

	$page_contents = build('nodecontent', $lang, $page_id);

	$page_url = '/' . $lang . '/' . $page_name;
	$page_comment = ($thread_nocomment or $node_nocomment) ? false : build('nodecomment', $lang, $page_id, $page_url, ($thread_nomorecomment or $node_nomorecomment));

	head('title', $page_title);
	head('description', $page_abstract);
	head('keywords', $page_cloud);

	$edit=user_has_role('writer') ? url('editpage', $_SESSION['user']['locale']) . '/'. $page_id . '?' . 'clang=' . $lang : false;
	$validate='/' . $lang . '/'. $page_name;
	$banner = build('banner', $lang, compact('edit', 'validate'));

	$content = view('page', false, compact('page_title', 'page_contents', 'page_comment'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

