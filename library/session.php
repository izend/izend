<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function session_reopen() {
	session_close();
	session_open();
}

function session_open() {
	session_start();
}

function session_close() {
	session_destroy();
	$_SESSION=array();
}

function session_regenerate() {
	session_regenerate_id(true);
}
