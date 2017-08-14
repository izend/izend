<?php

/**
 *
 * @copyright  2010-2017 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

require_once 'socialize.php';

function homepage($lang) {
	global $sitename, $siteshot;

	$page_contents = build('content', $lang, 'homepage');

	$besocial=$sharebar=false;
	$ilike=true;
	$tweetit=true;
	$plusone=true;
	$linkedin=true;
	$pinit=true;
	if ($tweetit or $pinit) {
		$description=translate('description', $lang);
		if ($tweetit) {
			$tweet_text=$description ? $description : $sitename;
			$tweetit=$tweet_text ? compact('tweet_text') : true;
		}
		if ($pinit) {
			$pinit_text=$description ? $description : $sitename;
			$pinit_image=$siteshot;
			$pinit=$pinit_text && $pinit_image ? compact('pinit_text', 'pinit_image') : true;
		}
	}
	list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'plusone', 'linkedin', 'pinit'));

	$content = view('anypage', false, compact('page_contents', 'besocial'));

	head('title', $sitename);

	$languages='homepage';
	$contact=true;
	$banner = build('banner', $lang, compact('languages', 'contact'));

	$languages=false;
	$contact=false;
	$footer = build('footer', $lang, compact('languages', 'contact'));

	$output = layout('standard', compact('sharebar', 'banner', 'footer', 'content'));

	return $output;
}

