<?php

/**
 *
 * @copyright  2013 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/thread.inc';

function nodethread($lang, $node) {
	global $default_folder;

	if (!$default_folder) {
		return false;
	}

	foreach (is_array($default_folder) ? $default_folder : array($default_folder) as $folder) {
		$thread_id = thread_id($folder);
		if ($thread_id) {
			$node_id=thread_node_id($thread_id, $node, $lang);
			if ($node_id) {
				return $thread_id;
			}
		}
	}

	return false;
}
