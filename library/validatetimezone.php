<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_timezone($tz) {
	return in_array($tz, timezone_identifiers_list());
}
