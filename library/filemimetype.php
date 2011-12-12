<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function file_mime_type($file, $encoding=true) {
	$file = escapeshellarg($file);
	$cmd = "file -iL $file";

	exec($cmd, $output, $r);

	if ($r == 0) {
		$s = $output[0];
		$beg = strpos($s, ': ');
		if ($beg) {
			$end = $encoding ? false : strpos($s, '; ');
			return $end ? substr($s, $beg+2, $end - ($beg + 2)) : substr($s, $beg+2);
		}
	}

	return 'application/octet-stream';
}
