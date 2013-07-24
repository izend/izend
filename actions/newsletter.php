<?php

/**
 *
 * @copyright  2012-2013 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function newsletter($lang, $arglist=false) {
	global $newsletter_thread;

	if (!$newsletter_thread) {
		return run('error/notfound', $lang);
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

