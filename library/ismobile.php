<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function is_mobile($agent=false) {
	if (!$agent) {
		require_once 'useragent.php';

		$agent=user_agent();
	}

	return $agent and preg_match('/android|iphone|ipad|ipod|blackberry|opera mini/i', $agent);
}

