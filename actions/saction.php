<?php

/**
 *
 * @copyright  2014 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'urlencodeaction.php';

function saction($lang, $arglist=false) {
	static $actions = array(
		1 => 'confirmnewsletterunsubscribe'
	);

	$s64=false;

	if (is_array($arglist)) {
		if (isset($arglist[0])) {
			$s64=$arglist[0];
		}
	}

	if (!$s64) {
		return run('error/badrequest', $lang);
	}

	$r = urldecodeaction($s64);

	if (!$r) {
		return run('error/badrequest', $lang);
	}

	list($actionid, $timestamp, $param)=$r;

	if (!isset($actions[$actionid])) {
		return run('error/notimplemented', $lang);
	}

	$action=$actions[$actionid];

	return run($action, $lang, array($timestamp, $param));
}
