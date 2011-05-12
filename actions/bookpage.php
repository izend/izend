<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function bookpage($lang, $book, $page) {
	$book_id = thread_id($book);
	if (!$book_id) {
		return run('error/notfound', $lang);
	}

	$page_id = thread_node_id($book_id, $page);
	if (!$page_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $book_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud thread_nocloud thread_nosearch thread_nocomment thread_nomorecomment */

	if ($thread_type != 'book') {
		return run('error/notfound', $lang);
	}

	$book_name = $thread_name;
	$book_title = $thread_title;
	$book_abstract = $thread_abstract;
	$book_cloud = $thread_cloud;
	$book_nocloud = $thread_nocloud;
	$book_nosearch = $thread_nosearch;

	$r = thread_get_node($lang, $book_id, $page_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_name node_title node_abstract node_cloud node_nocomment node_nomorecomment */

	$page_name=$node_name;
	$page_title=$node_title;
	$page_number=$node_number;

	$page_contents = build('nodecontent', $lang, $page_id);

	$prev_page_label=$prev_page_url=false;
	$r=thread_node_prev($lang, $book_id, $page_id);
	if ($r) {
		extract($r);	/* prev_node_id prev_node_name prev_node_title prev_node_number */
		$prev_page_label = $prev_node_title ? $prev_node_title : $prev_node_number;
		$prev_page_url=url('book', $lang) . '/'. $book_name . '/'. ($prev_node_name ? $prev_node_name : $prev_node_id);
	}

	$next_page_label=$next_page_url=false;
	$r=thread_node_next($lang, $book_id, $page_id);
	if ($r) {
		extract($r);	/* next_node_id next_node_name next_node_title next_node_number */
		$next_page_label = $next_node_title ? $next_node_title : $next_node_number;
		$next_page_url=url('book', $lang) . '/'. $book_name . '/'. ($next_node_name ? $next_node_name : $next_node_id);
	}

	$searchbox=false;
	if (!($book_nosearch and $book_nocloud)) {
		$search_input=$search_cloud=false;
		if (!$book_nosearch) {
			$search_input = true;
			$search_text = '';
			$search_url = url('search', $lang) . '/'. $book_name;
		}
		if (!$book_nocloud) {
			$search_cloud = build('cloud', $lang, $book_id, 60, true, true);
		}
		$searchbox = view('searchbox', $lang, compact('search_input', 'search_text', 'search_url', 'search_cloud'));
	}

	$page_comment=false;
	if (!($thread_nocomment or $node_nocomment)) {
		$page_url = url('book', $lang) . '/'. $book_name . '/' . $page_name;
		$page_comment = build('nodecomment', $lang, $page_id, $page_url, ($thread_nomorecomment or $node_nomorecomment));
	}

	head('title', $book_title);
	head('description', empty($node_abstract) ? $book_abstract : $node_abstract);
	head('keywords', $node_cloud);

	$headline_text=$book_title;
	$headline_url=url('book', $lang) . '/'. $book_name;
	$headline = compact('headline_text', 'headline_url');
	$edit=user_has_role('writer') ? url('bookedit', $_SESSION['user']['locale']) . '/'. $book_id . '/' . $page_id . '?' . 'clang=' . $lang : false;
	$validate=url('book', $lang) . '/'. $book_name . '/' . $page_name;
	$banner = build('banner', $lang, compact('headline', 'edit', 'validate'));

	$sidebar = $searchbox;

	$content = view('bookpage', $lang, compact('page_title', 'page_contents', 'page_comment', 'page_number', 'prev_page_url', 'prev_page_label',  'next_page_url', 'next_page_label'));

	$output = layout('standard', compact('banner', 'sidebar', 'content'));

	return $output;
}

