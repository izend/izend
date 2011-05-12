<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function seval($s) {
	ob_start();
	echo eval('?>'. $s);
	return ob_get_clean();
}

