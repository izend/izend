<?php

/**
 *
 * @copyright  2014-2018 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function dirclear($dir='.') {
	if (!is_dir($dir)) {
		return false;
	}

	dirclearaux($dir);

	return true;
}

function dirclearaux($dir) {
	$handle = opendir($dir);
	while (($file = readdir($handle)) !== false) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		$filepath = $dir . DIRECTORY_SEPARATOR . $file;
		if (is_link($filepath)) {
			unlink($filepath);
		}
		else if (is_dir($filepath)) {
			dirclearaux($filepath);
			rmdir($filepath);
		}
		else {
			unlink($filepath);
		}
	}
	closedir($handle);
}
