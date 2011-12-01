<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';

function user_has_role($role) {
	return user_is_identified() and in_array($role, $_SESSION['user']['role']);
}

