<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function token_id() {
	return md5(uniqid(rand(), TRUE));
}

