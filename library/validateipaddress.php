<?php

/**
 *
 * @copyright  2010-2026 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function validate_ip_address($ipaddress) {
	return filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
}

