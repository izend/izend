<?php

/**
 *
 * @copyright  2010-2017 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function threadedit($lang, $arglist=false) {
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

	$clang=isset($_POST['clang']) ? $_POST['clang'] : (isset($_GET['clang']) ? $_GET['clang'] : $lang);

	if (!in_array($clang, $supported_languages)) {
		return run('error/notfound', $lang);
	}

	if (!$thread) {
		require_once 'actions/threadeditall.php';

		return threadeditall($lang, $clang);
	}

	if (!$node) {
		require_once 'actions/threadeditsummary.php';

		return threadeditsummary($lang, $clang, $thread);
	}

	require_once 'actions/threadeditnode.php';

	return threadeditnode($lang, $clang, $thread, $node);
}

