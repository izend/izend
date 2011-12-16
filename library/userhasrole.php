<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'userisidentified.php';

function user_has_role($role) {
	return user_is_identified() and !empty($_SESSION['user']['role']) and in_array($role, $_SESSION['user']['role']);
}

