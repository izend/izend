<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
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
	extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud thread_nocomment thread_nomorecomment */

	$folder_name = $thread_name;
	$folder_title = $thread_title;
	$folder_abstract = $thread_abstract;
	$folder_cloud = $thread_cloud;

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

	$page_url = '/' . $lang . '/' . $page_name;
	$page_comment = ($thread_nocomment or $node_nocomment) ? false : build('nodecomment', $lang, $page_id, $page_url, ($thread_nomorecomment or $node_nomorecomment));

	$content = view('folderpage', false, compact('page_title', 'page_contents', 'page_comment'));

	$edit=user_has_role('writer') ? url('folderedit', $_SESSION['user']['locale']) . '/'. $folder_id . '/'. $page_id . '?' . 'clang=' . $lang : false;
	$validate='/' . $lang . '/'. $page_name;
	$banner = build('banner', $lang, compact('edit', 'validate'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

