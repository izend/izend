<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';
require_once 'filemimetype.php';

function download($lang, $arglist=false) {
	$node_id=$download_name=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$node_id=$arglist[0];
		}
		if (isset($arglist[1])) {
			$download_name=$arglist[1];
		}
	}

	if (!$node_id) {
		return run('error/badrequest', $lang);
	}

	if (!$download_name) {
		return run('error/badrequest', $lang);
	}

	$path = node_get_content_download_path($lang, $node_id, $download_name);

	if (!$path) {
		return run('error/notfound', $lang);
	}

	$filepath=ROOT_DIR . DIRECTORY_SEPARATOR . $path;
	if (!file_exists($filepath)) {
		return run('error/internalerror', $lang);
	}

	$filename=$download_name;
	$filesize=filesize($filepath);
	$filetype=file_mime_type($filepath);

	header('HTTP/1.1 200 OK');	// Make sure status code is OK in case URL pointed to a plausible file not found like an image
	header('Content-Description: File Transfer');
	header("Content-Type: $filetype");
	header("Content-Disposition: attachment; filename=$filename");
	header("Content-Length: $filesize");

	readfile($filepath);

	return false;
}

