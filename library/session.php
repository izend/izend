<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

function session_reopen($name=false) {
	session_close();
	session_open($name);
}

function session_open($name=false) {
	if ($name) {
		session_name($name);
	}
	session_start();
}

function session_close() {
	session_destroy();
	$_SESSION=array();
}

function session_regenerate() {
	session_regenerate_id(true);
}
