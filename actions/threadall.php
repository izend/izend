<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function threadall($lang) {
	global $system_languages, $with_toolbar;

	if (!user_has_role('writer')) {
		return run('error/unauthorized', $lang);
	}

	$slang=false;
	if (isset($_GET['slang'])) {
		$slang = $_GET['slang'];
	}
	else {
		$slang=$lang;
	}
	if (!in_array($slang, $system_languages)) {
		return run('error/notfound', $lang);
	}

	$site_title=translate('title', $lang);
	$site_abstract=translate('description', $lang);
	$site_cloud=translate('keywords', $lang);

	head('title', translate('threadall:title', $slang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex, nofollow');

	$edit=user_has_role('writer') ? url('threadedit', $_SESSION['user']['locale']) . '?' . 'clang=' . $lang : false;
	$validate=url('thread', $lang);

	$banner = build('banner', $lang, $with_toolbar ? false : compact('edit', 'validate'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('edit', 'validate')) : false;

	$threadlist = build('threadlist', $lang, false, $slang);

	$content = view('threadall', $slang, compact('site_title', 'site_abstract', 'site_cloud', 'threadlist'));

	$output = layout('viewing', compact('toolbar', 'banner', 'content'));

	return $output;
}

