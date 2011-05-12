<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version		1
 * @link		http://www.izend.org
 */

function user_agent() {
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$agent = $_SERVER['HTTP_USER_AGENT'];
	}

	return $agent;
}

