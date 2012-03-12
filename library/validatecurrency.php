<?php

/**
 *
 * @copyright  2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_currency($currency) {
	global $supported_currencies;

	return in_array($currency, $supported_currencies);
}

