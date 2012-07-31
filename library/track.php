<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'clientipaddress.php';
require_once 'validateipaddress.php';
require_once 'requesturi.php';
require_once 'useragent.php';
require_once 'validateuseragent.php';

function track($request_uri=false, $track_agent=false) {
	global $track_log, $track_db;
	global $track_agent_blacklist;

	if (! ($track_log or $track_db) ) {
		return true;
	}

	if (!$request_uri) {
		$request_uri=request_uri();
	}

	if (!$request_uri) {
		return false;
	}

	$user_agent=false;

	if ($track_agent or $track_agent_blacklist) {
		$user_agent=user_agent();
		if (!validate_user_agent($user_agent)) {
			$user_agent=false;
		}

		if ($user_agent and $track_agent_blacklist) {
			$reg = '/' . implode('|', $track_agent_blacklist) . '/i';

			if (preg_match($reg, $user_agent)) {
				return true;
			}
		}
	}

	$r = true;

	if ($track_log) {
		require_once 'log.php';

		$logmsg = $request_uri;
		if ($user_agent) {
			$logmsg .= "\t" . $user_agent;
		}

		$r = write_log($track_log === true ? 'track.log' : $track_log, $logmsg);
	}

	if ($track_db) {
		$ip_address=client_ip_address();

		if (!validate_ip_address($ip_address)) {
			return false;
		}

		$sqlipaddress=db_sql_arg($ip_address, false);
		$sqlrequesturi=db_sql_arg($request_uri, true);
		$sqluseragent=db_sql_arg($user_agent, true, true);

		$tabtrack=db_prefix_table($track_db === true ? 'track' : $track_db);

		$sql="INSERT $tabtrack (ip_address, request_uri, user_agent) VALUES (INET_ATON($sqlipaddress), $sqlrequesturi, $sqluseragent)";

		$r = db_insert($sql);
	}

	return $r;
}

