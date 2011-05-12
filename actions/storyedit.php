<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'userhasrole.php';

function storyedit($lang, $arglist=false) {
	global $supported_languages;

	if (!user_has_role('writer')) {
		return run('error/unauthorized', $lang);
	}

	$story=$page=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$story=$arglist[0];
		}
		if (isset($arglist[1])) {
			$page=$arglist[1];
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

	if (!$story) {
		require_once 'actions/threadeditall.php';

		return threadeditall($lang, $clang);
	}

	if ($page) {
		require_once 'actions/threadeditnode.php';

		return threadeditnode($lang, $clang, $story, $page);

	}

	require_once 'actions/threadeditsummary.php';

	return threadeditsummary($lang, $clang, $story);
}

