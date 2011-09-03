<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function user_profile($info=false) {
	return isset($_SESSION['user']) ? ($info ? (isset($_SESSION['user'][$info]) ? $_SESSION['user'][$info] : false) : $_SESSION['user']) : false;
}

