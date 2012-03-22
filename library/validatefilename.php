<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_filename($name) {
	return preg_match('/^[[:alpha:]]+[[:alnum:]_-]*\.[[:alnum:]]+$/', $name);
}

