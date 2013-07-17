<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function dirlist($dir='.') {
	if (!is_dir($dir)) {
		return false;
	}

	$files = array();
	dirlistaux($dir, $files);

	return $files;
}

function dirlistaux($dir, &$files) {
	$handle = opendir($dir);
	while (($file = readdir($handle)) !== false) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		$filepath = $dir == '.' ? $file : $dir . DIRECTORY_SEPARATOR . $file;
		if (is_link($filepath))
			continue;
		if (is_file($filepath))
			$files[] = $filepath;
		else if (is_dir($filepath))
			dirlistaux($filepath, $files);
	}
	closedir($handle);
}

