<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_role($role) {
	global $supported_roles;

	return in_array($role, $supported_roles);
}

