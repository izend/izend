<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function validate_color($color, $with_hashmark=true) {
	return preg_match($with_hashmark ? '/^#?([0-9A-F]){6}$/i' : '/^([0-9A-F]){6}$/i', $color);
}

