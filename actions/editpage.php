<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function editpage($lang, $arglist=false) {
	global $default_folder;

	$folder=$page=false;

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

	require_once 'actions/folderedit.php';

	return folderedit($lang, array($folder, $page));
}

