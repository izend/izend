<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';

function comments($lang, $node_id) {
	$r = node_get_comments($node_id, $lang);
	if (!$r) {
		return false;
	}
	$comments=$r;

	$output = view('comments', $lang, compact('comments'));

	return $output;
}

