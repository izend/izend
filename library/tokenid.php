<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function token_id() {
	return md5(uniqid(rand(), true));
}

