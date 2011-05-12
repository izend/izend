<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function editpage($lang, $arglist=false) {
	global $supported_languages;

	if (!user_has_role('writer')) {
		return run('error/unauthorized', $lang);
	}

	$page=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$page=$arglist[0];
		}
	}

	$clang=false;
	foreach ($supported_languages as $slang) {
		if (isset($_POST[$slang . '_x'])) {
			$clang=$slang;
			break;
		}
	}
	if (!$clang) {
		if (isset($_POST['clang'])) {
			$clang = $_POST['clang'];
		}
		else if (isset($_GET['clang'])) {
			$clang = $_GET['clang'];
		}
		else {
			$clang=$lang;
		}
	}

	$thread_id = 1;

	if (!$page) {
		require_once 'actions/threadeditsummary.php';

		return threadeditsummary($lang, $clang, $thread_id);
	}

	require_once 'actions/threadeditnode.php';

	return threadeditnode($lang, $clang, $thread_id, $page);
}

