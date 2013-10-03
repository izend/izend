<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function validate_password($s) {
//	$regexp='/(?=[a-zA-Z0-9]*?[A-Z])(?=[a-zA-Z0-9]*?[a-z])(?=[a-zA-Z0-9]*?[0-9])[a-zA-Z0-9]{6,}/';
	$regexp='/(?=[a-zA-Z0-9]*?[A-Za-z])(?=[a-zA-Z0-9]*?[0-9])[a-zA-Z0-9]{6,}/';

	return preg_match($regexp, $s);
}

