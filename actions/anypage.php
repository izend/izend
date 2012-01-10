<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'socialize.php';

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

	$besocial=$sharebar=false;
	$ilike=true;
	$tweetit=true;
	$plusone=true;
	$linkedin=true;
	if ($tweetit) {
		$tweet_text=$sitename;
		$tweetit=$tweet_text ? compact('tweet_text') : true;
	}
	list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone', 'linkedin'));

	$content = view('anypage', false, compact('page_contents', 'besocial'));

	head('title', $sitename);

	$contact=false;
	$banner = build('banner', $lang, compact('contact'));

	$contact=false;
	$footer = build('footer', $lang, compact('contact'));

	$output = layout('standard', compact('sharebar', 'banner', 'footer', 'content'));

	return $output;
}

