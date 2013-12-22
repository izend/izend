<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    14
 * @link       http://www.izend.org
 */

require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'models/thread.inc';

function booksummary($lang, $book) {
	global $with_toolbar;

	$book_id = thread_id($book);
	if (!$book_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $book_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_name thread_title thread_abstract thread_cloud thread_image thread_nocloud thread_nosearch */

	if ($thread_type != 'book') {
		return run('error/notfound', $lang);
	}

	$book_name = $thread_name;
	$book_title = $thread_title;
	$book_abstract = $thread_abstract;
	$book_cloud = $thread_cloud;
	$book_modified= $thread_modified;
	$book_nocloud = $thread_nocloud;
	$book_nosearch = $thread_nosearch;
	$book_novote = $thread_novote;
	$book_nomorevote = $thread_nomorevote;

	if ($book_title) {
		head('title', $book_title);
	}
	if ($book_abstract) {
		head('description', $book_abstract);
	}
	if ($book_cloud) {
		head('keywords', $book_cloud);
	}
	head('date', $book_modified);

	$book_contents = array();
	$r = thread_get_contents($lang, $book_id);
	if ($r) {
		$book_url = url('book', $lang) . '/'. $book_name;
		foreach ($r as $c) {
			extract($c);	/* node_id node_name node_title node_number */
			$page_id = $node_id;
			$page_title = $node_title;
			$page_url = $book_url  . '/' . $node_name;
			$book_contents[] = compact('page_id', 'page_title', 'page_url');
		}
	}

	$vote=false;
	if (!$book_novote) {
		$nomore=(!$book_contents or $book_nomorevote) ? true : false;
		$vote=build('vote', $lang, $book_id, 'thread', $nomore);
	}

	$besocial=$sharebar=false;
	if ($book_contents) {
		$ilike=$thread_ilike;
		$tweetit=$thread_tweet;
		$plusone=$thread_plusone;
		$linkedin=$thread_linkedin;
		$pinit=$thread_pinit;
		if ($tweetit) {
			$tweet_text=$thread_abstract ? $thread_abstract : $thread_title;
			$tweetit=$tweet_text ? compact('tweet_text') : true;
		}
		if ($pinit) {
			$pinit_text=$thread_abstract ? $thread_abstract : $thread_title;
			$pinit_image=$thread_image;
			$pinit=$pinit_text && $pinit_image ? compact('pinit_text', 'pinit_image') : false;
		}
		list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone', 'linkedin', 'pinit'));
	}

	$content = view('booksummary', false, compact('book_id', 'book_title', 'book_abstract', 'book_contents', 'besocial', 'vote'));

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
		$cloud = build('cloud', $lang, $cloud_url, $book_id, false, 30, compact('byname', 'bycount', 'index'));
	}

	$headline_text=	translate('bookall:title', $lang);
	$headline_url=url('book', $lang);
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('search', 'cloud', 'title'));

	$search=!$book_nosearch ? compact('search_url', 'search_text', 'suggest_url') : false;
	$edit=user_has_role('writer') ? url('bookedit', $_SESSION['user']['locale']) . '/'. $book_id . '?' . 'clang=' . $lang : false;
	$validate=url('book', $lang) . '/'. $book_name;

	$banner = build('banner', $lang, $with_toolbar ? compact('headline', 'search') : compact('headline', 'edit', 'validate', 'search'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$output = layout('standard', compact('sharebar', 'toolbar', 'banner', 'sidebar', 'content'));

	return $output;
}

