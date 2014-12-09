<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function array_closest($arr, $val, $below=false) {
	$narr=count($arr);

	if ($narr == 0) {
		return $val;
	}

	if ($narr == 1) {
		return $arr[0];
	}

	sort($arr);

	if ($arr[0] >= $val) {
		return $arr[0];
	}
	if ($arr[$narr - 1] <= $val) {
		return $arr[$narr - 1];
	}

    foreach($arr as $v) {
    	if ($v == $val) {
    		return $v;
    	}
        if ($v > $val) {
        	return $below ? $prev : $v;
        }
        $prev=$v;
    }
}
