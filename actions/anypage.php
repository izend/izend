<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function anypage($lang, $arglist=false) {
	global $sitename;

	$page=false;

	if (is_array($arglist)) {
		$page=implode('/', $arglist);
	}

	if (!$page) {
		return run('error/notfound', $lang);
	}

	$page_contents = build('content', $lang, $page);
	if ($page_contents === false) {
		return run('error/notfound', $lang);
	}

	$besocial=false;
	if ($page_contents) {
		$ilike=$tweetit=$plusone=true;
		$besocial=build('besocial', $lang, compact('ilike', 'tweetit', 'plusone'));
	}

	$content = view('anypage', false, compact('page_contents', 'besocial'));

	head('title', $sitename);

	$contact=false;
	$banner = build('banner', $lang, compact('contact'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

