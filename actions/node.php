<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/node.inc';

function node($lang, $arglist=false) {
	global $system_languages;

	$node=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$node=$arglist[0];
		}
	}

	if (!$node) {
		return run('error/notfound', $lang);
	}

	$node_id = node_id($node);
	if (!$node_id) {
		return run('error/notfound', $lang);
	}

	$r = node_get($lang, $node_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_name node_title node_abstract node_cloud node_created node_modified */

	head('title', $node_id);
	head('description', $node_abstract);
	head('keywords', $node_cloud);
	head('robots', 'noindex, nofollow');

	$edit=user_has_role('writer') ? url('editnode', $_SESSION['user']['locale']) . '/'. $node_id . '?' . 'clang=' . $lang : false;
	$banner = build('banner', $lang, compact('edit'));

	$node_url = 0 . '/'. $node_id;
	$node_contents = build('nodecontent', $lang, $node_id);

	$content = view('node', $lang, compact('node_name', 'node_title', 'node_abstract', 'node_cloud', 'node_contents', 'node_created', 'node_modified'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

