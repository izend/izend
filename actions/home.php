<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/node.inc';

function home($lang) {
	global $root_node;

	$r = node_get($lang, $root_node);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_name node_title node_abstract node_cloud node_created node_modified */

	head('title', translate('home:title', $lang));
	if ($node_abstract) {
		head('description', $node_abstract);
	}
	if ($node_cloud) {
		head('keywords', $node_cloud);
	}

	$languages='home';
	$contact=$account=true;
	$edit=user_has_role('writer') ? url('editpage', $_SESSION['user']['locale']) . '/'. $root_node . '?' . 'clang=' . $lang : false;
	$validate=url('home', $lang);
	$banner = build('banner', $lang, compact('languages', 'contact', 'account', 'edit', 'validate'));

	$contact_page=url('contact', $lang);
	$footer = view('footer', $lang, compact('contact_page'));

	$content = build('nodecontent', $lang, $root_node);

	$output = layout('standard', compact('footer', 'banner', 'content'));

	return $output;
}

