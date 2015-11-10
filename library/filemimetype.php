<?php

/**
 *
 * @copyright  2010-2015 izend.org
 * @version    7
 * @link       http://www.izend.org
 */

function file_mime_type($file, $encoding=true) {
	$mime=false;

	if (function_exists('finfo_file')) {
		$finfo = finfo_open(FILEINFO_MIME);
		$mime = @finfo_file($finfo, $file);
		finfo_close($finfo);
	}
	else if (substr(PHP_OS, 0, 3) == 'WIN') {
		$mime = mime_content_type($file);
	}
	else {
		$file = escapeshellarg($file);
		$cmd = "file -iL $file";

		exec($cmd, $output, $r);

		if ($r == 0) {
			$mime = substr($output[0], strpos($output[0], ': ')+2);
		}
	}

	if (!$mime) {
		return false;
	}

	if ($encoding) {
		return $mime;
	}

	return substr($mime, 0, strpos($mime, '; '));
}
