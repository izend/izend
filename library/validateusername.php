<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_user_name($name) {
	return preg_match('/^[a-z]{2,20}$/', $name);
}

