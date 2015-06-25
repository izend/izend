<?php

/**
 *
 * @copyright  2015 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

global $stopwords;

$stopwords = array();

@include 'stopwords.inc';

require_once 'strflat.php';

function strfilter($s, $lang) {
	global $stopwords;

	if ($s) {
		$wlist=array_map('strtolower', array_map('strflat', array_unique(preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY))));

		if ($lang && array_key_exists($lang, $stopwords)) {
			$wlist=array_diff($wlist, $stopwords[$lang]);
		}

		return implode(' ', $wlist);
	}

	return false;
}
