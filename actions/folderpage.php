<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function folderpage($lang, $folder, $page) {
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
	extract($r); /* thread_type thread_name thread_title thread_nocomment thread_nomorecomment */

	$folder_name = $thread_name;
	$folder_title = $thread_title;

	$r = thread_get_node($lang, $folder_id, $page_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_ignored node_name node_title node_abstract node_cloud node_nocomment node_nomorecomment */

	if ($node_ignored) {
		return run('error/notfound', $lang);
	}

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

	$edit=user_has_role('writer') ? url('folderedit', $_SESSION['user']['locale']) . '/'. $folder_id . '/'. $page_id . '?' . 'clang=' . $lang : false;
	$validate='/' . $lang . '/'. $page_name;
	$banner = build('banner', $lang, compact('edit', 'validate'));

	$content = view('folderpage', false, compact('page_title', 'page_contents', 'page_comment'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

