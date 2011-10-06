<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function homepage($lang) {
	global $sitename;

	$page_contents = build('content', $lang, 'homepage');

	$besocial=false;
	if ($page_contents) {
		$ilike=$tweetit=$plusone=true;
		$besocial=build('besocial', $lang, compact('ilike', 'tweetit', 'plusone'));
	}

	$content = view('anypage', false, compact('page_contents', 'besocial'));

	head('title', $sitename);

	$languages='homepage';
	$contact=true;
	$banner = build('banner', $lang, compact('languages', 'contact'));

	$contact_page=url('contact', $lang);
	$footer = build('content', $lang, 'footer', compact('contact_page'));

	$output = layout('standard', compact('banner', 'content', 'footer'));

	return $output;
}

