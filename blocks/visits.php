<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';

function visits($lang, $node_id) {
	$visits=node_get_visits($node_id, $lang);

	$output = view('visits', $lang, compact('visits'));

	return $output;
}

