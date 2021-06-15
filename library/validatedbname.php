<?php

/**
 *
 * @copyright  2010-2021 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function validate_db_name($s) {
	$regexp='/^[a-zA-Z][a-zA-Z0-9_]{1,64}$/';

	return preg_match($regexp, $s);
}

