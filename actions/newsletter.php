<?php

/**
 *
 * @copyright  2012-2026 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function newsletter($lang, $arglist=false) {
	global $supported_languages;
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

	$clang=isset($_GET['clang']) ? $_GET['clang'] : $lang;

	if (!in_array($clang, $supported_languages)) {
		return run('error/notfound', $lang);
	}

	if (!$page) {
		require_once 'actions/newslettersummary.php';

		return newslettersummary($lang, $clang, $newsletter_thread);

	}

	require_once 'actions/newsletterpage.php';

	return newsletterpage($lang, $clang, $newsletter_thread, $page);
}

