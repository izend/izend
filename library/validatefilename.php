<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function validate_filename($name) {
	return preg_match('/^[[:alpha:]]+[[:alnum:]_-]*(\.[[:alnum:]]+)?$/', $name);
}

