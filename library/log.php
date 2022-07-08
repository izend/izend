<?php

/**
 *
 * @copyright  2010-2022 izend.org
 * @version    3
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

	$timestamp=date('Y-m-d H:i:s');

	$logmsg="$timestamp $ipaddress";
	if ($textline) {
		$logmsg .= "\t$textline";
	}
	$logmsg.="\n";

	$file = isset($log_dir) ? ($log_dir . DIRECTORY_SEPARATOR . $logfile) : $logfile;

	$r = @file_put_contents($file, $logmsg, FILE_APPEND);

	return $r;
}

