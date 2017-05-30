<?php

/**
 *
 * @copyright  2010-2017 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function thread($lang, $arglist=false) {
	global $supported_languages;

	if (!user_has_role('writer')) {
		return run('error/unauthorized', $lang);
	}

	$thread=$node=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$thread=$arglist[0];
		}
		if (isset($arglist[1])) {
			$node=$arglist[1];
		}
	}

	$clang=isset($_GET['clang']) ? $_GET['clang'] : $lang;

	if (!in_array($clang, $supported_languages)) {
		return run('error/notfound', $lang);
	}

	if (!$thread) {
		require_once 'actions/threadall.php';

		return threadall($lang, $clang);
	}

	if (!$node) {
		require_once 'actions/threadsummary.php';

		return threadsummary($lang, $clang, $thread);

	}

	require_once 'actions/threadnode.php';

	return threadnode($lang, $clang, $thread, $node);
}

