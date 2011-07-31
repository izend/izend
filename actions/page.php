<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function page($lang, $arglist=false) {
	global $default_folder;

	$folder=$page=false;
	$folder_id=$page_id=false;

	if (is_array($arglist)) {
		if (isset($arglist[1])) {
			$folder=$arglist[0];
			$page=$arglist[1];
		}
		else if (isset($arglist[0])) {
			$folder=$default_folder;
			$page=$arglist[0];
		}
	}

	if (!$folder or !$page) {
		return run('error/notfound', $lang);
	}

	foreach (is_array($folder) ? $folder : array($folder) as $folder) {
		$folder_id = thread_id($folder);
		if ($folder_id) {
			$page_id = thread_node_id($folder_id, $page);
			if ($page_id) {
				break;
			}
		}
	}

	if (!$folder_id or !$page_id) {
		return run('error/notfound', $lang);
	}

	require_once 'actions/folderpage.php';

	return folderpage($lang, $folder, $page);
}

