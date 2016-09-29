<?php

/**
 *
 * @copyright  2016 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function traffic($lang) {
	if (!user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	head('title', translate('traffic:title', $lang));

	$banner = build('banner', $lang);

	$analytics = build('analytics', $lang);

	$content=view('traffic', $lang, compact('analytics'));

	$output = layout('standard', compact('banner', 'content'));

	return $output;
}

