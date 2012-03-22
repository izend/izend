<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function is_filename_allowed($file) {
	global $blackfilelist;

	return !in_array($file, $blackfilelist);
}

