<?php

/**
 *
 * @copyright  2013 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

define('FACEBOOK_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'facebook');

function facebook($fileUpload=false) {
	global $facebookid, $facebooksecret;

	require_once FACEBOOK_DIR . DIRECTORY_SEPARATOR . 'sdk' . DIRECTORY_SEPARATOR. 'src' . DIRECTORY_SEPARATOR . 'facebook.php';

	$facebook = new Facebook(array(
		'appId'			=> $facebookid,
		'secret'		=> $facebooksecret,
		'fileUpload'	=> $fileUpload
	));

	return $facebook;
}
