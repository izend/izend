<?php

/**
 *
 * @copyright  2017-2018 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function dircopy($from, $to, $mode = 0777) {
	if (!is_dir($from)) {
		return false;
	}

	if (!is_dir($to)) {
		if (!@mkdir($to)) {
			return false;
		}
	}

	dircopyaux($from, $to, $mode);

	return true;
}

function dircopyaux($from, $to, $mode) {
	$handle = opendir($from);
	while (($file = readdir($handle)) !== false) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		$frompath = $from . DIRECTORY_SEPARATOR . $file;
		$topath = $to . DIRECTORY_SEPARATOR . $file;
		if (is_link($frompath)) {
			if (file_exists($topath)) {
				unlink($topath);
			}
			symlink(readlink($frompath), $topath);
		}
		else if (is_file($frompath)) {
			copy($frompath, $topath);
		}
		else if (is_dir($frompath)) {
			if (!is_dir($topath)) {
				mkdir($topath, $mode);
			}
			dircopyaux($frompath, $topath, $mode);
		}
	}
	closedir($handle);
}
