<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'clientipaddress.php';
require_once 'validateipaddress.php';

function write_log($logfile, $textline=false) {
	global $log_dir;

	$ipaddress = client_ip_address();

	if (!validate_ip_address($ipaddress)) {
		return false;
	}

	$timestamp=strftime('%Y-%m-%d %H:%M:%S', time());

	$logmsg="$timestamp $ipaddress";
	if ($textline) {
		$logmsg .= "\t$textline";
	}

	$file = isset($log_dir) ? ($log_dir . DIRECTORY_SEPARATOR . $logfile) : $logfile;

	$r = @file_put_contents($file, array($logmsg, "\n"), FILE_APPEND);

	return $r;
}

