<?php

/**
 *
 * @copyright  2016-2018 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function traffic($lang) {
	global $googleanalyticsaccount, $googleanalyticskeyfile;
	global $with_toolbar;

	if (!user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	if (! ($googleanalyticsaccount and $googleanalyticskeyfile)) {
		return run('error/internalerror', $lang);
	}

	$analytics = build('analytics', $lang);

	$content=view('traffic', $lang, compact('analytics'));

	head('title', translate('traffic:title', $lang));
	head('description', false);
	head('keywords', false);
	head('robots', 'noindex');

	$admin=true;
	$banner = build('banner', $lang, $with_toolbar ? false : compact('admin'));
	$toolbar = $with_toolbar ? build('toolbar', $lang, compact('admin')) : false;

	$output = layout('standard', compact('toolbar', 'banner', 'content'));

	return $output;
}

