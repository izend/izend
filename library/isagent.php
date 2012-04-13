<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function is_agent($s, $agent=false) {
	if (!$agent) {
		require_once 'useragent.php';

		$agent=user_agent();
	}

	return $agent and preg_match('/'. $s . '/i', $agent);
}

function is_ipad($agent=false) {
	return is_agent('ipad', $agent);
}

function is_iphone($agent=false) {
	return is_agent('iphone', $agent);
}

function is_android($agent=false) {
	return is_agent('android', $agent);
}

function is_facebook($agent=false) {
	return is_agent('facebook', $agent);
}
