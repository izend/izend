<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function nobody($lang) {
	session_reopen();

	redirect('home', $lang);
}

