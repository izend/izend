<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    12
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'readarg.php';
require_once 'strtofname.php';
require_once 'models/thread.inc';

function threadeditsummary($lang, $clang, $thread) {
	global $supported_threads, $with_toolbar;

	if (!user_has_role('writer')) {
		return run('error/unauthorized', $lang);
	}

	$confirmed=false;

	$thread_id = thread_id($thread);
	if (!$thread_id) {
		return run('error/notfound', $lang);
	}

	$action='init';
	if (isset($_POST['thread_edit'])) {
		$action='edit';
	}
	else if (isset($_POST['thread_reorder'])) {
		$action='reorder';
	}
	else if (isset($_POST['node_create'])) {
		$action='create';
	}
	else if (isset($_POST['node_delete'])) {
		$action='delete';
	}
	else if (isset($_POST['node_confirmdelete'])) {
		$action='delete';
		$confirmed=true;
	}
	else if (isset($_POST['node_hide'])) {
		$action='hide';
	}
	else if (isset($_POST['node_show'])) {
		$action='show';
	}

	$thread_type=$thread_name=$thread_title=$thread_abstract=$thread_cloud=$thread_search=$thread_tag=$thread_comment=$thread_morecomment=false;
	$thread_nosearch=$thread_nocloud=$thread_nocomment=$thread_nomorecomment=true;

	$new_node_name=$new_node_title=$new_node_number=false;
	$old_node_number=false;

	$thread_contents = false;
	$p=false;

	switch($action) {
		case 'init':
		case 'reset':
			$r = thread_get($clang, $thread_id, false);
			if ($r) {
				extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud thread_nosearch thread_nocloud thread_nocomment thread_nomorecomment */
			}
			$thread_search=!$thread_nosearch;
			$thread_tag=!$thread_nocloud;
			$thread_comment=!$thread_nocomment;
			$thread_morecomment=!$thread_nomorecomment;

			break;
		case 'edit':
		case 'create':
		case 'delete':
		case 'hide':
		case 'show':
		case 'reorder':
			if (isset($_POST['thread_type'])) {
				$thread_type=readarg($_POST['thread_type']);
			}
			if (isset($_POST['thread_title'])) {
				$thread_title=readarg($_POST['thread_title']);
			}
			if (isset($_POST['thread_name'])) {
				$thread_name=strtofname(readarg($_POST['thread_name']));
			}
			if (!$thread_name and $thread_title) {
				$thread_name = strtofname($thread_title);
			}
			if (isset($_POST['thread_abstract'])) {
				$thread_abstract=readarg($_POST['thread_abstract']);
			}
			if (isset($_POST['thread_cloud'])) {
				$thread_cloud=readarg($_POST['thread_cloud'], true, false);	// trim but DON'T strip!
				preg_match_all('/(\S+)/', $thread_cloud, $r);
				$thread_cloud=implode(' ', array_unique($r[0]));
			}
			if (isset($_POST['thread_search'])) {
				$thread_search=readarg($_POST['thread_search']) == 'on' ? true : false;
				$thread_nosearch=!$thread_search;
			}
			if (isset($_POST['thread_tag'])) {
				$thread_tag=readarg($_POST['thread_tag']) == 'on' ? true : false;
				$thread_nocloud=!$thread_tag;
			}
			if (isset($_POST['thread_comment'])) {
				$thread_comment=readarg($_POST['thread_comment']) == 'on' ? true : false;
				$thread_nocomment=!$thread_comment;
			}
			if (isset($_POST['thread_morecomment'])) {
				$thread_morecomment=readarg($_POST['thread_morecomment']) == 'on' ? true : false;
				$thread_nomorecomment=!$thread_morecomment;
			}
			if (isset($_POST['new_node_title'])) {
				$new_node_title=readarg($_POST['new_node_title']);
				$new_node_name = strtofname($new_node_title);
			}
			if (isset($_POST['new_node_number'])) {
				$new_node_number=readarg($_POST['new_node_number']);
			}
			if (isset($_POST['old_node_number'])) {
				$old_node_number=readarg($_POST['old_node_number']);
			}
			if (isset($_POST['p'])) {
				$p=$_POST['p'];	// DON'T readarg!
			}
			break;
		default:
			break;
	}

	$r = thread_get_contents($clang, $thread_id, false);	/* node_id node_number node_ignored node_name node_title node_cloud ... */

	if (count($p) != count($r)) {
		$p = false;
	}

	if ($r) {
		$pos=1;
		$thread_contents = array();
		$thread_url = url('threadedit', $lang) . '/'. $thread_id;
		foreach ($r as $c) {
			$c['node_url'] = $thread_url  . '/' . $c['node_id'];
			$c['pos'] = $p ? $p[$pos] : $pos;
			$thread_contents[$pos] = $c;
			$pos++;
		}
	}

	$missing_thread_name=false;
	$bad_thread_name=false;
	$missing_thread_type=false;
	$bad_thread_type=false;

	$missing_new_node_title=false;
	$bad_new_node_title=false;
	$bad_new_node_number=false;

	$missing_old_node_number=false;
	$bad_old_node_number=false;

	switch($action) {
		case 'edit':
			if (!$thread_name) {
				$missing_thread_name = true;
			}
			else if (!preg_match('#^[\w-]{3,}$#', $thread_name)) {
				$bad_thread_name = true;
			}
			if (!$thread_type) {
				$missing_thread_type = true;
			}
			else if (!in_array($thread_type, $supported_threads)) {
				$bad_thread_type = true;
			}
			break;

		case 'create':
			if (!$new_node_title) {
				$missing_new_node_title = true;
			}
			else if (!$new_node_name) {
				$bad_new_node_title = true;
			}
			else if (!preg_match('#^[\w-]{3,}$#', $new_node_name)) {
				$bad_new_node_title = true;
			}
			if (!$new_node_number) {
				$new_node_number = false;
			}
			else if (!is_numeric($new_node_number)) {
				$bad_new_node_number = true;
			}
			else if ($new_node_number < 1 or $new_node_number > count($thread_contents) + 1) {
				$bad_new_node_number = true;
			}
			break;

		case 'delete':
		case 'hide':
		case 'show':
			if (!$old_node_number) {
				$missing_old_node_number = true;
			}
			else if (!is_numeric($old_node_number)) {
				$bad_old_node_number = true;
			}
			else if ($old_node_number < 1 or $old_node_number > count($thread_contents)) {
				$bad_old_node_number = true;
			}
			break;

		case 'reorder':
			break;

		default:
			break;
	}

	$confirm_delete_node=false;

	switch($action) {
		case 'edit':
			if ($missing_thread_name or $bad_thread_name or $missing_thread_type or $bad_thread_type) {
				break;
			}

			$r = thread_set($clang, $thread_id, $thread_name, $thread_title, $thread_type, $thread_abstract, $thread_cloud, $thread_nosearch, $thread_nocloud, $thread_nocomment, $thread_nomorecomment);

			if (!$r) {
				break;
			}

			break;

		case 'create':
			if ($missing_new_node_title or $bad_new_node_title or $bad_new_node_number) {
				break;
			}

			$np = thread_create_node($clang, $thread_id, $new_node_name, $new_node_title, $new_node_number);
			if (!$np) {
				break;
			}

			extract($np);	/* node_id node_number node_ignored */
			$node_ignored = false;
			$node_title = $new_node_title;
			$node_ignored = false;
			$node_url = url('threadedit', $lang) . '/'. $thread_id . '/' . $node_id;
			$pos = $node_number;

			if ($thread_contents) {
				foreach ($thread_contents as &$c) {
					if ($c['node_number'] >= $pos) {
						$c['node_number']++;
					}
					if ($c['pos'] >= $pos) {
						$c['pos']++;
					}
				}
				array_splice($thread_contents, $pos-1, 0, array(compact('node_id', 'node_title', 'node_number', 'node_ignored', 'node_url', 'pos')));
			}
			else {
				$pos=1;
				$thread_contents=array($pos => compact('node_id', 'node_title', 'node_number', 'node_ignored', 'node_url', 'pos'));
			}

			break;

		case 'delete':
			if ($missing_old_node_number or $bad_old_node_number) {
				break;
			}

			if (!$confirmed) {
				$confirm_delete_node=true;
				break;
			}

			$node_id = $thread_contents[$old_node_number]['node_id'];

			$r = thread_delete_node($thread_id, $node_id);

			if (!$r) {
				break;
			}

			unset($thread_contents[$old_node_number]);
			$thread_contents = array_values($thread_contents);

			foreach ($thread_contents as &$c) {
				if ($c['node_number'] >= $old_node_number) {
					$c['node_number']--;
				}
				if ($c['pos'] >= $old_node_number) {
					$c['pos']--;
				}
			}

			$old_node_number = false;

			break;

		case 'hide':
			if ($missing_old_node_number or $bad_old_node_number) {
				break;
			}

			$node_id = $thread_contents[$old_node_number]['node_id'];

			$r = thread_set_node_ignored($thread_id, $node_id, true);

			if (!$r) {
				break;
			}

			$thread_contents[$old_node_number]['node_ignored'] = true;

			$old_node_number = false;

			break;

		case 'show':
			if ($missing_old_node_number or $bad_old_node_number) {
				break;
			}

			$node_id = $thread_contents[$old_node_number]['node_id'];

			$r = thread_set_node_ignored($thread_id, $node_id, false);

			if (!$r) {
				break;
			}

			$thread_contents[$old_node_number]['node_ignored'] = false;

			$old_node_number = false;

			break;

		case 'reorder':
			if (!$p) {
				break;
			}

			$neworder=range(1, count($p));
			array_multisort($p, SORT_NUMERIC, $neworder);

			$number=1;
			$nc=array();
			foreach ($neworder as $i) {
				$c = &$thread_contents[$i];
				if ($c['node_number'] != $number) {
					thread_set_node_number($thread_id, $c['node_id'], $number);
					$c['node_number'] = $number;
				}
				$c['pos']=$number;

				$nc[$number++] = $c;
			}
			$thread_contents = $nc;

			break;

		default:
			break;
	}

	head('title', $thread_title ? $thread_title : $thread_id);
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$headline_text=	translate('threadall:title', $lang);
	$headline_url=url('threadedit', $lang). '?' . 'clang=' . $clang;
	$headline = compact('headline_text', 'headline_url');
	$view=$thread_name ? url('thread', $clang) . '/'. $thread_id . '?' . 'slang=' . $lang : false;
	$validate=$thread_name ? url($thread_type, $clang) . '/'. $thread_id : false;

	$banner = build('banner', $lang, $with_toolbar ? compact('headline') : compact('headline', 'view', 'validate'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('view', 'validate')) : false;

	$title = view('headline', false, $headline);
	$sidebar = view('sidebar', false, compact('title'));

	$inlanguages=view('inlanguages', false, compact('clang'));

	$errors = compact('missing_thread_name', 'bad_thread_name', 'missing_thread_type', 'bad_thread_type', 'missing_new_node_title', 'bad_new_node_title', 'bad_new_node_number', 'missing_old_node_number', 'bad_old_node_number');

	$content = view('editing/threadeditsummary', $lang, compact('clang', 'inlanguages', 'supported_threads', 'thread_id', 'thread_type', 'thread_title', 'thread_name', 'thread_abstract', 'thread_cloud', 'thread_search', 'thread_tag', 'thread_comment', 'thread_morecomment', 'thread_contents', 'new_node_name', 'new_node_title', 'new_node_number', 'old_node_number', 'confirm_delete_node', 'errors'));

	$output = layout('editing', compact('toolbar', 'banner', 'content', 'sidebar'));

	return $output;
}

