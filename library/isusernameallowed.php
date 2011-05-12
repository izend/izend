<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function is_user_name_allowed($name) {
	global $blacknamelist;

	return !in_array($name, $blacknamelist);
}

