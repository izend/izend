<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'filemimetype.php';
require_once 'models/node.inc';

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

	$sqllang=db_sql_arg($lang, false);
	$sqlname=db_sql_arg($download_name, true);

	$tabnodecontent=db_prefix_table('node_content');
	$tabcontentdownload=db_prefix_table('content_download');

	$sql="SELECT cd.path FROM $tabnodecontent nc JOIN $tabcontentdownload cd ON nc.content_type='download' AND cd.content_id=nc.content_id AND cd.locale=$sqllang WHERE nc.node_id=$node_id AND cd.name=$sqlname LIMIT 1";

	$r = db_query($sql);

	if (!$r) {
		return run('error/notfound', $lang);
	}

	$path = $r[0]['path'];

	$filepath=ROOT_DIR . DIRECTORY_SEPARATOR . $path;
	if (!file_exists($filepath)) {
		return run('error/internalerror', $lang);
	}

	$filename=$download_name;
	$filesize=filesize($filepath);
	$filetype=file_mime_type($filepath);
	if (!$filetype) {
		$filetype = 'application/octet-stream';
	}

	header('HTTP/1.1 200 OK');	// Make sure status code is OK in case URL pointed to a plausible file not found like an image
	header('Content-Description: File Transfer');
	header("Content-Type: $filetype");
	header("Content-Disposition: attachment; filename=$filename");
	header("Content-Length: $filesize");

	readfile($filepath);

	return false;
}

