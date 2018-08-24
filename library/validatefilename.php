<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function validate_filename($name) {
	return preg_match('/^[[:alpha:]]+[[:alnum:] \._-]*(\.[[:alnum:]]+)?$/', $name);
}

