<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'models/thread.inc';

function booksummary($lang, $book) {
	$book_id = thread_id($book);
	if (!$book_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $book_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_name thread_title thread_abstract thread_cloud thread_nocloud thread_nosearch */

	if ($thread_type != 'book') {
		return run('error/notfound', $lang);
	}

	$book_name = $thread_name;
	$book_title = $thread_title;
	$book_abstract = $thread_abstract;
	$book_cloud = $thread_cloud;
	$book_nocloud = $thread_nocloud;
	$book_nosearch = $thread_nosearch;

	if ($book_title) {
		head('title', $book_title);
	}
	if ($book_abstract) {
		head('description', $book_abstract);
	}
	if ($book_cloud) {
		head('keywords', $book_cloud);
	}

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

	$besocial=$sharebar=false;
	if ($book_contents) {
		$ilike=false;
		$tweetit=false;
		$plusone=false;
		$besocial=build('besocial', $lang, compact('ilike', 'tweetit', 'plusone'));
		$ilike=true;
		$tweetit=true;
		$plusone=true;
		$sharebar=build('sharebar', $lang, compact('ilike', 'tweetit', 'plusone'));
	}

	$content = view('booksummary', false, compact('book_id', 'book_title', 'book_abstract', 'book_contents', 'besocial'));

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

	$headline_text=	translate('bookall:title', $lang);
	$headline_url=url('book', $lang);
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('search', 'cloud', 'title'));

	$search=!$book_nosearch ? compact('search_url', 'search_text', 'suggest_url') : false;
	$edit=user_has_role('writer') ? url('bookedit', $_SESSION['user']['locale']) . '/'. $book_id . '?' . 'clang=' . $lang : false;
	$validate=url('book', $lang) . '/'. $book_name;
	$banner = build('banner', $lang, compact('headline', 'edit', 'validate', 'search'));

	$output = layout('standard', compact('sharebar', 'banner', 'sidebar', 'content'));

	return $output;
}

