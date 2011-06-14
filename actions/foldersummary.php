<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function foldersummary($lang, $folder) {
	$folder_id = thread_id($folder);
	if (!$folder_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $folder_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_name thread_title thread_abstract thread_cloud */

	$folder_name = $thread_name;
	$folder_title = $thread_title;
	$folder_abstract = $thread_abstract;
	$folder_cloud = $thread_cloud;

	$folder_contents = array();
	$r = thread_get_contents($lang, $folder_id);
	if ($r) {
		$folder_url = url('folder', $lang) . '/'. $folder_name;
		foreach ($r as $c) {
			extract($c);	/* node_name node_title */
			$page_title = $node_title;
			$page_url = $folder_url  . '/' . $node_name;
			$folder_contents[] = compact('page_title' , 'page_url');
		}
	}

	head('title', $folder_title);
	head('description', $folder_abstract);
	head('keywords', $folder_cloud);

	$edit=user_has_role('writer') ? url('folderedit', $_SESSION['user']['locale']) . '/'. $folder_id . '?' . 'clang=' . $lang : false;
	$banner = build('banner', $lang, compact('edit'));

	$content = view('foldersummary', false, compact('folder_title', 'folder_contents'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

