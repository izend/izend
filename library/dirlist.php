<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
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
		$filepath = $dir == '.' ? $file : $dir . '/' . $file;
		if (is_link($filepath))
			continue;
		if (is_file($filepath))
			$files[] = $filepath;
		else if (is_dir($filepath))
			dirlistaux($filepath, $files);
	}
	closedir($handle);
}

