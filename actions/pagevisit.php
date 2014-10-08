<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';

function pagevisit($lang, $arglist=false) {
	$node_id=isset($_SESSION['visited']) ? $_SESSION['visited'] : false;

	if (!$node_id) {
		return false;
	}

	$r = node_add_visit($node_id, $lang);

	if ($r) {
		$_SESSION['visits'][]=$node_id;
	}

	unset($_SESSION['visited']);

	return true;
}

