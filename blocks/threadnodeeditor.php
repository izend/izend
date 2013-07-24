<?php

/**
 *
 * @copyright  2010-2013 izend.org
 * @version    2
 * @link       http://www.izend.org
 */

require_once 'models/node.inc';
require_once 'models/cloud.inc';

function threadnodeeditor($lang, $clang, $thread_id, $node_id, $content_types) {
	$output = build('nodeeditor', $lang, $clang, $node_id, $content_types);

	if (isset($_POST['node_edit'])) {
		$r = node_get($clang, $node_id, false);
		if ($r) {
			$node_cloud=$r['node_cloud'];
			cloud_tag_node($clang, $node_id, $node_cloud);
		}
	}

	return $output;
}

