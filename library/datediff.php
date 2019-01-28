<?php

/**
 *
 * @copyright  2019 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function datediff($d1, $d2) {
	$tz=new DateTimeZone('UTC');

	return date_diff(date_create(date('Y-m-d', $d2 > $d1 ? $d1 : $d2), $tz), date_create(date('Y-m-d', $d2 > $d1 ? $d2 : $d1), $tz));
}
