<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function is_mail_injected($s) {
	$injections = array(
	'(\n+)',
	'(\r+)',
	'(\t+)',
	'(%0A+)',
	'(%0D+)',
	'(%08+)',
	'(%09+)'
	);
	$reg = implode('|', $injections);
	$reg = "/$reg/";

	return preg_match($reg, $s);
}

