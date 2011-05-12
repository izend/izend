<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';
require_once 'models/node.inc';

function nodeurl($lang, $thread_id, $node_id) {
	global $db_debug;

	$flag = $db_debug;
	$db_debug = false;

	$action = false;
	$args = array();

	if ($thread_id) {
		$r = thread_get($lang, $thread_id);

		if (!$r) {
			return false;
		}

		$action = $r['thread_type'];
		$args[] = $r['thread_name'];
	}

	if ($node_id) {
		$r = node_get($lang, $node_id);

		if (!$r) {
			return false;
		}

		$args[] = $r['node_name'];
	}

	$db_debug = $flag;

	return url($action, $lang, $args);
}

