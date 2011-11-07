<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    9
 * @link       http://www.izend.org
 */

require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'models/thread.inc';

function bookpage($lang, $book, $page) {
	global $with_toolbar;

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
	extract($r); /* node_number node_ignored node_name node_title node_abstract node_cloud node_nocomment node_nomorecomment node_ilike node_tweet node_plusone */

	if ($node_ignored) {
		return run('error/notfound', $lang);
	}

	$page_name=$node_name;
	$page_title=$node_title;
	$page_abstract=$node_abstract;
	$page_cloud=$node_cloud;
	$page_number=$node_number;

	if ($book_title and $page_title) {
		head('title', $book_title . ' - ' . $page_title );
	}
	else if ($page_title) {
		head('title', $page_title );
	}
	else if ($book_title) {
		head('title', $book_title );
	}
	if ($page_abstract) {
		head('description', $page_abstract);
	}
	else if ($book_abstract) {
		head('description', $book_abstract);
	}
	if ($page_cloud) {
		head('keywords', $page_cloud);
	}
	else if ($book_cloud) {
		head('keywords', $book_cloud);
	}

	$page_contents = build('nodecontent', $lang, $page_id);

	$page_comment=false;
	if (!($thread_nocomment or $node_nocomment)) {
		$moderate=user_has_role('moderator');
		$nomore=(!$page_contents or $thread_nomorecomment or $node_nomorecomment) ? true : false;
		$page_url = url('book', $lang) . '/'. $book_name . '/' . $page_name;
		$page_comment = build('nodecomment', $lang, $page_id, $page_url, $nomore, $moderate);
	}

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

	$besocial=$sharebar=false;
	if ($page_contents or $page_comment) {
		$ilike=$node_ilike;
		$tweetit=$node_tweet;
		$plusone=$node_plusone;
		if ($tweetit) {
			$tweet_text=($book_title && $page_title) ? "$book_title - $page_title" : ($page_title ? $page_title : $book_title);
			$tweetit=$tweet_text ? compact('tweet_text') : true;
		}
		list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone'));
	}

	$content = view('bookpage', false, compact('page_id', 'page_title', 'page_contents', 'page_comment', 'page_number', 'prev_page_url', 'prev_page_label',  'next_page_url', 'next_page_label', 'besocial'));

	$search=false;
	if (!$book_nosearch) {
		$search_text='';
		$search_url= url('search', $lang, $book_name);
		$suggest_url= url('suggest', $lang, $book_name);
		$search=view('searchinput', $lang, compact('search_url', 'search_text', 'suggest_url'));
	}

	$cloud=false;
	if (!$book_nocloud) {
		$cloud = build('cloud', $lang, $book_id, false, 50, true, true);
	}

	$headline_text=$book_title ? $book_title : $book_id;
	$headline_url=url('book', $lang) . '/'. $book_name;
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('search', 'cloud', 'title'));

	$search=!$book_nosearch ? compact('search_url', 'search_text', 'suggest_url') : false;
	$edit=user_has_role('writer') ? url('bookedit', $_SESSION['user']['locale']) . '/'. $book_id . '/' . $page_id . '?' . 'clang=' . $lang : false;
	$validate=url('book', $lang) . '/'. $book_name . '/' . $page_name;

	$banner = build('banner', $lang, $with_toolbar ? compact('headline', 'search') : compact('headline', 'edit', 'validate', 'search'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$output = layout('standard', compact('sharebar', 'toolbar', 'banner', 'content', 'sidebar'));

	return $output;
}

