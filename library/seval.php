<?php

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

function seval($s) {
	global $base_path, $base_url, $base_root;

	ob_start();
	echo eval('?>'. $s);
	return ob_get_clean();
}

