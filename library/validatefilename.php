<?php

/**
 *
 * @copyright  2010-2019 izend.org
 * @version    4
 * @link       http://www.izend.org
 */

function validate_filename($name) {
	return preg_match('/^[[:alnum:]]+[[:alnum:] \._-]*(\.[[:alnum:]]+)?$/', $name);
}

