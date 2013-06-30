<?php

/**
 *
 * @copyright  2012-2013 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'isfilenameallowed.php';
require_once 'readarg.php';
require_once 'tokenid.php';
require_once 'validatefilename.php';

define('FILES_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'files');

function upload($lang) {
	$maxfilesize=1000000;

	$action='init';
	if (isset($_POST['upload_put'])) {
		$action='upload';
	}

	$file=$name=$type=$error=false;
	$size=0;
	$token=false;

	$bad_copy=false;

	switch($action) {
		case 'upload':
			if (isset($_POST['upload_token'])) {
				$token=readarg($_POST['upload_token']);
			}

			if (isset($_FILES['upload_file'])) {
				$error=$_FILES['upload_file']['error'];

				if ($error != UPLOAD_ERR_OK) {
					$bad_copy=true;
					break;
				}

				$file=$_FILES['upload_file']['tmp_name'];
				$name=$_FILES['upload_file']['name'];
				$type=$_FILES['upload_file']['type'];
				$size=$_FILES['upload_file']['size'];
			}
			break;
		default:
			break;
	}

	$bad_token=false;

	$missing_file=false;
	$bad_file=false;
	$bad_name=false;
	$bad_type=false;
	$bad_size=false;

	$file_copied=false;

	$copy_error=false;

	switch($action) {
		case 'upload':
			if (!isset($_SESSION['upload_token']) or $token != $_SESSION['upload_token']) {
				$bad_token=true;
			}

			if ($bad_copy) {
				break;
			}

			if (!$file) {
				$missing_file=true;
			}
			else if (!is_uploaded_file($file)) {
				$bad_file=true;
			}
			else if ($size > $maxfilesize) {
				$bad_size=true;
			}
			else if (!validate_filename($name) or !is_filename_allowed($name)) {
				$bad_name=true;
			}

			break;
		default:
			break;
	}

	switch($action) {
		case 'upload':
			if ($bad_copy or $bad_token or $missing_file or $bad_file or $bad_size or $bad_name or $bad_type) {
				break;
			}

			$filecopy = FILES_DIR . DIRECTORY_SEPARATOR . $name;

			if (!@move_uploaded_file($file, $filecopy))  {
				$copy_error=true;
				break;
			}

			$file_copied=true;
			break;
		default:
			break;
	}

	$_SESSION['upload_token'] = $token = token_id();

	$errors = compact('missing_file', 'bad_file', 'bad_size', 'bad_name', 'bad_type', 'bad_copy', 'copy_error');
	$infos = compact('file_copied');

	$output = view('upload', $lang, compact('token', 'maxfilesize', 'name', 'errors', 'infos'));

	return $output;
}

