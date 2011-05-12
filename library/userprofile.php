<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function user_profile() {
	return isset($_SESSION['user']) ? $_SESSION['user'] : false;
}

