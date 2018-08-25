<?php

/**
 *
 * @copyright  2018 izend.org
 * @version	   2
 * @link	   http://www.izend.org
 */

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
		return false;
	}

	if (!is_numeric($offset) or $offset < 0) {
		return false;
	}

	if (!is_numeric($size) or $size < 0) {
		return false;
	}

	if (!validate_filename($name) or !is_filename_allowed($name)) {
		return false;
	}

	if ($filetypes and (!$type or !in_array($type, $filetypes))) {
		return false;
	}

	$fname = FILES_DIR . DIRECTORY_SEPARATOR . $name;

	if ($offset == 0) {
		@unlink($fname);
	}

	$r = @file_put_contents($fname, $data, FILE_APPEND);

	if (!$r) {
		@unlink($fname);

		return false;
	}

	return true;
}
