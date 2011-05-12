<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version		1
 * @link		http://www.izend.org
 */

function request_uri() {
	if (isset($_SERVER['REQUEST_URI'])) {
		$uri = $_SERVER['REQUEST_URI'];
	}
	else {
		if (isset($_SERVER['argv'])) {
			$uri = $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['argv'][0];
		}
		elseif (isset($_SERVER['QUERY_STRING'])) {
			$uri = $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING'];
		}
		else {
			$uri = $_SERVER['SCRIPT_NAME'];
		}
	}

	$uri = '/'. ltrim($uri, '/');

	return $uri;
}

