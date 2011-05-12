<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function homepage($lang) {
	global $sitename;

	$languages='homepage';
	$contact=true;
	$banner = build('banner', $lang, compact('languages', 'contact'));

	$contact_page=url('contact', $lang);
	$footer = build('content', $lang, 'footer', compact('contact_page'));

	$content = build('content', $lang, 'homepage');

	head('title', $sitename);

	$output = layout('standard', compact('banner', 'content', 'footer'));

	return $output;
}

