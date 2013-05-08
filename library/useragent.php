<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function user_agent() {
	return !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
}

