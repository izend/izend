<?php

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    1
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';
require_once 'models/cloud.inc';

function threadnodeeditor($lang, $clang, $thread_id, $node_id) {
	$output = build('nodeeditor', $lang, $clang, $node_id);

	if (isset($_POST['node_edit'])) {
		$r = node_get($clang, $node_id, false);
		if ($r) {
			$node_cloud=$r['node_cloud'];
			cloud_tag_node($clang, $node_id, $node_cloud);
		}
	}

	return $output;
}

