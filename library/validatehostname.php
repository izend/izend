<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_host_name($host) {
	return preg_match('/^\[?(?:[a-z0-9-:\]_]+\.?)+$/', $host);
}

