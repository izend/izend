<?php

/**
 *
 * @copyright  2011 frasq.org
 * @version    2
 * @link       http://www.frasq.org
 */

function home($lang) {
	head('title', translate('home:title', $lang));

	$search_text='';
	$search_url=url('search', $lang);
	$suggest_url=url('suggest', $lang);
	$search=view('searchinput', $lang, compact('search_url', 'search_text', 'suggest_url'));
	$sidebar = view('sidebar', false, compact('search'));

	$languages='home';
	$contact=$account=true;

	$search=compact('search_url', 'search_text', 'suggest_url');
	$banner = build('banner', $lang, compact('languages', 'contact', 'account', 'search'));

	$contact_page=url('contact', $lang);
	$footer = view('footer', $lang, compact('contact_page'));

	$homeblog = build('homeblog', $lang);
	$social = view('social', $lang);

	$content = view('home', $lang, compact('homeblog', 'social'));

	$output = layout('standard', compact('footer', 'banner', 'content', 'sidebar'));

	return $output;
}

