<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function nobody($lang) {
	session_reopen();

	return redirect('home', $lang);
}

