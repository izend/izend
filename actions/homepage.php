<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'socialize.php';

function homepage($lang) {
	global $sitename;

	$page_contents = build('content', $lang, 'homepage');

	$besocial=$sharebar=false;
	$ilike=true;
	$tweetit=true;
	$plusone=true;
	if ($tweetit) {
		$tweet_text=$sitename;
		$tweetit=$tweet_text ? compact('tweet_text') : true;
	}
	list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone'));

	$content = view('anypage', false, compact('page_contents', 'besocial'));

	head('title', $sitename);

	$languages='homepage';
	$contact=true;
	$banner = build('banner', $lang, compact('languages', 'contact'));

	$contact_page=url('contact', $lang);
	$footer = build('content', $lang, 'footer', compact('contact_page'));

	$output = layout('standard', compact('footer', 'banner', 'content', 'sharebar'));

	return $output;
}

