<?php

/**
 *
 * @copyright  2013 izend.org
 * @version    1
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
		if (is_dir($filepath)) {
			dirclearaux($filepath, $files);
		}
		else {
			unlink($filepath);
		}
	}
	closedir($handle);
}
