<?php

/**
 *
 * @copyright  2010-2018 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';
require_once 'models/node.inc';

function nodeurl($lang, $thread_id, $node_id, $action=false) {
	$args = array();

	if ($thread_id) {
		$r = thread_get($lang, $thread_id);

		if (!$r) {
			return false;
		}

		if (!$action) {
			$action = $r['thread_type'];
		}
		$args[] = $r['thread_name'];
	}

	if ($node_id) {
		$r = node_get($lang, $node_id);

		if (!$r) {
			return false;
		}

		$args[] = $r['node_name'];
	}

	return url($action, $lang, $args);
}

