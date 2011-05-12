<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_db_name($s) {
	$regexp='/^[a-zA-Z][a-zA-Z0-9_]{1,30}$/';

	return preg_match($regexp, $s);
}

