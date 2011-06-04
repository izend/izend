<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
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

		if ($d == 0 && $closest) {
			return array($w);
		}

		if ($d <= $dlimit) {
			if ($closest && $d < $dlimit) {
				$ret=array($w);
				$dlimit=$d;
			}
			else {
				$ret[]=$w;
			}
		}
	}

	return $ret;
}

