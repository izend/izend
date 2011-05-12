<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_locale($locale) {
	global $system_languages;

	return in_array($locale, $system_languages);
}

