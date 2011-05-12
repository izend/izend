<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function nobody($lang) {
	session_reopen();

	$next_page=isset($_SESSION['starlink']) ? $_SESSION['starlink'] : url('home', $lang);
	header("Location: $next_page");

	return false;
}

