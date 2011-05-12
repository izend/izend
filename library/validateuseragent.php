<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function validate_user_agent($agent) {
	return preg_match('/^[a-zA-Z0-9 \;\:\.\-\)\(\/\@\]\[\+\~\_\,\?\=\{\}\*\|\&\#\!]+$/', $agent);
}

