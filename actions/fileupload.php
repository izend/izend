<?php

/**
 *
 * @copyright  2018-2020 izend.org
 * @version	   4
 * @link	   http://www.izend.org
 */

require_once 'userhasrole.php';
require_once 'isfilenameallowed.php';
require_once 'validatefilename.php';

define('FILES_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'files');

function fileupload($lang) {
	if (!user_has_role('administrator')) {
		return false;
	}

	$name=$type=$data=$token=false;
	$size=$offset=0;

	$filetypes=false;

	if (isset($_POST['file_token'])) {
		$token=$_POST['file_token'];
	}
	if (isset($_POST['file_name'])) {
		$name=$_POST['file_name'];
	}
	if (isset($_POST['file_size'])) {
		$size=$_POST['file_size'];
	}
	if (isset($_POST['file_type'])) {
		$type=$_POST['file_type'];
	}
	if (isset($_POST['file_offset'])) {
		$offset=$_POST['file_offset'];
	}
	if (isset($_POST['file_data'])) {
		$data=explode(';base64,', $_POST['file_data']);
		$data=is_array($data) && isset($data[1]) ? base64_decode($data[1]) : false;
	}

	if (!isset($_SESSION['upload_token']) or $token != $_SESSION['upload_token']) {
		goto badrequest;
	}

	if (!is_numeric($offset) or $offset < 0) {
		goto badrequest;
	}

	if (!is_numeric($size) or $size < 0) {
		goto badrequest;
	}

	if (!validate_filename($name) or !is_filename_allowed($name)) {
		goto badrequest;
	}

	if ($filetypes and (!$type or !in_array($type, $filetypes))) {
		goto badrequest;
	}

	if (!$data) {
		goto badrequest;
	}

	$datasize=strlen($data);

	if ($offset + $datasize > $size) {
		goto badrequest;
	}

	$file = FILES_DIR . DIRECTORY_SEPARATOR . $name;

	$fout = @fopen($file, $offset == 0 ? 'wb' : 'cb');

	if ($fout === false) {
		goto internalerror;
	}

	$r = fseek($fout, $offset);

	if ($r == -1) {
		goto internalerror;
	}

	$r = fwrite($fout, $data);

	if ($r === false) {
		goto internalerror;
	}

	if ($offset + $datasize < $size) {
		return false;
	}

	return false;

badrequest:
	header('HTTP/1.1 400 Bad Request');
	return false;

internalerror:
	header('HTTP/1.1 500 Internal Error');
	return false;
}
