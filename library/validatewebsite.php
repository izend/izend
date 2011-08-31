<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_website($url) {
	return preg_match('#^(http(s)?://)?(www\.)?([^\.]+)\.[a-z]{2,}$#', $url);
}

