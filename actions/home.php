<?php

/**
 *
 * @copyright  2010-2019 izend.org
 * @version    19
 * @link       http://www.izend.org
 */

require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'models/node.inc';

function home($lang) {
	global $root_node, $request_path, $with_toolbar, $sitename, $siteshot;

	if (!$root_node) {
		return run('error/internalerror', $lang);
	}

	$r = node_get($lang, $root_node);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* node_name node_title node_abstract node_cloud node_image node_created node_modified node_nocomment node_nomorecomment node_ilike node_tweet node_linkedin node_pinit node_whatsapp */

	head('title', translate('home:title', $lang));
	if ($node_abstract) {
		head('description', $node_abstract);
	}
	else {
		head('description', translate('description', $lang));
	}
	if ($node_cloud) {
		head('keywords', $node_cloud);
	}
	else {
		head('keywords', translate('keywords', $lang));
	}
	head('date', $node_modified);

	$request_path=$lang;

	$page_contents = build('nodecontent', $lang, $root_node);

	$besocial=$sharebar=false;
	if ($page_contents) {
		$ilike=$node_ilike;
		$tweetit=$node_tweet;
		$linkedin=$node_linkedin;
		$pinit=$node_pinit;
		$whatsapp=$node_whatsapp;
		if ($tweetit or $pinit) {
			$description=$node_abstract ? $node_abstract : translate('description', $lang);
			if ($tweetit) {
				$tweet_text=$description ? $description : $sitename;
				$tweetit=$tweet_text ? compact('tweet_text') : true;
			}
			if ($pinit) {
				$pinit_text=$description ? $description : $sitename;
				$pinit_image=$node_image ? $node_image : $siteshot;
				$pinit=$pinit_text && $pinit_image ? compact('pinit_text', 'pinit_image') : true;
			}
		}
		list($besocial, $sharebar) = socialize($lang, compact('ilike', 'tweetit', 'linkedin', 'pinit', 'whatsapp'));
	}

	$content = view('home', false, compact('page_contents', 'besocial'));

	$languages='home';
	$contact=$account=$admin=$donate=true;
	$edit=user_has_role('writer') ? url('editpage', $_SESSION['user']['locale']) . '/'. $root_node . '?' . 'clang=' . $lang : false;
	$validate=url('home', $lang);

	$banner = build('banner', $lang, $with_toolbar ? compact('languages', 'contact', 'account', 'admin', 'donate') : compact('languages', 'contact', 'account', 'admin', 'donate', 'edit', 'validate'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$search_text='';
	$search_url=url('search', $lang);
	$suggest_url=url('suggest', $lang);
	$search=view('searchinput', $lang, compact('search_url', 'search_text', 'suggest_url'));
	$sidebar = view('sidebar', false, compact('search'));

	$contact_page=url('contact', $lang);
	$newsletter_page=false;
	$footer = view('footer', $lang, compact('contact_page', 'newsletter_page'));

	$output = layout('standard', compact('lang', 'footer', 'banner', 'content', 'sidebar', 'sharebar', 'toolbar'));

	return $output;
}

