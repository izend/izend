<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function client_ip_address() {
	return $_SERVER['REMOTE_ADDR'];	// DON'T TRUST $_SERVER['HTTP_X_FORWARDED_FOR']
}

