<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function user_has_role($role) {
	return isset($_SESSION['user']) and $_SESSION['user']['role'] and in_array($role, $_SESSION['user']['role']);
}

