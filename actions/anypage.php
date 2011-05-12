<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
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

	$content = build('content', $lang, $page);
	if ($content === false) {
		return run('error/notfound', $lang);
	}

	$contact=false;
	$banner = build('banner', $lang, compact('contact'));

	head('title', $sitename);

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

