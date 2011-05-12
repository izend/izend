<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

function thread($lang, $arglist=false) {
	$thread=$node=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$thread=$arglist[0];
		}
		if (isset($arglist[1])) {
			$node=$arglist[1];
		}
	}

	if (!$thread) {
		require_once 'actions/threadall.php';

		return threadall($lang);
	}

	if (!$node) {
		require_once 'actions/threadsummary.php';

		return threadsummary($lang, $thread);

	}

	require_once 'actions/threadnode.php';

	return threadnode($lang, $thread, $node);
}

