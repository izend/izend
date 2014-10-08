<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    3
 * @link       http://www.izend.org
 */

require_once 'isagent.php';
require_once 'models/node.inc';

function visits($lang, $node_id, $nomore=false) {
	$visits=node_get_visits($node_id, $lang);

	$visit_page=false;
	if (!$nomore and (!isset($_SESSION['visits']) or !in_array($node_id, $_SESSION['visits'])) and !is_bot() and !is_opengraph()) {
		$visit_page=url('pagevisit', $lang);
		$_SESSION['visited']=$node_id;
	}

	$output = view('visits', $lang, compact('visits', 'visit_page'));

	return $output;
}

