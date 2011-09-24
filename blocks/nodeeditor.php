<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'readarg.php';
require_once 'strtofname.php';
require_once 'models/node.inc';

function nodeeditor($lang, $clang, $node_id) {
	$action='init';
	if (isset($_POST['node_edit'])) {
		$action='edit';
	}

	$node_name=$node_title=$node_abstract=$node_cloud=$node_comment=$node_morecomment=false;
	$node_nocomment=$node_nomorecomment=true;

	switch($action) {
		case 'init':
		case 'reset':
			$r = node_get($clang, $node_id, false);
			if ($r) {
				extract($r);
			}
			$node_comment=!$node_nocomment;
			$node_morecomment=!$node_nomorecomment;

			break;
		case 'edit':
			if (isset($_POST['node_title'])) {
				$node_title=readarg($_POST['node_title']);
			}
			if (isset($_POST['node_name'])) {
				$node_name=strtofname(readarg($_POST['node_name']));
			}
			if (empty($node_name) and !empty($node_title)) {
				$node_name = strtofname($node_title);
			}
			if (isset($_POST['node_abstract'])) {
				$node_abstract=readarg($_POST['node_abstract']);
			}
			if (isset($_POST['node_cloud'])) {
				$node_cloud=readarg($_POST['node_cloud'], true, false);	// trim but DON'T strip!
				preg_match_all('/(\S+)/', $node_cloud, $r);
				$node_cloud=implode(' ', array_unique($r[0]));
			}
			if (isset($_POST['node_comment'])) {
				$node_comment=readarg($_POST['node_comment']) == 'on' ? true : false;
				$node_nocomment=!$node_comment;
			}
			if (isset($_POST['node_morecomment'])) {
				$node_morecomment=readarg($_POST['node_morecomment']) == 'on' ? true : false;
				$node_nomorecomment=!$node_morecomment;
			}
			break;
		default:
			break;
	}

	$missing_node_name=false;
	$bad_node_name=false;

	switch($action) {
		case 'edit':
			if (empty($node_name)) {
				$missing_node_name = true;
			}
			else if (!preg_match('#^[\w-]{3,}$#', $node_name)) {
				$bad_node_name = true;
			}
			break;
		default:
			break;
	}

	switch($action) {
		case 'edit':
			if ($missing_node_name or $bad_node_name) {
				break;
			}

			$r = node_set($clang, $node_id, $node_name, $node_title, $node_abstract, $node_cloud, $node_nocomment, $node_nomorecomment);

			if (!$r) {
				break;
			}

			break;

		default:
			break;
	}

	$content_editor = build('nodecontenteditor', $lang, $clang, $node_id);

	$inlanguages=view('inlanguages', false, compact('clang'));

	$errors = compact('missing_node_name', 'bad_node_name');

	$output = view('editing/nodeeditor', $lang, compact('clang', 'inlanguages', 'node_name', 'node_title', 'node_abstract', 'node_cloud', 'node_comment', 'node_morecomment', 'content_editor', 'errors'));

	return $output;
}

