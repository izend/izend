<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    6
 * @link       http://www.izend.org
 */

function client_ip_address() {
	return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : (PHP_SAPI == 'cli' ? '127.0.0.1' : false);	// DON'T TRUST $_SERVER['HTTP_X_FORWARDED_FOR']
}

