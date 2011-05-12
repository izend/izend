<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function file_mime_type($file) {
	$file = escapeshellarg($file);
	$cmd = "file -iL $file";

	exec($cmd, $output, $r);

	if ($r == 0) {
		$s = $output[0];
		$pos = strpos($s, ': ');
		if ($pos) {
			return substr($s, $pos+2);	/* keep charset? */
		}
	}

	return 'application/octet-stream';
}

