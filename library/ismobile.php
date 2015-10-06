<?php

/**
 *
 * @copyright  2010-2015 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function is_mobile($agent=false) {
	if (!$agent) {
		require_once 'useragent.php';

		$agent=user_agent();
	}

	return $agent and preg_match('/android|webos|iphone|ipad|ipod|iemobile|blackberry|opera mini/i', $agent);
}

