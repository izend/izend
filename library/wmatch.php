<?php

/**
 *
 * @copyright  2010-2025 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'strflat.php';

function wmatch($word, $wl, $dlimit=0, $closest=true) {
	$word = strtolower(strflat($word));
	$ret = false;

	foreach ($wl as $w) {
	    $d = levenshtein($word, strtolower(strflat($w)));

	    if ($d < 0) {
	    	continue;
	    }

	    /* DON'T return immediately if $d is 0 to be case and accent insensitive */

	    if ($d <= $dlimit) {
			if ($closest && $d < $dlimit) {
				$ret=array($w);
				$dlimit=$d;
			}
			else {
				if ($ret === false)
					$ret=array();
				$ret[]=$w;
			}
		}
	}

	return $ret;
}

