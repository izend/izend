<?php

/**
 *
 * @copyright  2010-2016 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function validate_mail($email) {
	return preg_match('/^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,}$/', $email);
}

