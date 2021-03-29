<?php

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    32
 * @link       http://www.izend.org
 */

require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'userhasaccess.php';
require_once 'models/thread.inc';

function bookpage($lang, $book, $page) {
	global $with_toolbar, $siteshot, $search_cloud;

	$book_id = thread_id($book);
	if (!$book_id) {
		return run('error/notfound', $lang);
	}

	if (!user_can_read($book_id)) {
		return run('error/unauthorized', $lang);
	}

	$page_id = thread_node_id($book_id, $page, $lang);
	if (!$page_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $book_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_type thread_name thread_title thread_abstract thread_cloud thread_image thread_nocloud thread_nosearch thread_nocomment thread_nomorecomment */

	if ($thread_type != 'book') {
		return run('error/notfound', $lang);
	}

	$book_name = $thread_name;
	$book_title = $thread_title;
	$book_abstract = $thread_abstract;
	$book_cloud = $thread_cloud;
	$book_image = $thread_image;
	$book_nocloud = $thread_nocloud;
	$book_nosearch = $thread_nosearch;

	$r = thread_get_node($lang, $book_id, $page_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_number node_ignored node_name node_title node_abstract node_cloud node_image node_user_id node_visits node_nocomment node_nomorecomment node_novote node_nomorevote node_ilike node_tweet node_linkedin node_pinit node _whatsapp */

	if ($node_ignored) {
		return run('error/notfound', $lang);
	}

	$page_user_id=$node_user_id;
	$page_name=$node_name;
	$page_title=$node_title;
	$page_abstract=$node_abstract;
	$page_cloud=$node_cloud;
	$page_image=$node_image;
	$page_number=$node_number;
	$page_modified=$node_modified;

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
	if ($page_image) {
		head('image', $page_image);
	}
	else if ($book_image) {
		head('image', $book_image);
	}
	head('date', $page_modified);

	$page_contents = build('nodecontent', $lang, $page_id);

	$page_comment=false;
	if (!($thread_nocomment or $node_nocomment)) {
		$nomore=(!$page_contents or $thread_nomorecomment or $node_nomorecomment) ? true : false;
		$page_url = url('book', $lang) . '/'. $book_name . '/' . $page_name;
		$page_comment = build('nodecomment', $lang, $page_id, $page_user_id, $page_url, $nomore);
	}

	$vote=false;
	if (!($thread_novote or $node_novote)) {
		$nomore=(!$page_contents or $thread_nomorevote or $node_nomorevote) ? true : false;
		$vote=build('vote', $lang, $page_id, 'node', $nomore);
	}

	$visits=false;
	if ($thread_visits and $node_visits) {
		$nomore=user_has_role('writer');
		$visits=build('visits', $lang, $page_id, $nomore);
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
		$ilike=$thread_ilike && $node_ilike;
		$tweetit=$thread_tweet && $node_tweet;
		$linkedin=$thread_linkedin && $node_linkedin;
		$pinit=$thread_pinit && $node_pinit;
		$whatsapp=$thread_whatsapp && $node_whatsapp;
		if ($tweetit) {
			$tweet_text=$node_abstract ? $node_abstract : ($node_title ? $node_title : $thread_title);
			$tweetit=$tweet_text ? compact('tweet_text') : true;
		}
		if ($pinit) {
			$pinit_text=$node_abstract ? $node_abstract : ($node_title ? $node_title : $thread_title);
			$pinit_image=$node_image ? $node_image : $siteshot;
			$pinit=$pinit_text && $pinit_image ? compact('pinit_text', 'pinit_image') : true;
		}
		list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'linkedin', 'pinit', 'whatsapp'));
	}

	$content = view('bookpage', false, compact('page_id', 'page_title', 'page_contents', 'page_comment', 'page_number', 'prev_page_url', 'prev_page_label', 'next_page_url', 'next_page_label', 'besocial', 'vote', 'visits'));

	$search=false;
	if (!$book_nosearch) {
		$search_text='';
		$search_url= url('search', $lang, $book_name);
		$suggest_url= url('suggest', $lang, $book_name);
		$search=view('searchinput', $lang, compact('search_url', 'search_text', 'suggest_url'));
	}

	$cloud=false;
	if (!$book_nocloud) {
		$cloud_url= url('search', $lang, $book_name);
		$byname=$bycount=$index=true;
		$cloud = build('cloud', $lang, $cloud_url, $book_id, false, false, $search_cloud, compact('byname', 'bycount', 'index'));
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

	$output = layout('standard', compact('lang', 'sharebar', 'toolbar', 'banner', 'content', 'sidebar'));

	return $output;
}

