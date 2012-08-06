<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'socialize.php';
require_once 'userhasrole.php';
require_once 'models/thread.inc';

function newslettersummary($lang, $newsletter) {
	global $with_toolbar;

	$newsletter_id = thread_id($newsletter);
	if (!$newsletter_id) {
		return run('error/notfound', $lang);
	}

	$r = thread_get($lang, $newsletter_id);
	if (!$r) {
		return run('error/notfound', $lang);
	}
	extract($r); /* thread_name thread_title thread_abstract thread_cloud thread_nocloud thread_nosearch */

	$newsletter_name = $thread_name;
	$newsletter_title = $thread_title;
	$newsletter_abstract = $thread_abstract;
	$newsletter_cloud = $thread_cloud;
	$newsletter_modified= $thread_modified;
	$newsletter_nocloud = $thread_nocloud;
	$newsletter_nosearch = $thread_nosearch;

	if ($newsletter_title) {
		head('title', $newsletter_title);
	}
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$newsletter_contents = array();
	$r = thread_get_contents($lang, $newsletter_id);
	if ($r) {
		$newsletter_url = url('newsletter', $lang);
		foreach ($r as $c) {
			extract($c);	/* node_id node_name node_title node_number */
			$page_id = $node_id;
			$page_title = $node_title;
			$page_url = $newsletter_url  . '/' . $node_name;
			$newsletter_contents[] = compact('page_id', 'page_title', 'page_url');
		}
	}

	$content = view('newslettersummary', false, compact('newsletter_id', 'newsletter_title', 'newsletter_abstract', 'newsletter_contents'));

	$search=false;
	if (!$newsletter_nosearch) {
		$search_text='';
		$search_url= url('search', $lang, $newsletter_name);
		$suggest_url= url('suggest', $lang, $newsletter_name);
		$search=view('searchinput', $lang, compact('search_url', 'search_text', 'suggest_url'));
	}

	$cloud=false;
	if (!$newsletter_nocloud) {
		$cloud_url= url('search', $lang, $newsletter_name);
		$byname=$bycount=$index=true;
		$cloud = build('cloud', $lang, $cloud_url, $newsletter_id, false, 30, compact('byname', 'bycount', 'index'));
	}

	$headline_text=	translate('newsletterall:title', $lang);
	$headline_url=url('newsletter', $lang);
	$headline = compact('headline_text', 'headline_url');
	$title = view('headline', false, $headline);

	$sidebar = view('sidebar', false, compact('search', 'cloud', 'title'));

	$search=!$newsletter_nosearch ? compact('search_url', 'search_text', 'suggest_url') : false;
	$edit=user_has_role('writer') ? url('newsletteredit', $_SESSION['user']['locale']) . '/'. $newsletter_id : false;
	$validate=url('newsletter', $lang) . '/'. $newsletter_name;

	$banner = build('banner', $lang, $with_toolbar ? compact('headline', 'search') : compact('headline', 'edit', 'validate', 'search'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$output = layout('standard', compact('sharebar', 'toolbar', 'banner', 'sidebar', 'content'));

	return $output;
}

