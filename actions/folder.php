<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function folder($lang, $arglist=false) {
	$folder=$page=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$folder=$arglist[0];
		}
		if (isset($arglist[1])) {
			$page=$arglist[1];
		}
	}

	if (!$folder) {
		return run('error/notfound', $lang);
	}

	if (!$page) {
		require_once 'actions/foldersummary.php';

		return foldersummary($lang, $folder);
	}

	require_once 'actions/folderpage.php';

	return folderpage($lang, $folder, $page);
}

