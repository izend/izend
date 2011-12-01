<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function user_is_identified() {
	global $login_lifetime;

	return isset($_SESSION['user']) and (!$login_lifetime or $_SESSION['idletime'] <= $login_lifetime);
}

