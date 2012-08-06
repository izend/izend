<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function newsletter($lang, $arglist=false) {
	global $supported_languages, $newsletter_thread;

	if (!user_has_role('administrator')) {
		return run('error/unauthorized', $lang);
	}

	if (!$newsletter_thread) {
		return run('error/internalerror', $lang);
	}

	$page=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$page=$arglist[0];
		}
	}

	if (!$page) {
		require_once 'actions/newslettersummary.php';

		return newslettersummary($lang, $newsletter_thread);

	}

	require_once 'actions/newsletterpage.php';

	return newsletterpage($lang, $newsletter_thread, $page);
}

