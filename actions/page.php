<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function page($lang, $arglist=false) {
	global $default_folder;

	if (!$default_folder) {
		return run('error/notfound', $lang);
	}

	$page=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$page=$arglist[0];
		}
	}

	if (!$page) {
		return run('error/notfound', $lang);
	}

	$folder_id=$page_id=false;

	foreach (is_array($default_folder) ? $default_folder : array($default_folder) as $folder) {
		$folder_id = thread_id($folder);
		if ($folder_id) {
			$page_id = thread_node_id($folder_id, $page, $lang);
			if ($page_id) {
				break;
			}
		}
	}

	if (!$folder_id or !$page_id) {
		return run('error/notfound', $lang);
	}

	require_once 'actions/folderpage.php';

	return folderpage($lang, $folder_id, $page_id);
}

